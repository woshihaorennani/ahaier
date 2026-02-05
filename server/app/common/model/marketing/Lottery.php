<?php

namespace app\common\model\marketing;

use app\common\model\BaseModel;
use think\model\concern\SoftDelete;

/**
 * 奖品模型
 * Class Lottery
 * @package app\common\model\marketing
 */
class Lottery extends BaseModel
{
    use SoftDelete;

    protected $name = 'lottery';
    protected $deleteTime = 'delete_time';
}
