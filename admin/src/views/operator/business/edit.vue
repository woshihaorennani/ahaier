<template>
    <div class="edit-popup">
        <popup
            ref="popupRef"
            :title="popupTitle"
            :async="true"
            width="500px"
            @confirm="handleSubmit"
            @close="handleClose"
        >
            <el-form
                ref="formRef"
                :model="formData"
                label-width="100px"
                :rules="formRules"
            >
                <el-form-item label="名称" prop="name">
                    <el-input v-model="formData.name" placeholder="请输入业务范畴名称" />
                </el-form-item>
                <el-form-item label="排序" prop="sort">
                    <el-input-number v-model="formData.sort" :min="0" :max="9999" />
                </el-form-item>
                <el-form-item label="状态" prop="status">
                    <el-switch v-model="formData.status" :active-value="1" :inactive-value="0" />
                </el-form-item>
            </el-form>
        </popup>
    </div>
</template>

<script lang="ts" setup>
import type { FormInstance, FormRules } from 'element-plus'
import Popup from '@/components/popup/index.vue'
import { businessScopeAdd, businessScopeEdit, businessScopeDetail } from '@/api/operator'
import { ref, shallowRef, reactive, computed } from 'vue'
import feedback from '@/utils/feedback'

const emit = defineEmits(['success', 'close'])

const formRef = shallowRef<FormInstance>()
const popupRef = shallowRef<InstanceType<typeof Popup>>()
const mode = ref('add')
const popupTitle = computed(() => {
    return mode.value == 'edit' ? '编辑业务范畴' : '新增业务范畴'
})

const formData = reactive({
    id: '',
    name: '',
    sort: 0,
    status: 1
})

const formRules: FormRules = {
    name: [{ required: true, message: '请输入业务范畴名称', trigger: 'blur' }]
}

const handleSubmit = async () => {
    await formRef.value?.validate()
    const params = { ...formData }
    if (mode.value == 'edit') {
        await businessScopeEdit(params)
    } else {
        //@ts-ignore
        delete params.id
        await businessScopeAdd(params)
    }
    popupRef.value?.close()
    feedback.msgSuccess('操作成功')
    emit('success')
}

const handleClose = () => {
    emit('close')
}

const open = (type = 'add') => {
    mode.value = type
    popupRef.value?.open()
    resetFormData()
}

const setFormData = async (data: any) => {
    for (const key in formData) {
        if (data[key] != null && data[key] != undefined) {
            //@ts-ignore
            formData[key] = data[key]
        }
    }
}

const resetFormData = () => {
    formData.id = ''
    formData.name = ''
    formData.sort = 0
    formData.status = 1
}

const getDetail = async (row: any) => {
    const data = await businessScopeDetail({
        id: row.id
    })
    setFormData(data)
}

defineExpose({
    open,
    setFormData,
    getDetail
})
</script>
