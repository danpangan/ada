<template>
    <div class="container">
        <div class="content-header mb-3" style="border-bottom: 1px solid black;">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h4 class="m-0 text-dark"><strong>Sales Management</strong></h4>
                    </div>
                    <div class="col-sm-6">
                        <router-link :to="'/add-cs'">
                            <button type="submit" class="btn btn-add btn-sm float-right">
                                <i class="fas fa-plus mr-1"></i> Add New CS
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
                        <h3 class="card-title">Computation Sheets</h3>
                    </div>
                    <div class="card-body">
                        <el-col :span="6" :offset="18">
                            <el-input
                            v-model="search"
                            @input="queryForKeywords"
                            size="small"
                            clearable
                            placeholder="Control Number"/>
                        </el-col>
                        <el-table
                            v-model="CSTable"
                            row-key="CSTableId"
                            height="500px"
                            :data="CSTable"
                            @sort-change="sortChange"
                            :default-sort = "{prop: 'id', order: 'descending'}"
                            v-loading="loading"
                            element-loading-text="Loading..."
                            element-loading-spinner="el-icon-loading"
                            style="width: 100%">
                            <el-table-column
                                prop="cs_control_number"
                                label="Control Number"
                                width="160"
                                sortable>
                            </el-table-column>
                            <el-table-column
                                label="Version"
                                width="90">
                                <template slot-scope="scope">
                                    <el-col class="text-center">
                                        <span>{{ 'v' + CSTable[scope.$index].cs_version_number }}</span>
                                    </el-col>
                                </template>
                            </el-table-column>
                            <el-table-column
                                prop="client_short_name"
                                sortable
                                width="150"
                                label="Customer">
                            </el-table-column>
                            <el-table-column
                                prop="order_part_number"
                                sortable
                                width="220"
                                label="Part Number">
                            </el-table-column>
                            <el-table-column
                                prop="cs_total_amount"
                                width="170"
                                label="Amount">
                                <template slot-scope="scope">
                                    <el-row>
                                        <el-col :span="6">
                                            <span>{{ CSTable[scope.$index].cs_currency }}</span>
                                        </el-col>
                                        <el-col class="text-right" :span="18">
                                            <span>{{ CSTable[scope.$index].cs_total_amount }}</span>
                                        </el-col>
                                    </el-row>
                                </template>
                            </el-table-column>
                            <el-table-column
                                label="Action"
                                :fixed="`right`"
                                width="130"
                                >
                                <template slot-scope="scope">
                                    <el-tooltip class="item" effect="dark" content="More info" placement="top">
                                        <router-link :to="'/view-cs/'+CSTable[scope.$index].id">
                                            <i class="icon el-icon-info pointer" v-if="!showDeleted"></i>
                                            <i class="icon el-icon-info pointer info-deleted" v-else></i>
                                        </router-link>
                                    </el-tooltip>

                                    <el-tooltip class="item" effect="dark" content="Edit" placement="top" v-show="!showDeleted">
                                        <router-link :to="'/update-cs/'+CSTable[scope.$index].id">
                                            <i class="icon el-icon-edit-outline pointer ml-2"></i>
                                        </router-link>
                                    </el-tooltip>

                                    <el-tooltip class="item" effect="dark" content="Generate Quotation" placement="top" v-show="!showDeleted">
                                        <router-link :to="'/update-cs/'+CSTable[scope.$index].id">
                                            <i class="el-icon-connection pointer ml-2"></i>
                                        </router-link>
                                    </el-tooltip>

                                    <el-tooltip class="item" effect="dark" content="Delete" placement="top" v-if="!showDeleted">
                                        <i class="icon el-icon-delete pointer ml-2" @click="deleteCS(CSTable[scope.$index].id)"></i>
                                    </el-tooltip>
                                    <el-tooltip class="item" effect="dark" content="Restore" placement="top" v-else>
                                        <i class="icon el-icon-circle-check pointer ml-2" @click="deleteCS(CSTable[scope.$index].id)"></i>
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
                            <el-checkbox v-model="showDeleted" @change="showDeletedCS">Show deleted CS</el-checkbox>
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
                CSTable: [],
                search: '',
                pageInfo: {},
                perPage: 10,
                showDeleted: false,
                alert_text: '',
                keywords: '',
                sortParameter: {
                    "column":{},
                    "prop":"id",
                    "order":"descending"
                },
                loading: '',
            }
        },
        methods: {
            getCS(){
                this.clearTable();
                this.loading = true;
                axios.get("api/cs", { 
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
                    if(this.$currentPageCS > 0){
                        this.handleCurrentChange(this.$currentPageCS);
                    }
                    else{
                        this.CSTable = data.data.data;
                        this.pageInfo = data.data;
                        this.pageInfo.current_page = this.$currentPageCS;
                        this.loading = false;   
                    }      
                })
                .catch(error => {
                    console.log(error)
                });
            },
            handleSizeChange(val) {
                this.clearTable();
                this.loading = true;
                this.perPage = val;
                axios.get("api/cs", { 
                    params: { 
                        take: this.perPage,
                        showDeleted: this.showDeleted, 
                        search: this.keywords,
                        sortProp: this.sortParameter.prop, 
                        sortOrder: this.sortParameter.order,
                    } 
                })
                .then((data) => {
                    this.CSTable = data.data.data;
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
                    this.CSTable = data.data.data;
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
                    this.CSTable = data.data.data;
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
                axios.get("api/cs", { 
                    params: { 
                        take: this.perPage, 
                        showDeleted: this.showDeleted, 
                        search: this.keywords,
                        sortProp: this.sortParameter.prop, 
                        sortOrder: this.sortParameter.order,
                    } 
                })
                .then((data) => {
                    this.CSTable = data.data.data;
                    this.pageInfo = data.data;
                    this.loading = false;
                })
                .catch(error => {
                    console.log(error);
                });
            },
            deleteCS(cs_id){
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
                        axios.delete('api/cs/'+cs_id)
                        .then((data) => {
                            Swal.fire(
                                'Success!',
                                'CS has been '+this.alert_text+'d.',
                                'success'
                            )
                            this.getCS();
                            this.loading = false;
                        })
                        .catch((error) => {
                            let error_message = '';
                            if(error.response.data.cs_version) error_message = error.response.data.cs_version;
                            else error_message = 'Failed to '+this.alert_text+' cs.';

                            Swal.fire({
                                icon: 'error',
                                title: 'Oops!',
                                text: error_message,
                            });
                            this.loading = false;
                        })
                    }
                })
            },
            showDeletedCS(){
                this.clearTable();
                this.getCS();
            },
            exportPDF(){
                var keywords = this.keywords;
                var isDeleted = this.showDeleted;
                var sortProp = this.sortParameter.prop;
                var sortOrder = this.sortParameter.order;
                window.open('/export-pdf-computation-sheets?keywords='+keywords+'&showDeleted='+isDeleted+'&sortProp='+sortProp+'&sortOrder='+sortOrder);
            },
            sortChange(param){
                this.sortParameter = param;
                this.getCS();
            },
            currentPageGlobal(){
                Vue.prototype.$currentPageCS = this.pageInfo.current_page;
                this.currentPage = this.pageInfo.current_page;
            },
            clearTable(){
                this.CSTable = [];
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
            this.getCS();
        },
    }
</script>
