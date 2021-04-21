<template>
    <div class="container">
        <div class="content-header mb-3" style="border-bottom: 1px solid black;">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h4 class="m-0 text-dark"><strong>Forex Management</strong></h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-12">
                <el-card class="box-card mb-3" shadow="hover">
                    <div slot="header">
                        <span>Exchange Rates</span>
                    </div>
                    <el-form label-width="15%">
                        <el-form-item :inline="true" label="PHP to USD">
                            <el-col :span="4">
                                <el-input v-model="form.PHPToUSD" :disabled="isPHPToUSDReadonly"></el-input>
                            </el-col>
                            <i class="icon el-icon-edit-outline pointer ml-3" @click="isForUpdate(1)" v-if="isPHPToUSDReadonly"></i>
                            <div v-else>
                                <i class="icon el-icon-circle-check pointer ml-3" @click="updateCurrency(1)"></i>
                                <i class="icon el-icon-circle-close pointer ml-3" @click="close(1)"></i>
                            </div>
                        </el-form-item>
                        <el-form-item :inline="true" label="USD to PHP">
                            <el-col :span="4">
                                <el-input v-model="form.USDToPHP" :disabled="isUSDToPHPReadonly"></el-input>
                            </el-col>
                            <i class="icon el-icon-edit-outline pointer ml-3" @click="isForUpdate(2)" v-if="isUSDToPHPReadonly"></i>
                            <div v-else>
                                <i class="icon el-icon-circle-check pointer ml-3" @click="updateCurrency(2)"></i>
                                <i class="icon el-icon-circle-close pointer ml-3" @click="close(2)"></i>
                            </div>
                        </el-form-item>
                        <el-form-item :inline="true" label="JPY to USD">
                            <el-col :span="4">
                                <el-input v-model="form.JPYToUSD" :disabled="isJPYToUSDReadonly"></el-input>
                            </el-col>
                            <i class="icon el-icon-edit-outline pointer ml-3" @click="isForUpdate(3)" v-if="isJPYToUSDReadonly"></i>
                            <div v-else>
                                <i class="icon el-icon-circle-check pointer ml-3" @click="updateCurrency(3)"></i>
                                <i class="icon el-icon-circle-close pointer ml-3" @click="close(3)"></i>
                            </div>
                        </el-form-item>
                        <el-form-item :inline="true" label="JPY to PHP">
                            <el-col :span="4">
                                <el-input v-model="form.JPYToPHP" :disabled="isJPYToPHPReadonly"></el-input>
                            </el-col>
                            <i class="icon el-icon-edit-outline pointer ml-3" @click="isForUpdate(4)" v-if="isJPYToPHPReadonly"></i>
                            <div v-else>
                                <i class="icon el-icon-circle-check pointer ml-3" @click="updateCurrency(4)"></i>
                                <i class="icon el-icon-circle-close pointer ml-3" @click="close(4)"></i>
                            </div>
                        </el-form-item>
                    </el-form>
                </el-card>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-12">
                <div class="card">
                    <div class="card-header card-adamay-blue">
                        <h3 class="card-title">Update History</h3>
                    </div>
                    <div class="card-body">
                        <el-col :span="6">
                            <el-select v-model="filter" @change="selectFilter" clearable placeholder="Select Filter">
                                <el-option
                                v-for="conversion in $currency_conversions"
                                :key="conversion.id"
                                :label="conversion.label"
                                :value="conversion.id">
                                </el-option>
                            </el-select>
                        </el-col>
                        <el-table
                            v-model="historyTable"
                            row-key="historyTableId"
                            :data="historyTable"
                            :default-sort = "{prop: 'id', order: 'descending'}"
                            v-loading="loading"
                            element-loading-text="Loading..."
                            element-loading-spinner="el-icon-loading"
                            style="width: 100%">
                            <el-table-column
                                label="Exchange Currency">
                                <template slot-scope="scope">
                                    {{ exchangeCurrency(historyTable[scope.$index].conversion_id) }}
                                </template>
                            </el-table-column>
                            <el-table-column
                                prop="rate"
                                align="right"
                                label="Conversion Rate">
                                <template slot-scope="scope">
                                    {{ conversionRate(historyTable[scope.$index].conversion_id, historyTable[scope.$index].rate) }}
                                </template>
                            </el-table-column>
                            <el-table-column
                                prop="employee_id"
                                label="Employee Name">
                                <template slot-scope="scope">
                                    {{ employeeName(historyTable[scope.$index].employee_id) }}
                                </template>
                            </el-table-column>
                            <el-table-column
                                prop="created_at"
                                label="Update Date">
                                <template slot-scope="scope">
                                    {{ dateFormat(historyTable[scope.$index].created_at) }}
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
    import moment from 'moment';
    export default {
        data(){
            return {
                form: new Form({
                    PHPToUSD: '',
                    USDToPHP: '',
                    JPYToUSD: '',
                    JPYToPHP: '',
                }),
                isPHPToUSDReadonly: true,
                isUSDToPHPReadonly: true,
                isJPYToUSDReadonly: true,
                isJPYToPHPReadonly: true,
                historyTable: [],
                pageInfo: {},
                perPage: 10,
                alert_text: '',
                loading: '',
                filter: '',
            }
        },
        methods: {
            updateCurrency(id){
                Swal.fire({
                    text: 'Are you sure you want to update currency?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Update',
                    width: '25rem',
                })
                .then((result) => {
                    if(result.value){
                        this.form.post('api/exchange-rate?id='+id)
                        .then((res) => {
                            this.getExchangeRates();
                            for (let i = 1; i <= 4; i++) {
                                this.close(i);
                            }
                            Swal.fire(
                                'Updated!',
                                'Exchange rate successfully updated!.',
                                'success'
                            );
                        })
                        .catch(error => {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops!',
                                text: 'Exchange rate update failed.',
                            })
                        });
                    }
                })
                .catch((error) => {
                    console.log(error)
                })
            },
            getExchangeRates(){
                axios.get('api/exchange-rate', {
                    params: { 
                        take: this.perPage,
                        conversion_id: this.filter,
                    } 
                })
                .then((res) => {
                    this.form.PHPToUSD = res.data.exchange_rates.PHPToUSD.rate;
                    this.form.USDToPHP = res.data.exchange_rates.USDToPHP.rate;
                    this.form.JPYToUSD = res.data.exchange_rates.JPYToUSD.rate;
                    this.form.JPYToPHP = res.data.exchange_rates.JPYToPHP.rate;

                    this.pageInfo = res.data.history;
                    if(this.$currentPageEmployees > 0){
                        this.handleCurrentChange(this.$currentPageEmployees);
                    }
                    else{
                        this.historyTable = res.data.history.data;
                        this.pageInfo.current_page = this.$currentPageEmployees;
                        this.loading = false;   
                    }

                    this.employees = res.data.employees;
                })
                .catch((error) => {
                    console.log(error)
                })
            },
            isForUpdate(id){
                if(id == 1) this.isPHPToUSDReadonly = false;
                if(id == 2) this.isUSDToPHPReadonly = false;
                if(id == 3) this.isJPYToUSDReadonly = false;
                if(id == 4) this.isJPYToPHPReadonly = false;
            },
            close(id){
                if(id == 1) this.isPHPToUSDReadonly = true;
                if(id == 2) this.isUSDToPHPReadonly = true;
                if(id == 3) this.isJPYToUSDReadonly = true;
                if(id == 4) this.isJPYToPHPReadonly = true;
            },
            handleSizeChange(val) {
                this.clearTable();
                this.loading = true;
                this.perPage = val;
                axios.get("api/exchange-rate", { 
                    params: { 
                        take: this.perPage,
                        conversion_id: this.filter,
                    } 
                })
                .then((data) => {
                    this.historyTable = data.data.history.data;
                    this.pageInfo = data.data.history;
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
                        conversion_id: this.filter,
                    } 
                })
                .then((data) => {
                    this.historyTable = data.data.history.data;
                    this.pageInfo = data.data.history;
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
                        conversion_id: this.filter,
                    } 
                })
                .then((data) => {
                    this.historyTable = data.data.history.data;
                    this.pageInfo = data.data.history;
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
            exportPDF(){
                var conversion_id = this.filter;
                window.open('/export-pdf-exchange-rate-logs?conversion_id='+conversion_id);
            },
            currentPageGlobal(){
                Vue.prototype.$currentPageEmployees = this.pageInfo.current_page;
                this.currentPage = this.pageInfo.current_page;
            },
            clearTable(){
                this.historyTable = [];
            },
            exchangeCurrency(id){
                for (let i = 0; i < this.$currency_conversions.length; i++) {
                    if(this.$currency_conversions[i].id == id) return this.$currency_conversions[i].label;
                }
            },
            dateFormat(date){
                return moment(date).format('MM/DD/YYYY HH:mm');
            },
            employeeName(employee_id){
                var name = this.employees.find(employee => employee.id == employee_id);
                return name.first_name+' '+name.last_name;
            },
            conversionRate(id, rate){
                for (let i = 0; i < this.$currency_conversions.length; i++) {
                    if(this.$currency_conversions[i].id == id) return rate + ' ' +this.$currency_conversions[i].convert_to;
                }
            },
            selectFilter(conversion_id){
                axios.get('api/exchange-rate', {
                    params: { 
                        take: this.perPage,
                        conversion_id: this.filter,
                    } 
                })
                .then((res) => {
                    this.form.PHPToUSD = res.data.exchange_rates.PHPToUSD.rate;
                    this.form.USDToPHP = res.data.exchange_rates.USDToPHP.rate;
                    this.form.JPYToUSD = res.data.exchange_rates.JPYToUSD.rate;
                    this.form.JPYToPHP = res.data.exchange_rates.JPYToPHP.rate;

                    this.pageInfo = res.data.history;
                    if(this.$currentPageEmployees > 0){
                        this.handleCurrentChange(this.$currentPageEmployees);
                    }
                    else{
                        this.historyTable = res.data.history.data;
                        this.pageInfo.current_page = this.$currentPageEmployees;
                        this.loading = false;   
                    }

                    this.employees = res.data.employees;
                })
                .catch((error) => {
                    console.log(error)
                })
            }
        },
        computed: {
            PHPToUSDReadonly(){
                return this.isPHPToUSDReadonly = true;
            },
            USDToPHPReadonly(){
                return this.isUSDToPHPReadonly = true;
            },
            JPYToUSDReadonly(){
                return this.isJPYToUSDReadonly = true;
            },
            JPYToPHPReadonly(){
                return this.isJPYToPHPReadonly = true;
            },
        },
        created(){
            this.getExchangeRates();
        },
    }
</script>