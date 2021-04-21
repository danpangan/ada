<template>
    <div class="container">
        <div class="content-header mb-3" style="border-bottom: 1px solid black;">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h4 class="m-0 text-dark"><strong>Supplier Management</strong></h4>
                    </div>
                    <div class="col-sm-6">
                        <button type="button" class="btn button-standard float-right" @click="$router.push('/supplier-management')">
                            <i class="el-icon-arrow-left"></i> Lists
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-12">
                <form @submit.prevent="createSupplier()">
                    <el-card class="box-card" shadow="hover">
                        <div slot="header">
                            <span>Supplier Information</span>
                        </div>
                        <el-form ref="form" :model="form" label-width="15%" :label-position="`right`">
                            <el-form-item label="Supplier Name">
                                <el-col :span="12">
                                    <el-input v-model="form.supplier_name"></el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Address">
                                <el-col :span="6">
                                    <el-select v-model="form.client_province_id" @change="selectClientProvince" placeholder="Province">
                                        <el-option
                                        v-for="client_province in clientProvinces"
                                        :key="client_province.id"
                                        :label="client_province.province_name"
                                        :value="client_province.id">
                                        </el-option>
                                    </el-select>
                                </el-col>
                                <el-col :span="6">
                                    <el-select v-model="form.client_municipality_id" placeholder="Municipality">
                                        <el-option
                                        v-for="client_municipality in clientMunicipalities"
                                        :key="client_municipality.id"
                                        :label="client_municipality.municipality_name"
                                        :value="client_municipality.id">
                                        </el-option>
                                    </el-select>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Address">
                                <el-col :span="12">
                                    <el-input 
                                        type="textarea" 
                                        v-model="form.supplier_address" 
                                        resize="none"
                                        placeholder="Complete address..."
                                        :autosize="{ minRows: 4, maxRows: 6}">
                                    </el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Email">
                                <el-col :span="8">
                                    <el-input v-model="form.supplier_email"></el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Contact No.">
                                <el-col :span="6">
                                    <el-input v-model="form.supplier_contact"></el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Fax No.">
                                <el-col :span="6">
                                    <el-input v-model="form.supplier_fax"></el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Transaction Currency">
                                <el-col :span="24">
                                    <el-radio v-model="form.transaction_currency" label="PHP">PHP</el-radio>
                                    <el-radio v-model="form.transaction_currency" label="USD">USD</el-radio>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="">
                                <el-col :span="24" style="text-align: right">
                                    <button type="button" class="btn button-standard ml-3" size="mini" @click="addContactPerson">
                                        <i class="el-icon-plus"></i> Add contact person
                                    </button>
                                </el-col>
                            </el-form-item>
                            <div v-for="contactPerson in form.contactPersons" :key="contactPerson.id">
                                <el-form-item label="Contact Person">
                                    <el-col :span="12">
                                        <el-input v-model="contactPerson.contact_name"></el-input>
                                    </el-col>
                                    <el-col :span="12" style="text-align: right">
                                        <el-button type="danger" icon="el-icon-delete" size="mini" @click="deleteContactPerson(contactPerson.id)" v-show="form.contactPersons.length > 1 && contactPerson.id != 1" circle></el-button>
                                    </el-col>
                                </el-form-item>
                                <el-form-item label="Contact Person Email">
                                    <el-col :span="12">
                                        <el-input v-model="contactPerson.contact_email"></el-input>
                                    </el-col>
                                </el-form-item>
                                <hr>
                            </div>
                            <el-form-item label="Materials">
                                <el-col :span="12">
                                    <el-input 
                                        type="textarea" 
                                        v-model="form.supplier_materials" 
                                        resize="none"
                                        placeholder=""
                                        :autosize="{ minRows: 4, maxRows: 6}">
                                    </el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Category">
                                <el-col :span="6">
                                    <el-select v-model="form.supplier_category" placeholder="Category">
                                        <el-option
                                        v-for="category in $supplier_categories"
                                        :key="category.id"
                                        :label="category.category_name"
                                        :value="category.id">
                                        </el-option>
                                    </el-select>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Classification">
                                <el-col :span="6">
                                    <el-select v-model="form.supplier_classification_id" placeholder="Classification">
                                        <el-option
                                        v-for="classification in $supplier_classifications"
                                        :key="classification.id"
                                        :label="classification.classification_name"
                                        :value="classification.id">
                                        </el-option>
                                    </el-select>
                                </el-col>
                            </el-form-item>
                        </el-form>
                    </el-card>
                    <div class="outside-card-button">
                        <el-button type="primary" v-on:click="createSupplier"><i class="el-icon-plus"></i> Create</el-button>
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
                    supplier_name: '',
                    client_province_id: '',
                    client_municipality_id: '',
                    supplier_address: '',
                    supplier_email: '',
                    supplier_contact: '',
                    supplier_fax: '',
                    supplier_contact_person: '',
                    supplier_materials: '',
                    supplier_category: '',
                    supplier_classification_id: '',
                    contactPersons: [{ 'id': 1, 'contact_name': '', 'contact_email': '' }],
                    transaction_currency: '',
                }),
                clientProvinces: [{}],
                clientMunicipalities: [{}],
            }
        },
        methods: {
            createSupplier(){
                Swal.fire({
                    text: 'Are you sure you want to register new supplier?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Register',
                    width: '25rem',
                })
                .then((result) => {
                    if(result.value){
                        this.form.post('api/supplier')
                        .then((res) => {
                            Swal.fire(
                                'Registered!',
                                'Supplier successfully registered.',
                                'success'
                            )
                        })
                        .catch(error => {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops!',
                                text: 'Supplier registration failed.',
                            })
                            console.log(error)
                        });
                    }
                })
            },
            resetFields(){

            },
            getProvinces(){
                axios.get('/api/get-provinces')
                .then((res) => {
                    this.clientProvinces = res.data;
                })
                .catch((error) => {

                });
            },
            selectClientProvince(province_id){
                this.clientMunicipalities = {};
                this.form.client_municipality_id = '';
                this.getClientMunicipalities(province_id);
            },
            getClientMunicipalities(province_id){
                axios.get('/api/get-municipalities?province_id='+province_id)
                .then((res) => {
                    this.clientMunicipalities = res.data;
                })
                .catch((error) => {

                });
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
        },
        created(){
            this.getProvinces();
        },

    }
</script>
