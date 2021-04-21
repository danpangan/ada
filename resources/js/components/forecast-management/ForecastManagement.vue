<template>
    <div class="container">
        <div class="content-header mb-3" style="border-bottom: 1px solid black;">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h4 class="m-0 text-dark"><strong>Forecast Management</strong></h4>
                    </div>
                    <div class="col-sm-6">
                        <router-link :to="'/add-client-forecast'">
                            <button type="submit" class="btn btn-add btn-sm float-right">
                                <i class="fas fa-plus mr-1"></i> Add New Forecast
                            </button>
                        </router-link>
                    </div>
                </div>
            </div>
        </div>

        <el-card class="box-card mb-3" shadow="hover">
            <div slot="header">
                <span>Forecasts</span>
            </div>
            <el-form label-width="15%">
                <el-form-item :inline="true" label="Select Customer">
                    <el-col :span="6">
                        <el-select v-model="clientId" @change="selectclientCode" clearable placeholder="Customer Code" class="block">
                            <el-option
                            v-for="customer_code in clientNames"
                            :key="customer_code.id"
                            :label="customer_code.client_number"
                            :value="customer_code.id">
                            </el-option>
                        </el-select>
                    </el-col>
                    <el-col :span="12">
                        <el-select v-model="clientId" @change="selectClientName" clearable placeholder="Customer Name" class="block">
                            <el-option
                            v-for="client_name in clientNames"
                            :key="client_name.id"
                            :label="client_name.client_name"
                            :value="client_name.id">
                            </el-option>
                        </el-select>
                    </el-col>
                </el-form-item>
            </el-form>

            <div v-show="clientId">
                <el-col :span="6" :offset="18">
                    <el-input
                    v-model="search"
                    @input="queryForKeywords"
                    size="small"
                    clearable
                    placeholder="Search"/>
                </el-col>
                <el-table
                    v-model="forecastsTable"
                    row-key="forecastsTableId"
                    height="500px"
                    :data="forecastsTable"
                    @sort-change="sortChange"
                    :default-sort = "{prop: 'id', order: 'descending'}"
                    v-loading="loading"
                    element-loading-text="Loading..."
                    element-loading-spinner="el-icon-loading"
                    style="width: 100%">
                    <el-table-column
                        prop="forecast_number"
                        label="Forecast Number"
                        :min-width="80">
                    </el-table-column>
                    <el-table-column
                        label="Action"
                        :min-width="20"
                        >
                        <template slot-scope="scope">
                            <el-tooltip class="item" effect="dark" content="More info" placement="top">
                                <router-link :to="'/view-client-forecast/'+forecastsTable[scope.$index].id">
                                    <i class="icon el-icon-info pointer" v-if="!showDeleted"></i>
                                    <i class="icon el-icon-info pointer info-deleted" v-else></i>
                                </router-link>
                            </el-tooltip>

                            <el-tooltip class="item" effect="dark" content="Edit" placement="top" v-show="!showDeleted">
                                <router-link :to="'/update-client-forecast/'+forecastsTable[scope.$index].id">
                                    <i class="icon el-icon-edit-outline pointer ml-2"></i>
                                </router-link>
                            </el-tooltip>

                            <el-tooltip class="item" effect="dark" content="Delete" placement="top" v-if="!showDeleted">
                                <i class="icon el-icon-delete pointer ml-2" @click="deleteForecast(forecastsTable[scope.$index].id)"></i>
                            </el-tooltip>
                            <el-tooltip class="item" effect="dark" content="Restore" placement="top" v-else>
                                <i class="icon el-icon-circle-check pointer ml-2" @click="deleteForecast(forecastsTable[scope.$index].id)"></i>
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
                    <el-checkbox v-model="showDeleted" @change="showDeletedForecasts">Show deleted forecasts</el-checkbox>
                </el-col>

                <el-col class="text-center mb-3">
                    <el-button class="pdf" size="mini" @click="exportPDF" round><i class="fas fa-file-pdf"></i> Download</el-button>
                </el-col>
            </div>
        </el-card>
    </div>
</template>

