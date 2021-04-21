`<template>
    <div class="container">
        <div class="content-header mb-3" style="border-bottom: 1px solid black;">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h4 class="m-0 text-dark"><strong>Process Maintenance</strong></h4>
                    </div>
                    <div class="col-sm-6">
                        <button type="button" class="btn button-standard float-right" @click="$router.push('/process-maintenance')">
                            <i class="el-icon-arrow-left"></i> Lists
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="card">
                    <div class="card-header card-adamay-blue">
                        <h3 class="card-title">{{ process_name }}</h3>
                    </div>
                    <div class="card-body">
                        
                            <el-form ref="form" :model="form" label-width="15%" :label-position="`right`" v-show="identifier!= -1 && identifier!=-2">
                            <el-form-item label="Process Name" prop="table_name">
                                <el-col :span="12">
                                    <el-input :disabled="bundleInput==false" v-model="process_name" clearable></el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Quantity">
                                <el-col :span="12">
                                <el-input placeholder="Quantity" v-model="form.qty" clearable></el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Price">
                                <el-col :span="12">
                                <el-input placeholder="Price" v-model="form.price" clearable></el-input>
                                </el-col>
                            </el-form-item>
                             <el-form-item label="Paper Size" v-show="!bundled">
                                 <el-col :span="12">
                                <el-input placeholder="Paper Size" v-model="form.paper_size" clearable></el-input>
                                </el-col>
                            </el-form-item>
                             <el-form-item label="Status" v-show="!bundled">
                                 <el-col :span="12">
                                <el-input placeholder="Status" v-model="form.status" clearable></el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Bundle" v-show="bundled">
                                 <el-col :span="12">
                                <el-input v-model="form.bundle" :disabled="bundleInput==false" clearable></el-input>
                                </el-col>
                            </el-form-item>
                            <hr>
                            <div>
                            <el-form-item>
                                <el-col :span="12" :offset="6">
                                <el-button @click="addPrice" type="primary"> <i class="fas fa-plus mr-1"></i> Add Price Bracket </el-button>
                                </el-col>
                            </el-form-item>
                            </div>

                        </el-form>
                        <!-- FORM for GSM -->
                        <el-form ref="form" :model="form" label-width="15%" :label-position="`right`" v-if="identifier == -1">
                            <el-form-item label="GSM From">
                                 <el-col :span="12">
                                <el-input v-model="form.gsmFrom" clearable></el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="GSM To">
                                 <el-col :span="12">
                                <el-input v-model="form.gsmTo" clearable></el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Sheets">
                                 <el-col :span="12">
                                <el-input v-model="form.sheets" clearable></el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item>
                                <el-col :span="12" :offset="6">
                                <el-button @click="addPrice" type="primary"> <i class="fas fa-plus mr-1"></i> Add Price Bracket </el-button>
                                </el-col>
                            </el-form-item>
                        </el-form>
                        <!-- FORM for Printing -->
                        <el-form ref="form" :model="form" label-width="15%" :label-position="`right`" v-if="identifier == -2">
                            <el-form-item label="Color Type">
                                 <el-col :span="12">
                                <el-input v-model="form.colorType" clearable></el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Quantity">
                                 <el-col :span="12">
                                <el-input v-model="form.qty" clearable></el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Price">
                                 <el-col :span="12">
                                <el-input v-model="form.price" clearable></el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item>
                                <el-col :span="12" :offset="6">
                                <el-button @click="addPrice" type="primary"> <i class="fas fa-plus mr-1"></i> Add Price Bracket </el-button>
                                </el-col>
                            </el-form-item>
                        </el-form>

                        <h5><strong> {{process_name}} Table </strong> </h5> 

                <hr>
                    
                        <el-table
                            v-show="identifier!= -1 && identifier!=-2"
                            v-model="processTable"
                            row-key="processTableId"
                            height="500px"
                            :data="processTable"
                            @sort-change="sortChange"
                            :default-sort = "{prop: 'id', order: 'descending'}"
                            v-loading="loading"
                            element-loading-text="Loading..."
                            element-loading-spinner="el-icon-loading"
                            style="width: 100%">
                           
                            <el-table-column
                                prop="qty"
                                label="Quantity"
                                sortable>
                            </el-table-column>
                            <el-table-column
                                prop="price"
                                label="Price"
                                sortable>
                            </el-table-column>
                            <el-table-column
                                v-if="!bundled"
                                prop="paper_size"
                                sortable
                                label="Paper Size">
                            </el-table-column>

                            <el-table-column
                                label="Action"
                                >
                                <template slot-scope="scope">    
                                    <el-tooltip class="item" effect="dark" content="Delete" placement="top" v-if="!showDeleted">
                                        <i class="icon el-icon-delete pointer ml-2" @click="deleteProcess(processTable[scope.$index].id)"></i>
                                    </el-tooltip>
                                    <el-tooltip class="item" effect="dark" content="Restore" placement="top" v-else>
                                        <i class="icon el-icon-circle-check pointer ml-2" @click="deleteProcess(processTable[scope.$index].id)"></i>
                                    </el-tooltip>
                                </template>
                            </el-table-column>
                            </el-table>

                            <!-- For GSM -->
                            <el-table
                            v-if="identifier==-1"
                            v-model="processTable"
                            row-key="processTableId"
                            height="500px"
                            :data="processTable"
                            @sort-change="sortChange"
                            :default-sort = "{prop: 'id', order: 'descending'}"
                            v-loading="loading"
                            element-loading-text="Loading..."
                            element-loading-spinner="el-icon-loading"
                            style="width: 100%">
                            <el-table-column
                                prop="gsm_from"
                                label="GSM From"
                                sortable>
                            </el-table-column>
                            <el-table-column
                                prop="gsm_to"
                                label="GSM To"
                                sortable>
                            </el-table-column>
                            <el-table-column
                                prop="sheets"
                                label="Sheets"
                                sortable>
                            </el-table-column>
                    
                            <el-table-column
                                label="Action"
                                >
                                <template slot-scope="scope">    
                                    <el-tooltip class="item" effect="dark" content="Delete" placement="top" v-if="!showDeleted">
                                        <i class="icon el-icon-delete pointer ml-2" @click="deleteProcess(processTable[scope.$index].id)"></i>
                                    </el-tooltip>
                                    <el-tooltip class="item" effect="dark" content="Restore" placement="top" v-else>
                                        <i class="icon el-icon-circle-check pointer ml-2" @click="deleteProcess(processTable[scope.$index].id)"></i>
                                    </el-tooltip>
                                </template>
                            </el-table-column>
                        </el-table>

                        <el-table
                            v-if="identifier==-2"
                            v-model="processTable"
                            row-key="processTableId"
                            height="500px"
                            :data="processTable"
                            @sort-change="sortChange"
                            :default-sort = "{prop: 'id', order: 'descending'}"
                            v-loading="loading"
                            element-loading-text="Loading..."
                            element-loading-spinner="el-icon-loading"
                            style="width: 100%">
                            <el-table-column
                                prop="qty"
                                label="Quantity"
                                sortable>
                            </el-table-column>
                            <el-table-column
                                prop="price"
                                label="Price"
                                sortable>
                            </el-table-column>
                            <el-table-column
                                prop="color_type"
                                label="Color Type"
                                sortable>
                            </el-table-column>
                    
                            <el-table-column
                                label="Action"
                                >
                                <template slot-scope="scope">    
                                    <el-tooltip class="item" effect="dark" content="Delete" placement="top" v-if="!showDeleted">
                                        <i class="icon el-icon-delete pointer ml-2" @click="deleteProcess(processTable[scope.$index].id)"></i>
                                    </el-tooltip>
                                    <el-tooltip class="item" effect="dark" content="Restore" placement="top" v-else>
                                        <i class="icon el-icon-circle-check pointer ml-2" @click="deleteProcess(processTable[scope.$index].id)"></i>
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

                    <div class="d-flex justify-content-center">
                        <div style="margin-right:10px">
                        <el-button class="pdf" size="mini" @click="exportPDF" round><i class="fas fa-file-pdf"></i> Download</el-button>
                        </div>
                        
                        <el-form @submit.prevent="formSubmit">
                            <el-button class="pdf" v-if="file==''" size="mini" @click="onBrowse" round><i class="fas fa-file-excel"></i>Import Excel File</el-button>
                            <input type="file" v-show="false" name="import" ref="input" @input="handleUpload">
                            <el-button class="pdf" @click="formSubmit" size="mini" v-if="file" round><i class="fas fa-file-excel"></i>Import {{file.name}}</el-button>
                            <el-button @click="cancelImport" size="mini" v-if="file" round><i class="fas fa-times"></i></el-button>
                        </el-form>
                    </div>
                                            
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
                        qty:'',
                        process_id:'',
                        paper_size:'',
                        price:'',
                        status:'',
                        bundle: '',
                        gsmFrom: '',
                        gsmTo:'',
                        sheets: '',
                        colorType: '',
            

                    }

                ),
                bundled: false,
                bundleInput: false,
                processTable: [],
                search: '',
                pageInfo: {},
                perPage: 10,
                showDeleted: false,
                alert_text: '',
                keywords: '',
                process_name:'',
                sortParameter: {
                    "column":{},
                    "prop":"id",
                    "order":"ascending"
                },
                loading: '',
                matrix_process:'',
                bundle_number:'',
                table_name:'',
                identifier:'',
                show_all_bundle:'',
                dropDownName: '',
                file: '',
                name: '',
                success: '',
                importedSuccess: false,
                process_id: '',
                isMatrixIndependent: false,
            }
        },
        methods: { 
            getProcess(){
                this.clearTable();
                this.loading = true;
                // console.log(this.$route.{.process_id)
              axios.get('/api/process/'+this.$route.params.process_id, { 
                 params: { 
                        take: this.perPage, 
                        showDeleted: this.showDeleted, 
                        search: this.keywords, 
                        sortProp: this.sortParameter.prop, 
                        sortOrder: this.sortParameter.order,
                        show_all_bundle: this.$route.params.show_all_bundle,
                        name: this.$route.params.name,
                        sourceTable: this.$route.params.source_table,
                    } 
                })
                .then((data) => {
                    this.form.process_id = this.$route.params.process_id;
                    this.processTable = data.data.table_data.data;
                    this.process_name = data.data.table_name;
                    this.pageInfo = data.data.table_data;
                    this.loading = false;
                    this.matrix_process = data.data.matrix_process;
                    this.bundle_number = data.data.bundle_number;
                    this.form.bundle = data.data.bundle_number;
                    this.identifier = data.data.identifier;
                    this.show_all_bundle = this.$route.params.show_all_bundle;
                    this.dropDownName = this.$route.params.name;
                    this.process_id =  this.$route.params.process_id;
                    if(this.matrix_process == 'matrix_bundled'){
                        this.bundled = true;

                    }
                    if(this.process_name == 'Matrix Bundled'){
                        this.bundleInput = true;
                        this.form.bundle = '';
                    }
                    if(this.matrix_process == 'matrix_independent'){
                        this.isMatrixIndependent = true;
                    }
                })
                .catch((error) => {
                    this.loading = false;
                })
                
            },
            handleSizeChange(val) {
                this.clearTable();
                this.loading = true;
                this.perPage = val;
                axios.get("/api/process/"+this.$route.params.process_id, { 
                    params: { 
                        take: this.perPage, 
                        showDeleted: this.showDeleted, 
                        search: this.keywords, 
                        sortProp: this.sortParameter.prop, 
                        sortOrder: this.sortParameter.order,
                        show_all_bundle: this.$route.params.show_all_bundle,
                        name: this.$route.params.name,
                        sourceTable: this.$route.paramas.source_table, 
                    }
                })
                .then((data) => {
                    this.processTable = data.data.table_data.data;
                    this.pageInfo = data.data.table_data;
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
                        show_all_bundle: this.$route.params.show_all_bundle,
                        name: this.$route.params.name,
                    } 
                })
                .then((data) => {
                    this.processTable = data.data.table_data.data;
                    this.pageInfo = data.data.table_data;
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
                        show_all_bundle: this.$route.params.show_all_bundle,
                        name: this.$route.params.name,
                    } 
                })
                .then((data) => {
                    this.processTable = data.data.table_data.data;
                    this.pageInfo = data.data.table_data;
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
                axios.get("/api/process", { 
                    params: { 
                        take: this.perPage, 
                        showDeleted: this.showDeleted, 
                        search: this.keywords,
                        sortProp: this.sortParameter.prop, 
                        sortOrder: this.sortParameter.order,
                        show_all_bundle: this.$route.params.show_all_bundle,
                        name: this.$route.params.name,
                    } 
                })
                .then((data) => {
                    this.processTable = data.data.table_data.data;
                    this.pageInfo = data.data.table_data;
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
            deleteProcess(table_id){
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
                        axios.delete('/api/delete-price-bracket/'+table_id+'/source_table/'+this.process_id)
                        .then((data) => {
                            Swal.fire(
                                'Success!',
                                'process has been '+this.alert_text+'d.',
                                'success'
                            )
                            this.getProcess();
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
                this.getProcess();
            },
            exportPDF(){ 
                var keywords = this.keywords;
                var isDeleted = this.showDeleted;
                var sortProp = this.sortParameter.prop;
                var sortOrder = this.sortParameter.order;
                var identifier = this.identifier;
                var process_name = this.process_name;
                var matrix_process = this.matrix_process;
                var bundled = this.bundled;
                var bundle_number = this.bundle_number;
                var show_all_bundle = this.show_all_bundle;
                var dropDownName = this.dropDownName;
                var processTable = this.processTable;
                window.open('/export-pdf-process?keywords='+keywords+'&showDeleted='+isDeleted+'&sortProp='
                +sortProp+'&sortOrder='+sortOrder+'&identifier='+identifier+'&process_name='+process_name+
                '&matrix_process='+matrix_process+'&bundled='+bundled+'&bundle_number='+bundle_number+'&show_all_bundle='+show_all_bundle+
                '&processTable='+processTable+'&dropDownName='+dropDownName);
            },
            sortChange(param){
                this.sortParameter = param;
                this.getProcess();
            },
            currentPageGlobal(){
                Vue.prototype.$currentPageprocesses = this.pageInfo.current_page;
                this.currentPage = this.pageInfo.current_page;
            },
            clearTable(){
                this.processTable = [];
            },

            addPrice(){

                var id = this.$route.params.process_id;
                var matrix_process = this.matrix_process;
                this.form.post('/api/add-price-bracket?matrix_process='+matrix_process)
                this.clearTable();
                this.getProcess();
            },

            onBrowse(){
                this.$refs.input.click()
            },

            handleUpload(e){
                
                console.log(e.target.files[0]);
                this.file = e.target.files[0];
            },
            
           formSubmit(e) {
                e.preventDefault();
                let currentObj = this;
   
                const config = {
                    headers: { 'content-type': 'multipart/form-data' }
                }
    
                let formData = new FormData();
                formData.append('file', this.file);

                var matrix_process = this.matrix_process;

                let id = this.matrix_process;
                axios.post('/api/import?matrix_process='+matrix_process, formData, config)
                // .then(function (response) {
                //     currentObj.success = response.data.success;
                //     this.file = '';
                //     console.log(response);
                //     this.importedSuccess = true;
                // })

                .then(()=>{
                    this.file = '';
                })
        
                .catch(function (error) {
                    currentObj.output = error;
                });

              
            },
            cancelImport(){
                this.file = '';
            }

        },
        created() {
            this.getProcess();
        },
        watch:{
            $route (to, from){
                this.pageType();
            }
        }
    }
</script>