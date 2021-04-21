<template>
    <div class="container">
        <div class="content-header mb-3" style="border-bottom: 1px solid black;">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h4 class="m-0 text-dark"><strong>Add Computation Sheet</strong></h4>
                    </div>
                    <div class="col-sm-6">
                        <button type="button" class="btn button-standard float-right" @click="$router.push('/computation-sheets')">
                            <i class="el-icon-arrow-left"></i> Lists
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-12">
                <form @submit.prevent="createCS()">
                    <el-card class="box-card" shadow="hover">
                        <div slot="header">
                            <span>Customer Details</span>
                        </div>
                        <el-form ref="form" :model="form" label-width="15%" :label-position="`right`">
                            <el-form-item label="Customer Name">
                                <el-col :span="5">
                                    <el-select v-model="form.cs.cs_client_id" placeholder="Customer Number" class="block">
                                        <el-option
                                        v-for="client in clients"
                                        :key="client.id"
                                        :label="client.client_number"
                                        :value="client.id">
                                        </el-option>
                                    </el-select>
                                </el-col>
                                <el-col :span="15" class="ml-1">
                                    <el-select v-model="form.cs.cs_client_id" placeholder="Customer Name" class="block">
                                        <el-option
                                        v-for="client in clients"
                                        :key="client.id"
                                        :label="clientLabel(client.client_name, client.client_short_name)"
                                        :value="client.id">                                        <template>
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
                        </el-form>
                    </el-card>

                    <el-card class="box-card mt-3" shadow="hover">
                        <div slot="header">
                            <span>Order Details</span>
                        </div>
                        <el-form ref="form" :model="form" label-width="15%" :label-position="`right`">
                            <el-form-item label="Part Number">
                                <el-col :span="12">
                                    <el-select v-model="form.cs.client_order_id" placeholder="Part Number" class="block" @change="selectCSOrder" :remote="true" @visible-change="getCSOrder">
                                        <el-option
                                        v-for="order in orders"
                                        :key="order.id"
                                        :label="order.order_part_number"
                                        :value="order.id">
                                        </el-option>
                                    </el-select>
                                </el-col>
                                <el-col :span="12" style="text-align: right">
                                    <button type="button" class="btn button-standard ml-3" size="mini" @click="addOrder">
                                        <i class="el-icon-plus"></i> Add order
                                    </button>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Order Quantity">
                                <el-col :span="4">
                                    <el-input v-model="form.cs.cs_qty" @input="pricePerPiece"></el-input>
                                </el-col>
                                <el-col :span="4">
                                    <el-select v-model="form.cs.cs_qty_unit" placeholder="Unit" :disabled="true">
                                        <el-option
                                        v-for="unit in $units_quantity"
                                        :key="unit.id"
                                        :label="unit.unit_name"
                                        :value="unit.id">
                                        </el-option>
                                    </el-select>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Order Currency">
                                <el-col :span="24">
                                    <el-radio v-model="form.cs.cs_currency" @change="changeCurrency" label="PHP" :disabled="true">PHP</el-radio>
                                    <el-radio v-model="form.cs.cs_currency" @change="changeCurrency" label="USD" :disabled="true">USD</el-radio>
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
                                                        <el-checkbox v-model="supplier_material.is_selected" :label="supplier_material.id" @change="csMaterialTotal">{{ supplier_material.supplier.supplier_name }}</el-checkbox>
                                                    </el-col>
                                                    <el-col :span="2">
                                                        <el-input class="misc-input" v-model="supplier_material.supplier_material_quantity" @input="csMaterialTotal"></el-input>
                                                    </el-col>
                                                    <el-col :span="2">
                                                        <el-input class="misc-input" v-model="supplier_material.supplier_material_amount" 
                                                        @input="csMaterialTotal"></el-input>
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
                                            <el-input v-model="form.cs_order.primary_materials[scope.$index].cs_material_outs"></el-input>
                                        </el-col>
                                    </template>
                                    </el-table-column>
                                    <el-table-column
                                    label="Actual"
                                    width="120">
                                    <template slot-scope="scope">
                                        <el-col :span="24">
                                            <el-input v-model="form.cs_order.primary_materials[scope.$index].cs_material_actual"></el-input>
                                        </el-col>
                                    </template>
                                    </el-table-column>
                                     <el-table-column
                                    label="PRM"
                                    width="120">
                                    <template slot-scope="scope">
                                        <el-col :span="24">
                                            <el-input v-model="form.cs_order.primary_materials[scope.$index].cs_material_prm"></el-input>
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
                                                        <el-checkbox v-model="supplier_material.is_selected" :label="supplier_material.id" @change="csMaterialTotal">{{ supplier_material.supplier.supplier_name }}</el-checkbox>
                                                    </el-col>
                                                    <el-col :span="3">
                                                        <el-input class="misc-input" v-model="supplier_material.supplier_material_quantity" @input="csMaterialTotal"></el-input>
                                                    </el-col>
                                                    <el-col :span="3">
                                                        <el-input class="misc-input" v-model="supplier_material.supplier_material_amount" 
                                                        @input="csMaterialTotal"></el-input>
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
                                                        <el-checkbox v-model="supplier_sc_material.is_selected" :label="supplier_sc_material.id" @change="csMaterialTotal">{{ supplier_sc_material.supplier.supplier_name }}</el-checkbox>
                                                    </el-col>
                                                    <el-col :span="3">
                                                        <el-input class="misc-input" v-model="supplier_sc_material.supplier_sc_material_quantity" @input="csMaterialTotal"></el-input>
                                                    </el-col>
                                                    <el-col :span="3">
                                                        <el-input class="misc-input" v-model="supplier_sc_material.supplier_sc_material_amount" 
                                                        @input="csMaterialTotal"></el-input>
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
                                <el-input class="misc-input input-right" v-model="form.cs.cs_material_loss">
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
                                                        <el-checkbox v-model="supplier_material.is_selected" :label="supplier_material.id" @change="csPrintingTotal">{{ supplier_material.supplier.supplier_name }}</el-checkbox>
                                                    </el-col>
                                                    <el-col :span="3">
                                                        <el-input class="misc-input" v-model="supplier_material.supplier_material_quantity" @input="csPrintingTotal"></el-input>
                                                    </el-col>
                                                    <el-col :span="3">
                                                        <el-input class="misc-input" v-model="supplier_material.supplier_material_amount" 
                                                        @input="csPrintingTotal"></el-input>
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
                                        <el-input v-model="form.cs_order.process_list[scope.$index].total_process_quantity" @input="csProcessTotal"></el-input>
                                    </el-col>
                                    </template>
                                </el-table-column>
                                <el-table-column
                                :label="amountLabel()"
                                class="content-title"
                                :span="2">
                                    <template slot-scope="scope">
                                    <el-col :span="24">
                                        <el-input v-model="form.cs_order.process_list[scope.$index].total_process_amount" @input="csProcessTotal"></el-input>
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
                                        <el-input v-model="form.cs_order.process_subcon[scope.$index].total_process_quantity" @input="csProcessTotal"></el-input>
                                    </el-col>
                                    </template>
                                </el-table-column>
                                <el-table-column
                                :label="amountLabel()"
                                class="content-title"
                                :span="2">
                                    <template slot-scope="scope">
                                    <el-col :span="24">
                                        <el-input v-model="form.cs_order.process_subcon[scope.$index].total_process_amount" @input="csProcessTotal"></el-input>
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
                                <el-checkbox v-model="form.cs_misc.cs_is_label">Label</el-checkbox>
                            </el-col>
                            <el-col :span="10" :inline="true">
                                <el-checkbox v-model="form.cs_misc.cs_is_package_label" :disabled="isCheckMiscLabel">Packaging Label</el-checkbox>
                                <el-checkbox v-model="form.cs_misc.cs_is_item_label" :disabled="isCheckMiscLabel">Item Label</el-checkbox>
                            </el-col>
                            <el-col :span="3">
                                <el-input v-model="form.cs_misc.cs_qty_label" class="misc-input" :disabled="isCheckMiscLabel"></el-input>
                            </el-col>
                            <el-col :span="3">
                                <el-input v-model="form.cs_misc.cs_unit_amt_label" class="misc-input" :disabled="isCheckMiscLabel"></el-input>
                            </el-col>
                            <el-col :span="3">
                                <el-input v-model="form.cs_misc.cs_total_amt_label" class="misc-input" :disabled="true"></el-input>
                            </el-col>
                        </el-row>
                        <el-row>
                            <el-col :span="5">
                                <el-checkbox v-model="form.cs_misc.cs_is_package">Packaging</el-checkbox>
                            </el-col>
                            <el-col :span="10">
                                <el-row :inline="true">
                                    <el-col :span="8">
                                        <el-checkbox v-model="form.cs_misc.cs_is_packaging_cartoon" :disabled="isCheckMiscPackaging">Cartoon</el-checkbox>
                                    </el-col>
                                    <el-col :span="8" class="text-right">
                                        <span class="ml-3">Qty/box</span>
                                    </el-col>
                                    <el-col :span="8">
                                        <el-input v-model="form.cs_misc.cs_packaging_cartoon_qty_per_box" class="misc-input" :disabled="isCheckMiscPackaging"></el-input>
                                    </el-col>
                                </el-row>
                            </el-col>
                            <el-col :span="3">
                                <el-input v-model="form.cs_misc.cs_qty_package" class="misc-input" :disabled="isCheckMiscPackaging"></el-input>
                            </el-col>
                            <el-col :span="3">
                                <el-input v-model="form.cs_misc.cs_unit_amt_package" class="misc-input" :disabled="isCheckMiscPackaging"></el-input>
                            </el-col>
                            <el-col :span="3">
                                <el-input v-model="form.cs_misc.cs_total_amt_package" class="misc-input" :disabled="true"></el-input>
                            </el-col>
                        </el-row>
                        <el-row class="mb-3">
                            <el-col :span="10" :offset="5">
                                <el-row :inline="true">
                                    <el-col :span="8">
                                        <el-checkbox v-model="form.cs_misc.cs_is_packaging_others" :disabled="isCheckMiscPackaging">Others</el-checkbox>
                                    </el-col>
                                    <el-col :span="16">
                                        <el-input v-model="form.cs_misc.cs_packing_others_desc" class="misc-input" :disabled="isCheckMiscPackaging"></el-input>
                                    </el-col>
                                </el-row>
                            </el-col>
                        </el-row>
                        <el-row>
                            <el-col :span="5">
                                <el-checkbox v-model="form.cs_misc.cs_is_transpo">Transportation</el-checkbox>
                            </el-col>
                            <el-col :span="10">
                                <el-row :inline="true">
                                    <el-col :span="24">
                                        <el-input v-model="form.cs_misc.cs_transpo_desc" class="misc-input" :disabled="isCheckMiscTranspo"></el-input>
                                    </el-col>
                                </el-row>
                            </el-col>
                            <el-col :span="3">
                                <el-input v-model="form.cs_misc.cs_qty_transpo" class="misc-input" :disabled="isCheckMiscTranspo"></el-input>
                            </el-col>
                            <el-col :span="3">
                                <el-input v-model="form.cs_misc.cs_unit_amt_transpo" class="misc-input" :disabled="isCheckMiscTranspo"></el-input>
                            </el-col>
                            <el-col :span="3">
                                <el-input v-model="form.cs_misc.cs_total_amt_transpo" class="misc-input" :disabled="true"></el-input>
                            </el-col>
                        </el-row>
                        <el-row>
                            <el-col :span="5">
                                <el-checkbox v-model="form.cs_misc.cs_is_layout">Layout</el-checkbox>
                            </el-col>
                            <el-col :span="10">
                                <el-row :inline="true">
                                    <el-col :span="24">
                                        <el-input v-model="form.cs_misc.cs_layout_desc" class="misc-input" :disabled="isCheckMiscLayout"></el-input>
                                    </el-col>
                                </el-row>
                            </el-col>
                            <el-col :span="3">
                                <el-input v-model="form.cs_misc.cs_qty_layout" class="misc-input" :disabled="isCheckMiscLayout"></el-input>
                            </el-col>
                            <el-col :span="3">
                                <el-input v-model="form.cs_misc.cs_unit_amt_layout" class="misc-input" :disabled="isCheckMiscLayout"></el-input>
                            </el-col>
                            <el-col :span="3">
                                <el-input v-model="form.cs_misc.cs_total_amt_layout" class="misc-input" :disabled="true"></el-input>
                            </el-col>
                        </el-row>
                        <el-row>
                            <el-col :span="5">
                                <el-checkbox v-model="form.cs_misc.cs_is_film">Film</el-checkbox>
                            </el-col>
                            <el-col :span="10">
                                <el-row :inline="true">
                                    <el-col :span="24">
                                        <el-input v-model="form.cs_misc.cs_film_desc" class="misc-input" :disabled="isCheckMiscFilm"></el-input>
                                    </el-col>
                                </el-row>
                            </el-col>
                            <el-col :span="3">
                                <el-input v-model="form.cs_misc.cs_qty_film" class="misc-input" :disabled="isCheckMiscFilm"></el-input>
                            </el-col>
                            <el-col :span="3">
                                <el-input v-model="form.cs_misc.cs_unit_amt_film" class="misc-input" :disabled="isCheckMiscFilm"></el-input>
                            </el-col>
                            <el-col :span="3">
                                <el-input v-model="form.cs_misc.cs_total_amt_film" class="misc-input" :disabled="true"></el-input>
                            </el-col>
                        </el-row>
                        <el-row>
                            <el-col :span="5">
                                <el-checkbox v-model="form.cs_misc.cs_is_plate">Plate</el-checkbox>
                            </el-col>
                            <el-col :span="10">
                                <el-row :inline="true">
                                    <el-col :span="24">
                                        <el-input v-model="form.cs_misc.cs_plate_desc" class="misc-input" :disabled="isCheckMiscPlate"></el-input>
                                    </el-col>
                                </el-row>
                            </el-col>
                            <el-col :span="3">
                                <el-input v-model="form.cs_misc.cs_qty_plate" class="misc-input" :disabled="isCheckMiscPlate"></el-input>
                            </el-col>
                            <el-col :span="3">
                                <el-input v-model="form.cs_misc.cs_unit_amt_plate" class="misc-input" :disabled="isCheckMiscPlate"></el-input>
                            </el-col>
                            <el-col :span="3">
                                <el-input v-model="form.cs_misc.cs_total_amt_plate" class="misc-input" :disabled="true"></el-input>
                            </el-col>
                        </el-row>
                        <el-row>
                            <el-col :span="5">
                                <el-checkbox v-model="form.cs_misc.cs_is_diecut">Die Cut</el-checkbox>
                            </el-col>
                            <el-col :span="10">
                                <el-row :inline="true">
                                    <el-col :span="24">
                                        <el-input v-model="form.cs_misc.cs_diecut_desc" class="misc-input" :disabled="isCheckMiscDiecut"></el-input>
                                    </el-col>
                                </el-row>
                            </el-col>
                            <el-col :span="3">
                                <el-input v-model="form.cs_misc.cs_qty_diecut" class="misc-input" :disabled="isCheckMiscDiecut"></el-input>
                            </el-col>
                            <el-col :span="3">
                                <el-input v-model="form.cs_misc.cs_unit_amt_diecut" class="misc-input" :disabled="isCheckMiscDiecut"></el-input>
                            </el-col>
                            <el-col :span="3">
                                <el-input v-model="form.cs_misc.cs_total_amt_diecut" class="misc-input" :disabled="true"></el-input>
                            </el-col>
                        </el-row>
                        <el-row>
                            <el-col :span="5">
                                <el-checkbox v-model="form.cs_misc.cs_is_peza">PEZA</el-checkbox>
                            </el-col>
                            <el-col :span="10">
                                <el-row :inline="true">
                                    <el-col :span="24">
                                        <el-input v-model="form.cs_misc.cs_peza_desc" class="misc-input" :disabled="isCheckMiscPEZA"></el-input>
                                    </el-col>
                                </el-row>
                            </el-col>
                            <el-col :span="3">
                                <el-input v-model="form.cs_misc.cs_qty_peza" class="misc-input" :disabled="isCheckMiscPEZA"></el-input>
                            </el-col>
                            <el-col :span="3">
                                <el-input v-model="form.cs_misc.cs_unit_amt_peza" class="misc-input" :disabled="isCheckMiscPEZA"></el-input>
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
                                            <el-input v-model="misc_other.other_misc_description" class="misc-input" placeholder="Other miscellaneous"></el-input>
                                        </el-col>
                                    </el-row>
                                </el-col>
                                <el-col :span="3">
                                    <el-input v-model="misc_other.other_misc_quantity" class="misc-input"></el-input>
                                </el-col>
                                <el-col :span="3">
                                    <el-input v-model="misc_other.other_misc_price" class="misc-input"></el-input>
                                </el-col>
                                <el-col :span="3">
                                    <el-input :value="totalCSMiscOther(misc_other.id, misc_other.other_misc_quantity, misc_other.other_misc_price, index)" class="misc-input" :disabled="true"></el-input>
                                </el-col>
                            </el-row>
                        </div>
                        <div>
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
                        <h5 class="content-title">Total Cost Details</h5>
                        <el-row>
                            <el-col :span="12">
                                <span>Total Cost</span>
                            </el-col>
                            <el-col :span="12" class="text-right">
                                <span>{{ totalCSCost }}</span>
                            </el-col>
                        </el-row>
                        <el-row>
                            <el-col :span="12">
                                <span>Price per piece</span>
                            </el-col>
                            <el-col :span="12" class="text-right">
                                <span>{{ pricePerPiece }}</span>
                            </el-col>
                        </el-row>

                        <h5 class="content-title mt-3">Markup Details</h5>
                        <el-row>
                            <el-col :span="24">
                                <el-table
                                    v-model="form.cs_markup"
                                    :data="form.cs_markup"
                                    style="width: 100%">
                                    <el-table-column
                                    prop="percentage"
                                    label="Percentage"
                                    :span="6">
                                    <template slot-scope="scope">
                                        <el-col :span="24">
                                            <el-radio v-model="form.cs.cs_markup_percentage" :label="form.cs_markup[scope.$index].percentage" v-if="form.cs_markup[scope.$index].id < 5">{{ form.cs_markup[scope.$index].percentage + ' %' }}</el-radio>
                                            <el-radio v-model="form.cs.cs_markup_percentage" :label="other_markup_percentage" v-else>
                                                <el-select v-model="other_markup_percentage" placeholder="Other" size="small" style="width: 75%" @change="selectMarkup">
                                                    <el-option
                                                    v-for="markup in other_markups"
                                                    :key="markup"
                                                    :label="markup + ' %'"
                                                    :value="markup">
                                                    </el-option>
                                                </el-select>
                                            </el-radio>
                                        </el-col>
                                    </template>
                                    </el-table-column>
                                    <el-table-column
                                    prop="markup_per_piece"
                                    align="center"
                                    label="Markup/piece"
                                    :span="6">
                                    <template slot-scope="scope">
                                        <el-col :span="24" class="text-right">
                                            <span>{{ amountFormat(form.cs_markup[scope.$index].markup_per_piece) }}</span>
                                        </el-col>
                                    </template>
                                    </el-table-column>
                                    <el-table-column
                                    label="Total Markup"
                                    align="center"
                                    prop="total_markup"
                                    :span="6">
                                    <template slot-scope="scope">
                                        <el-col :span="24" class="text-right">
                                            <span>{{ amountFormat(form.cs_markup[scope.$index].total_markup) }}</span>
                                        </el-col>
                                    </template>
                                    </el-table-column>
                                    <el-table-column
                                    label="Profit"
                                    align="center"
                                    prop="profit"
                                    :span="6">
                                    <template slot-scope="scope">
                                        <el-col :span="24" class="text-right">
                                            <span>{{ amountFormat(form.cs_markup[scope.$index].profit) }}</span>
                                        </el-col>
                                    </template>
                                    </el-table-column>
                                </el-table>
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
                                    v-model="form.cs.cs_remarks" 
                                    type="textarea" 
                                    resize="none"
                                    :autosize="{ minRows: 4, maxRows: 6}">
                                    </el-input>
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
                        <el-row>
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
                                    :data="form.cs_evaluation_checkers"
                                    style="width: 100%">
                                    <el-table-column
                                    prop="evaluator_name"
                                    label="Name">
                                    </el-table-column>
                                    <el-table-column label="Action" fixed="right" width="70">
                                        <template slot-scope="scope">
                                            <el-tooltip class="item" effect="dark" content="Delete" placement="top">
                                                <i class="icon el-icon-delete pointer ml-2" @click="deleteCSEvaluator(form.cs_evaluation_checkers[scope.$index].evaluator_id, 'checker')"></i>
                                            </el-tooltip>
                                        </template>
                                    </el-table-column>
                                </el-table>
                            </el-col>
                            <el-col :span="12">
                                <el-table
                                    :data="form.cs_evaluation_approvers"
                                    style="width: 100%">
                                    <el-table-column
                                    prop="evaluator_name"
                                    label="Name">
                                    </el-table-column>
                                    <el-table-column label="Action" fixed="right" width="70">
                                        <template slot-scope="scope">
                                            <el-tooltip class="item" effect="dark" content="Delete" placement="top">
                                                <i class="icon el-icon-delete pointer ml-2" @click="deleteCSEvaluator(form.cs_evaluation_approvers[scope.$index].evaluator_id, 'approver')"></i>
                                            </el-tooltip>
                                        </template>
                                    </el-table-column>
                                </el-table>
                            </el-col>
                        </el-row>
                    </el-card>
                    <div class="outside-card-button">
                        <el-button type="primary" v-on:click="createCS"><i class="el-icon-plus"></i> Create</el-button>
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
                    cs: {},
                    cs_misc: {},
                    cs_misc_others: [],
                    cs_evaluation_checkers: [],
                    cs_evaluation_approvers: [],
                    cs_order: [],
                    cs_markup: [],
                    is_new_version: false,
                }),
                clients: [],
                orders: [],
                other_percentage: '',
                evaluator_checkers: [],
                evaluator_approvers: [],
                draft_evaluator_checker: '',
                draft_evaluator_approver: '',
                exchange_rates: [],
                other_markup_percentage: 0,
                other_markups: _.remove(Array.from({ length: 101 }, (v, i) => i), function(n){
                    return n != 10 && n != 15 && n != 20 && n != 25;
                }),
            }
        },
        methods: {
            createCS(){
                Swal.fire({
                    text: 'Are you sure you want to register new CS?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Register',
                    width: '25rem',
                })
                .then((result) => {
                    if(result.value){
                        this.form.post('api/cs')
                        .then((res) => {
                            this.form.reset();
                            
                            Swal.fire(
                                'Registered!',
                                'CS successfully registered.',
                                'success'
                            )
                        })
                        .catch(error => {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops!',
                                text: 'CS registration failed.',
                            })
                            console.log(error)
                        });
                    }
                });
            },
            resetFields(){
                this.form.clear();
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
            initializeCSForm(){
                this.form.cs = {
                    client_order_id: '',
                    cs_qty: '',
                    cs_qty_unit: 1,
                    cs_currency: '',
                    cs_material_subtotal: 0,
                    cs_material_subtotal_php: 0,
                    cs_material_loss: 0,
                    cs_material_loss_php: 0,
                    cs_material_total: 0,
                    cs_material_total_php: 0,
                    cs_printing_subtotal: 0,
                    cs_printing_plate: 0,
                    cs_printing_plate_amount: 0,
                    cs_printing_color: 0,
                    cs_printing_color_amount: 0,
                    cs_printing_total: 0,
                    cs_printing_total_php: 0,
                    cs_process_total: 0,
                    cs_process_total_php: 0,
                    cs_miscellaneous_total: 0,
                    cs_miscellaneous_total_php: 0,
                    cs_total_amount: 0,
                    cs_total_amount_php: 0,
                    cs_markup_percentage: 0,
                    cs_markup_piece: 0,
                    cs_markup_total: 0,
                    cs_markup_profit: 0,
                    cs_remarks: '',
                }
            },
            initializeCSMiscForm(){
                this.form.cs_misc = {
                    cs_is_label: false,
                    cs_is_package: false,
                    cs_is_transpo: false,
                    cs_is_layout: false,
                    cs_is_film: false,
                    cs_is_plate: false,
                    cs_is_diecut: false,
                    cs_is_peza: false,
                    cs_is_package_label: false,
                    cs_is_item_label: false,
                    cs_is_packaging_cartoon: false,
                    cs_is_packaging_others: false,
                    cs_packaging_cartoon_qty_per_box: '',
                    cs_packing_others_desc: '',
                    cs_transpo_desc: '',
                    cs_layout_desc: '',
                    cs_film_desc: '',
                    cs_plate_desc: '',
                    cs_diecut_desc: '',
                    cs_peza_desc: '',
                    cs_qty_label: '',
                    cs_qty_package: '',
                    cs_qty_transpo: '',
                    cs_qty_layout: '',
                    cs_qty_film: '',
                    cs_qty_plate: '',
                    cs_qty_diecut: '',
                    cs_qty_peza: '',
                    cs_unit_amt_label: '',
                    cs_unit_amt_package: '',
                    cs_unit_amt_transpo: '',
                    cs_unit_amt_layout: '',
                    cs_unit_amt_film: '',
                    cs_unit_amt_plate: '',
                    cs_unit_amt_diecut: '',
                    cs_unit_amt_peza: '',
                    cs_total_amt_label: '',
                    cs_total_amt_package: '',
                    cs_total_amt_transpo: '',
                    cs_total_amt_layout: '',
                    cs_total_amt_film: '',
                    cs_total_amt_plate: '',
                    cs_total_amt_diecut: '',
                    cs_total_amt_peza: '',
                    cs_miscellaneous_total: 0,
                }
            },
            initializeCSEvaluationForm(){
                this.form.cs_evaluation = {
                    evaluator_id: '',
                    evaluator_name: '',
                    evaluator_permission: '',
                    evaluator_remarks: '',
                }
            },
            selectCSOrder(id){
                axios.get('/api/get-cs-order/'+id)
                .then((res) => {
                    this.form.cs_order = res.data;
                    this.form.cs.cs_currency = res.data.order.order_currency;
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
                        if(!_.find(this.form.cs_evaluation_checkers, { 'evaluator_id': this.draft_evaluator_checker }) && !_.find(this.form.cs_evaluation_approvers, { 'evaluator_id': this.draft_evaluator_checker })){
                            let selected_checker = _.find(this.evaluator_checkers, { 'id': this.draft_evaluator_checker });
                            let evaluator_name = selected_checker.first_name + ' ' + selected_checker.last_name;

                            this.form.cs_evaluation_checkers.push({
                                evaluator_id: selected_checker.id,
                                evaluator_name: evaluator_name,
                                evaluator_permission: 1,
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
                        if(!_.find(this.form.cs_evaluation_approvers, { 'evaluator_id': this.draft_evaluator_approver }) && !_.find(this.form.cs_evaluation_checkers, { 'evaluator_id': this.draft_evaluator_approver })){
                            let selected_approver = _.find(this.evaluator_approvers, { 'id': this.draft_evaluator_approver });
                            let evaluator_name = selected_approver.first_name + ' ' + selected_approver.last_name;

                            this.form.cs_evaluation_approvers.push({
                                evaluator_id: selected_approver.id,
                                evaluator_name: evaluator_name,
                                evaluator_permission: 2,
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
                    secondary_materials[i].total_material_quantity = parseInt(secondary_material_quantity);
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
                        let supplier_sc_material_quantity = parseFloat(subcontractor_material[i].supplier_sc_materials[j].supplier_sc_material_quantity);

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
                        let supplier_material_quantity = parseFloat(prints[i].supplier_materials[j].supplier_material_quantity);

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
            deleteCSEvaluator(evaluator_id, type){
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
                            for(let i = 0; i < this.form.cs_evaluation_checkers.length; i++){
                                if(this.form.cs_evaluation_checkers[i].evaluator_id == evaluator_id) this.form.cs_evaluation_checkers.splice(i, 1);
                            }
                        }
                        else if(type == 'approver'){
                            for(let i = 0; i < this.form.cs_evaluation_approvers.length; i++){
                                if(this.form.cs_evaluation_approvers[i].evaluator_id == evaluator_id) this.form.cs_evaluation_approvers.splice(i, 1);
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
            previewCS(){

            }
        },
        created(){
            this.getCustomers();
            this.initializeCSForm();
            this.initializeCSMiscForm();
            this.getCSOrder();
            this.getEvaluators();
            this.initializeMarkup();
        },
        computed: {
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
        }
    }
</script>
