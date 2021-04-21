<template>
    <div class="container">
        <div class="content-header mb-3" style="border-bottom: 1px solid black;">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h4 class="m-0 text-dark" v-show="isEdit" ><strong>Edit Quotation</strong></h4>
                        <h4 class="m-0 text-dark" v-show="!isEdit"><strong>View Quotation</strong></h4>
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
                <form @submit.prevent="updateOrder()">
                    <el-card class="box-card" shadow="hover">
                        <div slot="header">
                            <span>Customer Details</span>
                        </div>
                        <el-form ref="form" :model="form" label-width="15%" :label-position="`right`">
                            <el-form-item label="Customer Name">
                                <el-col :span="5">
                                    <el-select v-model="form.quote.client_id" placeholder="Customer Number" @change="selectCustomer" class="block" :remote="true" :disabled="true">
                                        <el-option
                                        v-for="client in clients"
                                        :key="client.id"
                                        :label="client.client_number"
                                        :value="client.id">
                                        </el-option>
                                    </el-select>
                                </el-col>
                                <el-col :span="15" class="ml-1">
                                    <el-select v-model="form.quote.client_id" placeholder="Customer Name" @change="selectCustomer" class="block" :remote="true" :disabled="true">
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
                                    <el-select v-model="form.quotation.client_pic_id" placeholder="PIC" class="block" :remote="true" :disabled="!isEdit">
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
                                   <el-select v-model="form.part_no" placeholder="Part Number" class="block" @change="selectOrder" :remote="true" disabled>
                                        <el-option
                                        v-for="order in orders"
                                        :key="order.id"
                                        :label="order.order_part_number"
                                        :value="order.id">
                                        </el-option>
                                    </el-select>
                                </el-col>
                                <!-- <el-col :span="12" style="text-align: right">
                                    <button type="button" class="btn button-standard ml-3" size="mini" @click="openAddCS" v-if="isEdit">
                                        <i class="el-icon-plus"></i> Add Manual CS
                                    </button>
                                </el-col> -->
                            </el-form-item>
                            <el-form-item label="Product Name">
                                <el-col :span="12">
                                    <el-input v-model="form.product_name" :disabled="true"></el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Quantity Unit">
                                <el-col :span="6">
                                    <el-select v-model="form.cs_qty_unit" class="block" placeholder="Unit" @change="selectCSQuantityUnit" :disabled="readonly">
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
                                    <el-input v-model="form.cs_pieces_per_qty_unit" @input="changePerQuantity" :disabled="readonly"></el-input>
                                </el-col>
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
                               
                                prop="cs_control_number"
                                label="Control Number">
                            </el-table-column>
                            <el-table-column
                                property="cs_qty"
                                label="Quantity">
                            </el-table-column>
                            <el-table-column
                                property="cs_qty_unit"
                                label="Unit Price   "
                                show-overflow-tooltip>
                            </el-table-column>
                            <el-table-column
                                property="cs_total_amount"
                                :label="amountLabel()"
                                show-overflow-tooltip>
                                <template slot-scope="scope">
                                    <el-col :span="12">
                                        {{ form.csTable[scope.$index].cs_currency }}
                                    </el-col>
                                    <el-col :span="12">
                                        {{ form.csTable[scope.$index].cs_total_amount }}
                                    </el-col>
                                </template>
                            </el-table-column>
                        </el-table>

                        <el-row v-if="this.form.part_no == 0 && isEdit" class="mt-10">
                            <div v-for="(added) in form.added_quotation" :key="added.id">
                            <el-row>
                                <el-col :span="8">
                                    <el-input v-model="added.quotation_qty" placeholder="Quantity" class="misc-input" @input="totalCS" :disabled="!isEdit"></el-input>
                                </el-col>
                                <el-col :span="8">
                                    <el-input v-model="added.quotation_unit_price" placeholder="Unit Price" class="misc-input" @input="totalCS" :disabled="!isEdit"></el-input>
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
                        <!-- <el-row>
                            <el-col :span="12">
                                <el-form :label-position="`right`">
                                    <el-form-item label="Quantity Unit">
                                        <el-col :span="8">
                                            <el-select v-model="form.cs_qty_unit" placeholder="Unit" :disabled="!isEdit">
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
                            <el-col :span="12">
                                <el-form :label-position="`right`">
                                    <el-form-item label="Pieces per Quantity Unit">
                                        <el-col :span="8">
                                            <el-input v-model="form.cs_pieces_per_qty_unit" :disabled="!isEdit"></el-input>
                                        </el-col>
                                    </el-form-item>
                                </el-form>
                            </el-col>
                        </el-row> -->
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
                                <strong>Quantity</strong>
                            </el-col>
                            <el-col :span="5">
                                <strong>Unit Price</strong>
                            </el-col>
                            <el-col :span="5">
                                <strong>Amount {{ '('+form.currency+')' }}</strong>
                            </el-col>
                        </el-row>
                        <div v-for="(added, index) in form.initial_charge" :key="added.id">
                            <el-row>
                                <el-col :span="9">
                                    <el-input v-model="added.item_name"  class="misc-input" :disabled="readonly"></el-input>
                                </el-col>
                                <el-col :span="5">
                                    <el-input v-model="added.item_qty" class="misc-input" :disabled="readonly"></el-input>
                                </el-col>
                                <el-col :span="5">
                                    <el-input v-model="added.item_unit_amt" class="misc-input" :disabled="readonly"></el-input>
                                </el-col>
                          
                                <el-col :span="5">
                                    <el-input :value="totalInitialCharge(added.id, added.item_qty, added.item_unit_amt, index)" class="misc-input" :disabled="true"></el-input>
                                </el-col>
                            </el-row>
                        </div>
                            <el-row>
                                <el-col :span="24" class="text-center" v-if="isEdit">
                                    <button type="button" class="btn button-standard ml-3" size="mini" @click="addInitialCharge">
                                        <i class="el-icon-plus"></i> Add Another Initial Charge
                                    </button>
                                    <button type="button" class="btn btn-danger ml-3" size="mini" @click="removeInitialCharge" v-show="this.form.initial_charge.length > 0">
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
                                    v-model="form.quote.remarks" 
                                    type="textarea" 
                                    resize="none"
                                    :disabled="readonly"
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
                            <el-row v-if="isEdit">
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
                                    label="Name">
                                        <template slot-scope="scope">
                                            {{ form.checkers[scope.$index].first_name + ' ' + form.checkers[scope.$index].last_name }}
                                        </template>
                                    </el-table-column>
                                    <el-table-column label="Action" fixed="right" width="70" v-if="isEdit">
                                        <template slot-scope="scope">
                                            <el-tooltip class="item" effect="dark" content="Delete" placement="top">
                                                <i class="icon el-icon-delete pointer ml-2" @click="deleteEvaluator(form.checkers[scope.$index].evaluator_id, 'checker')"></i>
                                            </el-tooltip>
                                        </template>
                                    </el-table-column>
                                    <el-table-column label="Status" fixed="right" width="140" v-else>
                                        <template slot-scope="scope">
                                            <i :class="'icon '+evaluationStatusIcon(form.checkers[scope.$index].evaluation_status)+' ml-2'"></i>
                                            {{ evaluationStatus(form.checkers[scope.$index].evaluation_status) }}
                                        </template>
                                    </el-table-column>
                                </el-table>
                            </el-col>
                            <el-col :span="12">
                                <el-table
                                    :data="form.approvers"
                                    style="width: 100%">
                                    <el-table-column
                                    label="Name">
                                        <template slot-scope="scope">
                                            {{ form.approvers[scope.$index].first_name + ' ' + form.approvers[scope.$index].last_name }}
                                        </template>
                                    </el-table-column>
                                    <el-table-column label="Action" fixed="right" width="70" v-if="isEdit">
                                        <template slot-scope="scope">
                                            <el-tooltip class="item" effect="dark" content="Delete" placement="top">
                                                <i class="icon el-icon-delete pointer ml-2" @click="deleteEvaluator(form.approvers[scope.$index].evaluator_id, 'approver')"></i>
                                            </el-tooltip>
                                        </template>
                                    </el-table-column>
                                    <el-table-column label="Status" fixed="right" width="140" v-else>
                                        <template slot-scope="scope">
                                            <i :class="'icon '+evaluationStatusIcon(form.approvers[scope.$index].evaluation_status)+' ml-2'"></i>
                                            {{ evaluationStatus(form.approvers[scope.$index].evaluation_status) }}
                                        </template>
                                    </el-table-column>
                                </el-table>
                            </el-col>
                        </el-row>
                    </el-row>
                    <h5>Remarks</h5>
                        <hr>
                        <el-row>
                            <el-col :span="24">
                                <el-table
                                    :data="evaluation_remarks"
                                    style="width: 100%">
                                    <el-table-column
                                    width="90"
                                    label="Type">
                                        <template slot-scope="scope">
                                            {{ evaluatorType(evaluation_remarks[scope.$index].evaluation_type) }}
                                        </template>
                                    </el-table-column>
                                    <el-table-column
                                    label="Name">
                                        <template slot-scope="scope">
                                            {{ evaluatorNameFormat(evaluation_remarks[scope.$index].first_name, evaluation_remarks[scope.$index].last_name) }}
                                        </template>
                                    </el-table-column>
                                    <el-table-column 
                                    prop="evaluator_remarks"
                                    label="Message">
                                    </el-table-column>
                                    <el-table-column
                                    fixed="right"
                                    width="150"
                                    label="Date">
                                        <template slot-scope="scope">
                                            {{ dateFormat(evaluation_remarks[scope.$index].updated_at) }}
                                        </template>
                                    </el-table-column>
                                </el-table>
                            </el-col>
                        </el-row>
                        <div v-show="!isEdit && evaluationForm.id" class="text-center">
                            <br>
                            <el-input 
                            type="textarea" 
                            v-model="evaluationForm.message"
                            resize="none"
                            style="width: 60%"
                            placeholder="Message"
                            :disabled="includesRejected || isChecked || preventApproval || isApproved"
                            :autosize="{ minRows: 4, maxRows: 6}">
                            </el-input>
                            <br>
                            <el-button type="success" icon="el-icon-success" @click="evaluateQuote" v-if="isEvaluated(evaluationForm.evaluation_status)" :disabled="includesRejected || isChecked || preventApproval || isApproved">{{ evaluationForm.evaluation_type == 1 ? 'Check' : 'Approve' }}</el-button>
                            <el-button type="danger" icon="el-icon-remove-outline" @click="rejectCSEvaluation()" v-if="isEvaluated(evaluationForm.evaluation_status)" :disabled="isChecked || preventApproval || isApproved">{{ evaluationForm.evaluation_status == 5 ? 'Revert' : 'Reject' }}</el-button>
                            <el-button type="danger" icon="el-icon-remove-outline" @click="evaluateQuote" :disabled="preventCheckDiscarding" v-else>Discard</el-button>
                        </div>
                    </el-card>

                    <div class="outside-card-button" v-if="!isEdit">
                        <el-button type="primary" @click="editQuote"><i class="el-icon-edit-outline"></i> Edit</el-button>
                        <el-button type="danger" @click="$router.back(-1)"><i class="el-icon-close"></i> Cancel</el-button>
                    </div>
                    <div class="outside-card-button" v-if="isEdit">
                        <el-button type="primary" v-on:click="updateQuotation"><i class="el-icon-circle-check"></i> Update</el-button>
                        <el-button type="danger" @click="$router.back(-1)"><i class="el-icon-close"></i> Cancel</el-button>
                    </div>
                </form>
            </div>
            
            <el-col class="text-center mt-3">
                <el-button class="pdf" size="mini" @click="exportPDF" v-show="!isEdit" round><i class="fas fa-file-pdf"></i> Download</el-button>
            </el-col>
        </div>
    </div>
