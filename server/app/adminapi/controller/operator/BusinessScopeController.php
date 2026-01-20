<?php
namespace app\adminapi\controller\operator;

use app\adminapi\controller\BaseAdminController;
use app\adminapi\logic\operator\BusinessScopeLogic;
use app\adminapi\validate\operator\BusinessScopeValidate;

/**
 * 经营范围控制器
 * Class BusinessScopeController
 * @package app\adminapi\controller\operator
 */
class BusinessScopeController extends BaseAdminController
{
    /**
     * @notes 经营范围列表
     * @return \think\response\Json
     */
    public function lists()
    {
        $params = (new BusinessScopeValidate())->goCheck('lists');
        $result = BusinessScopeLogic::lists($params);
        return $this->success('', $result);
    }

    /**
     * @notes 添加经营范围
     * @return \think\response\Json
     */
    public function add()
    {
        $params = (new BusinessScopeValidate())->post()->goCheck('add');
        BusinessScopeLogic::add($params);
        return $this->success('添加成功', [], 1, 1);
    }

    /**
     * @notes 编辑经营范围
     * @return \think\response\Json
     */
    public function edit()
    {
        $params = (new BusinessScopeValidate())->post()->goCheck('edit');
        $result = BusinessScopeLogic::edit($params);
        if ($result === true) {
            return $this->success('编辑成功', [], 1, 1);
        }
        return $this->fail(BusinessScopeLogic::getError());
    }

    /**
     * @notes 删除经营范围
     * @return \think\response\Json
     */
    public function delete()
    {
        $params = (new BusinessScopeValidate())->post()->goCheck('delete');
        BusinessScopeLogic::delete($params);
        return $this->success('删除成功', [], 1, 1);
    }

    /**
     * @notes 经营范围详情
     * @return \think\response\Json
     */
    public function detail()
    {
        $params = (new BusinessScopeValidate())->goCheck('detail');
        $result = BusinessScopeLogic::detail($params);
        return $this->success('', $result);
    }

    /**
     * @notes 调整状态
     * @return \think\response\Json
     */
    public function status()
    {
        $params = (new BusinessScopeValidate())->post()->goCheck('status');
        $result = BusinessScopeLogic::status($params);
        if ($result === true) {
            return $this->success('状态修改成功', [], 1, 1);
        }
        return $this->fail(BusinessScopeLogic::getError());
    }
}
