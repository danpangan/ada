<template>
    <div class="container">
        <div class="content-header mb-3" style="border-bottom: 1px solid black;">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h4 class="m-0 text-dark" v-show="isEdit"><strong>Edit Forecast</strong></h4>
                        <h4 class="m-0 text-dark" v-show="!isEdit"><strong>View Forecast</strong></h4>
                    </div>
                    <div class="col-sm-6">
                        <button type="button" class="btn button-standard float-right" @click="$router.push('/forecast-management')">
                            <i class="el-icon-arrow-left"></i> Lists
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-12">
                <form @submit.prevent="createEmployee()">
                    <el-card class="box-card mb-3" shadow="hover">
                        <div slot="header">
                            <span>Forecast Information</span>
                        </div>
                        <el-form ref="form" :model="form" label-width="15%" :label-position="`right`">
                            <el-form-item label="Forecast Number">
                                <el-col :span="6">
                                    <el-input v-model="forecastNumber" :disabled="true"></el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Customer">
                                <el-col :span="12">
                                    <el-input v-model="clientDetails" :disabled="true"></el-input>
                                </el-col>
                            </el-form-item>
                        </el-form>
                    </el-card>

                    <div class="card">
                        <div class="card-header card-adamay-blue">
                            <h3 class="card-title">Forecast Items</h3>
                        </div>
                        <div class="card-body">
                            <div v-show="isEdit">
                                <el-form ref="form" :model="form" label-width="15%" :label-position="`right`">
                                    <el-form-item :inline="true" label="Select Item">
                                        <el-col :span="6">
                                            <el-select v-model="form.item" @change="getItem" placeholder="Item Code" class="el-col-24">
                                                <el-option
                                                v-for="item in items"
                                                :key="item.id"
                                                :label="item.order_id"
                                                :value="item.part_number">
                                                </el-option>
                                            </el-select>
                                        </el-col>
                                        <el-col :span="6">
                                            <el-select v-model="form.item" @change="getItem" placeholder="Part Number" class="el-col-24">
                                                <el-option
                                                v-for="item in items"
                                                :key="item.id"
                                                :label="item.part_number"
                                                :value="item.part_number">
                                                </el-option>
                                            </el-select>
                                        </el-col>
                                    </el-form-item>
                                    <el-form-item label="Part Name">
                                        <el-col :span="12">
                                            <el-input v-model="form.partName" :disabled="true"></el-input>
                                        </el-col>
                                    </el-form-item>
                                    <el-form-item :inline="true" label="Quantity">
                                        <el-col :span="3">
                                            <el-input v-model="form.quantity"></el-input>
                                        </el-col>
                                        <el-col :span="3">
                                            <el-select v-model="form.quantity_unit" placeholder="Unit" class="el-col-24">
                                                <el-option
                                                v-for="quantity_unit in $units_quantity"
                                                :key="quantity_unit.id"
                                                :label="quantity_unit.unit_name"
                                                :value="quantity_unit.id">
                                                </el-option>
                                            </el-select>
                                        </el-col>
                                    </el-form-item>
                                    <el-form-item :inline="true" label="Delivery Date">
                                        <el-col :span="3">
                                            <el-select v-model="form.month" placeholder="Month" class="el-col-24">
                                                <el-option
                                                v-for="month in $months"
                                                :key="month.id"
                                                :label="month.month_name"
                                                :value="month.id">
                                                </el-option>
                                            </el-select>
                                        </el-col>
                                        <el-col :span="3">
                                            <el-select v-model="form.year" placeholder="Year" class="el-col-24">
                                                <el-option
                                                v-for="year in years"
                                                :key="year"
                                                :value="year">
                                                </el-option>
                                            </el-select>
                                        </el-col>
                                    </el-form-item>
                                </el-form>
                                <el-col class="text-center">
                                    <button type="button" class="btn button-standard float-center mb-3" @click="updateForecast">
                                        <i class="el-icon-plus"></i> Add Forecast to List
                                    </button>
                                </el-col>
                            </div>

                            <el-col :span="6" :offset="18">
                                <el-input
                                v-model="search"
                                @input="queryForKeywords"
                                size="small"
                                clearable
                                placeholder="Search"/>
                            </el-col>
                            <el-table
                                v-model="forecastItemsTable"
                                row-key="forecastItemsTableId"
                                :data="forecastItemsTable"
                                @sort-change="sortChange"
                                :default-sort = "{prop: 'id', order: 'descending'}"
                                style="width: 100%">
                                <el-table-column
                                    prop="order_id"
                                    label="Order Number"
                                    sortable>
                                </el-table-column>
                                <el-table-column
                                    label="Quantity">
                                    <template slot-scope="scope">
                                        {{ forecastQuantity(forecastItemsTable[scope.$index].forecast_quantity, forecastItemsTable[scope.$index].forecast_quantity_unit) }}
                                    </template>
                                </el-table-column>
                                <el-table-column
                                    label="Delivery Date">
                                    <template slot-scope="scope">
                                        {{ deliveryDate(forecastItemsTable[scope.$index].forecast_month, forecastItemsTable[scope.$index].forecast_year) }}
                                    </template>
                                </el-table-column>
                                <el-table-column
                                    label="Action"
                                    v-if="isEdit"
                                    >
                                    <template slot-scope="scope">
                                        <el-tooltip class="item" effect="dark" content="Delete" placement="top" v-if="!showDeleted">
                                            <i class="icon el-icon-delete pointer ml-2" @click="deleteForecast(forecastItemsTable[scope.$index].id)"></i>
                                        </el-tooltip>
                                        <el-tooltip class="item" effect="dark" content="Restore" placement="top" v-else>
                                            <i class="icon el-icon-circle-check pointer ml-2" @click="deleteForecast(forecastItemsTable[scope.$index].id)"></i>
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
                                <el-checkbox v-model="showDeleted" @change="showDeletedForecastItems">Show deleted forecast items</el-checkbox>
                            </el-col>
                        </div>
                    </div>
                </form>
            </div>
            <el-col class="text-center mt-3" v-show="!isEdit">
                <el-button class="pdf" size="mini" @click="exportPDF" round><i class="fas fa-file-pdf"></i> Download</el-button>
            </el-col>
        </div>
    </div>
