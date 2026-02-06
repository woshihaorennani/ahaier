<?php
namespace app\common\model\marketing;

use app\common\model\BaseModel;
use think\model\concern\SoftDelete;

class LotteryContact extends BaseModel
{
    use SoftDelete;

    protected $name = 'lottery_contact';
    protected $deleteTime = 'delete_time';
}
