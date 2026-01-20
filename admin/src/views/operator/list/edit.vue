<template>
    <div class="edit-popup">
        <popup
            ref="popupRef"
            :title="popupTitle"
            :async="true"
            width="600px"
            @confirm="handleSubmit"
            @close="handleClose"
        >
            <el-form
                ref="formRef"
                :model="formData"
                label-width="100px"
                :rules="formRules"
            >
                <el-form-item label="运营商名称" prop="name">
                    <el-input v-model="formData.name" placeholder="请输入运营商名称" />
                </el-form-item>
                <el-form-item label="联系人" prop="contact">
                    <el-input v-model="formData.contact" placeholder="请输入联系人姓名" />
                </el-form-item>
                <el-form-item label="联系电话" prop="phone">
                    <el-input v-model="formData.phone" placeholder="请输入联系电话" />
                </el-form-item>
                <el-form-item label="邮箱" prop="email">
                    <el-input v-model="formData.email" placeholder="请输入邮箱" />
                </el-form-item>
                <el-form-item label="所在地区" required>
                    <el-cascader
                        v-model="selectedRegion"
                        :options="regionOptions"
                        placeholder="请选择所在地区"
                        class="w-full"
                    />
                </el-form-item>
                <el-form-item label="公司地址" prop="address">
                    <el-input v-model="formData.address" placeholder="请输入公司地址" type="textarea" :rows="2" />
                </el-form-item>
                <el-form-item label="业务范畴" prop="scope">
                    <el-select
                        v-model="formData.scope"
                        placeholder="请选择业务范畴"
                        class="w-full"
                        filterable
                        clearable
                    >
                        <el-option
                            v-for="item in optionsData.operator_scope"
                            :key="item.value"
                            :label="item.name"
                            :value="item.value"
                        />
                    </el-select>
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
import { operatorAdd, operatorEdit, operatorDetail, businessScopeLists } from '@/api/operator'
import { regionData } from 'element-china-area-data'
import { ref, shallowRef, reactive, computed, watch } from 'vue'
import feedback from '@/utils/feedback'
import { useDictOptions } from '@/hooks/useDictOptions'

const emit = defineEmits(['success', 'close'])

const formRef = shallowRef<FormInstance>()
const popupRef = shallowRef<InstanceType<typeof Popup>>()
const mode = ref('add')
const popupTitle = computed(() => {
    return mode.value == 'edit' ? '编辑运营商' : '新增运营商'
})

const { optionsData } = useDictOptions<{
    operator_scope: any[]
}>({
    operator_scope: {
        api: businessScopeLists,
        params: {
            page_no: 1,
            page_size: 100,
            status: 1
        },
        transformData(data: any) {
            return data.lists.map((item: any) => ({
                name: item.name,
                value: item.name
            }))
        }
    }
})

const formData = reactive({
    id: '',
    name: '',
    contact: '',
    phone: '',
    email: '',
    province: '',
    city: '',
    district: '',
    address: '',
    scope: '',
    sort: 0,
    status: 1
})

const formRules: FormRules = {
    name: [{ required: true, message: '请输入运营商名称', trigger: 'blur' }],
    contact: [{ required: true, message: '请输入联系人姓名', trigger: 'blur' }],
    phone: [{ required: true, message: '请输入联系电话', trigger: 'blur' }]
}

// 省市区数据
const regionOptions = regionData.map((item: any) => ({
    value: item.label,
    label: item.label,
    children: item.children?.map((child: any) => ({
        value: child.label,
        label: child.label,
        children: child.children?.map((grandchild: any) => ({
            value: grandchild.label,
            label: grandchild.label
        }))
    }))
}))

const selectedRegion = computed({
    get() {
        if (formData.province && formData.city && formData.district) {
            return [formData.province, formData.city, formData.district]
        }
        return []
    },
    set(val: any) {
        if (val && val.length === 3) {
            formData.province = val[0]
            formData.city = val[1]
            formData.district = val[2]
        } else {
            formData.province = ''
            formData.city = ''
            formData.district = ''
        }
    }
})

const handleSubmit = async () => {
    await formRef.value?.validate()
    const params = { ...formData }
    if (mode.value == 'edit') {
        await operatorEdit(params)
    } else {
        //@ts-ignore
        delete params.id
        await operatorAdd(params)
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
    formData.contact = ''
    formData.phone = ''
    formData.email = ''
    formData.province = ''
    formData.city = ''
    formData.district = ''
    formData.address = ''
    formData.scope = ''
    formData.sort = 0
    formData.status = 1
}

const getDetail = async (row: any) => {
    const data = await operatorDetail({
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