<?php

namespace app\adminapi\lists\marketing;

use app\adminapi\lists\BaseAdminDataLists;
use app\common\lists\ListsSearchInterface;
use app\common\model\marketing\Lottery;
use app\common\model\marketing\LotteryRecord;

/**
 * 奖品列表
 * Class LotteryLists
 * @package app\adminapi\lists\marketing
 */
class LotteryLists extends BaseAdminDataLists implements ListsSearchInterface
{
    /**
     * @notes 设置搜索条件
     * @return array
     */
    public function setSearch(): array
    {
        return [
            '%like%' => ['name'],
            '=' => ['dates']
        ];
    }

    /**
     * @notes 获取列表
     * @return array
     */
    public function lists(): array
    {
        $lists = Lottery::where($this->searchWhere)
            ->limit($this->limitOffset, $this->limitLength)
            ->order(['id' => 'desc'])
            ->select()
            ->toArray();

        foreach ($lists as &$item) {
            $item['distributed_amount'] = round(LotteryRecord::where('lottery_id', $item['id'])
                ->where('is_win', 1)
                ->sum('amount'), 2);
            // Ensure bonuses_pool is present (it should be if in DB, but just in case)
            if (!isset($item['bonuses_pool'])) {
                $item['bonuses_pool'] = 0; // Default or fetch if field name is different
            }
        }
        return $lists;
    }

    /**
     * @notes 获取数量
     * @return int
     */
    public function count(): int
    {
        return Lottery::where($this->searchWhere)->count();
    }
}
