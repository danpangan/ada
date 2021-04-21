<template>
    <div class="container">
        <div class="content-header mb-3" style="border-bottom: 1px solid black;">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h4 class="m-0 text-dark"><strong>Inventory Management</strong></h4>
                    </div>
                    <div class="col-sm-6">
                        <button type="button" class="btn button-standard float-right" @click="$router.push('/subcontract-materials')">
                            <i class="el-icon-arrow-left"></i> Lists
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-12">
                <form @submit.prevent="createSCMaterial()">
                    <el-card class="box-card" shadow="hover">
                        <div slot="header">
                            <span>Subcontract Material Information</span>
                        </div>
                        <el-form ref="form" :model="form" label-width="15%" :label-position="`right`">
                            <el-form-item label="Material Name">
                                <el-col :span="12">
                                    <el-input :spna="12" v-model="form.sc_material_name"></el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Part Number">
                                <el-col :span="12">
                                    <el-input :spna="12" v-model="form.sc_order_part_number"></el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Type Code">
                                <el-col :span="6">
                                    <el-select v-model="form.sc_material_type_id" @change="selectTypeCode" :disabled="disableTypeCode" placeholder="Type Code">
                                        <el-option
                                        v-for="type_code in type_codes"
                                        :key="type_code.id"
                                        :label="typeCodeLabel(type_code.type_code, type_code.type_code_description)"
                                        :value="type_code.id">
                                        </el-option>
                                    </el-select>
                                </el-col>
                                <el-col :span="6" v-show="form.sc_material_type_id">
                                    <el-tooltip class="item" effect="dark" content="Add material classification" placement="top">
                                        <i :class="isVisiblelassificationsSetting ? 'icon el-icon-minus pointer ml-3' : 'icon el-icon-plus pointer ml-3'" @click="classificationsSetting"></i>
                                    </el-tooltip>
                                </el-col>
                            </el-form-item>
                            <div v-show="isVisiblelassificationsSetting">
                                <hr>
                                <el-form-item label="Add Material Classification">
                                    <el-col :span="8">
                                        <el-input :span="8" v-model="classificationForm.material_classification_name">
                                        </el-input>
                                    </el-col>
                                    <el-col :span="12">
                                        <el-button type="primary" class="ml-1" v-on:click="createNewClassification"><i class="el-icon-plus"></i> Add</el-button>
                                    </el-col>
                                </el-form-item>
                                <hr>
                            </div>
                            <el-form-item label="Classification">
                                <el-col :span="6">
                                    <el-select v-model="form.sc_material_classification_id" :disabled="classifications.length == 0" placeholder="Classification">
                                        <el-option
                                        v-for="classification in classifications"
                                        :key="classification.id"
                                        :label="classification.material_classification_name"
                                        :value="classification.id">
                                        </el-option>
                                    </el-select>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Supplier">
                                <el-col :span="12">
                                    <el-select v-model="form.supplier_sc_material_id" placeholder="Suppliers" class="block">
                                        <el-option
                                        v-for="supplier in suppliers"
                                        :key="supplier.id"
                                        :label="supplierLabel(supplier.supplier_code, supplier.supplier_name)"
                                        :value="supplier.id">
                                        </el-option>
                                    </el-select>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Quantity">
                                <el-col :span="6">
                                    <el-input v-model="form.sc_material_quantity"></el-input>
                                </el-col>
                                <el-col :span="6">
                                    <el-select v-model="form.sc_material_quantity_unit" placeholder="Unit">
                                        <el-option
                                        v-for="quantity_unit in $units_quantity"
                                        :key="quantity_unit.id"
                                        :label="quantity_unit.unit_name"
                                        :value="quantity_unit.id">
                                        </el-option>
                                    </el-select>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Amount">
                                <el-col :span="6">
                                    <el-input v-model="form.sc_material_unit_amount"></el-input>
                                </el-col>
                                <el-col :span="6">
                                    <el-select v-model="form.sc_material_unit_amount_currency" placeholder="Unit">
                                        <el-option
                                        v-for="amount_unit in $units_amount"
                                        :key="amount_unit.id"
                                        :label="amount_unit.unit_name"
                                        :value="amount_unit.unit_name">
                                        </el-option>
                                    </el-select>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Location">
                                <el-col :span="12">
                                    <el-input 
                                        type="textarea" 
                                        v-model="form.sc_material_location" 
                                        resize="none"
                                        :autosize="{ minRows: 4, maxRows: 6}">
                                    </el-input>
                                </el-col>
                            </el-form-item>
                        </el-form>
                    </el-card>

                    <div class="outside-card-button">
                        <el-button type="primary" v-on:click="createSCMaterial"><i class="el-icon-plus"></i> Create</el-button>
                        <el-button type="warning" v-on:click="resetFields"><i class="el-icon-refresh-left"></i> Reset</el-button>
                        <el-button type="danger" @click="$router.back(-1)"><i class="el-icon-close"></i> Cancel</el-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        data(){
            return {
                form: new Form({
                    sc_material_name: '',
                    sc_order_part_number: '',
                    sc_material_type_id: '',
                    sc_material_classification_id: '',
                    supplier_sc_material_id: '',
                    sc_material_quantity: '',
                    sc_material_quantity_unit: '',
                    sc_material_unit_amount: '',
                    sc_material_unit_amount_currency: '',
                    sc_material_location: '',
                    sc_material_flow_id: 1,
                }),
                classificationForm: new Form({
                    type_code_id: '',
                    material_classification_name: '',
                }),
                type_codes: '',
                categories: [],
                classifications: [],
                suppliers: [],
                isVisiblelassificationsSetting: false,
                disableTypeCode: false,
            }
        },
        methods: {
            createSCMaterial(){
                Swal.fire({
                    text: 'Are you sure you want to register new subcontract material?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Register',
                    width: '25rem',
                })
                .then((result) => {
                    if(result.value){
                        this.form.post('api/sc-material')
                        .then((res) => {
                            this.form.reset();
                            Swal.fire(
                                'Registered!',
                                'Subcontract material successfully registered.',
                                'success'
                            )
                        })
                        .catch(error => {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops!',
                                text: 'Subcontract material registration failed.',
                            })
                            console.log(error)
                        });
                    }
                })
            },
            getTypeCodes(){
                axios.get('api/get-sc-type-codes')
                .then((res) => {
                    this.type_codes = res.data;
                })
                .catch((error) => {
                    console.log(error)
                });
            },
            resetFields(){

            },
            typeCodeLabel(type_code, classification){
                return type_code + ' - ' + classification;
            },
            selectTypeCode(){
                this.classificationForm.type_code_id = this.form.sc_material_type_id;
                axios.get('api/get-material-classification/'+this.form.sc_material_type_id)
                .then((res) => {
                    this.form.sc_material_classification_id = '';
                    this.classifications = res.data;
                })
                .catch((error) => {
                    console.log(error)
                })
            },
            getSuppliers(){
                axios.get('api/get-suppliers')
                .then((res) => {
                    this.suppliers = res.data;
                })
                .catch((error) => {
                    console.log(error)
                })
            },
            supplierLabel(code, name){
                return code + ' - ' + name;
            },
            classificationsSetting(){
                if(this.isVisiblelassificationsSetting == false) this.isVisiblelassificationsSetting = true;
                else this.isVisiblelassificationsSetting = false;

                if(this.disableTypeCode == false) this.disableTypeCode = true;
                else this.disableTypeCode = false;
            },
            createNewClassification(){
                Swal.fire({
                    text: 'Are you sure you want to register new material classification?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Register',
                    width: '25rem',
                })
                .then((result) => {
                    if(result.value){
                        this.classificationForm.post('api/add-material-classification/'+this.classificationForm.type_code_id)
                        .then((res) => {
                            this.selectTypeCode();
                            this.isVisiblelassificationsSetting = false;
                            this.disableTypeCode = false;
                            this.classificationForm.material_classification_name = '';
                            Swal.fire(
                                'Registered!',
                                'Material classification successfully registered.',
                                'success'
                            )
                        })
                        .catch(error => {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops!',
                                text: 'Material classification registration failed.',
                            })
                            console.log(error)
                        });
                    }
                })
            },
        },
        created(){
            this.getTypeCodes();
            this.getSuppliers();
        },
    }
</script>
