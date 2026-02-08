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

            // 开启事务 (针对高并发，提前开启事务并加锁)
            Db::startTrans();
            try {
                // 2. 查询今日可用奖品
                $today = date('Y-m-d');
                // 查询当天有库存的奖品，并加锁 (lock for update)
                $prize = Lottery::where('dates', $today)
                    ->whereRaw('number_all > number_user')
                    ->limit(1)
                    ->lock(true)
                    ->find();

                if (!$prize) {
                    Db::rollback();
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
                        Db::rollback();
                        return [
                            'is_win' => 0,
                            'message' => '您已达到今日中奖上限'
                        ];
                    }
                }

                // 3.2 检查总奖金池 (使用 distributed_amount 字段代替 sum 查询)
                if (isset($prize->bonuses_pool) && $prize->bonuses_pool > 0) {
                    $usedAmount = $prize->distributed_amount; // 直接使用字段值
                    
                    if ($usedAmount >= $prize->bonuses_pool) {
                        Db::rollback();
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

                // 检查奖金池剩余金额
                if (isset($prize->bonuses_pool) && $prize->bonuses_pool > 0) {
                    $remainingPool = $prize->bonuses_pool - ($prize->used_amount ?? 0);
                    
                    // 如果剩余金额连最小奖金都不够，直接返回未中奖
                    if ($remainingPool < $min) {
                        Db::rollback();
                        return [
                            'is_win' => 0,
                            'message' => '奖池余额不足'
                        ];
                    }

                    // 动态调整最大金额，确保 number_all 都能中出
                    if ($prize->number_all > 0) {
                        $remainingCount = $prize->number_all - $prize->number_user; // 剩余数量（含本次）
                        
                        // 为后续每个人预留最少金额 min
                        $reservedForOthers = max(0, ($remainingCount - 1) * $min);
                        
                        // 本次允许的最大金额 = 剩余奖金 - 预留金额
                        $safeMax = $remainingPool - $reservedForOthers;
                        
                        // 确保最大值不超过 safeMax，同时保证至少能发出 min
                        // 优先保证当前用户能拿到 min (因为 remainingPool >= min 已校验)
                        // 如果 safeMax < min，说明奖池紧张，无法完全保证后续用户，但当前用户必须满足 >= min
                        $calculatedMax = min($max, $safeMax);
                        $max = max($min, $calculatedMax);
                    } else {
                        // 如果没有总数量限制，则最大值受限于剩余奖金池
                        $max = min($max, $remainingPool);
                    }
                }
                
                // 生成随机金额，保留2位小数
                if ($max <= $min) {
                    $randomAmount = $min;
                } else {
                    $randomAmount = $min + mt_rand() / mt_getrandmax() * ($max - $min);
                }
                
                // 兜底检查：确保不超过剩余奖金池
                if (isset($remainingPool) && $randomAmount > $remainingPool) {
                    $randomAmount = $remainingPool;
                }
                
                $amountStr = number_format($randomAmount, 2, '.', '');
                
                // 如果金额太小（例如0），则视为未中奖或异常
                if ($randomAmount <= 0) {
                     Db::rollback();
                     return [
                        'is_win' => 0,
                        'message' => '奖池已空'
                    ];
                }

                // 更新奖品库存和已发放金额
                $res = Lottery::where('id', $prize->id)
                    ->inc('number_user')
                    ->inc('distributed_amount', $randomAmount)
                    ->update();

                if (!$res) {
                    Db::rollback();
                    return [
                        'is_win' => 0,
                        'message' => '更新失败'
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

                Db::commit();

                // 发送红包 (建议：在大流量下，外部API调用应移出数据库事务，避免长时间占用锁)
                // $sendRes = self::sendRedPacket($user->openid, $randomAmount);
                // if ($sendRes['errcode'] != 0) {
                //     // 记录错误日志，后续重试
                //     // Log::error('RedPacket Fail: ' . $sendRes['errmsg']);
                // }

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
        $type = 1; // 默认红包接口

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
