<template>
    <div class="lottery-lists">
        <el-card class="!border-none" shadow="never">
            <el-form ref="formRef" class="mb-[-16px]" :model="queryParams" :inline="true">
                <el-form-item label="奖品名称">
                    <el-input
                        class="w-[180px]"
                        v-model="queryParams.name"
                        placeholder="请输入奖品名称"
                        clearable
                        @keyup.enter="resetPage"
                    />
                </el-form-item>
                <el-form-item label="日期">
                    <el-date-picker
                        class="w-[180px]"
                        v-model="queryParams.dates"
                        type="date"
                        value-format="YYYY-MM-DD"
                        placeholder="请选择日期"
                        clearable
                        @change="resetPage"
                    />
                </el-form-item>
                <el-form-item>
                    <el-button type="primary" @click="resetPage">查询</el-button>
                    <el-button @click="resetParams">重置</el-button>
                </el-form-item>
            </el-form>
        </el-card>

        <el-card class="!border-none mt-4" shadow="never">
            <div class="mb-4">
                <el-button type="primary" @click="handleAdd">新增奖品</el-button>
            </div>
            <el-table size="large" v-loading="pager.loading" :data="pager.lists">
                <el-table-column label="ID" prop="id" min-width="80" />
                <el-table-column label="奖品名称" prop="name" min-width="150" show-tooltip-when-overflow />
                <el-table-column label="可用日期" prop="dates" min-width="120" />
                <el-table-column label="特等奖" min-width="80">
                    <template #default="{ row }">
                        ¥{{ row.special }}
                    </template>
                </el-table-column>
                <el-table-column label="中奖区间" min-width="120">
                    <template #default="{ row }">
                        ¥{{ row.min }} - ¥{{ row.max }}
                    </template>
                </el-table-column>
                <el-table-column label="发放数量" prop="number_all" min-width="100" />
                <el-table-column label="已发数量" prop="number_user" min-width="100" />
                <el-table-column label="创建时间" prop="create_time" min-width="160" />
                <el-table-column label="操作" width="120" fixed="right">
                    <template #default="{ row }">
                        <el-button link type="primary" @click="handleEdit(row)">编辑</el-button>
                        <el-button link type="danger" @click="handleDelete(row)">删除</el-button>
                    </template>
                </el-table-column>
            </el-table>

            <div class="flex justify-end mt-4">
                <pagination v-model="pager" @change="getLists" />
            </div>
        </el-card>

        <edit-popup ref="editRef" @success="getLists" />
    </div>
</template>

<script lang="ts" setup name="lotteryLists">
import { reactive, ref, shallowRef } from 'vue'
import { lotteryLists, lotteryDelete } from '@/api/marketing'
import { usePaging } from '@/hooks/usePaging'
import { ElMessage, ElMessageBox } from 'element-plus'
import EditPopup from './edit.vue'

const editRef = shallowRef<InstanceType<typeof EditPopup>>()
const queryParams = reactive({
    name: '',
    dates: ''
})

const { pager, getLists, resetPage, resetParams } = usePaging({
    fetchFun: lotteryLists,
    params: queryParams
})

const handleAdd = () => {
    editRef.value?.open('add')
}

const handleEdit = (row: any) => {
    editRef.value?.open('edit', row.id)
}

const handleDelete = async (row: any) => {
    await ElMessageBox.confirm('确定要删除该奖品吗？', '提示', {
        type: 'warning'
    })
    await lotteryDelete({ id: row.id })
    ElMessage.success('删除成功')
    getLists()
}

getLists()
</script>
