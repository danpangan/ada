<template>
    <div class="container">
        <div class="content-header mb-3" style="border-bottom: 1px solid black;">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h4 class="m-0 text-dark" v-show="isEdit"><strong>Edit Product</strong></h4>
                        <h4 class="m-0 text-dark" v-show="!isEdit"><strong>View Product</strong></h4>
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
                <form @submit.prevent="updateProduct()">
                    <el-card class="box-card mb-3" shadow="hover">
                        <div slot="header">
                            <span>Product Information</span>
                        </div>
                        <el-form ref="form" :model="form" label-width="15%" :label-position="`right`">
                            <el-form-item label="SS Reference No.">
                                <el-col :span="12">
                                    <el-input :spna="12" v-model="form.ss_reference_no" :disabled="readonly"></el-input>
                                </el-col>
                                <el-col  :span="12" style="text-align: right" v-show="!isEdit">
                                    <el-dropdown>
                                        <el-button type="primary">
                                            Transaction<i class="el-icon-arrow-down el-icon--right"></i>
                                        </el-button>
                                        <el-dropdown-menu slot="dropdown">
                                            <div v-for="product_flow in $product_flows" :key="product_flow.id">
                                                <el-dropdown-item>
                                                    <router-link :to="'/product-flow/'+product_flow.id+'/product-id/'+$route.params.product_id">
                                                        <p class="dropdown-item-links">{{ product_flow.flow_name }}</p>
                                                    </router-link>
                                                </el-dropdown-item>
                                            </div>
                                        </el-dropdown-menu>
                                    </el-dropdown>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Order Item Code">
                                <el-col :span="12">
                                    <el-input :spna="12" v-model="form.order_item_code" :disabled="readonly"></el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Part Number">
                                <el-col :span="12">
                                    <el-input v-model="form.product_part_number" :disabled="readonly">
                                    </el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Part Name">
                                <el-col :span="12">
                                    <el-input v-model="form.product_name" :disabled="readonly">
                                    </el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Type Code">
                                <el-col :span="6">
                                    <el-select v-model="form.product_type_code_id" @change="selectTypeCode" :disabled="disableTypeCode || readonly" placeholder="Type Code">
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
                                        <i :class="isVisiblelassificationsSetting ? 'icon el-icon-minus pointer ml-3' : 'icon el-icon-plus pointer ml-3'" @click="classificationsSetting" v-show="isEdit"></i>
                                    </el-tooltip>
                                </el-col>
                            </el-form-item>
                            <div v-show="isVisiblelassificationsSetting">
                                <hr>
                                <el-form-item label="Add Product Classification">
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
                                    <el-select v-model="form.product_classification_id" :disabled="readonly" placeholder="Classification">
                                        <el-option
                                        v-for="classification in classifications"
                                        :key="classification.id"
                                        :label="classification.material_classification_name"
                                        :value="classification.id">
                                        </el-option>
                                    </el-select>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Last In" v-show="!isEdit">
                                <el-col :span="12">
                                    <el-input :value="dateFormat(form.product_last_in)" :disabled="readonly" v-show="!isEdit"></el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Last Out" v-show="!isEdit">
                                <el-col :span="12">
                                    <el-input :value="dateFormat(form.product_last_out)" :disabled="readonly" v-show="!isEdit"></el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Quantity" v-show="!isEdit">
                                <el-col :span="4">
                                    <el-input v-model="form.product_stock_quantity" :disabled="readonly"></el-input>
                                </el-col>
                                <el-col :span="3">
                                    <el-select v-model="form.product_stock_quantity_unit" :disabled="readonly" placeholder="Unit">
                                        <el-option
                                        v-for="unit in $units_quantity"
                                        :key="unit.id"
                                        :label="unit.unit_name"
                                        :value="unit.id">
                                        </el-option>
                                    </el-select>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Customer">
                                <el-col :span="12">
                                    <el-select v-model="form.product_customer" :disabled="readonly" placeholder="Customers" class="block">
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
                                        :disabled="readonly"
                                        :autosize="{ minRows: 4, maxRows: 6}">
                                    </el-input>
                                </el-col>
                            </el-form-item>
                        </el-form>
                        <div class="outside-card-button" v-if="isEdit">
                            <el-button type="primary" v-on:click="updateProduct">
                                <i class="el-icon-circle-check"></i> Update
                            </el-button>
                            <el-button type="warning" v-on:click="resetFields">
                                <i class="el-icon-refresh-left"></i> Reset
                            </el-button>
                            <el-button type="danger" @click="$router.back(-1)"><i class="el-icon-close">
                                </i> Cancel
                            </el-button>
                        </div>
                        <div class="outside-card-button" v-else>
                            <router-link :to="'/update-product/'+$route.params.product_id">
                                <el-button type="primary" @click="toEdit"><i class="el-icon-edit-outline"></i> Edit</el-button>
                            </router-link>
                        </div>
                        <el-col class="text-center mt-1 mb-3">
                            <el-button class="pdf" size="mini" @click="exportPDFProduct" v-show="!isEdit" round><i class="fas fa-file-pdf"></i> Download</el-button>
                        </el-col>
                    </el-card>
                </form>
            </div>
        </div>
        <div class="row justify-content-center mt-3" v-show="!isEdit">
            <div class="col-12">
                <div class="card">
                    <div class="card-header card-adamay-blue">
                        <h3 class="card-title">Sales Sheet Reference History</h3>
                    </div>
                    <div class="card-body">
                        <el-col :span="6">
                            <el-select v-model="filter" @change="selectFilter" clearable placeholder="Select Filter">
                                <el-option
                                v-for="material_flow in $material_flows"
                                :key="material_flow.id"
                                :label="material_flow.material_flow_name"
                                :value="material_flow.id">
                                </el-option>
                            </el-select>
                        </el-col>
                        <el-table
                            v-model="trackingTable"
                            row-key="trackingTableId"
                            :data="trackingTable"
                            :default-sort = "{prop: 'id', order: 'descending'}"
                            v-loading="loading"
                            element-loading-text="Loading..."
                            element-loading-spinner="el-icon-loading"
                            style="width: 100%">
                            <el-table-column
                                prop=""
                                label="Column 1">
                            </el-table-column>
                            <el-table-column
                                prop=""
                                label="Column 2">
                            </el-table-column>
                            <el-table-column
                                prop=""
                                label="Column 3">
                            </el-table-column>
                        </el-table>
                        <el-pagination
                            @size-change="handleSizeChange"
                            @current-change="handleCurrentChange"
                            :current-page.sync="pageInfo.current_page"
                            :page-sizes="[10, 20, 50, 100]"
                            :page-size="10"
                            layout="sizes, prev, pager, next"
                            @next-click="handleNextPage"
                            @prev-click="handlePrevPage"
                            :total="pageInfo.total">
                        </el-pagination>
                    </div>
                    <el-col class="text-center mb-3">
                        <el-button class="pdf" size="mini" @click="exportSSReferenceHistory" round><i class="fas fa-file-pdf"></i> Download</el-button>
                    </el-col>
                </div>
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
                    classificationForm: '',
                }),
                type_codes: '',
                classifications: [],
                suppliers: [],
                isEdit: false,

                trackingTable: [],
                pageInfo: {},
                perPage: 10,
                loading: '',
                sortParameter: {
                    "column":{},
                    "prop":"id",
                    "order":"ascending"
                },
                filter: '',
                tracking_details: [],
                isVisiblelassificationsSetting: false,
                disableTypeCode: false,
                product_customers: [],
            }
        },
        methods: {
            updateProduct() {
                Swal.fire({
                    text: 'Are you sure you want to update product?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Update',
                    width: '25rem',
                })
                .then((result) => {
                    if(result.value){
                        this.isLoading = true;
                        this.form.put('/api/product/'+this.$route.params.product_id)
                        .then((res) => {
                            Swal.fire(
                                'Updated!',
                                'Product successfully updated.',
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
                this.getProduct();
            },
            typeCodeLabel(type_code, classification){
                return type_code + ' - ' + classification;
            },
            selectTypeCode(){
                this.classificationForm.type_code_id = this.form.product_type_code_id;
                axios.get('/api/get-material-classification/'+this.form.product_type_code_id)
                .then((res) => {
                    this.classifications = res.data;
                })
                .catch((error) => {
                    console.log(error)
                })
            },
            pageType(){
                this.isEdit = false;
                if(window.location.href.indexOf("update") > -1) this.isEdit = true;
            },
            toEdit(){
                this.isEdit = true;
            },
            getProductTracking(){
                axios.get('/api/get-sc-material-tracking', {
                    params: { 
                        take: this.perPage,
                        showDeleted: this.showDeleted, 
                        sortProp: this.sortParameter.prop, 
                        sortOrder: this.sortParameter.order,
                        sc_material_id: this.$route.params.product_id,
                        flow: this.filter,
                    } 
                })
                .then((res) => {
                    this.pageInfo = res.data;
                    if(this.$currentPageSCMaterialsTracking > 0){
                        this.handleCurrentChange(this.$currentPageSCMaterialsTracking);
                    }
                    else{
                        this.trackingTable = res.data.data;
                        this.pageInfo.current_page = this.$currentPageSCMaterialsTracking;
                        this.loading = false;   
                    }
                })
                .catch((error) => {
                    console.log(error)
                })
            },
            handleSizeChange(val) {
                this.clearTable();
                this.loading = true;
                this.perPage = val;
                axios.get("/api/get-sc-material-tracking", { 
                    params: { 
                        take: this.perPage,
                        showDeleted: this.showDeleted, 
                        sortProp: this.sortParameter.prop, 
                        sortOrder: this.sortParameter.order,
                        sc_material_id: this.$route.params.product_id,
                        flow: this.filter,
                    } 
                })
                .then((data) => {
                    this.trackingTable = data.data.data;
                    this.pageInfo = data.data;
                    this.loading = false;
                })
                .catch((error) => {
                    this.loading = false;
                });
                this.currentPageGlobal();
            },
            handleCurrentChange(val) {
                this.clearTable();
                this.loading = true;
                axios.get(this.pageInfo.path+'?page='+val, { 
                    params: { 
                        take: this.perPage,
                        showDeleted: this.showDeleted, 
                        sortProp: this.sortParameter.prop, 
                        sortOrder: this.sortParameter.order,
                        sc_material_id: this.$route.params.product_id,
                        flow: this.filter,
                    } 
                })
                .then((data) => {
                    this.trackingTable = data.data.data;
                    this.pageInfo = data.data;
                    this.loading = false;
                })
                .catch((error) => {
                    this.loading = false;
                });
                this.currentPageGlobal();
            },
            handleNextPage(val) {
                this.clearTable();
                this.loading = true;
                axios.get(this.pageInfo.next_page_url, { 
                    params: { 
                        take: this.perPage,
                        showDeleted: this.showDeleted, 
                        sortProp: this.sortParameter.prop, 
                        sortOrder: this.sortParameter.order,
                        sc_material_id: this.$route.params.product_id,
                        flow: this.filter,
                    } 
                })
                .then((data) => {
                    this.trackingTable = data.data.data;
                    this.pageInfo = data.data;
                    this.loading = false;
                })
                .catch((error) => {
                    this.loading = false;
                });
                this.currentPageGlobal();
            },
            handlePrevPage(val) {
                this.clearTable();
                this.loading = true;
                this.currentPageGlobal();
            },
            currentPageGlobal(){
                Vue.prototype.$currentPageSCMaterialsTracking = this.pageInfo.current_page;
                this.currentPage = this.pageInfo.current_page;
            },
            clearTable(){
                this.trackingTable = [];
            },
            selectFilter(){
                axios.get('/api/get-sc-material-tracking', {
                    params: { 
                        take: this.perPage,
                        showDeleted: this.showDeleted, 
                        sortProp: this.sortParameter.prop, 
                        sortOrder: this.sortParameter.order,
                        sc_material_id: this.$route.params.product_id,
                        flow: this.filter,
                    } 
                })
                .then((res) => {
                    this.pageInfo = res.data;
                    if(this.$currentPageSCMaterialsTracking > 0){
                        this.handleCurrentChange(this.$currentPageSCMaterialsTracking);
                    }
                    else{
                        this.trackingTable = res.data.data;
                        this.pageInfo.current_page = this.$currentPageSCMaterialsTracking;
                        this.loading = false;   
                    }
                })
                .catch((error) => {
                    console.log(error)
                })
            },
            materialFlow(flow){
                return this.$material_flows.find(flow_element => flow_element.id == flow).material_flow_name;
            },
            selectFilter(id){
                axios.get('/api/get-sc-material-tracking', {
                    params: { 
                        take: this.perPage,
                        showDeleted: this.showDeleted, 
                        sortProp: this.sortParameter.prop, 
                        sortOrder: this.sortParameter.order,
                        sc_material_id: this.$route.params.product_id,
                        flow: id,
                    } 
                })
                .then((res) => {
                    this.pageInfo = res.data;
                    if(this.$currentPageSCMaterialsTracking > 0){
                        this.handleCurrentChange(this.$currentPageSCMaterialsTracking);
                    }
                    else{
                        this.trackingTable = res.data.data;
                        this.pageInfo.current_page = this.$currentPageSCMaterialsTracking;
                        this.loading = false;   
                    }
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
                    text: 'Are you sure you want to register new material classification?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Register',
                    width: '25rem',
                })
                .then((result) => {
                    if(result.value){
                        this.classificationForm.post('/api/add-material-classification/'+this.classificationForm.product_type_code_id)
                        .then((res) => {
                            this.selectTypeCode();
                            this.isVisiblelassificationsSetting = false;
                            this.disableTypeCode = false;
                            this.classificationForm.material_classification_name = '';
                            this.form.product_classification_id = '';
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
            exportPDFProduct(){
                let product_id = this.$route.params.product_id;
                window.open('/export-pdf-product?product_id='+product_id);
            },
            exportSSReferenceHistory(){
                let product_id = this.$route.params.product_id;
                window.open('/export-pdf-product-ss-reference-history?product_id='+product_id+'&filter='+this.filter);
            },
            dateFormat(date){
                if(date === null) return '-';
                else return moment(date).format('MM/DD/YYYY HH:mm');
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
            readonly(){
                if(!this.isEdit) return true;
            },
        },
        watch:{
            $route (to, from){
                this.pageType();
            }
        }
    }
</script>
