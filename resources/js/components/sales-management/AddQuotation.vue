<template>
    <div class="container">
        <div class="content-header mb-3" style="border-bottom: 1px solid black;">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h4 class="m-0 text-dark"><strong>Add Quotation</strong></h4>
                    </div>
                    <div class="col-sm-6">
                        <button type="button" class="btn button-standard float-right" @click="$router.push('/quotations')">
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
                            <span>Customer Details</span>
                        </div>
                        <el-form ref="form" :model="form" label-width="15%" :label-position="`right`">
                            <el-form-item label="Customer Name">
                                <el-col :span="5">
                                    <el-select v-model="form.quotation.client_id" placeholder="Customer Number" class="block" @change="selectCustomer" :remote="true">
                                        <el-option
                                        v-for="client in clients"
                                        :key="client.id"
                                        :label="client.client_number"
                                        :value="client.id">
                                        </el-option>
                                    </el-select>
                                </el-col>
                                <el-col :span="15" class="ml-1">
                                    <el-select v-model="form.quotation.client_id" placeholder="Customer Name" class="block" @change="selectCustomer" :remote="true">
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
                            <el-form-item label="Person-In-Charge">
                                <el-col :span="12">
                                    <el-select v-model="form.quotation.client_pic_id" placeholder="PIC" class="block" :remote="true" :disabled="!form.quotation.client_id">
                                        <el-option
                                        v-for="client in clientPIC"
                                        :key="client.id"
                                        :label="client.client_pic"
                                        :value="client.id">
                                        </el-option>
                                    </el-select>
                                </el-col>
                            </el-form-item>
                        </el-form>
                    </el-card>

                    <el-card class="box-card mt-3" shadow="hover">
                        <div slot="header">
                            <span>Computation Sheets</span>
                        </div>
                        <el-form ref="form" :model="form" label-width="15%" :label-position="`right`">
                            <el-form-item label="Part Number">
                                <el-col :span="12">
                                    <el-select v-model="form.order.id" placeholder="Part Number" class="block" @change="selectOrder" :remote="true" :disabled="this.orders.length == 0">
                                        <el-option
                                        v-for="order in orders"
                                        :key="order.id"
                                        :label="order.order_part_number"
                                        :value="order.id">
                                        </el-option>
                                    </el-select>
                                </el-col>
                                <el-col :span="12" style="text-align: right">
                                    <button type="button" class="btn button-standard ml-3" size="mini" @click="addCS">
                                        <i class="el-icon-plus"></i> Add Manual CS
                                    </button>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Product Name">
                                <el-col :span="12">
                                    <el-input v-model="order_description" :disabled="true"></el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Quantity Unit">
                                <el-col :span="6">
                                    <el-select v-model="form.cs_qty_unit" class="block" placeholder="Unit" @change="selectCSQuantityUnit">
                                        <el-option
                                        v-for="unit in $units_quantity"
                                        :key="unit.id"
                                        :label="unit.unit_name"
                                        :value="unit.id">
                                            </el-option>
                                    </el-select>
                                </el-col>
                            </el-form-item>
                            <el-form-item :label="`Pieces / `+cs_qty_unit_string" v-show="form.cs_qty_unit > 1">
                                <el-col :span="6">
                                    <el-input v-model="form.cs_pieces_per_qty_unit" @input="changePerQuantity"></el-input>
                                </el-col>
                            </el-form-item>
                            <!-- <el-form-item label="Currency">
                                <el-col :span="24">
                                    <el-radio v-model="form.currency" @change="changeCurrency" label="PHP">PHP</el-radio>
                                    <el-radio v-model="form.currency" @change="changeCurrency" label="USD">USD</el-radio>
                                </el-col>
                            </el-form-item> -->
                        </el-form>
                        
                        <el-row v-if="showControlNumberList">
                        <h5>Control Number List</h5>
                        <el-table
                            v-if="this.orders.length > 0"
                            :data="csTable"
                            ref="multipleTable"
                            style="width: 100%"
                            @selection-change="handleSelectionChange">
                            <el-table-column
                                type="selection">
                            </el-table-column>
                            <el-table-column
                                prop="cs_control_number"
                                label="Control No.">
                            </el-table-column>
                            <el-table-column
                                property="cs_qty"
                                label="Quantity">
                                <template slot-scope="scope">
                                    {{ Math.ceil(csTable[scope.$index].cs_qty) }}
                                </template>
                            </el-table-column>
                            <el-table-column
                                property="cs_qty_unit"
                                label="Unit Price "
                                show-overflow-tooltip>
                                <template slot-scope="scope">
                                    {{ (csTable[scope.$index].cs_total_amount / csTable[scope.$index].cs_qty).toFixed(4) }}
                                </template>
                            </el-table-column>
                            <el-table-column
                                property="cs_total_amount"
                                :label="amountLabel()"
                                show-overflow-tooltip>
                                <template slot-scope="scope">
                                    <el-col :span="12">
                                        {{ csTable[scope.$index].cs_currency }}
                                    </el-col>
                                    <el-col :span="12">
                                        {{ csTable[scope.$index].cs_total_amount }}
                                    </el-col>
                                </template>
                            </el-table-column>
                        </el-table>
                        </el-row>
                        <el-row  v-if="this.orders.length == 0 && this.csTable.length == 0">
                             <div v-for="(added) in form.added_quotation" :key="added.id">
                            <el-row>
                                <el-col :span="8">
                                    <el-input v-model="added.quotation_qty" placeholder="Quantity" class="misc-input" @input="totalCS"></el-input>
                                </el-col>
                                <el-col :span="8">
                                    <el-input v-model="added.quotation_unit_price" placeholder="Unit Price" class="misc-input" @input="totalCS"></el-input>
                                </el-col>
                          
                                <el-col :span="8">
                                    <el-col :span="24">
                                        <el-input v-model="added.quotation_amount" placeholder="Amount" class="misc-input" disabled></el-input>
                                    </el-col>
                                </el-col>
                            </el-row>
                        </div>
                            <el-col :span="24" class="text-center">
                                <button type="button" class="btn button-standard ml-3" size="mini" @click="addQuotation">
                                    <i class="el-icon-plus"></i> Add Quotation
                                </button>
                                <button type="button" class="btn btn-danger ml-3" size="mini" @click="removeQuotation" v-show="this.form.added_quotation.length > 0">
                                    <i class="el-icon-minus"></i> Remove row
                                </button>
                            </el-col>
                        </el-row>
                    </el-card>

                    <el-card class="box-card mt-3" shadow="hover">
                        <div slot="header">
                            <span>Initial Charge</span>
                        </div>
                        <el-row class="content-title">
                            <el-col :span="9">
                                <strong>Item Name</strong>
                            </el-col>
                            <el-col :span="5">
                                <strong>Quantity {{ ' ('+cs_qty_unit_string+')' }}</strong>
                            </el-col>
                            <el-col :span="5">
                                <strong>Unit Price {{ ' ('+form.currency+')' }}</strong>
                            </el-col>
                            <el-col :span="5">
                                <strong>Amount {{ ' ('+form.currency+')' }}</strong>
                            </el-col>
                        </el-row>
                        <div v-for="(added, index) in form.initial_charge" :key="added.id">
                            <el-row>
                                <el-col :span="9">
                                    <el-input v-model="added.item_name"  class="misc-input"></el-input>
                                </el-col>
                                <el-col :span="5">
                                    <el-input v-model="added.initial_charge_qty" class="misc-input"></el-input>
                                </el-col>
                                <el-col :span="5">
                                    <el-input v-model="added.initial_charge_unit_price" class="misc-input"></el-input>
                                </el-col>
                          
                                <el-col :span="5">
                                    <el-input :value="totalInitialCharge(added.id, added.initial_charge_qty, added.initial_charge_unit_price, index)" class="misc-input" :disabled="true"></el-input>
                                </el-col>
                            </el-row>
                        </div>
                            <el-row>
                                <el-col :span="24" class="text-center">
                                    <button type="button" class="btn button-standard ml-3" size="mini" @click="addInitialCharge">
                                        <i class="el-icon-plus"></i> Add Initial Charge
                                    </button>
                                    <button type="button" class="btn btn-danger ml-3" size="mini" @click="removeInitialCharge" v-show="this.form.initial_charge.length > 1">
                                        <i class="el-icon-minus"></i> Remove row
                                    </button>
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

                    <el-card class="box-card mt-3" shadow="hover">
                        <div slot="header">
                            <span>Evaluation</span>
                        </div>
                        <el-row class="mb-3">
                            <el-col :span="12" class="text-center">
                                <h5 class="content-title">Checker</h5>
                            </el-col>
                            <el-col :span="12" class="text-center">
                                <h5 class="content-title">Approver</h5>
                            </el-col>
                        </el-row>
                         <el-row>
                            <el-col :span="6">
                                <el-select v-model="draft_evaluator_checker" placeholder="Select checker" class="block">
                                    <el-option
                                    v-for="evaluator in evaluator_checkers"
                                    :key="evaluator.id"
                                    :label="evaluator.first_name + ' ' + evaluator.last_name"
                                    :value="evaluator.id">
                                    </el-option>
                                </el-select>
                            </el-col>
                            <el-col :span="6">
                                <button type="button" class="btn button-standard ml-3" size="mini" @click="addChecker">
                                    <i class="el-icon-plus"></i> Add checker
                                </button>
                            </el-col>
                            <el-col :span="6">
                                <el-select v-model="draft_evaluator_approver" placeholder="Select approver" class="block">
                                    <el-option
                                    v-for="evaluator in evaluator_approvers"
                                    :key="evaluator.id"
                                    :label="evaluator.first_name + ' ' + evaluator.last_name"
                                    :value="evaluator.id">
                                    </el-option>
                                </el-select>
                            </el-col>
                            <el-col :span="6">
                                <button type="button" class="btn button-standard ml-3" size="mini" @click="addApprover">
                                    <i class="el-icon-plus"></i> Add approver
                                </button>
                            </el-col>
                        </el-row>
                        <el-row class="mt-3">
                            <el-col :span="12">
                                <el-table
                                    :data="form.checkers"
                                    style="width: 100%">
                                    <el-table-column
                                    prop="evaluator_name"
                                    label="Name">
                                    </el-table-column>
                                    <el-table-column label="Action" fixed="right" width="70">
                                        <template slot-scope="scope">
                                            <el-tooltip class="item" effect="dark" content="Delete" placement="top">
                                                <i class="icon el-icon-delete pointer ml-2" @click="deleteCSEvaluator(form.checkers[scope.$index].evaluator_id, 'checker')"></i>
                                            </el-tooltip>
                                        </template>
                                    </el-table-column>
                                </el-table>
                            </el-col>
                            <el-col :span="12">
                                <el-table
                                    :data="form.approvers"
                                    style="width: 100%">
                                    <el-table-column
                                    prop="evaluator_name"
                                    label="Name">
                                    </el-table-column>
                                    <el-table-column label="Action" fixed="right" width="70">
                                        <template slot-scope="scope">
                                            <el-tooltip class="item" effect="dark" content="Delete" placement="top">
                                                <i class="icon el-icon-delete pointer ml-2" @click="deleteCSEvaluator(form.approvers[scope.$index].evaluator_id, 'approver')"></i>
                                            </el-tooltip>
                                        </template>
                                    </el-table-column>
                                </el-table>
                            </el-col>
                        </el-row>
                    </el-card>

                    <div class="outside-card-button">
                        <el-button type="primary" v-on:click="createQuotation"><i class="el-icon-plus"></i> Create</el-button>
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
                    quotation: {},
                    added_quotation: [],
                    initial_charge: [],
                    order: {},
                    cs_qty_unit: 1,
                    cs_pieces_per_qty_unit: 1,
                    selectedCs: [],
                    checkers: [],
                    approvers: [],
                }),
                csTable: [],
                orders: [],
                clients: [],
                inputQuote: false,
                addQuote: false,
                order_description: '',
                showControlNumberList: false,
                evaluator_checkers: [],
                evaluator_approvers: [],
                draft_evaluator_checker: '',
                draft_evaluator_approver: '',
                exchange_rates: [],
                cs_qty_unit_string: '',
                clientPIC: [],
                client_id: '',
                originalCNTable: []
            }
        },
        methods: {
            initializeUnit(){
                this.form.cs_qty_unit = 1;
                this.form.cs_pieces_per_qty_unit = 1;
                this.form.currency = 'PHP';
                this.cs_qty_unit_string = 'pcs';
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
                this.orders = [];
                this.order_des = '';
                this.form.order.id = '';
                this.csTable = '';
                this.client_id = id

                axios.get('/api/get-orders/'+id)
                .then((res) => {
                    this.orders = res.data;
                    this.initializeUnit();
                    this.getClientPIC(id);
                })
                .catch((error) => {
                    console.log(error)
                });

                for(let i = 0; i < this.clients.length; i++){
                    if(this.clients[i].id == id) {
                        this.form.quotation.client_name = this.clients[i].client_name;
                        this.form.quotation.client_address = this.clients[i].client_address;
                        };               
                }

                if(this.orders == []){
                    this.addQuote = true;     
                }
            },
            getClientPIC(id){
                axios.get('/api/get-client-pic/'+id)
                .then((res) => {
                    this.clientPIC = res.data;
                })
                .catch((error) => {
                    console.log(error)
                })
            },
            selectOrder(id){
                axios.put('/api/order-selected/'+id, {
                    params:{
                        client_id: this.form.quotation.client_id,
                    }
                })
                .then((res) => {
                    this.csTable = res.data.cs;
                    
                    for (let i = 0; i < this.csTable.length; i++) {
                        this.originalCNTable.push({
                            id: this.csTable[i].id,
                            quantity: this.csTable[i].cs_qty
                        })
                    }
                    this.form.currency = this.csTable[0].cs_currency;
                    this.showControlNumberList = true;
                    this.form.quotation.order_id = id;
                     for(let i = 0; i < this.orders.length; i++){
                            if(this.orders[i].id == id) {
                                this.order_description = this.orders[i].order_item_description;
                                this.form.part_name = this.orders[i].order_part_number;
                                };
                                
                        }
                })
                .catch((error) => {
                    console.log(error)
                });
            },
            clientLabel(name, short_name){
                return '(' + short_name + ') ' + name;
            },
            addQuotation(){
                this.inputQuote = true;
                let id = 1;
                if(this.form.added_quotation.length > 0) id = this.form.added_quotation[this.form.added_quotation.length - 1].id + 1;

                this.form.added_quotation.push({
                    id: id,
                    quotation_qty: '',
                    quotation_unit_price: '',
                    quotation_currency: '',
                    quotation_amount: '',
                })
            },
            amountLabel(){
                let currency = '';
                if(this.form.currency) {
                    currency = this.form.currency;
                    return 'Amount' + ' ('+currency+')';
                }else return 'Amount';
            },
            addInitialCharge(){
                let id = 1;
                if(this.form.initial_charge.length > 0) id = this.form.initial_charge[this.form.initial_charge.length - 1].id + 1;

                this.form.initial_charge.push({
                    id: id,
                    item_name: '',
                    initial_charge_qty: '',
                    initial_charge_unit_price: '',
                    initial_charge_amount: '',
                })
            },
            removeQuotation(){
                this.form.added_quotation.splice(this.form.added_quotation.length - 1, 1);
            },
            removeInitialCharge(){
                this.form.initial_charge.splice(this.form.initial_charge.length - 1, 1);
            },
            createQuotation(){
                Swal.fire({
                    text: 'Are you sure you want to create new quotation',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Register',
                    width: '25rem',
                })
                .then((result) => {
                    if(result.value){
                        this.form.post('api/quotation')
                        .then((res) => {
                            this.form.reset();
                            this.initializeInitialCharge()
                            this.form.selectedCs = [];
                            this.$refs.multipleTable.clearSelection();
                            this.order_description = ''
                            
                            Swal.fire(
                                'Registered!',
                                'Quotation successfully registered.',
                                'success'
                            )
                            this.form.selectedCs = [];
                            this.$refs.multipleTable.clearSelection();
                            this.initializeUnit();
                        })
                        .catch(error => {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops!',
                                text: 'Quotation registration failed.',
                            })
                            console.log(error)
                        });
                    }
                })
            },
            initialChargeTotal(){
                this.form.initial_charge.initial_charge_amount = parseInt(this.form.initial_charge.initial_charge_qty 
                * this.form.initial_charge.initial_charge_unit_price).toFixed(4);
                if(isNaN(this.form.initial_charge.initial_charge_qty)) this.form.initial_charge.initial_charge_amount = 0;
                if(isNaN(this.form.initial_charge.initial_charge_unit_price)) this.form.initial_charge.initial_charge_amount = 0;
            },
            resetFields(){
                this.form.clear();
            },
            totalInitialCharge(id, quantity, price, index){
                this.form.initial_charge[index].initial_charge_amount = parseFloat(quantity * price).toFixed(4);
                return parseFloat(quantity * price).toFixed(4);
            },
            addContactPerson(){
                let id = '';
                id = this.form.contactPersons[this.form.contactPersons.length - 1].id + 1;
                this.form.contactPersons.push({ 'id': id, 'contact_name': '', 'contact_email': '' });
            },
            deleteContactPerson(id){
                for(let i = 0; i < this.form.contactPersons.length; i++){
                    if(this.form.contactPersons[i].id == id) this.form.contactPersons.splice(i, 1);
                }
            },
            totalCS(){
                let total = 0;
                let quote = this.form.added_quotation;
                for (let i = 0; i < quote.length; i++) {
                    

                    let unit_price = parseFloat(quote[i].quotation_unit_price);
                    let qty = parseFloat(quote[i].quotation_qty);

                    quote[i].quotation_amount = parseFloat(unit_price * qty).toFixed(4);

                    if(isNaN(unit_price)) quote[i].quotation_amount = 0;
                    if(isNaN(qty)) quote[i].quotation_amount = 0;
 
                }
            },
            handleSelectionChange(val) {
                this.form.selectedCs = val;
            },
            getEvaluators(){
                axios.get('/api/get-evaluators')
                .then((res) => {
                    this.evaluator_checkers = res.data.checkers;
                    this.evaluator_approvers = res.data.approvers;
                })
                .catch((error) => {
                    console.log(error);
                });
            },
            addChecker(){
                Swal.fire({
                    text: 'Are you sure you want to add user as checker?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Add to list',
                    width: '25rem',
                })
                .then((result) => {
                    if(result.value){
                        if(!_.find(this.form.checkers, { 'evaluator_id': this.draft_evaluator_checker }) && !_.find(this.form.approvers, { 'evaluator_id': this.draft_evaluator_checker })){
                            let selected_checker = _.find(this.evaluator_checkers, { 'id': this.draft_evaluator_checker });
                            let evaluator_name = selected_checker.first_name + ' ' + selected_checker.last_name;

                            this.form.checkers.push({
                                evaluator_id: selected_checker.id,
                                evaluator_name: evaluator_name,
                                evaluator_permission: 1,
                                evaluator_remarks: '',
                            });

                            this.draft_evaluator_checker = '';

                            Swal.fire(
                                'Added to list!',
                                'User was successfully added as checker.',
                                'success'
                            );
                        }
                        else{
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops!',
                                text: 'User was already assigned as checker/approver.',
                            });
                        }
                    }
                });
            },
            addApprover(){
                Swal.fire({
                    text: 'Are you sure you want to add user as approver?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Add to list',
                    width: '25rem',
                })
                .then((result) => {
                    if(result.value){
                        if(!_.find(this.form.approvers, { 'evaluator_id': this.draft_evaluator_approver }) && !_.find(this.form.checkers, { 'evaluator_id': this.draft_evaluator_approver })){
                            let selected_approver = _.find(this.evaluator_approvers, { 'id': this.draft_evaluator_approver });
                            let evaluator_name = selected_approver.first_name + ' ' + selected_approver.last_name;

                            this.form.approvers.push({
                                evaluator_id: selected_approver.id,
                                evaluator_name: evaluator_name,
                                evaluator_permission: 2,
                                evaluator_remarks: '',
                            });

                            this.draft_evaluator_approver = '';

                            Swal.fire(
                                'Added to list!',
                                'User was successfully added as approver.',
                                'success'
                            );
                        }
                        else{
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops!',
                                text: 'User was already assigned as checker/approver.',
                            });
                        }
                    }
                });
            },
            deleteCSEvaluator(evaluator_id, type){
                Swal.fire({
                    text: 'Are you sure you want to delete ' + type + ' from list?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Delete',
                    width: '25rem',
                })
                .then((result) => {
                    if(result.value){
                        if(type == 'checker'){
                            for(let i = 0; i < this.form.checkers.length; i++){
                                if(this.form.checkers[i].evaluator_id == evaluator_id) this.form.checkers.splice(i, 1);
                            }
                        }
                        else if(type == 'approver'){
                            for(let i = 0; i < this.form.approvers.length; i++){
                                if(this.form.approvers[i].evaluator_id == evaluator_id) this.form.approvers.splice(i, 1);
                            }
                        }

                        Swal.fire(
                            'Deleted!',
                            'Evaluator has been removed from the list.',
                            'success'
                        )
                    }
                });
            },

            changeCurrency(){
                if(this.form.quotation.client_id){
                    Swal.fire({
                        text: 'Are you sure you want to change currency? Existing draft in control number list and initial charge lists will be reloaded.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Change',
                        width: '25rem',
                    })
                    .then((result) => {
                        if(result.value){
                            this.selectOrder(this.form.order.id);

                            Swal.fire(
                                'Changed!',
                                'Currency successfully changed.',
                                'success'
                            )
                        }
                    });
                }
            },
            selectCSQuantityUnit(){
                for (let i = 0; i < this.$units_quantity.length; i++) {
                    if(this.$units_quantity[i].id == this.form.cs_qty_unit) this.cs_qty_unit_string = this.$units_quantity[i].unit_name;
                }
            },
            initializeInitialCharge () {
                this.form.initial_charge.push({
                    'id': 1,
                    'initial_charge_amount': '0.0000',
                    'initial_charge_qty': '',
                    'initial_charge_unit_price': '',
                    'item_name': ''
                })
            },
            addCS(){
                Swal.fire({
                    text: 'Are you sure you want to create new CS? Another tab will open for new CS registration.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Create CS',
                    width: '25rem',
                })
                .then((result) => {
                    if(result.value){
                        window.open('/add-cs');
                    }
                });
            },
            getOrders () {
                axios.get('/api/get-orders/'+this.client_id)
                .then((res) => {
                    this.orders = res.data;
                    this.getClientPIC(this.client_id);
                })
                .catch((error) => {
                    console.log(error)
                });
            },
            initialCNUnitPrice (cs_total_amount, cs_total_quantity) {
                let unit_price = 0
                let cs_pieces_per_qty_unit = 1
                if (this.form.cs_pieces_per_qty_unit > 1) cs_pieces_per_qty_unit = this.form.cs_pieces_per_qty_unit
                unit_price = cs_total_amount / cs_total_quantity / cs_pieces_per_qty_unit
                return parseFloat(unit_price).toFixed(4)
            }
        },
        created(){
            this.getCustomers();
            this.getEvaluators();
            this.initializeInitialCharge();
            this.initializeUnit();
        },
        computed: {
            changePerQuantity () {
                let cs_pieces_per_qty_unit = 1
                if (this.form.cs_pieces_per_qty_unit > 1) cs_pieces_per_qty_unit = this.form.cs_pieces_per_qty_unit

                for (let i = 0; i < this.csTable.length; i++) {
                    let original_object = _.find(this.originalCNTable, { 'id': this.csTable[i].id })
                    this.csTable[i].cs_qty = original_object.quantity / cs_pieces_per_qty_unit
                }
                // console.log(this.originalCNTable)
            }
        }
    }
</script>
