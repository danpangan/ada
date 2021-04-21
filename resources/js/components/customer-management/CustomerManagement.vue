<template>
    <div class="container">
        <div class="content-header mb-3" style="border-bottom: 1px solid black;">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h4 class="m-0 text-dark"><strong>Customer Management</strong></h4>
                    </div>
                    <div class="col-sm-6">
                        <router-link :to="'/add-client'">
                            <button type="submit" class="btn btn-add btn-sm float-right">
                                <i class="fas fa-plus mr-1"></i> Add New Customer
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
                        <h3 class="card-title">Customers</h3>
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
                            v-model="clientsTable"
                            row-key="clientsTableId"
                            height="500px"
                            :data="clientsTable"
                            @sort-change="sortChange"
                            :default-sort = "{prop: 'id', order: 'descending'}"
                            v-loading="loading"
                            element-loading-text="Loading..."
                            element-loading-spinner="el-icon-loading"
                            style="width: 100%">
                            <el-table-column
                                prop="client_name"
                                label="Customer Name"
                                :min-width="20"
                                sortable>
                            </el-table-column>
                            <el-table-column
                                prop="client_address"
                                label="Address"
                                :min-width="30"
                                sortable>
                            </el-table-column>
                            <el-table-column
                                prop="client_contact"
                                label="Contact"
                                :min-width="15"
                                sortable>
                            </el-table-column>
                            <el-table-column
                                prop="is_temporary"
                                label="Status"
                                :min-width="15">
                                <template slot-scope="scope">
                                    {{ customerStatus(clientsTable[scope.$index].is_temporary) }}
                                </template>
                            </el-table-column>
                            <el-table-column
                                label="Action"
                                :fixed="`right`"
                                width="100"
                                >
                                <template slot-scope="scope">
                                    <el-tooltip class="item" effect="dark" content="More info" placement="top">
                                        <router-link :to="'/view-client/'+clientsTable[scope.$index].id">
                                            <i class="icon el-icon-info pointer" v-if="!showDeleted && clientsTable[scope.$index].is_temporary == 0"></i>
                                            <i class="icon el-icon-info pointer info-temporary" v-else-if="clientsTable[scope.$index].is_temporary == 1"></i>
                                            <i class="icon el-icon-info pointer info-deleted" v-else></i>
                                        </router-link>
                                    </el-tooltip>

                                    <el-tooltip class="item" effect="dark" content="Edit" placement="top" v-show="!showDeleted">
                                        <router-link :to="'/update-client/'+clientsTable[scope.$index].id">
                                            <i class="icon el-icon-edit-outline pointer ml-2"></i>
                                        </router-link>
                                    </el-tooltip>

                                    <el-tooltip class="item" effect="dark" content="Delete" placement="top" v-if="!showDeleted">
                                        <i class="icon el-icon-delete pointer ml-2" @click="deleteClient(clientsTable[scope.$index].id)"></i>
                                    </el-tooltip>
                                    <el-tooltip class="item" effect="dark" content="Restore" placement="top" v-else>
                                        <i class="icon el-icon-circle-check pointer ml-2" @click="deleteClient(clientsTable[scope.$index].id)"></i>
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
                            <el-checkbox v-model="showDeleted" @change="showDeletedDepartments">Show deleted customers</el-checkbox>
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
                clientsTable: [],
                search: '',
                pageInfo: {},
                perPage: 10,
                showDeleted: false,
                alert_text: '',
                keywords: '',
                sortParameter: {
                    "column":{},
                    "prop":"client_name",
                    "order":"ascending"
                },
                loading: true,
            }
        },
        methods: {
            getClients(){
                this.clearTable();
                this.loading = true;
                axios.get("api/client", { 
                    params: { 
                        take: this.perPage, 
                        showDeleted: this.showDeleted, 
                        search: this.keywords, 
                        sortProp: this.sortParameter.prop, 
                        sortOrder: this.sortParameter.order,
                    } 
                })
                .then((data) => {
                    this.clientsTable = data.data.data;
                    this.pageInfo = data.data;
                    this.pageInfo.current_page = this.$currentPageClients;
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
            handleSizeChange(val) {
                this.clearTable();
                this.loading = true;
                this.perPage = val;
                axios.get("api/client", { 
                    params: { 
                        take: this.perPage, 
                        showDeleted: this.showDeleted, 
                        search: this.keywords,
                        sortProp: this.sortParameter.prop, 
                        sortOrder: this.sortParameter.order,
                    } 
                })
                .then((data) => {
                    this.clientsTable = data.data.data;
                    this.pageInfo = data.data;
                    this.currentPageGlobal();
                    this.loading = false;
                })
                .catch((error) => {
                    this.loading = false;
                });
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
                    this.clientsTable = data.data.data;
                    this.pageInfo = data.data;
                    this.currentPageGlobal();
                    this.loading = false;
                })
                .catch((error) => {
                    this.loading = false;
                });
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
                    this.clientsTable = data.data.data;
                    this.pageInfo = data.data;
                    this.currentPageGlobal();
                    this.loading = false;
                })
                .catch((error) => {
                    this.loading = false;
                });
            },
            handlePrevPage(val) {
                this.clearTable();
                this.loading = true;
                axios.get(this.pageInfo.prev_page_url, { 
                    params: { 
                        take: this.perPage, 
                        showDeleted: this.showDeleted, 
                        search: this.keywords,
                        sortProp: this.sortParameter.prop, 
                        sortOrder: this.sortParameter.order,
                        } 
                    })
                .then((data) => {
                    this.clientsTable = data.data.data;
                    this.pageInfo = data.data;
                    this.currentPageGlobal();
                    this.loading = false;
                })
                .catch((error) => {
                    this.loading = false;
                });
            },
            queryForKeywords(search) {
                this.clearTable();
                this.loading = true;
                this.keywords = search;
                axios.get("api/client", { 
                    params: { 
                        take: this.perPage, 
                        showDeleted: this.showDeleted, 
                        search: this.keywords,
                        sortProp: this.sortParameter.prop, 
                        sortOrder: this.sortParameter.order,
                    } 
                })
                .then((data) => {
                    this.clientsTable = data.data.data;
                    this.pageInfo = data.data;
                    this.loading = false;
                })
                .catch(error => {
                    this.loaded = true;
                    this.loading = false;
                });
            },
            deleteClient(client_id){
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
                        axios.delete('api/client/'+client_id)
                        .then(() => {
                            Swal.fire(
                                'Success!',
                                'Customer has been '+this.alert_text+'d.',
                                'success'
                            )
                            this.getClients();
                            this.loading = false;
                        })
                        .catch(() => {
                            Swal.fire(
                                'Failed!',
                                'Failed to '+this.alert_text+' customer.',
                                'warning'
                            )
                            this.loading = false;
                        })
                    }
                })
            },
            showDeletedDepartments(){
                this.clearTable();
                this.getClients();
            },
            exportPDF(){
                var keywords = this.keywords;
                var isDeleted = this.showDeleted;
                var sortProp = this.sortParameter.prop;
                var sortOrder = this.sortParameter.order;
                window.open('/export-pdf-clients?keywords='+keywords+'&showDeleted='+isDeleted+'&sortProp='+sortProp+'&sortOrder='+sortOrder);
            },
            sortChange(param){
                this.sortParameter = param;
                this.getClients();
            },
            currentPageGlobal(){
                Vue.prototype.$currentPageClients = this.pageInfo.current_page;
                this.currentPage = this.pageInfo.current_page;
            },
            customerStatus(is_temporary){
                if(is_temporary == 1) return 'Temporary';
                else return '-';
            },
            clearTable(){
                this.clientsTable = [];
            }
        },
        created() {
            this.getClients();
            Fire.$on('loadTable', () => {
                this.getClients();
            })
        },
    }
</script>
