<?php

namespace app\adminapi\validate\marketing;

use app\common\validate\BaseValidate;

/**
 * 奖品验证器
 * Class LotteryValidate
 * @package app\adminapi\validate\marketing
 */
class LotteryValidate extends BaseValidate
{
    protected $rule = [
        'id' => 'require|integer',
        'name' => 'require',
        'dates' => 'require|date',
        'min' => 'require|float|min:0',
        'max' => 'require|float|min:0',
        'bonuses_pool' => 'require|float|min:0',
        'number_all' => 'require|integer|min:0',
        'can_win' => 'require|integer|min:0',
        'special' => 'integer|min:0',
        'special_number' => 'integer|min:0',
    ];

    protected $message = [
        'id.require' => 'ID不能为空',
        'id.integer' => 'ID必须为整数',
        'name.require' => '奖品名称不能为空',
        'dates.require' => '日期不能为空',
        'dates.date' => '日期格式不正确',
        'min.require' => '最小区间不能为空',
        'min.float' => '最小区间必须为数字',
        'max.require' => '最大区间不能为空',
        'max.float' => '最大区间必须为数字',
        'bonuses_pool.require' => '奖金池不能为空',
        'bonuses_pool.float' => '奖金池必须为数字',
        'number_all.require' => '发放数量不能为空',
        'number_all.integer' => '发放数量必须为整数',
        'can_win.require' => '可中奖次数不能为空',
        'can_win.integer' => '可中奖次数必须为整数',
    ];

    protected $scene = [
        'add' => ['name', 'dates', 'min', 'max', 'bonuses_pool', 'number_all', 'can_win', 'special', 'special_number'],
        'edit' => ['id', 'name', 'dates', 'min', 'max', 'bonuses_pool', 'number_all', 'can_win', 'special', 'special_number'],
        'delete' => ['id'],
        'detail' => ['id'],
    ];
}
