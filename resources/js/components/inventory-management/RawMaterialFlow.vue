<template>
    <div class="container">
        <div class="content-header mb-3" style="border-bottom: 1px solid black;">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h4 class="m-0 text-dark"><strong>{{ titleHeader }}</strong></h4>
                    </div>
                    <div class="col-sm-6">
                        <button type="button" class="btn button-standard float-right" @click="$router.go(-1)">
                            <i class="el-icon-arrow-left"></i> Back
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-12">
                <form @submit.prevent="updateRawMaterial()">
                    <el-card class="box-card mb-3" shadow="hover">
                        <div slot="header">
                            <span>Raw Material Information</span>
                        </div>
                        <el-form ref="form" :model="form" label-width="15%" :label-position="`right`">
                            <el-form-item label="Material Description">
                                <el-col :span="12">
                                    <el-input 
                                        type="textarea" 
                                        v-model="form.material_description" 
                                        resize="none"
                                         :disabled="true"
                                        :autosize="{ minRows: 4, maxRows: 6}">
                                    </el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Type Code">
                                <el-col :span="6">
                                    <el-select v-model="form.material_type_id" @change="selectTypeCode" :disabled="true" placeholder="Type Code">
                                        <el-option
                                        v-for="type_code in type_codes"
                                        :key="type_code.id"
                                        :label="typeCodeLabel(type_code.type_code, type_code.type_code_description)"
                                        :value="type_code.id">
                                        </el-option>
                                    </el-select>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Classification">
                                <el-col :span="6">
                                    <el-select v-model="form.material_classification_id" :disabled="true" placeholder="Classification">
                                        <el-option
                                        v-for="classification in classifications"
                                        :key="classification.id"
                                        :label="classification.material_classification_name"
                                        :value="classification.id">
                                        </el-option>
                                    </el-select>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Dimension">
                                <el-col :span="4">
                                    <el-input v-model="form.material_length" :disabled="true"></el-input>
                                </el-col>
                                <el-col :span="4">
                                    <el-select v-model="form.material_length_unit" :disabled="true" placeholder="Unit">
                                        <el-option
                                        v-for="unit in $units_dimension"
                                        :key="unit.id"
                                        :label="unit.unit_name"
                                        :value="unit.unit_name">
                                        </el-option>
                                    </el-select>
                                </el-col>
                                <el-col :span="4">
                                    <el-input v-model="form.material_width" :disabled="true"></el-input>
                                </el-col>
                                <el-col :span="4">
                                    <el-select v-model="form.material_width_unit" :disabled="true" placeholder="Unit">
                                        <el-option
                                        v-for="unit in $units_dimension"
                                        :key="unit.id"
                                        :label="unit.unit_name"
                                        :value="unit.unit_name">
                                        </el-option>
                                    </el-select>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Supplier">
                                <el-col :span="18">
                                    <el-select v-model="form.supplier_id" placeholder="Suppliers" class="block" @change="selectSupplier">
                                        <el-option
                                        v-for="supplier in suppliers"
                                        :key="supplier.id"
                                        :label="supplierLabel(supplier.supplier_code, supplier.supplier_name)"
                                        :value="supplier.id">
                                        </el-option>
                                    </el-select>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Roll Materials" v-show="form.material_type_id == 8 && $route.params.material_flow_id >= 2">
                                <el-col>
                                    <el-select v-model="form.roll_material_tracking_id" @change="rollMaterialSelect(form.roll_material_tracking_id)" placeholder="Roll Materials">
                                        <el-option
                                        v-for="material_roll in material_rolls"
                                        v-show="material_roll.stock_roll_quantity > 0"
                                        :key="material_roll.id"
                                        :label="rollMaterialName(
                                            material_roll.material_description, 
                                            material_roll.roll_length, 
                                            material_roll.roll_length_unit,
                                            material_roll.material_width, 
                                            material_roll.material_width_unit, 
                                            material_roll.stock_roll_quantity, 
                                            material_roll.material_quantity_unit)"
                                        :value="material_roll.roll_material_tracking_id">
                                        </el-option>
                                    </el-select>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Quantity">
                                <el-col :span="4">
                                    <el-input v-model="form.material_quantity"></el-input>
                                </el-col>
                                <el-col :span="3">
                                    <el-select v-model="form.material_quantity_unit" :disabled="true" placeholder="Unit">
                                        <el-option
                                        v-for="quantity_unit in $units_quantity"
                                        :key="quantity_unit.id"
                                        :label="quantity_unit.unit_name"
                                        :value="quantity_unit.id">
                                        </el-option>
                                    </el-select>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Roll Length" v-show="$route.params.material_flow_id == 2 || $route.params.material_flow_id == 4">
                                <el-col :span="4">
                                    <el-input v-model="form.material_roll_length" :disabled="$route.params.material_flow_id == 4"></el-input>
                                </el-col>
                                <el-col :span="3">
                                    <el-select v-model="form.material_length_unit" :disabled="true" placeholder="Unit">
                                        <el-option
                                        v-for="units_dimension in $units_dimension"
                                        :key="units_dimension.id"
                                        :label="units_dimension.unit_name"
                                        :value="units_dimension.id">
                                        </el-option>
                                    </el-select>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Amount" v-show="$route.params.material_flow_id == 1">
                                <el-col :span="4">
                                    <el-input v-model="form.material_unit_amount"></el-input>
                                </el-col>
                                <el-col :span="3">
                                    <el-select v-model="form.material_unit_amount_currency" placeholder="Unit" :disabled="true">
                                        <el-option
                                        v-for="amount_unit in $units_amount"
                                        :key="amount_unit.id"
                                        :label="amount_unit.unit_name"
                                        :value="amount_unit.unit_name">
                                        </el-option>
                                    </el-select>
                                </el-col>
                            </el-form-item>
                        </el-form>
                        <div class="outside-card-button">
                            <el-button type="primary" v-on:click="transactMaterial">
                                <i class="el-icon-circle-check"></i> Transact
                            </el-button>
                        </div>
                    </el-card>
                </form>
            </div>
        </div>
    </div>
