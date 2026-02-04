<?php

namespace app\common\model\marketing;

use app\common\model\BaseModel;
use think\model\concern\SoftDelete;

class WeixinUser extends BaseModel
{
    use SoftDelete;

    protected $name = 'weixin_user';
    protected $deleteTime = 'delete_time';
}
