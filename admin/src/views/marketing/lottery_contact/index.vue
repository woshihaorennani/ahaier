<template>
    <div class="lottery-contact-lists">
        <el-card class="!border-none" shadow="never">
            <el-form ref="formRef" class="mb-[-16px]" :model="queryParams" :inline="true">
                <el-form-item label="姓名">
                    <el-input
                        class="w-[180px]"
                        v-model="queryParams.name"
                        placeholder="请输入姓名"
                        clearable
                        @keyup.enter="resetPage"
                    />
                </el-form-item>
                <el-form-item label="电话">
                    <el-input
                        class="w-[180px]"
                        v-model="queryParams.phone"
                        placeholder="请输入电话"
                        clearable
                        @keyup.enter="resetPage"
                    />
                </el-form-item>
                <el-form-item label="业务">
                     <el-select class="w-[180px]" v-model="queryParams.business" clearable placeholder="请选择业务">
                        <el-option label="分布式光伏解决方案" value="分布式光伏解决方案" />
                        <el-option label="能源储能系统" value="能源储能系统" />
                        <el-option label="光伏逆变器" value="光伏逆变器" />
                    </el-select>
                </el-form-item>
                <el-form-item label="区域">
                    <el-input
                        class="w-[180px]"
                        v-model="queryParams.region"
                        placeholder="请输入区域"
                        clearable
                        @keyup.enter="resetPage"
                    />
                </el-form-item>
                <el-form-item>
                    <el-button type="primary" @click="resetPage">查询</el-button>
                    <el-button @click="resetParams">重置</el-button>
                    <export-data
                        class="ml-2.5"
                        :fetch-fun="lotteryContactLists"
                        :params="queryParams"
                        :page-size="pager.size"
                    />
                </el-form-item>
            </el-form>
        </el-card>

        <el-card class="!border-none mt-4" shadow="never">
            <el-table size="large" v-loading="pager.loading" :data="pager.lists">
                <el-table-column label="ID" prop="id" min-width="80" />
                <el-table-column label="姓名" prop="name" min-width="120" />
                <el-table-column label="电话" prop="phone" min-width="120" />
                <el-table-column label="业务" prop="business" min-width="150" />
                <el-table-column label="所在区域" prop="region" min-width="150" />
                <el-table-column label="需求" prop="request" min-width="200" show-overflow-tooltip />
                <el-table-column label="提交时间" prop="create_time" min-width="160" />
            </el-table>

            <div class="flex justify-end mt-4">
                <pagination v-model="pager" @change="getLists" />
            </div>
        </el-card>
    </div>
</template>

<script lang="ts" setup name="lotteryContactLists">
import { reactive } from 'vue'
import { lotteryContactLists } from '@/api/marketing'
import { usePaging } from '@/hooks/usePaging'
import ExportData from '@/components/export-data/index.vue'

const queryParams = reactive({
    name: '',
    phone: '',
    business: '',
    region: ''
})

const { pager, getLists, resetPage, resetParams } = usePaging({
    fetchFun: lotteryContactLists,
    params: queryParams
})

getLists()
</script>
