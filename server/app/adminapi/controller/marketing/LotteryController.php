<?php

namespace app\adminapi\controller\marketing;

use app\adminapi\controller\BaseAdminController;
use app\adminapi\lists\marketing\LotteryLists;
use app\adminapi\lists\marketing\LotteryContactLists;
use app\adminapi\logic\marketing\LotteryLogic;
use app\adminapi\validate\marketing\LotteryValidate;

/**
 * 奖品控制器
 * Class LotteryController
 * @package app\adminapi\controller\marketing
 */
class LotteryController extends BaseAdminController
{
    /**
     * @notes 奖品列表
     * @return \think\response\Json
     */
    public function lists()
    {
        return $this->dataLists(new LotteryLists());
    }

    /**
     * @notes 添加奖品
     * @return \think\response\Json
     */
    public function add()
    {
        $params = (new LotteryValidate())->post()->goCheck('add');
        $result = LotteryLogic::add($params);
        if ($result === true) {
            return $this->success('添加成功', [], 1, 1);
        }
        return $this->fail(LotteryLogic::getError());
    }

    /**
     * @notes 编辑奖品
     * @return \think\response\Json
     */
    public function edit()
    {
        $params = (new LotteryValidate())->post()->goCheck('edit');
        $result = LotteryLogic::edit($params);
        if ($result === true) {
            return $this->success('编辑成功', [], 1, 1);
        }
        return $this->fail(LotteryLogic::getError());
    }

    /**
     * @notes 删除奖品
     * @return \think\response\Json
     */
    public function delete()
    {
        $params = (new LotteryValidate())->post()->goCheck('delete');
        LotteryLogic::delete($params);
        return $this->success('删除成功', [], 1, 1);
    }

    /**
     * @notes 奖品详情
     * @return \think\response\Json
     */
    public function detail()
    {
        $params = (new LotteryValidate())->goCheck('detail');
        $result = LotteryLogic::detail($params['id']);
        return $this->success('获取成功', $result);
    }

    /**
     * @notes 联系人列表
     * @return \think\response\Json
     */
    public function contactLists()
    {
        return $this->dataLists(new LotteryContactLists());
    }
}
