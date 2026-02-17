<?php
namespace app\adminapi\lists\marketing;

use app\adminapi\lists\BaseAdminDataLists;
use app\common\lists\ListsExcelInterface;
use app\common\model\marketing\LotteryContact;

/**
 * 抽奖联系人列表
 * Class LotteryContactLists
 * @package app\adminapi\lists\marketing
 */
class LotteryContactLists extends BaseAdminDataLists implements ListsExcelInterface
{
    /**
     * @notes 设置搜索条件
     * @return array
     */
    public function setSearch(): array
    {
        return [
            '%like%' => ['name', 'phone', 'region'],
            '=' => ['business']
        ];
    }

    /**
     * @notes 获取列表
     * @return array
     */
    public function lists(): array
    {
        // 获取去重后的最新记录ID列表
        $ids = LotteryContact::where($this->searchWhere)
            ->group('openid')
            ->field('max(id) as max_id')
            ->order('max_id', 'desc')
            ->limit($this->limitOffset, $this->limitLength)
            ->select()
            ->column('max_id');

        if (empty($ids)) {
            return [];
        }

        // 根据ID获取详细信息
        return LotteryContact::where('id', 'in', $ids)
            ->order(['id' => 'desc'])
            ->select()
            ->toArray();
    }

    /**
     * @notes 获取数量
     * @return int
     */
    public function count(): int
    {
        return LotteryContact::where($this->searchWhere)->count('DISTINCT openid');
    }

    /**
     * @notes 导出文件名
     * @return string
     */
    public function setFileName(): string
    {
        return '抽奖联系人列表';
    }

    /**
     * @notes 导出字段
     * @return array
     */
    public function setExcelFields(): array
    {
        return [
            'name' => '姓名',
            'phone' => '电话',
            'business' => '业务',
            'region' => '所在地区',
            'request' => '需求',
            'create_time' => '提交时间',
        ];
    }
}
