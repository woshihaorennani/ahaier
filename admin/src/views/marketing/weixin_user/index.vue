<template>
    <div class="weixin-user-lists">
        <el-card class="!border-none" shadow="never">
            <el-form ref="formRef" class="mb-[-16px]" :model="queryParams" :inline="true">
                <el-form-item label="昵称">
                    <el-input
                        class="w-[180px]"
                        v-model="queryParams.nickname"
                        placeholder="请输入昵称"
                        clearable
                        @keyup.enter="resetPage"
                    />
                </el-form-item>
                <el-form-item label="OpenID">
                    <el-input
                        class="w-[220px]"
                        v-model="queryParams.openid"
                        placeholder="请输入 OpenID"
                        clearable
                        @keyup.enter="resetPage"
                    />
                </el-form-item>
                <el-form-item label="UnionID">
                    <el-input
                        class="w-[220px]"
                        v-model="queryParams.unionid"
                        placeholder="请输入 UnionID"
                        clearable
                        @keyup.enter="resetPage"
                    />
                </el-form-item>
                <el-form-item>
                    <el-button type="primary" @click="resetPage">查询</el-button>
                    <el-button @click="resetParams">重置</el-button>
                </el-form-item>
            </el-form>
        </el-card>

        <el-card class="!border-none mt-4" shadow="never">
            <el-table size="large" v-loading="pager.loading" :data="pager.lists">
                <el-table-column label="ID" prop="id" min-width="80" />
                <el-table-column label="头像" min-width="80">
                    <template #default="{ row }">
                        <el-avatar :size="32" :src="row.avatar" />
                    </template>
                </el-table-column>
                <el-table-column label="昵称" prop="nickname" min-width="150" show-tooltip-when-overflow />
                <el-table-column label="OpenID" prop="openid" min-width="220" show-tooltip-when-overflow />
                <el-table-column label="UnionID" prop="unionid" min-width="220" show-tooltip-when-overflow />
                <el-table-column label="性别" min-width="80">
                    <template #default="{ row }">
                        <span v-if="row.sex === 1">男</span>
                        <span v-else-if="row.sex === 2">女</span>
                        <span v-else>未知</span>
                    </template>
                </el-table-column>
                <el-table-column label="地区" min-width="180">
                    <template #default="{ row }">
                        <span>{{ [row.country, row.province, row.city].filter(Boolean).join(' / ') }}</span>
                    </template>
                </el-table-column>
                <el-table-column label="订阅来源" prop="subscribe_scene" min-width="120" show-tooltip-when-overflow />
                <el-table-column label="订阅时间" prop="subscribe_time" min-width="160" />
                <el-table-column label="创建时间" prop="create_time" min-width="160" />
            </el-table>

            <div class="flex justify-end mt-4">
                <pagination v-model="pager" @change="getLists" />
            </div>
        </el-card>
    </div>
</template>

<script lang="ts" setup name="weixinUserLists">
import { reactive } from 'vue'
import { weixinUserLists } from '@/api/marketing'
import { usePaging } from '@/hooks/usePaging'

const queryParams = reactive({
    nickname: '',
    openid: '',
    unionid: ''
})

const { pager, getLists, resetPage, resetParams } = usePaging({
    fetchFun: weixinUserLists,
    params: queryParams
})

getLists()
</script>
