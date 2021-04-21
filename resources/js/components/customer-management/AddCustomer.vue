<template>
    <div class="container">
        <div class="content-header mb-3" style="border-bottom: 1px solid black;">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h4 class="m-0 text-dark"><strong>Add Customer</strong></h4>
                    </div>
                    <div class="col-sm-6">
                        <button type="button" class="btn button-standard float-right" @click="$router.push('/customer-management')">
                            <i class="el-icon-arrow-left"></i> Lists
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-12">
                <form @submit.prevent="createCustomer()">
                    <el-card class="box-card mt-3" shadow="hover">
                        <div slot="header">
                            <span>Customer Information</span>
                        </div>

                        <el-form ref="form" :model="form" label-width="15%" :label-position="`right`">
                            <el-form-item label="Customer Name">
                                <el-col :span="12">
                                    <el-input v-model="form.formClient.client_name" @input="form.formClient.client_name = $event.charAt(0).toUpperCase() + $event.slice(1)" :disabled="clientReadonly"></el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Short Name">
                                <el-col :span="6">
                                    <el-input v-model="form.formClient.client_short_name" @input="form.formClient.client_short_name = $event.toUpperCase()" :disabled="clientReadonly"></el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Address">
                                <el-col :span="6">
                                    <el-select v-model="form.formClient.client_province_id" @change="selectClientProvince" placeholder="Province" :disabled="clientReadonly">
                                        <el-option
                                        v-for="client_province in clientProvinces"
                                        :key="client_province.id"
                                        :label="client_province.province_name"
                                        :disabled="clientReadonly"
                                        :value="client_province.id">
                                        </el-option>
                                    </el-select>
                                </el-col>
                                <el-col :span="6">
                                    <el-select v-model="form.formClient.client_municipality_id" placeholder="Municipality" :disabled="clientReadonly">
                                        <el-option
                                        v-for="client_municipality in clientMunicipalities"
                                        :key="client_municipality.id"
                                        :label="client_municipality.municipality_name"
                                        :disabled="clientReadonly"
                                        :value="client_municipality.id">
                                        </el-option>
                                    </el-select>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="">
                                <el-col :span="12">
                                    <el-input 
                                        type="textarea" 
                                        v-model="form.formClient.client_address" 
                                        resize="none"
                                        @input="form.formClient.client_address = $event.charAt(0).toUpperCase() + $event.slice(1)"
                                        :disabled="clientReadonly"
                                        placeholder="Complete address..."
                                        :autosize="{ minRows: 4, maxRows: 6}">
                                    </el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Contact">
                                <el-col :span="6">
                                    <el-input v-model="form.formClient.client_contact" :disabled="clientReadonly"></el-input>
                                </el-col>
                            </el-form-item>

                            <el-form-item label="" v-show="!form.isSameAsCustomerDetails">
                                <el-col :span="24" style="text-align: right">
                                    <button type="button" class="btn button-standard ml-3" size="mini" @click="addClientPIC">
                                        <i class="el-icon-plus"></i> Add client PIC
                                    </button>
                                </el-col>
                            </el-form-item>

                            <div v-for="clientPIC in form.formClient.pic" :key="clientPIC.pic_key">
                                <el-form-item label="PIC">
                                    <el-col :span="2">
                                        <el-select v-model="clientPIC.client_pic_title" placeholder="Title" :disabled="clientReadonly">
                                            <el-option
                                            v-for="title in titles"
                                            :key="title.client_pic_title"
                                            :label="title.title_name"
                                            :disabled="clientReadonly"
                                            :value="title.client_pic_title">
                                            </el-option>
                                        </el-select>
                                    </el-col>
                                    <el-col :span="10">
                                        <el-input v-model="clientPIC.client_pic" @input="clientPIC.client_pic = $event.charAt(0).toUpperCase() + $event.slice(1)" :disabled="clientReadonly"></el-input>
                                    </el-col>

                                    <el-col :span="12" style="text-align: right" v-show="!form.isSameAsCustomerDetails">
                                        <el-button type="danger" icon="el-icon-delete" size="mini" @click="deleteFormClientPIC(clientPIC.pic_key)" v-show="form.formClient.pic.length > 1 && clientPIC.pic_key != 1" circle></el-button>
                                    </el-col>

                                </el-form-item>
                                <el-form-item label="PIC Contact" v-show="!form.isTemporaryCustomer">
                                    <el-col :span="6">
                                        <el-input v-model="clientPIC.client_pic_contact" :disabled="clientReadonly"></el-input>
                                    </el-col>
                                </el-form-item>
                                <el-form-item label="PIC Email" v-show="!form.isTemporaryCustomer">
                                    <el-col :span="6">
                                        <el-input v-model="clientPIC.client_pic_email" type="email" :disabled="clientReadonly"></el-input>
                                    </el-col>
                                </el-form-item>
                                <el-form-item label="PIC Department" v-show="!form.isTemporaryCustomer">
                                    <el-col :span="6">
                                        <el-input v-model="clientPIC.client_pic_department" @input="clientPIC.client_pic_department = $event.charAt(0).toUpperCase() + $event.slice(1)" :disabled="clientReadonly"></el-input>
                                    </el-col>
                                </el-form-item>
                                <hr v-show="form.formClient.pic.length > 1">    
                            </div>
                            <el-form-item label="Payment Terms" v-show="!form.isTemporaryCustomer">
                                <el-col :span="2">
                                    <el-input v-model="form.formClient.client_pay_terms" :disabled="clientReadonly" @input="numberInput"></el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Supply Product" v-show="!form.isTemporaryCustomer">
                                <el-col :span="12">
                                    <el-input 
                                        type="textarea" 
                                        v-model="form.formClient.client_order_description" 
                                        resize="none"
                                        :disabled="clientReadonly"
                                        placeholder=""
                                        :autosize="{ minRows: 4, maxRows: 6}">
                                    </el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="" v-show="!form.isSameAsCustomerDetails">
                                <el-col :span="24">
                                    <el-checkbox v-model="form.isTemporaryCustomer" @change="temporaryCustomer">Save as temporary customer</el-checkbox>
                                </el-col>
                            </el-form-item>
                        </el-form>
                    </el-card>

                    <el-card class="box-card mt-3" shadow="hover" v-show="!form.isTemporaryCustomer">
                        <div slot="header">
                            <span>Billing Information</span>
                        </div>
                        <el-form ref="form" :model="form" label-width="15%" :label-position="`right`">
                            <el-form-item label="">
                                <el-col :span="24" style="text-align: right">
                                    <el-checkbox v-model="form.isSameAsCustomerDetails" @change="sameAsCustomerDetails" :disabled="billingReadonly" >Same as customer details</el-checkbox>
                                    <button type="button" @click="makeCustomerConsignee" class="btn button-standard ml-3" size="mini" v-show="!this.form.isSameAsCustomerDetails" v-if="!form.isCustomerConsignee">
                                        <i class="el-icon-key"></i> Make customer consignee
                                    </button>
                                    <button type="button" @click="deleteCustomerConsignee" class="btn button-standard ml-3" size="mini" v-show="!this.form.isSameAsCustomerDetails" v-else>
                                        <i class="el-icon-success"></i> Authorized consignee
                                    </button>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Customer Name">
                                <el-col :span="12">
                                    <el-input v-model="form.formBilling.client_name" @input="form.formBilling.client_name = $event.charAt(0).toUpperCase() + $event.slice(1)" :disabled="clientReadonly"></el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Billing Address">
                                <el-col :span="6">
                                    <el-select v-model="form.formBilling.client_province_id" @change="selectBillingProvince" placeholder="Province" :disabled="clientReadonly">
                                        <el-option
                                        v-for="billing_province in billingProvinces"
                                        :key="billing_province.id"
                                        :label="billing_province.province_name"
                                        :disabled="clientReadonly"
                                        :value="billing_province.id">
                                        </el-option>
                                    </el-select>
                                </el-col>
                                <el-col :span="6">
                                    <el-select v-model="form.formBilling.client_municipality_id" placeholder="Municipality" :disabled="clientReadonly">
                                        <el-option
                                        v-for="billing_municipality in billingMunicipalities"
                                        :key="billing_municipality.id"
                                        :label="billing_municipality.municipality_name"
                                        :disabled="clientReadonly"
                                        :value="billing_municipality.id">
                                        </el-option>
                                    </el-select>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="">
                                <el-col :span="12">
                                    <el-input 
                                        type="textarea" 
                                        v-model="form.formBilling.client_address" 
                                        resize="none"
                                        @input="form.formBilling.client_address = $event.charAt(0).toUpperCase() + $event.slice(1)"
                                        :disabled="clientReadonly"
                                        placeholder="Complete address..."
                                        :autosize="{ minRows: 4, maxRows: 6}">
                                    </el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Contact">
                                <el-col :span="6">
                                    <el-input v-model="form.formBilling.client_contact" :disabled="clientReadonly"></el-input>
                                </el-col>
                            </el-form-item>

                            <el-form-item label="">
                                <el-col :span="24" style="text-align: right">
                                    <button type="button" class="btn button-standard ml-3" size="mini" @click="addBillingPIC" v-show="!form.isSameAsCustomerDetails">
                                        <i class="el-icon-plus"></i> Add billing PIC
                                    </button>
                                </el-col>
                            </el-form-item>

                            <div v-for="billingPIC in form.formBilling.pic" :key="billingPIC.pic_key">
                                <el-form-item label="PIC">
                                    <el-col :span="2">
                                        <el-select v-model="billingPIC.client_pic_title" placeholder="Title" :disabled="clientReadonly">
                                            <el-option
                                            v-for="title in titles"
                                            :key="title.client_pic_title"
                                            :label="title.title_name"
                                            :disabled="clientReadonly"
                                            :value="title.client_pic_title">
                                            </el-option>
                                        </el-select>
                                    </el-col>
                                    <el-col :span="10">
                                        <el-input v-model="billingPIC.client_pic" @input="billingPIC.client_pic = $event.charAt(0).toUpperCase() + $event.slice(1)" :disabled="clientReadonly"></el-input>
                                    </el-col>

                                    <el-col :span="12" style="text-align: right" v-show="!form.isSameAsCustomerDetails">
                                        <el-button type="danger" icon="el-icon-delete" size="mini" @click="deleteFormBillingPIC(billingPIC.pic_key)" v-show="form.formBilling.pic.length > 1 && billingPIC.pic_key != 1" circle></el-button>
                                    </el-col>

                                </el-form-item>
                                <el-form-item label="PIC Contact">
                                    <el-col :span="6">
                                        <el-input v-model="billingPIC.client_pic_contact" :disabled="clientReadonly"></el-input>
                                    </el-col>
                                </el-form-item>
                                <el-form-item label="PIC Email">
                                    <el-col :span="6">
                                        <el-input v-model="billingPIC.client_pic_email" :disabled="clientReadonly"></el-input>
                                    </el-col>
                                </el-form-item>
                                <el-form-item label="PIC Department">
                                    <el-col :span="6">
                                        <el-input v-model="billingPIC.client_pic_department" @input="billingPIC.client_pic_department = $event.charAt(0).toUpperCase() + $event.slice(1)" :disabled="clientReadonly"></el-input>
                                    </el-col>
                                </el-form-item>
                                <hr v-show="form.formBilling.pic.length > 1"> 
                            </div>
                        </el-form>
                    </el-card>

                    <el-card class="box-card mt-3" shadow="hover" v-show="!form.isTemporaryCustomer">
                        <div slot="header">
                            <span>Delivery Information</span>
                        </div>
                        <el-form ref="form" :model="form" label-width="15%" :label-position="`right`">
                            <el-form-item label="">
                                <el-col :span="24" style="text-align: right">
                                    <el-checkbox v-model="form.isSameAsBillingDetails" @change="sameAsBillingDetails">Same as billing details</el-checkbox>
                                    <button type="button" class="btn button-standard ml-3" size="mini" @click="addDeliveryInformation" v-show="!form.isSameAsBillingDetails">
                                        <i class="el-icon-plus"></i> Add delivery details
                                    </button>
                                </el-col>
                            </el-form-item>

                            <div v-for="(formDelivery, index) in this.form.formDelivery" :key="formDelivery.key">
                                <el-form-item label="Customer Name">
                                    <el-col :span="12">
                                        <el-input v-model="formDelivery.client_name" @input="formDelivery.client_name = $event.charAt(0).toUpperCase() + $event.slice(1)" :disabled="billingReadonly"></el-input>
                                    </el-col>
                                    <el-col :span="12" style="text-align: right" v-show="!form.isSameAsBillingDetails">
                                        <el-button type="danger" icon="el-icon-delete" size="mini" @click="deleteFormDelivery(formDelivery.key)" v-show="form.formDelivery.length > 1 && formDelivery.key != 1" circle></el-button>
                                    </el-col>
                                </el-form-item>
                                <el-form-item label="Delivery Address">
                                    <el-col :span="6">
                                        <el-select v-model="formDelivery.client_province_id" @change="selectDeliveryProvince(formDelivery.client_province_id, formDelivery.key)" placeholder="Province" :disabled="billingReadonly">
                                            <el-option
                                            v-for="delivery_province in deliveryProvinces"
                                            :key="delivery_province.id"
                                            :label="delivery_province.province_name"
                                            :disabled="billingReadonly"
                                            :value="delivery_province.id">
                                            </el-option>
                                        </el-select>
                                    </el-col>
                                    <el-col :span="6">
                                        <el-select v-model="formDelivery.client_municipality_id" placeholder="Municipality" :disabled="billingReadonly">
                                            <el-option
                                            v-for="delivery_municipality in customMunicipality[index]"
                                            :key="delivery_municipality.id"
                                            :label="delivery_municipality.municipality_name"
                                            :disabled="billingReadonly"
                                            :value="delivery_municipality.id">
                                            </el-option>
                                        </el-select>
                                    </el-col>
                                </el-form-item>
                                <el-form-item label="">
                                    <el-col :span="12">
                                        <el-input 
                                            type="textarea" 
                                            v-model="formDelivery.client_address" 
                                            resize="none"
                                            @input="formDelivery.client_address = $event.charAt(0).toUpperCase() + $event.slice(1)"
                                            :disabled="billingReadonly"
                                            placeholder="Complete address..."
                                            :autosize="{ minRows: 4, maxRows: 6}">
                                        </el-input>
                                    </el-col>
                                </el-form-item>
                                <el-form-item label="Contact">
                                    <el-col :span="6">
                                        <el-input v-model="formDelivery.client_contact" :disabled="billingReadonly"></el-input>
                                    </el-col>
                                </el-form-item>

                                <el-form-item label="" v-show="!form.isSameAsBillingDetails">
                                    <el-col :span="24" style="text-align: right">
                                        <button type="button" class="btn button-standard ml-3" size="mini" @click="addDeliveryPIC(formDelivery.key)">
                                            <i class="el-icon-plus"></i> Add delivery PIC
                                        </button>
                                    </el-col>
                                </el-form-item>

                                <div v-for="deliveryPIC in formDelivery.pic" :key="deliveryPIC.pic_key">
                                    <el-form-item label="PIC">
                                        <el-col :span="2">
                                            <el-select v-model="deliveryPIC.client_pic_title" placeholder="Title" :disabled="billingReadonly">
                                                <el-option
                                                v-for="title in titles"
                                                :key="title.client_pic_title"
                                                :label="title.title_name"
                                                :disabled="billingReadonly"
                                                :value="title.client_pic_title">
                                                </el-option>
                                            </el-select>
                                        </el-col>
                                        <el-col :span="10">
                                            <el-input v-model="deliveryPIC.client_pic" @input="deliveryPIC.client_pic = $event.charAt(0).toUpperCase() + $event.slice(1)" :disabled="billingReadonly"></el-input>
                                        </el-col>

                                        <el-col :span="12" style="text-align: right" v-show="!form.isSameAsBillingDetails">
                                            <el-button type="danger" icon="el-icon-delete" size="mini" @click="deleteFormDeliveryPIC(deliveryPIC.pic_key, formDelivery.key)" v-show="formDelivery.pic.length > 1 && deliveryPIC.pic_key != 1" circle></el-button>
                                        </el-col>

                                    </el-form-item>
                                    <el-form-item label="PIC Contact">
                                        <el-col :span="6">
                                            <el-input v-model="deliveryPIC.client_pic_contact" :disabled="billingReadonly"></el-input>
                                        </el-col>
                                    </el-form-item>
                                    <el-form-item label="PIC Email">
                                        <el-col :span="6">
                                            <el-input v-model="deliveryPIC.client_pic_email" :disabled="billingReadonly"></el-input>
                                        </el-col>
                                    </el-form-item>
                                    <el-form-item label="PIC Department">
                                        <el-col :span="6">
                                            <el-input v-model="deliveryPIC.client_pic_department" @input="deliveryPIC.client_pic_department = $event.charAt(0).toUpperCase() + $event.slice(1)" :disabled="billingReadonly"></el-input>
                                        </el-col>
                                    </el-form-item>
                                </div>
                                <hr v-show="form.formDelivery.length > 1">
                            </div>
                        </el-form>
                    </el-card>
                    <div class="outside-card-button">
                        <el-button type="primary" v-on:click="createCustomer" v-if="!isLoading"><i class="el-icon-plus"></i> Create</el-button>
                        <el-button type="primary" v-on:click="createCustomer" :loading="isLoading" v-else> Loading</el-button>
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
                    formClient: { 
                        client_name: '',
                        client_short_name: '',
                        client_province_id: '',
                        client_municipality_id: '',
                        client_name: '',
                        client_address: '',
                        client_contact: '',
                        client_pay_terms: '',
                        key: 1,
                        client_detail_status: 1,
                        client_order_description: '',
                        pic: [{
                            client_pic_title: '',
                            client_pic: '',
                            client_pic_contact: '',
                            client_pic_email: '',
                            client_pic_department: '',
                            pic_key: 1,
                        }],
                    },
                    formBilling: { 
                        client_name: '',
                        client_province_id: '',
                        client_municipality_id: '',
                        client_address: '',
                        client_contact: '',
                        client_detail_status: 4,
                        key: 1,
                        pic: [{
                            client_pic_title: '',
                            client_pic: '',
                            client_pic_contact: '',
                            client_pic_email: '',
                            client_pic_department: '',
                            pic_key: 1,
                        }],
                    },
                    formDelivery: [{ 
                        client_name: '',
                        client_province_id: '',
                        client_municipality_id: '',
                        client_address: '',
                        client_contact: '',
                        client_detail_status: 3,
                        key: 1,
                        pic: [{
                            client_pic_title: '',
                            client_pic: '',
                            client_pic_contact: '',
                            client_pic_email: '',
                            client_pic_department: '',
                            pic_key: 1,
                        }]
                    }],
                    isTemporaryCustomer: false,
                    isSameAsCustomerDetails: false,
                    isSameAsBillingDetails: false,
                    isCustomerConsignee: false,
                }),
                departments: [],
                isLoading: false,
                titles: [
                    { client_pic_title: 1, title_name: 'Mr.' },
                    { client_pic_title: 2, title_name: 'Ms.' },
                ],
                deliveryInformationCounts: [ { key: 1 } ],
                clientDetailsStatus: '',
                isVisibleDeleteFormDelivery: false,
                clientPICCounts: [ { key: 1 } ],
                withClientPICError: false,
                billingPICCounts: [ { key: 1 } ],
                withBillingPICError: false,
                withDeliveryPICError: false,
                deliveryKey: '',
                clientProvinces: [{}],
                clientMunicipalities: [{}],
                isClientProvinceSelected: false,
                billingProvinces: [{}],
                billingMunicipalities: [{}],
                isBillingProvinceSelected: false,
                deliveryProvinces: [{}],
                deliveryMunicipalities: [],
                isDeliveryProvinceSelected: false,
                billing_pic: [],
                delivery_pic: [],
                municipalities: [],
                customMunicipality: [],
            }
        },
        methods: {
            createCustomer(){
                if(this.form.formClient.client_name != this.form.formBilling.client_name && !this.form.isCustomerConsignee && !this.form.isTemporaryCustomer){
                    Swal.fire({
                        position: 'center',
                        icon: 'error',
                        text: 'Customer consignee is required.',
                        showConfirmButton: true,
                        timer: 15000
                    })
                }
                else{
                    if(!this.form.isCustomerConsignee && !this.form.isSameAsBillingDetails){
                        this.form.formBilling.client_detail_status = 2;
                        var index = 0;
                        this.form.formDelivery.forEach(form => {
                            this.form.formDelivery[index].client_detail_status = 3;   
                            index++;
                        });
                    }

                    Swal.fire({
                        text: 'Are you sure you want to register new customer?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Register',
                        width: '25rem',
                    })
                    .then((result) => {
                        if(result.value){
                            this.form.post('api/client')
                            .then((res) => {
                                Swal.fire(
                                    'Registered!',
                                    'Customer successfully registered.',
                                    'success'
                                )
                                this.form.reset();
                                this.isVisibleDeleteFormDelivery = false;
                            })
                            .catch(error => {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Oops!',
                                    text: 'Customer registration failed.',
                                })
                                if(error.response.status == 422){
                                    this.errors = error.response.data.errors || {};
                                }
                            });
                        }
                    })
                }
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
                this.errors.reset();
                this.isVisibleDeleteFormDelivery = false;
            },
            addDeliveryInformation(){
                this.deliveryInformationCounts.push({ 'key' : this.form.formDelivery[this.form.formDelivery.length - 1].key + 1 });
                this.form.formDelivery.push(
                    {
                        "client_name": "",
                        "client_province_id": '',
                        "client_municipality_id": '',
                        "client_address": "",
                        "client_contact": "",
                        "client_detail_status": 3,
                        "key": this.form.formDelivery[this.form.formDelivery.length - 1].key + 1,
                        "pic": [{
                            "client_pic_title": "",
                            "client_pic": "",
                            "client_pic_contact": "",
                            "client_pic_email": "",
                            "client_pic_department": "",
                            "pic_key": 1,
                        }]
                    }
                );
            },
            sameAsCustomerDetails(){
                this.getBillingMunicipalities(this.form.formClient.client_province_id);
                if(this.form.isSameAsCustomerDetails == true){
                    this.form.formBilling = this.form.formClient;
                }
                else{
                    for(var i = 0; i < this.form.formClient.pic.length; i++) {
                        this.billing_pic.push({
                            "client_pic_title": this.form.formClient.pic[i].client_pic_title,
                            "client_pic": this.form.formClient.pic[i].client_pic,
                            "client_pic_contact": this.form.formClient.pic[i].client_pic_contact,
                            "client_pic_email": this.form.formClient.pic[i].client_pic_email,
                            "client_pic_department": this.form.formClient.pic[i].client_pic_department,
                            "pic_key": 1,
                        });
                    }
                    this.form.formBilling = {
                        "client_name": this.form.formClient.client_name,
                        "client_province_id": this.form.formClient.client_province_id,
                        "client_municipality_id": this.form.formClient.client_municipality_id,
                        "client_address": this.form.formClient.client_address,
                        "client_contact": this.form.formClient.client_contact,
                        "client_detail_status": 3,
                        "key": this.form.formDelivery[this.form.formDelivery.length - 1].key + 1,
                        "pic": this.billing_pic,
                    };
                }
            },
            sameAsBillingDetails(){
                this.delivery_pic = [];
                if(this.form.isSameAsBillingDetails == true){
                    for(var i = 0; i < this.form.formBilling.pic.length; i++) {
                        this.delivery_pic.push({
                            "client_pic_title": this.form.formBilling.pic[i].client_pic_title,
                            "client_pic": this.form.formBilling.pic[i].client_pic,
                            "client_pic_contact": this.form.formBilling.pic[i].client_pic_contact,
                            "client_pic_email": this.form.formBilling.pic[i].client_pic_email,
                            "client_pic_department": this.form.formBilling.pic[i].client_pic_department,
                            "pic_key": 1,
                        });
                    }
                    this.form.formDelivery.splice(0, 1);
                    this.form.formDelivery.push({
                        "client_name": this.form.formBilling.client_name,
                        "client_province_id": this.form.formBilling.client_province_id,
                        "client_municipality_id": this.form.formBilling.client_municipality_id,
                        "client_address": this.form.formBilling.client_address,
                        "client_contact": this.form.formBilling.client_contact,
                        "key": 1,
                        "client_detail_status": 1,
                        "pic": this.delivery_pic,
                    })

                    if(!this.form.isCustomerConsignee && this.form.formClient.client_name != this.form.formBilling.client_name){
                        this.form.formBilling.client_detail_status = 2;
                        var index = 0;
                        this.form.formDelivery.forEach(form => {
                            this.form.formDelivery[index].client_detail_status = 3;   
                            index++;
                        });
                    }
                }
                else{
                    for(var i = 0; i < this.form.formBilling.pic.length; i++) {
                        this.delivery_pic.push({
                            "client_pic_title": this.form.formBilling.pic[i].client_pic_title,
                            "client_pic": this.form.formBilling.pic[i].client_pic,
                            "client_pic_contact": this.form.formBilling.pic[i].client_pic_contact,
                            "client_pic_email": this.form.formBilling.pic[i].client_pic_email,
                            "client_pic_department": this.form.formBilling.pic[i].client_pic_department,
                            "pic_key": 1,
                        });
                    }
                    this.form.formBilling = {
                        "client_name": this.form.formBilling.client_name,
                        "client_province_id": this.form.formBilling.client_province_id,
                        "client_municipality_id": this.form.formBilling.client_municipality_id,
                        "client_address": this.form.formBilling.client_address,
                        "client_contact": this.form.formBilling.client_contact,
                        "client_detail_status": 3,
                        "key": this.form.formDelivery[this.form.formDelivery.length - 1].key + 1,
                        "pic": this.delivery_pic,
                    };
                }
            },
            deleteFormDelivery(key){
                for(var i = 0; i < this.form.formDelivery.length; i++){
                    if(this.form.formDelivery[i].key == key){
                        this.form.formDelivery.splice(i, 1);
                    }
                }
                for(var i = 0; i < this.deliveryInformationCounts.length; i++){
                    if(this.deliveryInformationCounts[i].key == key){
                        this.deliveryInformationCounts.splice(i, 1);
                    }
                }
                for(var i = 0; i < this.errors.formDelivery.length; i++){
                    if(this.errors.formDelivery[i].key == key){
                        this.errors.formDelivery.splice(i, 1);
                    }
                }
                this.visibleDeleteFormDelivery();
            },
            visibleDeleteFormDelivery(){
                if(this.form.formDelivery.length > 1){
                    this.isVisibleDeleteFormDelivery = true;
                }
                else{
                    this.isVisibleDeleteFormDelivery = false;
                }
            },
            numberInput(){
                if(this.form.formClient.client_pay_terms) this.form.formClient.client_pay_terms = this.form.formClient.client_pay_terms.replace(/[^0-9]+/g, '');
            },
            temporaryCustomer(){

            },
            makeCustomerConsignee(){
                Swal.fire({
                    html:
                        '<p class="mb-3 red">Administrator authorization required.</p>' +
                        '<input type="text" placeholder="Admin username" class="form-control m-1" id="adminUsername"></input>' +
                        '<input type="password" placeholder="Admin password" class="form-control m-1" id="adminPassword"></input>',
                    inputAttributes: {
                        autocapitalize: 'off'
                    },
                    showCancelButton: true,
                    confirmButtonText: 'Authorize',
                    preConfirm: (data) => {
                        return axios.get('api/admin-validation/?username='+document.getElementById('adminUsername').value+'&password='+document.getElementById('adminPassword').value)
                        .then(response => {
                            if (response.status == 200){
                                console.log('Request authenticated.')
                            }
                            else{
                                throw new Error(response.statusText)
                            }
                        })
                        .catch(error => {
                            Swal.showValidationMessage('Request not authenticated.')
                        })
                    },
                    }).then((result) => {
                    if (result.value) {
                        this.form.isCustomerConsignee = true;
                        var index = 0;
                        this.form.formDelivery.forEach(form => {
                            this.form.formDelivery[index].client_detail_status = 3;
                            index++;
                        });
                        this.form.formBilling.client_detail_status = 4;
                        Swal.fire({
                            position: 'center',
                            icon: 'success',
                            text: 'Authorization request granted.',
                            showConfirmButton: false,
                            timer: 3000
                        })
                    }
                })
            },
            deleteCustomerConsignee(){
                Swal.fire({
                    text: 'Are you sure you want to undo consignee authorization?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Unauthorize',
                    width: '25rem',
                })
                .then((result) => {
                    if(result.value){
                        this.form.isCustomerConsignee = false;
                        Swal.fire({
                            position: 'center',
                            icon: 'success',
                            text: 'Authorization request discarded.',
                            showConfirmButton: false,
                            timer: 3000
                        })
                    }
                })
            },
            addClientPIC(key){
                this.clientPICCounts.push({ 'key' : this.form.formClient.pic[this.form.formClient.pic.length - 1].pic_key + 1 });
                this.form.formClient.pic.push(
                    {
                        "client_pic_title": "",
                        "client_province_id": "",
                        "client_municipality_id": "",
                        "client_pic": "",
                        "client_pic_contact": "",
                        "client_pic_email": "",
                        "client_pic_department": "",
                        "pic_key": this.form.formClient.pic[this.form.formClient.pic.length - 1].pic_key + 1,
                    }
                );
            },
            deleteFormClientPIC(key){
                for(var i = 0; i < this.form.formClient.pic.length; i++){
                    if(this.form.formClient.pic[i].pic_key == key){
                        this.form.formClient.pic.splice(i, 1);
                    }
                }
                for(var i = 0; i < this.clientPICCounts.length; i++){
                    if(this.clientPICCounts[i].key == key){
                        this.clientPICCounts.splice(i, 1);
                    }
                }
            },
            addBillingPIC(){
                this.billingPICCounts.push({ 'key' : this.form.formBilling.pic[this.form.formBilling.pic.length - 1].pic_key + 1 });
                this.form.formBilling.pic.push(
                    {
                        "client_pic_title": "",
                        "client_pic": "",
                        "client_pic_contact": "",
                        "client_pic_email": "",
                        "client_pic_department": "",
                        "pic_key": this.form.formBilling.pic[this.form.formBilling.pic.length - 1].pic_key + 1,
                    }
                );
            },
            deleteFormBillingPIC(key){
                for(var i = 0; i < this.form.formBilling.pic.length; i++){
                    if(this.form.formBilling.pic[i].pic_key == key){
                        this.form.formBilling.pic.splice(i, 1);
                    }
                }
                for(var i = 0; i < this.billingPICCounts.length; i++){
                    if(this.billingPICCounts[i].key == key){
                        this.billingPICCounts.splice(i, 1);
                    }
                }
            },
            addDeliveryPIC(delivery_key){
                var delivery_key = delivery_key - 1;

                this.form.formDelivery[delivery_key].pic.push(
                    {
                        "client_pic_title": "",
                        "client_pic": "",
                        "client_pic_contact": "",
                        "client_pic_email": "",
                        "client_pic_department": "",
                        "pic_key": this.form.formDelivery[delivery_key].pic[this.form.formDelivery[delivery_key].pic.length - 1].pic_key + 1,
                    }
                );
            },
            deleteFormDeliveryPIC(key, index){
                for(var i = 0; i < this.form.formDelivery[index - 1].pic.length; i++){
                    if(this.form.formDelivery[index - 1].pic[i].pic_key == key){
                        this.form.formDelivery[index - 1].pic.splice(i, 1);
                    }
                }
            },
            getProvinces(){
                axios.get('/api/get-provinces')
                .then((res) => {
                    this.clientProvinces = res.data;
                    this.billingProvinces = res.data;
                    this.deliveryProvinces = res.data;
                })
                .catch((error) => {

                });
            },
            selectClientProvince(province_id){
                this.clientMunicipalities = {};
                this.form.formClient.client_municipality_id = '';
                this.getClientMunicipalities(province_id);
                this.isClientProvinceSelected = true;
            },
            getClientMunicipalities(province_id){
                axios.get('/api/get-municipalities?province_id='+province_id)
                .then((res) => {
                    this.clientMunicipalities = res.data;
                })
                .catch((error) => {

                });
            },
            selectBillingProvince(province_id){
                this.billingMunicipalities = {};
                this.form.formBilling.client_municipality_id = '';
                this.getBillingMunicipalities(province_id);
                this.isBillingProvinceSelected = true;
            },
            getBillingMunicipalities(province_id){
                axios.get('/api/get-municipalities?province_id='+province_id)
                .then((res) => {
                    this.billingMunicipalities = res.data;
                })
                .catch((error) => {

                });
            },
            selectDeliveryProvince(province_id, key){
                let index = key - 1;
                this.customMunicipality[index] = _.filter(this.municipalities, ['province_id', province_id]);
                this.isDeliveryProvinceSelected = true;
            },
            getMunicipalities(){
                axios.get('/api/get-municipalities')
                .then(res => {
                    this.municipalities = res.data;
                })
            },
        },
        created(){
            this.getProvinces();
            this.getMunicipalities();
        },
        computed: {
            clientReadonly(){
                if(this.form.isSameAsCustomerDetails) return true;
            },
            billingReadonly(){
                if(this.form.isSameAsBillingDetails) return true;
            },
        }
    }
</script>
