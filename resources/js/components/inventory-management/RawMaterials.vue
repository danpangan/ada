<template>
    <div class="container">
        <div class="content-header mb-3" style="border-bottom: 1px solid black;">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h4 class="m-0 text-dark"><strong>Inventory Management</strong></h4>
                    </div>
                    <div class="col-sm-6">
                        <router-link :to="'/add-raw-material'">
                            <button type="submit" class="btn btn-add btn-sm float-right">
                                <i class="fas fa-plus mr-1"></i> Add New Material
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
                        <h3 class="card-title">Raw Materials</h3>
                    </div>
                    <div class="card-body">
                        <el-col :span="6" :offset="18">
                            <el-input
                            v-model="search"
                            @input="queryForKeywords"
                            size="small"
                            clearable
                            placeholder="Material Description"/>
                        </el-col>
                        <el-table
                            v-model="rawMaterialsTable"
                            row-key="rawMaterialsTableId"
                            height="500px"
                            :data="rawMaterialsTable"
                            @sort-change="sortChange"
                            :default-sort = "{prop: 'id', order: 'descending'}"
                            v-loading="loading"
                            element-loading-text="Loading..."
                            element-loading-spinner="el-icon-loading"
                            style="width: 100%">
                            <el-table-column
                                sortable
                                prop="material_description"
                                label="Material Description">
                            </el-table-column>
                            <el-table-column
                                prop="material_type_id"
                                label="Material Type">
                                <template slot-scope="scope">
                                    {{ materialType(rawMaterialsTable[scope.$index].material_type_id) }}
                                </template>
                            </el-table-column>
                            <el-table-column
                                label="Classification">
                                <template slot-scope="scope">
                                    {{ materialClassification(rawMaterialsTable[scope.$index].material_classification_id) }}
                                </template>
                            </el-table-column>
                            <el-table-column
                                label="Dimension">
                                <template slot-scope="scope">
                                    {{ materialDimension(rawMaterialsTable[scope.$index].material_length, rawMaterialsTable[scope.$index].material_length_unit, rawMaterialsTable[scope.$index].material_width, rawMaterialsTable[scope.$index].material_width_unit) }}
                                </template>
                            </el-table-column>
                            <el-table-column
                                label="Action"
                                :fixed="`right`"
                                width="100"
                                >
                                <template slot-scope="scope">
                                    <el-tooltip class="item" effect="dark" content="More info" placement="top">
                                        <router-link :to="'/view-raw-material/'+rawMaterialsTable[scope.$index].id">
                                            <i class="icon el-icon-info pointer" v-if="!showDeleted"></i>
                                            <i class="icon el-icon-info pointer info-deleted" v-else></i>
                                        </router-link>
                                    </el-tooltip>

                                    <el-tooltip class="item" effect="dark" content="Edit" placement="top" v-show="!showDeleted">
                                        <router-link :to="'/update-raw-material/'+rawMaterialsTable[scope.$index].id">
                                            <i class="icon el-icon-edit-outline pointer ml-2"></i>
                                        </router-link>
                                    </el-tooltip>

                                    <el-tooltip class="item" effect="dark" content="Delete" placement="top" v-if="!showDeleted">
                                        <i class="icon el-icon-delete pointer ml-2" @click="deleteRawMaterial(rawMaterialsTable[scope.$index].id)"></i>
                                    </el-tooltip>
                                    <el-tooltip class="item" effect="dark" content="Restore" placement="top" v-else>
                                        <i class="icon el-icon-circle-check pointer ml-2" @click="deleteRawMaterial(rawMaterialsTable[scope.$index].id)"></i>
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
                            <el-checkbox v-model="showDeleted" @change="showDeletedRawMaterials">Show deleted raw materials</el-checkbox>
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
                rawMaterialsTable: [],
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
                materialTypeCodes: '',
                materialClassifications: [],
            }
        },
        methods: {
            getRawMaterials(){
                this.clearTable();
                this.loading = true;
                axios.get("api/material", { 
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
                    this.getMaterialClassifications();
                    if(this.$currentPageRawMaterials > 0){
                        this.handleCurrentChange(this.$currentPageRawMaterials);
                    }
                    else{
                        this.rawMaterialsTable = data.data.data;
                        this.pageInfo = data.data;
                        this.pageInfo.current_page = this.$currentPageRawMaterials;
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
                axios.get("api/material", { 
                    params: { 
                        take: this.perPage,
                        showDeleted: this.showDeleted, 
                        search: this.keywords,
                        sortProp: this.sortParameter.prop, 
                        sortOrder: this.sortParameter.order,
                    } 
                })
                .then((data) => {
                    this.rawMaterialsTable = data.data.data;
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
                    this.rawMaterialsTable = data.data.data;
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
                    this.rawMaterialsTable = data.data.data;
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
                axios.get("api/material", { 
                    params: { 
                        take: this.perPage, 
                        showDeleted: this.showDeleted, 
                        search: this.keywords,
                        sortProp: this.sortParameter.prop, 
                        sortOrder: this.sortParameter.order,
                    } 
                })
                .then((data) => {
                    this.rawMaterialsTable = data.data.data;
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
            deleteRawMaterial(raw_material_id){
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
                        axios.delete('api/material/'+raw_material_id)
                        .then((data) => {
                            Swal.fire(
                                'Success!',
                                'Supplier has been '+this.alert_text+'d.',
                                'success'
                            )
                            this.getRawMaterials();
                            this.loading = false;
                        })
                        .catch((error) => {
                            Swal.fire(
                                'Failed!',
                                'Failed to '+this.alert_text+' supplier.',
                                'warning'
                            )
                            this.loading = false;
                        })
                    }
                })
            },
            showDeletedRawMaterials(){
                this.clearTable();
                this.getRawMaterials();
            },
            exportPDF(){
                var keywords = this.keywords;
                var isDeleted = this.showDeleted;
                var sortProp = this.sortParameter.prop;
                var sortOrder = this.sortParameter.order;
                window.open('/export-pdf-raw-materials?keywords='+keywords+'&showDeleted='+isDeleted+'&sortProp='+sortProp+'&sortOrder='+sortOrder);
            },
            sortChange(param){
                this.sortParameter = param;
                this.getRawMaterials();
            },
            currentPageGlobal(){
                Vue.prototype.$currentPageRawMaterials = this.pageInfo.current_page;
                this.currentPage = this.pageInfo.current_page;
            },
            clearTable(){
                this.rawMaterialsTable = [];
            },
            getMaterialType(){
                axios.get('api/get-rm-type-codes')
                .then((res) => {
                    this.materialTypeCodes = res.data;
                })
                .catch((error) => {
                    console.log(error);
                });
            },
            getMaterialClassifications(){
                axios.get('api/get-material-classifications')
                .then((res) => {
                    this.materialClassifications = res.data;
                })
                .catch((error) => {
                    console.log(error);
                });
            },
            materialDimension(length, length_unit, width, width_unit){
                return length + ' ' + length_unit + ' x ' + width + ' ' + width_unit;
            },
            materialType(type_id){
                for(let i = 0; i < this.materialTypeCodes.length; i++){
                    if(this.materialTypeCodes[i].id == type_id){
                        let code = '';
                        let description = '';
                        code = this.materialTypeCodes[i].type_code;
                        description = this.materialTypeCodes[i].type_code_description;
                        return code + ' - ' + description;
                    }
                }
            },
            materialClassification(classification_id){
                let object = this.materialClassifications.find(classification => classification.id == classification_id);
                return _.values(object)[2];
            },
        },
        created() {
            this.getRawMaterials();
            this.getMaterialType();
        },
    }
</script>
