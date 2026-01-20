<?php
namespace app\adminapi\validate\operator;

use app\common\validate\BaseValidate;

/**
 * 经营范围验证
 * Class BusinessScopeValidate
 * @package app\adminapi\validate\operator
 */
class BusinessScopeValidate extends BaseValidate
{
    protected $rule = [
        'id' => 'require|integer',
        'name' => 'require',
        'status' => 'in:0,1',
        'sort' => 'integer',
        'page_no' => 'integer',
        'page_size' => 'integer',
    ];

    protected $message = [
        'id.require' => '参数错误',
        'id.integer' => '参数错误',
        'name.require' => '请输入名称',
        'status.in' => '状态值错误',
        'status.require' => '请选择状态',
        'sort.integer' => '排序值错误',
        'sort.require' => '请输入排序',
        'page_no.integer' => '页码必须是整数',
        'page_size.integer' => '每页数量必须是整数',
    ];

    /**
     * @notes 添加场景
     */
    public function sceneAdd()
    {
        return $this->only(['name', 'status', 'sort']);
    }

    /**
     * @notes 编辑场景
     */
    public function sceneEdit()
    {
        return $this->only(['id', 'name', 'status', 'sort'])
            ->append('status', 'require')
            ->append('sort', 'require');
    }

    /**
     * @notes 删除场景
     */
    public function sceneDelete()
    {
        return $this->only(['id']);
    }

    /**
     * @notes 详情场景
     */
    public function sceneDetail()
    {
        return $this->only(['id']);
    }

    /**
     * @notes 状态场景
     */
    public function sceneStatus()
    {
        return $this->only(['id', 'status'])
            ->append('status', 'require');
    }

    /**
     * @notes 列表场景
     */
    public function sceneLists()
    {
        return $this->only(['name', 'status', 'page_no', 'page_size'])
            ->remove('name', 'require')
            ->remove('status', 'require');
    }
}
