<template>
    <div class="container">
        <div class="content-header mb-3" style="border-bottom: 1px solid black;">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h4 class="m-0 text-dark"><strong>Edit Municipality</strong></h4>
                    </div>
                    <div class="col-sm-6">
                        <button type="button" class="btn button-standard float-right" @click="$router.push('/locations')">
                            <i class="el-icon-arrow-left"></i> Lists
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-12">
                <form @submit.prevent="updateMunicipality()">
                    <el-card class="box-card mb-3" shadow="hover">
                        <div slot="header">
                            <span>Location Information</span>
                        </div>
                        <el-form ref="form" :model="form" label-width="15%" :label-position="`right`">
                            <el-form-item label="Province Name">
                                <el-col :span="12">
                                    <el-input v-model="form.province_name" :disabled="true"></el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Municipality Name">
                                <el-col :span="12">
                                    <el-input v-model="form.municipality_name"></el-input>
                                </el-col>
                            </el-form-item>
                        </el-form>

                        <div class="outside-card-button">
                            <el-button type="primary" @click="updateMunicipality"><i class="el-icon-edit-outline"></i> Update</el-button>
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
                    province_name: '',
                    province_id: '',
                    municipality_name: '',
                    municipality_id: '',
                }),
                isProvinceEdit: false,
                isMunicipalityEdit: false,
            }
        },
        methods: {
            updateMunicipality() {
                Swal.fire({
                    text: 'Are you sure you want to update municipality?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Update',
                    width: '25rem',
                })
                .then((result) => {
                    if(result.value){
                        this.isLoading = true;
                        this.form.put('/api/update-municipality/'+this.$route.params.municipality_id)
                        .then((res) => {
                            Swal.fire(
                                'Updated!',
                                'Municipality successfully updated.',
                                'success'
                            )
                            this.getMunicipality();
                        })
                        .catch(error => {
                            console.log(error)
                        });
                        this.isLoading = false;
                    }
                })
            },
            getMunicipality(){
                axios.get('/api/get-municipality/'+this.$route.params.municipality_id)
                .then((res) => {
                    this.form.fill(res.data);
                })
                .catch((error) => {
                    console.log(error)
                })
            }
        },
        created() {
            this.getMunicipality();
        },
    }
</script>
