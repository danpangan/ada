<template>
    <div class="container">
        <div class="content-header mb-3" style="border-bottom: 1px solid black;">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h4 class="m-0 text-dark" v-show="isEdit"><strong>Edit Material</strong></h4>
                        <h4 class="m-0 text-dark" v-show="!isEdit"><strong>View Material</strong></h4>
                    </div>
                    <div class="col-sm-6">
                        <button type="button" class="btn button-standard float-right" @click="$router.push('/raw-materials')">
                            <i class="el-icon-arrow-left"></i> Lists
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-12">
                <form @submit.prevent="updateRawMaterial()">
                    <el-card class="box-card mb-3" shadow="hover">
                        <div slot="header">
                            <span>Raw Material Information</span>
                        </div>
                        <el-form ref="form" :model="form" label-width="15%" :label-position="`right`">
                            <el-form-item label="Material Description">
                                <el-col :span="12">
                                    <el-input 
                                        type="textarea" 
                                        v-model="form.material_description" 
                                        resize="none"
                                         :disabled="readonly"
                                        :autosize="{ minRows: 4, maxRows: 6}">
                                    </el-input>
                                </el-col>
                                <el-col  :span="12" style="text-align: right" v-show="!isEdit">
                                    <el-dropdown>
                                        <el-button type="primary">
                                            Transaction<i class="el-icon-arrow-down el-icon--right"></i>
                                        </el-button>
                                        <el-dropdown-menu slot="dropdown">
                                            <div v-for="material_flow in $material_flows_title" :key="material_flow.id">
                                                <el-dropdown-item>
                                                    <router-link :to="'/raw-material-flow/'+material_flow.id+'/material-id/'+$route.params.material_id">
                                                        <p class="dropdown-item-links">{{ material_flow.material_flow_name }}</p>
                                                    </router-link>
                                                </el-dropdown-item>
                                            </div>
                                        </el-dropdown-menu>
                                    </el-dropdown>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Type Code">
                                <el-col :span="6">
                                    <el-select v-model="form.material_type_id" @change="selectTypeCode" :disabled="disableTypeCode || readonly" placeholder="Type Code">
                                        <el-option
                                        v-for="type_code in type_codes"
                                        :key="type_code.id"
                                        :label="typeCodeLabel(type_code.type_code, type_code.type_code_description)"
                                        :value="type_code.id">
                                        </el-option>
                                    </el-select>
                                </el-col>
                                <el-col :span="6" v-show="form.material_type_id">
                                    <el-tooltip class="item" effect="dark" content="Add material classification" placement="top">
                                        <i :class="isVisiblelassificationsSetting ? 'icon el-icon-minus pointer ml-3' : 'icon el-icon-plus pointer ml-3'" @click="classificationsSetting" v-show="isEdit"></i>
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
                                    <el-select v-model="form.material_classification_id" :disabled="readonly" placeholder="Classification">
                                        <el-option
                                        v-for="classification in classifications"
                                        :key="classification.id"
                                        :label="classification.material_classification_name"
                                        :value="classification.id">
                                        </el-option>
                                    </el-select>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Dimension">
                                <el-col :span="3">
                                    <el-input v-model="form.material_length" :disabled="readonly"></el-input>
                                </el-col>
                                <el-col :span="3">
                                    <el-select v-model="form.material_length_unit" :disabled="readonly" placeholder="Unit">
                                        <el-option
                                        v-for="unit in $units_dimension"
                                        :key="unit.id"
                                        :label="unit.unit_name"
                                        :value="unit.unit_name">
                                        </el-option>
                                    </el-select>
                                </el-col>
                                <el-col :span="3">
                                    <el-input v-model="form.material_width" :disabled="readonly"></el-input>
                                </el-col>
                                <el-col :span="3">
                                    <el-select v-model="form.material_width_unit" :disabled="readonly" placeholder="Unit">
                                        <el-option
                                        v-for="unit in $units_dimension"
                                        :key="unit.id"
                                        :label="unit.unit_name"
                                        :value="unit.unit_name">
                                        </el-option>
                                    </el-select>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Last In" v-show="!isEdit">
                                <el-col :span="12">
                                    <el-input :value="dateFormat(form.material_last_in)" :disabled="readonly" v-show="!isEdit"></el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Last Out" v-show="!isEdit">
                                <el-col :span="12">
                                    <el-input :value="dateFormat(form.material_last_out)" :disabled="readonly" v-show="!isEdit"></el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Quantity" v-show="!isEdit">
                                <el-col :span="4">
                                    <el-input v-model="form.material_quantity" :disabled="readonly"></el-input>
                                </el-col>
                                <el-col :span="3">
                                    <el-select v-model="form.material_quantity_unit" :disabled="readonly || isRoll" placeholder="Unit">
                                        <el-option
                                        v-for="quantity_unit in $units_quantity"
                                        :key="quantity_unit.id"
                                        :label="quantity_unit.unit_name"
                                        :value="quantity_unit.id">
                                        </el-option>
                                    </el-select>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Location">
                                <el-col :span="12">
                                    <el-input 
                                        type="textarea" 
                                        v-model="form.material_location" 
                                        resize="none"
                                        :disabled="readonly"
                                        :autosize="{ minRows: 4, maxRows: 6}">
                                    </el-input>
                                </el-col>
                            </el-form-item>
                        </el-form>
                        <div class="outside-card-button" v-if="isEdit">
                            <el-button type="primary" v-on:click="updateRawMaterial">
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
                            <router-link :to="'/update-raw-material/'+$route.params.material_id">
                                <el-button type="primary" @click="toEdit"><i class="el-icon-edit-outline"></i> Edit</el-button>
                            </router-link>
                        </div>
                        <el-col class="text-center mt-1 mb-3">
                            <el-button class="pdf" size="mini" @click="exportPDFRawMaterial" v-show="!isEdit" round><i class="fas fa-file-pdf"></i> Download</el-button>
                        </el-col>
                    </el-card>
                </form>
            </div>
        </div>

        <div class="row justify-content-center mt-3" v-show="!isEdit">
            <div class="col-12">
                <div class="card">
                    <div class="card-header card-adamay-blue">
                        <h3 class="card-title">Material Tracking</h3>
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
                                prop="conversion_id"
                                label="Transaction">
                                <template slot-scope="scope">
                                    {{ materialFlow(trackingTable[scope.$index].material_flow) }}
                                </template>
                            </el-table-column>
                            <el-table-column
                                prop="material_code"
                                label="Material Code">
                            </el-table-column>
                            <el-table-column
                                label="Supplier">
                                <template slot-scope="scope">
                                    {{ supplierName(trackingTable[scope.$index].supplier_code, trackingTable[scope.$index].supplier_name) }}
                                </template>
                            </el-table-column>
                            <el-table-column
                                label="Quantity">
                                <template slot-scope="scope">
                                    {{ materialQuantity(trackingTable[scope.$index].material_quantity, trackingTable[scope.$index].material_quantity_unit) }}
                                </template>
                            </el-table-column>
                            <el-table-column
                                align="right"
                                label="Amount">
                                <template slot-scope="scope">
                                    {{ materialAmount(trackingTable[scope.$index].material_unit_amount, trackingTable[scope.$index].material_unit_amount_currency) }}
                                </template>
                            </el-table-column>
                            <el-table-column
                                label="Date">
                                <template slot-scope="scope">
                                    {{ dateFormat(trackingTable[scope.$index].created_at) }}
                                </template>
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
                        <el-button class="pdf" size="mini" @click="exportPDFMaterialTracking" round><i class="fas fa-file-pdf"></i> Download</el-button>
                    </el-col>
                </div>
            </div>
        </div>

        <div class="row justify-content-center mt-3" v-show="!isEdit">
            <div class="col-12">
                <div class="card">
                    <div class="card-header card-adamay-blue">
                        <h3 class="card-title">Suppliers</h3>
                    </div>
                    <div class="card-body">
                        <el-table
                            v-model="materialSuppliersTable"
                            row-key="materialSuppliersTableId"
                            :data="materialSuppliersTable"
                            :default-sort = "{prop: 'id', order: 'descending'}"
                            v-loading="loading"
                            element-loading-text="Loading..."
                            element-loading-spinner="el-icon-loading"
                            style="width: 100%">
                            <el-table-column
                                prop="supplier_code"
                                label="Supplier Code">
                            </el-table-column>
                            <el-table-column
                            prop="supplier_name"
                                label="Supplier Name">
                            </el-table-column>
                            <el-table-column
                                prop="material_code"
                                label="Material Code">
                            </el-table-column>
                            <el-table-column
                                label="Quantity (In)">
                                <template slot-scope="scope">
                                    {{ supplierMaterialQuantity(materialSuppliersTable[scope.$index].id) }}
                                </template>
                            </el-table-column>
                            <el-table-column
                                align="right"
                                label="Amount">
                                <template slot-scope="scope">
                                    {{ supplierMaterialAmount(materialSuppliersTable[scope.$index].id) }}
                                </template>
                            </el-table-column>
                            <el-table-column
                                label="Last Purchase">
                                <template slot-scope="scope">
                                    {{ dateFormat(materialSuppliersTable[scope.$index].created_at) }}
                                </template>
                            </el-table-column>
                        </el-table>
                        <el-pagination
                            @size-change="materialSuppliersHandleSizeChange"
                            @current-change="materialSuppliersHandleCurrentChange"
                            :current-page.sync="materialSuppliersPageInfo.current_page"
                            :page-sizes="[10, 20, 50, 100]"
                            :page-size="10"
                            layout="sizes, prev, pager, next"
                            @next-click="materialSuppliersHandleNextPage"
                            @prev-click="materialSuppliersHandlePrevPage"
                            :total="materialSuppliersPageInfo.total">
                        </el-pagination>
                    </div>
                    <el-col class="text-center mb-3">
                        <el-button class="pdf" size="mini" @click="exportPDFMaterialSuppliers" round><i class="fas fa-file-pdf"></i> Download</el-button>
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
                    material_description: '',
                    material_type_id: '',
                    material_classification_id: '',
                    material_length: '',
                    material_length_unit: '',
                    material_width: '',
                    material_width_unit: '',
                    supplier_material_id: '',
                    material_quantity: '',
                    material_quantity_unit: '',
                    material_unit_amount: '',
                    material_unit_amount_currency: '',
                    material_location: '',
                    material_last_in: '',
                    material_last_out: '',
                    supplier_id: '',
                    material_code: '',
                    supplier_name: '',
                }),
                classificationForm: new Form({
                    type_code_id: '',
                    classificationForm: '',
                }),
                type_codes: '',
                categories: [],
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
                materialSuppliersTable: [],
                materialSuppliersPageInfo: {},
                materialSuppliersPerPage: 10,
                materialSuppliersLoading: '',
                materialSuppliersSortParameter: {
                    "column":{},
                    "prop":"id",
                    "order":"ascending"
                },
                tracking_details: [],
                isVisiblelassificationsSetting: false,
                disableTypeCode: false,
                isRoll: false,
            }
        },
        methods: {
            updateRawMaterial() {
                Swal.fire({
                    text: 'Are you sure you want to update material?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Update',
                    width: '25rem',
                })
                .then((result) => {
                    if(result.value){
                        this.isLoading = true;
                        this.form.put('/api/material/'+this.$route.params.material_id)
                        .then((res) => {
                            Swal.fire(
                                'Updated!',
                                'Material successfully updated.',
                                'success'
                            )
                            this.getMaterial();
                        })
                        .catch(error => {
                            console.log(error)
                        });
                        this.isLoading = false;
                    }
                })
            },
            getMaterial(){
                axios.get('/api/material/'+this.$route.params.material_id)
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
                this.getMaterial();
            },
            getTypeCodes(){
                axios.get('/api/get-rm-type-codes')
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
                this.isRoll = false;
                this.classificationForm.type_code_id = this.form.material_type_id;
                if(this.form.material_type_id == 8){
                    this.form.material_quantity_unit = 3;
                    this.isRoll = true;
                }
                axios.get('/api/get-material-classification/'+this.form.material_type_id)
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

            getMaterialTracking(){
                axios.get('/api/get-material-tracking', {
                    params: { 
                        take: this.perPage,
                        showDeleted: this.showDeleted, 
                        sortProp: this.sortParameter.prop, 
                        sortOrder: this.sortParameter.order,
                        material_id: this.$route.params.material_id,
                        flow: this.filter,
                    } 
                })
                .then((res) => {
                    this.pageInfo = res.data;
                    if(this.$currentPageMaterialsTracking > 0){
                        this.handleCurrentChange(this.$currentPageMaterialsTracking);
                    }
                    else{
                        this.trackingTable = res.data.data;
                        this.pageInfo.current_page = this.$currentPageMaterialsTracking;
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
                axios.get("/api/get-material-tracking", { 
                    params: { 
                        take: this.perPage,
                        showDeleted: this.showDeleted, 
                        sortProp: this.sortParameter.prop, 
                        sortOrder: this.sortParameter.order,
                        material_id: this.$route.params.material_id,
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
                        material_id: this.$route.params.material_id,
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
                        material_id: this.$route.params.material_id,
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
                Vue.prototype.$currentPageMaterialsTracking = this.pageInfo.current_page;
                this.currentPage = this.pageInfo.current_page;
            },
            clearTable(){
                this.trackingTable = [];
            },
            selectFilter(){
                axios.get('/api/get-material-tracking', {
                    params: { 
                        take: this.perPage,
                        showDeleted: this.showDeleted, 
                        sortProp: this.sortParameter.prop, 
                        sortOrder: this.sortParameter.order,
                        material_id: this.$route.params.material_id,
                        flow: this.filter,
                    } 
                })
                .then((res) => {
                    this.pageInfo = res.data;
                    if(this.$currentPageMaterialsTracking > 0){
                        this.handleCurrentChange(this.$currentPageMaterialsTracking);
                    }
                    else{
                        this.trackingTable = res.data.data;
                        this.pageInfo.current_page = this.$currentPageMaterialsTracking;
                        this.loading = false;   
                    }

                    this.employees = res.data.employees;
                })
                .catch((error) => {
                    console.log(error)
                })
            },
            materialFlow(flow){
                return this.$material_flows.find(flow_element => flow_element.id == flow).material_flow_name;
            },
            supplierName(supplier_code, supplier_name){
                return supplier_code + ' - ' + supplier_name;
            },
            materialQuantity(quantity, unit){
                for (let i = 0; i < this.$units_quantity.length; i++) {
                    if(this.$units_quantity[i].id == unit) return quantity + ' ' + this.$units_quantity[i].unit_name;
                }
            },
            materialAmount(amount, currency){
                if(parseInt(amount) == 0) return '-';
                else return amount + ' ' + currency;
            },
            dateFormat(date){
                if(date === null) return '-';
                else return moment(date).format('MM/DD/YYYY HH:mm');
            },

            getMaterialSuppliers(){
                axios.get('/api/get-material-suppliers/'+this.$route.params.material_id, {
                    params: { 
                        take: this.perPage,
                        showDeleted: this.showDeleted, 
                        sortProp: this.materialSuppliersSortParameter.prop, 
                        sortOrder: this.materialSuppliersSortParameter.order,
                        material_id: this.$route.params.material_id,
                    } 
                })
                .then((res) => {
                    this.materialSuppliersPageInfo = res.data.suppliers;
                    this.tracking_details = res.data.tracking_details;
                    if(this.$currentPageMaterialSuppliers > 0){
                        this.handleCurrentChange(this.$currentPageMaterialSuppliers);
                    }
                    else{
                        this.materialSuppliersTable = res.data.suppliers.data;
                        this.materialSuppliersPageInfo.current_page = this.$currentPageMaterialSuppliers;
                        this.materialSuppliersLoading = false;   
                    }
                })
                .catch((error) => {
                    console.log(error)
                })
            },
            materialSuppliersHandleSizeChange(val) {
                this.clearMaterialSuppliersTable();
                this.materialSuppliersLoading = true;
                this.perPage = val;
                axios.get('/api/get-material-suppliers/'+this.$route.params.material_id, { 
                    params: { 
                        take: this.perPage,
                        showDeleted: this.showDeleted, 
                        sortProp: this.materialSuppliersSortParameter.prop, 
                        sortOrder: this.materialSuppliersSortParameter.order,
                        material_id: this.$route.params.material_id,
                        flow: this.filter,
                    } 
                })
                .then((res) => {
                    this.materialSuppliersTable = res.data.suppliers.data;
                    this.materialSuppliersPageInfo = res.data.suppliers;
                    this.materialSuppliersLoading = false;
                })
                .catch((error) => {
                    this.materialSuppliersLoading = false;
                });
                this.materialSuppliersCurrentPageGlobal();
            },
            materialSuppliersHandleCurrentChange(val) {
                this.clearMaterialSuppliersTable();
                this.materialSuppliersLoading = true;
                axios.get(this.materialSuppliersPageInfo.path+'?page='+val, { 
                    params: { 
                        take: this.perPage,
                        showDeleted: this.showDeleted, 
                        sortProp: this.materialSuppliersSortParameter.prop, 
                        sortOrder: this.materialSuppliersSortParameter.order,
                        material_id: this.$route.params.material_id,
                    } 
                })
                .then((res) => {
                    this.materialSuppliersTable = res.data.suppliers.data;
                    this.materialSuppliersPageInfo = res.data.suppliers;
                    this.materialSuppliersLoading = false;
                })
                .catch((error) => {
                    this.materialSuppliersLoading = false;
                });
                this.materialSuppliersCurrentPageGlobal();
            },
            materialSuppliersHandleNextPage(val) {
                this.clearMaterialSuppliersTable();
                this.materialSuppliersLoading = true;
                axios.get(this.materialSuppliersPageInfo.next_page_url, { 
                    params: { 
                        take: this.perPage,
                        showDeleted: this.showDeleted, 
                        sortProp: this.materialSuppliersSortParameter.prop, 
                        sortOrder: this.materialSuppliersSortParameter.order,
                        material_id: this.$route.params.material_id,
                    } 
                })
                .then((res) => {
                    this.materialSuppliersTable = res.data.suppliers.data;
                    this.materialSuppliersPageInfo = res.data.suppliers;
                    this.materialSuppliersLoading = false;
                })
                .catch((error) => {
                    this.materialSuppliersLoading = false;
                });
                this.materialSuppliersCurrentPageGlobal();
            },
            materialSuppliersHandlePrevPage(val) {
                this.clearMaterialSuppliersTable();
                this.materialSuppliersLoading = true;
                this.materialSuppliersCurrentPageGlobal();
            },
            materialSuppliersCurrentPageGlobal(){
                Vue.prototype.$currentPageMaterialSuppliers = this.materialSuppliersPageInfo.current_page;
                this.currentPage = this.materialSuppliersPageInfo.current_page;
            },
            clearMaterialSuppliersTable(){
                this.materialSuppliersTable = [];
            },
            supplierMaterialQuantity(supplier_material_id){
                // let material_quantity_unit = _.find(this.tracking_details, { 'supplier_material_id': supplier_material_id }).material_quantity_unit;
                // return _.find(this.tracking_details, { 'supplier_material_id': supplier_material_id }).total_quantity + ' ' + _.find(this.$units_quantity, { 'id': material_quantity_unit }).unit_name;
                for (let i = 0; i < this.tracking_details.length; i++) {
                    if(this.tracking_details[i].supplier_material_id == supplier_material_id){
                        for (let j = 0; j < this.$units_quantity.length; j++) {
                            if(this.$units_quantity[j].id == this.tracking_details[i].material_quantity_unit) return this.tracking_details[i].total_quantity + ' ' + this.$units_quantity[j].unit_name;
                        }
                    }
                }
            },
            supplierMaterialAmount(supplier_material_id){
                // return _.find(this.tracking_details, { 'supplier_material_id': supplier_material_id }).material_unit_amount + ' ' + _.find(this.tracking_details, { 'supplier_material_id': supplier_material_id }).material_unit_amount_currency;
                for (let i = 0; i < this.tracking_details.length; i++) {
                    if(this.tracking_details[i].supplier_material_id == supplier_material_id) return this.tracking_details[i].material_unit_amount + ' ' + this.tracking_details[i].material_unit_amount_currency;
                }
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
                        this.classificationForm.post('/api/add-material-classification/'+this.classificationForm.type_code_id)
                        .then((res) => {
                            this.selectTypeCode();
                            this.isVisiblelassificationsSetting = false;
                            this.disableTypeCode = false;
                            this.classificationForm.material_classification_name = '';
                            this.form.material_classification_id = '';
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
            exportPDFRawMaterial(){
                let material_id = this.$route.params.material_id;
                window.open('/export-pdf-raw-material?material_id='+material_id);
            },
            exportPDFMaterialTracking(){
                let material_id = this.$route.params.material_id;
                window.open('/export-pdf-raw-material-tracking?material_id='+material_id+'&filter='+this.filter);
            },
            exportPDFMaterialSuppliers(){
                let material_id = this.$route.params.material_id;
                window.open('/export-pdf-raw-material-suppliers?material_id='+material_id);
            },
        },
        created() {
            this.getTypeCodes();
            this.getSuppliers();
            this.pageType();
            this.getMaterial();
            this.getMaterialTracking();
            this.getMaterialSuppliers();
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
