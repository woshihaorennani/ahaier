<?php
namespace app\api\logic;

use app\common\logic\BaseLogic;
use app\common\model\marketing\Lottery;
use app\common\model\marketing\LotteryRecord;
use app\common\model\marketing\LotteryLog;
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
                self::recordLog($openidString, 0, 0, '用户不存在');
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
                    self::recordLog($user->openid, 0, 0, '今日奖品已派完');
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
                        self::recordLog($user->openid, 0, 0, '您已达到今日中奖上限', ['lottery_id' => $prize->id]);
                        return [
                            'is_win' => 0,
                            'message' => '您已达到今日中奖上限'
                        ];
                    }
                }

                // 3.2 检查总奖金池 (使用 distributed_amount 字段代替 sum 查询)
                if (isset($prize->bonuses_pool) && $prize->bonuses_pool > 0) {
                    $usedAmount = $prize->distributed_amount; // 直接使用字段值
                    
                    // 自动修复：如果字段显示奖池已空或不足最小金额，则进行二次核对
                    // 这种情况通常发生在历史数据存在精度漂移时
                    $minPrize = floatval($prize->min);
                    if ($usedAmount >= $prize->bonuses_pool || ($prize->bonuses_pool - $usedAmount) < $minPrize) {
                        // 使用真实记录求和进行核对
                        $realUsed = LotteryRecord::where('lottery_id', $prize->id)
                            ->where('is_win', 1)
                            ->sum('amount');
                            
                        // 如果真实记录显示还有余额
                        if ($realUsed < $prize->bonuses_pool && ($prize->bonuses_pool - $realUsed) >= $minPrize) {
                            // 修正数据库中的脏数据
                            Lottery::where('id', $prize->id)->update(['distributed_amount' => $realUsed]);
                            // 使用修正后的值
                            $usedAmount = $realUsed;
                            // 更新对象中的值，供后续逻辑使用
                            $prize->distributed_amount = $realUsed;
                        }
                    }
                    
                    if ($usedAmount >= $prize->bonuses_pool) {
                        Db::rollback();
                         self::recordLog($user->openid, 0, 0, '奖池已空', ['lottery_id' => $prize->id]);
                         return [
                            'is_win' => 0,
                            'message' => '奖池已空'
                        ];
                    }
                    // 将已用金额暂存到对象中，供后续计算使用
                    $prize->used_amount = $usedAmount;
                }

                // 5. 计算中奖金额
                $isSpecial = false;
                $actualAmount = 0;
                $amountStr = '0.00';

                // 优先检查特等奖
                if (!empty($prize->special) && $prize->special > 0 && !empty($prize->special_number) && $prize->special_number > 0) {
                    // 获取已中特等奖数量 (假设字段 special_user 存在)
                    $specialUsed = $prize->special_user ?? 0;
                    
                    if ($specialUsed < $prize->special_number) {
                        $specialAmount = floatval($prize->special);
                        
                        // 检查奖金池是否足够支付特等奖
                        if (isset($prize->bonuses_pool) && $prize->bonuses_pool > 0) {
                            $remainingPool = $prize->bonuses_pool - ($prize->used_amount ?? 0);
                            if ($remainingPool < $specialAmount) {
                                // 奖池不足，无法发放特等奖
                                Db::rollback();
                                self::recordLog($user->openid, 0, 0, '特等奖奖池余额不足', ['lottery_id' => $prize->id, 'remaining' => $remainingPool, 'special' => $specialAmount]);
                                return [
                                    'is_win' => 0,
                                    'message' => '奖池余额不足'
                                ];
                            }
                        }
                        
                        $actualAmount = $specialAmount;
                        $amountStr = number_format($actualAmount, 2, '.', '');
                        $isSpecial = true;
                    }
                }

                if (!$isSpecial) {
                    // 普通奖项逻辑
                    $min = floatval($prize->min);
                    $max = floatval($prize->max);

                    // 检查奖金池剩余金额
                    if (isset($prize->bonuses_pool) && $prize->bonuses_pool > 0) {
                        $remainingPool = $prize->bonuses_pool - ($prize->used_amount ?? 0);
                        
                        // 如果剩余金额连最小奖金都不够，直接返回未中奖
                        if ($remainingPool < $min) {
                            Db::rollback();
                            self::recordLog($user->openid, 0, 0, '奖池余额不足', ['lottery_id' => $prize->id, 'remaining' => $remainingPool, 'min' => $min]);
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
                    $actualAmount = floatval($amountStr);
                }
                
                // 如果金额太小（例如0），则视为未中奖或异常
                if ($actualAmount <= 0) {
                     Db::rollback();
                     self::recordLog($user->openid, 0, 0, '计算金额异常', ['lottery_id' => $prize->id, 'calc_amount' => $actualAmount]);
                     return [
                        'is_win' => 0,
                        'message' => '奖池已空'
                    ];
                }

                // 更新奖品库存和已发放金额
                $updateQuery = Lottery::where('id', $prize->id)
                    ->inc('number_user')
                    ->inc('distributed_amount', $actualAmount);
                
                if ($isSpecial) {
                    $updateQuery->inc('special_user');
                }

                $res = $updateQuery->update();

                if (!$res) {
                    Db::rollback();
                    self::recordLog($user->openid, $actualAmount, 0, '更新库存失败', ['lottery_id' => $prize->id]);
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

                self::recordLog($user->openid, $actualAmount, 1, '中奖成功', ['lottery_id' => $prize->id], ['amount' => $amountStr]);

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
            $logOpenid = isset($user) && $user ? $user->openid : $openidString;
            self::recordLog($logOpenid, 0, 0, '抽奖异常:' . $e->getMessage());
            self::setError('抽奖失败:' . $e->getMessage());
            return false;
        }
    }

    /**
     * @notes 记录日志
     * @param $openid
     * @param $money
     * @param $status
     * @param $message
     * @param $params
     * @param $result
     */
    private static function recordLog($openid, $money, $status, $message, $params = [], $result = [])
    {
        try {
            LotteryLog::create([
                'openid' => $openid,
                'money' => $money,
                'status' => $status,
                'message' => $message,
                'params' => json_encode($params, JSON_UNESCAPED_UNICODE),
                'result' => json_encode($result, JSON_UNESCAPED_UNICODE),
                'create_time' => time()
            ]);
        } catch (\Exception $e) {
            // 日志记录失败忽略
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

    /**
     * @notes 批量发送红包（定时任务调用）
     * @param int $limit 每次处理数量
     * @return array
     */
    public static function batchSendRedPacketTask($limit = 60)
    {
        // 1. 生成唯一 send_code
        $sendCode = uniqid('batch_') . mt_rand(1000, 9999);

        // 2. 锁定待发送记录 (乐观锁/任务认领)
        // 更新条件：未发送(is_send=0) 且 未被认领(send_code is null)
        // 注意：thinkphp 的 update limit 可能不支持，需先查主键或直接用原生sql，或者这里假设 id 是连续的? 
        // 比较稳妥的方式是先查出 id，再 update。但为了原子性，最好直接 update。
        // TP5/6 某些版本 update 不支持 limit，但 MySQL 支持。
        // 这里尝试用 where 配合 order 和 limit 更新。
        
        $recordModel = new LotteryRecord();
        
        // 由于 TP 的 update 默认不支持 limit，我们先查询 ID，然后更新这些 ID
        // 为了防止并发问题，这里其实应该用 update ... limit ... 但 TP ORM 限制。
        // 替代方案：使用原生 SQL 或者 只要并发不高，先查后改 (会有极小概率并发冲突，但 send_code 检查可以避免)
        // 更严谨方案：
        // UPDATE la_lottery_record SET send_code = '$sendCode' WHERE is_send = 0 AND (send_code IS NULL OR send_code = '') LIMIT $limit
        
        $prefix = config('database.connections.mysql.prefix');
        $sql = "UPDATE {$prefix}lottery_record SET send_code = :send_code WHERE is_send = 0 AND (send_code IS NULL OR send_code = '') LIMIT :limit";
        
        try {
            Db::execute($sql, ['send_code' => $sendCode, 'limit' => $limit]);
        } catch (\Exception $e) {
            return ['errcode' => -1, 'errmsg' => '锁定记录失败:' . $e->getMessage()];
        }

        // 3. 查询被当前 send_code 锁定的记录
        $records = LotteryRecord::where('send_code', $sendCode)
            ->where('is_send', 0)
            ->select();

        if ($records->isEmpty()) {
            return ['errcode' => 0, 'errmsg' => '无待发送记录', 'data' => []];
        }

        $results = [
            'total' => count($records),
            'success' => 0,
            'fail' => 0,
            'details' => []
        ];

        // 4. 遍历发送
        foreach ($records as $record) {
            $res = self::sendRedPacket($record->openid, floatval($record->amount));
            
            if ($res['errcode'] == 0) {
                $record->is_send = 1;
                $record->result = '发送成功';
                $record->update_time = time();
                $record->save();
                $results['success']++;
            } else {
                // 发送失败
                // 策略：标记为发送失败，保留 send_code 以便排查，或者清空 send_code 允许重试？
                // 这里暂时保留 send_code 并记录错误信息，避免无限重试死循环
                // 如果需要重试，可以在后台手动重置 send_code
                $record->is_send = 0; // 仍然是未成功
                $record->result = '失败:' . $res['errmsg'];
                // 如果是系统错误，可能需要清空 send_code 让下次任务重试？
                // 暂时不清空，防止某些 bad data 阻塞队列，改为记录 result，下次任务只取 send_code is null 的
                $record->update_time = time();
                $record->save();
                $results['fail']++;
            }
            
            $results['details'][] = [
                'id' => $record->id,
                'openid' => $record->openid,
                'status' => $res['errcode'] == 0 ? 'success' : 'fail',
                'msg' => $res['errmsg'] ?? ''
            ];
        }

        return ['errcode' => 0, 'errmsg' => '执行完成', 'data' => $results];
    }

    /**
     * @notes 获取用户状态（中奖记录、联系人记录）
     * @param $openid
     * @return array
     */
    public static function getUserStatus($openid)
    {
        try {
            // 获取今日起止时间戳
            $todayStart = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
            $todayEnd = mktime(23, 59, 59, date('m'), date('d'), date('Y'));

            // 查找今日最新的中奖记录
            $record = LotteryRecord::where('openid', $openid)
                ->where('is_win', 1)
                ->whereBetween('create_time', [$todayStart, $todayEnd])
                ->order('create_time', 'desc')
                ->find();
            
            // 查找今日最新的联系人记录
            $contact = \app\common\model\marketing\LotteryContact::where('openid', $openid)
                ->whereBetween('create_time', [$todayStart, $todayEnd])
                ->order('create_time', 'desc')
                ->find();

            return [
                'has_win' => !!$record,
                'win_record' => $record,
                'has_contact' => !!$contact,
                'contact_record' => $contact
            ];
        } catch (\Exception $e) {
            // Database error fallback
            return [
                'has_win' => false,
                'win_record' => null,
                'has_contact' => false,
                'contact_record' => null,
                'error' => $e->getMessage()
            ];
        }
    }
}