</template>

<script>
    import moment from 'moment';
    export default {
        data(){
            return {
                form: new Form({
                    material_description: '',
                    material_type_id: '',
                    material_classification_id: '',
                    material_length: '',
                    material_length_unit: '',
                    material_width: '',
                    material_width_unit: '',
                    supplier_material_id: '',
                    material_quantity: '',
                    material_quantity_unit: '',
                    material_unit_amount: '',
                    material_unit_amount_currency: '',
                    material_location: '',
                    material_last_in: '',
                    material_last_out: '',
                    material_id: '',
                    material_flow_id: '',
                    supplier_id: '',
                    roll_material_tracking_id: '',
                    material_roll_length: '',
                }),
                type_codes: '',
                categories: [],
                classifications: [],
                suppliers: [],
                isEdit: false,
                filter: '',
                suppliers: [],
                material_rolls: [],
            }
        },
        methods: {
            transactMaterial() {
                Swal.fire({
                    text: 'Are you sure you want to transact material?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Transact',
                    width: '25rem',
                })
                .then((result) => {
                    if(result.value){
                        this.isLoading = true;
                        this.form.post('/api/transact-material-supplier/'+this.$route.params.material_id)
                        .then((res) => {
                            Swal.fire(
                                'Transacted!',
                                'Material successfully transacted.',
                                'success'
                            )
                            this.getMaterial();
                        })
                        .catch(error => {
                            console.log(error)
                        });
                        this.isLoading = false;
                    }
                })
            },
            getMaterial(){
                axios.get('/api/material/'+this.$route.params.material_id)
                .then((res) => {
                    this.form.fill(res.data);
                    this.form.material_quantity = '';
                    this.selectTypeCode();
                    if(this.$route.params.material_flow_id > 1){
                        this.form.material_unit_amount = 0;
                        this.form.material_unit_amount_currency = 'PHP';
                    }
                    this.form.material_flow_id = this.$route.params.material_flow_id;
                    this.material_rolls = res.data.roll_materials;
                })
                .catch((error) => {
                    console.log(error)
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
                this.getMaterial();
            },
            getTypeCodes(){
                axios.get('/api/get-rm-type-codes')
                .then((res) => {
                    this.type_codes = res.data;
                })
                .catch((error) => {
                    console.log(error)
                });
            },
            resetFields(){

            },
            typeCodeLabel(type_code, classification){
                return type_code + ' - ' + classification;
            },
            selectTypeCode(){
                axios.get('/api/get-material-classification/'+this.form.material_type_id)
                .then((res) => {
                    this.classifications = res.data;
                })
                .catch((error) => {
                    console.log(error)
                })
            },
            getSuppliers(){
                let material_flow_id = this.$route.params.material_flow_id;
                axios.get('/api/get-suppliers', {
                    params: {
                        material_flow_id: material_flow_id,
                        material_id: this.$route.params.material_id,
                    }
                })
                .then((res) => {
                    this.suppliers = res.data;
                })
                .catch((error) => {
                    console.log(error)
                })
            },
            supplierLabel(code, name){
                return code + ' - ' + name;
            },
            pageType(){
                if(window.location.href.indexOf("update") > -1){
                    this.isEdit = true;
                }
                else{
                    this.isEdit = false;
                }
            },
            toEdit(){
                this.isEdit = true;
            },
            rollMaterialName(material_decsription, roll_length, roll_length_unit, width, width_unit, quantity, quantity_unit){
                for (let i = 0; i < this.$units_quantity.length; i++) {
                    if(this.$units_quantity[i].id == quantity_unit) return material_decsription + '_' + roll_length + '' + roll_length_unit + 'x' + width + '' + width_unit + ' (' + quantity + ' ' + this.$units_quantity[i].unit_name + ')';
                }
            },
            rollMaterialSelect(roll_material_tracking_id){
                if(this.$route.params.material_flow_id == 4){
                    let roll_length = _.find(this.material_rolls, { 'id': this.form.roll_material_tracking_id }).roll_length;
                    this.form.material_roll_length = roll_length;
                }
            },
            selectSupplier(event){
                let supplier = _.find(this.suppliers, { 'id': event });
                this.form.material_unit_amount_currency = supplier.transaction_currency;
            }
        },
        created() {
            this.getMaterial();
            this.getTypeCodes();
            this.getSuppliers();
            this.pageType();
        },
        computed: {
            titleHeader(){
                return this.$material_flows_title.find(flow => flow.id == this.$route.params.material_flow_id).material_flow_name + ' Material';
            }
        },
        watch:{
            $route (to, from){
                this.pageType();
            }
        }
    }
</script>
