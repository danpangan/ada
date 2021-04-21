<template>
    <div class="container">
        <div class="content-header mb-3" style="border-bottom: 1px solid black;">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h4 class="m-0 text-dark" v-show="isProvinceEdit"><strong>Edit Province</strong></h4>
                        <h4 class="m-0 text-dark" v-show="!isProvinceEdit"><strong>View Province</strong></h4>
                    </div>
                    <div class="col-sm-6">
                        <button type="button" class="btn button-standard float-right" @click="$router.push('/locations')">
                            <i class="el-icon-arrow-left"></i> Lists
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-12">
                <form @submit.prevent="updateRawProvince()">
                    <el-card class="box-card mb-3" shadow="hover">
                        <div slot="header">
                            <span>Location Information</span>
                        </div>
                        <el-form ref="form" :model="form" label-width="15%" :label-position="`right`">
                            <el-form-item label="Province Name">
                                <el-col :span="12">
                                    <el-input v-model="form.province_name" :disabled="!isProvinceEdit"></el-input>
                                </el-col>
                            </el-form-item>
                        </el-form>
                        <div class="outside-card-button">
                            <el-button type="primary" @click="toEdit" v-show="!isProvinceEdit"><i class="el-icon-edit-outline"></i> Edit</el-button>
                            <el-button type="primary" @click="updateProvince" v-show="isProvinceEdit"><i class="el-icon-edit-outline"></i> Update</el-button>
                            <el-button type="warning" v-on:click="resetFields" v-show="isProvinceEdit">
                                <i class="el-icon-refresh-left"></i> Reset
                            </el-button>
                        </div>
                    </el-card>
                </form>
            </div>
        </div>

        <div class="row justify-content-center mt-3" v-show="!isProvinceEdit">
            <div class="col-12">
                <div class="card">
                    <div class="card-header card-adamay-blue">
                        <h3 class="card-title">Municipalities</h3>
                    </div>
                    <div class="card-body">
                        <el-form ref="form" :model="form" label-width="15%" :label-position="`right`">
                            <el-form-item label="Municipality Name">
                                <el-col :span="8">
                                    <el-input v-model="form.municipality_name" clearable></el-input>
                                </el-col>
                            <el-col :span="6">
                                <button type="button" class="btn button-standard ml-3" size="mini" @click="createMunicipality">
                                    <i class="el-icon-plus"></i> Add Municipality
                                </button>
                            </el-col>
                            </el-form-item>
                        </el-form>
                        <hr>
                        <el-col :span="6">
                            <el-select v-model="filter" @change="getProvinceDetails" clearable placeholder="Select Filter">
                                <el-option
                                v-for="location_filter in $location_filters"
                                :key="location_filter.id"
                                :label="location_filter.filter_name"
                                :value="location_filter.id">
                                </el-option>
                            </el-select>
                        </el-col>
                        <el-col :span="6" :offset="18">
                            <el-input
                            v-model="search"
                            @input="queryForKeywords"
                            size="small"
                            clearable
                            placeholder="Search"/>
                        </el-col>
                        <el-table
                            v-model="municipalitiesTable"
                            row-key="municipalitiesTableId"
                            :data="municipalitiesTable"
                            :default-sort = "{prop: 'id', order: 'descending'}"
                            v-loading="loading"
                            element-loading-text="Loading..."
                            element-loading-spinner="el-icon-loading"
                            style="width: 100%">
                            <el-table-column
                                prop="municipality_name"
                                sortable
                                :min-width="70"
                                label="Municipality Name">
                            </el-table-column>
                            <el-table-column 
                                label="Action" 
                                :fixed="`right`"
                                width="100">
                                <template slot-scope="scope">
                                    <el-tooltip class="item" effect="dark" content="Edit" placement="top" v-show="!showDeleted">
                                        <router-link :to="'/update-municipality/'+municipalitiesTable[scope.$index].id">
                                            <i class="icon el-icon-edit-outline pointer ml-2"></i>
                                        </router-link>
                                    </el-tooltip>

                                    <el-tooltip class="item" effect="dark" content="Delete" placement="top" v-if="!showDeleted">
                                        <i class="icon el-icon-delete pointer ml-2" @click="deleteMunicipality(municipalitiesTable[scope.$index].id)"></i>
                                    </el-tooltip>
                                    <el-tooltip class="item" effect="dark" content="Restore" placement="top" v-else>
                                        <i class="icon el-icon-circle-check pointer ml-2" @click="deleteMunicipality(municipalitiesTable[scope.$index].id)"></i>
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
                            <el-checkbox v-model="showDeleted" @change="showDeletedMunicipalities">Show deleted municipalities</el-checkbox>
                        </el-col>
                    </div>
                    <el-col class="text-center mb-3">
                        <el-button class="pdf" size="mini" @click="exportPDFMunicipalities" round><i class="fas fa-file-pdf"></i> Download</el-button>
                    </el-col>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import moment from 'moment';
    export default {
        data(){
            return {
                form: new Form({
                    province_name: '',
                }),

                editMode: false,
                municipalitiesTable: [],
                search: '',
                pageInfo: {},
                perPage: 10,
                showDeleted: false,
                alert_text: '',
                keywords: '',
                sortParameter: {
                    "column":{},
                    "prop":"municipality_name",
                    "order":"ascending"
                },
                loading: '',
                municipality_name: '',
                isProvinceEdit: false,
                filter: '',
            }
        },
        methods: {
            updateProvince() {
                Swal.fire({
                    text: 'Are you sure you want to update province?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Update',
                    width: '25rem',
                })
                .then((result) => {
                    if(result.value){
                        this.isLoading = true;
                        this.form.put('/api/location/'+this.$route.params.province_id)
                        .then((res) => {
                            Swal.fire(
                                'Updated!',
                                'Province successfully updated.',
                                'success'
                            )
                            this.getProvinceDetails();
                            this.isProvinceEdit = false;
                        })
                        .catch(error => {
                            console.log(error)
                        });
                        this.isLoading = false;
                    }
                })
            },
            getProvinceDetails(){
                this.clearTable();
                this.loading = true;
                axios.get('/api/view-municipalities/'+this.$route.params.province_id, { 
                    params: { 
                        take: this.perPage, 
                        showDeleted: this.showDeleted, 
                        search: this.keywords, 
                        sortProp: this.sortParameter.prop, 
                        sortOrder: this.sortParameter.order,
                        filter: this.filter,
                    } 
                })
                .then((data) => {
                    this.pageInfo = data.data;
                    if(this.$currentPageMunicipalities > 0){
                        this.handleCurrentChange(this.$currentPageMunicipalities);
                    }
                    else{
                        this.municipalitiesTable = data.data.data;
                        this.pageInfo = data.data;
                        this.pageInfo.current_page = this.$currentPageMunicipalities;
                        this.form.province_name = this.$route.params.province_name;
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
                axios.get('/api/view-municipalities/'+this.$route.params.province_id, { 
                    params: { 
                        take: this.perPage, 
                        showDeleted: this.showDeleted, 
                        search: this.keywords,
                        sortProp: this.sortParameter.prop, 
                        sortOrder: this.sortParameter.order,
                        filter: this.filter,
                    } 
                })
                .then((data) => {
                    this.municipalitiesTable = data.data.data;
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
                        filter: this.filter,
                    } 
                })
                .then((data) => {
                    this.municipalitiesTable = data.data.data;
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
                        filter: this.filter,
                    } 
                })
                .then((data) => {
                    this.municipalitiesTable = data.data.data;
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
                        filter: this.filter,
                        } 
                    })
                .then((data) => {
                    this.municipalitiesTable = data.data.data;
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
                axios.get('/api/view-municipalities/'+this.$route.params.province_id, { 
                    params: { 
                        take: this.perPage, 
                        showDeleted: this.showDeleted, 
                        search: this.keywords,
                        sortProp: this.sortParameter.prop, 
                        sortOrder: this.sortParameter.order,
                        filter: this.filter,
                    } 
                })
                .then((data) => {
                    this.municipalitiesTable = data.data.data;
                    this.pageInfo = data.data;
                    this.loading = false;
                })
                .catch(error => {
                    this.loaded = true;
                    this.loading = false;
                });
            },
            deleteMunicipalities(province_id){
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
                        axios.delete('/api/view-municipalities/'+province_id)
                        .then(() => {
                            Swal.fire(
                                'Success!',
                                'Province has been '+this.alert_text+'d.',
                                'success'
                            )
                            this.getProvinceDetails();
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
            showDeletedMunicipalities(){
                this.clearTable();
                this.getProvinceDetails();
            },
            exportPDFMunicipalities(){
                var keywords = this.keywords;
                var isDeleted = this.showDeleted;
                var sortProp = this.sortParameter.prop;
                var sortOrder = this.sortParameter.order;
                var filter = this.filter;
                var province_id = this.$route.params.province_id;
                window.open('/export-pdf-location?keywords='+keywords+'&showDeleted='+isDeleted+'&sortProp='+sortProp+'&sortOrder='+sortOrder+'&filter='+filter+'&province_id='+province_id);
            },
            sortChange(param){
                this.sortParameter = param;
                this.getProvinceDetails();
            },
            currentPageGlobal(){
                Vue.prototype.$currentPageMunicipalities = this.pageInfo.current_page;
                this.currentPage = this.pageInfo.current_page;
            },
            clearTable(){
                this.municipalitiesTable = [];
            },
            pageType(){
                if(window.location.href.indexOf("update-province") > -1){
                    this.isProvinceEdit = true;
                }
                if(window.location.href.indexOf("update-municipality") > -1){
                    this.isProvinceEdit = false;
                }
                else{
                    this.isProvinceEdit = false;
                } 
            },
            toEdit(){
                this.isProvinceEdit = true;
            },
            resetFields(){
                this.getProvinceDetails();
            },
            createMunicipality(){
                Swal.fire({
                    text: 'Are you sure you want to register new municipality?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Register',
                    width: '25rem',
                })
                .then((result) => {
                    if(result.value){
                        this.form.post('/api/add-municipality/'+this.$route.params.province_id)
                        .then((res) => {
                            this.form.reset();
                            Swal.fire(
                                'Registered!',
                                'Municipality successfully registered.',
                                'success'
                            )
                            this.clearTable();
                            this.getProvinceDetails();
                        })
                        .catch(error => {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops!',
                                text: 'Municipality registration failed.',
                            })
                            console.log(error)
                        });
                    }
                })
            },
            deleteMunicipality(municipality_id){
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
                        axios.delete('/api/delete-municipality/'+municipality_id)
                        .then(() => {
                            Swal.fire(
                                'Success!',
                                'Municipality has been '+this.alert_text+'d.',
                                'success'
                            )
                            this.getProvinceDetails();
                            this.loading = false;
                        })
                        .catch(() => {
                            Swal.fire(
                                'Failed!',
                                'Failed to '+this.alert_text+' municipality.',
                                'warning'
                            )
                            this.loading = false;
                        })
                    }
                })
            }
        },
        created() {
            this.getProvinceDetails();
            this.pageType();
        },
        computed: {
            readonly(){
                if(!this.isProvinceEdit) return true;
            },
        },
        watch:{
            $route (to, from){
                this.pageType();
            }
        }
    }
</script>
