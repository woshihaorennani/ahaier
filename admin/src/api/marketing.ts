import request from '@/utils/request'

// 抽奖记录列表
export function lotteryRecordLists(params?: any) {
    return request.get({ url: '/marketing.lottery_record/lists', params })
}

// 微信用户列表
export function weixinUserLists(params?: any) {
    return request.get({ url: '/marketing.weixin_user/lists', params })
}
