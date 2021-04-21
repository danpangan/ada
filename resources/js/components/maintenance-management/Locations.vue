<template>
    <div class="container">
        <div class="content-header mb-3" style="border-bottom: 1px solid black;">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h4 class="m-0 text-dark"><strong>Location Maintenance</strong></h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="card">
                    <div class="card-header card-adamay-blue">
                        <h3 class="card-title">Locations</h3>
                    </div>
                    <div class="card-body">
                        <el-form ref="form" :model="form" label-width="15%" :label-position="`right`">
                            <el-form-item label="Province Name">
                                <el-col :span="8">
                                    <el-input v-model="form.province_name" clearable></el-input>
                                </el-col>
                            <el-col :span="6">
                                <button type="button" class="btn button-standard ml-3" size="mini" @click="createProvince">
                                    <i class="el-icon-plus"></i> Add Province
                                </button>
                            </el-col>
                            </el-form-item>
                        </el-form>
                        <hr>
                        <el-col :span="6">
                            <el-select v-model="filter" @change="selectFilter" clearable placeholder="Select Filter">
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
                            v-model="locationTable"
                            row-key="locationTableId"
                            height="500px"
                            :data="locationTable"
                            @sort-change="sortChange"
                            :default-sort = "{prop: 'id', order: 'descending'}"
                            v-loading="loading"
                            element-loading-text="Loading..."
                            element-loading-spinner="el-icon-loading"
                            style="width: 100%">
                            <el-table-column 
                                prop="province_name"
                                label="Provinces"
                                :min-width="70"
                                sortable>
                            </el-table-column>
                            <el-table-column 
                                label="Action" 
                                :fixed="`right`"
                                width="100">
                                <template slot-scope="scope">
                                    <el-tooltip class="item" effect="dark" content="More info" placement="top">
                                        <router-link :to="'/view-province/'+locationTable[scope.$index].id+'/'+locationTable[scope.$index].province_name">
                                            <i class="icon el-icon-info pointer" v-if="!showDeleted"></i>
                                            <i class="icon el-icon-info pointer info-deleted" v-else></i>
                                        </router-link>
                                    </el-tooltip>

                                    <el-tooltip class="item" effect="dark" content="Edit" placement="top" v-show="!showDeleted">
                                        <router-link :to="'/update-province/'+locationTable[scope.$index].id+'/'+locationTable[scope.$index].province_name">
                                            <i class="icon el-icon-edit-outline pointer ml-2"></i>
                                        </router-link>
                                    </el-tooltip>

                                    <el-tooltip class="item" effect="dark" content="Delete" placement="top" v-if="!showDeleted">
                                        <i class="icon el-icon-delete pointer ml-2" @click="deleteProv(locationTable[scope.$index].id)"></i>
                                    </el-tooltip>
                                    <el-tooltip class="item" effect="dark" content="Restore" placement="top" v-else>
                                        <i class="icon el-icon-circle-check pointer ml-2" @click="deleteProv(locationTable[scope.$index].id)"></i>
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
                            <el-checkbox v-model="showDeleted" @change="showDeletedlocation">Show deleted provinces</el-checkbox>
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
                       province_name: '',
                    }
                ),
                editMode: false,
                locationTable: [],
                search: '',
                pageInfo: {},
                perPage: 10,
                showDeleted: false,
                alert_text: '',
                keywords: '',
                sortParameter: {
                    "column":{},
                    "prop":"province_name",
                    "order":"ascending"
                },
                loading: '',
                filter: '',
            }
        },
        methods: {
            createProvince(){
                Swal.fire({
                    text: 'Are you sure you want to register new province?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Register',
                    width: '25rem',
                })
                .then((result) => {
                    if(result.value){
                        this.form.post('/api/locations')
                        .then((res) => {
                            this.form.reset();
                            Swal.fire(
                                'Registered!',
                                'Province successfully registered.',
                                'success'
                            )
                            this.clearTable();
                            this.getProvinces();
                        })
                        .catch(error => {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops!',
                                text: 'Province registration failed.',
                            })
                            console.log(error)
                        });
                    }
                })
            },
            getProvinces(){
                this.clearTable();
                this.loading = true;
                axios.get("/api/locations", { 
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
                    if(this.$currentPagelocation > 0){
                        this.handleCurrentChange(this.$currentPagelocation);
                    }
                    else{
                        this.locationTable = data.data.data;
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
                axios.get("api/locations", { 
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
                    this.locationTable = data.data.data;
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
                    this.locationTable = data.data.data;
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
                    this.locationTable = data.data.data;
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
                    this.locationTable = data.data.data;
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
                        filter: this.filter,
                    } 
                })
                .then((data) => {
                    this.locationTable = data.data.data;
                    this.pageInfo = data.data;
                    this.loading = false;
                })
                .catch(error => {
                    this.loaded = true;
                    this.loading = false;
                });
            },
            deleteProv(province_id){
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
                        axios.delete('/api/locations/'+province_id)
                        .then(() => {
                            Swal.fire(
                                'Success!',
                                'Province has been '+this.alert_text+'d.',
                                'success'
                            )
                            this.getProvinces();
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
                this.getProvinces();
            },
            exportPDF(){
                var keywords = this.keywords;
                var isDeleted = this.showDeleted;
                var sortProp = this.sortParameter.prop;
                var sortOrder = this.sortParameter.order;
                var filter = this.filter;
                window.open('/export-pdf-locations?keywords='+keywords+'&showDeleted='+isDeleted+'&sortProp='+sortProp+'&sortOrder='+sortOrder+'&filter='+filter);
            },
            sortChange(param){
                this.sortParameter = param;
                this.getProvinces();
            },
            currentPageGlobal(){
                Vue.prototype.$currentPagelocation = this.pageInfo.current_page;
                this.currentPage = this.pageInfo.current_page;
            },
            clearTable(){
                this.employeesTable = [];
            },
            selectFilter(){
                this.clearTable();
                this.loading = true;
                axios.get("api/locations", { 
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
                    if(this.$currentPagelocation > 0){
                        this.handleCurrentChange(this.$currentPagelocation);
                    }
                    else{
                        this.locationTable = data.data.data;
                        this.pageInfo = data.data;
                        this.pageInfo.current_page = this.$currentPagelocation;
                        this.loading = false;   
                    }   
                })
                .catch(error => {
                    this.loaded = true;
                    console.log(error)
                    this.loading = false;
                });
            },
        },
        created() {
            this.getProvinces();
        },
    }
</script>
