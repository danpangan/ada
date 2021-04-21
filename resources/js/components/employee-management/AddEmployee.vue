<template>
    <div class="container">
        <div class="content-header mb-3" style="border-bottom: 1px solid black;">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h4 class="m-0 text-dark"><strong>Add Employee</strong></h4>
                    </div>
                    <div class="col-sm-6">
                        <button type="button" class="btn button-standard float-right" @click="$router.push('/employees')">
                            <i class="el-icon-arrow-left"></i> Lists
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-12">
                <form @submit.prevent="createEmployee()">
                    <el-card class="box-card mt-3" shadow="hover">
                        <div slot="header">
                            <span>Employee Information</span>
                        </div>
                        <el-form ref="form" :model="form" label-width="15%" :label-position="`right`">
                            <el-form-item label="Date Hired">
                                <el-col :span="4">
                                    <el-select v-model="form.hired_month_value" placeholder="Month">
                                        <el-option
                                        v-for="month in $months"
                                        :key="month.id"
                                        :label="month.month_name"
                                        :value="month.id">
                                        </el-option>
                                    </el-select>
                                </el-col>
                                <el-col :span="4">
                                    <el-select v-model="form.hired_day_value" placeholder="Day">
                                        <el-option
                                        v-for="day in days"
                                        :key="day"
                                        :value="day">
                                        </el-option>
                                    </el-select>
                                </el-col>
                                <el-col :span="4">
                                    <el-select v-model="form.hired_year_value" placeholder="Year">
                                        <el-option
                                        v-for="year in hire_years"
                                        :key="year"
                                        :value="year">
                                        </el-option>
                                    </el-select>
                                </el-col>
                            </el-form-item>
                        </el-form>
                    </el-card>

                    <el-card class="box-card mt-3" shadow="hover">
                        <div slot="header">
                            <span>Personal Information</span>
                        </div>
                        <el-form ref="form" :model="form" label-width="15%" :label-position="`right`">
                            <el-form-item label="Address">
                                <el-col :span="12">
                                    <el-input 
                                        type="textarea" 
                                        v-model="form.address" 
                                        resize="none"
                                        :autosize="{ minRows: 4, maxRows: 6}">
                                    </el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="First Name">
                                <el-col :span="12">
                                    <el-input v-model="form.first_name" @input="form.first_name = $event.charAt(0).toUpperCase() + $event.slice(1)"></el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Middle Name">
                                <el-col :span="12">
                                    <el-input v-model="form.middle_name" @input="form.middle_name = $event.charAt(0).toUpperCase() + $event.slice(1)"></el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Last Name">
                                <el-col :span="12">
                                    <el-input v-model="form.last_name" @input="form.last_name = $event.charAt(0).toUpperCase() + $event.slice(1)"></el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Email">
                                <el-col :span="12">
                                    <el-input v-model="form.email"></el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Contact">
                                <el-col :span="12">
                                    <el-input v-model="form.contact"></el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Birthday">
                                <el-col :span="4">
                                    <el-select v-model="form.month_value" placeholder="Month">
                                        <el-option
                                        v-for="month in $months"
                                        :key="month.id"
                                        :label="month.month_name"
                                        :value="month.id">
                                        </el-option>
                                    </el-select>
                                </el-col>
                                <el-col :span="4">
                                    <el-select v-model="form.day_value" placeholder="Day">
                                        <el-option
                                        v-for="day in days"
                                        :key="day"
                                        :value="day">
                                        </el-option>
                                    </el-select>
                                </el-col>
                                <el-col :span="4">
                                    <el-select v-model="form.year_value" placeholder="Year">
                                        <el-option
                                        v-for="year in years"
                                        :key="year"
                                        :value="year">
                                        </el-option>
                                    </el-select>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Gender">
                                <el-col :span="4">
                                    <el-select v-model="form.gender_value" placeholder="Gender">
                                        <el-option
                                        v-for="gender in $genders"
                                        :key="gender.id"
                                        :label="gender.gender_name"
                                        :value="gender.id">
                                        </el-option>
                                    </el-select>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Department">
                                <el-col :span="8">
                                    <el-select v-model="form.department_value" placeholder="Department" class="el-col-24">
                                        <el-option 
                                        v-for="department in departments"
                                        :key="department.id"
                                        :label="department.department_name"
                                        :value="department.id">
                                        </el-option>
                                    </el-select>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="User Role">
                                <el-col :span="4">
                                    <el-select v-model="form.user_role" placeholder="User Role">
                                        <el-option
                                        v-for="role in $user_roles"
                                        :key="role.id"
                                        :label="role.role_name"
                                        :value="role.id">
                                        </el-option>
                                    </el-select>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="">
                                <el-col :span="12">
                                    <el-checkbox>Checker</el-checkbox>
                                    <el-checkbox>Approver</el-checkbox>
                                </el-col>
                            </el-form-item>
                        </el-form>
                    </el-card>

                    <el-card class="box-card mt-3" shadow="hover">
                        <div slot="header">
                            <span>Permissions</span>
                        </div>
                        <div v-for="permission in form.permissions" :key="permission.id">
                            <el-collapse v-model="activePermissions">
                                <el-collapse-item :name="permission[0].permission_description" disabled>
                                    <template slot="title">
                                        <el-col :span="17">
                                            <div class="permission-title">{{ permission[0].permission_description }}</div>
                                        </el-col>
                                        <el-col :span="8">
                                            <el-checkbox v-model="permission[0].isSelected" :indeterminate="permission[0].isIndeterminate" class="float-right mr-3" @change="isSelectedPermission(permission[0].permission_description)"></el-checkbox>
                                        </el-col>
                                    </template>
                                    <el-row :gutter="20" v-for="item in permission" :key="item.permission_id" class="permission-row">
                                        <el-col :span="16">
                                            <div v-if="!item.isTitle" class="ml-3">{{ item.permission_description }}</div>
                                        </el-col>
                                        <el-col :span="8">
                                            <template v-if="!item.isTitle">
                                                <el-checkbox v-model="item.isSelected" class="float-right mr-3" @change="handleItemChange(permission[0]['permission_description'])"></el-checkbox>
                                            </template>
                                        </el-col>
                                    </el-row>
                                </el-collapse-item>
                            </el-collapse>
                        </div>
                    </el-card>

                    <div class="outside-card-button">
                        <el-button type="primary" v-on:click="createEmployee" v-if="!isLoading"><i class="el-icon-plus"></i> Create</el-button>
                        <el-button type="primary" v-on:click="createEmployee" :loading="isLoading" v-else> Loading</el-button>
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
                    employee_number: '',
                    address: '',
                    first_name: '',
                    middle_name: '',
                    last_name: '',
                    email: '',
                    contact: '',
                    month_value: '',
                    day_value: '',
                    year_value: '',
                    hired_month_value: '',
                    hired_day_value: '',
                    hired_year_value: '',
                    gender_value: '',
                    department_value: '',
                    user_role: '',
                    permissions: [],
                }),
                errors: {},
                month_value: '',
                day_value: '',
                days: Array.from({ length: 32 }, (v, i) => i).slice(1),
                year_value: '',
                years: Array.from({ length: new Date().getFullYear() - 17 }, (v, i) => i).slice(new Date().getFullYear() - 65),
                hire_years: Array.from({ length: new Date().getFullYear() + 1 }, (v, i) => i).slice(new Date().getFullYear() - 65).reverse(),
                departments: [],
                isLoading: false,
                permissionDepartments: [
                    { permission_id: 0, permission_description: 'Department Permissions', isSelected: false, item_id: 'A0', isIndeterminate: false, isTitle: true },
                    { permission_id: 1, permission_description: 'Create new department', isSelected: false, item_id: 'A1' },
                    { permission_id: 2, permission_description: 'View department list', isSelected: false, item_id: 'A2' },
                    { permission_id: 3, permission_description: 'View department details', isSelected: false, item_id: 'A3' },
                    { permission_id: 4, permission_description: 'Edit department', isSelected: false, item_id: 'A4' },
                    { permission_id: 5, permission_description: 'Delete department', isSelected: false, item_id: 'A5' },
                    { permission_id: 6, permission_description: 'Restore deleted department', isSelected: false, item_id: 'A6' },
                ],
                permissionEmployees: [
                    { permission_id: 0, permission_description: 'Employee Permissions', isSelected: false, item_id: 'B0', isIndeterminate: false, isTitle: true },
                    { permission_id: 1, permission_description: 'Register new employee', isSelected: false, item_id: 'B1',},
                    { permission_id: 2, permission_description: 'View employee list', isSelected: false, item_id: 'B2',},
                    { permission_id: 3, permission_description: 'View employee details', isSelected: false, item_id: 'B3',},
                    { permission_id: 4, permission_description: 'Edit employee', isSelected: false, item_id: 'B4',},
                    { permission_id: 5, permission_description: 'Delete employee', isSelected: false, item_id: 'B5',},
                    { permission_id: 6, permission_description: 'Restore deleted employee', isSelected: false, item_id: 'B6',},
                ],
                permissionCustomers: [
                    { permission_id: 0, permission_description: 'Customer Permissions', isSelected: false, item_id: 'C0', isIndeterminate: false, isTitle: true },
                    { permission_id: 1, permission_description: 'Register new customer', isSelected: false, item_id: 'C1',},
                    { permission_id: 2, permission_description: 'View customer list', isSelected: false, item_id: 'C2',},
                    { permission_id: 3, permission_description: 'View customer details', isSelected: false, item_id: 'C3',},
                    { permission_id: 4, permission_description: 'Edit customer information', isSelected: false, item_id: 'C4',},
                    { permission_id: 5, permission_description: 'Edit customer billing information', isSelected: false, item_id: 'C5',},
                    { permission_id: 6, permission_description: 'Edit customer delivery information', isSelected: false, item_id: 'C6',},
                    { permission_id: 7, permission_description: 'Add new customer delivery information', isSelected: false, item_id: 'C7',},
                    { permission_id: 8, permission_description: 'Delete old customer delivery information', isSelected: false, item_id: 'C8',},
                    { permission_id: 9, permission_description: 'Delete customer', isSelected: false, item_id: 'C9',},
                    { permission_id: 10, permission_description: 'Restore deleted customer', isSelected: false, item_id: 'C10',},
                ],
                permissionRawMaterialSuppliers: [
                    { permission_id: 0, permission_description: 'Raw Material Supplier Permissions', isSelected: false, item_id: 'D0', isIndeterminate: false, isTitle: true },
                    { permission_id: 1, permission_description: 'Register new raw material supplier', isSelected: false, item_id: 'D1',},
                    { permission_id: 2, permission_description: 'View raw material supplier list', isSelected: false, item_id: 'D2',},
                    { permission_id: 3, permission_description: 'View raw material supplier details', isSelected: false, item_id: 'D3',},
                    { permission_id: 4, permission_description: 'Edit raw material supplier', isSelected: false, item_id: 'D4',},
                    { permission_id: 5, permission_description: 'Delete raw material supplier', isSelected: false, item_id: 'D5',},
                    { permission_id: 6, permission_description: 'Restore deleted raw material supplier', isSelected: false, item_id: 'D6',},
                ],
                permissionSubcontractMaterialSuppliers: [
                    { permission_id: 0, permission_description: 'Subcontractor Material Supplier Permissions', isSelected: false, item_id: 'E0', isIndeterminate: false, isTitle: true },
                    { permission_id: 1, permission_description: 'Register new subcontract material supplier', isSelected: false, item_id: 'E1' },
                    { permission_id: 2, permission_description: 'View subcontract material supplier list', isSelected: false, item_id: 'E2' },
                    { permission_id: 3, permission_description: 'View subcontract material supplier details', isSelected: false, item_id: 'E3' },
                    { permission_id: 4, permission_description: 'Edit subcontract material supplier', isSelected: false, item_id: 'E4' },
                    { permission_id: 5, permission_description: 'Delete subcontract material supplier', isSelected: false, item_id: 'E5' },
                    { permission_id: 6, permission_description: 'Restore deleted subcontract material supplier', isSelected: false, item_id: 'E6' },
                ],
                permissionForecasts: [
                    { permission_id: 0, permission_description: 'Forecast Permissions', isSelected: false, item_id: 'F0', isIndeterminate: false, isTitle: true },
                    { permission_id: 1, permission_description: 'Add new forecast', isSelected: false, item_id: 'F1' },
                    { permission_id: 2, permission_description: 'View forecast list', isSelected: false, item_id: 'F2' },
                    { permission_id: 3, permission_description: 'View forecast details', isSelected: false, item_id: 'F3' },
                    { permission_id: 4, permission_description: 'Edit forecast details', isSelected: false, item_id: 'F4' },
                    { permission_id: 5, permission_description: 'Add forecast items', isSelected: false, item_id: 'F5' },
                    { permission_id: 6, permission_description: 'Delete forecast items', isSelected: false, item_id: 'F6' },
                    { permission_id: 7, permission_description: 'Delete forecast', isSelected: false, item_id: 'F7' },
                    { permission_id: 8, permission_description: 'Restore deleted forecast', isSelected: false, item_id: 'F8' },
                ],
                permissionOrders: [
                    { permission_id: 0, permission_description: 'Order Permissions', isSelected: false, item_id: 'G0', isIndeterminate: false, isTitle: true },
                    { permission_id: 1, permission_description: 'Add new order', isSelected: false, item_id: 'G1' },
                    { permission_id: 2, permission_description: 'View order list', isSelected: false, item_id: 'G2' },
                    { permission_id: 3, permission_description: 'View order details', isSelected: false, item_id: 'G3' },
                    { permission_id: 4, permission_description: 'Edit order', isSelected: false, item_id: 'G4' },
                    { permission_id: 5, permission_description: 'Delete order', isSelected: false, item_id: 'G5' },
                    { permission_id: 6, permission_description: 'Restore deleted order', isSelected: false, item_id: 'G6' },
                    { permission_id: 7, permission_description: 'Order evaluator (Checker)', isSelected: false, item_id: 'G7' },
                    { permission_id: 8, permission_description: 'Order evaluator (Approver)', isSelected: false, item_id: 'G8' },
                ],
                permissionComputationSheets: [
                    { permission_id: 0, permission_description: 'Computation Sheet Permissions', isSelected: false, item_id: 'H0', isIndeterminate: false, isTitle: true },
                    { permission_id: 1, permission_description: 'Create new computation sheet', isSelected: false, item_id: 'H1' },
                    { permission_id: 2, permission_description: 'Manual entry for computation sheet', isSelected: false, item_id: 'H2' },
                    { permission_id: 3, permission_description: 'View all computation sheet list', isSelected: false, item_id: 'H3' },
                    { permission_id: 4, permission_description: 'View valid computation sheet list', isSelected: false, item_id: 'H4' },
                    { permission_id: 5, permission_description: 'View latest version of valid computations sheet list', isSelected: false, item_id: 'H5' },
                    { permission_id: 6, permission_description: 'View all computation sheet details', isSelected: false, item_id: 'H6' },
                    { permission_id: 7, permission_description: 'View valid computations sheet details only', isSelected: false, item_id: 'H7' },
                    { permission_id: 8, permission_description: 'View latest version of valid computation sheet only', isSelected: false, item_id: 'H8' },
                    { permission_id: 9, permission_description: 'Edit all computation sheet details', isSelected: false, item_id: 'H9' },
                    { permission_id: 10, permission_description: 'Edit valid computations sheet details only', isSelected: false, item_id: 'H10' },
                    { permission_id: 11, permission_description: 'Edit latest version of valid computation sheet only', isSelected: false, item_id: 'H11' },
                    { permission_id: 12, permission_description: 'Delete all computation sheet details', isSelected: false, item_id: 'H12' },
                    { permission_id: 13, permission_description: 'Delete valid computations sheet details only', isSelected: false, item_id: 'H13' },
                    { permission_id: 14, permission_description: 'Delete latest version of valid computation sheet only', isSelected: false, item_id: 'H14' },
                    { permission_id: 15, permission_description: 'Restore deleted computation sheet', isSelected: false, item_id: 'H15' },
                    { permission_id: 16, permission_description: 'Computation sheet evaluator (Checker)', isSelected: false, item_id: 'H16' },
                    { permission_id: 17, permission_description: 'Computation sheet evaluator (Approver)', isSelected: false, item_id: 'H17' },
                ],
                permissionQuotations: [
                    { permission_id: 0, permission_description: 'Quotation Permissions', isSelected: false, item_id: 'I0', isIndeterminate: false, isTitle: true },
                    { permission_id: 1, permission_description: 'Create new quotation', isSelected: false, item_id: 'I1' },
                    { permission_id: 2, permission_description: 'Manual entry for quotation', isSelected: false, item_id: 'I2' },
                    { permission_id: 3, permission_description: 'View quotation list', isSelected: false, item_id: 'I3' },
                    { permission_id: 4, permission_description: 'View quotation details', isSelected: false, item_id: 'I4' },
                    { permission_id: 5, permission_description: 'View invalid quotation details', isSelected: false, item_id: 'I5' },
                    { permission_id: 6, permission_description: 'Edit quotation details', isSelected: false, item_id: 'I6' },
                    { permission_id: 7, permission_description: 'Edit invalid quotation details', isSelected: false, item_id: 'I7' },
                    { permission_id: 8, permission_description: 'Delete quotation', isSelected: false, item_id: 'I8' },
                    { permission_id: 9, permission_description: 'Delete invalid quotation', isSelected: false, item_id: 'I9' },
                    { permission_id: 10, permission_description: 'Restore deleted quotation', isSelected: false, item_id: 'I10' },
                    { permission_id: 11, permission_description: 'Quotation evaluator (Checker)', isSelected: false, item_id: 'I11' },
                    { permission_id: 12, permission_description: 'Quotation evaluator (Approver)', isSelected: false, item_id: 'I12' },
                ],
                permissionPurchaseOrders: [
                    { permission_id: 0, permission_description: 'Purchase Order Permissions', isSelected: false, item_id: 'J0', isIndeterminate: false, isTitle: true },
                    { permission_id: 1, permission_description: 'Encode purchase order', isSelected: false, item_id: 'J1' },
                    { permission_id: 2, permission_description: 'View purchase order list', isSelected: false, item_id: 'J2' },
                    { permission_id: 3, permission_description: 'View closed purchase order list', isSelected: false, item_id: 'J3' },
                    { permission_id: 4, permission_description: 'View purchase order details', isSelected: false, item_id: 'J4' },
                    { permission_id: 5, permission_description: 'View closed purchase order details', isSelected: false, item_id: 'J5' },
                    { permission_id: 6, permission_description: 'Edit purchase order details', isSelected: false, item_id: 'J6' },
                    { permission_id: 7, permission_description: 'Delete closed purchased orders', isSelected: false, item_id: 'J7' },
                    { permission_id: 8, permission_description: 'Permanently delete purchase orders', isSelected: false, item_id: 'J8' },
                    { permission_id: 9, permission_description: 'Permanently delete closed purchase orders', isSelected: false, item_id: 'J9' },
                ],
                permissionSalesSheets: [
                    { permission_id: 0, permission_description: 'Sales Sheet Permissions', isSelected: false, item_id: 'K0', isIndeterminate: false, isTitle: true },
                    { permission_id: 1, permission_description: 'Create new sales sheet', isSelected: false, item_id: 'K1' },
                    { permission_id: 2, permission_description: 'Manual entry for sales sheet', isSelected: false, item_id: 'K2' },
                    { permission_id: 3, permission_description: 'View sales sheet list', isSelected: false, item_id: 'K3' },
                    { permission_id: 4, permission_description: 'View closed sales sheet list', isSelected: false, item_id: 'K4' },
                    { permission_id: 5, permission_description: 'View deleted sales sheet list', isSelected: false, item_id: 'K5' },
                    { permission_id: 6, permission_description: 'View sales sheet details', isSelected: false, item_id: 'K6' },
                    { permission_id: 7, permission_description: 'View closed sales sheet details', isSelected: false, item_id: 'K7' },
                    { permission_id: 8, permission_description: 'Edit sales sheet details', isSelected: false, item_id: 'K8' },
                    { permission_id: 9, permission_description: 'Delete closed sales sheet', isSelected: false, item_id: 'K9' },
                    { permission_id: 10, permission_description: 'Restore deleted closed sales sheet', isSelected: false, item_id: 'K10' },
                    { permission_id: 11, permission_description: 'Delete closed sales sheet', isSelected: false, item_id: 'K11' },
                    { permission_id: 12, permission_description: 'Delete closed sales sheet', isSelected: false, item_id: 'K12' },
                    { permission_id: 13, permission_description: 'Delete closed sales sheet', isSelected: false, item_id: 'K13' },
                ],
                permissionRawMaterials: [
                    { permission_id: 0, permission_description: 'Raw Material Permissions', isSelected: false, item_id: 'L0', isIndeterminate: false, isTitle: true },
                    { permission_id: 1, permission_description: 'Add new raw material', isSelected: false, item_id: 'L1' },
                    { permission_id: 2, permission_description: 'View raw material list', isSelected: false, item_id: 'L2' },
                    { permission_id: 3, permission_description: 'View raw material details', isSelected: false, item_id: 'L3' },
                    { permission_id: 4, permission_description: 'Edit raw material details', isSelected: false, item_id: 'L4' },
                    { permission_id: 5, permission_description: 'Restock raw material', isSelected: false, item_id: 'L5' },
                    { permission_id: 6, permission_description: 'Release raw material', isSelected: false, item_id: 'L6' },
                    { permission_id: 7, permission_description: 'Delete raw material', isSelected: false, item_id: 'L7' },
                    { permission_id: 8, permission_description: 'Restore raw material', isSelected: false, item_id: 'L8' },
                ],
                permissionSubcontractMaterials: [
                    { permission_id: 0, permission_description: 'Subcontractor Material Permissions', isSelected: false, item_id: 'M0', isIndeterminate: false, isTitle: true },
                    { permission_id: 1, permission_description: 'Add new subcontract material', isSelected: false, item_id: 'M1' },
                    { permission_id: 2, permission_description: 'View subcontract material list', isSelected: false, item_id: 'M2' },
                    { permission_id: 3, permission_description: 'View subcontract material details', isSelected: false, item_id: 'M3' },
                    { permission_id: 4, permission_description: 'Edit subcontract material details', isSelected: false, item_id: 'M4' },
                    { permission_id: 5, permission_description: 'Restock subcontract material', isSelected: false, item_id: 'M5' },
                    { permission_id: 6, permission_description: 'Release subcontract material', isSelected: false, item_id: 'M6' },
                    { permission_id: 7, permission_description: 'Delete subcontract material', isSelected: false, item_id: 'M7' },
                    { permission_id: 8, permission_description: 'Restore subcontract material', isSelected: false, item_id: 'M8' },
                ],
                permissionFinishGoods: [
                    { permission_id: 0, permission_description: 'Finish Goods Permissions', isSelected: false, item_id: 'N0', isIndeterminate: false, isTitle: true },
                    { permission_id: 1, permission_description: 'Store finish goods', isSelected: false, item_id: 'N1' },
                    { permission_id: 2, permission_description: 'View finish goods list', isSelected: false, item_id: 'N2' },
                    { permission_id: 3, permission_description: 'View finish goods details', isSelected: false, item_id: 'N3' },
                    { permission_id: 4, permission_description: 'Edit finish goods details', isSelected: false, item_id: 'N4' },
                    { permission_id: 5, permission_description: 'Remove finish goods', isSelected: false, item_id: 'N5' },
                    { permission_id: 6, permission_description: 'Return finish goods', isSelected: false, item_id: 'N6' },
                    { permission_id: 7, permission_description: 'Release finish goods', isSelected: false, item_id: 'N7' },
                    { permission_id: 8, permission_description: 'Delete finish goods', isSelected: false, item_id: 'N8' },
                    { permission_id: 9, permission_description: 'Restore finish goods', isSelected: false, item_id: 'N9' },
                ],
                permissionForeignExchangeRates: [
                    { permission_id: 0, permission_description: 'Foreign Exchange Rates Permissions', isSelected: false, item_id: 'O0', isIndeterminate: false, isTitle: true },
                    { permission_id: 1, permission_description: 'View foreign exchange rate', isSelected: false, item_id: 'O1' },
                    { permission_id: 2, permission_description: 'Edit foreign exchange rate', isSelected: false, item_id: 'O2' },
                ],
                permissionProcessMaintenance: [
                    { permission_id: 0, permission_description: 'Process Maintenance Permissions', isSelected: false, item_id: 'P0', isIndeterminate: false, isTitle: true },
                    { permission_id: 1, permission_description: 'Add new process', isSelected: false, item_id: 'P1' },
                    { permission_id: 2, permission_description: 'View process list', isSelected: false, item_id: 'P2' },
                    { permission_id: 3, permission_description: 'View process details', isSelected: false, item_id: 'P3' },
                    { permission_id: 4, permission_description: 'Edit process details', isSelected: false, item_id: 'P4' },
                    { permission_id: 5, permission_description: 'Add new price bracket', isSelected: false, item_id: 'P5' },
                    { permission_id: 6, permission_description: 'Delete price bracket', isSelected: false, item_id: 'P6' },
                    { permission_id: 7, permission_description: 'Delete process', isSelected: false, item_id: 'P7' },
                    { permission_id: 8, permission_description: 'Restore deleted process', isSelected: false, item_id: 'P8' },
                ],
                activePermissions: [],
                isActivePermission: false,
            }
        },
        methods: {
            createEmployee(){
                Swal.fire({
                    text: 'Are you sure you want to register new user?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Register',
                    width: '25rem',
                })
                .then((result) => {
                    if(result.value){
                        this.form.post('api/user')
                        .then((res) => {
                            Swal.fire(
                                'Registered!',
                                'User successfully registered.',
                                'success'
                            )
                            this.errors = {};
                            this.form.reset();
                            this.loadPermissions();
                            this.resetPermissions();
                            this.activePermissions = [];
                        })
                        .catch(error => {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops!',
                                text: 'User registration failed.',
                            })
                            console.log(error)
                        });
                    }
                })
            },
            getDepartments(){
                axios.get('/api/get-all-departments')
                .then((res) => {
                    this.departments = res.data;
                })
                .catch(error => {
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
                })
                this.form.reset();
                this.loadPermissions();
            },
            isSelectedPermission(permissionDescription){
                if(this.activePermissions.includes(permissionDescription)){
                    for(var i = 0; i < this.activePermissions.length; i++){ 
                        if(this.activePermissions[i] === permissionDescription) {
                            this.activePermissions.splice(i, 1); 
                            this.form.permissions[i][0]['isIndeterminate'] = false;
                        }
                    }
                }
                else{
                    this.activePermissions.push(permissionDescription);
                    for(var i = 0; i < this.form.permissions.length; i++){ 
                        if(this.form.permissions[i][0].permission_description === permissionDescription) {
                            for(var j = 0; j < this.form.permissions[i].length; j++){
                                this.form.permissions[i][j].isSelected = false;
                                this.form.permissions[i][0].isSelected = true;
                            }
                        }
                    }
                }
            },
            handleItemChange(permission_description){
                for(let i = 0; i < this.form.permissions.length; i++){
                    var selectedCount = 0;
                    for(var j = 1; j < this.form.permissions[i].length; j++){
                        if(this.form.permissions[i][0]['permission_description'] === permission_description && this.form.permissions[i][j]['isSelected'] == true){
                            selectedCount++;
                        }
                    }
                    if(selectedCount < this.form.permissions[i].length - 1 && this.form.permissions[i][0]['permission_description'] === permission_description){
                        this.form.permissions[i][0]['isIndeterminate'] = true;
                    }
                    else{
                        this.form.permissions[i][0]['isIndeterminate'] = false;
                    }
                }
            },
            loadPermissions(){
                this.form.permissions = [
                    this.permissionDepartments,
                    this.permissionEmployees,
                    this.permissionCustomers,
                    this.permissionRawMaterialSuppliers,
                    this.permissionSubcontractMaterialSuppliers,
                    this.permissionForecasts,
                    this.permissionOrders,
                    this.permissionComputationSheets,
                    this.permissionQuotations,
                    this.permissionPurchaseOrders,
                    this.permissionSalesSheets,
                    this.permissionRawMaterials,
                    this.permissionSubcontractMaterials,
                    this.permissionFinishGoods,
                    this.permissionForeignExchangeRates,
                    this.permissionProcessMaintenance,
                ];
            },
            resetPermissions(){
                for(var i = 0; i < this.form.permissions.length; i++){ 
                    for(var j = 0; j < this.form.permissions[i].length; j++){
                        this.form.permissions[i][j].isSelected = false;
                    }
                }
            }
        },
        created(){
            this.getDepartments();
            this.loadPermissions();
        },

    }
</script>
