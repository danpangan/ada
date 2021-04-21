<template>
    <div class="container">
        <div class="content-header mb-3" style="border-bottom: 1px solid black;">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h4 class="m-0 text-dark"><strong>Inventory Management</strong></h4>
                    </div>
                    <div class="col-sm-6">
                        <button type="button" class="btn button-standard float-right" @click="$router.push('/finish-products')">
                            <i class="el-icon-arrow-left"></i> Lists
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-12">
                <form @submit.prevent="createProduct()">
                    <el-card class="box-card" shadow="hover">
                        <div slot="header">
                            <span>Product Information</span>
                        </div>
                        <el-form ref="form" :model="form" label-width="15%" :label-position="`right`">
                            <el-form-item label="SS Reference No.">
                                <el-col :span="12">
                                    <el-input :spna="12" v-model="form.ss_reference_no"></el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Order Item Code">
                                <el-col :span="12">
                                    <el-input :spna="12" v-model="form.order_item_code"></el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Part Number">
                                <el-col :span="12">
                                    <el-input :spna="12" v-model="form.product_part_number"></el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Part Name">
                                <el-col :span="12">
                                    <el-input :span="12" v-model="form.product_name"></el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Category">
                                <el-col :span="6">
                                    <el-select v-model="form.product_category_id" placeholder="Category">
                                        <el-option
                                        v-for="product_category in $product_categories"
                                        :key="product_category.id"
                                        :label="product_category.category_name"
                                        :value="product_category.id">
                                        </el-option>
                                    </el-select>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Type Code">
                                <el-col :span="6">
                                    <el-select v-model="form.product_type_code_id" @change="selectTypeCode" :disabled="disableTypeCode" placeholder="Type Code">
                                        <el-option
                                        v-for="type_code in type_codes"
                                        :key="type_code.id"
                                        :label="typeCodeLabel(type_code.type_code, type_code.type_code_description)"
                                        :value="type_code.id">
                                        </el-option>
                                    </el-select>
                                </el-col>
                                <el-col :span="6" v-show="form.product_type_code_id">
                                    <el-tooltip class="item" effect="dark" content="Add product classification" placement="top">
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
                                    <el-select v-model="form.product_classification_id" :disabled="classifications.length == 0" placeholder="Classification">
                                        <el-option
                                        v-for="classification in classifications"
                                        :key="classification.id"
                                        :label="classification.material_classification_name"
                                        :value="classification.id">
                                        </el-option>
                                    </el-select>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Quantity">
                                <el-col :span="6">
                                    <el-input v-model="form.product_stock_quantity"></el-input>
                                </el-col>
                                <el-col :span="6">
                                    <el-select v-model="form.product_stock_quantity_unit" placeholder="Unit">
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
                                    <el-select v-model="form.product_customer" placeholder="Customers" class="block">
                                        <el-option
                                        v-for="product_customer in product_customers"
                                        :key="product_customer.id"
                                        :label="product_customer.client_name"
                                        :value="product_customer.id">
                                        </el-option>
                                    </el-select>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Location">
                                <el-col :span="12">
                                    <el-input 
                                        type="textarea" 
                                        v-model="form.product_location" 
                                        resize="none"
                                        :autosize="{ minRows: 4, maxRows: 6}">
                                    </el-input>
                                </el-col>
                            </el-form-item>
                        </el-form>
                    </el-card>
                    <div class="outside-card-button">
                        <el-button type="primary" v-on:click="createProduct"><i class="el-icon-plus"></i> Create</el-button>
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
                    product_part_number: '', 
                    product_name: '', 
                    product_category_id: '',
                    product_classification_id: '',
                    product_type_code_id: '',
                    product_stock_quantity: '',
                    product_stock_quantity_unit: '',
                    product_location: '',
                    ss_reference_no: '',
                    order_item_code: '',
                    product_customer: '',
                }),
                classificationForm: new Form({
                    type_code_id: '',
                    material_classification_name: '',
                }),
                type_codes: '',
                categories: [],
                classifications: [],
                isVisiblelassificationsSetting: false,
                disableTypeCode: false,
                product_customers: [],
            }
        },
        methods: {
            createProduct(){
                Swal.fire({
                    text: 'Are you sure you want to register new product?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Register',
                    width: '25rem',
                })
                .then((result) => {
                    if(result.value){
                        this.form.post('api/product')
                        .then((res) => {
                            this.form.reset();
                            Swal.fire(
                                'Registered!',
                                'Product successfully registered.',
                                'success'
                            )
                        })
                        .catch(error => {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops!',
                                text: 'Product registration failed.',
                            })
                            console.log(error)
                        });
                    }
                })
            },
            getTypeCodes(){
                axios.get('api/get-fg-type-codes')
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
                this.classificationForm.type_code_id = this.form.product_type_code_id;
                axios.get('api/get-material-classification/'+this.form.product_type_code_id)
                .then((res) => {
                    this.form.product_classification_id = '';
                    this.classifications = res.data;
                })
                .catch((error) => {
                    console.log(error)
                })
            },
            classificationsSetting(){
                if(this.isVisiblelassificationsSetting == false) this.isVisiblelassificationsSetting = true;
                else this.isVisiblelassificationsSetting = false;

                if(this.disableTypeCode == false) this.disableTypeCode = true;
                else this.disableTypeCode = false;
            },
            createNewClassification(){
                Swal.fire({
                    text: 'Are you sure you want to register new product classification?',
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
                                'Product classification successfully registered.',
                                'success'
                            )
                        })
                        .catch(error => {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops!',
                                text: 'Product classification registration failed.',
                            })
                            console.log(error)
                        });
                    }
                })
            },
            getProductCustomers(){
                axios.get('api/get-product-customers')
                .then((res) => {
                    this.product_customers = res.data;
                })
                .catch((error) => {
                    console.log(error)
                });
            }
        },
        created(){
            this.getTypeCodes();
            this.getProductCustomers();
        },
    }
</script>
