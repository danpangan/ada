<template>
    <div class="container">
        <div class="content-header mb-3" style="border-bottom: 1px solid black;">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h4 class="m-0 text-dark"><strong>Add Purchase Order</strong></h4>
                    </div>
                    <div class="col-sm-6">
                        <button type="button" class="btn button-standard float-right" @click="$router.push('/purchase-orders')">
                            <i class="el-icon-arrow-left"></i> Lists
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-12">
                <form @submit.prevent="createOrder()">
                    <el-card class="box-card" shadow="hover">
                        <div slot="header">
                            <span>Purchase Order</span>
                        </div>
                        <el-form ref="form" :model="form" label-width="15%" :label-position="`right`">
                            <el-form-item label="PO Number">
                                <el-col :span="8">
                                    <el-input v-model="form.po_order"></el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Customer Name">
                                <el-col :span="5">
                                    <el-select v-model="form.client_id" placeholder="Customer Number" class="block" @change="selectCustomer" :remote="true">
                                        <el-option
                                        v-for="client in clients"
                                        :key="client.id"
                                        :label="client.client_number"
                                        :value="client.id">
                                        </el-option>
                                    </el-select>
                                </el-col>
                                <el-col :span="12">
                                    <el-select v-model="form.client_id" placeholder="Customer Name" class="block" @change="selectCustomer" :remote="true">
                                        <el-option
                                        v-for="client in clients"
                                        :key="client.id"
                                        :label="clientLabel(client.client_name, client.client_short_name)"
                                        :value="client.id">                                       
                                        <template>
                                            <el-col :span="6">
                                                {{ client.client_short_name }}
                                            </el-col>
                                            <el-col :span="18">
                                                {{ client.client_name }}
                                            </el-col>
                                        </template>
                                        </el-option>
                                    </el-select>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="PO Date">
                                <el-date-picker
                                v-model="form.po_date"
                                type="datetime"
                                value-format="yyyy-MM-dd"
                                placeholder="Select date">
                                </el-date-picker>
                            </el-form-item>
                            <el-form-item label="Receive Date">
                                <el-date-picker
                                v-model="form.receive_date"
                                type="datetime"
                                 value-format="yyyy-MM-dd"
                                placeholder="Select date">
                                </el-date-picker>
                            </el-form-item>
                                <!-- <el-form-item label="Delivery Date">
                                    <el-date-picker
                                    v-model="form.date"
                                    type="datetime"
                                    placeholder="Select date">
                                    </el-date-picker>
                                </el-form-item> -->
                        </el-form>
                    </el-card>

                    <el-card class="box-card mt-3" shadow="hover">
                        <div slot="header">
                            <span>Quotations</span>
                        </div>
                        <el-form ref="form" :model="form" label-width="15%" :label-position="`right`">
                            <el-form-item label="Part Number">
                                <el-col :span="12">
                                    <el-select v-model="form.part_no" placeholder="Part Number" class="block" @change="selectOrder" :remote="true">
                                        <el-option
                                        v-for="order in orders"
                                        :key="order.id"
                                        :label="order.order_part_number"
                                        :value="order.id">
                                        </el-option>
                                    </el-select>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Product Name">
                                <el-col :span="12">
                                    <el-input v-model="form.product_des" :disabled="true"></el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Delivery Date">
                                <el-date-picker
                                value-format="yyyy-MM-dd"
                                v-model="form.delivery_date"
                                type="datetime"
                                placeholder="Select date">
                                </el-date-picker>
                            </el-form-item>
                        </el-form>
                        <h5>Control Number List</h5>
                        <el-table
                            :data="form.csTable"
                            ref="multipleTable"
                            style="width: 100%"
                            >
                            <el-table-column
                            v-if="isEdit" 
                            width="55">
                                <template slot-scope="scope">
                                    <el-checkbox v-model="form.csTable[scope.$index].is_selected" >
                                    </el-checkbox>
                                </template>
                            </el-table-column>
                            <el-table-column
                                v-if="this.form.part_no !=  undefined"
                                prop="cs_control_no"
                                label="Control Number">
                            </el-table-column>
                            <el-table-column
                                property="cs_qty"
                                label="Quantity">
                            </el-table-column>
                            <el-table-column
                                property="cs_unit_amount"
                                label="Unit Price   "
                                show-overflow-tooltip>
                            </el-table-column>
                            <el-table-column
                                property="cs_unit_amount_php"
                                :label="amountLabel()"
                                show-overflow-tooltip>
                                <template slot-scope="scope">
                                    <el-col :span="12">
                                        {{ form.csTable[scope.$index].cs_unit_amount_currency }}
                                    </el-col>
                                    <el-col :span="12">
                                        {{ form.csTable[scope.$index].cs_unit_amount_php }}
                                    </el-col>
                                </template>
                            </el-table-column>
                            <el-table-column
                                v-if="isEdit && showDeleted"
                                label="Action"
                                >
                            <template slot-scope="scope">
                            <el-tooltip class="item" effect="dark" content="Restore" placement="top">
                                    <i class="icon el-icon-circle-check pointer ml-2" @click="deleteQuote(form.csTable[scope.$index].id)"></i>
                            </el-tooltip>
                            </template>
                            </el-table-column>
                        </el-table>
                       
                        <hr>
                        <el-row>
                            <el-col :span="4">
                                <el-form :label-position="`right`">
                                    <el-form-item label="Cost">
                                    </el-form-item>
                                </el-form>
                            </el-col>
                            <el-col :span="5">
                                <el-form :label-position="`right`">
                                    <el-form-item label="Quantity">
                                        <el-col :span="12">
                                            <el-input v-model="form.qty" @input="totalCS"></el-input>
                                        </el-col>
                                    </el-form-item>
                                </el-form>
                            </el-col>
                            <el-col :span="5">
                                <el-form :label-position="`right`">
                                    <el-form-item label="Unit">
                                        <el-col :span="12">
                                            <el-select v-model="form.unit" placeholder="Unit">
                                                <el-option
                                                v-for="unit in $units_quantity"
                                                :key="unit.id"
                                                :label="unit.unit_name"
                                                :value="unit.id">
                                                 </el-option>
                                            </el-select>
                                        </el-col>
                                    </el-form-item>
                                </el-form>
                            </el-col>
                            <el-col :span="5">
                                <el-form :label-position="`right`">
                                    <el-form-item label="Unit Price">
                                        <el-col :span="12">
                                            <el-input v-model="form.unit_price" @input="totalCS"></el-input>
                                        </el-col>
                                    </el-form-item>
                                </el-form>
                            </el-col>
                            <el-col :span="5">
                                <el-form :label-position="`right`">
                                    <el-form-item label="Total">
                                        <el-col :span="12">
                                            <el-input v-model="form.total" disabled></el-input>
                                        </el-col>
                                    </el-form-item>
                                </el-form>
                            </el-col>
                        </el-row>
                        <el-row>
                            <el-col :span="24" class="text-center mb-3">
                                <button type="button" class="btn button-standard ml-3" size="mini" @click="pickQuotation">
                                    <i class="el-icon-plus"></i> Save quotation to list
                                </button>
                            </el-col>
                        </el-row>
                        <hr>
                        <h5>Quotation List</h5>
                        <el-table
                            :data="form.quotations"
                            ref="multipleTable"
                            style="width: 100%"
                            >
                            <el-table-column
                                v-if="this.form.part_no !=  undefined"
                                prop="cs_control_no"
                                label="Control Number">
                            </el-table-column>
                            <el-table-column
                                property="cs_qty"
                                label="Quantity">
                            </el-table-column>
                            <el-table-column
                                label="Unit Price"
                                show-overflow-tooltip>
                                <template slot-scope="scope">
                                    <el-col :span="24">
                                        {{ form.quotations[scope.$index].cs_unit_amount+'/' }}
                                        {{ unitLabel(form.quotations[scope.$index].cs_unit_qty) }}
                                    </el-col>
                                </template>
                            </el-table-column>
                            <el-table-column
                                property="cs_unit_amount_php"
                                :label="amountLabel()"
                                show-overflow-tooltip>
                                <template slot-scope="scope">
                                    <el-col :span="24">
                                        {{ form.quotations[scope.$index].cs_unit_amount_currency+' ' }}
                                        {{ form.quotations[scope.$index].cs_unit_amount_php }}
                                    </el-col>
                                </template>
                            </el-table-column>
                            <el-table-column
                                label="Action"
                                >
                            <template slot-scope="scope">
                            <el-tooltip class="item" effect="dark" content="Remove" placement="top">
                                    <i class="icon el-icon-delete pointer ml-2" @click="removeQuotation(form.quotations[scope.$index].id)"></i>
                            </el-tooltip>
                            </template>
                            </el-table-column>
                        </el-table>
                        <el-row class="mt-3">
                            <el-col :span="12">
                                <span>Total Amount (Quotation)</span>
                            </el-col>
                            <el-col :span="12" class="text-right">
                                <span>{{ this.form.quotationTotal }}</span>
                            </el-col>
                        </el-row>
                    </el-card>

                    <el-card class="box-card mt-3" shadow="hover">
                        <div slot="header">
                            <span>Additional Information</span>
                        </div>
                        <el-form ref="form" :model="form" label-width="15%" :label-position="`right`">
                            <el-form-item label="Remarks">
                                <el-col :span="12">
                                    <el-input 
                                    v-model="form.remarks" 
                                    type="textarea" 
                                    resize="none"
                                    :autosize="{ minRows: 4, maxRows: 6}">
                                    </el-input>
                                </el-col>
                            </el-form-item>
                        </el-form>
                    </el-card>

                    <div class="outside-card-button">
                        <el-button type="primary" v-on:click="createPO" ><i class="el-icon-plus"></i> Create</el-button>
                        <el-button type="warning" ><i class="fas fa-file-pdf"></i> Preview</el-button>
                        <el-button type="danger" @click="$router.back(-1)"><i class="el-icon-close"></i> Cancel</el-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script>
