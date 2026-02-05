<template>
    <el-dialog
        v-model="show"
        :title="mode == 'add' ? '新增奖品' : '编辑奖品'"
        width="500px"
        :close-on-click-modal="false"
        @closed="handleClosed"
    >
        <el-form ref="formRef" :model="formData" :rules="rules" label-width="100px">
            <el-form-item label="奖品名称" prop="name">
                <el-input v-model="formData.name" placeholder="请输入奖品名称" />
            </el-form-item>
            <el-form-item label="日期" prop="dates">
                <el-date-picker
                    v-model="formData.dates"
                    type="date"
                    value-format="YYYY-MM-DD"
                    placeholder="请选择日期"
                    class="w-full"
                />
            </el-form-item>
            <el-form-item label="特等奖" prop="special">
                <el-input-number v-model="formData.special" :min="0" :precision="0" />
            </el-form-item>
            <el-form-item label="中奖区间" required>
                <div class="flex items-center">
                    <el-form-item prop="min">
                        <el-input-number v-model="formData.min" :min="0" :precision="2" placeholder="最小值" />
                    </el-form-item>
                    <span class="mx-2">-</span>
                    <el-form-item prop="max">
                        <el-input-number v-model="formData.max" :min="0" :precision="2" placeholder="最大值" />
                    </el-form-item>
                </div>
            </el-form-item>
            <el-form-item label="奖金池" prop="bonuses_pool">
                <el-input-number v-model="formData.bonuses_pool" :min="0" :precision="2" />
            </el-form-item>
            <el-form-item label="发放数量" prop="number_all">
                <el-input-number v-model="formData.number_all" :min="0" :precision="0" />
            </el-form-item>
        </el-form>
        <template #footer>
            <div class="dialog-footer">
                <el-button @click="show = false">取消</el-button>
                <el-button type="primary" @click="handleSubmit" :loading="loading">确定</el-button>
            </div>
        </template>
    </el-dialog>
</template>

<script lang="ts" setup>
import { ref, reactive } from 'vue'
import { lotteryAdd, lotteryEdit, lotteryDetail } from '@/api/marketing'
import { ElMessage } from 'element-plus'
import type { FormInstance, FormRules } from 'element-plus'
import MaterialPicker from '@/components/material/picker.vue'

const emit = defineEmits(['success'])

const show = ref(false)
const mode = ref('add')
const loading = ref(false)
const formRef = ref<FormInstance>()

const formData = reactive({
    id: '',
    name: '',
    dates: '',
    min: 0,
    max: 0,
    bonuses_pool: 0,
    number_all: 0,
    special: 0
})

const rules = reactive<FormRules>({
    name: [{ required: true, message: '请输入奖品名称', trigger: 'blur' }],
    dates: [{ required: true, message: '请选择日期', trigger: 'change' }],
    min: [{ required: true, message: '请输入最小区间', trigger: 'blur' }],
    max: [{ required: true, message: '请输入最大区间', trigger: 'blur' }],
    bonuses_pool: [{ required: true, message: '请输入奖金池', trigger: 'blur' }],
    number_all: [{ required: true, message: '请输入发放数量', trigger: 'blur' }]
})

const open = async (type: string, id?: number) => {
    mode.value = type
    show.value = true
    if (type == 'edit' && id) {
        const res = await lotteryDetail({ id })
        Object.assign(formData, res)
    }
}

const handleClosed = () => {
    if (!formRef.value) return
    formRef.value.resetFields()
    formData.id = ''
    formData.name = ''
    formData.dates = ''
    formData.min = 0
    formData.max = 0
    formData.bonuses_pool = 0
    formData.number_all = 0
    formData.special = 0
}

const handleSubmit = async () => {
    await formRef.value?.validate()
    loading.value = true
    try {
        if (mode.value == 'add') {
            await lotteryAdd(formData)
        } else {
            await lotteryEdit(formData)
        }
        ElMessage.success('操作成功')
        show.value = false
        emit('success')
    } finally {
        loading.value = false
    }
}

defineExpose({
    open
})
</script>
