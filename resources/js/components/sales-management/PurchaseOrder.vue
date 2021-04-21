<template>
    <div class="container">
        <div class="content-header mb-3" style="border-bottom: 1px solid black;">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h4 class="m-0 text-dark" v-show="isEdit"><strong>Edit Purchase Order</strong></h4>
                        <h4 class="m-0 text-dark" v-show="!isEdit"><strong>View Purchase Order</strong></h4>
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
                <form @submit.prevent="updateOrder()">
                    <el-card class="box-card" shadow="hover">
                        <div slot="header">
                            <span>Purchase Order</span>
                        </div>
                        <el-form ref="form" :model="form" label-width="15%" :label-position="`right`">
                            <el-form-item label="PO Number">
                                <el-col :span="8">
                                    <el-input v-model="form.purchase_order.client_po_number" :disabled="!isEdit"></el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Customer Name">
                                <el-col :span="5">
                                   <el-select v-model="form.purchase_order.client_id" placeholder="Customer Number" class="block" @change="selectCustomer" :remote="true" :disabled="!isEdit">
                                        <el-option
                                        v-for="client in clients"
                                        :key="client.id"
                                        :label="client.client_number"
                                        :value="client.id">
                                        </el-option>
                                    </el-select>
                                </el-col>
                                <el-col :span="12">
                                     <el-select v-model="form.purchase_order.client_id" placeholder="Customer Name" class="block" @change="selectCustomer" :remote="true" :disabled="!isEdit">
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
                                :disabled="!isEdit"
                                value-format="yyyy-MM-dd"
                                v-model="form.purchase_order.client_po_date"
                                type="datetime"
                                placeholder="Select date">
                                </el-date-picker>
                            </el-form-item>
                            <el-form-item label="Receive Date">
                                <el-date-picker
                                :disabled="!isEdit"
                                value-format="yyyy-MM-dd"
                                v-model="form.purchase_order.client_po_received_date"
                                type="datetime"
                                placeholder="Select date">
                                </el-date-picker>
                            </el-form-item>
                        </el-form>
                    </el-card>

                    <el-card class="box-card mt-3" shadow="hover">
                        <div slot="header">
                            <span>Quotations</span>
                        </div>
                        <el-form ref="form" :model="form" label-width="15%" :label-position="`right`">
                            <el-form-item label="Part Number">
                                <el-col :span="12">
                                    <el-select v-model="form.part_no" placeholder="Part Number" class="block" @change="selectOrder" :remote="true" :disabled="!isEdit">
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
                                :disabled="!isEdit"
                                value-format="yyyy-MM-dd"
                                v-model="form.purchase_order.client_po_delivery_date"
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
                                label="Amount"
                                
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
                                v-if="isEdit"
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
                        <el-row v-if="isEdit">
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
                                            <el-input v-model="form.po_qty"></el-input>
                                        </el-col>
                                    </el-form-item>
                                </el-form>
                            </el-col>
                            <el-col :span="5">
                                <el-form :label-position="`right`">
                                    <el-form-item label="Unit">
                                        <el-col :span="12">
                                            <el-input v-model="form.po_unit"></el-input>
                                        </el-col>
                                    </el-form-item>
                                </el-form>
                            </el-col>
                            <el-col :span="5">
                                <el-form :label-position="`right`">
                                    <el-form-item label="Unit Price">
                                        <el-col :span="12">
                                            <el-input v-model="form.po_unit_price"></el-input>
                                        </el-col>
                                    </el-form-item>
                                </el-form>
                            </el-col>
                            <el-col :span="5">
                                <el-form :label-position="`right`">
                                    <el-form-item label="Total">
                                        <el-col :span="12">
                                            <el-input v-model="form.po_total"></el-input>
                                        </el-col>
                                    </el-form-item>
                                </el-form>
                            </el-col>
                        </el-row>
                        <el-row v-if="isEdit">
                            <el-col :span="24" class="text-center mb-3">
                                <button type="button" class="btn button-standard ml-3" size="mini" @click="addContactPerson">
                                    <i class="el-icon-plus"></i> Save quotation to list
                                </button>
                            </el-col>
                        </el-row>
                        <hr>
                        <h5>Quotation List</h5>
                       <el-table
                            :data="form.quotations"
                            ref="multipleTable"
                            style="width: 100%">
                            <el-table-column
                                v-if="this.form.part_no !=  undefined"
                                prop="cs_control_number"
                                label="Control Number">
                            </el-table-column>
                            <el-table-column
                                property="client_po_item_qty"
                                label="Quantity">
                            </el-table-column>
                            <el-table-column
                                property="client_po_item_unit_amount"
                                label="Unit Price"
                                show-overflow-tooltip>
                                <template slot-scope="scope">
                                    <el-col>
                                        {{ form.quotations[scope.$index].client_po_item_unit_amount+'/'+unitLabel(form.quotations[scope.$index].client_po_item_qty_unit) }}
                                    </el-col>
                                </template>
                            </el-table-column>
                            <el-table-column
                                property="client_po_item_total_amount"
                                :label="amountLabel()"
                                show-overflow-tooltip>
                                <template slot-scope="scope">
                                    <el-col :span="12">
                                        {{ form.quotations[scope.$index].client_po_item_currency }}
                                    </el-col>
                                    <el-col :span="12">
                                        {{ form.quotations[scope.$index].client_po_item_total_amount }}
                                    </el-col>
                                </template>
                            </el-table-column>
                            <el-table-column
                                v-if="isEdit"
                                label="Action">
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
                                <span>{{ form.purchase_order.client_po_amount_currency+' '+form.purchase_order.client_po_amount}}</span>
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
                                    :disabled="!isEdit"
                                    v-model="form.purchase_order.remarks" 
                                    type="textarea" 
                                    resize="none"
                                    :autosize="{ minRows: 4, maxRows: 6}">
                                    </el-input>
                                </el-col>
                            </el-form-item>
                        </el-form>
                    </el-card>

                    <div class="outside-card-button" v-if="isEdit">
                        <el-button type="primary" v-on:click="updatePO"><i class="el-icon-circle-check"></i> Update</el-button>
                        <el-button type="warning" v-on:click="resetFields"><i class="fas fa-file-pdf"></i> Preview</el-button>
                        <el-button type="danger" @click="$router.back(-1)"><i class="el-icon-close"></i> Cancel</el-button>
                    </div>
                    <div class="outside-card-button" v-else>
                        <el-button type="primary" @click="toEdit"><i class="el-icon-edit-outline"></i> Edit</el-button>
                        <router-link :to="'/purchase-orders'">
                            <el-button type="danger"><i class="el-icon-arrow-left"></i> Lists</el-button>
                        </router-link>
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
    export default {
        data(){
            return {
                form: new Form({
                    purchase_order: [],
                    csTable: [],
                    part_no: '',
                    product_des: '',
                }),
                clients: [],
                orders: [],
                isEdit: false,
                isLoading: false,
                units_quantity: [
                    { 'id': 1, 'unit_name': 'pcs' },
                    { 'id': 2, 'unit_name': 'box' },
                    { 'id': 3, 'unit_name': 'roll' },
                    { 'id': 4, 'unit_name': 'sheet' },
                ],
            }
        },
        methods: {
            getPO(){ 
               axios.get('/api/purchase-order/'+this.$route.params.po_id)
               .then((res) => {
                    this.form.purchase_order = res.data.po;
                    this.form.client_id = res.data.po.client_id;
                    this.selectCustomer(res.data.po.client_id);
                    this.selectOrder(res.data.po.client_order_id);
                    this.form.quotations = res.data.po_items;
                    
                })
                .catch((error) => {
                    console.log(error)
                })
                for (let i = 0; i < this.orders.length; i++){
                        if(this.orders[i].id == this.form.purchase_order.client_order_id )
                            this.form.product_des = this.orders[i].order_item_description;
                    }
            },
             updatePO() {
                Swal.fire({
                    text: 'Are you sure you want to update this purchase-order?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Update',
                    width: '25rem',
                })
                .then((result) => {
                    if(result.value){
                        this.isLoading = true;
                        this.form.put('/api/purchase-order/'+this.$route.params.po_id)
                        .then((res) => {
                            Swal.fire(
                                'Updated!',
                                'PO successfully updated.',
                                'success'
                            )
                            this.getPO();
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
                this.getSupplier();
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
                var supplier_id = this.$route.params.supplier_id;
                window.open('/export-pdf-supplier?supplier_id='+supplier_id);
            },
            toEdit(){
                this.isEdit = true;
            },
            addContactPerson(){
                let id = '';
                id = this.form.contactPersons[this.form.contactPersons.length - 1].id + 1;
                this.form.contactPersons.push({ 'id': id, 'contact_name': '', 'contact_email': '', 'deleted_at': '' });
            },
            deleteContactPerson(id){
                for(let i = 0; i < this.form.contactPersons.length; i++){
                    if(this.form.contactPersons[i].id == id) this.form.contactPersons[i].deleted_at = true;
                }
            },
            addPrimaryToList(){

            },
            selectCustomer(client_id){
                this.orders = [];
                this.form.product_des = '';
                this.form.part_no = '';
                this.form.csTable = [];
                axios.get('/api/get-orders/'+client_id)
                .then((res) => {
                    this.orders = res.data;
                    
                    for (let i = 0; i < this.orders.length; i++) {
                        if(this.orders[i].id == this.form.purchase_order.client_order_id){
                        this.form.part_no = this.orders[i].order_part_number;
                        this.form.product_des = this.orders[i].order_item_description;
                        }
                    }
                    
                })
                .catch((error) => {
                    console.log(error)
                });
                
            },
            selectOrder(order_id){
                console.log(order_id)
                for (let i = 0; i < this.orders.length; i++){
                    if(order_id == this.orders[i].id){
                        this.form.part_no = this.orders[i].order_part_number;
                        this.form.product_des = this.orders[i].order_item_description;
                    }
                }
                axios.post('/api/order-was-selected/'+this.form.client_id)
                .then((res) => {
                    this.form.csTable = res.data;
                   
                })
                .catch((error) => {
                    console.log(error)
                });
            },
            amountLabel(){
                // let $currency = '';
                // if(this.form.quotation.currency) {
                //     $currency = this.form.quotation.currency;
                //     return 'Amount' + '('+$currency+')';
                // }else return 'Amount';
                return 'Amount';
            },
            removeQuotation(){
                this.form.quotations.splice(this.form.quotations.length - 1, 1);
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
            clientLabel(name, short_name){
                return '(' + short_name + ') ' + name;
            },
            unitLabel(unit){
                for (let i = 0; i < this.units_quantity.length; i++) {
                    if(this.units_quantity[i].id == unit) return this.units_quantity[i].unit_name;
                }
            }
        },
        created() {
            this.pageType();
            // this.initializePO();
            this.getPO();
            this.getCustomers();
        },
        computed: {
            readonly(){
                if(!this.isEdit) return true;
            },
        }
    }
</script>
