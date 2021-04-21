`<template>
    <div class="container">
        <div class="content-header mb-3" style="border-bottom: 1px solid black;">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h4 class="m-0 text-dark"><strong>Process Management</strong></h4>
                    </div>
                    <div class="col-sm-6">
                        <el-col  :span=24 style="text-align: right">
                            <el-dropdown>
                                <el-button type="primary">
                                    Add Price Bracket <i class="el-icon-arrow-down el-icon--right"></i>
                                </el-button>
                                <el-dropdown-menu slot="dropdown">
                                    <div v-for="dropdown in dropdowns" :key="dropdown.id">
                                        <el-dropdown-item>
                                            <router-link :to="'/add-price-bracket/'+dropdown.process_id+'/name/'+dropdown.name+'/show-all-bundle/'+dropdown.show_all_bundle">
                                                <p class="dropdown-item-links">{{ dropdown.name }}</p>
                                            </router-link>
                                        </el-dropdown-item>
                                    </div>
                                </el-dropdown-menu>
                            </el-dropdown>
                        </el-col>         
                    </div>
                </div>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="card">
                    <div class="card-header card-adamay-blue">
                        <h3 class="card-title">Processes</h3>
                    </div>
                    <div class="card-body">
                        <el-form :inline="true" class="demo-form-inline">
                            <el-form-item label="Process Name">
                                    <el-input placeholder="Process Name" v-model="form.name"></el-input>
                            </el-form-item>
                            <el-form-item>
                                <el-button @click="addProcess" type="primary"> <i class="fas fa-plus mr-1"></i> Add Process </el-button>
                            </el-form-item>
                        </el-form>
                    <hr>
                        <el-table
                            v-model="processesTable"
                            row-key="processesTableId"
                            height="500px"
                            :data="processesTable"
                            @sort-change="sortChange"
                            :default-sort = "{prop: 'id', order: 'descending'}"
                            v-loading="loading"
                            element-loading-text="Loading..."
                            element-loading-spinner="el-icon-loading"
                            style="width: 100%">
                            <el-table-column
                                prop="name"
                                label="Process Name"
                                sortable>
                            </el-table-column>
                             <el-table-column
                                label="Category">
                                <template slot-scope="scope">
                                    {{ categoryName(processesTable[scope.$index].source_table) }}
                                </template>
                            </el-table-column>
                            <el-table-column
                                label="Action"
                                :fixed="`right`"
                                width="70"
                                >
                                <template slot-scope="scope">
                                    <el-tooltip class="item" effect="dark" content="More info" placement="top">
                                        <router-link :to="'/view-process/'+processesTable[scope.$index].id+'/process/'+processesTable[scope.$index].source_table">
                                            <i class="icon el-icon-info pointer" v-if="!showDeleted"></i>
                                            <i class="icon el-icon-info pointer info-deleted" v-else></i>
                                        </router-link>
                                    </el-tooltip>
                                    <el-tooltip class="item" effect="dark" content="Delete" placement="top" v-if="!showDeleted">
                                        <i class="icon el-icon-delete pointer ml-2" v-show="processesTable[scope.$index].id >= 12" @click="deleteprocess(processesTable[scope.$index].id)"></i>
                                    </el-tooltip>
                                    <el-tooltip class="item" effect="dark" content="Restore" placement="top" v-else>
                                        <i class="icon el-icon-circle-check pointer ml-2" @click="deleteprocess(processesTable[scope.$index].id)"></i>
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
                            <el-checkbox v-model="showDeleted" @change="showDeletedprocesses">Show deleted processes</el-checkbox>
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
                form: new Form(
                    {
                        name:'',
                    }
                ),
                dropdowns:[
                    { id: 1,  name: 'Binding',         process_id: 10, show_all_bundle: 0},
                    { id: 2,  name: 'Bundled',         process_id: 16, show_all_bundle: 1},
                    { id: 3,  name: 'Cutting Book',    process_id: 4,  show_all_bundle: 0},
                    { id: 4,  name: 'Cutting Sheet',   process_id: 5,  show_all_bundle: 0},
                    { id: 5,  name: 'Diecut',          process_id: 1,  show_all_bundle: 0},
                    { id: 6,  name: 'Diecut Label',    process_id: 3,  show_all_bundle: 0},
                    { id: 7,  name: 'Folding Machine', process_id: 8,  show_all_bundle: 0},
                    { id: 8,  name: 'Folding Manual',  process_id: 9,  show_all_bundle: 0},
                    { id: 9,  name: 'Gathering',       process_id: 6,  show_all_bundle: 0},
                    { id: 10, name: 'GSM',             process_id: -1, show_all_bundle: 0},
                    { id: 11, name: 'Independent',     process_id: 19, show_all_bundle: 0},
                    { id: 12, name: 'Printing',        process_id: -2, show_all_bundle: 0},
                    { id: 13, name: 'Stitching',       process_id: 12, show_all_bundle: 0},
                ],

                editMode: false,
                bundled: false,
                processesTable: [],
                search: '',
                pageInfo: {},
                perPage: 10,
                showDeleted: false,
                alert_text: '',
                keywords: '',
                sortParameter: {
                    "column":{},
                    "prop":"id",
                    "order":"ascending"
                },
                loading: '',
            }
        },
        methods: { 
            getProcesses(){
                this.clearTable();
                this.loading = true;
                axios.get("api/process", { 
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
                    if(this.$currentPageprocesses > 0){
                        this.handleCurrentChange(this.$currentPageprocesses);
                    }
                    else{
                        this.processesTable = data.data.data;
                        this.pageInfo = data.data;
                        this.pageInfo.current_page = this.$currentPageprocesses;
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
                axios.get("api/process", { 
                    params: { 
                        take: this.perPage,
                        showDeleted: this.showDeleted, 
                        search: this.keywords,
                        sortProp: this.sortParameter.prop, 
                        sortOrder: this.sortParameter.order,
                    } 
                })
                .then((data) => {
                    this.processesTable = data.data.data;
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
                    this.processesTable = data.data.data;
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
                    this.processesTable = data.data.data;
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
                axios.get("api/process", { 
                    params: { 
                        take: this.perPage, 
                        showDeleted: this.showDeleted, 
                        search: this.keywords,
                        sortProp: this.sortParameter.prop, 
                        sortOrder: this.sortParameter.order,
                    } 
                })
                .then((data) => {
                    this.processesTable = data.data.data;
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
            deleteprocess(process_id){
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
                        axios.delete('api/process/'+process_id)
                        .then((data) => {
                            Swal.fire(
                                'Success!',
                                'process has been '+this.alert_text+'d.',
                                'success'
                            )
                            this.getProcesses();
                            this.loading = false;
                        })
                        .catch((error) => {
                            Swal.fire(
                                'Failed!',
                                'Failed to '+this.alert_text+' process.',
                                'warning'
                            )
                            this.loading = false;
                        })
                    }
                })
            },
            showDeletedprocesses(){
                this.clearTable();
                this.getProcesses();
            },
            exportPDF(){
                var keywords = this.keywords;
                var showDeleted = this.showDeleted;
                var sortProp = this.sortParameter.prop;
                var sortOrder = this.sortParameter.order;
                window.open('/export-pdf-processes?keywords='+keywords+'&showDeleted='+showDeleted+'&sortProp='+sortProp+'&sortOrder='+sortOrder);
            },
            sortChange(param){
                this.sortParameter = param;
                this.getProcesses();
            },
            currentPageGlobal(){
                Vue.prototype.$currentPageprocesses = this.pageInfo.current_page;
                this.currentPage = this.pageInfo.current_page;
            },
            clearTable(){
                this.processesTable = [];
            },

            addProcess(){
                   Swal.fire({
                    text: 'Are you sure you want to add process to list?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Add to list',
                    width: '25rem',
                })
                .then((result) => {
                    this.form.post('/api/process')
                    Swal.fire(
                        'Added to list!',
                        'Primary material successfully added to list.',
                        'success'
                    )
                    this.clearTable();
                    this.getProcesses();
                })
                .catch((error) => {
                    console.log(error);
                });
            },
            categoryName(source_table){
                if( source_table == 'matrix_bundled' ){
                    return 'Bundled';
                }
                else if(source_table == 'matrix_independent'){
                    return 'Independent Process';
                }
                else{
                    return 'Pre-defined';
                }
            }
        },
        created() {
            this.getProcesses();
        },
    }
</script>