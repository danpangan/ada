<template>
    <div class="container">
        <div class="content-header mb-3" style="border-bottom: 1px solid black;">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h4 class="m-0 text-dark"><strong>Add Forecast</strong></h4>
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
                <form @submit.prevent="createForecast()">
                    <el-card class="box-card mt-3" shadow="hover">
                        <div slot="header">
                            <span>Forecast Information</span>
                        </div>
                        <el-form ref="form" :model="form" label-width="15%" :label-position="`right`">
                            <el-form-item :inline="true" label="Select Customer">
                                <el-col :span="6">
                                    <el-select v-model="form.clientId" placeholder="Customer Code" class="block" :disabled="isClientDisabled">
                                        <el-option
                                        v-for="customer_code in clientNames"
                                        :key="customer_code.id"
                                        :label="customer_code.client_number"
                                        :value="customer_code.id">
                                        </el-option>
                                    </el-select>
                                </el-col>
                                <el-col :span="12">
                                    <el-select v-model="form.clientId" placeholder="Customer Name" class="block" :disabled="isClientDisabled">
                                        <el-option
                                        v-for="client_name in clientNames"
                                        :key="client_name.id"
                                        :label="client_name.client_name"
                                        :value="client_name.id">
                                        </el-option>
                                    </el-select>
                                </el-col>
                            </el-form-item>
                            <el-form-item :inline="true" label="Select Item">
                                <el-col :span="6">
                                    <el-select v-model="item" @change="getItem" placeholder="Item Code" class="el-col-24">
                                        <el-option
                                        v-for="item in items"
                                        :key="item.id"
                                        :label="item.order_id"
                                        :value="item.part_number">
                                        </el-option>
                                    </el-select>
                                </el-col>
                                <el-col :span="6">
                                    <el-select v-model="item" @change="getItem" placeholder="Part Number" class="el-col-24">
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
                                    <el-input v-model="partName" :disabled="true"></el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item :inline="true" label="Quantity">
                                <el-col :span="3">
                                    <el-input v-model="quantity"></el-input>
                                </el-col>
                                <el-col :span="3">
                                    <el-select v-model="quantityUnit" placeholder="Unit" class="el-col-24">
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
                                    <el-select v-model="month" placeholder="Month" class="el-col-24">
                                        <el-option
                                        v-for="month in $months"
                                        :key="month.id"
                                        :label="month.month_name"
                                        :value="month.id">
                                        </el-option>
                                    </el-select>
                                </el-col>
                                <el-col :span="3">
                                    <el-select v-model="year" placeholder="Year" class="el-col-24">
                                        <el-option
                                        v-for="year in years"
                                        :key="year"
                                        :value="year">
                                        </el-option>
                                    </el-select>
                                </el-col>
                            </el-form-item>
                            <el-col class="text-center">
                                <button type="button" class="btn button-standard float-center mb-3" @click="addForecastToList">
                                    <i class="el-icon-plus"></i> Add Forecast to List
                                </button>
                            </el-col>

                            <el-table
                                ref="multipleTable"
                                :data="form.forecastList"
                                style="width: 100%"
                                v-show="showForecastItems"
                                @selection-change="handleSelectionChange">
                                <el-table-column
                                type="selection"
                                width="50">
                                </el-table-column>
                                <el-table-column
                                property="part_number"
                                label="Part Number"
                                width="280">
                                </el-table-column>
                                <el-table-column
                                property="date"
                                label="Date">
                                </el-table-column>
                                <el-table-column
                                    label="Action"
                                    >
                                    <template slot-scope="scope">
                                        <el-tooltip class="item" effect="dark" content="More info" placement="top">
                                            <i class="icon el-icon-info pointer" @click="viewForecastItem(form.forecastList[scope.$index].id)"></i>
                                        </el-tooltip>
                                    </template>
                                </el-table-column>
                            </el-table>
                            <div style="margin-top: 20px" v-show="showForecastItems">
                                <el-button @click="deleteForecast()">Delete</el-button>
                            </div>
                        </el-form>
                    </el-card>

                    <div class="outside-card-button">
                        <el-button type="primary" v-on:click="createForecast" v-if="!isLoading"><i class="el-icon-plus"></i> Create</el-button>
                        <el-button type="primary" v-on:click="createForecast" :loading="isLoading" v-else> Loading</el-button>
                        <el-button type="warning" v-on:click="resetFields"><i class="el-icon-refresh-left"></i> Reset</el-button>
                        <el-button type="danger" @click="$router.back(-1)"><i class="el-icon-close"></i> Cancel</el-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        data(){
            return {
                form: new Form({
                    forecastList: [],
                    clientId: '',
                }),
                errors: {},
                departments: [],
                isLoading: false,
                clientNames: [],
                item: '',
                items: [
                    { 'id': 1, 'order_id': 'AD2-SP0000001', 'part_number': 'PART-NUMBER-8352', 'part_name': 'PART-NAME-8352' },
                    { 'id': 2, 'order_id': 'AD2-SP0000002', 'part_number': 'PART-NUMBER-6342', 'part_name': 'PART-NAME-6342' },
                    { 'id': 3, 'order_id': 'AD2-SP0000003', 'part_number': 'PART-NUMBER-1284', 'part_name': 'PART-NAME-1284' },
                ],
                partName: '',
                quantity: '',
                quantityUnit: '',
                month: '',
                years: Array.from({ length: new Date().getFullYear() + 10 }, (v, i) => i).slice(new Date().getFullYear()),
                year: '',
                multipleSelection: [],
                orderId: '',
            }
        },
        methods: {
            createForecast(){
                Swal.fire({
                    text: 'Are you sure you want to create new forecast?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Create',
                    width: '25rem',
                })
                .then((result) => {
                    if(result.value){
                        this.form.post('api/client-forecast')
                        .then((res) => {
                            Swal.fire(
                                'Created!',
                                'Forecast successfully created.',
                                'success'
                            )
                            this.form.clientId = '';
                            this.form.forecastList = [];
                        })
                        .catch(error => {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops!',
                                text: 'Failed to create forecast.',
                            })
                        });
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
                })
                this.form.reset();
                this.errors = {};
                this.loadPermissions();
            },
            selectCustomerCode(){

            },
            getClients(){
                axios.get('api/get-clients')
                .then((res) => {
                    this.clientNames = res.data;
                })
                .catch((error) => {

                })
            },
            selectitem(){

            },
            getItem(){
                this.partName = this.items.find(part => part.part_number == this.item).part_name;
                this.orderId = this.items.find(part => part.part_number == this.item).order_id;
            },
            deleteForecast() {
                for(let i = 0; i < this.form.forecastList.length; i++){
                    for(let j = 0; j < this.multipleSelection.length; j++){
                        if(this.form.forecastList[i].id == this.multipleSelection[j].id) this.form.forecastList.splice(i, 1);
                    }
                }
            },
            handleSelectionChange(val) {
                this.multipleSelection = val;
            },
            addForecastToList(){
                if(this.form.clientId && this.item && this.quantity && this.quantityUnit && this.month && this.year){
                    let id = 0;
                    if(this.form.forecastList.length == 0) id = 1;
                    else id = this.form.forecastList[this.form.forecastList.length - 1].id + 1;

                    let month = '';
                    if(this.month < 10) month = '0'+this.month;
                    else month = this.month;

                    this.form.forecastList.push({
                        'id': id,
                        'client_id': this.form.clientId,
                        'order_id': this.orderId,
                        'part_number': this.item,
                        'quantity': this.quantity,
                        'quantity_unit': this.quantityUnit,
                        'month': this.month,
                        'year': this.year,
                        'date': month+'/'+this.year,
                    });

                    this.item = '';
                    this.partName = '';
                    this.quantity = '';
                    this.quantityUnit = '';
                    this.month = '';
                    this.year = '';
                    this.orderId = '';
                }
                else{
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops!',
                        text: 'Invalid forecast item.',
                    })
                }
            },
            viewForecastItem(id){
                console.log(id)
            }
        },
        created() {
            this.getClients();
        },
        computed: {
            showForecastItems(){
                if(this.form.forecastList.length > 0) return true;
                else return false;
            },
            isClientDisabled(){
                if(this.form.forecastList.length > 0) return true;
                else return false;
            }
        }
    }
</script>