<script>
    export default {
        data(){
            return {
                editMode: false,
                forecastsTable: [],
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

                filter: '',
                clientNames: [],
                clientId: '',
            }
        },
        methods: {
            getClient(){
                this.clearTable();
                this.loading = true;
                axios.get("api/client-forecast", { 
                    params: { 
                        take: this.perPage, 
                        showDeleted: this.showDeleted, 
                        search: this.keywords, 
                        sortProp: this.sortParameter.prop, 
                        sortOrder: this.sortParameter.order,
                        client_id: this.clientId,
                    } 
                })
                .then((data) => {
                    this.pageInfo = data.data;
                    Vue.prototype.clientGlobal = this.clientId;
                    console.log(this.clientGlobal)

                    if(this.$currentPageForecasts > 0){
                        this.handleCurrentChange(this.$currentPageForecasts);
                    }
                    else{
                        this.forecastsTable = data.data.data;
                        this.pageInfo = data.data;
                        this.pageInfo.current_page = this.$currentPageForecasts;
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
                axios.get("api/client-forecast", { 
                    params: { 
                        take: this.perPage, 
                        showDeleted: this.showDeleted, 
                        search: this.keywords,
                        sortProp: this.sortParameter.prop, 
                        sortOrder: this.sortParameter.order,
                        client_id: this.clientId,
                    } 
                })
                .then((data) => {
                    this.forecastsTable = data.data.data;
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
                        client_id: this.clientId,
                    } 
                })
                .then((data) => {
                    this.forecastsTable = data.data.data;
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
                        client_id: this.clientId,
                    } 
                })
                .then((data) => {
                    this.forecastsTable = data.data.data;
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
                        client_id: this.clientId,
                    } 
                })
                .then((data) => {
                    this.forecastsTable = data.data.data;
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
                axios.get("api/client-forecast", { 
                    params: { 
                        take: this.perPage, 
                        showDeleted: this.showDeleted, 
                        search: this.keywords,
                        sortProp: this.sortParameter.prop, 
                        sortOrder: this.sortParameter.order,
                        client_id: this.clientId,
                    } 
                })
                .then((data) => {
                    this.forecastsTable = data.data.data;
                    this.pageInfo = data.data;
                    this.loading = false;
                })
                .catch(error => {
                    this.loaded = true;
                    this.loading = false;
                });
            },
            deleteForecast(forecast_id){
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
                        axios.delete('api/client-forecast/'+forecast_id)
                        .then((res) => {
                            Swal.fire(
                                'Success!',
                                'Forecast has been '+this.alert_text+'d.',
                                'success'
                            )
                            this.getClient();
                            this.loading = false;
                        })
                        .catch((error) => {
                            Swal.fire(
                                'Failed!',
                                'Failed to '+this.alert_text+' forecast.',
                                'warning'
                            )
                            this.loading = false;
                            console.log(error)
                        })
                    }
                })
            },
            showDeletedForecasts(){
                this.clearTable();
                this.getClient();
            },
            exportPDF(){
                var keywords = this.keywords;
                var isDeleted = this.showDeleted;
                var sortProp = this.sortParameter.prop;
                var sortOrder = this.sortParameter.order;
                var clientId =  this.clientId;
                window.open('/export-pdf-client-forecasts?keywords='+keywords+'&showDeleted='+isDeleted+'&sortProp='+sortProp+'&sortOrder='+sortOrder+'&clientId='+clientId);
            },
            sortChange(param){
                this.sortParameter = param;
                this.getForecasts();
            },
            currentPageGlobal(){
                Vue.prototype.$currentPageForecasts = this.pageInfo.current_page;
                this.currentPage = this.pageInfo.current_page;
            },
            clearTable(){
                this.forecastsTable = [];
            },
            selectclientCode(){
                this.getClient();
            },
            selectClientName(){
                this.getClient();
            },
            getClients(){
                axios.get('api/get-clients')
                .then((res) => {
                    this.clientNames = res.data;
                })
                .catch((error) => {

                })
            },
            loadClientGlobal(){
                // if(!this.clientId) Vue.prototype.$clientGlobal = this.clientId;
                // else this.clientId = Vue.prototype.$clientGlobal;

                if(!this.clientId){
                    this.clientId = this.clientGlobal;
                    this.getClient();
                }
            },
        },
        created() {
            this.getClients();
            this.loadClientGlobal();
        },
    }
</script>
