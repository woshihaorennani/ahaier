<template>
    <div class="weixin-user-lists">
        <el-card class="!border-none" shadow="never">
            <el-form ref="formRef" class="mb-[-16px]" :model="queryParams" :inline="true">
                <el-form-item label="来源">
                    <el-select
                        class="!w-[180px]"
                        v-model="queryParams.is_from"
                        placeholder="请选择来源"
                        clearable
                    >
                        <el-option label="有值" value="1" />
                        <el-option label="没有值" value="0" />
                    </el-select>
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
                <el-form-item>
                    <el-button type="primary" @click="resetPage">查询</el-button>
                    <el-button @click="resetParams">重置</el-button>
                    <el-button type="success" @click="handleExport">全部导出</el-button>
                </el-form-item>
            </el-form>
        </el-card>

        <el-card class="!border-none mt-4" shadow="never">
            <el-table size="large" v-loading="pager.loading" :data="pager.lists">
                <el-table-column label="ID" prop="id" min-width="80" />
                <el-table-column label="来源" prop="is_from" min-width="150" show-tooltip-when-overflow />
                <el-table-column label="OpenID" prop="openid" min-width="220" show-tooltip-when-overflow />
                <el-table-column label="创建时间" prop="create_time" min-width="160" />
                <el-table-column label="更新时间" prop="update_time" min-width="160" />
                <el-table-column label="操作" width="120" fixed="right">
                    <template #default="{ row }">
                        <el-button
                            v-if="row.is_from"
                            type="primary"
                            link
                            @click="handleSendRedPacket(row)"
                        >
                            发红包
                        </el-button>
                    </template>
                </el-table-column>
            </el-table>

            <div class="flex justify-end mt-4">
                <pagination v-model="pager" @change="getLists" />
            </div>
        </el-card>

        <el-dialog v-model="dialogVisible" title="发送红包" width="460px">
            <el-form :model="formData" label-width="80px">
                <el-form-item label="金额范围">
                     <div class="flex items-center">
                        <el-input-number 
                            v-model="formData.min_money" 
                            :min="0.1" 
                            :max="5000" 
                            :precision="2" 
                            :step="0.1" 
                            placeholder="最小金额"
                            class="!w-[140px]"
                        />
                        <span class="mx-2">-</span>
                        <el-input-number 
                            v-model="formData.max_money" 
                            :min="0.1" 
                            :max="5000" 
                            :precision="2" 
                            :step="0.1" 
                            placeholder="最大金额"
                            class="!w-[140px]"
                        />
                        <span class="ml-2">元</span>
                     </div>
                </el-form-item>
                <el-form-item label="发送数量">
                    <el-input-number 
                        v-model="formData.count" 
                        :min="1" 
                        :max="100" 
                        :step="1" 
                        placeholder="数量"
                        class="!w-[140px]"
                    />
                    <span class="ml-2">个</span>
                </el-form-item>
            </el-form>
            <template #footer>
                <el-button @click="dialogVisible = false">取消</el-button>
                <el-button type="primary" @click="handleSubmit">确定</el-button>
            </template>
        </el-dialog>
    </div>
</template>

<script lang="ts" setup name="weixinUserLists">
import { reactive, ref } from 'vue'
import { weixinUserLists, sendRedPacket } from '@/api/marketing'
import { usePaging } from '@/hooks/usePaging'
import feedback from '@/utils/feedback'

const dialogVisible = ref(false)
const formData = reactive({
    openid: '',
    min_money: 0.3,
    max_money: 0.3,
    count: 1
})

const handleSendRedPacket = (row: any) => {
    formData.openid = row.openid
    formData.min_money = 0.3
    formData.max_money = 0.3
    formData.count = 1
    dialogVisible.value = true
}

const handleSubmit = async () => {
    await sendRedPacket(formData)
    feedback.msgSuccess('发送成功')
    dialogVisible.value = false
}

const queryParams = reactive({
    is_from: '',
    openid: '',
    unionid: ''
})

const { pager, getLists, resetPage, resetParams } = usePaging({
    fetchFun: weixinUserLists,
    params: queryParams
})

const handleExport = async () => {
    await weixinUserLists({
        ...queryParams,
        export: 2,
        page_type: 0
    })
}

getLists()
</script>
