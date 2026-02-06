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
            // 查询当天有库存的奖品
            $prize = Lottery::where('dates', $today)
                ->whereRaw('number_all > number_user')
                ->limit(1)
                ->find();

            if (!$prize) {
                return [
                    'is_win' => 0,
                    'message' => '今日奖品已派完'
                ];
            }

            // 3. 校验奖品条件（用户限额、奖金池）
            
            // 3.1 用户是否还有中奖次数 (对比 can_win 字段)
            if (isset($prize->can_win) && $prize->can_win > 0) {
                $userWinCount = LotteryRecord::where('openid', $user->openid)
                    ->where('lottery_id', $prize->id)
                    ->where('is_win', 1)
                    ->count();
                if ($userWinCount >= $prize->can_win) {
                    return [
                        'is_win' => 0,
                        'message' => '您已达到今日中奖上限'
                    ];
                }
            }

            // 3.2 检查总奖金池 (bonuses_pool)
            if (isset($prize->bonuses_pool) && $prize->bonuses_pool > 0) {
                $usedAmount = LotteryRecord::where('lottery_id', $prize->id)
                    ->where('is_win', 1)
                    ->sum('amount');
                if ($usedAmount >= $prize->bonuses_pool) {
                     return [
                        'is_win' => 0,
                        'message' => '奖池已空'
                    ];
                }
                // 将已用金额暂存到对象中，供后续计算使用
                $prize->used_amount = $usedAmount;
            }

            // 5. 计算中奖金额
            $min = floatval($prize->min);
            $max = floatval($prize->max);
            
            // 生成随机金额，保留2位小数
            $randomAmount = $min + mt_rand() / mt_getrandmax() * ($max - $min);
            
            // 检查奖金池剩余金额，如果随机金额超过剩余金额，则取剩余金额
            if (isset($prize->bonuses_pool) && $prize->bonuses_pool > 0) {
                $remaining = $prize->bonuses_pool - ($prize->used_amount ?? 0);
                if ($randomAmount > $remaining) {
                    $randomAmount = $remaining;
                }
            }
            
            $amountStr = number_format($randomAmount, 2, '.', '');
            
            // 如果金额太小（例如0），则视为未中奖或异常
            if ($randomAmount <= 0) {
                 return [
                    'is_win' => 0,
                    'message' => '奖池已空'
                ];
            }

            // 6. 开启事务记录
            Db::startTrans();
            try {
                // 增加已发放数量
                // 使用乐观锁更新
                $res = Lottery::where('id', $prize->id)
                    ->where('number_user', '<', Db::raw('number_all'))
                    ->inc('number_user')
                    ->update();

                if (!$res) {
                    Db::rollback();
                    return [
                        'is_win' => 0,
                        'message' => '手慢了，奖品刚发完'
                    ];
                }

                // 记录抽奖记录
                LotteryRecord::create([
                    'openid' => $user->openid, // 存 用户openid
                    'lottery_id' => $prize->id,
                    'is_win' => 1,
                    'amount' => $amountStr,
                    'prize_name' => $prize->name,
                    'create_time' => time(),
                    'update_time' => time()
                ]);

                // 发送红包
                $sendRes = self::sendRedPacket($user->openid, $randomAmount);
                if ($sendRes['errcode'] != 0) {
                    Db::rollback();
                    return [
                        'is_win' => 0,
                        'message' => '奖品发放失败:' . $sendRes['errmsg']
                    ];
                }

                Db::commit();

                return [
                    'is_win' => 1,
                    'prize_name' => $prize->name,
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

    /**
     * @notes 提交联系人信息
     * @param $params
     * @return bool
     */
    public static function submitContact($params)
    {
        try {
            \app\common\model\marketing\LotteryContact::create([
                'openid' => $params['openid'] ?? '',
                'name' => $params['name'],
                'phone' => $params['phone'],
                'business' => $params['business'],
                'region' => $params['region'],
                'request' => $params['request'] ?? '',
                'create_time' => time(),
                'update_time' => time()
            ]);
            return true;
        } catch (\Exception $e) {
            self::setError($e->getMessage());
            return false;
        }
    }

    /**
     * @notes 发送红包接口
     * @param string $openid 用户openid
     * @param float $money 金额（元）
     * @param string $wishing 祝福语
     * @param string $title 活动名称
     * @param string $sendName 发送方名称
     * @return array
     */
    public static function sendRedPacket($openid, $money, $wishing = '心想事成', $title = '恭喜发财', $sendName = '千跃科技')
    {
        // 接口配置
        $domain = 'www.yaoyaola.net';
        $uid = '10815991';
        $apikey = 'A7f9K2M8Qe4XbT6RZ0nP3sY'; // 需替换为实际APIKEY

        // 参数处理
        $moneyFen = intval(strval($money * 100)); // 转为分
        $type = 0; // 默认红包接口
        if ($moneyFen > 20000) { // 大于200元使用企业付款
            $type = 1;
        }
        
        // 校验金额
        if ($moneyFen < 30) {
            return ['errcode' => -1, 'errmsg' => '红包金额不能低于0.3元'];
        }

        $orderid = date('YmdHis') . mt_rand(100000, 999999);
        $reqtick = time();
        
        // 签名 sign = md5(uid+type+orderid+money+reqtick+openid+apikey)
        $signStr = $uid . $type . $orderid . $moneyFen . $reqtick . $openid . $apikey;
        $sign = md5($signStr);

        // 请求参数
        $params = [
            'uid' => $uid,
            'type' => $type,
            'money' => $moneyFen,
            'orderid' => $orderid,
            'reqtick' => $reqtick,
            'openid' => $openid,
            'sign' => $sign,
            'title' => $title,
            'sendname' => $sendName,
            'wishing' => $wishing
        ];

        // 发送请求
        $url = "https://{$domain}/exapi/SendRedPackToOpenid?" . http_build_query($params);
        
        $logData = [
            'openid' => $openid,
            'money' => $money,
            'params' => json_encode($params, JSON_UNESCAPED_UNICODE),
            'create_time' => time()
        ];

        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            $result = curl_exec($ch);
            
            if (curl_errno($ch)) {
                throw new \Exception(curl_error($ch));
            }
            curl_close($ch);
            
            $logData['result'] = $result;
            $res = json_decode($result, true);

            if ($res && isset($res['errcode']) && $res['errcode'] == 0) {
                 $logData['status'] = 1;
                 $logData['message'] = '发送成功';
            } else {
                 $logData['status'] = 0;
                 $logData['message'] = $res['errmsg'] ?? '接口返回异常或解析失败';
            }
            \app\common\model\marketing\LotteryLog::create($logData);

            return $res ?: ['errcode' => -1, 'errmsg' => '接口返回异常'];
            
        } catch (\Exception $e) {
            $logData['status'] = 0;
            $logData['message'] = $e->getMessage();
            $logData['result'] = 'Exception: ' . $e->getMessage();
            \app\common\model\marketing\LotteryLog::create($logData);

            return ['errcode' => -1, 'errmsg' => $e->getMessage()];
        }
    }
}
