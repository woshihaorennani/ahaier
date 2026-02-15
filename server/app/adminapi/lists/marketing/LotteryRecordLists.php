<?php

namespace app\adminapi\lists\marketing;

use app\adminapi\lists\BaseAdminDataLists;
use app\common\lists\ListsSearchInterface;
use app\common\lists\ListsSortInterface;
use app\common\lists\ListsExcelInterface;
use app\common\model\marketing\LotteryRecord;

/**
 * 抽奖记录列表
 * Class LotteryRecordLists
 * @package app\adminapi\lists\marketing
 */
class LotteryRecordLists extends BaseAdminDataLists implements ListsSearchInterface, ListsSortInterface, ListsExcelInterface
{
    /**
     * @notes 设置搜索条件
     * @return array
     */
    public function setSearch(): array
    {
        return [
            '%like%' => ['nickname', 'prize_name', 'openid'],
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

    /**
     * @notes 导出文件名
     * @return string
     */
    public function setFileName(): string
    {
        return '抽奖记录列表';
    }

    /**
     * @notes 导出字段
     * @return array
     */
    public function setExcelFields(): array
    {
        return [
            'id' => 'ID',
            'lottery_id' => '奖品ID',
            'is_win' => '是否中奖',
            'amount' => '金额',
            'prize_name' => '奖品名称',
            'create_time' => '创建时间',
            'update_time' => '更新时间',
        ];
    }
}
