<?php
namespace app\adminapi\controller\operator;

use app\adminapi\controller\BaseAdminController;
use app\adminapi\logic\operator\OperatorLogic;
use app\adminapi\validate\operator\OperatorValidate;

/**
 * 运营商管理控制器
 * Class OperatorController
 * @package app\adminapi\controller\operator
 */
class OperatorController extends BaseAdminController
{
    /**
     * @notes 运营商列表
     * @return \think\response\Json
     */
    public function lists()
    {
        $params = (new OperatorValidate())->goCheck('lists');
        $result = OperatorLogic::lists($params);
        return $this->success('', $result);
    }

    /**
     * @notes 添加运营商
     * @return \think\response\Json
     */
    public function add()
    {
        $params = (new OperatorValidate())->post()->goCheck('add');
        OperatorLogic::add($params);
        return $this->success('添加成功', [], 1, 1);
    }

    /**
     * @notes 编辑运营商
     * @return \think\response\Json
     */
    public function edit()
    {
        $params = (new OperatorValidate())->post()->goCheck('edit');
        $result = OperatorLogic::edit($params);
        if ($result === true) {
            return $this->success('编辑成功', [], 1, 1);
        }
        return $this->fail(OperatorLogic::getError());
    }

    /**
     * @notes 删除运营商
     * @return \think\response\Json
     */
    public function delete()
    {
        $params = (new OperatorValidate())->post()->goCheck('delete');
        OperatorLogic::delete($params);
        return $this->success('删除成功', [], 1, 1);
    }

    /**
     * @notes 运营商详情
     * @return \think\response\Json
     */
    public function detail()
    {
        $params = (new OperatorValidate())->goCheck('detail');
        $result = OperatorLogic::detail($params);
        return $this->success('', $result);
    }

    /**
     * @notes 调整状态
     * @return \think\response\Json
     */
    public function status()
    {
        $params = (new OperatorValidate())->post()->goCheck('status');
        $result = OperatorLogic::status($params);
        if ($result === true) {
            return $this->success('状态修改成功', [], 1, 1);
        }
        return $this->fail(OperatorLogic::getError());
    }
}
