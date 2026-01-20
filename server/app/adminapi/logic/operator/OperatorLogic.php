<?php
namespace app\adminapi\logic\operator;

use app\common\logic\BaseLogic;
use app\common\model\operator\Operator;

/**
 * 运营商管理逻辑
 * Class OperatorLogic
 * @package app\adminapi\logic\operator
 */
class OperatorLogic extends BaseLogic
{
    /**
     * @notes 运营商列表
     * @param $params
     * @return array
     */
    public static function lists($params)
    {
        $where = [];
        if (!empty($params['keyword'])) {
            $where[] = ['name|contact|phone|email|address|scope|province|city|district', 'like', '%' . $params['keyword'] . '%'];
        }
        if (!empty($params['name'])) {
            $where[] = ['name', 'like', '%' . $params['name'] . '%'];
        }
        if (!empty($params['contact'])) {
            $where[] = ['contact', 'like', '%' . $params['contact'] . '%'];
        }
        if (!empty($params['phone'])) {
            $where[] = ['phone', 'like', '%' . $params['phone'] . '%'];
        }

        $pageNo = $params['page_no'] ?? 1;
        $pageSize = $params['page_size'] ?? 15;

        $count = Operator::where($where)->count();
        $lists = Operator::where($where)
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
     * @notes 添加运营商
     * @param $params
     */
    public static function add($params)
    {
        Operator::create([
            'name' => $params['name'],
            'contact' => $params['contact'],
            'phone' => $params['phone'],
            'email' => $params['email'] ?? '',
            'province' => $params['province'],
            'city' => $params['city'],
            'district' => $params['district'],
            'address' => $params['address'],
            'scope' => $params['scope'] ?? '',
            'status' => $params['status'],
            'sort' => $params['sort'] ?? 0,
            'create_time' => time(),
            'update_time' => time(),
        ]);
    }

    /**
     * @notes 编辑运营商
     * @param $params
     */
    public static function edit($params)
    {
        $operator = Operator::findOrEmpty($params['id']);
        if ($operator->isEmpty()) {
            self::setError('运营商不存在');
            return false;
        }

        $operator->save([
            'name' => $params['name'],
            'contact' => $params['contact'],
            'phone' => $params['phone'],
            'email' => $params['email'] ?? '',
            'province' => $params['province'],
            'city' => $params['city'],
            'district' => $params['district'],
            'address' => $params['address'],
            'scope' => $params['scope'] ?? '',
            'status' => $params['status'],
            'sort' => $params['sort'] ?? 0,
            'update_time' => time(),
        ]);
        return true;
    }

    /**
     * @notes 删除运营商
     * @param $params
     */
    public static function delete($params)
    {
        Operator::destroy($params['id']);
    }

    /**
     * @notes 获取运营商详情
     * @param $params
     * @return array
     */
    public static function detail($params)
    {
        return Operator::findOrEmpty($params['id'])->toArray();
    }

    /**
     * @notes 调整状态
     * @param $params
     */
    public static function status($params)
    {
        $operator = Operator::findOrEmpty($params['id']);
        if ($operator->isEmpty()) {
            self::setError('运营商不存在');
            return false;
        }

        $operator->status = $params['status'];
        $operator->save();
        return true;
    }
}