import moment from 'moment'
    export default {
        data(){
            return {
                form: new Form({
                    quotationTotal: '',
                    qty: '',
                    unit: '',
                    po_date: '',
                    receive_date: '',
                    delivery_date: '',
                    unit_price: '',
                    total: '',
                    part_no: '',
                    product_des: '',
                    quotation: {},
                    quotations: [],
                    client_id: '',
                    po_order: '',
                    order: {},
                    csTable: [],
                    date: '',
                    remarks: '',
                    
                
                }),
                isEdit: false,
                orders: '',
                order_des: '',
                clients: [],
                less_than_indexes: [],
                primaryMaterialList: [
                    { id: 1, name: 'Test', address: 'Test' },
                    { id: 2, name: 'Test', address: 'Test' },
                    { id: 3, name: 'Test', address: 'Test' },
                    { id: 4, name: 'Test', address: 'Test' },
                ],
                checkbox_dummy: true,
                checkerTable: [
                    { id: 1, name: 'John Doe' },
                    { id: 2, name: 'Jane Doe' },
                ],
                approverTable: [
                    { id: 1, name: 'Spongebob Squarepants' },
                ],
                // units_quantity = [
                //     { 'id': 1, 'unit_name': 'pcs' },
                //     { 'id': 2, 'unit_name': 'box' },
                //     { 'id': 3, 'unit_name': 'roll' },
                //     { 'id': 4, 'unit_name': 'sheet' },
                // ],
            }
        },
        methods: {
             initializePOdates(){
                this.form.po_date = new Date().toISOString().slice(0,10);
                this.form.receive_date = new Date().toISOString().slice(0,10);
                this.form.delivery_date = new Date().toISOString().slice(0,10);
                this.form.unit = 1;
            },
            getCustomers(){
                axios.get('/api/get-clients')
                .then((res) => {
                    this.clients = res.data;
                })
                .catch((error) => {
                    console.log(error)
                });
            },
            selectCustomer(id){
                console.log(id)
                this.orders = '';
                this.form.product_des = '';
                this.form.part_no = '';
                this.form.order.id = '';
                this.form.csTable = [];
                // this.getQuotations(id);
                axios.get('/api/get-orders/'+id)
                .then((res) => {
                    this.orders = res.data;
                    
                })
                .catch((error) => {
                    console.log(error)
                });
                
            },
            
             clientLabel(name, short_name){
                return '(' + short_name + ') ' + name;
            },
            selectOrder(id){
                console.log(id)
                for (let i = 0; i < this.orders.length; i++) {
                    if(id == this.orders[i].id){
                        this.form.order_id = this.orders[i].id;
                        this.form.part_no = this.orders[i].order_part_number;
                        this.form.product_des = this.orders[i].order_item_description;
                    }
                }
                axios.get('/api/get-quotations/'+this.form.client_id)
                .then((res) => {
                    this.form.csTable = res.data;
                   
                })
                .catch((error) => {
                    console.log(error)
                });
            },
            pageType(){
                if(window.location.href.indexOf("update") > -1){
                    this.isEdit = true;
                }
                else{
                    this.isEdit = false;
                }
            },
            amountLabel(){
                let $currency = '';
                if(this.form.quotation.currency) {
                    $currency = this.form.quotation.currency;
                    return 'Amount' + '('+$currency+')';
                }else return 'Amount';
            },
            unitLabel(unit){
                for (let i = 0; i < this.$units_quantity.length; i++) {
                    if(unit == this.$units_quantity[i].id) return this.$units_quantity[i].unit_name;
                }
                return this.$units_quantity[0].unit_name;
                // return unit;
            },
            pickQuotation(){
                Swal.fire({
                    text: 'Are you sure you want to add quotation to list',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Added',
                    width: '25rem',
                })
                .then((result) => {
                    let id = 1;
                    if(this.form.quotations.length > 0) id = this.form.quotations[this.form.quotations.length - 1].id + 1;
                    for (let i = 0; i < this.form.csTable.length; i++) {
                        if(this.form.qty == this.form.csTable[i].cs_qty)
                            this.form.quotations.push({
                                'id': id,
                                'cs_id': this.form.csTable[i].cs_id, 
                                'cs_control_no': this.form.csTable[i].cs_control_no, 
                                'cs_qty': this.form.qty,
                                'cs_unit_amount': this.form.unit_price,
                                'cs_unit_qty': this.form.unit,
                                'cs_unit_amount_currency': this.form.csTable[i].cs_unit_amount_currency    ,
                                'cs_unit_amount_php': this.form.total,
                                });

                        else this.less_than_indexes.push({'id': i});
                    }
                    for (let j = 0; j < this.form.csTable.length; j++) {  
                    var lowest = Number.POSITIVE_INFINITY;
                    var highest = Number.NEGATIVE_INFINITY;
                    var checker; 
                    if(this.form.qty != this.form.csTable[j].cs_qty)
                        for (var i=this.less_than_indexes.length-1; i>=0; i--){
                            checker = this.less_than_indexes[i].id;
                            if (checker < lowest) lowest = checker;
                            if (checker > highest) highest = checker;
                        
                        }
                    }
                    console.log(lowest, highest);
                    if(this.form.qty < this.form.csTable[lowest].cs_qty || this.form.qty < this.form.csTable[highest].cs_qty){
                        this.form.quotations.push({
                                'id': id,
                                'cs_id': this.form.csTable[lowest].cs_id, 
                                'cs_control_no': this.form.csTable[lowest].cs_control_no,
                                'cs_unit_qty': this.form.unit,
                                'cs_qty': this.form.qty,
                                'cs_unit_amount': this.form.unit_price,
                                'cs_unit_amount_currency': this.form.csTable[lowest].cs_unit_amount_currency,
                                'cs_unit_amount_php': this.form.total,
                                });
                    }
                    else if(this.form.qty > this.form.csTable[highest].cs_qty){
                        this.form.quotations.push({
                                'id': id,
                                'cs_id': this.form.csTable[highest].cs_id,
                                'cs_unit_qty': this.form.unit,
                                'cs_control_no': this.form.csTable[highest].cs_control_no, 
                                'cs_qty': this.form.qty,
                                'cs_unit_amount': this.form.unit_price,
                                'cs_unit_amount_currency': this.form.csTable[highest].cs_unit_amount_currency,
                                'cs_unit_amount_php': this.form.total,
                                });
                    }
                    else console.log('error');
                    this.less_than_indexes = [];
                    
                    if(this.form.quotations.length > 0) this.totalQuotation();
                    
                })
                .catch(error => {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops!',
                                text: 'Quotation registration failed.',
                            })
                            console.log(error)
                        });
                
                
            },

            removeQuotation(id){
                for(var i = this.form.quotations.length - 1; i >= 0; i--) {
                    if(this.form.quotations[i].id == id) {
                        this.form.quotations.splice(i, 1);
                    }
                }
                this.totalQuotation();
                    
            },
            totalCS(){
                let total = 0;
                
                    let unit_price = parseFloat(this.form.unit_price);
                    let qty = parseFloat(this.form.qty);

                    this.form.total = parseFloat(unit_price * qty).toFixed(4);

                    if(isNaN(unit_price)) this.form.total = 0;
                    if(isNaN(qty)) this.form.total = 0;
 
            },
             createPO(){
                console.log('AWW')
                Swal.fire({
                    text: 'Are you sure you want to copy new purchase order',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Register',
                    width: '25rem',
                })
                .then((result) => {
                    if(result.value){
                        this.form.post('/api/purchase-order')
                        .then((res) => {
                            Swal.fire(
                                'Registered!',
                                'Purchase Order successfully registered.',
                                'success'
                            )
                        })
                        .catch(error => {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops!',
                                text: 'Purchase Order registration failed.',
                            })
                            console.log(error)
                        });
                    }
                })
            },

            totalQuotation(){
                let total = 0;
                let quote = this.form.quotations;
                for (let i = 0; i < quote.length; i++) {
                    total += parseFloat(quote[i].cs_unit_amount_php);
                }
                this.form.quotationTotal = parseFloat(total).toFixed(4);
            },
           
            
        },
        created(){
            this.getCustomers();
            this.pageType();
            this.initializePOdates();
        },

    }
</script>
