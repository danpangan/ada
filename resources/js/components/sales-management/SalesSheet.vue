<template>
    <div class="container">
        <div class="content-header mb-3" style="border-bottom: 1px solid black;">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h4 class="m-0 text-dark" v-show="isEdit"><strong>Edit Sales Sheet</strong></h4>
                        <h4 class="m-0 text-dark" v-show="!isEdit"><strong>View Sales Sheet</strong></h4>
                    </div>
                    <div class="col-sm-6">
                        <button type="button" class="btn button-standard float-right" @click="$router.push('/sales-sheets')">
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
                            <span>Sales Sheet</span>
                        </div>
                        <h5 class="content-title">Customer</h5>
                        <el-form ref="form" :model="form" label-width="15%" :label-position="`right`">
                            <el-form-item label="Customer Name">
                                <el-col :span="12">
                                    <el-input v-model="form.dummy_length" :disabled="true"></el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Contact Person">
                                <el-col :span="12">
                                    <el-input v-model="form.dummy_length" :disabled="true"></el-input>
                                </el-col>
                            </el-form-item>
                            <h5 class="content-title mt-3">Product</h5>
                            <el-form-item label="Item Code">
                                <el-col :span="12">
                                    <el-input v-model="form.dummy_length" :disabled="readonly"></el-input>
                                </el-col>
                                <el-col :span="12">
                                    <el-tooltip class="item" effect="dark" content="Add product" placement="top">
                                        <router-link :to="'/update-po/'">
                                            <i class="icon el-icon-document-add pointer ml-2"></i>
                                        </router-link>
                                    </el-tooltip>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Customer Name">
                                <el-col :span="12">
                                    <el-select v-model="form.dummy_date" placeholder="Part Number" class="block" :disabled="readonly">
                                        <el-option
                                        v-for="category in $dummy_dropdowns"
                                        :key="category.id"
                                        :label="category.category_name"
                                        :value="category.id">
                                        </el-option>
                                    </el-select>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Product Name">
                                <el-col :span="12">
                                    <el-input v-model="form.dummy_length" :disabled="true"></el-input>
                                </el-col>
                            </el-form-item>
                            <h5 class="content-title mt-3">Purchase Order</h5>
                            <el-form-item label="PO Number">
                                <el-col :span="12">
                                    <el-input v-model="form.dummy_length" :disabled="readonly"></el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Quotation Number">
                                <el-col :span="12">
                                    <el-input v-model="form.dummy_length" :disabled="true"></el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Control Number">
                                <el-col :span="12">
                                    <el-input v-model="form.dummy_length" :disabled="true"></el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Quantity">
                                <el-col :span="4" class="text-right">
                                    <span>Purchase Order</span>
                                </el-col>
                                <el-col :span="3" class="ml-1">
                                    <el-input v-model="form.dummy_length" :disabled="true"></el-input>
                                </el-col>
                                <el-col :span="4" class="text-right">
                                    <span>Forecast (pcs)</span>
                                </el-col>
                                <el-col :span="3" class="ml-1">
                                    <el-input v-model="form.dummy_length" :disabled="true"></el-input>
                                </el-col>
                                <el-col :span="4" class="text-right">
                                    <span>Buffer Stocks (pcs)</span>
                                </el-col>
                                <el-col :span="3" class="ml-1">
                                    <el-input v-model="form.dummy_length" :disabled="true"></el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Total Quantity">
                                <el-col :span="4">
                                    <el-input v-model="form.dummy_length" :disabled="true"></el-input>
                                </el-col>
                            </el-form-item>
                        </el-form>
                    </el-card>

                    <el-card class="box-card mt-3" shadow="hover">
                        <div slot="header">
                            <span>Material List</span>
                        </div>
                        <h5 class="mt-3">Primary Material List</h5>
                        <el-row>
                            <el-col :span="24" class="my-3">
                                <el-table
                                    v-model="form.cs_order.primary_materials"
                                    :data="form.cs_order.primary_materials"
                                    :default-expand-all="true"
                                    style="width: 100%">
                                    <el-table-column type="expand">
                                        <template slot-scope="scope">
                                            <div v-for="supplier_material in scope.row.supplier_materials" :key="supplier_material.id">
                                                <el-row>
                                                    <el-col :span="16" :offset="2">
                                                        <el-checkbox v-model="supplier_material.is_selected" :label="supplier_material.id" @change="csMaterialTotal" :disabled="readonly">{{ supplier_material.supplier.supplier_name }}</el-checkbox>
                                                    </el-col>
                                                    <el-col :span="2">
                                                        <el-input class="misc-input" v-model="supplier_material.supplier_material_quantity" @input="csMaterialTotal" :disabled="readonly"></el-input>
                                                    </el-col>
                                                    <el-col :span="2">
                                                        <el-input class="misc-input" v-model="supplier_material.supplier_material_amount" 
                                                        @input="csMaterialTotal" :disabled="readonly"></el-input>
                                                    </el-col>
                                                    <el-col :span="2" style="padding-right: 5px">
                                                        <el-input class="misc-input" v-model="supplier_material.supplier_material_total" 
                                                        :disabled="true"></el-input>
                                                    </el-col>
                                                </el-row>
                                            </div>
                                        </template>
                                    </el-table-column>
                                    <el-table-column
                                    prop="material_name"
                                    label="Material Name"
                                    width="370">
                                        <template slot-scope="scope">
                                            <el-tooltip class="item" effect="dark" content="View material details" placement="top">
                                                <span class="expandable-row-name" @click="viewDetails(form.cs_order.primary_materials[scope.$index].material_id, 'primary')">{{ form.cs_order.primary_materials[scope.$index].material_name }}</span>
                                            </el-tooltip>
                                        </template>
                                    </el-table-column>
                                    <el-table-column
                                    prop="order_material_length"
                                    label="Dimension"
                                    width="150">
                                    <template slot-scope="scope">
                                        {{ dimensionFormat(
                                            form.cs_order.primary_materials[scope.$index].order_material_length, 
                                            form.cs_order.primary_materials[scope.$index].order_material_length_unit,
                                            form.cs_order.primary_materials[scope.$index].order_material_width,
                                            form.cs_order.primary_materials[scope.$index].order_material_width_unit,
                                        ) }}
                                    </template>
                                    </el-table-column>
                                    <el-table-column
                                    label="Outs"
                                    width="120">
                                    <template slot-scope="scope">
                                        <el-col :span="24">
                                            <el-input v-model="form.cs_order.primary_materials[scope.$index].cs_material_outs" :disabled="readonly"></el-input>
                                        </el-col>
                                    </template>
                                    </el-table-column>
                                    <el-table-column
                                    label="Actual"
                                    width="120">
                                    <template slot-scope="scope">
                                        <el-col :span="24">
                                            <el-input v-model="form.cs_order.primary_materials[scope.$index].cs_material_actual" :disabled="readonly"></el-input>
                                        </el-col>
                                    </template>
                                    </el-table-column>
                                     <el-table-column
                                    label="PRM"
                                    width="120">
                                    <template slot-scope="scope">
                                        <el-col :span="24">
                                            <el-input v-model="form.cs_order.primary_materials[scope.$index].cs_material_prm" :disabled="readonly"></el-input>
                                        </el-col>
                                    </template>
                                    </el-table-column>
                                    <el-table-column
                                    label="Quantity"
                                    width="120">
                                    <template slot-scope="scope">
                                        <el-col :span="24">
                                            <el-input v-model="form.cs_order.primary_materials[scope.$index].total_material_quantity" :disabled="true"></el-input>
                                        </el-col>
                                    </template>
                                    </el-table-column>
                                    <el-table-column
                                    :label="amountLabel()"
                                    width="120">
                                    </el-table-column>
                                    <el-table-column
                                    :label="totalLabel()"
                                    width="120">
                                    <template slot-scope="scope">
                                        <el-col :span="24">
                                            <el-input v-model="form.cs_order.primary_materials[scope.$index].total_material_amount" :disabled="true"></el-input>
                                        </el-col>
                                    </template>
                                    </el-table-column>
                                </el-table>
                            </el-col>
                        </el-row>

                        <h5 class="mt-3">Secondary Material List</h5>
                        <el-row>
                            <el-col :span="24" class="my-3">
                                <el-table
                                    v-model="form.cs_order.secondary_materials"
                                    :data="form.cs_order.secondary_materials"
                                    :default-expand-all="true"
                                    row-class-name="expandable-row"
                                    style="width: 100%">
                                    <el-table-column type="expand">
                                        <template slot-scope="scope">
                                            <div v-for="supplier_material in scope.row.supplier_materials" :key="supplier_material.id">
                                                <el-row>
                                                    <el-col :span="13" :offset="2">
                                                        <el-checkbox v-model="supplier_material.is_selected" :label="supplier_material.id" @change="csMaterialTotal" :disabled="readonly">{{ supplier_material.supplier.supplier_name }}</el-checkbox>
                                                    </el-col>
                                                    <el-col :span="3">
                                                        <el-input class="misc-input" v-model="supplier_material.supplier_material_quantity" @input="csMaterialTotal" :disabled="readonly"></el-input>
                                                    </el-col>
                                                    <el-col :span="3">
                                                        <el-input class="misc-input" v-model="supplier_material.supplier_material_amount" 
                                                        @input="csMaterialTotal" :disabled="readonly"></el-input>
                                                    </el-col>
                                                    <el-col :span="3">
                                                        <el-input class="misc-input" v-model="supplier_material.supplier_material_total" 
                                                        :disabled="true"></el-input>
                                                    </el-col>
                                                </el-row>
                                            </div>
                                        </template>
                                    </el-table-column>
                                    <el-table-column
                                    label="Material Name"
                                    width="390">
                                        <template slot-scope="scope">
                                            <el-tooltip class="item" effect="dark" content="View material details" placement="top">
                                                <span class="expandable-row-name" @click="viewDetails(form.cs_order.secondary_materials[scope.$index].material_id, 'secondary')">{{ form.cs_order.secondary_materials[scope.$index].material_name }}</span>
                                            </el-tooltip>
                                        </template>
                                    </el-table-column>
                                    <el-table-column
                                    prop="order_material_length"
                                    label="Dimension"
                                    width="150">
                                    <template slot-scope="scope">
                                        {{ dimensionFormat(
                                            form.cs_order.secondary_materials[scope.$index].order_material_length, 
                                            form.cs_order.secondary_materials[scope.$index].order_material_length_unit,
                                            form.cs_order.secondary_materials[scope.$index].order_material_width,
                                            form.cs_order.secondary_materials[scope.$index].order_material_width_unit,
                                        ) }}
                                    </template>
                                    </el-table-column>
                                    <el-table-column
                                    label="Quantity"
                                    width="120">
                                    <template slot-scope="scope">
                                        <el-col :span="24">
                                            <el-input v-model="form.cs_order.secondary_materials[scope.$index].total_material_quantity" :disabled="true"></el-input>
                                        </el-col>
                                    </template>
                                    </el-table-column>
                                    <el-table-column
                                    :label="amountLabel()"
                                    width="120">
                                    </el-table-column>
                                    <el-table-column
                                    :label="totalLabel()"
                                    width="120">
                                    <template slot-scope="scope">
                                        <el-col :span="24">
                                            <el-input v-model="form.cs_order.secondary_materials[scope.$index].total_material_amount" :disabled="true"></el-input>
                                        </el-col>
                                    </template>
                                    </el-table-column>
                                </el-table>
                            </el-col>
                        </el-row>

                        <h5 class="mt-3">Subcontractor Material</h5>
                        <el-row>
                            <el-col :span="24" class="my-3">
                                <el-table
                                    v-model="form.cs_order.subcontractor_material"
                                    :data="form.cs_order.subcontractor_material"
                                    :default-expand-all="true"
                                    row-class-name="expandable-row"
                                    style="width: 100%">
                                    <el-table-column type="expand">
                                        <template slot-scope="scope">
                                            <div v-for="supplier_sc_material in scope.row.supplier_sc_materials" :key="supplier_sc_material.id">
                                                <el-row>
                                                    <el-col :span="13" :offset="2">
                                                        <el-checkbox v-model="supplier_sc_material.is_selected" :label="supplier_sc_material.id" @change="csMaterialTotal" :disabled="readonly">{{ supplier_sc_material.supplier.supplier_name }}</el-checkbox>
                                                    </el-col>
                                                    <el-col :span="3">
                                                        <el-input class="misc-input" v-model="supplier_sc_material.supplier_sc_material_quantity" @input="csMaterialTotal" :disabled="readonly"></el-input>
                                                    </el-col>
                                                    <el-col :span="3">
                                                        <el-input class="misc-input" v-model="supplier_sc_material.supplier_sc_material_amount" 
                                                        @input="csMaterialTotal" :disabled="readonly"></el-input>
                                                    </el-col>
                                                    <el-col :span="3">
                                                        <el-input class="misc-input" v-model="supplier_sc_material.supplier_sc_material_total" 
                                                        :disabled="true"></el-input>
                                                    </el-col>
                                                </el-row>
                                            </div>
                                        </template>
                                    </el-table-column>
                                    <el-table-column
                                    label="Material Name"
                                    width="390">
                                        <template slot-scope="scope">
                                            <el-tooltip class="item" effect="dark" content="View material details" placement="top">
                                                <span class="expandable-row-name" @click="viewDetails(form.cs_order.subcontractor_material[scope.$index].material_id, 'subcon')">{{ form.cs_order.subcontractor_material[scope.$index].material_name }}</span>
                                            </el-tooltip>
                                        </template>
                                    </el-table-column>
                                    <el-table-column
                                    prop="order_material_length"
                                    label="Dimension"
                                    width="150">
                                    <template slot-scope="scope">
                                        {{ dimensionFormat(
                                            form.cs_order.subcontractor_material[scope.$index].order_material_length, 
                                            form.cs_order.subcontractor_material[scope.$index].order_material_length_unit,
                                            form.cs_order.subcontractor_material[scope.$index].order_material_width,
                                            form.cs_order.subcontractor_material[scope.$index].order_material_width_unit,
                                        ) }}
                                    </template>
                                    </el-table-column>
                                    <el-table-column
                                    label="Quantity"
                                    width="120">
                                    <template slot-scope="scope">
                                        <el-col :span="24">
                                            <el-input v-model="form.cs_order.subcontractor_material[scope.$index].total_sc_material_quantity" :disabled="true"></el-input>
                                        </el-col>
                                    </template>
                                    </el-table-column>
                                    <el-table-column
                                    :label="amountLabel()"
                                    width="120">
                                    </el-table-column>
                                    <el-table-column
                                    :label="totalLabel()"
                                    width="120">
                                    <template slot-scope="scope">
                                        <el-col :span="24">
                                            <el-input v-model="form.cs_order.subcontractor_material[scope.$index].total_sc_material_amount" :disabled="true"></el-input>
                                        </el-col>
                                    </template>
                                    </el-table-column>
                                </el-table>
                            </el-col>
                        </el-row>

                        <el-row class="mt-3">
                            <el-col :span="12">
                                <span>Subtotal Amount</span>
                            </el-col>
                            <el-col :span="12" class="text-right">
                                <span>{{ subtotalMaterialAmount }}</span>
                            </el-col>
                        </el-row>
                        <el-row>
                            <el-col :span="18">
                                <span>10% Material Lost</span>
                            </el-col>
                            <el-col :span="6" class="text-right">
                                <el-input class="misc-input input-right" v-model="form.cs.cs_material_loss" :disabled="readonly">
                                    <template slot="prepend">{{ form.cs.cs_currency }}</template>
                                </el-input>
                            </el-col>
                        </el-row>
                        <el-col>
                            <hr class="hr1">
                        </el-col>
                        <el-row class="mt-3">
                            <el-col :span="12">
                                <span>Total Amount (Material)</span>
                            </el-col>
                            <el-col :span="12" class="text-right">
                                <span>{{ totalMaterialAmount }}</span>
                            </el-col>
                        </el-row>
                    </el-card>

                    <el-card class="box-card mt-3" shadow="hover">
                        <div slot="header">
                            <span>Printing List</span>
                        </div>
                        <el-row>
                            <el-col :span="24" class="my-3">
                                <el-table
                                    v-model="form.cs_order.prints"
                                    :data="form.cs_order.prints"
                                    :default-expand-all="true"
                                    row-class-name="expandable-row"
                                    style="width: 100%">
                                    <el-table-column type="expand">
                                        <template slot-scope="scope">
                                            <div v-for="supplier_material in scope.row.supplier_materials" :key="supplier_material.id">
                                                <el-row>
                                                    <el-col :span="13" :offset="2">
                                                        <el-checkbox v-model="supplier_material.is_selected" :label="supplier_material.id" @change="csPrintingTotal" :disabled="readonly">{{ supplier_material.supplier.supplier_name }}</el-checkbox>
                                                    </el-col>
                                                    <el-col :span="3">
                                                        <el-input class="misc-input" v-model="supplier_material.supplier_material_quantity" @input="csPrintingTotal" :disabled="readonly"></el-input>
                                                    </el-col>
                                                    <el-col :span="3">
                                                        <el-input class="misc-input" v-model="supplier_material.supplier_material_amount" 
                                                        @input="csPrintingTotal" :disabled="readonly"></el-input>
                                                    </el-col>
                                                    <el-col :span="3">
                                                        <el-input class="misc-input" v-model="supplier_material.supplier_material_total" 
                                                        :disabled="true"></el-input>
                                                    </el-col>
                                                </el-row>
                                            </div>
                                        </template>
                                    </el-table-column>
                                    <el-table-column
                                    label="Printing"
                                    width="370">
                                        <template slot-scope="scope">
                                            <el-tooltip class="item" effect="dark" :content="'Material Name: '+form.cs_order.prints[scope.$index].material_name" placement="top">
                                                <span class="expandable-row-name" @click="viewDetails(form.cs_order.prints[scope.$index].material_id, 'print')">{{ form.cs_order.prints[scope.$index].order_print_item }}</span>
                                            </el-tooltip>
                                        </template>
                                    </el-table-column>
                                    <el-table-column
                                    prop="order_print_outs"
                                    label="Outs"
                                    width="75">
                                    </el-table-column>
                                    <el-table-column
                                    prop="order_material_length"
                                    label="Color F/B"
                                    width="95">
                                        <template slot-scope="scope">
                                            {{ form.cs_order.prints[scope.$index].order_print_front + ' / ' + form.cs_order.prints[scope.$index].order_print_back }}
                                        </template>
                                    </el-table-column>
                                    <el-table-column
                                    label="Quantity"
                                    width="120">
                                    <template slot-scope="scope">
                                        <el-col :span="24">
                                            <el-input v-model="form.cs_order.prints[scope.$index].total_material_quantity" :disabled="true"></el-input>
                                        </el-col>
                                    </template>
                                    </el-table-column>
                                    <el-table-column
                                    :label="amountLabel()"
                                    width="120">
                                    </el-table-column>
                                    <el-table-column
                                    :label="totalLabel()"
                                    width="120">
                                    <template slot-scope="scope">
                                        <el-col :span="24">
                                            <el-input v-model="form.cs_order.prints[scope.$index].total_material_amount" :disabled="true"></el-input>
                                        </el-col>
                                    </template>
                                    </el-table-column>
                                </el-table>
                            </el-col>
                        </el-row>
                        <el-col>
                            <hr class="hr1">
                        </el-col>
                        <el-row class="mt-3">
                            <el-col :span="12">
                                <span>Total Amount (Printing)</span>
                            </el-col>
                            <el-col :span="12" class="text-right">
                                <span>{{ totalCSPrinting }}</span>
                            </el-col>
                        </el-row>
                    </el-card>

                    <el-card class="box-card mt-3" shadow="hover">
                        <div slot="header">
                            <span>Processing List</span>
                        </div>
                        <el-row>
                            <h5>Primary Process</h5>
                            <el-table
                                v-model="form.cs_order.process_list"
                                :data="form.cs_order.process_list"
                                style="width: 100%">
                                <el-table-column
                                prop="process_name"
                                label="Process"
                                :span="4">
                                </el-table-column>
                                <el-table-column
                                prop="order_process_description"
                                class="content-title"
                                label="Description"
                                :span="2">
                                </el-table-column>
                                <el-table-column
                                label="Quantity"
                                class="content-title"
                                :span="2">
                                    <template slot-scope="scope">
                                    <el-col :span="24">
                                        <el-input v-model="form.cs_order.process_list[scope.$index].total_process_quantity" @input="csProcessTotal" :disabled="readonly"></el-input>
                                    </el-col>
                                    </template>
                                </el-table-column>
                                <el-table-column
                                :label="amountLabel()"
                                class="content-title"
                                :span="2">
                                    <template slot-scope="scope">
                                    <el-col :span="24">
                                        <el-input v-model="form.cs_order.process_list[scope.$index].total_process_amount" @input="csProcessTotal" :disabled="readonly"></el-input>
                                    </el-col>
                                    </template>
                                </el-table-column>
                                <el-table-column
                                :label="totalLabel()"
                                class="content-title"
                                :span="2">
                                    <template slot-scope="scope">
                                    <el-col :span="24">
                                        <el-input v-model="form.cs_order.process_list[scope.$index].process_subtotal" @input="csProcessTotal" :disabled="true"></el-input>
                                    </el-col>
                                    </template>
                                </el-table-column>
                            </el-table>
                        </el-row>
                        <el-row>
                            <h5 class="mt-3">Subcontractor Process</h5>
                            <el-table
                                v-model="form.cs_order.process_subcon"
                                :data="form.cs_order.process_subcon"
                                style="width: 100%">
                                <el-table-column
                                prop="order_process_subcon"
                                label="Process Name"
                                :span="4">
                                </el-table-column>
                                <el-table-column
                                prop="order_process_subcon_description"
                                label="Description"
                                :span="2">
                                </el-table-column>
                                <el-table-column
                                label="Quantity"
                                class="content-title"
                                :span="2">
                                    <template slot-scope="scope">
                                    <el-col :span="24">
                                        <el-input v-model="form.cs_order.process_subcon[scope.$index].total_process_quantity" @input="csProcessTotal" :disabled="readonly"></el-input>
                                    </el-col>
                                    </template>
                                </el-table-column>
                                <el-table-column
                                :label="amountLabel()"
                                class="content-title"
                                :span="2">
                                    <template slot-scope="scope">
                                    <el-col :span="24">
                                        <el-input v-model="form.cs_order.process_subcon[scope.$index].total_process_amount" @input="csProcessTotal" :disabled="readonly"></el-input>
                                    </el-col>
                                    </template>
                                </el-table-column>
                                <el-table-column
                                :label="totalLabel()"
                                class="content-title"
                                :span="2">
                                    <template slot-scope="scope">
                                    <el-col :span="24">
                                        <el-input v-model="form.cs_order.process_subcon[scope.$index].process_subtotal" @input="csProcessTotal" :disabled="true"></el-input>
                                    </el-col>
                                    </template>
                                </el-table-column>
                            </el-table>
                        </el-row>
                        <el-col>
                            <hr class="hr1">
                        </el-col>
                        <el-row class="mt-3">
                            <el-col :span="19">
                                <span>Total Amount (Processing)</span>
                            </el-col>
                            <el-col :span="5" class="text-right">
                                    <span>{{ totalCSProcess }}</span>
                            </el-col>
                        </el-row>
                    </el-card>

                    <el-card class="box-card mt-3" shadow="hover">
                        <div slot="header">
                            <span>Miscellaneous</span>
                        </div>
                        <el-row class="content-title">
                            <el-col :span="5" class="text-center">
                                <strong>Miscellaneous</strong>
                            </el-col>
                            <el-col :span="10" class="text-center">
                                <strong>Description</strong>
                            </el-col>
                            <el-col :span="3" class="text-center">
                                <strong>Quantity</strong>
                            </el-col>
                            <el-col :span="3" class="text-center">
                                <strong>{{ amountLabel() }}</strong>
                            </el-col>
                            <el-col :span="3" class="text-center">
                                <strong>{{ totalLabel() }}</strong><br>
                            </el-col>
                        </el-row>
                        <el-row class="mt-3">
                            <el-col :span="5">
                                <el-checkbox v-model="form.cs_misc.cs_is_label" :disabled="readonly">Label</el-checkbox>
                            </el-col>
                            <el-col :span="10" :inline="true">
                                <el-checkbox v-model="form.cs_misc.cs_is_package_label" :disabled="isCheckMiscLabel || readonly">Packaging Label</el-checkbox>
                                <el-checkbox v-model="form.cs_misc.cs_is_item_label" :disabled="isCheckMiscLabel || readonly">Item Label</el-checkbox>
                            </el-col>
                            <el-col :span="3">
                                <el-input v-model="form.cs_misc.cs_qty_label" class="misc-input" :disabled="isCheckMiscLabel || readonly"></el-input>
                            </el-col>
                            <el-col :span="3">
                                <el-input v-model="form.cs_misc.cs_unit_amt_label" class="misc-input" :disabled="isCheckMiscLabel || readonly"></el-input>
                            </el-col>
                            <el-col :span="3">
                                <el-input v-model="form.cs_misc.cs_total_amt_label" class="misc-input" :disabled="true"></el-input>
                            </el-col>
                        </el-row>
                        <el-row>
                            <el-col :span="5">
                                <el-checkbox v-model="form.cs_misc.cs_is_package" :disabled="readonly">Packaging</el-checkbox>
                            </el-col>
                            <el-col :span="10">
                                <el-row :inline="true">
                                    <el-col :span="8">
                                        <el-checkbox v-model="form.cs_misc.cs_is_packaging_cartoon" :disabled="isCheckMiscPackaging || readonly">Cartoon</el-checkbox>
                                    </el-col>
                                    <el-col :span="8" class="text-right">
                                        <span class="ml-3">Qty/box</span>
                                    </el-col>
                                    <el-col :span="8">
                                        <el-input v-model="form.cs_misc.cs_packaging_cartoon_qty_per_box" class="misc-input" :disabled="isCheckMiscPackaging || readonly"></el-input>
                                    </el-col>
                                </el-row>
                            </el-col>
                            <el-col :span="3">
                                <el-input v-model="form.cs_misc.cs_qty_package" class="misc-input" :disabled="isCheckMiscPackaging || readonly"></el-input>
                            </el-col>
                            <el-col :span="3">
                                <el-input v-model="form.cs_misc.cs_unit_amt_package" class="misc-input" :disabled="isCheckMiscPackaging || readonly"></el-input>
                            </el-col>
                            <el-col :span="3">
                                <el-input v-model="form.cs_misc.cs_total_amt_package" class="misc-input" :disabled="true"></el-input>
                            </el-col>
                        </el-row>
                        <el-row>
                            <el-col :span="10" :offset="5">
                                <el-row :inline="true">
                                    <el-col :span="8">
                                        <el-checkbox v-model="form.cs_misc.cs_is_packaging_others" :disabled="isCheckMiscPackaging || readonly">Others</el-checkbox>
                                    </el-col>
                                    <el-col :span="16">
                                        <el-input v-model="form.cs_misc.cs_packing_others_desc" class="misc-input" :disabled="isCheckMiscPackaging || readonly"></el-input>
                                    </el-col>
                                </el-row>
                            </el-col>
                        </el-row>
                        <el-row>
                            <el-col :span="5">
                                <el-checkbox v-model="form.cs_misc.cs_is_transpo" :disabled="readonly">Transportation</el-checkbox>
                            </el-col>
                            <el-col :span="10">
                                <el-row :inline="true">
                                    <el-col :span="24">
                                        <el-input v-model="form.cs_misc.cs_transpo_desc" class="misc-input" :disabled="isCheckMiscTranspo || readonly"></el-input>
                                    </el-col>
                                </el-row>
                            </el-col>
                            <el-col :span="3">
                                <el-input v-model="form.cs_misc.cs_qty_transpo" class="misc-input" :disabled="isCheckMiscTranspo || readonly"></el-input>
                            </el-col>
                            <el-col :span="3">
                                <el-input v-model="form.cs_misc.cs_unit_amt_transpo" class="misc-input" :disabled="isCheckMiscTranspo || readonly"></el-input>
                            </el-col>
                            <el-col :span="3">
                                <el-input v-model="form.cs_misc.cs_total_amt_transpo" class="misc-input" :disabled="true"></el-input>
                            </el-col>
                        </el-row>
                        <el-row>
                            <el-col :span="5">
                                <el-checkbox v-model="form.cs_misc.cs_is_layout" :disabled="readonly">Layout</el-checkbox>
                            </el-col>
                            <el-col :span="10">
                                <el-row :inline="true">
                                    <el-col :span="24">
                                        <el-input v-model="form.cs_misc.cs_layout_desc" class="misc-input" :disabled="isCheckMiscLayout || readonly"></el-input>
                                    </el-col>
                                </el-row>
                            </el-col>
                            <el-col :span="3">
                                <el-input v-model="form.cs_misc.cs_qty_layout" class="misc-input" :disabled="isCheckMiscLayout || readonly"></el-input>
                            </el-col>
                            <el-col :span="3">
                                <el-input v-model="form.cs_misc.cs_unit_amt_layout" class="misc-input" :disabled="isCheckMiscLayout || readonly"></el-input>
                            </el-col>
                            <el-col :span="3">
                                <el-input v-model="form.cs_misc.cs_total_amt_layout" class="misc-input" :disabled="true"></el-input>
                            </el-col>
                        </el-row>
                        <el-row>
                            <el-col :span="5">
                                <el-checkbox v-model="form.cs_misc.cs_is_film" :disabled="readonly">Film</el-checkbox>
                            </el-col>
                            <el-col :span="10">
                                <el-row :inline="true">
                                    <el-col :span="24">
                                        <el-input v-model="form.cs_misc.cs_film_desc" class="misc-input" :disabled="isCheckMiscFilm || readonly"></el-input>
                                    </el-col>
                                </el-row>
                            </el-col>
                            <el-col :span="3">
                                <el-input v-model="form.cs_misc.cs_qty_film" class="misc-input" :disabled="isCheckMiscFilm || readonly"></el-input>
                            </el-col>
                            <el-col :span="3">
                                <el-input v-model="form.cs_misc.cs_unit_amt_film" class="misc-input" :disabled="isCheckMiscFilm || readonly"></el-input>
                            </el-col>
                            <el-col :span="3">
                                <el-input v-model="form.cs_misc.cs_total_amt_film" class="misc-input" :disabled="true"></el-input>
                            </el-col>
                        </el-row>
                        <el-row>
                            <el-col :span="5">
                                <el-checkbox v-model="form.cs_misc.cs_is_plate" :disabled="readonly">Plate</el-checkbox>
                            </el-col>
                            <el-col :span="10">
                                <el-row :inline="true">
                                    <el-col :span="24">
                                        <el-input v-model="form.cs_misc.cs_plate_desc" class="misc-input" :disabled="isCheckMiscPlate || readonly"></el-input>
                                    </el-col>
                                </el-row>
                            </el-col>
                            <el-col :span="3">
                                <el-input v-model="form.cs_misc.cs_qty_plate" class="misc-input" :disabled="isCheckMiscPlate || readonly"></el-input>
                            </el-col>
                            <el-col :span="3">
                                <el-input v-model="form.cs_misc.cs_unit_amt_plate" class="misc-input" :disabled="isCheckMiscPlate || readonly"></el-input>
                            </el-col>
                            <el-col :span="3">
                                <el-input v-model="form.cs_misc.cs_total_amt_plate" class="misc-input" :disabled="true"></el-input>
                            </el-col>
                        </el-row>
                        <el-row>
                            <el-col :span="5">
                                <el-checkbox v-model="form.cs_misc.cs_is_diecut" :disabled="readonly">Die Cut</el-checkbox>
                            </el-col>
                            <el-col :span="10">
                                <el-row :inline="true">
                                    <el-col :span="24">
                                        <el-input v-model="form.cs_misc.cs_diecut_desc" class="misc-input" :disabled="isCheckMiscDiecut || readonly"></el-input>
                                    </el-col>
                                </el-row>
                            </el-col>
                            <el-col :span="3">
                                <el-input v-model="form.cs_misc.cs_qty_diecut" class="misc-input" :disabled="isCheckMiscDiecut || readonly"></el-input>
                            </el-col>
                            <el-col :span="3">
                                <el-input v-model="form.cs_misc.cs_unit_amt_diecut" class="misc-input" :disabled="isCheckMiscDiecut || readonly"></el-input>
                            </el-col>
                            <el-col :span="3">
                                <el-input v-model="form.cs_misc.cs_total_amt_diecut" class="misc-input" :disabled="true"></el-input>
                            </el-col>
                        </el-row>
                        <el-row>
                            <el-col :span="5">
                                <el-checkbox v-model="form.cs_misc.cs_is_peza" :disabled="readonly">PEZA</el-checkbox>
                            </el-col>
                            <el-col :span="10">
                                <el-row :inline="true">
                                    <el-col :span="24">
                                        <el-input v-model="form.cs_misc.cs_peza_desc" class="misc-input" :disabled="isCheckMiscPEZA || readonly"></el-input>
                                    </el-col>
                                </el-row>
                            </el-col>
                            <el-col :span="3">
                                <el-input v-model="form.cs_misc.cs_qty_peza" class="misc-input" :disabled="isCheckMiscPEZA || readonly"></el-input>
                            </el-col>
                            <el-col :span="3">
                                <el-input v-model="form.cs_misc.cs_unit_amt_peza" class="misc-input" :disabled="isCheckMiscPEZA || readonly"></el-input>
                            </el-col>
                            <el-col :span="3">
                                <el-input v-model="form.cs_misc.cs_total_amt_peza" class="misc-input" :disabled="true"></el-input>
                            </el-col>
                        </el-row>
                        <div v-for="(misc_other, index) in form.cs_misc_others" :key="misc_other.id">
                            <el-row>
                                <el-col :span="10" :offset="5">
                                    <el-row :inline="true">
                                        <el-col :span="24">
                                            <el-input v-model="misc_other.other_misc_description" class="misc-input" placeholder="Other miscellaneous" :disabled="readonly"></el-input>
                                        </el-col>
                                    </el-row>
                                </el-col>
                                <el-col :span="3">
                                    <el-input v-model="misc_other.other_misc_quantity" class="misc-input" :disabled="readonly"></el-input>
                                </el-col>
                                <el-col :span="3">
                                    <el-input v-model="misc_other.other_misc_price" class="misc-input" :disabled="readonly"></el-input>
                                </el-col>
                                <el-col :span="3">
                                    <el-input :value="totalCSMiscOther(misc_other.id, misc_other.other_misc_quantity, misc_other.other_misc_price, index)" class="misc-input" :disabled="true"></el-input>
                                </el-col>
                            </el-row>
                        </div>
                        <div v-show="isEdit">
                            <el-row>
                                <el-col :span="24" class="text-center">
                                    <button type="button" class="btn button-standard ml-3" size="mini" @click="addCSMiscOther">
                                        <i class="el-icon-plus"></i> Add other misc
                                    </button>
                                    <button type="button" class="btn btn-danger ml-3" size="mini" @click="removeCSMiscOther" v-show="this.form.cs_misc_others.length > 0">
                                        <i class="el-icon-minus"></i> Remove row
                                    </button>
                                </el-col>
                            </el-row>
                        </div>

                        <el-col>
                            <hr class="hr1">
                        </el-col>
                        <el-row>
                            <el-col :span="12">
                                <span>Total Amount (Miscellaneous)</span>
                            </el-col>
                            <el-col :span="12" class="text-right">
                                <span>{{ totalCSMisc }}</span>
                            </el-col>
                        </el-row>
                    </el-card>

                    <el-card class="box-card mt-3" shadow="hover">
                        <div slot="header">
                            <span>Pricing</span>
                        </div>
                        <el-form ref="form" :model="form" label-width="15%" :label-position="`right`">
                            <el-form-item label="Total Cost">
                                <el-col :span="6">
                                    <el-input v-model="form.dummy_length" :disabled="true"></el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Cost Rate">
                                <el-col :span="6">
                                    <el-input v-model="form.dummy_length" :disabled="true"></el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Gross Sales">
                                <el-col :span="6">
                                    <el-input v-model="form.dummy_length" :disabled="true"></el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Sales Price">
                                <el-col :span="6">
                                    <el-input v-model="form.dummy_length" :disabled="true"></el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Gross Profit">
                                <el-col :span="6">
                                    <el-input v-model="form.dummy_length" :disabled="true"></el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Quotation Quantity">
                                <el-col :span="6">
                                    <el-input v-model="form.dummy_length" :disabled="true"></el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Forms of Payment">
                                <el-col :span="4">
                                    <el-checkbox v-model="checkbox_dummy">Transfer</el-checkbox>
                                </el-col>
                                <el-col :span="4">
                                    <el-checkbox v-model="checkbox_dummy">Cash</el-checkbox>
                                </el-col>
                                <el-col :span="4">
                                    <el-checkbox v-model="checkbox_dummy">Check</el-checkbox>
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
                        <el-row v-show="isEdit">
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
                                    :data="form.ss_evaluation_checkers"
                                    style="width: 100%">
                                    <el-table-column
                                    label="Name">
                                        <template slot-scope="scope">
                                            {{ form.ss_evaluation_checkers[scope.$index].first_name + ' ' + form.ss_evaluation_checkers[scope.$index].last_name }}
                                        </template>
                                    </el-table-column>
                                    <el-table-column label="Action" fixed="right" width="70" v-if="isEdit">
                                        <template slot-scope="scope">
                                            <el-tooltip class="item" effect="dark" content="Delete" placement="top">
                                                <i class="icon el-icon-delete pointer ml-2" @click="deleteSSEvaluator(form.ss_evaluation_checkers[scope.$index].evaluator_id, 'checker')"></i>
                                            </el-tooltip>
                                        </template>
                                    </el-table-column>
                                    <el-table-column label="Status" fixed="right" width="140" v-else>
                                        <template slot-scope="scope">
                                            <i :class="'icon '+evaluationStatusIcon(form.ss_evaluation_checkers[scope.$index].evaluation_status)+' ml-2'"></i>
                                            {{ evaluationStatus(form.ss_evaluation_checkers[scope.$index].evaluation_status) }}
                                        </template>
                                    </el-table-column>
                                </el-table>
                            </el-col>
                            <el-col :span="12">
                                <el-table
                                    :data="form.ss_evaluation_approvers"
                                    style="width: 100%">
                                    <el-table-column
                                    label="Name">
                                        <template slot-scope="scope">
                                            {{ form.ss_evaluation_approvers[scope.$index].first_name + ' ' + form.ss_evaluation_approvers[scope.$index].last_name }}
                                        </template>
                                    </el-table-column>
                                    <el-table-column label="Action" fixed="right" width="70" v-if="isEdit">
                                        <template slot-scope="scope">
                                            <el-tooltip class="item" effect="dark" content="Delete" placement="top">
                                                <i class="icon el-icon-delete pointer ml-2" @click="deleteSSEvaluator(form.ss_evaluation_approvers[scope.$index].evaluator_id, 'approver')"></i>
                                            </el-tooltip>
                                        </template>
                                    </el-table-column>
                                    <el-table-column label="Status" fixed="right" width="140" v-else>
                                        <template slot-scope="scope">
                                            <i :class="'icon '+evaluationStatusIcon(form.ss_evaluation_approvers[scope.$index].evaluation_status)+' ml-2'"></i>
                                            {{ evaluationStatus(form.ss_evaluation_approvers[scope.$index].evaluation_status) }}
                                        </template>
                                    </el-table-column>
                                </el-table>
                            </el-col>
                        </el-row>
                        <br>
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
                            <el-button type="success" icon="el-icon-success" @click="evaluateSS" v-if="isEvaluated(evaluationForm.evaluation_status)" :disabled="includesRejected || isChecked || preventApproval || isApproved">{{ evaluationForm.evaluation_type == 1 ? 'Check' : 'Approve' }}</el-button>
                            <el-button type="danger" icon="el-icon-remove-outline" @click="rejectSSEvaluation()" v-if="isEvaluated(evaluationForm.evaluation_status)" :disabled="isChecked || preventApproval || isApproved">{{ evaluationForm.evaluation_status == 5 ? 'Revert' : 'Reject' }}</el-button>
                            <el-button type="danger" icon="el-icon-remove-outline" @click="evaluateSS" :disabled="preventCheckDiscarding" v-else>Discard</el-button>
                        </div>
                    </el-card>

                    <div class="outside-card-button" v-if="isEdit">
                        <el-button type="primary" v-on:click="updateSS"><i class="el-icon-circle-check"></i> Update</el-button>
                        <el-button type="warning" v-on:click="resetFields"><i class="fas fa-file-pdf"></i> Preview</el-button>
                        <el-button type="danger" @click="$router.back(-1)"><i class="el-icon-close"></i> Cancel</el-button>
                    </div>
                    <div class="outside-card-button" v-else>
                        <router-link :to="'/update-ss/'+this.$route.params.ss_id">
                            <el-button type="primary" @click="toEdit"><i class="el-icon-edit-outline"></i> Edit</el-button>
                        </router-link>
                        <router-link :to="'/sales-sheets'">
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
    import moment from 'moment';
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

                    cs: {},
                    cs_misc: {},
                    cs_misc_others: [],
                    ss_evaluation_checkers: [],
                    ss_evaluation_approvers: [],
                    cs_order: [],
                    cs_markup: [],
                    is_new_version: false,
                    ss: '',
                    ss_evaluation_checkers: [],
                    ss_evaluation_approvers: [],
                    ss_evaluation_remarks: []
                }),
                primaryMaterialList: [
                    { id: 1, name: 'Test', address: 'Test' }
                ],
                checkbox_dummy: true,
                evaluationForm: new Form({
                    id: '',
                    cs_id: '',
                    evaluator_id: '',
                    message: '',
                }),
                isEdit: false,
                isLoading: false,

                clients: [],
                orders: [],
                other_percentage: '',
                evaluator_checkers: [],
                evaluator_approvers: [],
                draft_evaluator_checker: '',
                draft_evaluator_approver: '',
                evaluation_remarks: [],
                exchange_rates: [],
                other_markup_percentage: 0,
                other_markups: _.remove(Array.from({ length: 101 }, (v, i) => i), function(n){
                    return n != 10 && n != 15 && n != 20 && n != 25;
                }),
                original_quantity: 0,
                original_total_cs: 0,
                checkbox_dummy: false,
                customer_name: '',
                contact_person: '',
                evaluationForm: new Form({
                    id: '',
                    cs_id: '',
                    evaluator_id: '',
                    message: '',
                }),
            }
        },
        methods: {
            updateSS() {
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
                        this.form.put('/api/sales-sheet/'+this.$route.params.ss_id)
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
            getCS(){
                // axios.get('/api/cs/'+this.$route.params.cs_id)
                axios.get('/api/cs/'+42)
                .then((res) => {
                    this.form.cs = res.data.cs;
                    this.form.cs_order = res.data.cs_order;
                    this.form.cs_misc =  res.data.cs_misc;
                    this.form.cs_misc_others =  res.data.cs_misc_others;

                    this.form.cs_misc.cs_is_label = !!res.data.cs_misc.cs_is_label;
                    this.form.cs_misc.cs_is_package = !!res.data.cs_misc.cs_is_package;
                    this.form.cs_misc.cs_is_transpo = !!res.data.cs_misc.cs_is_transpo;
                    this.form.cs_misc.cs_is_layout = !!res.data.cs_misc.cs_is_layout;
                    this.form.cs_misc.cs_is_film = !!res.data.cs_misc.cs_is_film;
                    this.form.cs_misc.cs_is_plate = !!res.data.cs_misc.cs_is_plate;
                    this.form.cs_misc.cs_is_diecut = !!res.data.cs_misc.cs_is_diecut;
                    this.form.cs_misc.cs_is_peza = !!res.data.cs_misc.cs_is_peza;
                    this.form.cs_misc.cs_is_package_label = !!res.data.cs_misc.cs_is_package_label;
                    this.form.cs_misc.cs_is_item_label = !!res.data.cs_misc.cs_is_item_label;
                    this.form.cs_misc.cs_is_packaging_cartoon = !!res.data.cs_misc.cs_is_packaging_cartoon;
                    this.form.cs_misc.cs_is_packaging_others = !!res.data.cs_misc.cs_is_packaging_others;

                    this.exchange_rates = res.data.exchange_rates;
                    
                    let pre_markup = [10, 15, 20, 25];
                    if(!pre_markup.includes(this.form.cs.cs_markup_percentage)) this.other_markup_percentage = this.form.cs.cs_markup_percentage;
                    this.initializeMarkup();

                    this.original_quantity = this.form.cs.cs_qty;
                    this.original_total_cs = parseFloat(this.form.cs.cs_total_amount);
                    this.original_markup_percentage = this.form.cs.cs_markup_percentage;
                })
                .catch((error) => {
                    console.log(error)
                });
            },
            getSS () {
                axios.get('/api/sales-sheet/'+this.$route.params.ss_id)
                .then((res) => {
                    this.form.ss = res.data.ss;
                    this.form.ss_evaluation_checkers = res.data.ss_evaluation_checkers;
                    this.form.ss_evaluation_approvers = res.data.ss_evaluation_approvers;
                    this.form.ss_evaluation_remarks = res.data.ss_evaluation_remarks;

                    this.evaluation_remarks = res.data.ss_evaluation_remarks;
                    this.evaluationForm.evaluator_id = res.data.user_id;

                    for (let i = 0; i < this.form.ss_evaluation_checkers.length; i++) {
                        if(this.form.ss_evaluation_checkers[i].evaluator_id == res.data.user_id){
                            this.evaluationForm.id = this.form.ss_evaluation_checkers[i].id;
                            this.evaluationForm.cs_id = this.form.ss_evaluation_checkers[i].cs_id;
                            this.evaluationForm.message = this.form.ss_evaluation_checkers[i].evaluator_remarks;
                            this.evaluationForm.evaluation_type = this.form.ss_evaluation_checkers[i].evaluation_type;
                            this.evaluationForm.evaluation_status = this.form.ss_evaluation_checkers[i].evaluation_status;
                        }
                    }
                    for (let i = 0; i < this.form.ss_evaluation_approvers.length; i++) {
                        if(this.form.ss_evaluation_approvers[i].evaluator_id == res.data.user_id){
                            this.evaluationForm.id = this.form.ss_evaluation_approvers[i].id;
                            this.evaluationForm.cs_id = this.form.ss_evaluation_approvers[i].cs_id;
                            this.evaluationForm.message = this.form.ss_evaluation_approvers[i].evaluator_remarks;
                            this.evaluationForm.evaluation_type = this.form.ss_evaluation_approvers[i].evaluation_type;
                            this.evaluationForm.evaluation_status = this.form.ss_evaluation_approvers[i].evaluation_status;
                        }
                    }
                    this.evaluationForm.is_rejected = false;
                    if(this.evaluationForm.evaluation_status == 5) this.evaluationForm.is_rejected = true;
                })
                .catch((error) => {
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
                var ss_id = this.$route.params.ss_id;
                window.open('/export-pdf-sales-sheet?ss_id='+ss_id);
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
            getCustomers(){
                axios.get('/api/get-clients')
                .then((res) => {
                    this.clients = res.data;
                })
                .catch((error) => {
                    console.log(error)
                });
            },
            getCSOrder(){
                axios.get('/api/get-orders')
                .then((res) => {
                    this.orders = res.data;
                })
                .catch((error) => {
                    console.log(error)
                });
            },
            clientLabel(name, short_name){
                return '(' + short_name + ') ' + name;
            },
            selectCSOrder(id){
                axios.get('/api/get-cs-order/'+id)
                .then((res) => {
                    this.form.cs_order = res.data;
                    this.exchange_rates = res.data.exchange_rates;
                })
                .catch((error) => {
                    console.log(error)
                });
            },
            addCSMiscOther(){
                let id = 1;
                if(this.form.cs_misc_others.length > 0) id = this.form.cs_misc_others[this.form.cs_misc_others.length - 1].id + 1;

                this.form.cs_misc_others.push({
                    id: id,
                    other_misc_description: '',
                    other_misc_quantity: '',
                    other_misc_price: '',
                    other_misc_total: '',
                })
            },
            removeCSMiscOther(){
                this.form.cs_misc_others.splice(this.form.cs_misc_others.length - 1, 1);
            },
            totalCSMiscOther(id, quantity, price, index){
                this.form.cs_misc_others[index].other_misc_total = parseFloat(quantity * price).toFixed(4);
                return parseFloat(quantity * price).toFixed(4);
            },
            dimensionFormat(length, length_unit, width, width_unit){
                return length + ' ' + length_unit + ' x ' + width + ' ' + width_unit;
            },
            csMaterialTotal(){
                let material_subtotal = 0;

                let primary_materials = this.form.cs_order.primary_materials;
                for (let i = 0; i < primary_materials.length; i++) {
                    let primary_material_amount = 0;
                    let primary_material_quantity = 0;
                    let primary_material_total = 0;
                    
                    for (let j = 0; j < primary_materials[i].supplier_materials.length; j++) {
                        primary_materials[i].supplier_materials[j].supplier_material_total = parseFloat(primary_materials[i].supplier_materials[j].supplier_material_amount * primary_materials[i].supplier_materials[j].supplier_material_quantity).toFixed(4);

                        let supplier_material_amount = parseFloat(primary_materials[i].supplier_materials[j].supplier_material_amount);
                        let supplier_material_quantity = parseFloat(primary_materials[i].supplier_materials[j].supplier_material_quantity);

                        if(isNaN(supplier_material_amount)) supplier_material_amount = 0;
                        if(isNaN(supplier_material_quantity)) supplier_material_quantity = 0;

                        if(primary_materials[i].supplier_materials[j].is_selected == true){
                            primary_material_amount += supplier_material_amount;
                            primary_material_quantity += supplier_material_quantity;
                            primary_material_total += supplier_material_amount * supplier_material_quantity;
                        }
                    }

                    primary_materials[i].total_material_quantity = parseInt(primary_material_quantity);
                    primary_materials[i].total_material_amount = parseFloat(primary_material_total).toFixed(4);

                    material_subtotal += primary_material_total;
                }

                let secondary_materials = this.form.cs_order.secondary_materials;
                for (let i = 0; i < secondary_materials.length; i++) {
                    let secondary_material_amount = 0;
                    let secondary_material_quantity = 0;
                    let secondary_material_total = 0;
                    
                    for (let j = 0; j < secondary_materials[i].supplier_materials.length; j++) {
                        secondary_materials[i].supplier_materials[j].supplier_material_total = parseFloat(secondary_materials[i].supplier_materials[j].supplier_material_amount * secondary_materials[i].supplier_materials[j].supplier_material_quantity).toFixed(4);

                        let supplier_material_amount = parseFloat(secondary_materials[i].supplier_materials[j].supplier_material_amount);
                        let supplier_material_quantity = parseInt(secondary_materials[i].supplier_materials[j].supplier_material_quantity);

                        if(isNaN(supplier_material_amount)) supplier_material_amount = 0;
                        if(isNaN(supplier_material_quantity)) supplier_material_quantity = 0;

                        if(secondary_materials[i].supplier_materials[j].is_selected == true){
                            secondary_material_amount += supplier_material_amount;
                            secondary_material_quantity += supplier_material_quantity;
                            secondary_material_total += supplier_material_amount * supplier_material_quantity;
                        }
                    }
                    secondary_materials[i].total_material_quantity = parseFloat(secondary_material_quantity).toFixed(4);
                    secondary_materials[i].total_material_amount = parseFloat(secondary_material_total).toFixed(4);

                    material_subtotal += secondary_material_total;
                }

                let subcontractor_material = this.form.cs_order.subcontractor_material;
                for (let i = 0; i < subcontractor_material.length; i++) {
                    let sc_material_amount = 0;
                    let sc_material_quantity = 0;
                    let sc_material_total = 0;
                    
                    for (let j = 0; j < subcontractor_material[i].supplier_sc_materials.length; j++) {
                        subcontractor_material[i].supplier_sc_materials[j].supplier_sc_material_total = parseFloat(subcontractor_material[i].supplier_sc_materials[j].supplier_sc_material_amount * subcontractor_material[i].supplier_sc_materials[j].supplier_sc_material_quantity).toFixed(4);

                        let supplier_sc_material_amount = parseFloat(subcontractor_material[i].supplier_sc_materials[j].supplier_sc_material_amount);
                        let supplier_sc_material_quantity = parseInt(subcontractor_material[i].supplier_sc_materials[j].supplier_sc_material_quantity);

                        if(isNaN(supplier_sc_material_amount)) supplier_sc_material_amount = 0;
                        if(isNaN(supplier_sc_material_quantity)) supplier_sc_material_quantity = 0;

                        if(subcontractor_material[i].supplier_sc_materials[j].is_selected == true){
                            sc_material_amount += supplier_sc_material_amount;
                            sc_material_quantity += supplier_sc_material_quantity;
                            sc_material_total += supplier_sc_material_amount * supplier_sc_material_quantity;
                        }
                    }

                    subcontractor_material[i].total_sc_material_quantity = parseInt(sc_material_quantity);
                    subcontractor_material[i].total_sc_material_amount = parseFloat(sc_material_total).toFixed(4);

                    material_subtotal += sc_material_total;
                }

                this.form.cs.cs_material_subtotal = parseFloat(material_subtotal).toFixed(4);
                this.form.cs.cs_material_loss = parseFloat(material_subtotal * 0.10).toFixed(4);
                this.form.cs.cs_material_total = parseFloat(material_subtotal * 1.10).toFixed(4);

                let rate = 1;
                if(this.form.cs.cs_currency == 'USD') rate = parseFloat(this.exchange_rates.USDToPHP.rate);
                this.form.cs.cs_material_subtotal_php = parseFloat(material_subtotal * rate).toFixed(4);
                this.form.cs.cs_material_loss_php = parseFloat(material_subtotal * 0.10 * rate).toFixed(4);
                this.form.cs.cs_material_total_php = parseFloat(material_subtotal * 1.10 * rate).toFixed(4);
            },
            viewDetails(material_id, material){
                console.log(material_id + ' ' + material)
                if(material == 'primary' || material == 'secondary' || material == 'print') window.open('/view-raw-material/'+material_id);
                else if(material == 'subcon') window.open('/view-subcontract-material/'+material_id);
            },
            csPrintingTotal(){
                let printing_total = 0;

                let prints = this.form.cs_order.prints;
                for (let i = 0; i < prints.length; i++) {
                    let printing_amount = 0;
                    let printing_quantity = 0;
                    let printing_subtotal = 0;
                    
                    for (let j = 0; j < prints[i].supplier_materials.length; j++) {
                        prints[i].supplier_materials[j].supplier_material_total = parseFloat(prints[i].supplier_materials[j].supplier_material_amount * prints[i].supplier_materials[j].supplier_material_quantity).toFixed(4);

                        let supplier_material_amount = parseFloat(prints[i].supplier_materials[j].supplier_material_amount);
                        let supplier_material_quantity = parseInt(prints[i].supplier_materials[j].supplier_material_quantity);

                        if(isNaN(supplier_material_amount)) supplier_material_amount = 0;
                        if(isNaN(supplier_material_quantity)) supplier_material_quantity = 0;

                        if(prints[i].supplier_materials[j].is_selected == true){
                            printing_amount += supplier_material_amount;
                            printing_quantity += supplier_material_quantity;
                            printing_subtotal += supplier_material_amount * supplier_material_quantity;
                        }
                    }

                    prints[i].total_material_quantity = parseInt(printing_quantity);
                    prints[i].total_material_amount = parseFloat(printing_subtotal).toFixed(4);

                    printing_total += printing_subtotal;
                }

                this.form.cs.cs_printing_total = parseFloat(printing_total).toFixed(4);

                let rate = 1;
                if(this.form.cs.cs_currency == 'USD') rate = parseFloat(this.exchange_rates.USDToPHP.rate);
                this.form.cs.cs_printing_total_php = parseFloat(printing_total * rate).toFixed(4);
            },
            csProcessTotal(){
                let process_total = 0;
                let process_list = this.form.cs_order.process_list;
                for (let i = 0; i < process_list.length; i++) {
                    let total_process_amount = 0;
                    let total_process_quantity = 0;

                    process_list[i].process_subtotal = parseFloat(process_list[i].total_process_amount * process_list[i].total_process_quantity).toFixed(4);

                    if(isNaN(total_process_amount)) total_process_amount = 0;
                    if(isNaN(total_process_quantity)) total_process_quantity = 0;

                    total_process_amount += process_list[i].total_process_amount;
                    total_process_quantity += process_list[i].total_process_quantity;
                    process_total += total_process_amount * total_process_quantity;
                }

                let process_subcon = this.form.cs_order.process_subcon;
                for (let i = 0; i < process_subcon.length; i++) {
                    let total_process_amount = 0;
                    let total_process_quantity = 0;

                    process_subcon[i].process_subtotal = parseFloat(process_subcon[i].total_process_amount * process_subcon[i].total_process_quantity).toFixed(4);

                    if(isNaN(total_process_amount)) total_process_amount = 0;
                    if(isNaN(total_process_quantity)) total_process_quantity = 0;

                    total_process_amount += process_subcon[i].total_process_amount;
                    total_process_quantity += process_subcon[i].total_process_quantity;
                    process_total += total_process_amount * total_process_quantity;
                }

                this.form.cs.cs_process_total = parseFloat(process_total).toFixed(4);

                let rate = 1;
                if(this.form.cs.cs_currency == 'USD') rate = parseFloat(this.exchange_rates.USDToPHP.rate);
                this.form.cs.cs_process_total_php = parseFloat(process_total * rate).toFixed(4);
            },
            addOrder(){
                Swal.fire({
                    text: 'Are you sure you want to create new order? Another tab will open for new order registration.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Create order',
                    width: '25rem',
                })
                .then((result) => {
                    if(result.value){
                        window.open('/add-order');
                    }
                });
            },
            dateFormat(date){
                return moment(date).format('MM/DD/YYYY HH:mm');
            },
            amountLabel(){
                let $currency = '';
                if(this.form.cs.cs_currency) $currency = this.form.cs.cs_currency;
                return 'Amount' + ' (' + $currency + ')';
            },
            totalLabel(){
                let $currency = '';
                if(this.form.cs.cs_currency) $currency = this.form.cs.cs_currency;
                return 'Total' + ' (' + $currency + ')';
            },
            initializeMarkup(){
                this.form.cs_markup = [
                    { 'id': 1, 'percentage': 25, 'markup_per_piece': '', 'total_markup': '', 'profit': '' },
                    { 'id': 2, 'percentage': 20, 'markup_per_piece': '', 'total_markup': '', 'profit': '' },
                    { 'id': 3, 'percentage': 15, 'markup_per_piece': '', 'total_markup': '', 'profit': '' },
                    { 'id': 4, 'percentage': 10, 'markup_per_piece': '', 'total_markup': '', 'profit': '' },
                    { 'id': 5, 'percentage': this.other_markup_percentage, 'markup_per_piece': '', 'total_markup': '', 'profit': '' },
                ];
            },
            amountFormat(amount){
                let $currency = '';
                if(this.form.cs.cs_currency) $currency = this.form.cs.cs_currency;
                if(isNaN(amount) || amount == Infinity) amount = (0).toFixed(4);
                return $currency + ' ' + amount;
            },
            calculateMarkup(){
                for (let i = 0; i < this.form.cs_markup.length; i++) {
                    let markup_per_piece = ((parseFloat(this.form.cs.cs_total_amount).toFixed(4) * (1 - this.form.cs_markup[i].percentage / 100)) / parseInt(this.form.cs.cs_qty)).toFixed(4);

                    if(isNaN(markup_per_piece)) this.form.cs_markup[i].markup_per_piece = (0).toFixed(4);
                    else this.form.cs_markup[i].markup_per_piece = markup_per_piece;

                    this.form.cs_markup[i].total_markup = (parseFloat(this.form.cs.cs_total_amount).toFixed(4) * (1 + (this.form.cs_markup[i].percentage) / 100)).toFixed(4);

                    this.form.cs_markup[i].profit = (parseFloat(this.form.cs.cs_total_amount).toFixed(4) * (this.form.cs_markup[i].percentage / 100)).toFixed(4);
                }
            },
            selectMarkup(){
                this.form.cs_markup[4].percentage = this.other_markup_percentage;
                this.setMarkupToForm();
                this.calculateMarkup();
            },
            setMarkupToForm(){
                for (let i = 0; i < this.form.cs_markup.length; i++) {
                    if(this.form.cs.cs_markup_percentage == this.form.cs_markup[i].percentage){
                        let cs_markup_piece = this.form.cs_markup[i].markup_per_piece;

                        if(isNaN(cs_markup_piece)) this.form.cs.cs_markup_piece = (0).toFixed(4);
                        else this.form.cs.cs_markup_piece = cs_markup_piece;

                        this.form.cs.cs_markup_total = this.form.cs_markup[i].total_markup;
                        this.form.cs.cs_markup_profit = this.form.cs_markup[i].profit;
                    }
                }
            },
            changeCurrency(){
                if(this.form.cs.client_order_id){
                    Swal.fire({
                        text: 'Are you sure you want to change currency? Existing draft in material, printing and process lists will be reloaded.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Change',
                        width: '25rem',
                    })
                    .then((result) => {
                        if(result.value){
                            this.selectCSOrder(this.form.cs.client_order_id);

                            Swal.fire(
                                'Changed!',
                                'Currency successfully changed.',
                                'success'
                            )
                        }
                    });
                }
            },
            initializeSSEvaluationForm(){
                this.form.ss_evaluation = {
                    evaluator_id: '',
                    evaluator_name: '',
                    evaluator_permission: '',
                    evaluator_remarks: '',
                }
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
                        if(!_.find(this.form.ss_evaluation_checkers, { 'evaluator_id': this.draft_evaluator_checker }) && !_.find(this.form.ss_evaluation_approvers, { 'evaluator_id': this.draft_evaluator_checker })){
                            let selected_checker = _.find(this.evaluator_checkers, { 'id': this.draft_evaluator_checker });

                            let id = 1;
                            if(this.form.ss_evaluation_checkers.length > 0){
                                id = this.form.ss_evaluation_checkers[this.form.ss_evaluation_checkers.length - 1].id + 1;
                            }

                            this.form.ss_evaluation_checkers.push({
                                id: id,
                                evaluator_id: selected_checker.id,
                                first_name: selected_checker.first_name,
                                last_name: selected_checker.last_name,
                                evaluation_type: 1,
                                evaluator_remarks: '',
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
                        if(!_.find(this.form.ss_evaluation_approvers, { 'evaluator_id': this.draft_evaluator_approver }) && !_.find(this.form.ss_evaluation_checkers, { 'evaluator_id': this.draft_evaluator_approver })){
                            let selected_approver = _.find(this.evaluator_approvers, { 'id': this.draft_evaluator_approver });

                            let id = 1;
                            if(this.form.ss_evaluation_checkers.length > 0){
                                id = this.form.ss_evaluation_checkers[this.form.ss_evaluation_checkers.length - 1].id + 1;
                            }

                            this.form.ss_evaluation_approvers.push({
                                id: id,
                                evaluator_id: selected_approver.id,
                                first_name: selected_approver.first_name,
                                last_name: selected_approver.last_name,
                                evaluation_type: 2,
                                evaluator_remarks: '',
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
            deleteSSEvaluator(evaluator_id, type){
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
                            for(let i = 0; i < this.form.ss_evaluation_checkers.length; i++){
                                if(this.form.ss_evaluation_checkers[i].evaluator_id == evaluator_id) this.form.ss_evaluation_checkers.splice(i, 1);
                            }
                        }
                        else if(type == 'approver'){
                            for(let i = 0; i < this.form.ss_evaluation_approvers.length; i++){
                                if(this.form.ss_evaluation_approvers[i].evaluator_id == evaluator_id) this.form.ss_evaluation_approvers.splice(i, 1);
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
            initializeForm () {
                this.form.ss = {
                    cs_id: '0', 
                    ss_qty: '0', 
                    ss_qty_remaining: '0',
                    ss_total_amount: '0',
                    ss_cost_price: '0',
                    ss_cost_rate: '0',
                    ss_gross_sales: '0',
                    ss_sales_price: '0',
                    ss_gross_profit: '0',
                    ss_quotation_qty: '0',
                    ss_payment_method: '0',
                }
            },
            isEvaluated(status){
                if(status == 1 || status == 3 || status == 5) return true;
                else return false;
            },
            evaluateSS(){
                Swal.fire({
                    text: 'Are you sure you want to evaluate SS?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Evaluate',
                    width: '25rem',
                })
                .then((result) => {
                    if(result.value){
                        this.evaluationForm.put('/api/evaluate-ss')
                        .then((res) => {
                            // this.getCS();

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
            evaluationStatus(evaluation_status){
                return _.find(this.$evaluation_status, { 'id': evaluation_status }).evaluation_status;
            },
            evaluationStatusIcon(evaluation_status){
                if(evaluation_status == 1 || evaluation_status == 3) return 'el-icon-circle-check';
                else if(evaluation_status == 2 || evaluation_status == 4) return 'el-icon-success';
                else return 'el-icon-remove';
            },
            evaluatorType(type){
                let evaluator_type = 'Checker';
                if(type == 2) evaluator_type = 'Approver';
                return evaluator_type;
            },
            evaluatorNameFormat(first_name, last_name){
                return name = first_name + ' ' + last_name;
            },
            rejectSSEvaluation(){
                let process = 'reject';
                if(this.evaluationForm.is_rejected == true) process = 'undo';
                Swal.fire({
                    text: 'Are you sure you want to '+process+' SS?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: process.charAt(0).toUpperCase() + process.slice(1),
                    width: '25rem',
                })
                .then((result) => {
                    if(result.value){
                        this.evaluationForm.put('/api/reject-ss-evaluation')
                        .then((res) => {
                            this.getCS();
                            
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
        },
        created() {
            this.pageType();
            this.getCustomers();
            this.getCSOrder();
            this.getCS();
            this.getEvaluators();
            this.initializeForm();
            this.getSS()
        },
        computed: {
            readonly(){
                if(!this.isEdit) return true;
            },
            readonly(){
                if(!this.isEdit) return true;
            },
            isCheckMiscLabel(){
                if(!this.form.cs_misc.cs_is_label){
                    this.form.cs_misc.cs_qty_label = '';
                    this.form.cs_misc.cs_unit_amt_label = '';
                    this.form.cs_misc.cs_total_amt_label = 0;
                    return true;
                }
                else{
                    let quantity = parseFloat(this.form.cs_misc.cs_qty_label);
                    let amount = parseFloat(this.form.cs_misc.cs_unit_amt_label);
                    if(isNaN(quantity)) quantity = 0;
                    if(isNaN(amount)) amount = 0;
                    this.form.cs_misc.cs_total_amt_label = parseFloat(quantity * amount).toFixed(4);
                    return false;
                }
            },
            isCheckMiscPackaging(){
                if(!this.form.cs_misc.cs_is_package){
                    this.form.cs_misc.cs_qty_package = '';
                    this.form.cs_misc.cs_unit_amt_package = '';
                    this.form.cs_misc.cs_total_amt_package = 0;
                    return true;
                }
                else{
                    let quantity = parseFloat(this.form.cs_misc.cs_qty_package);
                    let amount = parseFloat(this.form.cs_misc.cs_unit_amt_package);
                    if(isNaN(quantity)) quantity = 0;
                    if(isNaN(amount)) amount = 0;
                    this.form.cs_misc.cs_total_amt_package = parseFloat(quantity * amount).toFixed(4);
                    return false;
                }
            },
            isCheckMiscTranspo(){
                if(!this.form.cs_misc.cs_is_transpo){
                    this.form.cs_misc.cs_qty_transpo = '';
                    this.form.cs_misc.cs_unit_amt_transpo = '';
                    this.form.cs_misc.cs_total_amt_transpo = 0;
                    return true;
                }
                else{
                    let quantity = parseFloat(this.form.cs_misc.cs_qty_transpo);
                    let amount = parseFloat(this.form.cs_misc.cs_unit_amt_transpo);
                    if(isNaN(quantity)) quantity = 0;
                    if(isNaN(amount)) amount = 0;
                    this.form.cs_misc.cs_total_amt_transpo = parseFloat(quantity * amount).toFixed(4);
                    return false;
                }
            },
            isCheckMiscLayout(){
                if(!this.form.cs_misc.cs_is_layout){
                    this.form.cs_misc.cs_qty_layout = '';
                    this.form.cs_misc.cs_unit_amt_layout = '';
                    this.form.cs_misc.cs_total_amt_layout = 0;
                    return true;
                }
                else{
                    let quantity = parseFloat(this.form.cs_misc.cs_qty_layout);
                    let amount = parseFloat(this.form.cs_misc.cs_unit_amt_layout);
                    if(isNaN(quantity)) quantity = 0;
                    if(isNaN(amount)) amount = 0;
                    this.form.cs_misc.cs_total_amt_layout = parseFloat(quantity * amount).toFixed(4);
                    return false;
                }
            },
            isCheckMiscFilm(){
                if(!this.form.cs_misc.cs_is_film){
                    this.form.cs_misc.cs_qty_film = '';
                    this.form.cs_misc.cs_unit_amt_film = '';
                    this.form.cs_misc.cs_total_amt_film = 0;
                    return true;
                }
                else{
                    let quantity = parseFloat(this.form.cs_misc.cs_qty_film);
                    let amount = parseFloat(this.form.cs_misc.cs_unit_amt_film);
                    if(isNaN(quantity)) quantity = 0;
                    if(isNaN(amount)) amount = 0;
                    this.form.cs_misc.cs_total_amt_film = parseFloat(quantity * amount).toFixed(4);
                    return false;
                }
            },
            isCheckMiscPlate(){
                if(!this.form.cs_misc.cs_is_plate){
                    this.form.cs_misc.cs_qty_plate = '';
                    this.form.cs_misc.cs_unit_amt_plate = '';
                    this.form.cs_misc.cs_total_amt_plate = 0;
                    return true;
                }
                else{
                    let quantity = parseFloat(this.form.cs_misc.cs_qty_plate);
                    let amount = parseFloat(this.form.cs_misc.cs_unit_amt_plate);
                    if(isNaN(quantity)) quantity = 0;
                    if(isNaN(amount)) amount = 0;
                    this.form.cs_misc.cs_total_amt_plate = parseFloat(quantity * amount).toFixed(4);
                    return false;
                }
            },
            isCheckMiscDiecut(){
                if(!this.form.cs_misc.cs_is_diecut){
                    this.form.cs_misc.cs_qty_diecut = '';
                    this.form.cs_misc.cs_unit_amt_diecut = '';
                    this.form.cs_misc.cs_total_amt_diecut = 0;
                    return true;
                }
                else{
                    let quantity = parseFloat(this.form.cs_misc.cs_qty_diecut);
                    let amount = parseFloat(this.form.cs_misc.cs_unit_amt_diecut);
                    if(isNaN(quantity)) quantity = 0;
                    if(isNaN(amount)) amount = 0;
                    this.form.cs_misc.cs_total_amt_diecut = parseFloat(quantity * amount).toFixed(4);
                    return false;
                }
            },
            isCheckMiscPEZA(){
                if(!this.form.cs_misc.cs_is_peza){
                    this.form.cs_misc.cs_qty_peza = '';
                    this.form.cs_misc.cs_unit_amt_peza = '';
                    this.form.cs_misc.cs_total_amt_peza = 0;
                    return true;
                }
                else{
                    let quantity = parseFloat(this.form.cs_misc.cs_qty_peza);
                    let amount = parseFloat(this.form.cs_misc.cs_unit_amt_peza);
                    if(isNaN(quantity)) quantity = 0;
                    if(isNaN(amount)) amount = 0;
                    this.form.cs_misc.cs_total_amt_peza = parseFloat(quantity * amount).toFixed(4);
                    return false;
                }
            },
            totalCSMisc(){
                let other_misc_total = _.map(this.form.cs_misc_others, 'other_misc_total');
                let cs_misc_others_total = 0;
                for (let i = 0; i < other_misc_total.length; i++) {
                    cs_misc_others_total += parseFloat(other_misc_total[i]);
                }

                this.form.cs.cs_miscellaneous_total = 
                    parseFloat(this.form.cs_misc.cs_total_amt_label) +
                    parseFloat(this.form.cs_misc.cs_total_amt_package) +
                    parseFloat(this.form.cs_misc.cs_total_amt_transpo) +
                    parseFloat(this.form.cs_misc.cs_total_amt_layout) +
                    parseFloat(this.form.cs_misc.cs_total_amt_film) +
                    parseFloat(this.form.cs_misc.cs_total_amt_plate) +
                    parseFloat(this.form.cs_misc.cs_total_amt_diecut) +
                    parseFloat(this.form.cs_misc.cs_total_amt_peza) +
                    parseFloat(cs_misc_others_total);

                if(this.exchange_rates && Object.keys(this.exchange_rates).length > 0){
                    let rate = 1;
                    if(this.form.cs.cs_currency == 'USD') rate = parseFloat(this.exchange_rates.USDToPHP.rate);
                    this.form.cs.cs_miscellaneous_total_php = parseFloat(this.form.cs.cs_miscellaneous_total * rate).toFixed(4);
                }

                return this.form.cs.cs_currency + ' ' + parseFloat(this.form.cs.cs_miscellaneous_total).toFixed(4);
            },
            totalCSProcess(){
                return this.form.cs.cs_currency + ' ' + parseFloat(this.form.cs.cs_process_total).toFixed(4);
            },
            totalCSCost(){
                this.form.cs.cs_total_amount = 
                    parseFloat(this.form.cs.cs_material_total) +
                    parseFloat(this.form.cs.cs_printing_total) +
                    parseFloat(this.form.cs.cs_process_total) +
                    parseFloat(this.form.cs.cs_miscellaneous_total);

                if(this.exchange_rates && Object.keys(this.exchange_rates).length > 0){
                    let rate = 1;
                    if(this.form.cs.cs_currency == 'USD') rate = parseFloat(this.exchange_rates.USDToPHP.rate);
                    this.form.cs.cs_total_amount_php = parseFloat(this.form.cs.cs_total_amount * rate).toFixed(4);
                }

                this.calculateMarkup();
                this.setMarkupToForm();
                
                return this.form.cs.cs_currency + ' ' + parseFloat(this.form.cs.cs_total_amount).toFixed(4);
            },
            pricePerPiece(){
                let price_per_piece = this.form.cs.cs_total_amount / this.form.cs.cs_qty;
                if(isNaN(price_per_piece) || price_per_piece == Infinity) price_per_piece = 0;
                return this.form.cs.cs_currency + ' ' + parseFloat(price_per_piece).toFixed(4);
            },
            subtotalMaterialAmount(){
                return this.form.cs.cs_currency + ' ' + parseFloat(this.form.cs.cs_material_subtotal).toFixed(4);
            },
            subtotalMateriaLoss(){
                return this.form.cs.cs_currency + ' ' + parseFloat(this.form.cs.cs_material_loss).toFixed(4);
            },
            totalMaterialAmount(){
                return this.form.cs.cs_currency + ' ' + parseFloat(this.form.cs.cs_material_total).toFixed(4);
            },
            totalCSPrinting(){
                return this.form.cs.cs_currency + ' ' + parseFloat(this.form.cs.cs_printing_total).toFixed(4);
            },
            includesRejected(){
                let checkers_array = _.map(this.form.ss_evaluation_checkers, 'evaluation_status');
                let approvers_array = _.map(this.form.ss_evaluation_approvers, 'evaluation_status');
                
                if(checkers_array.includes(5) || approvers_array.includes(5)) return true;
                else return false;
            },
            isChecked(){
                let checkers_array = _.map(this.form.ss_evaluation_checkers, 'evaluation_status');
                
                if(checkers_array.includes(2) && this.evaluationForm.evaluation_status == 1) return true;
                else return false;
            },
            preventApproval(){
                let approvers_array = _.map(this.form.ss_evaluation_approvers, 'evaluation_status');
                let checkers_array = _.map(this.form.ss_evaluation_checkers, 'evaluation_status');
                
                if(!checkers_array.includes(2) && this.evaluationForm.evaluation_status == 2) return true;
                else if (checkers_array.includes(1) && this.evaluationForm.evaluation_type != 1) return true;
                else if(approvers_array.includes(4) && this.evaluationForm.evaluation_status == 2) return true;
                else return false;
            },
            isApproved(){
                let approvers_array = _.map(this.form.ss_evaluation_approvers, 'evaluation_status');

                if(approvers_array.includes(4) && this.evaluationForm.evaluation_status == 3) return true;
                else return false;
            },
            preventCheckDiscarding(){
                let approvers_array = _.map(this.form.ss_evaluation_approvers, 'evaluation_status');

                if(approvers_array.includes(4) && this.evaluationForm.evaluation_type == 1) return true;
                else return false;
            }
        },
        watch:{
            $route (to, from){
                this.pageType();
            }
        }
    }
</script>
