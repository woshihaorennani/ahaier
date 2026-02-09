<?php
namespace app\api\controller;

use app\api\logic\LotteryLogic;

class LotteryController extends BaseApiController
{
    public array $notNeedLogin = ['draw', 'submitContact', 'initTable', 'batchSendRedPacketTask', 'batch_send_red_packet_task'];

    /**
     * @notes 定时任务：批量发送红包
     * @return \think\response\Json
     */
    public function batchSendRedPacketTask()
    {
        try {
            // 简单鉴权，防止恶意调用 (实际项目中建议配置在 Crontab 中，限制 IP 或 Token)
            // 这里假设是内部调用，通过密钥验证
            $key = $this->request->get('key');
            if ($key !== 'cron_secure_key_123') { // 简易密钥
                return $this->fail('Unauthorized');
            }

            $limit = $this->request->get('limit', 10);
            $result = LotteryLogic::batchSendRedPacketTask($limit);
            
            return $this->data($result);
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * @notes 兼容下划线访问
     */
    public function batch_send_red_packet_task()
    {
        return $this->batchSendRedPacketTask();
    }

    /**
     * @notes 抽奖
     * @return \think\response\Json
     */
    public function draw()
    {
        $openid = $this->request->post('openid');
        if (empty($openid)) {
            return $this->fail('openid不能为空');
        }
        $result = LotteryLogic::draw($openid);
        if ($result === false) {
            // 如果逻辑层返回false，可能是没有奖品或者其他错误
            // 这里假设 LotteryLogic::getError() 会返回具体错误，或者默认为未中奖
            $msg = LotteryLogic::getError() ?: '未中奖';
            // 如果是业务上的“未中奖”，通常也会返回成功数据但标记为未中奖
            // 但为了简单，如果 Logic 返回 false，我们认为就是没中或者出错
            // 具体看 Logic 实现
            return $this->fail($msg); 
        }
        return $this->data($result);
    }

    /**
     * @notes 提交联系信息
     */
    public function submitContact()
    {
        $params = $this->request->post();
        // Simple validation
        if (empty($params['name']) || empty($params['phone'])) {
             return $this->fail('请完善信息');
        }

        $result = LotteryLogic::submitContact($params);
        if ($result === false) {
             return $this->fail(LotteryLogic::getError() ?: '提交失败');
        }
        return $this->success('提交成功');
    }

    public function initTable()
    {
        try {
            $prefix = config('database.connections.mysql.prefix');
            $sql = "CREATE TABLE IF NOT EXISTS `{$prefix}lottery_contact` (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `openid` varchar(64) DEFAULT NULL COMMENT 'OpenID',
              `name` varchar(50) NOT NULL DEFAULT '' COMMENT '姓名',
              `phone` varchar(20) NOT NULL DEFAULT '' COMMENT '电话',
              `business` varchar(50) NOT NULL DEFAULT '' COMMENT '业务',
              `region` varchar(100) NOT NULL DEFAULT '' COMMENT '区域',
              `request` text COMMENT '需求',
              `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
              `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
              `delete_time` int(10) unsigned DEFAULT NULL COMMENT '删除时间',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='抽奖联系人表';";
            
            \think\facade\Db::execute($sql);

            $sql2 = "CREATE TABLE IF NOT EXISTS `{$prefix}lottery_log` (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `openid` varchar(64) DEFAULT NULL COMMENT 'OpenID',
              `money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '红包金额',
              `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态:1成功,0失败',
              `message` varchar(255) NOT NULL DEFAULT '' COMMENT '提示信息',
              `params` text COMMENT '请求参数',
              `result` text COMMENT '返回结果',
              `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='红包发放日志表';";
            \think\facade\Db::execute($sql2);

            return $this->success('Table created');
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }
}
