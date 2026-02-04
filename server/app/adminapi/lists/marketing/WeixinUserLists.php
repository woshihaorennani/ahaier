<?php

namespace app\adminapi\lists\marketing;

use app\adminapi\lists\BaseAdminDataLists;
use app\common\lists\ListsSearchInterface;
use app\common\lists\ListsSortInterface;
use app\common\model\marketing\WeixinUser;

class WeixinUserLists extends BaseAdminDataLists implements ListsSearchInterface, ListsSortInterface
{
    public function setSearch(): array
    {
        return [
            '%like%' => ['nickname', 'openid', 'unionid', 'country', 'province', 'city'],
        ];
    }

    public function setSortFields(): array
    {
        return ['subscribe_time' => 'subscribe_time', 'create_time' => 'create_time', 'id' => 'id'];
    }

    public function setDefaultOrder(): array
    {
        return ['id' => 'desc'];
    }

    public function lists(): array
    {
        return WeixinUser::where($this->searchWhere)
            ->limit($this->limitOffset, $this->limitLength)
            ->order($this->sortOrder)
            ->select()
            ->toArray();
    }

    public function count(): int
    {
        return WeixinUser::where($this->searchWhere)->count();
    }
}
