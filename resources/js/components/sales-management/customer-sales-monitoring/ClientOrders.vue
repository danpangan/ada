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
                        <h3 class="card-title">Customer Orders</h3>
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
                            row-key="tableDataId"
                            height="500px"
                            :data="tableData"
                            @sort-change="sortChange"
                            :default-sort = "{prop: 'id', order: 'descending'}"
                            v-loading="loading"
                            element-loading-text="Loading..."
                            element-loading-spinner="el-icon-loading"
                            style="width: 100%">
                            <el-table-column
                                prop="order_item_code"
                                label="Order Code"
                                width="140"
                                sortable>
                            </el-table-column>
                            <el-table-column
                                prop="order_part_number"
                                label="Part Number"
                                width="150"
                                sortable>
                            </el-table-column>
                            <el-table-column
                                prop="order_item_description"
                                label="Order Description"
                                width="200"
                                sortable>
                            </el-table-column>
                            <el-table-column
                                prop="supplier_category"
                                width="120"
                                label="Category">
                                <template slot-scope="scope">
                                    {{ orderCategory(tableData[scope.$index].order_category) }}
                                </template>
                            </el-table-column>
                            <el-table-column
                                prop="supplier_classification_id"
                                width="120"
                                label="Type Code">
                                <template slot-scope="scope">
                                    {{ orderTypeCode(tableData[scope.$index].order_item_type_code) }}
                                </template>
                            </el-table-column>
                            <el-table-column
                                prop="supplier_classification_id"
                                width="120"
                                label="Classification">
                                <template slot-scope="scope">
                                    {{ orderClassification(tableData[scope.$index].order_item_classification) }}
                                </template>
                            </el-table-column>
                            <el-table-column
                                label="Action"
                                :fixed="`right`"
                                width="100"
                                >
                                <template slot-scope="scope">
                                    <el-tooltip class="item" effect="dark" content="More info" placement="top">
                                        <router-link :to="'/view-order/'+tableData[scope.$index].id">
                                            <i class="icon el-icon-info pointer" v-if="!showDeleted"></i>
                                            <i class="icon el-icon-info pointer info-deleted" v-else></i>
                                        </router-link>
                                    </el-tooltip>

                                    <el-tooltip class="item" effect="dark" content="Edit" placement="top" v-show="!showDeleted">
                                        <router-link :to="'/update-order/'+tableData[scope.$index].id">
                                            <i class="icon el-icon-edit-outline pointer ml-2"></i>
                                        </router-link>
                                    </el-tooltip>

                                    <el-tooltip class="item" effect="dark" content="Delete" placement="top" v-if="!showDeleted">
                                        <i class="icon el-icon-delete pointer ml-2" @click="deleteOrder(tableData[scope.$index].client_order_id)"></i>
                                    </el-tooltip>
                                    <el-tooltip class="item" effect="dark" content="Restore" placement="top" v-else>
                                        <i class="icon el-icon-circle-check pointer ml-2" @click="deleteOrder(tableData[scope.$index].client_order_id)"></i>
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
                            <el-checkbox v-model="showDeleted" @change="showDeletedOrders">Show deleted Orders</el-checkbox>
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
                search: '',
                pageInfo: {},
                perPage: 10,
                showDeleted: false,
                alert_text: '',
                keywords: '',
                sortParameter: {
                    "column":{},
                    "prop":"order_item_code",
                    "order":"ascending"
                },
                loading: '',
                customerSalesMonitoring:[
                    { id: 1,  name: 'Customer PO',                link: 'purchase-orders'       },
                    { id: 2,  name: 'Customer Orders',            link: 'orders'                },
                    { id: 3,  name: 'Customer DM',                link: 'delivery-monitoring'   },
                    { id: 4,  name: 'Customer CS',                link: 'computation-sheets'    },
                    { id: 5,  name: 'Customer Quotation',         link: 'quotations'    },
                    { id: 6,  name: 'Customer SS',                link: 'sales-sheets'          },
                    { id: 7,  name: 'Customer Customer Forecast', link: 'forecast'              },
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
            insertClientId(){
                for (let i = 0; i < this.customerSalesMonitoring.length; i++) {
                    this.customerSalesMonitoring[i].client_id = this.$route.params.client_id;  
                }
            },
            getClientOrders(){
                this.clearTable();
                this.insertClientId();
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
                    this.pageInfo = data.data.orders;
                    this.tableData = data.data.orders.data;
                         
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
                axios.get("/api/customer-sales-monitoring-orders/", { 
                    params: { 
                        take: this.perPage,
                        showDeleted: this.showDeleted, 
                        search: this.keywords,
                        sortProp: this.sortParameter.prop, 
                        sortOrder: this.sortParameter.order,
                    } 
                })
                .then((data) => {
                    this.tableData = data.data.orders.data;
                    this.pageInfo = data.data.orders;
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
                    this.tableData = data.data.orders.data;
                    this.pageInfo = data.data.orders;
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
                    this.tableData = data.data.orders.data;
                    this.pageInfo = data.data.orders;
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
                axios.get("/api/customer-sales-monitoring-orders/", { 
                    params: { 
                        take: this.perPage, 
                        showDeleted: this.showDeleted, 
                        search: this.keywords,
                        sortProp: this.sortParameter.prop, 
                        sortOrder: this.sortParameter.order,
                    } 
                })
                .then((data) => {
                    this.pageInfo = data.data.orders.orders;
                    this.tableData = data.data.orders.orders.data;
                    this.loading = false;
                })
                .catch(error => {
                    this.loaded = true;
                    
                    this.loading = false;
                });
            },
            deleteOrder(supplier_id){
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
                        axios.delete('/api/order/'+supplier_id)
                        .then((data) => {
                            Swal.fire(
                                'Success!',
                                'Order has been '+this.alert_text+'d.',
                                'success'
                            )
                            this.getClientOrders();
                            this.loading = false;
                        })
                        .catch((error) => {
                            Swal.fire(
                                'Failed!',
                                'Failed to '+this.alert_text+' order.',
                                'warning'
                            )
                            this.loading = false;
                        })
                    }
                })
            },
            showDeletedOrders(){
                this.clearTable();
                this.getClientOrders();
            },
            exportPDF(){
                var keywords = this.keywords;
                var isDeleted = this.showDeleted;
                var sortProp = this.sortParameter.prop;
                var sortOrder = this.sortParameter.order;
                var clientId = this.$route.params.client_id;
                window.open('/export-pdf-customer-sales-monitoring-orders?keywords='+keywords+'&showDeleted='+isDeleted+'&sortProp='+sortProp+'&sortOrder='+sortOrder+'&clientId='+clientId);
            },
            sortChange(param){
                this.sortParameter = param;
                this.getClientOrders();
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
            this.getClientOrders();
            this.getOrderTypeCodes();
            this.getOrderClassifications();
        },
        
    }
</script>
