<?php
// +----------------------------------------------------------------------
// | likeadmin快速开发前后端分离管理后台（PHP版）
// +----------------------------------------------------------------------
// | 欢迎阅读学习系统程序代码，建议反馈是我们前进的动力
// | 开源版本可自由商用，可去除界面版权logo
// | gitee下载：https://gitee.com/likeshop_gitee/likeadmin
// | github下载：https://github.com/likeshop-github/likeadmin
// | 访问官网：https://www.likeadmin.cn
// | likeadmin团队 版权所有 拥有最终解释权
// +----------------------------------------------------------------------
// | author: likeadminTeam
// +----------------------------------------------------------------------

namespace app\api\controller;

use app\api\logic\WechatLogic;
use app\api\validate\WechatValidate;


/**
 * 微信
 * Class WechatController
 * @package app\api\controller
 */
class WechatController extends BaseApiController
{
    public array $notNeedLogin = ['jsConfig', 'silentAuth', 'silentCodeUrl', 'getAuthDomain', 'syncUser'];


    /**
     * @notes 微信JSSDK授权接口
     * @return mixed
     * @author 段誉
     * @date 2023/3/1 11:39
     */
    public function jsConfig()
    {
        $params = (new WechatValidate())->goCheck('jsConfig');
        $result = WechatLogic::jsConfig($params);
        if ($result === false) {
            return $this->fail(WechatLogic::getError(), [], 0, 0);
        }
        return $this->data($result);
    }

    /**
     * @notes 订阅号静默授权，获取openid并返回用户数据
     * @return \think\response\Json
     */
    public function silentAuth()
    {
        $params = (new WechatValidate())->goCheck('silentAuth');
        $code = $params['code'];
        $result = WechatLogic::silentAuth($code);
        if ($result === false) {
            return $this->fail(WechatLogic::getError(), [], 0, 0);
        }
        return $this->data($result);
    }

    /**
     * @notes 获取静默授权跳转url
     * @return \think\response\Json
     */
    public function silentCodeUrl()
    {
        $params = (new WechatValidate())->goCheck('silentCodeUrl');
        $url = $params['url'];
        $result = ['url' => WechatLogic::silentCodeUrl($url)];
        return $this->success('获取成功', $result);
    }

    /**
     * @notes 获取授权域名
     * @return \think\response\Json
     */
    public function getAuthDomain()
    {
        $uid = $this->request->get('uid', '10815991');
        $result = WechatLogic::getAuthDomain($uid);
        if ($result === false) {
            return $this->fail(WechatLogic::getError());
        }
        return $this->data($result);
    }

    /**
     * @notes 同步用户
     * @return \think\response\Json
     */
    public function syncUser()
    {
        $openid = $this->request->post('openid');
        if (empty($openid)) {
            return $this->fail('openid不能为空');
        }
        $is_from = $this->request->post('is_from');
        $result = WechatLogic::syncUser($openid, $is_from);
        if ($result === false) {
            return $this->fail(WechatLogic::getError());
        }
        return $this->success('同步成功');
    }
}
