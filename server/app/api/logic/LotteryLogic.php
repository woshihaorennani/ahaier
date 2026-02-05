<?php
namespace app\api\logic;

use app\common\logic\BaseLogic;
use app\common\model\marketing\Lottery;
use app\common\model\marketing\LotteryRecord;
use app\common\model\marketing\WeixinUser;
use think\facade\Db;

class LotteryLogic extends BaseLogic
{
    /**
     * @notes 抽奖逻辑
     * @param string $openidString
     * @return array|false
     */
    public static function draw($openidString)
    {
        try {
            // 1. 获取用户
            $user = WeixinUser::where('openid', $openidString)->find();
            if (!$user) {
                self::setError('用户不存在');
                return false;
            }

            // 2. 查询今日可用奖品
            $today = date('Y-m-d');
            $prizes = Lottery::where('available_date', $today)
                ->whereRaw('total_quantity > distributed_quantity')
                ->select();

            if ($prizes->isEmpty()) {
                // 没有奖品，返回未中奖状态
                return [
                    'is_win' => 0,
                    'message' => '今日奖品已派完或无活动'
                ];
            }

            // 3. 随机选取一个奖品 (这里简单取第一个，或者随机)
            // 假设同一天可能有多个配置，随机取一个
            $prize = $prizes->random();

            // 4. 计算中奖金额
            // prize_range 格式如 "0.1 - 0.3" 或 "¥0.1 - ¥0.3"
            $rangeStr = str_replace(['¥', '￥', ' '], '', $prize->prize_range);
            $range = explode('-', $rangeStr);
            $min = isset($range[0]) ? floatval($range[0]) : 0;
            $max = isset($range[1]) ? floatval($range[1]) : 0;
            
            // 生成随机金额，保留2位小数
            $randomAmount = $min + mt_rand() / mt_getrandmax() * ($max - $min);
            $amountStr = number_format($randomAmount, 2, '.', '');

            // 5. 开启事务记录
            Db::startTrans();
            try {
                // 增加已发放数量
                // 使用乐观锁或直接更新，这里直接更新
                $res = Lottery::where('id', $prize->id)
                    ->where('distributed_quantity', '<', Db::raw('total_quantity'))
                    ->inc('distributed_quantity')
                    ->update();

                if (!$res) {
                    // 并发情况下可能刚发完
                    Db::rollback();
                    return [
                        'is_win' => 0,
                        'message' => '手慢了，奖品刚发完'
                    ];
                }

                // 记录抽奖记录
                LotteryRecord::create([
                    'openid' => $user->id, // 存 ID
                    'lottery_id' => $prize->id,
                    'is_win' => 1,
                    'amount' => $amountStr, // 存金额
                    'prize_name' => $prize->prize_name,
                    'create_time' => time(),
                    'update_time' => time()
                ]);

                Db::commit();

                return [
                    'is_win' => 1,
                    'prize_name' => $prize->prize_name,
                    'amount' => $amountStr
                ];

            } catch (\Exception $e) {
                Db::rollback();
                throw $e;
            }

        } catch (\Exception $e) {
            self::setError('抽奖失败:' . $e->getMessage());
            return false;
        }
    }
}
