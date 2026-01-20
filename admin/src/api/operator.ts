import request from '@/utils/request'

// 运营商列表
export function operatorLists(params?: any) {
    return request.get({ url: '/operator.operator/lists', params })
}

// 添加运营商
export function operatorAdd(params: any) {
    return request.post({ url: '/operator.operator/add', params })
}

// 编辑运营商
export function operatorEdit(params: any) {
    return request.post({ url: '/operator.operator/edit', params })
}

// 删除运营商
export function operatorDelete(params: any) {
    return request.post({ url: '/operator.operator/delete', params })
}

// 运营商详情
export function operatorDetail(params: any) {
    return request.get({ url: '/operator.operator/detail', params })
}

// 运营商状态
export function operatorStatus(params: any) {
    return request.post({ url: '/operator.operator/status', params })
}

// 经营范围列表
export function businessScopeLists(params?: any) {
    return request.get({ url: '/operator.business_scope/lists', params })
}

// 添加经营范围
export function businessScopeAdd(params: any) {
    return request.post({ url: '/operator.business_scope/add', params })
}

// 编辑经营范围
export function businessScopeEdit(params: any) {
    return request.post({ url: '/operator.business_scope/edit', params })
}

// 删除经营范围
export function businessScopeDelete(params: any) {
    return request.post({ url: '/operator.business_scope/delete', params })
}

// 经营范围详情
export function businessScopeDetail(params: any) {
    return request.get({ url: '/operator.business_scope/detail', params })
}

// 经营范围状态
export function businessScopeStatus(params: any) {
    return request.post({ url: '/operator.business_scope/status', params })
}
