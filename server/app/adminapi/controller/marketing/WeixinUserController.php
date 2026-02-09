<?php

namespace app\adminapi\controller\marketing;

use app\adminapi\controller\BaseAdminController;
use app\adminapi\lists\marketing\WeixinUserLists;
use app\common\model\marketing\Lottery;
use app\common\model\marketing\LotteryRecord;
use think\facade\Db;

class WeixinUserController extends BaseAdminController
{
    public function lists()
    {
        return $this->dataLists(new WeixinUserLists());
    }

    /**
     * @notes 发送红包
     */
    public function sendRedPacket()
    {
        $params = $this->request->post();
        $openid = $params['openid'] ?? '';
        $minMoney = $params['min_money'] ?? 0;
        $maxMoney = $params['max_money'] ?? 0;
        $count = $params['count'] ?? 1;

        if (empty($openid) || empty($minMoney) || empty($maxMoney) || empty($count)) {
             return $this->fail('参数缺失');
        }

        if ($minMoney > $maxMoney) {
            return $this->fail('最小金额不能大于最大金额');
        }

        // 查找今日进行的抽奖活动
        $today = date('Y-m-d');
        $lottery = Lottery::where('dates', $today)->find();
        if (!$lottery) {
            return $this->fail("今日({$today})无进行中的抽奖活动，无法生成记录");
        }

        $successCount = 0;
        $failCount = 0;
        $lastError = '';

        Db::startTrans();
        try {
            // 锁定活动记录，确保并发安全
            $lottery = Lottery::where('id', $lottery->id)->lock(true)->find();
            
            $totalAmount = 0;
            $records = [];

            for ($i = 0; $i < $count; $i++) {
                // 生成随机金额 (保留2位小数)
                $minFen = intval(strval($minMoney * 100));
                $maxFen = intval(strval($maxMoney * 100));
                $randomFen = mt_rand($minFen, $maxFen);
                $money = $randomFen / 100;

                // 准备记录数据
                $records[] = [
                    'openid'      => $openid,
                    'lottery_id'  => $lottery->id,
                    'is_win'      => 1,
                    'amount'      => $money,
                    'prize_name'  => $lottery->name ?? '红包奖励',
                    'create_time' => time(),
                    'update_time' => time(),
                    'is_send'     => 0, // 未发送
                    'send_code'   => null
                ];

                $totalAmount += $money;
                $successCount++;
            }

            // 批量写入记录
            if (!empty($records)) {
                $lotteryRecordModel = new LotteryRecord();
                $lotteryRecordModel->saveAll($records);

                // 更新活动统计
                $lottery->number_user += $successCount;
                $lottery->distributed_amount += $totalAmount;
                $lottery->save();
            }

            Db::commit();

            return $this->success("成功预生成{$successCount}条红包记录，待发送");

        } catch (\Exception $e) {
            Db::rollback();
            return $this->fail('生成失败: ' . $e->getMessage());
        }
    }
}
