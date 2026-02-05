<?php
namespace app\api\controller;

use app\api\logic\LotteryLogic;
use app\common\controller\BaseLikeAdminController;

class LotteryController extends BaseLikeAdminController
{
    public array $notNeedLogin = ['draw'];

    /**
     * @notes 抽奖
     * @return \think\response\Json
     */
    public function draw()
    {
        $openid = $this->request->post('openid');
        if (empty($openid)) {
            return $this->fail('openid不能为空');
        }
        $result = LotteryLogic::draw($openid);
        if ($result === false) {
            // 如果逻辑层返回false，可能是没有奖品或者其他错误
            // 这里假设 LotteryLogic::getError() 会返回具体错误，或者默认为未中奖
            $msg = LotteryLogic::getError() ?: '未中奖';
            // 如果是业务上的“未中奖”，通常也会返回成功数据但标记为未中奖
            // 但为了简单，如果 Logic 返回 false，我们认为就是没中或者出错
            // 具体看 Logic 实现
            return $this->fail($msg); 
        }
        return $this->data($result);
    }
}
