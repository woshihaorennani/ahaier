<?php
// +----------------------------------------------------------------------
// | 控制台配置
// +----------------------------------------------------------------------
return [
    // 指令定义
    'commands' => [
        // 定时任务
        'crontab' => 'app\common\command\Crontab',
        // 退款查询
        'query_refund' => 'app\common\command\QueryRefund',
        // 批量发送红包
        'send_red_packet' => 'app\common\command\SendRedPacket',
    ],
];
