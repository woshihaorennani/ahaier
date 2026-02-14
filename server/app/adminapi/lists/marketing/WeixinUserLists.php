<?php

namespace app\adminapi\lists\marketing;

use app\adminapi\lists\BaseAdminDataLists;
use app\common\lists\ListsSearchInterface;
use app\common\lists\ListsSortInterface;
use app\common\lists\ListsExcelInterface;
use app\common\model\marketing\WeixinUser;

class WeixinUserLists extends BaseAdminDataLists implements ListsSearchInterface, ListsSortInterface, ListsExcelInterface
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
        $where = $this->searchWhere;
        $this->appendIsFromWhere($where);

        return WeixinUser::where($where)
            ->limit($this->limitOffset, $this->limitLength)
            ->order($this->sortOrder)
            ->select()
            ->toArray();
    }

    public function count(): int
    {
        $where = $this->searchWhere;
        $this->appendIsFromWhere($where);

        return WeixinUser::where($where)->count();
    }

    private function appendIsFromWhere(&$where)
    {
        if (isset($this->params['is_from']) && $this->params['is_from'] !== '') {
            if ($this->params['is_from'] == 1) {
                $where[] = ['is_from', '>', 0];
            } else {
                $where[] = ['is_from', 'null', ''];
            }
        }
    }

    public function setExcelFields(): array
    {
        return [
            'id' => 'ID',
            'avatar' => '头像',
            'nickname' => '昵称',
            'openid' => 'OpenID',
            'sex' => '性别',
            'country' => '国家',
            'province' => '省份',
            'city' => '城市',
            'subscribe_scene' => '订阅来源',
            'subscribe_time' => '订阅时间',
            'update_time' => '更新时间',
            'create_time' => '创建时间',
        ];
    }

    public function setFileName(): string
    {
        return '微信用户列表';
    }
}
