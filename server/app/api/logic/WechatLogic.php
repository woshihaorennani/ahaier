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

namespace app\api\logic;

use app\common\logic\BaseLogic;
use app\common\service\wechat\WeChatOaService;
use EasyWeChat\Kernel\Exceptions\Exception;

/**
 * 微信
 * Class WechatLogic
 * @package app\api\logic
 */
class WechatLogic extends BaseLogic
{

    /**
     * @notes 微信JSSDK授权接口
     * @param $params
     * @return false|mixed[]
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     * @author 段誉
     * @date 2023/3/1 11:49
     */
    public static function jsConfig($params)
    {
        try {
            $url = urldecode($params['url']);
            return (new WeChatOaService())->getJsConfig($url, [
                'onMenuShareTimeline',
                'onMenuShareAppMessage',
                'onMenuShareQQ',
                'onMenuShareWeibo',
                'onMenuShareQZone',
                'openLocation',
                'getLocation',
                'chooseWXPay',
                'updateAppMessageShareData',
                'updateTimelineShareData',
                'openAddress',
                'scanQRCode'
            ]);
        } catch (Exception $e) {
            self::setError('获取jssdk失败:' . $e->getMessage());
            return false;
        }
    }

    /**
     * @notes 订阅号静默授权，获取openid并回填用户数据
     * @param string $code
     * @return false|array
     */
    public static function silentAuth(string $code)
    {
        try {
            $res = (new WeChatOaService())->getOpenidByCode($code);
            $openid = $res['openid'];
            $unionid = $res['unionid'] ?? '';
            $user = \app\common\model\marketing\WeixinUser::where('openid', $openid)->find();
            if (empty($user)) {
                \app\common\model\marketing\WeixinUser::create([
                    'openid' => $openid,
                    'unionid' => $unionid,
                    'nickname' => '',
                    'avatar' => '',
                    'sex' => 0,
                    'country' => '',
                    'province' => '',
                    'city' => '',
                    'subscribe_scene' => '',
                    'subscribe_time' => 0,
                    'status' => 1,
                    'create_time' => time(),
                    'update_time' => time(),
                ]);
                $user = \app\common\model\marketing\WeixinUser::where('openid', $openid)->find();
            }
            return $user?->toArray() ?? [];
        } catch (Exception $e) {
            self::setError('静默授权失败:' . $e->getMessage());
            return false;
        }
    }

    /**
     * @notes 获取静默授权跳转url
     * @param string $url
     * @return string
     */
    public static function silentCodeUrl(string $url)
    {
        return (new WeChatOaService())->getSilentCodeUrl($url);
    }

    /**
     * @notes 获取授权域名
     * @param string $uid
     * @return mixed
     */
    public static function getAuthDomain(string $uid)
    {
        try {
            $url = "https://www.yaoyaola.net/exapi/get_authdomain/" . $uid;
            $result = file_get_contents($url);
            return json_decode($result, true);
        } catch (\Exception $e) {
            self::setError('获取授权域名失败:' . $e->getMessage());
            return false;
        }
    }
}
