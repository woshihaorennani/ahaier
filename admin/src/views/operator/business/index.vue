<template>
    <div class="business-scope-lists">
        <el-card class="!border-none" shadow="never">
            <el-form ref="formRef" class="mb-[-16px]" :model="queryParams" :inline="true">
                <el-form-item label="名称">
                    <el-input
                        class="w-[180px]"
                        v-model="queryParams.name"
                        placeholder="请输入业务范畴名称"
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
            <div>
                <el-button type="primary" class="mb-4" @click="handleAdd">
                    <template #icon>
                        <icon name="el-icon-Plus" />
                    </template>
                    新增业务范畴
                </el-button>
            </div>

            <el-table size="large" v-loading="pager.loading" :data="pager.lists">
                <el-table-column label="ID" prop="id" min-width="80" />
                <el-table-column label="业务范畴名称" prop="name" min-width="150" show-tooltip-when-overflow />
                <el-table-column label="排序" prop="sort" min-width="80" />
                <el-table-column label="状态" min-width="100">
                    <template #default="{ row }">
                        <el-switch
                            v-model="row.status"
                            :active-value="1"
                            :inactive-value="0"
                            @change="changeStatus($event, row.id)"
                        />
                    </template>
                </el-table-column>
                <el-table-column label="创建时间" prop="create_time" min-width="160" />
                <el-table-column label="操作" width="120" fixed="right">
                    <template #default="{ row }">
                        <el-button type="primary" link @click="handleEdit(row)">
                            编辑
                        </el-button>
                        <el-button type="danger" link @click="handleDelete(row.id)">
                            删除
                        </el-button>
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

<script lang="ts" setup name="businessScopeLists">
import { ref, reactive, shallowRef } from 'vue'
import { businessScopeLists, businessScopeDelete, businessScopeStatus } from '@/api/operator'
import { usePaging } from '@/hooks/usePaging'
import feedback from '@/utils/feedback'
import EditPopup from './edit.vue'

const editRef = shallowRef<InstanceType<typeof EditPopup>>()
const queryParams = reactive({
    name: '',
    status: ''
})

const { pager, getLists, resetPage, resetParams } = usePaging({
    fetchFun: businessScopeLists,
    params: queryParams
})

const handleAdd = () => {
    editRef.value?.open('add')
}

const handleEdit = (row: any) => {
    editRef.value?.open('edit')
    editRef.value?.getDetail(row)
}

const handleDelete = async (id: number) => {
    await feedback.confirm('确定要删除该业务范畴吗？')
    await businessScopeDelete({ id })
    feedback.msgSuccess('删除成功')
    getLists()
}

const changeStatus = async (active: any, id: number) => {
    try {
        await businessScopeStatus({ id, status: active })
        feedback.msgSuccess('状态修改成功')
    } catch (error) {
        getLists()
    }
}

getLists()
</script>
