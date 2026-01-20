<?php
namespace app\api\controller;

use app\common\model\operator\Operator;

/**
 * 运营商控制器
 * Class OperatorController
 * @package app\api\controller
 */
class OperatorController extends BaseApiController
{
    public array $notNeedLogin = ['all'];

    /**
     * @notes 查询全部运营商数据
     * @return \think\response\Json
     */
    public function all()
    {
        // 查询状态正常的运营商，按排序降序排列
        $result = Operator::where('status', 1)
            ->order(['sort' => 'desc', 'id' => 'desc'])
            ->select()
            ->toArray();
            
        return $this->data($result);
    }
}
