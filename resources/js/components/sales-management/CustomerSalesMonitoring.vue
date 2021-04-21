<template>
    <div class="container">
        <div class="content-header mb-3" style="border-bottom: 1px solid black;">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h4 class="m-0 text-dark"><strong>Customer Sales Monitoring</strong></h4>
                    </div>
                    <div class="col-sm-6" style="text-align: right">
                        <el-dropdown>
                            <el-button type="primary">
                                Customer Sales Monitoring <i class="el-icon-arrow-down el-icon--right"></i>
                            </el-button>
                            <el-dropdown-menu slot="dropdown">
                                <div v-for="dropdown in customerSalesMonitoring" :key="dropdown.id" @click="dropdownWasChanged">
                                    <el-dropdown-item>
                                        <router-link :to="'/customer-sales-monitoring/'+dropdown.id+'/client/'+dropdown.client_id">
                                            <p class="dropdown-item-links">{{ dropdown.name }}</p>
                                        </router-link>
                                    </el-dropdown-item>
                                </div>
                            </el-dropdown-menu>
                        </el-dropdown>
                    </div>
                </div>
            </div>
        </div>
        <el-card class="box-card mt-3" shadow="hover">
            <div slot="header">
                <span>Customer Information</span>
            </div>
            <el-form label-width="15%" :label-position="`right`">
                <el-form-item label="Customer Name">
                    <el-col :span="12">
                        <el-input v-model="client_info.client_name" disabled></el-input>
                    </el-col>
                </el-form-item>
                <el-form-item label="Customer Code">
                    <el-col :span="12">
                        <el-input v-model="client_info.client_number" disabled></el-input>
                    </el-col>
                </el-form-item>
                <el-form-item label="Short Name">
                    <el-col :span="6">
                        <el-input v-model="client_info.client_short_name" disabled></el-input>
                    </el-col>
                </el-form-item>
            </el-form>
        </el-card>
        <div class="row justify-content-center mt-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-header card-adamay-blue">
                        <h3 v-if="this.$route.params.page_type == 1" class="card-title">Customer PO</h3>
                        <h3 v-else-if="this.$route.params.page_type == 2" class="card-title">Customer Orders</h3>
                        <h3 v-else-if="this.$route.params.page_type == 3" class="card-title">Customer Delivery Monitoring</h3>
                        <h3 v-else-if="this.$route.params.page_type == 4" class="card-title">Customer Computation Sheets</h3>
                        <h3 v-else-if="this.$route.params.page_type == 5" class="card-title">Customer Quotation</h3>
                        <h3 v-else-if="this.$route.params.page_type == 6" class="card-title">Customer Sale Sheets</h3>
                        <h3 v-else-if="this.$route.params.page_type == 7" class="card-title">Customer Forecast</h3>
                    </div>
                    <div class="card-body">
                        <el-col :span="6" :offset="18">
                            <el-input
                            v-model="search"
                            @input="queryForKeywords"
                            size="small"
                            clearable
                            placeholder="Search"/>
                        </el-col>
                        <el-table
                            v-model="tableData"
                            row-key="tableData"
                            height="500px"
                            :data="tableData"
                            @sort-change="sortChange"
                            :default-sort = "{prop: 'id', order: 'descending'}"
                            v-loading="loading"
                            element-loading-text="Loading..."
                            element-loading-spinner="el-icon-loading"
                            style="width: 100%">
                        <!-- PO Table -->
                            <template v-if="this.$route.params.page_type == 1">
                                <el-table-column
                                    prop="quotation_number"
                                    label="Quotation No."
                                    width="200"
                                    sortable>
                                </el-table-column>
                                <el-table-column
                                    width="180"
                                    label="PIC">
                                    
                                </el-table-column>
                                <el-table-column
                                    width="120"
                                    label="Status">
                                    
                                </el-table-column>
                                <el-table-column
                                    width="120"
                                    label="Valid">
                                    <!-- <template slot-scope="scope">
                                        <span>{{ tableData[scope.$index].quotation_is_valid == 1 ? 'Valid' : 'Invalid' }}</span>
                                    </template> -->
                                    <template slot-scope="scope">
                                            <i :class="'icon '+validityIcon(tableData[scope.$index].quotation_is_valid)+' ml-2'"></i>
                                    </template>
                                </el-table-column>
                            </template>
                        <!-- PO Table -->
                        <!-- Orders Table -->
                            <template v-else-if="this.$route.params.page_type == 2">
                                <el-table-column
                                    prop="order_item_code"
                                    label="Order Code"
                                    width="200"
                                    sortable>
                                </el-table-column>
                                <el-table-column
                                    prop="order_part_number"
                                    width="180"
                                    label="Part Number">
                                </el-table-column>
                                <el-table-column
                                    prop="order_item_description"
                                    width="120"
                                    label="Order Description">
                                </el-table-column>
                                <el-table-column
                                    width="120"
                                    label="Category">
                                        <template slot-scope="scope">
                                            <span>{{ orderCategory(tableData[scope.$index].order_category) }}</span>
                                        </template>
                                </el-table-column>
                                <el-table-column
                                    width="120"
                                    label="Type Code">
                                        <template slot-scope="scope">
                                            <span>{{ orderTypeCode(tableData[scope.$index].order_item_type_code) }}</span>
                                        </template>
                                </el-table-column>
                                <el-table-column
                                    width="120"
                                    label="Classification">
                                        <template slot-scope="scope">
                                            <span>{{ orderClassification(tableData[scope.$index].order_item_classification) }}</span>
                                        </template>
                                </el-table-column>
                            </template>
                        <!-- Orders Table -->
                        <!-- DM Table -->
                            <template v-else-if="this.$route.params.page_type == 3">
                                <el-table-column
                                    prop="quotation_number"
                                    label="Quotation No."
                                    width="200"
                                    sortable>
                                </el-table-column>
                                <el-table-column
                                    width="180"
                                    label="PIC">
                                </el-table-column>
                                <el-table-column
                                    width="120"
                                    label="Valid">
                                    <!-- <template slot-scope="scope">
                                        <span>{{ tableData[scope.$index].quotation_is_valid == 1 ? 'Valid' : 'Invalid' }}</span>
                                    </template> -->
                                    <template slot-scope="scope">
                                            <i :class="'icon '+validityIcon(tableData[scope.$index].quotation_is_valid)+' ml-2'"></i>
                                    </template>
                                </el-table-column>
                            </template>
                        <!-- DM Table -->
                        <!-- CS Table -->
                            <template v-else-if="this.$route.params.page_type == 4">
                                <el-table-column
                                    prop="quotation_number"
                                    label="Quotation No."
                                    width="200"
                                    sortable>
                                </el-table-column>
                                <el-table-column
                                    width="120"
                                    label="Valid">
                                    <!-- <template slot-scope="scope">
                                        <span>{{ tableData[scope.$index].quotation_is_valid == 1 ? 'Valid' : 'Invalid' }}</span>
                                    </template> -->
                                    <template slot-scope="scope">
                                            <i :class="'icon '+validityIcon(tableData[scope.$index].quotation_is_valid)+' ml-2'"></i>
                                    </template>
                                </el-table-column>
                            </template>
                        <!-- CS Table -->
                        <!-- Quotation Table -->
                            <template v-else-if="this.$route.params.page_type == 5">
                                <el-table-column
                                    prop="quotation_number"
                                    label="Quotation Number"
                                    width="200"
                                    sortable>
                                </el-table-column>
                                <el-table-column
                                    width="180"
                                    label="PIC">
                                    <template slot-scope="scope">
                                        <span>{{ quotationPIC(tableData[scope.$index].client_pic_id) }}</span>
                                    </template>
                                </el-table-column>
                                <el-table-column
                                    width="120"
                                    label="Status">
                                    <template slot-scope="scope">
                                        <span>{{ quotationStatus(tableData[scope.$index].quotation_status) }}</span>
                                    </template>
                                </el-table-column>
                                <el-table-column
                                    width="120"
                                    label="Valid">
                                         <template slot-scope="scope">
                                            <i :class="'icon '+validityIcon(tableData[scope.$index].quotation_is_valid)+' ml-2'"></i>
                                        </template>
                                </el-table-column>
                            </template>
                        <!-- Quotation Table -->
                        <!-- Sale Sheet Table -->
                            <template v-else-if="this.$route.params.page_type == 6">
                                <el-table-column
                                    prop="quotation_number"
                                    label="Quotation No."
                                    width="200"
                                    sortable>
                                </el-table-column>
                                <el-table-column
                                    width="120"
                                    label="Valid">
                                    <!-- <template slot-scope="scope">
                                        <span>{{ tableData[scope.$index].quotation_is_valid == 1 ? 'Valid' : 'Invalid' }}</span>
                                    </template> -->
                                    <template slot-scope="scope">
                                            <i :class="'icon '+validityIcon(tableData[scope.$index].quotation_is_valid)+' ml-2'"></i>
                                    </template>
                                </el-table-column>
                            </template>
                        <!-- Sale Sheet Table -->
                        <!-- Forecast Table -->
                            <template v-else-if="this.$route.params.page_type == 7">
                                <el-table-column
                                    prop="quotation_number"
                                    label="Quotation No."
                                    width="200"
                                    sortable>
                                </el-table-column>
                                <el-table-column
                                    width="120"
                                    label="Valid">
                                    <!-- <template slot-scope="scope">
                                        <span>{{ tableData[scope.$index].quotation_is_valid == 1 ? 'Valid' : 'Invalid' }}</span>
                                    </template> -->
                                    <template slot-scope="scope">
                                            <i :class="'icon '+validityIcon(tableData[scope.$index].quotation_is_valid)+' ml-2'"></i>
                                    </template>
                                </el-table-column>
                            </template>
                        <!-- Forecast Table -->
                            <el-table-column
                                label="Action"
                                :fixed="`right`"
                                width="160"
                                >
                                <template slot-scope="scope">
                                    <el-tooltip class="item" effect="dark" content="More info" placement="top">
                                        <router-link :to="'/view-quotation/'+tableData[scope.$index].id">
                                            <i class="icon el-icon-info pointer" v-if="!showDeleted"></i>
                                            <i class="icon el-icon-info pointer info-deleted" v-else></i>
                                        </router-link>
                                    </el-tooltip>

                                    <el-tooltip class="item" effect="dark" content="Edit" placement="top" v-show="!showDeleted">
                                        <router-link :to="'/update-quotation/'+tableData[scope.$index].id">
                                            <i class="icon el-icon-edit-outline pointer ml-2"></i>
                                        </router-link>
                                    </el-tooltip>

                                    <el-tooltip class="item" effect="dark" content="Copy" placement="top" v-show="!showDeleted">
                                            <i class="icon el-icon-document-copy pointer ml-2" @click="copyQuoatation(tableData[scope.$index].id)"></i>
                                    </el-tooltip>
                                    <el-tooltip class="item" effect="dark" content="Preview" placement="top" v-if="!showDeleted">
                                        <i class="icon el-icon-search pointer ml-2" @click="printQuotation(tableData[scope.$index].id)"></i>
                                    </el-tooltip>

                                    <el-tooltip class="item" effect="dark" content="Delete" placement="top" v-if="!showDeleted">
                                        <i class="icon el-icon-delete pointer ml-2" @click="deleteQuote(tableData[scope.$index].id)"></i>
                                    </el-tooltip>
                                    <el-tooltip class="item" effect="dark" content="Restore" placement="top" v-else>
                                        <i class="icon el-icon-circle-check pointer ml-2" @click="deleteQuote(tableData[scope.$index].id)"></i>
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
                        <el-col class="show-deleted-checkbox" :span="6">
                            <el-checkbox v-if="this.$route.params.page_type == 1" v-model="showDeleted" @change="showDeletedOrders">Show deleted Purchase Orders</el-checkbox>
                            <el-checkbox v-else-if="this.$route.params.page_type == 2" v-model="showDeleted" @change="showDeletedOrders">Show deleted Orders</el-checkbox>
                            <el-checkbox v-else-if="this.$route.params.page_type == 3" v-model="showDeleted" @change="showDeletedOrders">Show deleted Delivery Monitoring</el-checkbox>
                            <el-checkbox v-else-if="this.$route.params.page_type == 4" v-model="showDeleted" @change="showDeletedOrders">Show deleted Computation Sheets</el-checkbox>
                            <el-checkbox v-else-if="this.$route.params.page_type == 5" v-model="showDeleted" @change="showDeletedOrders">Show deleted Quotations</el-checkbox>
                            <el-checkbox v-else-if="this.$route.params.page_type == 6" v-model="showDeleted" @change="showDeletedOrders">Show deleted Sales Sheet</el-checkbox>
                            <el-checkbox v-else-if="this.$route.params.page_type == 7" v-model="showDeleted" @change="showDeletedOrders">Show deleted Forecast</el-checkbox>
                        </el-col>
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
                editMode: false,
                client_info: {},
                pageInfo: {
                    po: [],
                    orders: [],
                    dm: [],
                    cs: [],
                    quotes: [],
                    forecast: [],
                },
                search: '',
                pageInfo: {},
                perPage: 10,
                showDeleted: false,
                alert_text: '',
                keywords: '',
                sortParameter: {
                    "column":{},
                    "prop":"client_id",
                    "order":"ascending"
                },
                loading: '',
                customerSalesMonitoring:[
                    { id: 1,  name: 'Customer PO',                  },
                    { id: 2,  name: 'Customer Orders',              },
                    { id: 3,  name: 'Customer DM',                  },
                    { id: 4,  name: 'Customer CS',                  },
                    { id: 5,  name: 'Customer Quotation',           },
                    { id: 6,  name: 'Customer SS',                  },
                    { id: 7,  name: 'Customer Customer Forecast',   },
                ],
                tableData: [],
                classifications: [],
                order_type_codes: [],
            }
        },
        methods: {
            getClientInfo(){
                axios.get('/api/client/'+this.$route.params.client_id)
                .then((res)=>{
                    this.client_info = res.data.formClient;
                })
                .catch(()=>{

                })
            },
            pageType(){
                this.tableData = [];
                for (let i = 0;i < this.customerSalesMonitoring.length; i++) {
                        this.customerSalesMonitoring[i].client_id = this.$route.params.client_id;
                    }
                if(this.$route.params.page_type == 1){
                    this.getClientPO();
                }
                else if(this.$route.params.page_type == 2){
                    this.getClientOrders();
                    this.getOrderTypeCodes();
                    this.getOrderClassifications();
                }
                else if(this.$route.params.page_type == 3){
                    this.getClientDM();
                }
                else if(this.$route.params.page_type == 4){
                    this.getClientCS();
                }
                else if(this.$route.params.page_type == 5){
                    this.getClientQuotations();
                }
                else if(this.$route.params.page_type == 6){
                    this.getClientSS();
                }
                else if(this.$route.params.page_type == 7){
                    this.getClientForecast();
                }
                else console.log('error')
            },
            dropdownWasChanged(event, id){
                this.pageType();
            },
            getClientPO(){
                this.clearTable();
                // this.loading = true;
                axios.get("/api/customer-sales-monitoring-po/"+this.$route.params.client_id, { 
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
                    tableData = data.data.quotes;
                         
                })
                .catch(error => {
                    this.loaded = true;
                   
                    this.loading = false;
                });
            },
            getClientOrders(){
                this.clearTable();
                // this.loading = true;
                axios.get("/api/customer-sales-monitoring-orders/"+this.$route.params.client_id, { 
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
                    this.tableData = data.data.orders;
                         
                })
                .catch(error => {
                    this.loaded = true;
                    
                    this.loading = false;
                });
            },
            getClientDM(){
                this.clearTable();
                // this.loading = true;
                axios.get("/api/customer-sales-monitoring-dm/"+this.$route.params.client_id, { 
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
                    tableData = data.data.quotes;
                         
                })
                .catch(error => {
                    this.loaded = true;
                    
                    this.loading = false;
                });
            },
            getClientCS(){
                this.clearTable();
                // this.loading = true;
                axios.get("/api/customer-sales-monitoring-cs/"+this.$route.params.client_id, { 
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
                    tableData = data.data.quotes;
                         
                })
                .catch(error => {
                    this.loaded = true;
                    
                    this.loading = false;
                });
            },
            getClientQuotations(){
                this.clearTable();
                // this.loading = true;
                axios.get("/api/customer-sales-monitoring-quotations/"+this.$route.params.client_id, { 
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
                    this.tableData = data.data.quotes;
                         
                })
                .catch(error => {
                    this.loaded = true;
                    
                    this.loading = false;
                });
            },
            getClientSS(){
                this.clearTable();
                // this.loading = true;
                axios.get("/api/customer-sales-monitoring-ss/"+this.$route.params.client_id, { 
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
                    tableData = data.data.quotes;
                         
                })
                .catch(error => {
                    this.loaded = true;
                    
                    this.loading = false;
                });
            },
            getClientForecast(){
                this.clearTable();
                // this.loading = true;
                axios.get("/api/customer-sales-monitoring-forecast/"+this.$route.params.client_id, { 
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
                    tableData = data.data.quotes;
                         
                })
                .catch(error => {
                    this.loaded = true;
                    
                    this.loading = false;
                });
            },
            handleSizeChange(val) {
                this.clearTable();
                this.loading = true;
                this.perPage = val;
                axios.get("api/quotation", { 
                    params: { 
                        take: this.perPage,
                        showDeleted: this.showDeleted, 
                        search: this.keywords,
                        sortProp: this.sortParameter.prop, 
                        sortOrder: this.sortParameter.order,
                    } 
                })
                .then((data) => {
                    this.tableData = data.data.data;
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
                    this.tableData = data.data.data;
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
                    this.tableData = data.data.data;
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
                axios.get("api/quotation", { 
                    params: { 
                        take: this.perPage, 
                        showDeleted: this.showDeleted, 
                        search: this.keywords,
                        sortProp: this.sortParameter.prop, 
                        sortOrder: this.sortParameter.order,
                    } 
                })
                .then((data) => {
                    this.tableData = data.data.data;
                    this.pageInfo = data.data;
                    this.loading = false;
                })
                .catch(error => {
                    this.loaded = true;
                    
                    this.loading = false;
                });
            },
            // deleteQuote(id){
            //     if(this.showDeleted == true) this.alert_text = 'restore';
            //     else this.alert_text = 'delete';

            //     Swal.fire({
            //         text: 'Are you sure you want to '+this.alert_text+' this?',
            //         icon: 'warning',
            //         showCancelButton: true,
            //         confirmButtonText: this.alert_text.charAt(0).toUpperCase() + this.alert_text.slice(1),
            //         width: '25rem',
            //     })
            //     .then((result) => {
            //         if(result.value){
            //             this.loading = true;
            //             axios.delete('api/quotation/'+id)
            //             .then((data) => {
            //                 Swal.fire(
            //                     'Success!',
            //                     'Quotation has been '+this.alert_text+'d.',
            //                     'success'
            //                 )
            //                 this.getQuotation();
            //                 this.loading = false;
            //             })
            //             .catch((error) => {
            //                 Swal.fire(
            //                     'Failed!',
            //                     'Failed to '+this.alert_text+' quotation.',
            //                     'warning'
            //                 )
            //                 this.loading = false;
            //             })
            //         }
            //     })
            // },
            showDeletedOrders(){
                this.clearTable();
                this.getQuotation();
            },
            exportPDF(){
                var keywords = this.keywords;
                var isDeleted = this.showDeleted;
                var sortProp = this.sortParameter.prop;
                var sortOrder = this.sortParameter.order;
                window.open('/export-pdf-quotations?keywords='+keywords+'&showDeleted='+isDeleted+'&sortProp='+sortProp+'&sortOrder='+sortOrder);
            },
            sortChange(param){
                this.sortParameter = param;
                this.getQuotation();
            },
            currentPageGlobal(){
                Vue.prototype.$currentPageSuppliers = this.pageInfo.current_page;
                this.currentPage = this.pageInfo.current_page;
            },
            clearTable(){
                this.tableData = [];
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
            copyQuoatation(id){
                Swal.fire({
                    text: 'Are you sure you want to copy this quotation?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Copy',
                    width: '25rem',
                })
                .then((result) => {
                    if(result.value){
                        this.loading = true;
                        axios.post('/api/create-quote-copy/'+id)
                        .then((data) => {
                            Swal.fire(
                                'Success!',
                                'Quotation has been copied',
                                'success'
                            )
                            this.getQuotation();
                            this.loading = false;
                        })
                        .catch((error) => {
                            Swal.fire(
                                'Failed!',
                                'Failed to copy quotation.',
                                'warning'
                            )
                            this.loading = false;
                        })
                    }
                })
            }, 
            
            validityIcon(validity){
                if(validity == 1) return 'el-icon-circle-check';
                else return 'el-icon-error';
            },

            printQuotation(id){
                window.open('/export-pdf-quotation?quotation_id='+id);
            },
            quotationStatus(status_id){
                return _.find(this.$evaluation_status, { 'id': status_id }).evaluation_status;
            },
            quotationPIC(pic_id){
                return _.find(this.client_info.pic, {'id': pic_id}).client_pic;
            },
            orderCategory(id){
                for (let i = 0; i < this.$order_categories.length; i++) {
                    if(this.$order_categories[i].id == id) return this.$order_categories[i].category_name;
                }
            },
            orderClassification(classification_id){
                let object = this.classifications.find(classification => classification.id == classification_id);
                return _.values(object)[2];
            },
            getOrderTypeCodes(){
                axios.get('/api/get-order-type-codes')
                .then((res) => {
                    this.order_type_codes = res.data;
                })
                .catch((error) => {
                    console.log(error);
                });
            },
            orderTypeCode(type_id){
                for(let i = 0; i < this.order_type_codes.length; i++){
                    if(this.order_type_codes[i].id == type_id){
                        let code = '';
                        let description = '';
                        code = this.order_type_codes[i].type_code;
                        description = this.order_type_codes[i].type_code_description;
                        return code + ' - ' + description;
                    }
                }
            },
            getOrderClassifications(){
                axios.get('/api/get-material-classifications')
                .then((res) => {
                    this.classifications = res.data;
                })
                .catch((error) => {
                    console.log(error);
                });
            },
        },
        created() {
            this.getClientInfo();
            this.pageType();
        },
        
    }
</script>
