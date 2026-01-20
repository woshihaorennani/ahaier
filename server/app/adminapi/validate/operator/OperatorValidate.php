<?php
namespace app\adminapi\validate\operator;

use app\common\model\operator\Operator;
use app\common\validate\BaseValidate;

/**
 * 运营商验证器
 * Class OperatorValidate
 * @package app\adminapi\validate\operator
 */
class OperatorValidate extends BaseValidate
{
    protected $rule = [
        'id' => 'require|integer',
        'name' => 'require|length:1,50',
        'contact' => 'require|length:1,30',
        'phone' => 'require',
        'email' => 'email',
        'province' => 'require',
        'city' => 'require',
        'district' => 'require',
        'address' => 'require',
        'status' => 'require|in:0,1',
        'sort' => 'integer|egt:0',
        'page_no' => 'integer',
        'page_size' => 'integer',
    ];

    protected $message = [
        'id.require' => '参数缺失',
        'id.integer' => '参数错误',
        'name.require' => '请填写运营商名称',
        'name.length' => '运营商名称长度须在1-50位字符',
        'contact.require' => '请填写联系人',
        'contact.length' => '联系人长度须在1-30位字符',
        'phone.require' => '请填写联系电话',
        'email.email' => '邮箱格式错误',
        'province.require' => '请选择省份',
        'city.require' => '请选择城市',
        'district.require' => '请选择区/县',
        'address.require' => '请填写公司地址',
        'status.require' => '请选择状态',
        'status.in' => '状态值错误',
        'sort.integer' => '排序必须是整数',
        'sort.egt' => '排序值不正确',
        'page_no.integer' => '页码必须是整数',
        'page_size.integer' => '每页数量必须是整数',
    ];

    /**
     * @notes 添加场景
     */
    public function sceneAdd()
    {
        return $this->remove('id', true);
    }

    /**
     * @notes 编辑场景
     */
    public function sceneEdit()
    {
        return $this->only(['id', 'name', 'contact', 'phone', 'email', 'province', 'city', 'district', 'address', 'scope', 'status', 'sort']);
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
        return $this->only(['id', 'status']);
    }

    /**
     * @notes 列表场景
     */
    public function sceneLists()
    {
        return $this->only(['page_no', 'page_size', 'name', 'contact', 'phone', 'status'])
            ->remove('name', 'require')
            ->remove('contact', 'require')
            ->remove('phone', 'require')
            ->remove('status', 'require');
    }
}
