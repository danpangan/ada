<template>
    <div class="container">
        <div class="content-header mb-3" style="border-bottom: 1px solid black;">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h4 class="m-0 text-dark"><strong>Material Classifications</strong></h4>
                    </div>
                    <div class="col-sm-6">
                        <button type="button" class="btn button-standard float-right" v-if="isAdd" @click="cancel">
                            <i class="el-icon-arrow-left"></i> Back
                        </button>
                        <button type="button" class="btn button-standard float-right" v-if="!isAdd" @click="showAddForm">
                             Add Material Classification
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-12">
                <el-card class="box-card" shadow="hover" v-show="isEdit | isAdd">
                        <div slot="header">
                            <span v-show="isAdd">Add Material Classification</span>
                            <span v-show="isEdit">Edit Material Classification</span>
                        </div>
                        <el-form ref="form" :model="form" label-width="15%" :label-position="`right`">
                             <el-form-item label="Type Code" prop="material_classification_name">
                                <el-col :span="12">
                                    <el-input v-model="typeCode" disabled></el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Classification Name" prop="material_classification_name">
                                <el-col :span="12">
                                    <el-input v-model="form.name" clearable></el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item>
                                <el-col :span="12" :offset="6">
                                <el-button v-show="isAdd" @click="addClassification" type="primary"> <i class="fas fa-plus mr-1"></i> Save </el-button>
                                <el-button v-show="isEdit" @click="saveEdit(form.id)" type="primary"> <i class="fas fa-plus mr-1"></i> Save </el-button>
                                <el-button @click="cancel" type="primary"> <i class="fas fa-plus mr-1"></i> Cancel </el-button>
                                </el-col>
                            </el-form-item>
                            
                        </el-form>
                    </el-card>
                <hr v-show="isEdit | isAdd">

                <div class="card">
                    <el-row type="flex" class="card-header card-adamay-blue">
                        <h3 class="card-title">Classifications And Type</h3>
                    </el-row>
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
                            v-model="classificationTable"
                            row-key="classificationTableId"
                            height="500px"
                            :data="classificationTable"
                            @sort-change="sortChange"
                            :default-sort = "{prop: 'id', order: 'descending'}"
                            v-loading="loading"
                            element-loading-text="Loading..."
                            element-loading-spinner="el-icon-loading"
                            style="width: 100%">
                            <el-table-column type="expand">
                            <template v-slot ="classificationTable">
                                <p>State: {{ classificationTable.material_classification_name }}</p>
                            </template>
                            </el-table-column>
                            <el-table-column 
                                prop="material_classification_name"
                                label="Classification Name"
                                :min-width="40"
                                sortable>
                            </el-table-column>
                            <el-table-column
                                label="Action"
                                :fixed="`right`"
                                width="100"
                                >
                                <template slot-scope="scope">
                                    <el-tooltip class="item" effect="dark" content="Edit" placement="top" v-show="!showDeleted">
                                            <i class="icon el-icon-edit-outline pointer ml-2" @click="editClass(classificationTable[scope.$index].type_code_id, classificationTable[scope.$index].material_classification_name, classificationTable[scope.$index].id)"></i>
                                    </el-tooltip>

                                    <el-tooltip class="item" effect="dark" content="Delete" placement="top" v-if="!showDeleted">
                                        <i class="icon el-icon-delete pointer ml-2" @click="deleteClass(classificationTable[scope.$index].id)"></i>
                                    </el-tooltip>
                                    <el-tooltip class="item" effect="dark" content="Restore" placement="top" v-else>
                                        <i class="icon el-icon-circle-check pointer ml-2" @click="deleteClass(classificationTable[scope.$index].id)"></i>
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
                        name:'',
                        typeCodeId:'',
                        id:'',
                    }
                ),
                classificationTable: [],
                search: '',
                pageInfo: {},
                perPage: 10,
                showDeleted: false,
                alert_text: '',
                keywords: '',
                sortParameter: {
                    "column":{},
                    "prop":"material_classification_name",
                    "order":"ascending"
                },
                loading: '',
                isEdit: false,
                isAdd: false,
                typeCode: '',

            }
        },
        methods: {
            getClassifications(){
                this.clearTable();
                this.loading = true;
                axios.get('/api/material-classifications-list/'+this.$route.params.type_code_id, { 
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
                        this.classificationTable = data.data.data;
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
                axios.get("api/material-classifications-list", { 
                    params: { 
                        take: this.perPage, 
                        showDeleted: this.showDeleted, 
                        search: this.keywords,
                        sortProp: this.sortParameter.prop, 
                        sortOrder: this.sortParameter.order,
                    } 
                })
                .then((data) => {
                    this.classificationTable = data.data.data;
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
                    this.classificationTable = data.data.data;
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
                    this.classificationTable = data.data.data;
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
                    this.classificationTable = data.data.data;
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
                    this.classificationTable = data.data.data;
                    this.pageInfo = data.data;
                    this.loading = false;
                })
                .catch(error => {
                    this.loaded = true;
                    this.loading = false;
                });
            },
            deleteClass(id){
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
                        axios.delete('api/delete-material-classification/'+id)
                        .then(() => {
                            Swal.fire(
                                'Success!',
                                'Province has been '+this.alert_text+'d.',
                                'success'
                            )
                            this.getClassifications();
                            this.loading = false;
                        })
                        .catch(() => {
                            Swal.fire(
                                'Failed!',
                                'Failed to '+this.alert_text+' province.',
                                'warning'
                            )
                            this.loading = false;
                        })
                    }
                })
            },
            showDeletedlocation(){
                this.clearTable();
                this.getClassifications();
            },
            exportPDF(){
                var keywords = this.keywords;
                var isDeleted = this.showDeleted;
                var sortProp = this.sortParameter.prop;
                var sortOrder = this.sortParameter.order;
                var typeCode = this.$route.params.type_code;
                var typeDescription = this.$route.params.descriptions;
                var typeCodeId = this.$route.params.type_code_id;
                window.open('/export-pdf-material-classifications?keywords='+keywords+'&showDeleted='+isDeleted+'&sortProp='+sortProp+'&sortOrder='+sortOrder+'&typeCode='+typeCode+'&typeDescription='+typeDescription+'&typeCodeId='+typeCodeId);
            },
            sortChange(param){
                this.sortParameter = param;
                this.getClassifications();
            },
            currentPageGlobal(){
                Vue.prototype.$currentPagelocation = this.pageInfo.current_page;
                this.currentPage = this.pageInfo.current_page;
            },
            clearTable(){
                this.employeesTable = [];
            },
            editClass(t, n, i){
                this.typeCode = this.$route.params.type_code;
                this.isEdit = true;
                this.isAdd = false;
                this.form.name = n;
                this.form.typeCodeId = t;
                this.form.id = i;
            },
            
            cancel(){
                this.isEdit = false;
                this.isAdd = false;
            },
            saveEdit(id){
                this.form.post('/api/update-material-classification/'+id);
                this.getClassifications();
                console.log(id);
            },
            showAddForm(){
                this.isAdd = true;
                this.isEdit = false;
                this.form.name = '';
                this.form.typeCodeId = '';
            },
            addClassification(){
                this.form.post('/add-material-classification/' +this.$route.params.type_code_id)
                .then(()=>{
                    this.clearTable();
                    this.getClassifications();
                })
                .catch(()=>{})
            },
            
        },
        created() {
            this.getClassifications();
            Fire.$on('loadTable', () => {
                this.getClassifications();
            })
        },
    }
</script>
