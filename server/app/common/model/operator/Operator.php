<?php
namespace app\common\model\operator;

use app\common\model\BaseModel;
use think\model\concern\SoftDelete;

/**
 * 运营商模型
 * Class Operator
 * @package app\common\model\operator
 */
class Operator extends BaseModel
{
    use SoftDelete;

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