</template>

<script>
    export default {
        data(){
            return {
                form: new Form({
                    forecast_id: '',
                    item: '',
                    partName: '',
                    quantity: '',
                    quantity_unit: '',
                    month: '',
                    year: '',
                    order_id: '',
                    clientId: '',
                }),
                errors: {},
                isEdit: false,
                isLoading: false,

                forecastItemsTable: [],
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

                forecastNumber: '',
                clientNumber: '',
                clientName: '',
                items: [
                    { 'id': 1, 'order_id': 'AD2-SP0000001', 'part_number': 'PART-NUMBER-8352', 'part_name': 'PART-NAME-8352' },
                    { 'id': 2, 'order_id': 'AD2-SP0000002', 'part_number': 'PART-NUMBER-6342', 'part_name': 'PART-NAME-6342' },
                    { 'id': 3, 'order_id': 'AD2-SP0000003', 'part_number': 'PART-NUMBER-1284', 'part_name': 'PART-NAME-1284' },
                ],
                years: Array.from({ length: new Date().getFullYear() + 10 }, (v, i) => i).slice(new Date().getFullYear()),
            }
        },
        methods: {
            getForecast(){
                axios.get("/api/client-forecast/"+this.$route.params.client_forecast_id)
                .then((res) => {
                    this.forecastNumber = res.data.forecast_number;
                    this.clientNumber = res.data.client_number;
                    this.clientName = res.data.client_name;
                    this.form.clientId = res.data.client_id;
                    this.form.forecast_id = this.$route.params.client_forecast_id;
                })
                .catch(error => {
                    this.loaded = true;
                    console.log(error)
                });
            },
            getForecastItems(){
                axios.get("/api/client-forecast-item", { 
                    params: { 
                        take: this.perPage, 
                        showDeleted: this.showDeleted, 
                        search: this.keywords, 
                        sortProp: this.sortParameter.prop, 
                        sortOrder: this.sortParameter.order,
                        forecast_id: this.$route.params.client_forecast_id,
                    } 
                })
                .then((data) => {
                    this.forecastItemsTable = data.data.data;
                    this.pageInfo = data.data;
                    this.pageInfo.current_page = this.$currentPageForecastItems;
                })
                .catch(error => {
                    this.loaded = true;
                    console.log(error)
                });
            },
            updateForecast() {
                Swal.fire({
                    text: 'Are you sure you want to add forecast item to list?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Update',
                    width: '25rem',
                })
                .then((result) => {
                    if(result.value){
                        this.isLoading = true;
                        this.form.put('/api/client-forecast-item/'+this.$route.params.client_forecast_id)
                        .then((res) => {
                            Swal.fire(
                                'Created!',
                                'Forecast item successfully added.',
                                'success'
                            )
                            this.form.item = '';
                            this.form.partName = '';
                            this.form.quantity = '';
                            this.form.quantity_unit = '';
                            this.form.month = '';
                            this.form.year = '';
                            this.getForecastItems();    
                        })
                        .catch(error => {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops!',
                                text: 'Failed to add forecast item.',
                            })
                        });
                        this.isLoading = false;
                    }
                })
            },
            resetFields(){
                Swal.fire({
                    position: 'center',
                    icon: 'success',
                    text: 'Form reset completed.',
                    showConfirmButton: false,
                    timer: 1500
                });
                this.form.clear();
            },
            pageType(){
                if(window.location.href.indexOf("update") > -1){
                    this.isEdit = true;
                }
                else{
                    this.isEdit = false;
                }
            },
            handleSizeChange(val) {
                this.perPage = val;
                axios.get("/api/client-forecast-item", { 
                    params: { 
                        take: this.perPage, 
                        search: this.keywords,
                        sortProp: this.sortParameter.prop, 
                        sortOrder: this.sortParameter.order,
                        forecast_id: this.$route.params.client_forecast_id,
                    } 
                })
                .then((data) => {
                    this.forecastItemsTable = data.data.data;
                    this.pageInfo = data.data;
                });
                this.currentPageGlobal();
            },
            handleCurrentChange(val) {
                axios.get(this.pageInfo.path+'?page='+val, { 
                    params: { 
                        take: this.perPage, 
                        search: this.keywords,
                        sortProp: this.sortParameter.prop, 
                        sortOrder: this.sortParameter.order,
                        forecast_id: this.$route.params.client_forecast_id,
                    } 
                })
                .then((data) => {
                    this.forecastItemsTable = data.data.data;
                    this.pageInfo = data.data;
                });
                this.currentPageGlobal();
            },
            handleNextPage(val) {
                axios.get(this.pageInfo.next_page_url, { 
                    params: { 
                        take: this.perPage, 
                        search: this.keywords,
                        sortProp: this.sortParameter.prop, 
                        sortOrder: this.sortParameter.order,
                        forecast_id: this.$route.params.client_forecast_id,
                    } 
                })
                .then((data) => {
                    this.forecastItemsTable = data.data.data;
                    this.pageInfo = data.data;
                });
                this.currentPageGlobal();
            },
            handlePrevPage(val) {
                axios.get(this.pageInfo.prev_page_url, { 
                    params: { 
                        take: this.perPage, 
                        search: this.keywords,
                        sortProp: this.sortParameter.prop, 
                        sortOrder: this.sortParameter.order,
                        forecast_id: this.$route.params.client_forecast_id,
                        } 
                    })
                .then((data) => {
                    this.forecastItemsTable = data.data.data;
                    this.pageInfo = data.data;
                });
                this.currentPageGlobal();
            },
            queryForKeywords(search) {
                this.keywords = search;
                axios.get("/api/client-forecast-item", { 
                    params: { 
                        take: this.perPage, 
                        showDeleted: this.showDeleted, 
                        search: this.keywords,
                        sortProp: this.sortParameter.prop, 
                        sortOrder: this.sortParameter.order,
                        forecast_id: this.$route.params.client_forecast_id,
                    } 
                })
                .then((data) => {
                    this.forecastItemsTable = data.data.data;
                    this.pageInfo = data.data;
                })
                .catch(error => {
                    this.loaded = true;
                    if(error.response.status == 401){
                        window.location.href = '/login';
                    }
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
                        axios.delete('/api/client-forecast-item/'+forecast_id)
                        .then(() => {
                            Swal.fire(
                                'Success!',
                                'Forecast has been '+this.alert_text+'d.',
                                'success'
                            )
                            this.getForecastItems();
                        })
                        .catch(() => {
                            Swal.fire(
                                'Failed!',
                                'Failed to '+this.alert_text+' forecast.',
                                'warning'
                            )
                        })
                    }
                })
            },
            showDeletedForecastItems(){
                this.forecastItemsTable = [];
                this.getForecastItems();
            },
            sortChange(param){
                this.sortParameter = param;
                this.getForecastItems();
            },
            currentPageGlobal(){
                Vue.prototype.$currentPageForecastItems = this.pageInfo.current_page;
                this.currentPage = this.pageInfo.current_page;
            },
            exportPDF(){
                var keywords = this.keywords;
                var isDeleted = this.showDeleted;
                var sortProp = this.sortParameter.prop;
                var sortOrder = this.sortParameter.order;
                var clientId =  this.form.clientId;
                var client_forecast_id = this.$route.params.client_forecast_id;
                window.open('/export-pdf-client-forecast-items?keywords='+keywords+'&showDeleted='+isDeleted+'&sortProp='+sortProp+'&sortOrder='+sortOrder+'&clientId='+clientId+'&clientForecastId='+client_forecast_id);
            },
            toEdit(){
                this.isEdit = true;
            },
            deliveryDate(month, year){
                let format_month = '';
                let delivery_date = '';

                if(month < 10) format_month = '0' + month;
                else format_month = month;

                delivery_date = format_month + '/' + year;

                return delivery_date;
            },
            forecastQuantity(quantity, unit){
                return quantity + ' ' + this.$units_quantity.find(unitElement => unitElement.id == unit).unit_name;
            },
            getItem(){
                this.form.partName = this.items.find(part => part.part_number == this.form.item).part_name;
                this.form.order_id = this.items.find(part => part.part_number == this.form.item).order_id;
            },
        },
        created() {
            this.pageType();

            this.getForecast();
            this.getForecastItems();
        },
        computed: {
            readonly(){
                if(!this.isEdit) return true;
            },
            clientDetails(){
                return this.clientName + ' (' + this.clientNumber + ')';
            },
        },
        watch:{
            $route (to, from){
                this.pageType();
            }
        }
    }
</script>
