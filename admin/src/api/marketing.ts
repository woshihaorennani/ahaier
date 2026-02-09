import request from '@/utils/request'

// 抽奖记录列表
export function lotteryRecordLists(params?: any) {
    return request.get({ url: '/marketing.lottery_record/lists', params })
}

// 微信用户列表
export function weixinUserLists(params?: any) {
    return request.get({ url: '/marketing.weixin_user/lists', params })
}

// 奖品列表
export function lotteryLists(params?: any) {
    return request.get({ url: '/marketing.lottery/lists', params })
}

// 添加奖品
export function lotteryAdd(params: any) {
    return request.post({ url: '/marketing.lottery/add', params })
}

// 编辑奖品
export function lotteryEdit(params: any) {
    return request.post({ url: '/marketing.lottery/edit', params })
}

// 删除奖品
export function lotteryDelete(params: any) {
    return request.post({ url: '/marketing.lottery/delete', params })
}

// 奖品详情
export function lotteryDetail(params: any) {
    return request.get({ url: '/marketing.lottery/detail', params })
}

// 联系人列表
export function lotteryContactLists(params?: any) {
    return request.get({ url: '/marketing.lottery/contactLists', params })
}

// 发送红包
export function sendRedPacket(params: any) {
    return request.post({ url: '/marketing.weixin_user/sendRedPacket', params })
}
