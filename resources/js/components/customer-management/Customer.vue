<template>
    <div class="container">
        <div class="content-header mb-3" style="border-bottom: 1px solid black;">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h4 class="m-0 text-dark" v-show="isEdit"><strong>Edit Customer</strong></h4>
                        <h4 class="m-0 text-dark" v-show="!isEdit"><strong>View Customer</strong></h4>
                    </div>
                    <div class="col-sm-6">
                        <el-col   style="text-align: right">
                            <el-dropdown>
                                <el-button type="primary">
                                    Customer Sales Monitoring <i class="el-icon-arrow-down el-icon--right"></i>
                                </el-button>
                                <el-dropdown-menu slot="dropdown">
                                    <div v-for="dropdown in customerSalesMonitoring" :key="dropdown.id">
                                        <el-dropdown-item>
                                            <router-link :to="'/customer-sales-monitoring/'+dropdown.link+'/client/'+dropdown.client_id">
                                                <p class="dropdown-item-links">{{ dropdown.name }}</p>
                                            </router-link>
                                        </el-dropdown-item>
                                    </div>
                                </el-dropdown-menu>
                            </el-dropdown>
                        </el-col> 
                        <!-- <el-col  :span=8 style="text-align: right">
                            <el-button  type="primary" class="float-right" @click="$router.push('/customer-management')">
                                <i class="el-icon-arrow-left"></i> Lists
                            </el-button>
                        </el-col>  -->
                    </div>
                </div>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-12">
                <form @submit.prevent="updateCustomer()">
                    <el-card class="box-card mt-3" shadow="hover">
                        <div slot="header">
                            <span>Customer Information</span>
                        </div>

                        <el-form ref="form" :model="form" label-width="15%" :label-position="`right`">
                            <el-form-item label="Customer Name">
                                <el-col :span="12">
                                    <el-input v-model="form.formClient.client_name" @input="form.formClient.client_name = $event.charAt(0).toUpperCase() + $event.slice(1)" :disabled="clientDisabled"></el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Customer Code">
                                <el-col :span="12">
                                    <el-input v-model="form.formClient.client_number" :disabled="true"></el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Short Name">
                                <el-col :span="6">
                                    <el-input v-model="form.formClient.client_short_name" @input="form.formClient.client_short_name = $event.toUpperCase()" :disabled="clientDisabled"></el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Address">
                                <el-col :span="6">
                                    <el-select v-model="form.formClient.client_province_id" @change="selectClientProvince" placeholder="Province" :disabled="clientDisabled">
                                        <el-option
                                        v-for="client_province in clientProvinces"
                                        :key="client_province.id"
                                        :label="client_province.province_name"
                                        :disabled="clientDisabled"
                                        :value="client_province.id">
                                        </el-option>
                                    </el-select>
                                </el-col>
                                <el-col :span="6">
                                    <el-select v-model="form.formClient.client_municipality_id" placeholder="Municipality" :disabled="clientDisabled">
                                        <el-option
                                        v-for="client_municipality in clientMunicipalities"
                                        :key="client_municipality.id"
                                        :label="client_municipality.municipality_name"
                                        :disabled="clientDisabled"
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
                                        :disabled="clientDisabled"
                                        :autosize="{ minRows: 4, maxRows: 6}">
                                    </el-input>
                                </el-col>
                            </el-form-item>

                            <el-form-item label="Contact">
                                <el-col :span="6">
                                    <el-input v-model="form.formClient.client_contact" :disabled="clientDisabled"></el-input>
                                </el-col>
                            </el-form-item>

                            <el-form-item label="" v-show="!form.isSameAsCustomerDetails && isEdit">
                                <el-col :span="24" style="text-align: right">
                                    <button type="button" class="btn button-standard ml-3" size="mini" @click="addClientPIC">
                                        <i class="el-icon-plus"></i> Add client PIC
                                    </button>
                                </el-col>
                            </el-form-item>

                            <div v-for="client_pic in form.formClient.pic" :key="client_pic.id" v-show="!client_pic.deleted_at">
                                <el-form-item label="PIC">
                                    <el-col :span="2">
                                        <el-select v-model="client_pic.client_pic_title" placeholder="Title" :disabled="clientDisabled">
                                            <el-option
                                            v-for="title in titles"
                                            :key="title.client_pic_title"
                                            :label="title.title_name"
                                            :disabled="clientDisabled"
                                            :value="title.client_pic_title">
                                            </el-option>
                                        </el-select>
                                    </el-col>
                                    <el-col :span="10">
                                        <el-input v-model="client_pic.client_pic" @input="client_pic.client_pic = $event.charAt(0).toUpperCase() + $event.slice(1)" :disabled="clientDisabled"></el-input>
                                    </el-col>
                                    
                                    <el-col :span="12" style="text-align: right" v-show="!form.isSameAsCustomerDetails && isEdit">
                                        <el-button type="danger" icon="el-icon-delete" size="mini" @click="deleteFormClientPIC(client_pic.id)" v-show="form.formClient.pic.length > 1" circle></el-button>
                                    </el-col>
                                </el-form-item>
                                <el-form-item label="PIC Contact" v-show="!form.isTemporaryCustomer">
                                    <el-col :span="6">
                                        <el-input v-model="client_pic.client_pic_contact" :disabled="clientDisabled"></el-input>
                                    </el-col>
                                </el-form-item>
                                <el-form-item label="PIC Email" v-show="!form.isTemporaryCustomer">
                                    <el-col :span="6">
                                        <el-input v-model="client_pic.client_pic_email" type="email" :disabled="clientDisabled"></el-input>
                                    </el-col>
                                </el-form-item>
                                <el-form-item label="PIC Department" v-show="!form.isTemporaryCustomer">
                                    <el-col :span="6">
                                        <el-input v-model="client_pic.client_pic_department" @input="client_pic.client_pic_department = $event.charAt(0).toUpperCase() + $event.slice(1)" :disabled="clientDisabled"></el-input>
                                    </el-col>
                                </el-form-item>
                            </div>
                            <el-form-item label="Payment Terms" v-show="!form.isTemporaryCustomer">
                                <el-col :span="12">
                                    <el-input v-model="form.formClient.client_pay_terms" :disabled="clientDisabled" @input="numberInput"></el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Supply Product" v-show="!form.isTemporaryCustomer">
                                <el-col :span="12">
                                    <el-input 
                                        type="textarea" 
                                        v-model="form.formClient.client_order_description" 
                                        resize="none"
                                        :disabled="clientDisabled"
                                        placeholder=""
                                        :autosize="{ minRows: 4, maxRows: 6}">
                                    </el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="" v-show="isEdit">
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
                            <el-form-item label="" v-show="isEdit">
                                <el-col :span="24" style="text-align: right">
                                    <el-checkbox v-model="form.isSameAsCustomerDetails" @change="sameAsCustomerDetails" :disabled="!form.isSameAsBillingDetails ? false : true" >Same as customer details</el-checkbox>
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
                                    <el-input v-model="form.formBilling.client_name" @input="form.formBilling.client_name = $event.charAt(0).toUpperCase() + $event.slice(1)" :disabled="clientDisabled"></el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Billing Address">
                                <el-col :span="6">
                                    <el-select v-model="form.formBilling.client_province_id" @change="selectBillingProvince" placeholder="Province" :disabled="clientDisabled">
                                        <el-option
                                        v-for="billing_province in billingProvinces"
                                        :key="billing_province.id"
                                        :label="billing_province.province_name"
                                        :disabled="clientDisabled"
                                        :value="billing_province.id">
                                        </el-option>
                                    </el-select>
                                </el-col>
                                <el-col :span="6">
                                    <el-select v-model="form.formBilling.client_municipality_id" placeholder="Municipality" :disabled="clientDisabled">
                                        <el-option
                                        v-for="billing_municipality in billingMunicipalities"
                                        :key="billing_municipality.id"
                                        :label="billing_municipality.municipality_name"
                                        :disabled="clientDisabled"
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
                                        :disabled="clientDisabled"
                                        :autosize="{ minRows: 4, maxRows: 6}">
                                    </el-input>
                                </el-col>
                            </el-form-item>

                            <el-form-item label="" v-show="!form.isSameAsCustomerDetails && isEdit">
                                <el-col :span="24" style="text-align: right">
                                    <button type="button" class="btn button-standard ml-3" size="mini" @click="addBillingPIC">
                                        <i class="el-icon-plus"></i> Add billing PIC
                                    </button>
                                </el-col>
                            </el-form-item>

                            <div v-for="billing_pic in form.formBilling.pic" :key="billing_pic.id" v-show="!billing_pic.deleted_at">
                                <el-form-item label="PIC">
                                    <el-col :span="2">
                                        <el-select v-model="billing_pic.client_pic_title" placeholder="Title" :disabled="clientDisabled">
                                            <el-option
                                            v-for="title in titles"
                                            :key="title.client_pic_title"
                                            :label="title.title_name"
                                            :disabled="clientDisabled"
                                            :value="title.client_pic_title">
                                            </el-option>
                                        </el-select>
                                    </el-col>
                                    <el-col :span="10">
                                        <el-input v-model="billing_pic.client_pic" @input="billing_pic.client_pic = $event.charAt(0).toUpperCase() + $event.slice(1)" :disabled="clientDisabled"></el-input>
                                    </el-col>

                                    <el-col :span="12" style="text-align: right" v-show="!form.isSameAsCustomerDetails && isEdit">
                                        <el-button type="danger" icon="el-icon-delete" size="mini" @click="deleteFormBillingPIC(billing_pic.id)" v-show="form.formBilling.pic.length > 1" circle></el-button>
                                    </el-col>
                                </el-form-item>
                                <el-form-item label="PIC Contact">
                                    <el-col :span="6">
                                        <el-input v-model="billing_pic.client_pic_contact" :disabled="clientDisabled"></el-input>
                                    </el-col>
                                </el-form-item>
                                <el-form-item label="PIC Email">
                                    <el-col :span="6">
                                        <el-input v-model="billing_pic.client_pic_email" :disabled="clientDisabled"></el-input>
                                    </el-col>
                                </el-form-item>
                                <el-form-item label="PIC Department">
                                    <el-col :span="6">
                                        <el-input v-model="billing_pic.client_pic_department" @input="billing_pic.client_pic_department = $event.charAt(0).toUpperCase() + $event.slice(1)" :disabled="clientDisabled"></el-input>
                                    </el-col>
                                </el-form-item>
                            </div>
                        </el-form>
                    </el-card>

                    <el-card class="box-card mt-3" shadow="hover" v-show="!form.isTemporaryCustomer">
                        <div slot="header">
                            <span>Delivery Information</span>
                        </div>
                        <el-form ref="form" :model="form" label-width="15%" :label-position="`right`">
                            <el-form-item label="" v-show="isEdit">
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
                                        <el-input v-model="formDelivery.client_name" @input="formDelivery.client_name = $event.charAt(0).toUpperCase() + $event.slice(1)" :disabled="billingDisabled"></el-input>
                                    </el-col>
                                    <el-col :span="12" style="text-align: right">
                                        <el-button type="danger" icon="el-icon-delete" size="mini" @click="deleteFormDelivery(formDelivery.key)" v-show="isVisibleDeleteFormDelivery && formDelivery.key != 1 && isEdit" circle></el-button>
                                    </el-col>
                                </el-form-item>
                                <el-form-item label="Delivery Address">
                                    <el-col :span="6">
                                        <el-select v-model="formDelivery.client_province_id" @change="selectDeliveryProvince(formDelivery.client_province_id, formDelivery.key)" placeholder="Province" :disabled="billingDisabled">
                                            <el-option
                                            v-for="delivery_province in deliveryProvinces"
                                            :key="delivery_province.id"
                                            :label="delivery_province.province_name"
                                            :disabled="billingDisabled"
                                            :value="delivery_province.id">
                                            </el-option>
                                        </el-select>
                                    </el-col>
                                    <el-col :span="6">
                                        <el-select v-model="formDelivery.client_municipality_id" placeholder="Municipality" :disabled="billingDisabled">
                                            <el-option
                                            v-for="delivery_municipality in customMunicipality[index]"
                                            :key="delivery_municipality.id"
                                            :label="delivery_municipality.municipality_name"
                                            :disabled="billingDisabled"
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
                                            :disabled="billingDisabled"
                                            :autosize="{ minRows: 4, maxRows: 6}">
                                        </el-input>
                                    </el-col>
                                </el-form-item>

                                <el-form-item label="" v-show="!form.isSameAsBillingDetails && isEdit">
                                    <el-col :span="24" style="text-align: right">
                                        <button type="button" class="btn button-standard ml-3" size="mini" @click="addDeliveryPIC(formDelivery.key)">
                                            <i class="el-icon-plus"></i> Add delivery PIC
                                        </button>
                                    </el-col>
                                </el-form-item>

                                <div v-for="delivery_pic in formDelivery.pic" :key="delivery_pic.id" v-show="!delivery_pic.deleted_at">
                                    <el-form-item label="PIC">
                                        <el-col :span="2">
                                            <el-select v-model="delivery_pic.client_pic_title" placeholder="Title" :disabled="billingDisabled">
                                                <el-option
                                                v-for="title in titles"
                                                :key="title.client_pic_title"
                                                :label="title.title_name"
                                                :disabled="billingDisabled"
                                                :value="title.client_pic_title">
                                                </el-option>
                                            </el-select>
                                        </el-col>
                                        <el-col :span="10">
                                            <el-input v-model="delivery_pic.client_pic" @input="delivery_pic.client_pic = $event.charAt(0).toUpperCase() + $event.slice(1)" :disabled="billingDisabled"></el-input>
                                        </el-col>

                                        <el-col :span="12" style="text-align: right" v-show="!form.isSameAsBillingDetails && isEdit">
                                            <el-button type="danger" icon="el-icon-delete" size="mini" @click="deleteFormDeliveryPIC(delivery_pic.id, formDelivery.key)" v-show="formDelivery.pic.length > 1" circle></el-button>
                                        </el-col>
                                    </el-form-item>
                                    <el-form-item label="PIC Contact">
                                        <el-col :span="6">
                                            <el-input v-model="delivery_pic.client_pic_contact" :disabled="billingDisabled"></el-input>
                                        </el-col>
                                    </el-form-item>
                                    <el-form-item label="PIC Email">
                                        <el-col :span="6">
                                            <el-input v-model="delivery_pic.client_pic_email" :disabled="billingDisabled"></el-input>
                                        </el-col>
                                    </el-form-item>
                                    <el-form-item label="PIC Department">
                                        <el-col :span="6">
                                            <el-input v-model="delivery_pic.client_pic_department" @input="delivery_pic.client_pic_department = $event.charAt(0).toUpperCase() + $event.slice(1)" :disabled="billingDisabled"></el-input>
                                        </el-col>
                                    </el-form-item>
                                </div>
                                <hr>
                            </div>
                        </el-form>
                    </el-card>
                    <div class="outside-card-button" v-if="isEdit">
                        <el-button type="primary" v-on:click="updateCustomer" v-if="!isLoading"><i class="el-icon-circle-check"></i> Update</el-button>
                        <el-button type="primary" v-on:click="updateCustomer" :loading="isLoading" v-else> Loading</el-button>
                        <el-button type="warning" v-on:click="resetFields"><i class="el-icon-refresh-left"></i> Reset</el-button>
                        <el-button type="danger" @click="$router.back(-1)"><i class="el-icon-close"></i> Cancel</el-button>
                    </div>
                    <div class="outside-card-button" v-else>
                        <router-link :to="'/update-client/'+this.$route.params.client_id">
                            <el-button type="primary" @click="toEdit"><i class="el-icon-edit-outline"></i> Edit</el-button>
                        </router-link>
                        <router-link :to="'/customer-management'">
                            <el-button type="danger"><i class="el-icon-arrow-left"></i> Lists</el-button>
                        </router-link>
                    </div>
                </form>
            </div>
            <el-col class="text-center mt-3">
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
                    'formClient': {},
                    'formBilling': {},
                    'formDelivery': [{}],
                    isTemporaryCustomer: false,
                    isSameAsCustomerDetails: false,
                    isSameAsBillingDetails: false,
                    isCustomerConsignee: false,
                }),
                departments: [],
                isEdit: false,
                isLoading: false,
                titles: [
                    { client_pic_title: 1, title_name: 'Mr.' },
                    { client_pic_title: 2, title_name: 'Ms.' },
                ],
                deliveryInformationCounts: [ { key: 1 } ],
                clientDetailsStatus: '',
                isVisibleDeleteFormClientPIC: false,
                isVisibleDeleteFormDelivery: false,
                detailIdBillingIndexZero: 0,
                detailIdDeliveryIndexZero: 0,
                clientPICCounts: [ { key: 1 } ],
                form_client_pic: [],
                clientProvinces: [{}],
                clientMunicipalities: [{}],
                isClientProvinceSelected: false,
                billingProvinces: [{}],
                billingMunicipalities: [{}],
                isBillingProvinceSelected: false,
                deliveryProvinces: [{}],
                deliveryMunicipalities: [{}],
                isDeliveryProvinceSelected: false,
                municipalities: [],
                customMunicipality: [],
                clientAddress: [],
                customerSalesMonitoring:[
                    { id: 1,  name: 'Customer PO',                link: 'purchase-orders'       },
                    { id: 2,  name: 'Customer Orders',            link: 'orders'                },
                    { id: 3,  name: 'Customer DM',                link: 'delivery-monitoring'   },
                    { id: 4,  name: 'Customer CS',                link: 'computation-sheets'    },
                    { id: 5,  name: 'Customer Quotation',         link: 'quotations'    },
                    { id: 6,  name: 'Customer SS',                link: 'sales-sheets'          },
                    { id: 7,  name: 'Customer Customer Forecast', link: 'forecast'              },
                ],
            }
        },
        methods: {
            getCustomer(){
                axios.get('/api/client/'+this.$route.params.client_id)
                .then((res) => {
                    if(res.data.formClient.is_temporary == 0){
                        this.form.formClient = res.data.formClient;
                        this.form.formBilling = res.data.formBilling;
                        this.form.formDelivery = res.data.formDelivery;
                        this.form.isSameAsCustomerDetails = !!res.data.isSameAsCustomerDetails;
                        this.form.isSameAsBillingDetails = !!res.data.isSameAsBillingDetails;
                        this.detailIdBillingIndexZero = res.data.formBilling.detail_id;
                        this.detailIdDeliveryIndexZero = res.data.formDelivery[0].detail_id;
                        this.visibleDeleteFormClientPIC();
                        this.visibleDeleteFormDelivery();
                        this.getClientMunicipalities(res.data.formClient.client_province_id);
                        this.getBillingMunicipalities(res.data.formBilling.client_province_id);
                    }
                    else{
                        this.form.formClient = res.data.formClient;
                        this.form.isTemporaryCustomer = true;
                    }
                    for (let i = 0;i < this.customerSalesMonitoring.length; i++) {
                        this.customerSalesMonitoring[i].client_id = this.$route.params.client_id;
                    }
                })
                .catch(error => {
                    console.log(error)
                });
            },
            updateCustomer(){
                if(this.form.formClient.client_name != this.form.formBilling.client_name && this.form.isCustomerConsignee == false){
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
                    if(this.form.isSameAsBillingDetails && this.form.formDelivery.length == 1){
                        this.form.formBilling.client_detail_status = 1;
                        var index = 0;
                        this.form.formDelivery.forEach(form => {
                            this.form.formDelivery[index].client_detail_status = 1;   
                            index++;
                        });
                    }

                    Swal.fire({
                        text: 'Are you sure you want to update customer?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Update',
                        width: '25rem',
                    })
                    .then((result) => {
                        if(result.value){
                            this.form.patch('/api/client/'+this.$route.params.client_id)
                            .then((res) => {
                                Swal.fire(
                                    'Updated!',
                                    'Customer successfully updated.',
                                    'success'
                                )
                                this.form.reset();
                                // this.errors.reset();
                                this.getCustomer();
                                this.visibleDeleteFormDelivery();
                            })
                            .catch(error => {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Oops!',
                                    text: 'Customer update failed.',
                                })
                                console.log(error)
                                // if(error.response.status == 422){
                                //     this.errors = error.response.data.errors || {};
                                // }
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
                // this.errors.reset();
                this.getCustomer();
                this.isVisibleDeleteFormDelivery = false;
            },
            addDeliveryInformation(){
                this.deliveryInformationCounts.push({ 'key' : this.form.formDelivery[this.form.formDelivery.length - 1].key + 1 });
                this.form.formDelivery.push(
                    {
                        "client_name": "",
                        "client_address": "",
                        "client_detail_status": 3,
                        "detail_id": 1 - this.form.formDelivery.length,
                        "key": this.form.formDelivery[this.form.formDelivery.length - 1].key + 1,
                        "pic": [{
                            "id": 0,
                            "client_pic_title": "",
                            "client_pic": "",
                            "client_pic_contact": "",
                            "client_pic_email": "",
                            "client_pic_department": "",
                            "client_detail_id": parseInt(this.form.formDelivery[this.form.formDelivery.length - 1].pic[0].client_detail_id),
                            "deleted_at": false,
                        }],
                    }
                );
                this.visibleDeleteFormDelivery();
                if(this.form.isSameAsCustomerDetails == true && !this.form.sameAsBillingDetails){
                    this.form.formBilling.client_detail_status = 2;
                    for (var i = 0; i < this.form.formDelivery.length; i++) {
                        this.form.formDelivery[i].client_detail_status = 3;
                    }
                }
            },
            sameAsCustomerDetails(){
                if(this.form.isSameAsCustomerDetails == true){
                    this.form.formBilling = this.form.formClient;
                    // this.errorFormClient();
                }
                else{
                    for(var i = 0; i < this.form.formClient.pic.length; i++) {
                        this.form_client_pic.push({
                            "client_id": this.$route.params.client_id,
                            "client_pic_title": this.form.formClient.pic[i].client_pic_title,
                            "client_pic": this.form.formClient.pic[i].client_pic,
                            "client_pic_contact": this.form.formClient.pic[i].client_pic_contact,
                            "client_pic_email": this.form.formClient.pic[i].client_pic_email,
                            "client_pic_department": this.form.formClient.pic[i].client_pic_department,
                            "pic_key": i + 1,
                            "id": 0 - i,
                        });
                    }
                    this.form.formBilling = {
                        "client_name": this.form.formClient.client_name,
                        "client_address": this.form.formClient.client_address,
                        "key": 1,
                        "client_detail_status": 1,
                        "detail_id": this.form.formClient.detail_id,
                        "pic": this.form_client_pic,
                    }
                }
            },
            sameAsBillingDetails(){
                if(this.form.isSameAsBillingDetails == true){
                    this.form.formDelivery[0] = {
                        "client_name": this.form.formBilling.client_name,
                        "client_address": this.form.formBilling.client_address,
                        "key": 1,
                        "client_detail_status": 1,
                        "detail_id": this.detailIdDeliveryIndexZero,
                        "pic": this.form.formBilling.pic,
                    }
                    if(!this.form.isCustomerConsignee && this.form.formClient.client_name != this.form.formBilling.client_name){
                        this.form.formBilling.client_detail_status = 2;
                        var index = 0;
                        this.form.formDelivery.forEach(form => {
                            this.form.formDelivery[index].client_detail_status = 3;   
                            index++;
                        });
                    }
                }
                if(this.form.isSameAsCustomerDetails == true && !this.form.sameAsBillingDetails){
                    this.form.formBilling.client_detail_status = 2;
                    for (var i = 0; i < this.form.formDelivery.length; i++) {
                        this.form.formDelivery[i].client_detail_status = 3;
                    }
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
            visibleDeleteFormClientPIC(){
                if(this.form.formClient.pic.length > 1){
                    this.isVisibleDeleteFormClientPIC = true;
                }
                else{
                    this.isVisibleDeleteFormClientPIC = false;
                }
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
                this.errors.reset();
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
                        return axios.get('/api/admin-validation/?username='+document.getElementById('adminUsername').value+'&password='+document.getElementById('adminPassword').value)
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
                    })
                    .then((result) => {
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
            pageType(){
                if(window.location.href.indexOf("update") > -1){
                    this.isEdit = true;
                }
                else{
                    this.isEdit = false;
                }
            },
            deliveryPIC(client_pic_title, client_pic){
                if(client_pic_title == 1) return 'Mr. '+client_pic;
                else return 'Ms. '+client_pic;
            },
            exportPDF(){
                var employee_number = this.form.employee_number;
                window.open('/export-pdf-client?client_id='+this.$route.params.client_id);
            },
            addClientPIC(key){
                this.form.formClient.pic.push(
                    {
                        "id": 1 - this.form.formClient.pic.length,
                        "client_pic_title": "",
                        "client_pic": "",
                        "client_pic_contact": "",
                        "client_pic_email": "",
                        "client_pic_department": "",
                        "client_id": parseInt(this.$route.params.client_id),
                        "deleted_at": false,
                    }
                );
            },
            addBillingPIC(key){
                this.form.formBilling.pic.push(
                    {
                        "id": 1 - this.form.formBilling.pic.length,
                        "client_pic_title": "",
                        "client_pic": "",
                        "client_pic_contact": "",
                        "client_pic_email": "",
                        "client_pic_department": "",
                        "client_detail_id": parseInt(this.form.formBilling.pic[0].client_detail_id),
                        "deleted_at": false,
                    }
                );
            },
            addDeliveryPIC(delivery_key){
                var delivery_key = delivery_key - 1;
                this.form.formDelivery[delivery_key].pic.push(
                    {
                        "id": 0 - this.form.formDelivery[delivery_key].pic.length,
                        "client_pic_title": "",
                        "client_pic": "",
                        "client_pic_contact": "",
                        "client_pic_email": "",
                        "client_pic_department": "",
                        "client_detail_id": parseInt(this.form.formDelivery[delivery_key].pic[0].client_detail_id),
                        "deleted_at": false,
                    }
                );
            },
            deleteFormClientPIC(id){
                this.visibleDeleteFormClientPIC();
                for(var i = 0; i < this.form.formClient.pic.length; i++){
                    if(this.form.formClient.pic[i].id == id){
                        this.form.formClient.pic[i].deleted_at = true;
                    }
                }
            },
            deleteFormBillingPIC(id){
                for(var i = 0; i < this.form.formBilling.pic.length; i++){
                    if(this.form.formBilling.pic[i].id == id){
                        this.form.formBilling.pic[i].deleted_at = true;
                    }
                }
            },
            deleteFormDeliveryPIC(id, index){
                for(var i = 0; i < this.form.formDelivery[index - 1].pic.length; i++){
                    if(this.form.formDelivery[index - 1].pic[i].id == id){
                        this.form.formDelivery[index - 1].pic[i].deleted_at = true;
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
                this.clientMunicipalities = [{}];
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
                this.billingMunicipalities = [{}];
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
                for (let i = 0; i < this.form.formDelivery.length; i++) {
                    if(this.form.formDelivery[i].key == key){
                        this.form.formDelivery[i].client_municipality_id = '';
                        let index = key - 1;
                        this.customMunicipality[index] = _.filter(this.municipalities, ['province_id', province_id]);
                    }
                }
                this.isDeliveryProvinceSelected = true;
            },
            getMunicipalities(){
                axios.get('/api/get-municipalities')
                .then(res => {
                    this.municipalities = res.data;
                    for(let i = 0; i < this.form.formDelivery.length; i++) {
                        this.customMunicipality.push(_.filter(res.data, ['province_id', this.form.formDelivery[i].client_province_id]));
                    }
                })
            },
            toEdit(){
                this.isEdit = true;
            },
        },
        computed: {
            clientDisabled(){
                if(this.form.isSameAsCustomerDetails && this.isEdit) return true;
                if(!this.isEdit) return true;
            },
            billingDisabled(){
                if(this.form.isSameAsBillingDetails && this.isEdit) return true;
                if(!this.isEdit) return true;
            },
        },
        created(){
            this.getCustomer();
            this.pageType();
            this.getProvinces();
            this.getMunicipalities();
        }
    }
</script>