</template>

<script>
    import moment from 'moment';
    export default {
        data(){
            return {
                form: new Form({
                    cs_pieces_per_qty_unit: '',
                    quote: {
                        quotation_qty_unit: '',
                    },
                    checkers : [],
                    approvers: [],
                    csTable: [],
                    part_no:  '',
                    product_name: '',
                    added_quotation: [],
                    initial_charge: [],
                    quotation: {},
                    customer_details: {},
                    cs: {},
                    initial_charge: [],
                }),
                evaluationForm: new Form({
                    id: '',
                    quote_id: '',
                    evaluator_id: '',
                    message: '',
                    evaluation_remarks: [],
                }),
                hideControlNumber: false,
                evaluation_remarks: [],
                showInitialCharge: false,
                csTable: [],
                isEdit: false,
                isLoading: false,
                clients: [],
                orders: [],
                test: false,
                evaluator_checkers: [],
                evaluator_approvers: [],
                draft_evaluator_checker: [],
                draft_evaluator_approver: [],
                clientPIC: [],
                cs_qty_unit_string: '',
                originalCNTable: [],
            }
        },
        methods: {
            getCustomers(){
                axios.get('/api/get-clients')
                .then((res) => {
                    this.clients = res.data;
                })
                .catch((error) => {
                    console.log(error)
                });
            },
            getQuotation(){
                axios.get('/api/quotation/'+this.$route.params.quotation_id)
                .then((res) => {
                    this.form.order = res.data.order;
                    this.form.quote = res.data.quote;
                    this.form.approvers = res.data.approvers;
                    this.form.checkers = res.data.checkers;
                    this.evaluationForm.evaluator_id = res.data.user_id;
                    this.form.currency = res.data.quote_cs[0].cs_unit_amount_currency;
                    this.evaluation_remarks = res.data.remarks;
                    this.form.quotation.client_pic_id = res.data.quote.client_pic_id;
                    // for (let i = 0; i < this.form.checkers.length; i++) {
                    //     if(this.form.checkers[i].evaluator_id == res.data.user_id){
                    //         this.evaluationForm.id = this.form.checkers[i].id;
                    //         this.evaluationForm.quote_id = this.$route.params.quotaion_id;
                    //         this.evaluationForm.message = this.form.checkers[i].evaluator_remarks;
                    //         this.evaluationForm.evaluation_type = this.form.checkers[i].evaluation_type;
                    //         this.evaluationForm.evaluation_status = this.form.checkers[i].evaluation_status;
                    //     }
                    // }
                    // for (let i = 0; i < this.form.approvers.length; i++) {
                    //     if(this.form.approvers[i].evaluator_id == res.data.user_id){
                    //         this.evaluationForm.id = this.form.approvers[i].id;
                    //         this.evaluationForm.quote_id = this.$route.params.quotaion_id;
                    //         this.evaluationForm.message = this.form.approvers[i].evaluator_remarks;
                    //         this.evaluationForm.evaluation_type = this.form.approvers[i].evaluation_type;
                    //         this.evaluationForm.evaluation_status = this.form.approvers[i].evaluation_status;
                    //     }
                    // }
                    this.evaluationForm.is_rejected = false;
                    if(this.evaluationForm.evaluation_status == 5) this.evaluationForm.is_rejected = true;
                    this.getOrders();
                    this.form.quotation.client_id = res.data.quote.client_id;
                    this.form.cs_qty_unit = res.data.quote.quotation_qty_unit;
                    this.form.cs_pieces_per_qty_unit = res.data.quote.order_qty_per_quotation_unit;
                    this.form.csTable = res.data.quote_cs;
                     for (let i = 0; i < this.form.csTable.length; i++){
                        this.form.csTable[i].cs_control_number = res.data.quote_cs[i].cs_control_no;
                        this.form.csTable[i].cs_qty_unit = res.data.quote_cs[i].cs_unit_amount;
                        this.form.csTable[i].cs_currency = res.data.quote_cs[i].cs_unit_amount_currency;
                        this.form.csTable[i].cs_total_amount = res.data.quote_cs[i].cs_unit_amount_php;
                        this.form.csTable[i].quote_cs_id = res.data.quote_cs[i].id;
                    }
                    this.form.initial_charge = res.data.quote_initial_charge;
                    if(res.data.order){
                        this.form.product_name = res.data.order.order_item_description;
                        this.form.part_no = res.data.order.order_part_number;
                    } else this.hideControlNumber = true;
                    this.form.customer_details.id = res.data.quote.client_id;
                    if(this.isEdit == true) this.selectOrder(this.form.order.id);
                    this.selectCSQuantityUnit();
                })
                .catch((error) => {
                    console.log(error)
                })
            },
            getOrders(){
                axios.get('/api/get-orders/'+this.form.quote.client_id)
                .then((res) => {
                    this.orders = res.data;
                    this.getClientPIC(this.form.quote.client_id);
                })
                .catch((error) => {
                    this.orders = []
                }); 
            },
            // edited
            editQuote(){
                this.isEdit = true;
                this.selectOrder(this.form.order.id);
            },
            selectOrder(order_id){
                console.log('AWWW')
                axios.get('/api/get-quotation-list/'+order_id, {
                    params:{
                        quote_id: this.$route.params.quotation_id,
                        client_id: this.form.quotation.client_id,
                    }
                })
                .then((res) => {
                    this.form.csTable = res.data;
                    for (let i = 0; i < this.form.csTable.length; i++) {
                        this.originalCNTable.push({
                            id: this.form.csTable[i].id,
                            quantity: this.form.csTable[i].cs_qty
                        })
                    }
                    this.changePerQuantity();
                })
                .catch((error) => {
                    console.log(error)
                });
            },
            updateQuotation() {
                Swal.fire({
                    text: 'Are you sure you want to update this quotation?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Update',
                    width: '25rem',
                })
                .then((result) => {
                    if(result.value){
                        this.isLoading = true;
                        this.form.put('/api/quotation/'+this.$route.params.quotation_id)
                        this.evaluationForm.evaluation_remarks = this.evaluation_remarks;
                        this.evaluationForm.put('/api/update-evaluation-status/'+this.$route.params.quotation_id)
                        .then((res) => {
                            Swal.fire(
                                'Updated!',
                                'Quotation successfully updated.',
                                'success'
                            )
                            this.form.added_quotation = [];
                            this.getQuotation();
                            this.isEdit = false;
                            
                        })
                        .catch(error => {
                            console.log(error)
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
            exportPDF(){
                var quotation_id = this.$route.params.quotaion_id;
                window.open('/export-pdf-quotation?quotation_id='+quotation_id);
            },
            toEdit(){
                this.isEdit = true;
            },
            openAddCS(){
                let id = '';
                id = this.form.contactPersons[this.form.contactPersons.length - 1].id + 1;
                this.form.contactPersons.push({ 'id': id, 'contact_name': '', 'contact_email': '', 'deleted_at': '' });
            },
            deleteContactPerson(id){
                for(let i = 0; i < this.form.contactPersons.length; i++){
                    if(this.form.contactPersons[i].id == id) this.form.contactPersons[i].deleted_at = true;
                }
            },
            clientLabel(name, short_name){
                return '(' + short_name + ') ' + name;
            },
            amountLabel(){
                let $currency = '';
                if(this.form.currency) {
                    $currency = this.form.currency;
                    return 'Amount' + ' ('+$currency+')';
                }else return 'Amount';
            },
            totalInitialCharge(id, quantity, price, index){
                this.form.initial_charge[index].item_total_amt = parseFloat(quantity * price).toFixed(4);
                return parseFloat(quantity * price).toFixed(4);
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
            removeInitialCharge(){
                this.form.initial_charge.splice(this.form.initial_charge.length - 1, 1);
            },
            initialChargeTotal(){
                this.form.initial_charge.initial_charge_amount = parseInt(this.form.initial_charge.initial_charge_qty 
                * this.form.initial_charge.initial_charge_unit_price).toFixed(4);
                if(isNaN(this.form.initial_charge.initial_charge_qty)) this.form.initial_charge.initial_charge_amount = 0;
                if(isNaN(this.form.initial_charge.initial_charge_unit_price)) this.form.initial_charge.initial_charge_amount = 0;
            },
            
            clearTable(){
                this.form.form.csTable = [];
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
            removeQuotation(){
                this.form.added_quotation.splice(this.form.added_quotation.length - 1, 1);
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
            selectCustomer(id){
                this.form.part_no = '';
                this.form.product_name = '';
                this.form.csTable = [];
                this.showInitialCharge = true;
                this.quote.remarks = '';
                axios.get('/api/get-orders/'+id)
                .then((res) => {
                    this.orders = res.data;
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
            createQuotation(){
                Swal.fire({
                    text: 'Are you sure you want to copy new quotation',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Register',
                    width: '25rem',
                })
                .then((result) => {
                    if(result.value){
                        this.form.post('/api/copy-quotation')
                        .then((res) => {
                            Swal.fire(
                                'Registered!',
                                'Copy successfully registered.',
                                'success'
                            )
                        })
                        .catch(error => {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops!',
                                text: 'Copy registration failed.',
                            })
                            console.log(error)
                        });
                    }
                })
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

                            let id = 1;
                            if(this.form.checkers.length > 0){
                                id = this.form.checkers[this.form.checkers.length - 1].id + 1;
                            }

                            this.form.checkers.push({
                                id: id,
                                evaluator_id: selected_checker.id,
                                first_name: selected_checker.first_name,
                                last_name: selected_checker.last_name,
                                evaluation_type: 1,
                                evaluator_remarks: '',
                                evaluator_permission: 1,
                                evaluator_name: selected_checker.first_name+ ' ' +selected_checker.last_name,
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

                            let id = 1;
                            if(this.form.checkers.length > 0){
                                id = this.form.checkers[this.form.checkers.length - 1].id + 1;
                            }

                            this.form.approvers.push({
                                id: id,
                                evaluator_id: selected_approver.id,
                                first_name: selected_approver.first_name,
                                last_name: selected_approver.last_name,
                                evaluation_type: 2,
                                evaluator_remarks: '',
                                evaluator_permission: 2,
                                evaluator_name: selected_approver.first_name+ ' ' +selected_approver.last_name,
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
            deleteEvaluator(evaluator_id, type){
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
            evaluationStatus(evaluation_status){
                return _.find(this.$evaluation_status, { 'id': evaluation_status }).evaluation_status;
            },
            evaluationStatusIcon(evaluation_status){
                if(evaluation_status == 1 || evaluation_status == 3) return 'el-icon-circle-check';
                else if(evaluation_status == 2 || evaluation_status == 4) return 'el-icon-success';
            },
            evaluatorType(type){
                let evaluator_type = 'Checker';
                if(type == 2) evaluator_type = 'Approver';
                return evaluator_type;
            },
            evaluatorNameFormat(first_name, last_name){
                return name = first_name + ' ' + last_name;
            },
            dateFormat(date){
                return moment(date).format('MM/DD/YYYY HH:mm');
            },
            isEvaluated(status){
                if(status == 1 || status == 3 || status == 5) return true;
                else return false;
            },
            
            rejectCSEvaluation(){
                let process = 'reject';
                if(this.evaluationForm.is_rejected == true) process = 'undo';
                Swal.fire({
                    text: 'Are you sure you want to '+process+' Quotation?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: process.charAt(0).toUpperCase() + process.slice(1),
                    width: '25rem',
                })
                .then((result) => {
                    if(result.value){
                        this.evaluationForm.put('/api/reject-cs-evaluation')
                        .then((res) => {
                            this.getQuotation();
                            
                            Swal.fire(
                                'Completed!',
                                'Action successfully completed.',
                                'success'
                            )
                        })
                        .catch((error) => {
                            console.log(error)
                            Swal.fire(
                                'Error!',
                                'Evaluation error encountered.',
                                'error'
                            )
                        });
                    }
                });
            },
            evaluateQuote(){
                Swal.fire({
                    text: 'Are you sure you want to evaluate Quotation?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Evaluate',
                    width: '25rem',
                })
                .then((result) => {
                    if(result.value){
                        this.evaluationForm.put('/api/evaluate-quote')
                        .then((res) => {
                            this.getQuotation();

                            Swal.fire(
                                'Evaluated!',
                                'Evaluation successfully completed.',
                                'success'
                            )
                        })
                        .catch((error) => {
                            console.log(error)
                            Swal.fire(
                                'Error!',
                                'Evaluation error encountered.',
                                'error'
                            )
                        });
                    }
                });
            },
           
            openAddCS(){
                window.open('/add-cs');
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
            getClientPIC(id){
                axios.get('/api/get-client-pic/'+id)
                .then((res) => {
                    this.clientPIC = res.data;
                })
                .catch((error) => {
                    console.log(error)
                })
            },
             selectCSQuantityUnit(){	
                for (let i = 0; i < this.$units_quantity.length; i++) {	
                    if(this.$units_quantity[i].id == this.form.cs_qty_unit) this.cs_qty_unit_string = this.$units_quantity[i].unit_name;	
                }	
            },
            changePerQuantity () {	
                let cs_pieces_per_qty_unit = 1	
                if (this.form.cs_pieces_per_qty_unit > 1) cs_pieces_per_qty_unit = this.form.cs_pieces_per_qty_unit	
                for (let i = 0; i < this.form.csTable.length; i++) {	
                    let original_object = _.find(this.originalCNTable, { 'id': this.form.csTable[i].id })	
                    this.form.csTable[i].cs_qty = original_object.quantity / cs_pieces_per_qty_unit	
                }		
            }
            
        },
        created() {
            this.getQuotation();
            this.getCustomers();
            this.getEvaluators();
            this.pageType();
        },
        computed: {
            readonly(){
                if(!this.isEdit) return true;
            },
            includesRejected(){
                let checkers_array = _.map(this.form.checkers, 'evaluation_status');
                let approvers_array = _.map(this.form.approvers, 'evaluation_status');
                
                if(checkers_array.includes(5) || approvers_array.includes(5)) return true;
                else return false;
            },
             preventCheckDiscarding(){
                let approvers_array = _.map(this.form.approvers, 'evaluation_status');

                if(approvers_array.includes(4) && this.evaluationForm.evaluation_type == 1) return true;
                else return false;
            },
            preventApproval(){
                let approvers_array = _.map(this.form.approvers, 'evaluation_status');
                let checkers_array = _.map(this.form.checkers, 'evaluation_status');

                if(!checkers_array.includes(2) && this.evaluationForm.evaluation_status == 2) return true;
                else if(approvers_array.includes(4) && this.evaluationForm.evaluation_status == 2) return true;
                else return false;
            },
            isChecked(){
                let checkers_array = _.map(this.form.checkers, 'evaluation_status');
                
                if(checkers_array.includes(2) && this.evaluationForm.evaluation_status == 1) return true;
                else return false;
            },

            isApproved(){
                let approvers_array = _.map(this.form.approvers, 'evaluation_status');

                if(approvers_array.includes(4) && this.evaluationForm.evaluation_status == 3) return true;
                else return false;
            },
            
        }
    }
</script>
