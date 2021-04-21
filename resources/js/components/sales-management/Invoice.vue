<template>
    <div class="container">
        <div class="content-header mb-3" style="border-bottom: 1px solid black;">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h4 class="m-0 text-dark" v-show="isEdit"><strong>Edit Invoice</strong></h4>
                        <h4 class="m-0 text-dark" v-show="!isEdit"><strong>View Invoice</strong></h4>
                    </div>
                    <div class="col-sm-6">
                        <button type="button" class="btn button-standard float-right" @click="$router.push('/invoices')">
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
                            <span>Invoice Details</span>
                        </div>
                        <el-form ref="form" :model="form" label-width="15%" :label-position="`right`">
                            <el-form-item label="SI Number">
                                <el-col :span="8">
                                    <el-input v-model="form.invoice.si_number" :disabled="true"></el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="DR Number">
                                <el-col :span="8">
                                    <el-input v-model="form.dummy_length" :disabled="true"></el-input>
                                </el-col>
                            </el-form-item>
                        </el-form>
                    </el-card>

                    <el-card class="box-card mt-3" shadow="hover">
                        <div slot="header">
                            <span>Customer</span>
                        </div>
                        <h5 class="content-title">Customer Details</h5>
                        <el-form ref="form" :model="form" label-width="15%" :label-position="`right`">
                            <el-form-item label="Customer Name">
                                <el-col :span="12">
                                    <el-input v-model="form.dummy_length" :disabled="true"></el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Invoice Date">
                                <el-col :span="12">
                                    <el-date-picker
                                    v-model="form.date"
                                    type="datetime"
                                    placeholder="Select date">
                                    </el-date-picker>
                                </el-col>
                            </el-form-item>
                            <h5 class="content-title mt-3">Billing Details</h5>
                            <el-form-item label="Company Name">
                                <el-col :span="12">
                                    <el-input v-model="form.dummy_length" :disabled="true"></el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Person-In-Charge">
                                <el-col :span="12">
                                    <el-input v-model="form.dummy_length" :disabled="true"></el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Billing Address">
                                <el-col :span="12">
                                    <el-input 
                                    v-model="form.dummy_data" 
                                    type="textarea" 
                                    resize="none"
                                    :disabled="true"
                                    :autosize="{ minRows: 4, maxRows: 6}">
                                    </el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Contact Number">
                                <el-col :span="12">
                                    <el-input v-model="form.dummy_length" :disabled="true"></el-input>
                                </el-col>
                            </el-form-item>
                            <h5 class="content-title mt-3">Delivery Details</h5>
                            <el-row class="content-title">
                                <el-col :span="5">
                                    <strong>Company Name</strong>
                                </el-col>
                                <el-col :span="5">
                                    <strong>PIC</strong>
                                </el-col>
                                <el-col :span="5">
                                    <strong>Address</strong>
                                </el-col>
                                <el-col :span="5">
                                    <strong>Contact Number</strong>
                                </el-col>
                                <el-col :span="4">
                                    <strong>Action</strong>
                                </el-col>
                            </el-row>
                            <el-row>
                                <el-col :span="5">
                                    <span>Test</span>
                                </el-col>
                                <el-col :span="5">
                                    <span>Test</span>
                                </el-col>
                                <el-col :span="5">
                                    <span>Test</span>
                                </el-col>
                                <el-col :span="5">
                                    <span>Test</span>
                                </el-col>
                                <el-col :span="4">
                                    <i class="el-icon-success"><span class="ml-1">Used</span></i> 
                                </el-col>
                            </el-row>
                            <el-row>
                                <el-col :span="5">
                                    <span>Test</span>
                                </el-col>
                                <el-col :span="5">
                                    <span>Test</span>
                                </el-col>
                                <el-col :span="5">
                                    <span>Test</span>
                                </el-col>
                                <el-col :span="5">
                                    <span>Test</span>
                                </el-col>
                                <el-col :span="4">
                                    <i class="el-icon-circle-check"><span class="ml-1">Used</span></i> 
                                </el-col>
                            </el-row>
                            <el-form-item label="Company Name" class="mt-3">
                                <el-col :span="12">
                                    <el-input v-model="form.dummy_length" :disabled="true"></el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Person-In-Charge">
                                <el-col :span="12">
                                    <el-input v-model="form.dummy_length" :disabled="true"></el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Delivery Address">
                                <el-col :span="12">
                                    <el-input 
                                    v-model="form.dummy_data" 
                                    type="textarea" 
                                    resize="none"
                                    :disabled="true"
                                    :autosize="{ minRows: 4, maxRows: 6}">
                                    </el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Contact Number">
                                <el-col :span="12">
                                    <el-input v-model="form.dummy_length" :disabled="true"></el-input>
                                </el-col>
                            </el-form-item>
                        </el-form>
                    </el-card>

                    <el-card class="box-card mt-3" shadow="hover">
                        <div slot="header">
                            <span>Items</span>
                        </div>
                        <el-form ref="form" :model="form" label-width="15%" :label-position="`right`">
                            <el-form-item label="PO Number">
                                <el-col :span="8">
                                    <el-input v-model="form.dummy_length"></el-input>
                                </el-col>
                                <el-col :span="16">
                                    <button type="button" class="btn button-standard ml-3" size="mini" @click="addPrimaryToList">
                                        <i class="el-icon-search"></i> Search
                                    </button>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Product Name">
                                <el-col :span="8">
                                    <el-select v-model="form.dummy_date" placeholder="Product Name" class="block">
                                        <el-option
                                        v-for="category in $dummy_dropdowns"
                                        :key="category.id"
                                        :label="category.category_name"
                                        :value="category.id">
                                        </el-option>
                                    </el-select>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Reference Number">
                                <el-col :span="8">
                                    <el-select v-model="form.dummy_date" placeholder="Reference Number" class="block">
                                        <el-option
                                        v-for="category in $dummy_dropdowns"
                                        :key="category.id"
                                        :label="category.category_name"
                                        :value="category.id">
                                        </el-option>
                                    </el-select>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Cost">
                                <el-col :span="4" class="text-right">
                                    <span>Quantity</span>
                                </el-col>
                                <el-col :span="4">
                                    <el-input v-model="form.dummy_data" class="ml-1"></el-input>
                                </el-col>
                                <el-col :span="4" class="text-right">
                                    <span>Unit</span>
                                </el-col>
                                <el-col :span="4">
                                    <el-input v-model="form.dummy_data" class="ml-1" value="pcs" :disabled="true"></el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="">
                                <el-col :span="4" class="text-right">
                                    <span>Unit Price</span>
                                </el-col>
                                <el-col :span="4">
                                    <el-input v-model="form.dummy_data" class="ml-1"></el-input>
                                </el-col>
                                <el-col :span="4" class="text-right">
                                    <span>Total</span>
                                </el-col>
                                <el-col :span="4">
                                    <el-input v-model="form.dummy_data" class="ml-1"></el-input>
                                </el-col>
                            </el-form-item>
                        </el-form>
                        <el-col :span="24" style="text-align: center">
                            <button type="button" class="btn button-standard ml-3" size="mini" @click="addPrimaryToList">
                                <i class="el-icon-plus"></i> Save item to list
                            </button>
                        </el-col>

                        <h5 class="mt-3">Item List</h5>
                        <el-row class="content-title">
                            <el-col :span="5">
                                <strong>Item</strong>
                            </el-col>
                            <el-col :span="5">
                                <strong>Quantity</strong>
                            </el-col>
                            <el-col :span="5">
                                <strong>Unit Price</strong>
                            </el-col>
                            <el-col :span="5">
                                <strong>Amount</strong>
                            </el-col>
                            <el-col :span="4">
                                <strong>More Info</strong>
                            </el-col>
                        </el-row>
                        <el-row>
                            <el-col :span="5">
                                <span>Test</span>
                            </el-col>
                            <el-col :span="5">
                                <span>Test</span>
                            </el-col>
                            <el-col :span="5">
                                <span>Test</span>
                            </el-col>
                            <el-col :span="5">
                                <span>Test</span>
                            </el-col>
                            <el-col :span="4">
                                <span>Test</span>
                            </el-col>
                        </el-row>
                        <el-row class="mt-3">
                            <el-col :span="12">
                                <span>Total Amount (Items)</span>
                            </el-col>
                            <el-col :span="12" class="text-right">
                                <span>PHP 4,000.00</span>
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
                                    v-model="form.dummy_data" 
                                    type="textarea" 
                                    resize="none"
                                    :disabled="readonly"
                                    :autosize="{ minRows: 4, maxRows: 6}">
                                    </el-input>
                                </el-col>
                            </el-form-item>
                        </el-form>
                    </el-card>

                    <div class="outside-card-button" v-if="isEdit">
                        <el-button type="primary" v-on:click="updateInvoice"><i class="el-icon-circle-check"></i> Update</el-button>
                        <el-button type="danger" @click="$router.back(-1)"><i class="el-icon-close"></i> Cancel</el-button>
                    </div>
                    <div class="outside-card-button" v-else>
                        <router-link :to="'/update-ss/'+this.$route.params.invoice_id">
                            <el-button type="primary" @click="toEdit"><i class="el-icon-edit-outline"></i> Edit</el-button>
                        </router-link>
                        <router-link :to="'/supplier-management'">
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
                    dummy_data: '',
                    dummy_data_unit: '',
                    dummy_length: '',
                    dummy_length_unit: '',
                    dummy_width: '',
                    dummy_width_unit: '',

                    invoice: {}
                }),
                primaryMaterialList: [
                    { id: 1, name: 'Test', address: 'Test' }
                ],
                checkbox_dummy: true,
                isEdit: false,
                isLoading: false,
            }
        },
        methods: {
            updateInvoice() {
                Swal.fire({
                    text: 'Are you sure you want to update supplier?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Update',
                    width: '25rem',
                })
                .then((result) => {
                    if(result.value){
                        this.isLoading = true;
                        this.form.put('/api/invoice/'+this.$route.params.invoice_id)
                        .then((res) => {
                            Swal.fire(
                                'Updated!',
                                'Supplier successfully updated.',
                                'success'
                            )
                            this.getSupplier();
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
                var invoice_id = this.$route.params.invoice_id;
                window.open('/export-pdf-invoice?invoice_id='+invoice_id);
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
            getInvoice () {
                axios.get('/api/invoice/' + this.$route.params.invoice_id)
                .then((res) => {
                    this.form.invoice = res.data.invoice;
                })
                .catch((error) => {
                    console.log(error)
                });
            }
        },
        created() {
            this.pageType();
            this.getInvoice()
        },
        computed: {
            readonly(){
                if(!this.isEdit) return true;
            },
        }
    }
</script>
