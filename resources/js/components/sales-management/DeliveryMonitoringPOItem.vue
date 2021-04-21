<template>
    <div class="container">
        <div class="content-header mb-3" style="border-bottom: 1px solid black;">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h4 class="m-0 text-dark"><strong>Sales Management</strong></h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="card">
                    <div class="card-header card-adamay-blue">
                        <h3 class="card-title">Delivery Monitoring - PO Item</h3>
                    </div>
                    <div class="card-body">
                        <h5 class="content-title">Purchase Order</h5>
                        <el-form ref="form" :model="form" label-width="15%" :label-position="`right`">
                            <el-form-item label="PO Number">
                                <el-col :span="6">
                                    <el-input v-model="form.dummy_length" :disabled="true"></el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Customer">
                                <el-col :span="12">
                                    <el-input v-model="form.dummy_length" :disabled="true"></el-input>
                                </el-col>
                            </el-form-item>
                            <h5 class="content-title mt-3">Purchase Order Item</h5>
                            <el-form-item label="Part Number">
                                <el-col :span="6">
                                    <el-input v-model="form.dummy_length" :disabled="true"></el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Item Name">
                                <el-col :span="6">
                                    <el-input v-model="form.dummy_length" :disabled="true"></el-input>
                                </el-col>
                            </el-form-item>
                        </el-form>

                        <h5 class="content-title mt-3">Item Delivery Tracking</h5>
                        <div class="row">
                            <div class="col">
                                <el-card class="box-card">
                                    <div slot="header" class="clearfix">
                                        <span class="tracking-card-title">Delivery Date: 04/15/2020</span>
                                    </div>
                                    <span>Quantity: 10 boxes</span><br>
                                    <span>Remaining: 40 boxes</span>
                                </el-card>
                            </div>
                            <div class="col">
                                <el-card class="box-card">
                                    <div slot="header" class="clearfix">
                                        <span class="tracking-card-title">Delivery Date: 04/15/2020</span>
                                    </div>
                                    <span>Quantity: 10 boxes</span><br>
                                    <span>Remaining: 40 boxes</span>
                                </el-card>
                            </div>
                            <div class="col">
                                <el-card class="box-card">
                                    <div slot="header" class="clearfix">
                                        <span class="tracking-card-title">Delivery Date: 04/15/2020</span>
                                    </div>
                                    <span>Quantity: 10 boxes</span><br>
                                    <span>Remaining: 40 boxes</span>
                                </el-card>
                            </div>
                        </div>

                        <hr>
                        <h5 class="mt-3">PO Order Items</h5>
                        <el-col :span="6" :offset="18">
                            <el-input
                            v-model="search"
                            @input="queryForKeywords"
                            size="small"
                            clearable
                            placeholder="Search"/>
                        </el-col>
                        <el-table
                            v-model="DMPOItemsTable"
                            row-key="DMPOItemsTableId"
                            height="500px"
                            :data="DMPOItemsTable"
                            @sort-change="sortChange"
                            :default-sort = "{prop: 'id', order: 'descending'}"
                            v-loading="loading"
                            element-loading-text="Loading..."
                            element-loading-spinner="el-icon-loading"
                            style="width: 100%">
                            <el-table-column
                                prop="supplier_code"
                                label="Code"
                                width="100"
                                sortable>
                            </el-table-column>
                            <el-table-column
                                prop="supplier_name"
                                label="Supplier"
                                width="250"
                                sortable>
                            </el-table-column>
                            <el-table-column
                                prop="supplier_category"
                                sortable
                                label="Category">
                                <template slot-scope="scope">
                                    {{ supplierCategory(DMPOItemsTable[scope.$index].supplier_category) }}
                                </template>
                            </el-table-column>
                            <el-table-column
                                prop="supplier_classification_id"
                                sortable
                                label="Classification">
                                <template slot-scope="scope">
                                    {{ supplierClassification(DMPOItemsTable[scope.$index].supplier_classification_id) }}
                                </template>
                            </el-table-column>
                            <el-table-column
                                label="Action"
                                >
                                <template slot-scope="scope">
                                    <el-tooltip class="item" effect="dark" content="More info" placement="top">
                                        <router-link :to="'/delivery-monitoring-po-item/'+DMPOItemsTable[scope.$index].id">
                                            <i class="icon el-icon-info pointer" v-if="!showDeleted"></i>
                                            <i class="icon el-icon-info pointer info-deleted" v-else></i>
                                        </router-link>
                                    </el-tooltip>
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
                        <el-button class="pdf" size="mini" @click="exportPDF" round><i class="fas fa-file-pdf"></i> Download</el-button>
                    </el-col>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        data(){
            return {
                form: new Form({
                    dummy_data: '',
                    dummy_data_unit: '',
                    dummy_length: '',
                    dummy_length_unit: '',
                    dummy_width: '',
                    dummy_width_unit: '',
                }),
                editMode: false,
                DMPOItemsTable: [],
                search: '',
                pageInfo: {},
                perPage: 10,
                showDeleted: false,
                alert_text: '',
                keywords: '',
                sortParameter: {
                    "column":{},
                    "prop":"supplier_name",
                    "order":"ascending"
                },
                loading: '',
            }
        },
        methods: {
            getSuppliers(){
                this.clearTable();
                this.loading = true;
                axios.get("/api/supplier", { 
                    params: { 
                        take: this.perPage, 
                        showDeleted: this.showDeleted, 
                        search: this.keywords, 
                        sortProp: this.sortParameter.prop, 
                        sortOrder: this.sortParameter.order,
                    } 
                })
                .then((data) => {
                    this.pageInfo = data.data;
                    if(this.$currentPageSuppliers > 0){
                        this.handleCurrentChange(this.$currentPageSuppliers);
                    }
                    else{
                        this.DMPOItemsTable = data.data.data;
                        this.pageInfo = data.data;
                        this.pageInfo.current_page = this.$currentPageSuppliers;
                        this.loading = false;   
                    }      
                })
                .catch(error => {
                    this.loaded = true;
                    if(error.response.status == 401){
                        window.location.href = '/login';
                    }
                    this.loading = false;
                });
            },
            handleSizeChange(val) {
                this.clearTable();
                this.loading = true;
                this.perPage = val;
                axios.get("api/supplier", { 
                    params: { 
                        take: this.perPage,
                        showDeleted: this.showDeleted, 
                        search: this.keywords,
                        sortProp: this.sortParameter.prop, 
                        sortOrder: this.sortParameter.order,
                    } 
                })
                .then((data) => {
                    this.DMPOItemsTable = data.data.data;
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
                        search: this.keywords,
                        sortProp: this.sortParameter.prop, 
                        sortOrder: this.sortParameter.order,
                    } 
                })
                .then((data) => {
                    this.DMPOItemsTable = data.data.data;
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
                        search: this.keywords,
                        sortProp: this.sortParameter.prop, 
                        sortOrder: this.sortParameter.order,
                    } 
                })
                .then((data) => {
                    this.DMPOItemsTable = data.data.data;
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
            queryForKeywords(search) {
                this.clearTable();
                this.loading = true;
                this.keywords = search;
                axios.get("api/supplier", { 
                    params: { 
                        take: this.perPage, 
                        showDeleted: this.showDeleted, 
                        search: this.keywords,
                        sortProp: this.sortParameter.prop, 
                        sortOrder: this.sortParameter.order,
                    } 
                })
                .then((data) => {
                    this.DMPOItemsTable = data.data.data;
                    this.pageInfo = data.data;
                    this.loading = false;
                })
                .catch(error => {
                    this.loaded = true;
                    if(error.response.status == 401){
                        window.location.href = '/login';
                    }
                    this.loading = false;
                });
            },
            deleteSupplier(supplier_id){
                if(this.showDeleted == true) this.alert_text = 'restore';
                else this.alert_text = 'delete';

                Swal.fire({
                    text: 'Are you sure you want to '+this.alert_text+' this?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: this.alert_text.charAt(0).toUpperCase() + this.alert_text.slice(1),
                    width: '25rem',
                })
                .then((result) => {
                    if(result.value){
                        this.loading = true;
                        axios.delete('api/supplier/'+supplier_id)
                        .then((data) => {
                            Swal.fire(
                                'Success!',
                                'Supplier has been '+this.alert_text+'d.',
                                'success'
                            )
                            this.getSuppliers();
                            this.loading = false;
                        })
                        .catch((error) => {
                            Swal.fire(
                                'Failed!',
                                'Failed to '+this.alert_text+' supplier.',
                                'warning'
                            )
                            this.loading = false;
                        })
                    }
                })
            },
            showDeletedOrders(){
                this.clearTable();
                this.getSuppliers();
            },
            exportPDF(){
                var keywords = this.keywords;
                var isDeleted = this.showDeleted;
                var sortProp = this.sortParameter.prop;
                var sortOrder = this.sortParameter.order;
                window.open('/export-pdf-suppliers?keywords='+keywords+'&showDeleted='+isDeleted+'&sortProp='+sortProp+'&sortOrder='+sortOrder);
            },
            sortChange(param){
                this.sortParameter = param;
                this.getSuppliers();
            },
            currentPageGlobal(){
                Vue.prototype.$currentPageSuppliers = this.pageInfo.current_page;
                this.currentPage = this.pageInfo.current_page;
            },
            clearTable(){
                this.DMPOItemsTable = [];
            },
            supplierCategory(id){
                for (let i = 0; i < this.$supplier_categories.length; i++) {
                    if(this.$supplier_categories[i].id == id) return this.$supplier_categories[i].category_name;
                }
            },
            supplierClassification(id){
                for (let i = 0; i < this.$supplier_classifications.length; i++) {
                    if(this.$supplier_classifications[i].id == id) return this.$supplier_classifications[i].classification_name;
                }
            },
        },
        created() {
            this.getSuppliers();
        },
    }
</script>