<?php
namespace app\common\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\console\input\Option;
use app\api\logic\LotteryLogic;

class SendRedPacket extends Command
{
    protected function configure()
    {
        $this->setName('send_red_packet')
            ->setDescription('批量发送红包任务')
            ->addOption('limit', null, Option::VALUE_OPTIONAL, '每次处理数量', 10)
            ->addOption('reset', null, Option::VALUE_NONE, '重置所有锁定状态');
    }

    protected function execute(Input $input, Output $output)
    {
        if ($input->getOption('reset')) {
            \app\common\model\marketing\LotteryRecord::where('is_send', 0)
                ->update(['send_code' => null]);
            $output->writeln('已重置所有未发送记录的锁定状态');
            return;
        }

        $limit = $input->getOption('limit');
        
        $output->writeln(sprintf('开始执行批量发送红包任务，limit=%d...', $limit));
        
        try {
            $result = LotteryLogic::batchSendRedPacketTask($limit);
            
            if ($result['errcode'] == 0) {
                $output->writeln(sprintf(
                    '任务完成: 总计 %d, 成功 %d, 失败 %d',
                    $result['data']['total'],
                    $result['data']['success'],
                    $result['data']['fail']
                ));
                
                if (!empty($result['data']['details'])) {
                    foreach ($result['data']['details'] as $detail) {
                        if ($detail['status'] !== 'success') {
                            $msg = sprintf(
                                'ID: %d, OpenID: %s, 失败原因: %s',
                                $detail['id'],
                                $detail['openid'],
                                $detail['msg']
                            );
                            $output->writeln(" - " . $msg);
                        }
                    }
                }
            } else {
                $output->writeln('任务执行错误: ' . $result['errmsg']);
                // 抛出异常以便 Crontab 记录错误信息到数据库
                throw new \Exception($result['errmsg']);
            }
        } catch (\Exception $e) {
            $output->writeln('发生异常: ' . $e->getMessage());
            // 继续抛出异常给 Crontab 捕获
            throw $e;
        }
        
        $output->writeln('执行结束');
    }
}
