<template>
    <div class="container">
        <div class="content-header mb-3" style="border-bottom: 1px solid black;">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h4 class="m-0 text-dark"><strong>{{ titleHeader }}</strong></h4>
                    </div>
                    <div class="col-sm-6">
                        <button type="button" class="btn button-standard float-right" @click="$router.go(-1)">
                            <i class="el-icon-arrow-left"></i> Back
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-12">
                <form @submit.prevent="updateSCMaterial()">
                    <el-card class="box-card mb-3" shadow="hover">
                        <div slot="header">
                            <span>Subcontract Material Information</span>
                        </div>
                        <el-form ref="form" :model="form" label-width="15%" :label-position="`right`">
                            <el-form-item label="Material Name">
                                <el-col :span="12">
                                    <el-input v-model="form.sc_material_name" :disabled="true"></el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Part Number">
                                <el-col :span="12">
                                    <el-input v-model="form.sc_order_part_no" :disabled="true">
                                    </el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Type Code">
                                <el-col :span="6">
                                    <el-select v-model="form.sc_material_type_id" @change="selectTypeCode" :disabled="true" placeholder="Type Code">
                                        <el-option
                                        v-for="type_code in type_codes"
                                        :key="type_code.id"
                                        :label="typeCodeLabel(type_code.type_code, type_code.type_code_description)"
                                        :value="type_code.id">
                                        </el-option>
                                    </el-select>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Classification">
                                <el-col :span="6">
                                    <el-select v-model="form.sc_material_classification_id" :disabled="true" placeholder="Classification">
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
                                <el-col>
                                    <el-select v-model="form.sc_supplier_id" placeholder="Suppliers">
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
                                <el-col :span="4">
                                    <el-input v-model="form.sc_material_quantity"></el-input>
                                </el-col>
                                <el-col :span="3">
                                    <el-select v-model="form.sc_material_quantity_unit" :disabled="true" placeholder="Unit">
                                        <el-option
                                        v-for="quantity_unit in $units_quantity"
                                        :key="quantity_unit.id"
                                        :label="quantity_unit.unit_name"
                                        :value="quantity_unit.id">
                                        </el-option>
                                    </el-select>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Amount" v-show="$route.params.material_flow_id == 1">
                                <el-col :span="4">
                                    <el-input v-model="form.sc_material_unit_amount"></el-input>
                                </el-col>
                                <el-col :span="3">
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
                        </el-form>
                        <div class="outside-card-button">
                            <el-button type="primary" v-on:click="transactSCMaterial">
                                <i class="el-icon-circle-check"></i> Transact
                            </el-button>
                        </div>
                    </el-card>
                </form>
            </div>
        </div>
    </div>
</template>

<script>
    import moment from 'moment';
    export default {
        data(){
            return {
                form: new Form({
                    sc_material_name: '',
                    sc_order_part_no: '',
                    sc_material_type_id: '',
                    sc_material_classification_id: '',
                    material_length: '',
                    material_length_unit: '',
                    material_width: '',
                    material_width_unit: '',
                    supplier_material_id: '',
                    sc_material_quantity: '',
                    sc_material_quantity_unit: '',
                    sc_material_unit_amount: '',
                    sc_material_unit_amount_currency: '',
                    material_location: '',
                    material_last_in: '',
                    material_last_out: '',
                    material_id: '',
                    material_flow_id: '',
                    sc_supplier_id: '',
                }),
                type_codes: '',
                categories: [],
                classifications: [],
                suppliers: [],
                isEdit: false,
                filter: '',
                suppliers: [],
            }
        },
        methods: {
            transactSCMaterial() {
                Swal.fire({
                    text: 'Are you sure you want to transact subcontract material?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Transact',
                    width: '25rem',
                })
                .then((result) => {
                    if(result.value){
                        this.isLoading = true;
                        this.form.post('/api/store-sc-material-supplier/'+this.$route.params.sc_material_id)
                        .then((res) => {
                            Swal.fire(
                                'Transacted!',
                                'Subcontract material successfully transacted.',
                                'success'
                            )
                            this.getSCMaterial();
                        })
                        .catch(error => {
                            console.log(error)
                        });
                        this.isLoading = false;
                    }
                })
            },
            getSCMaterial(){
                axios.get('/api/sc-material/'+this.$route.params.sc_material_id)
                .then((res) => {
                    this.form.fill(res.data);
                    this.form.sc_material_quantity = '';
                    this.selectTypeCode();

                    if(this.$route.params.material_flow_id > 1){
                        this.form.sc_material_unit_amount = 0;
                        this.form.sc_material_unit_amount_currency = 'PHP';
                    }
                    this.form.material_flow_id = this.$route.params.material_flow_id;
                })
                .catch((error) => {
                    console.log(error)
                })
            },
            resetFields(){
                Swal.fire({
                    position: 'center',
                    icon: 'success',
                    text: 'Form reset completed.',
                    showConfirmButton: false,
                    timer: 1500
                });
                this.form.clear();
                this.getSCMaterial();
            },
            getTypeCodes(){
                axios.get('/api/get-sc-type-codes')
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
                axios.get('/api/get-material-classification/'+this.form.sc_material_type_id)
                .then((res) => {
                    this.classifications = res.data;
                })
                .catch((error) => {
                    console.log(error)
                })
            },
            getSuppliers(){
                axios.get('/api/get-suppliers')
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
            pageType(){
                if(window.location.href.indexOf("update") > -1){
                    this.isEdit = true;
                }
                else{
                    this.isEdit = false;
                }
            },
            toEdit(){
                this.isEdit = true;
            },
        },
        created() {
            this.getSCMaterial();
            this.getTypeCodes();
            this.getSuppliers();
            this.pageType();
        },
        computed: {
            titleHeader(){
                return this.$material_flows_title.find(flow => flow.id == this.$route.params.material_flow_id).material_flow_name + ' Subcontract Material';
            }
        },
        watch:{
            $route (to, from){
                this.pageType();
            }
        }
    }
</script>
