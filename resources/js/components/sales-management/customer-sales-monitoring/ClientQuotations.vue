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
                                <div v-for="dropdown in customerSalesMonitoring" :key="dropdown.id">
                                    <el-dropdown-item>
                                        <router-link :to="'/customer-sales-monitoring/'+dropdown.link+'/client/'+dropdown.client_id">
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
                        <h3 class="card-title">Customer Quotations</h3>
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
                        <!-- Quotation Table -->
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
                        <!-- Quotation Table -->
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
                            <el-checkbox v-model="showDeleted" @change="showDeletedQuotes">Show deleted Quotations</el-checkbox>
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
                tableData: [],
                customerSalesMonitoring:[
                    { id: 1,  name: 'Customer PO',                link: 'purchase-orders'       },
                    { id: 2,  name: 'Customer Orders',            link: 'orders'                },
                    { id: 3,  name: 'Customer DM',                link: 'delivery-monitoring'   },
                    { id: 4,  name: 'Customer CS',                link: 'computation-sheets'    },
                    { id: 5,  name: 'Customer Quotation',         link: 'quotations'            },
                    { id: 6,  name: 'Customer SS',                link: 'sales-sheets'          },
                    { id: 7,  name: 'Customer Customer Forecast', link: 'forecast'              },
                ],
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
            insertClientId(){
                for (let i = 0; i < this.customerSalesMonitoring.length; i++) {
                    this.customerSalesMonitoring[i].client_id = this.$route.params.client_id;  
                }
            },
            getClientQuotations(){
                this.clearTable();
                this.insertClientId();
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
                    this.pageInfo = data.data.quotes;
                    this.tableData = data.data.quotes.data;
                         
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
                axios.get("/api/customer-sales-monitoring-quotations/", { 
                    params: { 
                        take: this.perPage,
                        showDeleted: this.showDeleted, 
                        search: this.keywords,
                        sortProp: this.sortParameter.prop, 
                        sortOrder: this.sortParameter.order,
                    } 
                })
                .then((data) => {
                    this.tableData = data.data.quotes.data;
                    this.pageInfo = data.data.quotes;
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
                    this.tableData = data.data.quotes.data;
                    this.pageInfo = data.data.quotes;
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
                    this.tableData = data.data.quotes.data;
                    this.pageInfo = data.data.quotes;
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
                axios.get("/api/customer-sales-monitoring-quotations/", { 
                    params: { 
                        take: this.perPage, 
                        showDeleted: this.showDeleted, 
                        search: this.keywords,
                        sortProp: this.sortParameter.prop, 
                        sortOrder: this.sortParameter.order,
                    } 
                })
                .then((data) => {
                    this.tableData = data.data.quotes.data;
                    this.pageInfo = data.data.quotes;
                    this.loading = false;
                })
                .catch(error => {
                    this.loaded = true;
                    
                    this.loading = false;
                });
            },
            deleteQuote(id){
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
                        axios.delete('/api/quotation/'+id)
                        .then((data) => {
                            Swal.fire(
                                'Success!',
                                'Quotation has been '+this.alert_text+'d.',
                                'success'
                            )
                            this.getClientQuotations();
                            this.loading = false;
                        })
                        .catch((error) => {
                            Swal.fire(
                                'Failed!',
                                'Failed to '+this.alert_text+' quotation.',
                                'warning'
                            )
                            this.loading = false;
                        })
                    }
                })
            },
            exportPDF(){
                var keywords = this.keywords;
                var isDeleted = this.showDeleted;
                var sortProp = this.sortParameter.prop;
                var sortOrder = this.sortParameter.order;
                var clientId = this.$route.params.client_id;
                window.open('/export-pdf-customer-sales-monitoring-quotations?keywords='+keywords+'&showDeleted='+isDeleted+'&sortProp='+sortProp+'&sortOrder='+sortOrder+'&clientId='+clientId);
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
                            this.getClientQuotations();
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
                if(this.client_info.pic.length > 0)
                return _.find(this.client_info.pic, {'id': pic_id}).client_pic;
            },
            showDeletedQuotes(){
                this.clearTable();
                this.getClientQuotations();

            },
            
        },
        created() {
            this.getClientInfo();
            this.getClientQuotations();
        },
        
    }
</script>
