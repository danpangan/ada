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
                <form @submit.prevent="updateProduct()">
                    <el-card class="box-card mb-3" shadow="hover">
                        <div slot="header">
                            <span>Product Information</span>
                        </div>
                        <el-form ref="form" :model="form" label-width="15%" :label-position="`right`">
                            <el-form-item label="SS Reference No.">
                                <el-col :span="6">
                                    <el-select v-model="form.ss_reference_no" @change="selectTypeCode" placeholder="SS Ref. No.">
                                        <el-option
                                        v-for="product_ss_ref in product_ss_refs"
                                        :key="product_ss_ref.id"
                                        :label="product_ss_ref.reference_no"
                                        :value="product_ss_ref.id">
                                        </el-option>
                                    </el-select>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Item Code">
                                <el-col :span="6">
                                    <el-select v-model="form.order_item_code" @change="selectTypeCode" placeholder="Type Code">
                                        <el-option
                                        v-for="order_item in order_items"
                                        :key="order_item.id"
                                        :label="order_item.order_item_name"
                                        :value="order_item.id">
                                        </el-option>
                                    </el-select>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Part Number">
                                <el-col :span="12">
                                    <el-input v-model="form.product_part_number" :disabled="true">
                                    </el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Quantity">
                                <el-col :span="4">
                                    <el-input v-model="form.product_stock_quantity" :disabled="true"></el-input>
                                </el-col>
                                <el-col :span="3">
                                    <el-select v-model="form.product_stock_quantity_unit" :disabled="true" placeholder="Unit">
                                        <el-option
                                        v-for="quantity_unit in $units_quantity"
                                        :key="quantity_unit.id"
                                        :label="quantity_unit.unit_name"
                                        :value="quantity_unit.id">
                                        </el-option>
                                    </el-select>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Customer">
                                <el-col :span="12">
                                    <el-select v-model="form.product_customer" :disabled="true" placeholder="Unit">
                                        <el-option
                                        v-for="product_customer in product_customers"
                                        :key="product_customer.id"
                                        :label="product_customer.client_name"
                                        :value="product_customer.id">
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
                    ss_reference_no: '',
                    order_item_code: '',

                    product_part_number: '', 
                    product_name: '', 
                    product_category_id: '',
                    product_classification_id: '',
                    product_type_code_id: '',
                    product_stock_quantity: '',
                    product_stock_quantity_unit: '',
                    product_location: '',
                    product_customer: '',

                }),
                type_codes: '',
                categories: [],
                classifications: [],
                suppliers: [],
                isEdit: false,
                filter: '',
                suppliers: [],
                product_ss_refs : [
                    { 'id': 1, 'reference_no': 'GAFH-7353' },
                    { 'id': 2, 'reference_no': 'BSGH-7362' },
                    { 'id': 3, 'reference_no': 'HSGG-7652' },
                ],
                order_items: [
                    { 'id': 1, 'order_item_name': 'ORDER-1873' },
                    { 'id': 2, 'order_item_name': 'ORDER-4433' },
                    { 'id': 3, 'order_item_name': 'ORDER-2256' },
                ],
                product_customers: [],
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
                        this.form.post('/api/store-sc-material-supplier/'+this.$route.paramsproductl_id)
                        .then((res) => {
                            Swal.fire(
                                'Transacted!',
                                'Subcontract material successfully transacted.',
                                'success'
                            )
                            this.getProduct();
                        })
                        .catch(error => {
                            console.log(error)
                        });
                        this.isLoading = false;
                    }
                })
            },
            getProduct(){
                axios.get('/api/product/'+this.$route.params.product_id)
                .then((res) => {
                    this.form.fill(res.data);
                    this.form.ss_reference_no = 1;
                    this.form.order_item_code = 1;
                    this.selectTypeCode();
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
                this.getProduct();
            },
            getTypeCodes(){
                axios.get('/api/get-fg-type-codes')
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
                axios.get('/api/get-material-classification/'+this.form.product_classification_id)
                .then((res) => {
                    this.classifications = res.data;
                })
                .catch((error) => {
                    console.log(error)
                })
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
            getProductCustomers(){
                axios.get('/api/get-product-customers')
                .then((res) => {
                    this.product_customers = res.data;
                })
                .catch((error) => {
                    console.log(error)
                });
            },
        },
        created() {
            this.getProduct();
            this.getTypeCodes();
            this.pageType();
            this.getProductCustomers();
        },
        computed: {
            titleHeader(){
                return this.$product_flows.find(flow => flow.id == this.$route.params.product_flow_id).flow_name;
            }
        },
        watch:{
            $route (to, from){
                this.pageType();
            }
        }
    }
</script>
