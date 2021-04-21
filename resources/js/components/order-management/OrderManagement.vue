<template>
    <div class="container">
        <div class="content-header mb-3" style="border-bottom: 1px solid black;">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h4 class="m-0 text-dark"><strong>Order Management</strong></h4>
                    </div>
                    <div class="col-sm-6">
                        <router-link :to="'/add-order'">
                            <button type="submit" class="btn btn-add btn-sm float-right">
                                <i class="fas fa-plus mr-1"></i> Add New Order
                            </button>
                        </router-link>
                    </div>
                </div>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="card">
                    <div class="card-header card-adamay-blue">
                        <h3 class="card-title">Orders</h3>
                    </div>
                    <div class="card-body">
                        <el-col :span="6" :offset="18">
                            <el-input
                            v-model="search"
                            @input="queryForKeywords"
                            size="small"
                            clearable
                            placeholder="Order Code, Description"/>
                        </el-col>
                        <el-table
                            v-model="ordersTable"
                            row-key="ordersTableId"
                            height="500px"
                            :data="ordersTable"
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
                                    {{ ordererCategory(ordersTable[scope.$index].order_category) }}
                                </template>
                            </el-table-column>
                            <el-table-column
                                prop="supplier_classification_id"
                                width="120"
                                label="Type Code">
                                <template slot-scope="scope">
                                    {{ orderTypeCode(ordersTable[scope.$index].order_item_type_code) }}
                                </template>
                            </el-table-column>
                            <el-table-column
                                prop="supplier_classification_id"
                                width="120"
                                label="Classification">
                                <template slot-scope="scope">
                                    {{ orderClassification(ordersTable[scope.$index].order_item_classification) }}
                                </template>
                            </el-table-column>
                            <el-table-column
                                label="Action"
                                :fixed="`right`"
                                width="100"
                                >
                                <template slot-scope="scope">
                                    <el-tooltip class="item" effect="dark" content="More info" placement="top">
                                        <router-link :to="'/view-order/'+ordersTable[scope.$index].id">
                                            <i class="icon el-icon-info pointer" v-if="!showDeleted"></i>
                                            <i class="icon el-icon-info pointer info-deleted" v-else></i>
                                        </router-link>
                                    </el-tooltip>

                                    <el-tooltip class="item" effect="dark" content="Edit" placement="top" v-show="!showDeleted">
                                        <router-link :to="'/update-order/'+ordersTable[scope.$index].id">
                                            <i class="icon el-icon-edit-outline pointer ml-2"></i>
                                        </router-link>
                                    </el-tooltip>

                                    <el-tooltip class="item" effect="dark" content="Delete" placement="top" v-if="!showDeleted">
                                        <i class="icon el-icon-delete pointer ml-2" @click="deleteOrder(ordersTable[scope.$index].id)"></i>
                                    </el-tooltip>
                                    <el-tooltip class="item" effect="dark" content="Restore" placement="top" v-else>
                                        <i class="icon el-icon-circle-check pointer ml-2" @click="deleteOrder(ordersTable[scope.$index].id)"></i>
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
                            <el-checkbox v-model="showDeleted" @change="showDeletedOrders">Show deleted orders</el-checkbox>
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
                ordersTable: [],
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
                order_type_codes: [],
                classifications: [],
            }
        },
        methods: {
            getOrders(){
                this.clearTable();
                this.loading = true;
                axios.get("api/order", { 
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
                    if(this.$currentPageOrders > 0){
                        this.handleCurrentChange(this.$currentPageOrders);
                    }
                    else{
                        this.ordersTable = data.data.data;
                        this.pageInfo = data.data;
                        this.pageInfo.current_page = this.$currentPageOrders;
                        this.loading = false;   
                    }      
                })
                .catch(error => {
                    this.loaded = true;
                    console.log(error)
                    this.loading = false;
                });
            },
            handleSizeChange(val) {
                this.clearTable();
                this.loading = true;
                this.perPage = val;
                axios.get("api/order", { 
                    params: { 
                        take: this.perPage,
                        showDeleted: this.showDeleted, 
                        search: this.keywords,
                        sortProp: this.sortParameter.prop, 
                        sortOrder: this.sortParameter.order,
                    } 
                })
                .then((data) => {
                    this.ordersTable = data.data.data;
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
                    this.ordersTable = data.data.data;
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
                    this.ordersTable = data.data.data;
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
                axios.get("api/order", { 
                    params: { 
                        take: this.perPage, 
                        showDeleted: this.showDeleted, 
                        search: this.keywords,
                        sortProp: this.sortParameter.prop, 
                        sortOrder: this.sortParameter.order,
                    } 
                })
                .then((data) => {
                    this.ordersTable = data.data.data;
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
                        axios.delete('api/order/'+supplier_id)
                        .then((data) => {
                            Swal.fire(
                                'Success!',
                                'Order has been '+this.alert_text+'d.',
                                'success'
                            )
                            this.getOrders();
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
                this.getOrders();
            },
            exportPDF(){
                var keywords = this.keywords;
                var isDeleted = this.showDeleted;
                var sortProp = this.sortParameter.prop;
                var sortOrder = this.sortParameter.order;
                window.open('/export-pdf-orders?keywords='+keywords+'&showDeleted='+isDeleted+'&sortProp='+sortProp+'&sortOrder='+sortOrder);
            },
            sortChange(param){
                this.sortParameter = param;
                this.getOrders();
            },
            currentPageGlobal(){
                Vue.prototype.$currentPageOrders = this.pageInfo.current_page;
                this.currentPage = this.pageInfo.current_page;
            },
            clearTable(){
                this.ordersTable = [];
            },
            ordererCategory(id){
                for (let i = 0; i < this.$order_categories.length; i++) {
                    if(this.$order_categories[i].id == id) return this.$order_categories[i].category_name;
                }
            },
            getOrderTypeCodes(){
                axios.get('api/get-order-type-codes')
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
                axios.get('api/get-material-classifications')
                .then((res) => {
                    this.classifications = res.data;
                })
                .catch((error) => {
                    console.log(error);
                });
            },
            orderClassification(classification_id){
                let object = this.classifications.find(classification => classification.id == classification_id);
                return _.values(object)[2];
            },
        },
        created() {
            this.getOrders();
            this.getOrderTypeCodes();
            this.getOrderClassifications();
        },
    }
</script>
