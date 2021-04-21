<template>
    <div class="container">
        <div class="content-header mb-3" style="border-bottom: 1px solid black;">
            <div class="container-fluid">
                <div class="row mb-2 d-flex">
                    <div class="col-sm-6">
                        <h4 class="m-0 text-dark"><strong>Type Code</strong></h4>
                    </div>
              
                    <div class="col-sm-6">
                        <button type="button" class="btn button-standard float-right" v-if="isAdd" @click="cancel">
                            <i class="el-icon-arrow-left"></i> Back
                        </button>
                        <button type="button" class="btn button-standard float-right" v-if="!isAdd" @click="showAddForm">
                             Add Type Code
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-12">
                <el-card class="box-card" shadow="hover" v-show="isEdit">
                        <div slot="header">
                            <span>Edit Material Classifications</span>
                        </div>
                        <el-form ref="form" :model="form" label-width="15%" :label-position="`right`">
                            <el-form-item label="Type Code">
                                <el-col :span="12">
                                    <el-input v-model="form.type_code" clearable></el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Description">
                                <el-col :span="12">
                                    <el-input v-model="form.type_code_description" clearable></el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="RM">
                                <el-col :span="12">
                                    <el-input v-model="form.is_rm" clearable></el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="SC">
                                <el-col :span="12">
                                    <el-input v-model="form.is_sc" clearable></el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="FG">
                                <el-col :span="12">
                                    <el-input v-model="form.is_fg" clearable></el-input>
                                </el-col>
                            </el-form-item>
                            
                            <el-form-item>
                                <el-col :span="12" :offset="6">
                                <el-button @click="saveEdit(form.id)" type="primary"> <i class="fas fa-plus mr-1"></i> Save </el-button>
                                <el-button @click="cancel" type="primary"> <i class="fas fa-plus mr-1"></i> Cancel </el-button>
                                </el-col>
                            </el-form-item>
                            
                        </el-form>
                    </el-card>
                    <hr v-show="isEdit">
                    <el-card class="box-card" shadow="hover" v-show="isAdd">
                        <div slot="header">
                            <span>Add Type Code</span>
                        </div>
                        <el-form ref="form" :model="form" label-width="15%" :label-position="`right`">
                            <el-form-item label="Type Code">
                                <el-col :span="12">
                                    <el-input v-model="form.type_code" clearable></el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Description">
                                <el-col :span="12">
                                    <el-input v-model="form.type_code_description" clearable></el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="RM">
                                <el-col :span="12">
                                    <el-input v-model="form.is_rm" clearable></el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="SC">
                                <el-col :span="12">
                                    <el-input v-model="form.is_sc" clearable></el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="FG">
                                <el-col :span="12">
                                    <el-input v-model="form.is_fg" clearable></el-input>
                                </el-col>
                            </el-form-item>
                            
                            <el-form-item>
                                <el-col :span="12" :offset="6">
                                <el-button @click="addTypeCode" type="primary"> <i class="fas fa-plus mr-1"></i> Save </el-button>
                                <el-button @click="cancel" type="primary"> <i class="fas fa-plus mr-1"></i> Cancel </el-button>
                                </el-col>
                            </el-form-item>
                            
                        </el-form>
                    </el-card>
                    <hr v-show="isAdd">
                <div class="card">
                    <div class="card-header card-adamay-blue">
                            <h3 class="card-title">Type Codes</h3>
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
                            v-model="typeCodeTable"
                            row-key="typeCodeTableId"
                            height="500px"
                            :data="typeCodeTable"
                            @sort-change="sortChange"
                            :default-sort = "{prop: 'id', order: 'descending'}"
                            v-loading="loading"
                            element-loading-text="Loading..."
                            element-loading-spinner="el-icon-loading"
                            style="width: 100%">
                            <el-table-column 
                                prop="type_code"
                                label="Type Code"
                                :min-width="40"
                                sortable>
                            </el-table-column>
                            <el-table-column
                                prop="type_code_description"
                                label="Description"
                                :min-width="40"
                                sortable>
                            </el-table-column>
                            <el-table-column
                                label="RM"
                                :min-width="40"
                                sortable>
                                <template slot-scope="scope">
                                    <i class="icon el-icon-check" v-if="typeCodeTable[scope.$index].is_rm == 1"></i>
                                    <i class="icon el-icon-close" v-if="typeCodeTable[scope.$index].is_rm == 0"></i>
                                </template>
                            </el-table-column>
                            <el-table-column
                                label="SC"
                                :min-width="40"
                                sortable>
                                <template slot-scope="scope">
                                    <i class="icon el-icon-check" v-if="typeCodeTable[scope.$index].is_rm == 1"></i>
                                    <i class="icon el-icon-close" v-if="typeCodeTable[scope.$index].is_rm == 0"></i>
                                </template>
                            </el-table-column>
                            <el-table-column
                                label="Fg"
                                :min-width="40"
                                sortable>   
                                <template slot-scope="scope">
                                    <i class="icon el-icon-check" v-if="typeCodeTable[scope.$index].is_rm == 1"></i>
                                    <i class="icon el-icon-close" v-if="typeCodeTable[scope.$index].is_rm == 0"></i>
                                </template>
                            </el-table-column>
                            <el-table-column
                                label="Action"
                                :fixed="`right`"
                                width="100"
                                >
                                <template slot-scope="scope">

                                    <el-tooltip class="item" effect="dark" content="More info" placement="top">
                                        <router-link :to="'/view-material-classification/'+typeCodeTable[scope.$index].id+'/type-code/'+typeCodeTable[scope.$index].type_code+'/'+typeCodeTable[scope.$index].type_code_description">
                                            <i class="icon el-icon-info pointer" v-if="!showDeleted"></i>
                                            <i class="icon el-icon-info pointer info-deleted" v-else></i>
                                        </router-link>
                                    </el-tooltip>

                                    <el-tooltip class="item" effect="dark" content="Edit" placement="top" v-show="!showDeleted">
                                            <i class="icon el-icon-edit-outline pointer ml-2" @click="editType(typeCodeTable[scope.$index].id, 
                                        typeCodeTable[scope.$index].is_fg, typeCodeTable[scope.$index].is_rm, 
                                        typeCodeTable[scope.$index].is_sc, typeCodeTable[scope.$index].type_code, 
                                        typeCodeTable[scope.$index].type_code_description)"></i>
                                    </el-tooltip>

                                    <el-tooltip class="item" effect="dark" content="Delete" placement="top" v-if="!showDeleted">
                                        <i class="icon el-icon-delete pointer ml-2" @click="deleteTypeCode(typeCodeTable[scope.$index].id)"></i>
                                    </el-tooltip>
                                    <el-tooltip class="item" effect="dark" content="Restore" placement="top" v-else>
                                        <i class="icon el-icon-circle-check pointer ml-2" @click="deleteTypeCode(typeCodeTable[scope.$index].id)"></i>
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
                            <el-checkbox v-model="showDeleted" @change="showDeletedlocation">Show deleted location</el-checkbox>
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
                        id:'',
                        is_fg: '',
                        is_rm: '',
                        is_sc: '',
                        type_code: '',
                        type_code_description: '',
                    }
                ),
                isAdd: false,
                isEdit: false,
                typeCodeTable: [],
                search: '',
                pageInfo: {},
                perPage: 10,
                showDeleted: false,
                alert_text: '',
                keywords: '',
                sortParameter: {
                    "column":{},
                    "prop":"type_code",
                    "order":"ascending"
                },
                loading: '',
                checkIcon: false,
            }
        },
        methods: {
            getTypeCodes(){
                this.clearTable();
                this.loading = true;
                axios.get("api/type-code-list", { 
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
                    if(this.$currentPagelocation > 0){
                        this.handleCurrentChange(this.$currentPagelocation);
                    }
                    else{
                        this.typeCodeTable = data.data.data;
                        this.pageInfo = data.data;
                        this.pageInfo.current_page = this.$currentPagelocation;
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
                axios.get("api/type-code-list", { 
                    params: { 
                        take: this.perPage, 
                        showDeleted: this.showDeleted, 
                        search: this.keywords,
                        sortProp: this.sortParameter.prop, 
                        sortOrder: this.sortParameter.order,
                    } 
                })
                .then((data) => {
                    this.typeCodeTable = data.data.data;
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
                    this.typeCodeTable = data.data.data;
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
                    this.typeCodeTable = data.data.data;
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
                    this.typeCodeTable = data.data.data;
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
                axios.get("api/locations", { 
                    params: { 
                        take: this.perPage, 
                        showDeleted: this.showDeleted, 
                        search: this.keywords,
                        sortProp: this.sortParameter.prop, 
                        sortOrder: this.sortParameter.order,
                    } 
                })
                .then((data) => {
                    this.typeCodeTable = data.data.data;
                    this.pageInfo = data.data;
                    this.loading = false;
                })
                .catch(error => {
                    this.loaded = true;
                    this.loading = false;
                });
            },
            deleteTypeCode(id){
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
                        axios.delete('api/delete-type-code/'+id)
                        .then(() => {
                            Swal.fire(
                                'Success!',
                                'Type code has been '+this.alert_text+'d.',
                                'success'
                            )
                            this.getTypeCodes();
                            this.loading = false;
                        })
                        .catch(() => {
                            Swal.fire(
                                'Failed!',
                                'Failed to '+this.alert_text+' type code.',
                                'warning'
                            )
                            this.loading = false;
                        })
                    }
                })
            },
            showDeletedlocation(){
                this.clearTable();
                this.getTypeCodes();
            },
            exportPDF(){
                var keywords = this.keywords;
                var isDeleted = this.showDeleted;
                var sortProp = this.sortParameter.prop;
                var sortOrder = this.sortParameter.order;
                window.open('/export-pdf-material-maintenanace-type?keywords='+keywords+'&showDeleted='+isDeleted+'&sortProp='+sortProp+'&sortOrder='+sortOrder);
            },
            sortChange(param){
                this.sortParameter = param;
                this.getTypeCodes();
            },
            currentPageGlobal(){
                Vue.prototype.$currentPagelocation = this.pageInfo.current_page;
                this.currentPage = this.pageInfo.current_page;
            },
            clearTable(){
                this.employeesTable = [];
            },
            editType(id, is_fg, is_rm, is_sc, type_code, des){
                this.isEdit = true;
                this.form.id = id;
                this.form.is_fg = is_fg;
                this.form.is_rm = is_rm;
                this.form.is_sc = is_sc;
                this.form.type_code = type_code;
                this.form.type_code_description = des;
            },
            cancel(){
                this.isEdit = false;
                this.isAdd = false;
            },
            saveEdit(id){
                this.form.post('/api/update-type-code/'+id);
                this.getTypeCodes();
                console.log(id);
            },
            showAddForm(){
                this.isAdd = true;
                this.form.is_fg = '';
                this.form.is_rm = '';
                this.form.is_sc = '';
                this.form.type_code = '';
                this.form.type_code_description = '';
            },
            addTypeCode(){
                this.form.post('api/add-type-code')
                .then(()=>{
                    this.clearTable();
                    this.getTypeCodes();
                })
                .catch(()=>{})
            },
            isRm(is_rm){
                if(is_rm == 1) return '/';
                else return 'X';
            },
            isSc(is_sc){
                if(is_sc == 1) return '/';
                else return 'X';
            },
            isFg(is_fg){
                if(is_fg == 1) return '/'; 
                else return 'X';
            }
        },
        created() {
            this.getTypeCodes();
            Fire.$on('loadTable', () => {
                this.getTypeCodes();
            })
        },
    }
</script>
