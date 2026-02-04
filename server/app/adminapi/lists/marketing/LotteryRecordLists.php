<?php

namespace app\adminapi\lists\marketing;

use app\adminapi\lists\BaseAdminDataLists;
use app\common\lists\ListsSearchInterface;
use app\common\lists\ListsSortInterface;
use app\common\model\marketing\LotteryRecord;

/**
 * 抽奖记录列表
 * Class LotteryRecordLists
 * @package app\adminapi\lists\marketing
 */
class LotteryRecordLists extends BaseAdminDataLists implements ListsSearchInterface, ListsSortInterface
{
    /**
     * @notes 设置搜索条件
     * @return array
     */
    public function setSearch(): array
    {
        return [
            '%like%' => ['nickname', 'prize_name'],
        ];
    }

    /**
     * @notes 设置支持排序字段
     * @return array
     */
    public function setSortFields(): array
    {
        return ['create_time' => 'create_time', 'id' => 'id'];
    }

    /**
     * @notes 设置默认排序
     * @return array
     */
    public function setDefaultOrder(): array
    {
        return ['id' => 'desc'];
    }

    /**
     * @notes 获取列表
     * @return array
     */
    public function lists(): array
    {
        return LotteryRecord::where($this->searchWhere)
            ->limit($this->limitOffset, $this->limitLength)
            ->order($this->sortOrder)
            ->select()
            ->toArray();
    }

    /**
     * @notes 获取数量
     * @return int
     */
    public function count(): int
    {
        return LotteryRecord::where($this->searchWhere)->count();
    }
}
