<?php
namespace app\adminapi\logic\operator;

use app\common\logic\BaseLogic;
use app\common\model\operator\BusinessScope;

/**
 * 经营范围逻辑
 * Class BusinessScopeLogic
 * @package app\adminapi\logic\operator
 */
class BusinessScopeLogic extends BaseLogic
{
    /**
     * @notes 经营范围列表
     * @param $params
     * @return array
     */
    public static function lists($params)
    {
        $where = [];
        if (!empty($params['name'])) {
            $where[] = ['name', 'like', '%' . $params['name'] . '%'];
        }
        if (isset($params['status']) && $params['status'] !== '') {
            $where[] = ['status', '=', $params['status']];
        }

        $pageNo = $params['page_no'] ?? 1;
        $pageSize = $params['page_size'] ?? 15;

        $count = BusinessScope::where($where)->count();
        $lists = BusinessScope::where($where)
            ->page($pageNo, $pageSize)
            ->order(['sort' => 'desc', 'id' => 'desc'])
            ->select()
            ->toArray();

        return [
            'count' => $count,
            'lists' => $lists,
            'page_no' => $pageNo,
            'page_size' => $pageSize,
        ];
    }

    /**
     * @notes 添加经营范围
     * @param $params
     */
    public static function add($params)
    {
        BusinessScope::create([
            'name' => $params['name'],
            'status' => $params['status'] ?? 1,
            'sort' => $params['sort'] ?? 0
        ]);
    }

    /**
     * @notes 编辑经营范围
     * @param $params
     * @return bool
     */
    public static function edit($params)
    {
        $model = BusinessScope::findOrEmpty($params['id']);
        if ($model->isEmpty()) {
            self::setError('经营范围不存在');
            return false;
        }

        $model->save([
            'name' => $params['name'],
            'status' => $params['status'],
            'sort' => $params['sort']
        ]);
        return true;
    }

    /**
     * @notes 删除经营范围
     * @param $params
     */
    public static function delete($params)
    {
        BusinessScope::destroy($params['id']);
    }

    /**
     * @notes 获取经营范围详情
     * @param $params
     * @return array
     */
    public static function detail($params)
    {
        return BusinessScope::findOrEmpty($params['id'])->toArray();
    }

    /**
     * @notes 调整状态
     * @param $params
     * @return bool
     */
    public static function status($params)
    {
        $model = BusinessScope::findOrEmpty($params['id']);
        if ($model->isEmpty()) {
            self::setError('经营范围不存在');
            return false;
        }

        $model->status = $params['status'];
        $model->save();
        return true;
    }
}
