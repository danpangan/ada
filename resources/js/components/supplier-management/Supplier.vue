<template>
    <div class="container">
        <div class="content-header mb-3" style="border-bottom: 1px solid black;">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h4 class="m-0 text-dark" v-show="isEdit"><strong>Edit Supplier</strong></h4>
                        <h4 class="m-0 text-dark" v-show="!isEdit"><strong>View Supplier</strong></h4>
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
                <form @submit.prevent="updateSupplier()">
                    <el-card class="box-card mb-3" shadow="hover">
                        <div slot="header">
                            <span>Supplier Information</span>
                        </div>
                        <el-form ref="form" :model="form" label-width="15%" :label-position="`right`">
                            <el-form-item label="Supplier Name">
                                <el-col :span="12">
                                    <el-input v-model="form.supplier_name" :disabled="readonly"></el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Address">
                                <el-col :span="6">
                                    <el-select v-model="form.supplier_province_id" @change="selectSupplierProvince" placeholder="Province" :disabled="readonly">
                                        <el-option
                                        v-for="supplier_province in supplierProvinces"
                                        :key="supplier_province.id"
                                        :label="supplier_province.province_name"
                                        :value="supplier_province.id">
                                        </el-option>
                                    </el-select>
                                </el-col>
                                <el-col :span="6">
                                    <el-select v-model="form.supplier_municipality_id" placeholder="Municipality" :disabled="readonly">
                                        <el-option
                                        v-for="supplier_municipality in supplierMunicipalities"
                                        :key="supplier_municipality.id"
                                        :label="supplier_municipality.municipality_name"
                                        :value="supplier_municipality.id">
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
                                        :disabled="readonly"
                                        :autosize="{ minRows: 4, maxRows: 6}">
                                    </el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Email">
                                <el-col :span="8">
                                    <el-input v-model="form.supplier_email" :disabled="readonly"></el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Contact No.">
                                <el-col :span="6">
                                    <el-input v-model="form.supplier_contact" :disabled="readonly"></el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Fax No.">
                                <el-col :span="6">
                                    <el-input v-model="form.supplier_fax" :disabled="readonly"></el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Transaction Currency">
                                <el-col :span="24">
                                    <el-radio v-model="form.transaction_currency" :disabled="readonly" label="PHP">PHP</el-radio>
                                    <el-radio v-model="form.transaction_currency" :disabled="readonly" label="USD">USD</el-radio>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="" v-show="isEdit">
                                <el-col :span="24" style="text-align: right">
                                    <button type="button" class="btn button-standard ml-3" size="mini" @click="addContactPerson">
                                        <i class="el-icon-plus"></i> Add contact person
                                    </button>
                                </el-col>
                            </el-form-item>
                            <div v-for="(contactPerson, index) in form.contactPersons" :key="contactPerson.id" v-show="!contactPerson.deleted_at">
                                <el-form-item label="Contact Person">
                                    <el-col :span="12">
                                        <el-input v-model="contactPerson.supplier_contact_name"  :disabled="readonly"></el-input>
                                    </el-col>
                                    <el-col :span="12" style="text-align: right">
                                        <el-button type="danger" icon="el-icon-delete" size="mini" @click="deleteContactPerson(contactPerson.id)" v-show="form.contactPersons.length > 1 && index > 0 & isEdit" circle></el-button>
                                    </el-col>
                                </el-form-item>
                                <el-form-item label="Contact Person Email">
                                    <el-col :span="12">
                                        <el-input v-model="contactPerson.supplier_contact_email"  :disabled="readonly"></el-input>
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
                                        :disabled="readonly"
                                        :autosize="{ minRows: 4, maxRows: 6}">
                                    </el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Category">
                                <el-col :span="6">
                                    <el-select v-model="form.supplier_category" placeholder="Category" :disabled="readonly">
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
                                    <el-select v-model="form.supplier_classification_id" placeholder="Classification" :disabled="readonly">
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

                    <div class="outside-card-button" v-if="isEdit">
                        <el-button type="primary" v-on:click="updateSupplier"><i class="el-icon-circle-check"></i> Update</el-button>
                        <el-button type="warning" v-on:click="resetFields"><i class="el-icon-refresh-left"></i> Reset</el-button>
                        <el-button type="danger" @click="$router.back(-1)"><i class="el-icon-close"></i> Cancel</el-button>
                    </div>
                    <div class="outside-card-button" v-else>
                        <router-link :to="'/update-supplier/'+this.$route.params.supplier_id">
                            <el-button type="primary" @click="toEdit"><i class="el-icon-edit-outline"></i> Edit</el-button>
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
                    supplier_name: '',
                    supplier_province_id: '',
                    supplier_municipality_id: '',
                    supplier_address: '',
                    supplier_email: '',
                    supplier_contact: '',
                    supplier_fax: '',
                    supplier_materials: '',
                    supplier_category: '',
                    supplier_classification_id: '',
                    contactPersons: [],
                    transaction_currency: '',
                }),
                supplierProvinces: [{}],
                supplierMunicipalities: [{}],
                isEdit: false,
                isLoading: false,
            }
        },
        methods: {
            updateSupplier() {
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
                        this.form.put('/api/supplier/'+this.$route.params.supplier_id)
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
            getSupplier(){
                axios.get('/api/supplier/'+this.$route.params.supplier_id)
                .then((res) => {
                    this.form.fill(res.data.supplier);
                    this.form.contactPersons = res.data.supplier_contacts;
                    this.getSupplierMunicipalities(res.data.supplier.supplier_province_id);
                    if(this.form.contactPersons.length == 0){
                        this.form.contactPersons = [{ 'id': 1, 'supplier_contact_name': '', 'supplier_contact_email': '', 'deleted_at': false }];
                    }
                })
                .catch(error => {
                    console.log(error)
                });
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
            getProvinces(){
                axios.get('/api/get-provinces')
                .then((res) => {
                    this.supplierProvinces = res.data;
                })
                .catch((error) => {

                });
            },
            selectSupplierProvince(province_id){
                this.supplierMunicipalities = {};
                this.form.supplier_municipality_id = '';
                this.getSupplierMunicipalities(province_id);
            },
            getSupplierMunicipalities(province_id){
                axios.get('/api/get-municipalities?province_id='+province_id)
                .then((res) => {
                    this.supplierMunicipalities = res.data;
                })
                .catch((error) => {

                });
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
        },
        created() {
            this.getSupplier();
            this.pageType();
            this.getProvinces();
        },
        computed: {
            readonly(){
                if(!this.isEdit) return true;
            },
        },
        watch:{
            $route (to, from){
                this.pageType();
            }
        }
    }
</script>
