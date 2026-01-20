<?php
namespace app\common\model\operator;

use app\common\model\BaseModel;
use think\model\concern\SoftDelete;

/**
 * 经营范围模型
 * Class BusinessScope
 * @package app\common\model\operator
 */
class BusinessScope extends BaseModel
{
    use SoftDelete;

    protected $name = 'business_scope';

    protected $deleteTime = 'delete_time';

    /**
     * @notes 状态描述
     * @param $value
     * @param $data
     * @return string
     */
    public function getStatusDescAttr($value, $data)
    {
        return $data['status'] ? '正常' : '停用';
    }
}
