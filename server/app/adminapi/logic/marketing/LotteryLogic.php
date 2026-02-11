<?php

namespace app\adminapi\logic\marketing;

use app\common\logic\BaseLogic;
use app\common\model\marketing\Lottery;
use app\common\service\FileService;

/**
 * 奖品逻辑
 * Class LotteryLogic
 * @package app\adminapi\logic\marketing
 */
class LotteryLogic extends BaseLogic
{
    /**
     * @notes 添加奖品
     * @param array $params
     * @return bool
     */
    public static function add(array $params)
    {
        try {
            Lottery::create([
                'name' => $params['name'],
                'dates' => $params['dates'],
                'min' => $params['min'] ?? 0,
                'max' => $params['max'] ?? 0,
                'bonuses_pool' => $params['bonuses_pool'] ?? 0,
                'number_all' => $params['number_all'] ?? 0,
                'can_win' => $params['can_win'] ?? 1,
                'special' => $params['special'] ?? 0,
                'special_number' => $params['special_number'] ?? 0,
                'create_time' => time(),
                'update_time' => time(),
            ]);
            return true;
        } catch (\Exception $e) {
            self::setError($e->getMessage());
            return false;
        }
    }

    /**
     * @notes 编辑奖品
     * @param array $params
     * @return bool
     */
    public static function edit(array $params)
    {
        try {
            Lottery::update([
                'id' => $params['id'],
                'name' => $params['name'],
                'dates' => $params['dates'],
                'min' => $params['min'] ?? 0,
                'max' => $params['max'] ?? 0,
                'bonuses_pool' => $params['bonuses_pool'] ?? 0,
                'number_all' => $params['number_all'] ?? 0,
                'can_win' => $params['can_win'] ?? 1,
                'special' => $params['special'] ?? 0,
                'special_number' => $params['special_number'] ?? 0,
                'update_time' => time(),
            ]);
            return true;
        } catch (\Exception $e) {
            self::setError($e->getMessage());
            return false;
        }
    }

    /**
     * @notes 删除奖品
     * @param array $params
     */
    public static function delete(array $params)
    {
        Lottery::destroy($params['id']);
    }

    /**
     * @notes 获取详情
     * @param int $id
     * @return array
     */
    public static function detail(int $id)
    {
        return Lottery::findOrEmpty($id)->toArray();
    }
}
