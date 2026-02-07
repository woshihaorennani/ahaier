<template>
    <div class="lottery-record-lists">
        <el-card class="!border-none" shadow="never">
            <el-form ref="formRef" class="mb-[-16px]" :model="queryParams" :inline="true">
                <el-form-item label="OpenID">
                    <el-input
                        class="w-[180px]"
                        v-model="queryParams.openid"
                        placeholder="请输入OpenID"
                        clearable
                        @keyup.enter="resetPage"
                    />
                </el-form-item>
                <el-form-item label="用户昵称">
                    <el-input
                        class="w-[180px]"
                        v-model="queryParams.nickname"
                        placeholder="请输入用户昵称"
                        clearable
                        @keyup.enter="resetPage"
                    />
                </el-form-item>
                <el-form-item label="奖品名称">
                    <el-input
                        class="w-[180px]"
                        v-model="queryParams.prize_name"
                        placeholder="请输入奖品名称"
                        clearable
                        @keyup.enter="resetPage"
                    />
                </el-form-item>
                <el-form-item>
                    <el-button type="primary" @click="resetPage">查询</el-button>
                    <el-button @click="resetParams">重置</el-button>
                    <export-data
                        class="ml-2.5"
                        :fetch-fun="lotteryRecordLists"
                        :params="queryParams"
                        :page-size="pager.size"
                    />
                </el-form-item>
            </el-form>
        </el-card>

        <el-card class="!border-none mt-4" shadow="never">
            <el-table size="large" v-loading="pager.loading" :data="pager.lists">
                <el-table-column label="ID" prop="id" min-width="80" />
                <!-- <el-table-column label="OpenID" prop="openid" min-width="180" show-tooltip-when-overflow /> -->
                <el-table-column label="奖品ID" prop="lottery_id" min-width="80" />
                <el-table-column label="是否中奖" prop="is_win" min-width="100" />
                <el-table-column label="金额" prop="amount" min-width="100" />
                <el-table-column label="奖品名称" prop="prize_name" min-width="150" show-tooltip-when-overflow />
                <el-table-column label="抽奖时间" prop="create_time" min-width="160" />
                <el-table-column label="更新时间" prop="update_time" min-width="160" />
            </el-table>

            <div class="flex justify-end mt-4">
                <pagination v-model="pager" @change="getLists" />
            </div>
        </el-card>
    </div>
</template>

<script lang="ts" setup name="lotteryRecordLists">
import { reactive } from 'vue'
import { lotteryRecordLists } from '@/api/marketing'
import { usePaging } from '@/hooks/usePaging'
import ExportData from '@/components/export-data/index.vue'

const queryParams = reactive({
    nickname: '',
    prize_name: '',
    openid: ''
})

const { pager, getLists, resetPage, resetParams } = usePaging({
    fetchFun: lotteryRecordLists,
    params: queryParams
})

getLists()
</script>
