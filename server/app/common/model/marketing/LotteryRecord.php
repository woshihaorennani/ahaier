<?php

namespace app\common\model\marketing;

use app\common\model\BaseModel;
use think\model\concern\SoftDelete;

/**
 * 抽奖记录模型
 * Class LotteryRecord
 * @package app\common\model\marketing
 */
class LotteryRecord extends BaseModel
{
    use SoftDelete;

    protected $name = 'lottery_record';
    protected $deleteTime = 'delete_time';
}
