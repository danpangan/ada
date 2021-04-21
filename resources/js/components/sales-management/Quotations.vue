<template>
    <div class="container">
        <div class="content-header mb-3" style="border-bottom: 1px solid black;">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h4 class="m-0 text-dark"><strong>Sales Management</strong></h4>
                    </div>
                    <div class="col-sm-6">
                        <router-link :to="'/add-quotation'">
                            <button type="submit" class="btn btn-add btn-sm float-right">
                                <i class="fas fa-plus mr-1"></i> Add New Quotation
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
                        <h3 class="card-title">Quotations</h3>
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
                            v-model="quotationTable"
                            row-key="quotationTableId"
                            height="500px"
                            :data="quotationTable"
                            @sort-change="sortChange"
                            :default-sort = "{prop: 'id', order: 'descending'}"
                            v-loading="loading"
                            element-loading-text="Loading..."
                            element-loading-spinner="el-icon-loading"
                            style="width: 100%">
                            <el-table-column
                                prop="quotation_number"
                                label="Quotation No."
                                width="200"
                                sortable>
                            </el-table-column>
                             <!-- prop="client_short_name"
                                label="Client"
                                width="100"
                                sortable>
                            </el-table-column>
                            <el-table-column
                                prop="client_pic"
                                sortable
                                label="PIC">
                            </el-table-column>
                            <el-table-column
                                prop="quotation_qty_unit"
                                sortable
                                label="Quantity Unit">
                            </el-table-column>
                            <el-table-column
                                prop="quotation_specs"
                                sortable
                                label="Specs">
                            </el-table-column>
                            <el-table-column
                                prop="quotation_is_valid"
                                sortable
                                label="Validity">
                                <template slot-scope="scope">
                                            <i :class="'icon '+validityIcon(quotationTable[scope.$index].quotation_is_valid)+' ml-2'"></i>
                                            
                                        </template> -->
                            <el-table-column
                                prop="client_name"
                                label="Customer"
                                width="200"
                                sortable>
                            </el-table-column>
                            <el-table-column
                                prop="pic_name"
                                width="180"
                                label="PIC">
                            </el-table-column>
                            <el-table-column
                                width="120"
                                label="Status">
                                <template slot-scope="scope">
                                    <span>{{ quotationStatus(quotationTable[scope.$index].quotation_status) }}</span>
                                </template>
                            </el-table-column>
                            <el-table-column
                                width="120"
                                label="Valid">
                                <!-- <template slot-scope="scope">
                                    <span>{{ quotationTable[scope.$index].quotation_is_valid == 1 ? 'Valid' : 'Invalid' }}</span>
                                </template> -->
                                <template slot-scope="scope">
                                        <i :class="'icon '+validityIcon(quotationTable[scope.$index].quotation_is_valid)+' ml-2'"></i>
                                </template>
                            </el-table-column>
                            <el-table-column
                                label="Action"
                                :fixed="`right`"
                                width="160"
                                >
                                <template slot-scope="scope">
                                    <el-tooltip class="item" effect="dark" content="More info" placement="top">
                                        <router-link :to="'/view-quotation/'+quotationTable[scope.$index].id">
                                            <i class="icon el-icon-info pointer" v-if="!showDeleted"></i>
                                            <i class="icon el-icon-info pointer info-deleted" v-else></i>
                                        </router-link>
                                    </el-tooltip>

                                    <el-tooltip class="item" effect="dark" content="Edit" placement="top" v-show="!showDeleted">
                                        <router-link :to="'/update-quotation/'+quotationTable[scope.$index].id">
                                            <i class="icon el-icon-edit-outline pointer ml-2"></i>
                                        </router-link>
                                    </el-tooltip>

                                    <el-tooltip class="item" effect="dark" content="Copy" placement="top" v-show="!showDeleted">
                                        <!-- <router-link :to="'/copy-quotation/'+quotationTable[scope.$index].id"> -->
                                            <i class="icon el-icon-document-copy pointer ml-2" @click="copyQuoatation(quotationTable[scope.$index].id)"></i>
                                        <!-- </router-link> -->
                                    </el-tooltip>
                                    <el-tooltip class="item" effect="dark" content="Preview" placement="top" v-if="!showDeleted">
                                        <i class="icon el-icon-search pointer ml-2" @click="printQuotation(quotationTable[scope.$index].id)"></i>
                                    </el-tooltip>

                                    <el-tooltip class="item" effect="dark" content="Delete" placement="top" v-if="!showDeleted">
                                        <i class="icon el-icon-delete pointer ml-2" @click="deleteQuote(quotationTable[scope.$index].id)"></i>
                                    </el-tooltip>
                                    <el-tooltip class="item" effect="dark" content="Restore" placement="top" v-else>
                                        <i class="icon el-icon-circle-check pointer ml-2" @click="deleteQuote(quotationTable[scope.$index].id)"></i>
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
                            <el-checkbox v-model="showDeleted" @change="showDeletedOrders">Show deleted quotations</el-checkbox>
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
                quotationTable: [],
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
                loading: '',
            }
        },
        methods: {
            getQuotation(){
                this.clearTable();
                this.loading = true;
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
                    this.pageInfo = data.data;
                    if(this.$currentPageSuppliers > 0){
                        this.handleCurrentChange(this.$currentPageSuppliers);
                    }
                    else{
                        this.quotationTable = data.data.data;
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
                    this.quotationTable = data.data.data;
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
                    this.quotationTable = data.data.data;
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
                    this.quotationTable = data.data.data;
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
                    this.quotationTable = data.data.data;
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
                        axios.delete('api/quotation/'+id)
                        .then((data) => {
                            Swal.fire(
                                'Success!',
                                'Quotation has been '+this.alert_text+'d.',
                                'success'
                            )
                            this.getQuotation();
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
                this.quotationTable = [];
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
            }
        },
        created() {
            this.getQuotation();
        },
        
    }
</script>
