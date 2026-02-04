<?php

namespace app\adminapi\controller\marketing;

use app\adminapi\controller\BaseAdminController;
use app\adminapi\lists\marketing\LotteryRecordLists;

/**
 * 抽奖记录控制器
 * Class LotteryRecordController
 * @package app\adminapi\controller\marketing
 */
class LotteryRecordController extends BaseAdminController
{
    /**
     * @notes 抽奖记录列表
     * @return \think\response\Json
     */
    public function lists()
    {
        return $this->dataLists(new LotteryRecordLists());
    }
}
