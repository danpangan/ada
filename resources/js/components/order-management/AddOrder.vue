<template>
    <div class="container">
        <div class="content-header mb-3" style="border-bottom: 1px solid black;">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h4 class="m-0 text-dark"><strong>Order Management</strong></h4>
                    </div>
                    <div class="col-sm-6">
                        <button type="button" class="btn button-standard float-right" @click="$router.push('/order-management')">
                            <i class="el-icon-arrow-left"></i> Lists
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-12">
                <form @submit.prevent="createOrder()">
                    <el-card class="box-card" shadow="hover">
                        <div slot="header">
                            <span>Order Information</span>
                        </div>
                        <el-form ref="form" :model="form" label-width="15%" :label-position="`right`">
                            <el-form-item label="Part Number">
                                <el-col :span="12">
                                    <el-input v-model="form.order.order_part_number"></el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Order Description">
                                <el-col :span="12">
                                    <el-input v-model="form.order.order_item_description"></el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Order Currency">
                                <el-col :span="24">
                                    <el-radio v-model="form.order.order_currency" label="PHP">PHP</el-radio>
                                    <el-radio v-model="form.order.order_currency" label="USD">USD</el-radio>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Order Category">
                                <el-col :span="6">
                                    <el-select v-model="form.order.order_category" placeholder="Category">
                                        <el-option
                                        v-for="category in $order_categories"
                                        :key="category.id"
                                        :label="category.category_name"
                                        :value="category.id">
                                        </el-option>
                                    </el-select>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Item Type Code">
                                <el-col :span="6">
                                    <el-select v-model="form.order.order_item_type_code" @change="selectTypeCode" :disabled="disableTypeCode" placeholder="Category">
                                        <el-option
                                        v-for="type_code in order_type_codes"
                                        :key="type_code.id"
                                        :label="typeCodeLabel(type_code.type_code, type_code.type_code_description)"
                                        :value="type_code.id">
                                        </el-option>
                                    </el-select>
                                </el-col>
                                <el-col :span="6" v-show="form.order.order_item_type_code">
                                    <el-tooltip class="item" effect="dark" content="Add classification" placement="right">
                                        <i :class="isVisiblelassificationsSetting ? 'icon el-icon-minus pointer ml-3' : 'icon el-icon-plus pointer ml-3'" @click="classificationsSetting"></i>
                                    </el-tooltip>
                                </el-col>
                            </el-form-item>
                            <div v-show="isVisiblelassificationsSetting">
                                <hr>
                                <el-form-item label="Add Classification">
                                    <el-col :span="8">
                                        <el-input :span="8" v-model="classificationForm.material_classification_name">
                                        </el-input>
                                    </el-col>
                                    <el-col :span="12">
                                        <el-button type="primary" class="ml-1" v-on:click="createNewClassification"><i class="el-icon-plus"></i> Add</el-button>
                                    </el-col>
                                </el-form-item>
                                <hr>
                            </div>
                            <el-form-item label="Item Classification">
                                <el-col :span="6">
                                    <el-select v-model="form.order.order_item_classification" :disabled="classifications.length == 0" placeholder="Category">
                                        <el-option
                                        v-for="category in classifications"
                                        :key="category.id"
                                        :label="category.material_classification_name"
                                        :value="category.id">
                                        </el-option>
                                    </el-select>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Order Dimension">
                                <el-col :span="4">
                                    <el-input v-model="form.order.order_length"></el-input>
                                </el-col>
                                <el-col :span="4">
                                    <el-select v-model="form.order.order_length_unit" placeholder="Unit">
                                        <el-option
                                        v-for="unit in $units_dimension"
                                        :key="unit.id"
                                        :label="unit.unit_name"
                                        :value="unit.unit_name">
                                        </el-option>
                                    </el-select>
                                </el-col>
                                <el-col :span="4">
                                    <el-input v-model="form.order.order_width"></el-input>
                                </el-col>
                                <el-col :span="4">
                                    <el-select v-model="form.order.order_width_unit" placeholder="Unit">
                                        <el-option
                                        v-for="unit in $units_dimension"
                                        :key="unit.id"
                                        :label="unit.unit_name"
                                        :value="unit.unit_name">
                                        </el-option>
                                    </el-select>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Order Gap">
                                <el-col :span="4">
                                    <el-input v-model="form.order.order_gaplr"></el-input>
                                </el-col>
                                <el-col :span="4">
                                    <el-select v-model="form.order.order_gaplr_unit" placeholder="Unit">
                                        <el-option
                                        v-for="unit in $units_dimension"
                                        :key="unit.id"
                                        :label="unit.unit_name"
                                        :value="unit.unit_name">
                                        </el-option>
                                    </el-select>
                                </el-col>
                                <el-col :span="4">
                                    <el-input v-model="form.order.order_gaptb"></el-input>
                                </el-col>
                                <el-col :span="4">
                                    <el-select v-model="form.order.order_gaptb_unit" placeholder="Unit">
                                        <el-option
                                        v-for="unit in $units_dimension"
                                        :key="unit.id"
                                        :label="unit.unit_name"
                                        :value="unit.unit_name">
                                        </el-option>
                                    </el-select>
                                </el-col>
                            </el-form-item>
                        </el-form>
                    </el-card>

                    <el-card class="box-card mt-3" shadow="hover">
                        <div slot="header">
                            <span>Material Details</span>
                        </div>
                        <el-form ref="form" :model="form" label-width="15%" :label-position="`right`">
                            <h5>Primary Material</h5>
                            <el-form-item label="Material Name">
                                <el-col :span="18">
                                    <el-select v-model="draft_primary_material.material_id" @change="selectPrimaryMaterial" placeholder="Material Name" class="block">
                                        <el-option
                                        v-for="category in materials"
                                        :key="category.id"
                                        :label="category.material_description + ' (' + dimensionFormat(category.material_length, category.material_length_unit, category.material_width, category.material_width_unit) + ')'"
                                        :value="category.id">
                                        <template>
                                            <el-col :span="18">
                                                {{ category.material_description }}
                                            </el-col>
                                            <el-col :span="6">
                                                {{ dimensionFormat(category.material_length, category.material_length_unit, category.material_width, category.material_width_unit) }}
                                            </el-col>
                                        </template>
                                        </el-option>
                                    </el-select>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Specifications">
                                <el-col :span="20">
                                    <el-col :span="4" class="text-right mr-1">
                                        <span>Dimensions</span>
                                    </el-col>
                                    <el-col :span="4">
                                        <el-input v-model="draft_primary_material.order_material_length"></el-input>
                                    </el-col>
                                    <el-col :span="4">
                                        <el-select v-model="draft_primary_material.order_material_length_unit" placeholder="Unit">
                                            <el-option
                                            v-for="unit in $units_dimension"
                                            :key="unit.id"
                                            :label="unit.unit_name"
                                            :value="unit.unit_name">
                                            </el-option>
                                        </el-select>
                                    </el-col>
                                    <el-col :span="4">
                                        <el-input v-model="draft_primary_material.order_material_width"></el-input>
                                    </el-col>
                                    <el-col :span="4">
                                        <el-select v-model="draft_primary_material.order_material_width_unit" placeholder="Unit">
                                            <el-option
                                            v-for="unit in $units_dimension"
                                            :key="unit.id"
                                            :label="unit.unit_name"
                                            :value="unit.unit_name">
                                            </el-option>
                                        </el-select>
                                    </el-col>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="">
                                <el-col :span="20">
                                    <el-col :span="4" class="text-right mr-1">
                                        <span>Margin</span>
                                    </el-col>
                                    <el-col :span="4">
                                        <el-input v-model="draft_primary_material.order_material_margintb"></el-input>
                                    </el-col>
                                    <el-col :span="4">
                                        <el-select v-model="draft_primary_material.order_material_margintb_unit" placeholder="Unit">
                                            <el-option
                                            v-for="unit in $units_dimension"
                                            :key="unit.id"
                                            :label="unit.unit_name"
                                            :value="unit.unit_name">
                                            </el-option>
                                        </el-select>
                                    </el-col>
                                    <el-col :span="4">
                                        <el-input v-model="draft_primary_material.order_material_marginlr"></el-input>
                                    </el-col>
                                    <el-col :span="4">
                                        <el-select v-model="draft_primary_material.order_material_marginlr_unit" placeholder="Unit">
                                            <el-option
                                            v-for="unit in $units_dimension"
                                            :key="unit.id"
                                            :label="unit.unit_name"
                                            :value="unit.unit_name">
                                            </el-option>
                                        </el-select>
                                    </el-col>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="">
                                <el-col :span="20">
                                    <el-col :span="4" class="text-right mr-1">
                                        <span>Pages</span>
                                    </el-col>
                                    <el-col :span="4">
                                        <el-input v-model="draft_primary_material.order_pages"></el-input>
                                    </el-col>
                                    <el-col :span="4" class="text-right mr-1">
                                        <span>Rolls</span>
                                    </el-col>
                                    <el-col :span="4">
                                        <el-input v-model="draft_primary_material.order_rolls"></el-input>
                                    </el-col>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="">
                                <el-col :span="20">
                                    <el-col :span="4" class="text-right mr-1">
                                        <span>Cut</span>
                                    </el-col>
                                    <el-col :span="4">
                                        <el-input v-model="draft_primary_material.order_material_cut"></el-input>
                                    </el-col>
                                    <el-col :span="4" class="text-right mr-1">
                                        <span>Slit</span>
                                    </el-col>
                                    <el-col :span="4">
                                        <el-input v-model="draft_primary_material.order_material_slit"></el-input>
                                    </el-col>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="">
                                <el-col :span="20">
                                    <el-col :span="4" class="text-right mr-1">
                                        <span>Cylinder</span>
                                    </el-col>
                                    <el-col :span="4">
                                        <el-input v-model="draft_primary_material.order_material_cylinder"></el-input>
                                    </el-col>
                                    <el-col :span="4" class="text-right mr-1">
                                        <span>Outs</span>
                                    </el-col>
                                    <el-col :span="4">
                                        <el-input v-model="draft_primary_material.order_material_outs"></el-input>
                                    </el-col>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="">
                                <el-col :span="20">
                                    <el-col :span="4" class="text-right mr-1">
                                        <span>Overlap</span>
                                    </el-col>
                                    <el-col :span="4">
                                        <el-input v-model="draft_primary_material.order_material_layout_overlap"></el-input>
                                    </el-col>
                                    <el-col :span="4">
                                        <el-select v-model="draft_primary_material.order_material_layout_overlap_unit" placeholder="Unit">
                                            <el-option
                                            v-for="unit in $units_dimension"
                                            :key="unit.id"
                                            :label="unit.unit_name"
                                            :value="unit.unit_name">
                                            </el-option>
                                        </el-select>
                                    </el-col>
                                </el-col>
                            </el-form-item>
                            <el-col :span="24" style="text-align: center">
                                <button type="button" class="btn button-standard ml-3" size="mini" @click="addPrimaryMaterialToList">
                                    <i class="el-icon-plus"></i> Save primary material to list
                                </button>
                            </el-col>
                            
                            <h5 class="mt-3">Primary Material List</h5>
                            <el-col :span="24" class="my-3">
                                <el-table
                                    v-model="form.primary_materials"
                                    :data="form.primary_materials"
                                    :row-key="form.primary_materials.id"
                                    style="width: 100%">
                                    <el-table-column type="expand">
                                        <template slot-scope="props">
                                            <el-row>
                                                <el-col :span="4">
                                                    <p>Material Name:</p>
                                                </el-col>
                                                <el-col :span="20">
                                                    <p>{{ props.row.material_name }}</p>
                                                </el-col>
                                            </el-row>
                                            <el-row>
                                                <el-col :span="4">
                                                    <p>Dimension:</p>
                                                </el-col>
                                                <el-col :span="20">
                                                    <p>{{ props.row.order_material_length + ' ' + props.row.order_material_length_unit + ' x ' + props.row.order_material_width + ' ' + props.row.order_material_width_unit }}</p>
                                                </el-col>
                                            </el-row>
                                            <el-row>
                                                <el-col :span="4">
                                                    <p>Margin:</p>
                                                </el-col>
                                                <el-col :span="20">
                                                    <p>{{ props.row.order_material_marginlr + ' ' + props.row.order_material_marginlr_unit + ' x ' + props.row.order_material_margintb + ' ' + props.row.order_material_margintb_unit }}</p>
                                                </el-col>
                                            </el-row>
                                            <el-row>
                                                <el-col :span="4">
                                                    <p>Pages:</p>
                                                </el-col>
                                                <el-col :span="20">
                                                    <p>{{ props.row.order_pages }}</p>
                                                </el-col>
                                            </el-row>
                                            <el-row>
                                                <el-col :span="4">
                                                    <p>Rolls:</p>
                                                </el-col>
                                                <el-col :span="20">
                                                    <p>{{ props.row.order_rolls }}</p>
                                                </el-col>
                                            </el-row>
                                            <el-row>
                                                <el-col :span="4">
                                                    <p>Cuts:</p>
                                                </el-col>
                                                <el-col :span="20">
                                                    <p>{{ props.row.order_material_cut }}</p>
                                                </el-col>
                                            </el-row>
                                            <el-row>
                                                <el-col :span="4">
                                                    <p>Slit:</p>
                                                </el-col>
                                                <el-col :span="20">
                                                    <p>{{ props.row.order_material_slit }}</p>
                                                </el-col>
                                            </el-row>
                                            <el-row>
                                                <el-col :span="4">
                                                    <p>Cylinder:</p>
                                                </el-col>
                                                <el-col :span="20">
                                                    <p>{{ props.row.order_material_cylinder }}</p>
                                                </el-col>
                                            </el-row>
                                            <el-row>
                                                <el-col :span="4">
                                                    <p>Outs:</p>
                                                </el-col>
                                                <el-col :span="20">
                                                    <p>{{ props.row.order_material_outs }}</p>
                                                </el-col>
                                            </el-row>
                                            <el-row>
                                                <el-col :span="4">
                                                    <p>Overlap:</p>
                                                </el-col>
                                                <el-col :span="20">
                                                    <p>{{ props.row.order_material_layout_overlap + ' ' + props.row.order_material_layout_overlap_unit}}</p>
                                                </el-col>
                                            </el-row>
                                        </template>
                                    </el-table-column>
                                    <el-table-column
                                    prop="material_name"
                                    label="Material Name"
                                    width="300">
                                    </el-table-column>
                                    <el-table-column
                                    prop="order_material_length"
                                    label="Dimension">
                                    <template slot-scope="scope">
                                        {{ dimensionFormat(
                                            form.primary_materials[scope.$index].order_material_length, 
                                            form.primary_materials[scope.$index].order_material_length_unit,
                                            form.primary_materials[scope.$index].order_material_width,
                                            form.primary_materials[scope.$index].order_material_width_unit,
                                        ) }}
                                    </template>
                                    </el-table-column>
                                    <el-table-column
                                    prop="order_pages"
                                    label="Pages">
                                    </el-table-column>
                                    <el-table-column
                                    prop="order_material_outs"
                                    label="Outs">
                                    </el-table-column>
                                    <el-table-column
                                    :fixed="`right`"
                                    width="70"
                                    label="Action">
                                        <template slot-scope="scope">
                                            <el-tooltip class="item" effect="dark" content="Delete" placement="right">
                                                <i class="icon el-icon-delete pointer ml-2" @click="deletePrimaryMaterialDraft(form.primary_materials[scope.$index].id)"></i>
                                            </el-tooltip>
                                        </template>
                                    </el-table-column>
                                </el-table>
                            </el-col>

                            <el-col>
                                <hr class="hr1">
                            </el-col>
                            <h5 class="mt-3">Secondary Material</h5>
                            <el-form-item label="Material Name">
                                <el-col :span="18">
                                    <el-select v-model="draft_secondary_material.material_id" @change="selectSecondaryMaterial" placeholder="Material Name" class="block">
                                        <el-option
                                        v-for="category in materials"
                                        :key="category.id"
                                        :label="category.material_description + ' (' + dimensionFormat(category.material_length, category.material_length_unit, category.material_width, category.material_width_unit) + ')'"
                                        :value="category.id">
                                        <template>
                                            <el-col :span="18">
                                                {{ category.material_description }}
                                            </el-col>
                                            <el-col :span="6">
                                                {{ dimensionFormat(category.material_length, category.material_length_unit, category.material_width, category.material_width_unit) }}
                                            </el-col>
                                        </template>
                                        </el-option>
                                    </el-select>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Specifications">
                                <el-col :span="20">
                                    <el-col :span="4" class="text-right mr-1">
                                        <span>Dimensions</span>
                                    </el-col>
                                    <el-col :span="4">
                                        <el-input v-model="draft_secondary_material.order_material_length"></el-input>
                                    </el-col>
                                    <el-col :span="4">
                                        <el-select v-model="draft_secondary_material.order_material_length_unit" placeholder="Unit">
                                            <el-option
                                            v-for="unit in $units_dimension"
                                            :key="unit.id"
                                            :label="unit.unit_name"
                                            :value="unit.unit_name">
                                            </el-option>
                                        </el-select>
                                    </el-col>
                                    <el-col :span="4">
                                        <el-input v-model="draft_secondary_material.order_material_width"></el-input>
                                    </el-col>
                                    <el-col :span="4">
                                        <el-select v-model="draft_secondary_material.order_material_width_unit" placeholder="Unit">
                                            <el-option
                                            v-for="unit in $units_dimension"
                                            :key="unit.id"
                                            :label="unit.unit_name"
                                            :value="unit.unit_name">
                                            </el-option>
                                        </el-select>
                                    </el-col>
                                </el-col>
                            </el-form-item>
                            <el-col :span="24" style="text-align: center">
                                <button type="button" class="btn button-standard ml-3" size="mini" @click="addSecondaryMaterialToList">
                                    <i class="el-icon-plus"></i> Save secondary material to list
                                </button>
                            </el-col>

                            <h5 class="mt-3">Secondary Material List</h5>
                            <el-col :span="24" class="my-3">
                                <el-table
                                    v-model="form.secondary_materials"
                                    :data="form.secondary_materials"
                                    :row-key="form.secondary_materials.id"
                                    style="width: 100%">
                                    <el-table-column type="expand">
                                        <template slot-scope="props">
                                            <el-row>
                                                <el-col :span="4">
                                                    <p>Material Name:</p>
                                                </el-col>
                                                <el-col :span="20">
                                                    <p>{{ props.row.material_name }}</p>
                                                </el-col>
                                            </el-row>
                                            <el-row>
                                                <el-col :span="4">
                                                    <p>Dimension:</p>
                                                </el-col>
                                                <el-col :span="20">
                                                    <p>{{ props.row.order_material_length + ' ' + props.row.order_material_length_unit + ' x ' + props.row.order_material_width + ' ' + props.row.order_material_width_unit }}</p>
                                                </el-col>
                                            </el-row>
                                        </template>
                                    </el-table-column>
                                    <el-table-column
                                    prop="material_name"
                                    label="Material Name"
                                    width="300">
                                    </el-table-column>
                                    <el-table-column
                                    prop="order_material_length"
                                    label="Dimension">
                                    <template slot-scope="scope">
                                        {{ dimensionFormat(
                                            form.secondary_materials[scope.$index].order_material_length, 
                                            form.secondary_materials[scope.$index].order_material_length_unit,
                                            form.secondary_materials[scope.$index].order_material_width,
                                            form.secondary_materials[scope.$index].order_material_width_unit,
                                        ) }}
                                    </template>
                                    </el-table-column>
                                    <el-table-column
                                    :fixed="`right`"
                                    width="70"
                                    label="Action">
                                        <template slot-scope="scope">
                                            <el-tooltip class="item" effect="dark" content="Delete" placement="right">
                                                <i class="icon el-icon-delete pointer ml-2" @click="deleteSecondaryMaterialDraft(form.secondary_materials[scope.$index].id)"></i>
                                            </el-tooltip>
                                        </template>
                                    </el-table-column>
                                </el-table>
                            </el-col>

                            <el-col>
                                <hr class="hr1">
                            </el-col>
                            <h5 class="mt-3">Subcontractor Material</h5>
                            <el-form-item label="Material Name">
                                <el-col :span="18">
                                    <el-select v-model="form.subcontractor_material.material_id" placeholder="Material Name" @change="selectSubcontractMaterial" class="block">
                                        <el-option
                                        v-for="category in sc_materials"
                                        :key="category.id"
                                        :label="category.sc_material_name + ' (' + category.sc_order_part_no  + ')'"
                                        :value="category.id">
                                        <template>
                                            <el-col :span="18">
                                                {{ category.sc_material_name }}
                                            </el-col>
                                            <el-col :span="6">
                                                {{ category.sc_order_part_no }}
                                            </el-col>
                                        </template>
                                        </el-option>
                                    </el-select>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Specifications">
                                <el-col :span="20">
                                    <el-col :span="4" class="text-right mr-1">
                                        <span>Dimensions</span>
                                    </el-col>
                                    <el-col :span="4">
                                        <el-input v-model="form.subcontractor_material.order_material_length"></el-input>
                                    </el-col>
                                    <el-col :span="4">
                                        <el-select v-model="form.subcontractor_material.order_material_length_unit" placeholder="Unit">
                                            <el-option
                                            v-for="unit in $units_dimension"
                                            :key="unit.id"
                                            :label="unit.unit_name"
                                            :value="unit.unit_name">
                                            </el-option>
                                        </el-select>
                                    </el-col>
                                    <el-col :span="4">
                                        <el-input v-model="form.subcontractor_material.order_material_width"></el-input>
                                    </el-col>
                                    <el-col :span="4">
                                        <el-select v-model="form.subcontractor_material.order_material_width_unit" placeholder="Unit">
                                            <el-option
                                            v-for="unit in $units_dimension"
                                            :key="unit.id"
                                            :label="unit.unit_name"
                                            :value="unit.unit_name">
                                            </el-option>
                                        </el-select>
                                    </el-col>
                                </el-col>
                            </el-form-item>
                            <el-col :span="24" class="text-center mb-3">
                                <button type="button" class="btn button-standard ml-3" size="mini" @click="resetSubcontractorMaterial">
                                    <i class="el-icon-refresh-left"></i> Reset subcontractor material
                                </button>
                            </el-col>
                        </el-form>
                    </el-card>

                    <el-card class="box-card mt-3" shadow="hover">
                        <div slot="header">
                            <span>Printing Details</span>
                        </div>
                        <el-form ref="form" :model="form" label-width="15%" :label-position="`right`">
                            <el-form-item label="Item Part">
                                <el-col :span="12">
                                    <el-input v-model="draft_print.order_print_item"></el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Machine">
                                <el-col :span="12">
                                    <el-select v-model="draft_print.order_print_machine_id" @change="selectMachine" placeholder="Machines">
                                        <el-option
                                        v-for="category in $machines"
                                        :key="category.id"
                                        :label="category.machine_name"
                                        :value="category.id">
                                        </el-option>
                                    </el-select>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="">
                                <el-col :span="24" class="mr-1">
                                    <el-checkbox v-model="printSubconMaterialSelected" @change="selectSubconAsMaterial">Select subcon as material</el-checkbox>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Raw Material" v-show="!printSubconMaterialSelected">
                                <el-col :span="18">
                                    <el-select v-model="draft_print.material_id" @change="selectPrintRawMaterial" placeholder="Material Name" class="block">
                                        <el-option
                                        v-for="category in printing_materials"
                                        :key="category.id"
                                        :label="category.material_name + ' (' + dimensionFormat(category.order_material_length, category.order_material_length_unit, category.order_material_width, category.order_material_width_unit) + ')'"
                                        :value="category.id">
                                        <template>
                                            <el-col :span="18">
                                                {{ category.material_name }}
                                            </el-col>
                                            <el-col :span="6">
                                                {{ dimensionFormat(category.order_material_length, category.order_material_length_unit, category.order_material_width, category.order_material_width_unit) }}
                                            </el-col>
                                        </template>
                                        </el-option>
                                    </el-select>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Subcon Material" v-show="printSubconMaterialSelected">
                                <el-col :span="12">
                                    <el-input :value="subconMaterialFormat()" :disabled="true"></el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Specifications">
                                <el-col :span="21">
                                    <el-col :span="3" class="text-right mr-1">
                                        <span>Cut Size</span>
                                    </el-col>
                                    <el-col :span="4">
                                        <el-input v-model="draft_print.order_print_cutsize"></el-input>
                                    </el-col>
                                    <el-col :span="3" class="text-right mr-1">
                                        <span>Outs</span>
                                    </el-col>
                                    <el-col :span="4">
                                        <el-input v-model="draft_print.order_print_outs"></el-input>
                                    </el-col>
                                    <el-col :span="3" class="text-right mr-1">
                                        <span>Pages</span>
                                    </el-col>
                                    <el-col :span="4">
                                        <el-input v-model="draft_print.order_print_pages"></el-input>
                                    </el-col>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="">
                                <el-col :span="21">
                                    <el-col :span="3" class="text-right mr-1">
                                        <span>Color Front</span>
                                    </el-col>
                                    <el-col :span="4">
                                        <el-input v-model="colorFront" :disabled="true"></el-input>
                                    </el-col>
                                    <el-col :span="3" class="text-right mr-1">
                                        <span>Color Back</span>
                                    </el-col>
                                    <el-col :span="4">
                                        <el-input v-model="colorBack" :disabled="true"></el-input>
                                    </el-col>
                                    <el-col :span="7" class="text-right mr-1">
                                        <el-checkbox v-model="draft_print.order_print_wit">Work-In-Turn</el-checkbox>
                                    </el-col>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="">
                                <el-col :span="21">
                                    <el-col :span="3" class="text-right mr-1">
                                        <span>Regular Front</span>
                                    </el-col>
                                    <el-col :span="4">
                                        <el-input v-model="draft_print.order_print_front_regular"></el-input>
                                    </el-col>
                                    <el-col :span="3" class="text-right mr-1">
                                        <span>Regular Back</span>
                                    </el-col>
                                    <el-col :span="4">
                                        <el-input v-model="draft_print.order_print_back_regular"></el-input>
                                    </el-col>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="">
                                <el-col :span="21">
                                    <el-col :span="3" class="text-right mr-1">
                                        <span>Special Front</span>
                                    </el-col>
                                    <el-col :span="4">
                                        <el-input v-model="draft_print.order_print_front_special"></el-input>
                                    </el-col>
                                    <el-col :span="3" class="text-right mr-1">
                                        <span>Special Back</span>
                                    </el-col>
                                    <el-col :span="4">
                                        <el-input v-model="draft_print.order_print_back_special"></el-input>
                                    </el-col>
                                </el-col>
                            </el-form-item>
                            <el-col :span="24" style="text-align: center">
                                <button type="button" class="btn button-standard ml-3" size="mini" @click="addPrintToList">
                                    <i class="el-icon-plus"></i> Save printing to list
                                </button>
                            </el-col>
                            
                            <el-col>
                                <hr class="hr1">
                            </el-col>
                            <h5 class="mt-3">Printing List</h5>
                            <el-col :span="24" class="my-3">
                                <el-table
                                    :data="form.prints"
                                    style="width: 100%">
                                    <el-table-column type="expand">
                                        <template slot-scope="props">
                                            <el-row>
                                                <el-col :span="4">
                                                    <p>Item Part:</p>
                                                </el-col>
                                                <el-col :span="20">
                                                    <p>{{ props.row.order_print_item }}</p>
                                                </el-col>
                                            </el-row>
                                            <el-row>
                                                <el-col :span="4">
                                                    <p>Machine:</p>
                                                </el-col>
                                                <el-col :span="20">
                                                    <p>{{ props.row.machine_name }}</p>
                                                </el-col>
                                            </el-row>
                                            <el-row>
                                                <el-col :span="4">
                                                    <p>Material:</p>
                                                </el-col>
                                                <el-col :span="20">
                                                    <p>{{ props.row.material_name }}</p>
                                                </el-col>
                                            </el-row>
                                            <el-row>
                                                <el-col :span="4">
                                                    <p>Cut Size:</p>
                                                </el-col>
                                                <el-col :span="20">
                                                    <p>{{ props.row.order_print_cutsize }}</p>
                                                </el-col>
                                            </el-row>
                                            <el-row>
                                                <el-col :span="4">
                                                    <p>Outs:</p>
                                                </el-col>
                                                <el-col :span="20">
                                                    <p>{{ props.row.order_print_outs }}</p>
                                                </el-col>
                                            </el-row>
                                            <el-row>
                                                <el-col :span="4">
                                                    <p>Pages:</p>
                                                </el-col>
                                                <el-col :span="20">
                                                    <p>{{ props.row.order_print_pages }}</p>
                                                </el-col>
                                            </el-row>
                                            <el-row>
                                                <el-col :span="4">
                                                    <p>Color Front:</p>
                                                </el-col>
                                                <el-col :span="20">
                                                    <p>{{ props.row.order_print_front }}</p>
                                                </el-col>
                                            </el-row>
                                            <el-row>
                                                <el-col :span="4">
                                                    <p>Color Back:</p>
                                                </el-col>
                                                <el-col :span="20">
                                                    <p>{{ props.row.order_print_back }}</p>
                                                </el-col>
                                            </el-row>
                                            <el-row>
                                                <el-col :span="4">
                                                    <p>Work-In-Turn:</p>
                                                </el-col>
                                                <el-col :span="20">
                                                    <p>{{ props.row.order_print_wit ? 'Yes' : 'No' }}</p>
                                                </el-col>
                                            </el-row>
                                            <el-row>
                                                <el-col :span="4">
                                                    <p>Regular Front:</p>
                                                </el-col>
                                                <el-col :span="20">
                                                    <p>{{ props.row.order_print_front_regular }}</p>
                                                </el-col>
                                            </el-row>
                                            <el-row>
                                                <el-col :span="4">
                                                    <p>Regular Back:</p>
                                                </el-col>
                                                <el-col :span="20">
                                                    <p>{{ props.row.order_print_back_regular }}</p>
                                                </el-col>
                                            </el-row>
                                            <el-row>
                                                <el-col :span="4">
                                                    <p>Special Front:</p>
                                                </el-col>
                                                <el-col :span="20">
                                                    <p>{{ props.row.order_print_front_special }}</p>
                                                </el-col>
                                            </el-row>
                                            <el-row>
                                                <el-col :span="4">
                                                    <p>Special Back:</p>
                                                </el-col>
                                                <el-col :span="20">
                                                    <p>{{ props.row.order_print_back_special }}</p>
                                                </el-col>
                                            </el-row>
                                        </template>
                                    </el-table-column>
                                    <el-table-column
                                    prop="order_print_item"
                                    label="Printing"
                                    width="180">
                                    </el-table-column>
                                    <el-table-column
                                    prop="order_print_cutsize"
                                    label="Cut Size">
                                    </el-table-column>
                                    <el-table-column
                                    prop="order_print_outs"
                                    label="Outs">
                                    </el-table-column>
                                    <el-table-column
                                    prop="order_print_pages"
                                    label="Pages">
                                    </el-table-column>
                                    <el-table-column
                                    prop="order_print_front"
                                    label="Color Front">
                                    </el-table-column>
                                    <el-table-column
                                    prop="order_print_back"
                                    label="Color Back">
                                    </el-table-column>
                                    <el-table-column
                                    :fixed="`right`"
                                    width="70"
                                    label="Action">
                                        <template slot-scope="scope">
                                            <el-tooltip class="item" effect="dark" content="Delete" placement="right">
                                                <i class="icon el-icon-delete pointer ml-2" @click="deletePrintDraft(form.prints[scope.$index].id)"></i>
                                            </el-tooltip>
                                        </template>
                                    </el-table-column>
                                </el-table>
                            </el-col>
                        </el-form>
                    </el-card>

                    <el-card class="box-card mt-3" shadow="hover">
                        <div slot="header">
                            <span>Processing Details</span>
                        </div>
                        <el-form ref="form" :model="form" label-width="15%" :label-position="`right`">
                             <!-- Add Process -->
                                <div v-show="showAddProcess">
                                <el-form-item label="Add Process">
                                    <el-col :span="12">
                                        <el-input :span="12" v-model="processForm.name">
                                        </el-input>
                                    </el-col>
                                    <el-col :span="8" style="text-align: right">
                                        <el-button type="primary" class="ml-1" @click="addProcess" size="mini"><i class="el-icon-plus"></i> Add</el-button>
                                        <el-button type="danger" @click="showAddProcess = false" size="mini"><i class="el-icon-close"></i> Cancel</el-button>
                                    </el-col>
                                </el-form-item>
                                <hr>
                                </div>
                            <!-- End Add Process -->
                            <el-form-item label="Process">
                                <el-col :span="12">
                                    <el-select v-model="draft_process.process_id" @change="selectProcess" placeholder="Process" class="block">
                                        <el-option
                                        v-for="category in processes"
                                        :key="category.id"
                                        :label="category.name"
                                        :value="category.id">
                                        </el-option>
                                    </el-select>
                                </el-col>
                                <el-col :span="12" style="text-align: right">
                                    <button type="button" class="btn button-standard ml-3" size="mini" @click="showAddProcess = true" v-show="!showAddProcess">
                                        <i class="el-icon-plus"></i> Add process
                                    </button>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Description">
                                <el-col :span="12">
                                    <el-input v-model="draft_process.order_process_description"></el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Specifications">
                                <el-col :span="21">
                                    <el-col :span="3" class="text-right mr-1">
                                        <span>Cuts</span>
                                    </el-col>
                                    <el-col :span="4">
                                        <el-input v-model="draft_process.cuts"></el-input>
                                    </el-col>
                                    <el-col :span="3" class="text-right mr-1">
                                        <span>Outs</span>
                                    </el-col>
                                    <el-col :span="4">
                                        <el-input v-model="draft_process.outs"></el-input>
                                    </el-col>
                                    <el-col :span="3" class="text-right mr-1">
                                        <span>Folds</span>
                                    </el-col>
                                    <el-col :span="4">
                                        <el-input v-model="draft_process.folds"></el-input>
                                    </el-col>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="">
                                <el-col :span="21">
                                    <el-col :span="3" class="text-right mr-1">
                                        <span>Size</span>
                                    </el-col>
                                    <el-col :span="4">
                                        <el-input v-model="draft_process.size"></el-input>
                                    </el-col>
                                    <el-col :span="3" class="text-right mr-1">
                                        <span>Sig</span>
                                    </el-col>
                                    <el-col :span="4">
                                        <el-input v-model="draft_process.sig"></el-input>
                                    </el-col>
                                    <el-col :span="3" class="text-right mr-1">
                                        <span>GSM</span>
                                    </el-col>
                                    <el-col :span="4">
                                        <el-input v-model="draft_process.gsm"></el-input>
                                    </el-col>
                                </el-col>
                            </el-form-item>
                            <el-col :span="24" style="text-align: center">
                                <button type="button" class="btn button-standard ml-3" size="mini" @click="addProcessToList">
                                    <i class="el-icon-plus"></i> Save process to list
                                </button>
                            </el-col>
                            
                            <h5 class="mt-3">Process List</h5>
                            <el-col :span="24" class="my-3">
                                <el-table
                                    v-model="form.process_list"
                                    :data="form.process_list"
                                    :row-key="form.process_list.id"
                                    style="width: 100%">
                                    <!-- Expandables -->
                                        <el-table-column type="expand">
                                        <template slot-scope="props">
                                            <el-row>
                                                <el-col :span="4">
                                                    <p>Process Name:</p>
                                                </el-col>
                                                <el-col :span="20">
                                                    <p>{{ props.row.process_name }}</p>
                                                </el-col>
                                            </el-row>
                                            <el-row>
                                                <el-col :span="4">
                                                    <p>Description:</p>
                                                </el-col>
                                                <el-col :span="20">
                                                    <p>{{ props.row.order_process_description }}</p>
                                                </el-col>
                                            </el-row>
                                            <el-row>
                                                <el-col :span="4">
                                                    <p>Cuts:</p>
                                                </el-col>
                                                <el-col :span="20">
                                                    <p>{{ props.row.process_cuts }}</p>
                                                </el-col>
                                            </el-row>
                                            <el-row>
                                                <el-col :span="4">
                                                    <p>Outs:</p>
                                                </el-col>
                                                <el-col :span="20">
                                                    <p>{{ props.row.process_outs }}</p>
                                                </el-col>
                                            </el-row>
                                            <el-row>
                                                <el-col :span="4">
                                                    <p>Folds:</p>
                                                </el-col>
                                                <el-col :span="20">
                                                    <p>{{ props.row.process_folds }}</p>
                                                </el-col>
                                            </el-row>
                                            <el-row>
                                                <el-col :span="4">
                                                    <p>Size:</p>
                                                </el-col>
                                                <el-col :span="20">
                                                    <p>{{ props.row.process_size }}</p>
                                                </el-col>
                                            </el-row>
                                            <el-row>
                                                <el-col :span="4">
                                                    <p>Sig:</p>
                                                </el-col>
                                                <el-col :span="20">
                                                    <p>{{ props.row.process_sig }}</p>
                                                </el-col>
                                            </el-row>
                                            <el-row>
                                                <el-col :span="4">
                                                    <p>GSM:</p>
                                                </el-col>
                                                <el-col :span="20">
                                                    <p>{{ props.row.process_gsm }}</p>
                                                </el-col>
                                            </el-row>
                                        </template>
                                        </el-table-column>
                                    <!--End Expandables  -->
                                        <el-table-column
                                        prop="process_name"
                                        label="Process Name">
                                        </el-table-column>
                                        <el-table-column
                                        prop="order_process_description"
                                        label="Description">
                                        </el-table-column>
                                        <el-table-column
                                            label="Action"
                                            :fixed="`right`"
                                            width="100"
                                            inline>
                                            <template slot-scope="scope">
                                                <el-tooltip class="item" effect="dark" content="Delete" placement="right">
                                                    <i class="icon el-icon-delete pointer ml-2" @click="deleteProcessToList(form.process_list[scope.$index].id)"></i>
                                                </el-tooltip>
                                            </template>
                                        </el-table-column>
                                </el-table>
                            </el-col>

                            <el-col>
                                <hr class="hr1">
                            </el-col>
                            <h5 class="mt-3">Subcontractor Process</h5>
                            <el-form-item label="Process Name">
                                <el-col :span="18">
                                    <el-input v-model="draft_process_subcon.process_name"></el-input>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Description">
                                <el-col :span="18">
                                    <el-input v-model="draft_process_subcon.description"></el-input>
                                </el-col>
                            </el-form-item>
                            
                            <el-col :span="24" class="text-center mb-3">
                                <button type="button" class="btn button-standard ml-3" size="mini" @click="addSubconProcessToList">
                                     <i class="el-icon-plus"></i> Save subcontractor process to list
                                </button>
                            </el-col>
                             <h5 class="mt-3">Subcontractor Process List</h5>
                            <el-col :span="24" class="my-3">
                                <el-table
                                    v-model="form.process_subcon"
                                    :data="form.process_subcon"
                                    :row-key="form.process_subcon.id"
                                    style="width: 100%">
                                        <el-table-column
                                        prop="process_name"
                                        label="Process Name">
                                        </el-table-column>
                                        <el-table-column
                                        prop="description"
                                        label="Description">
                                        </el-table-column>
                                        <el-table-column
                                            label="Action"
                                            :fixed="`right`"
                                            width="100"
                                            inline>
                                            <template slot-scope="scope">
                                                <el-tooltip class="item" effect="dark" content="Delete" placement="right">
                                                    <i class="icon el-icon-delete pointer ml-2" @click="deleteSubconProcessToList(form.process_subcon[scope.$index].id)"></i>
                                                </el-tooltip>
                                            </template>
                                        </el-table-column>
                                </el-table>
                            </el-col>
                        </el-form>
                    </el-card>

                    <el-card class="box-card mt-3" shadow="hover">
                        <div slot="header">
                            <span>Additional Information</span>
                        </div>
                        <el-form ref="form" :model="form" label-width="15%" :label-position="`right`">
                            <el-form-item label="Remarks">
                                <el-col :span="12">
                                    <el-input 
                                    v-model="form.order.order_remarks" 
                                    type="textarea" 
                                    resize="none"
                                    :autosize="{ minRows: 4, maxRows: 6}">
                                    </el-input>
                                </el-col>
                            </el-form-item>
                        </el-form>
                    </el-card>

                    <div class="outside-card-button">
                        <el-button type="primary" v-on:click="createOrder"><i class="el-icon-plus"></i> Create</el-button>
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
                    order: {
                        order_item_description: '',
                        order_part_number: '',
                        order_category: '',
                        order_currency: 'PHP',
                        order_item_type_code: '',
                        order_item_classification: '',
                        order_length: '',
                        order_length_unit: '',
                        order_width: '',
                        order_width_unit: '',
                        order_gaplr: '',
                        order_gaplr_unit: '',
                        order_gaptb: '',
                        order_gaptb_unit: '',
                        order_remarks: '',
                    },
                    primary_materials: [],
                    process_list: [],
                    process_subcon: [],
                    secondary_materials: [],
                    subcontractor_material: {},
                    prints: [],
                }),
                processForm: new Form({
                    name: '',
                }),
                classificationForm: new Form({
                    type_code_id: '',
                    material_classification_name: '',
                }),
                order_type_codes: [],
                classifications: [],
                isVisiblelassificationsSetting: false,
                disableTypeCode: false,
                materials: [],
                sc_materials: [],
                sc_process: [],
                draft_primary_material: {},
                draft_secondary_material: {},
                draft_print: {},
                printing_materials: [],
                processes:{},
                draft_process: {},
                draft_process_subcon: {},
                showAddProcess: false,
                printSubconMaterialSelected: false,
            }
        },
        methods: {
            createOrder(){
                Swal.fire({
                    text: 'Are you sure you want to register new order?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Register',
                    width: '25rem',
                })
                .then((result) => {
                    if(result.value){
                        this.form.post('api/order')
                        .then((res) => {
                            this.form.reset();
                            this.processForm.reset();
                            this.classificationForm.reset();
                            this.draft_primary_material = {};
                            this.draft_secondary_material = {};
                            this.draft_print = {};
                            this.printing_materials = [];                          
                            this.draft_process = {};
                            this.showAddProcess = false;
                            this.printSubconMaterialSelected = false;

                            Swal.fire(
                                'Registered!',
                                'Order successfully registered.',
                                'success'
                            )
                        })
                        .catch(error => {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops!',
                                text: 'Order registration failed.',
                            })
                            console.log(error)
                        });
                    }
                })
            },
            resetFields(){
                this.form.reset();
                this.processForm.reset();
                this.classificationForm.reset();
                this.draft_primary_material = {};
                this.draft_secondary_material = {};
                this.draft_print = {};
                this.printing_materials = [];                          
                this.draft_process = {};
                this.showAddProcess = false;
                this.printSubconMaterialSelected = false;
            },
            getOrderTypeCodes(){
                axios.get('api/get-order-type-codes')
                .then((res) => {
                    this.order_type_codes = res.data;
                })
                .catch((error) => {
                    console.log(error);
                });
            },
            typeCodeLabel(type_code, classification){
                return type_code + ' - ' + classification;
            },
            selectTypeCode(){
                this.classificationForm.type_code_id = this.form.order.order_item_type_code;
                axios.get('api/get-order-classifications/'+this.form.order.order_item_type_code)
                .then((res) => {
                    this.form.order.order_item_classification = '';
                    this.classifications = res.data;
                })
                .catch((error) => {
                    console.log(error)
                })
            },
            classificationsSetting(){
                if(this.isVisiblelassificationsSetting == false) this.isVisiblelassificationsSetting = true;
                else this.isVisiblelassificationsSetting = false;

                if(this.disableTypeCode == false) this.disableTypeCode = true;
                else this.disableTypeCode = false;
            },
            createNewClassification(){
                Swal.fire({
                    text: 'Are you sure you want to register new product classification?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Register',
                    width: '25rem',
                })
                .then((result) => {
                    if(result.value){
                        this.classificationForm.post('api/add-material-classification/'+this.classificationForm.type_code_id)
                        .then((res) => {
                            this.selectTypeCode();
                            this.isVisiblelassificationsSetting = false;
                            this.disableTypeCode = false;
                            this.classificationForm.material_classification_name = '';
                            Swal.fire(
                                'Registered!',
                                'Product classification successfully registered.',
                                'success'
                            )
                        })
                        .catch(error => {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops!',
                                text: 'Product classification registration failed.',
                            })
                            console.log(error)
                        });
                    }
                });
            },
            dimensionFormat(length, length_unit, width, width_unit){
                return length + ' ' + length_unit + ' x ' + width + ' ' + width_unit;
            },
            getMaterials(){
                axios.get('api/get-order-primary-materials')
                .then((res) => {
                    this.materials = res.data;
                })
                .catch((error) => {
                    console.log(error);
                });
            },
            selectPrimaryMaterial(){
                let material_object = _.find(this.materials, { 'id': this.draft_primary_material.material_id })
                this.draft_primary_material.material_name = material_object.material_description;
            },
            addPrimaryMaterialToList(){
                Swal.fire({
                    text: 'Are you sure you want to add primary material to list?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Add to list',
                    width: '25rem',
                })
                .then((result) => {
                    if(result.value && this.primaryMaterialValidator){
                        if(this.form.primary_materials.length > 0){
                            let id = '';
                            id = this.form.primary_materials[this.form.primary_materials.length - 1].id + 1;
                            this.draft_primary_material.id = id;
                        }

                        this.form.primary_materials.push({
                            'id': this.draft_primary_material.id,
                            'material_id': this.draft_primary_material.material_id,
                            'material_name': this.draft_primary_material.material_name,
                            'order_material_length': this.draft_primary_material.order_material_length,
                            'order_material_length_unit': this.draft_primary_material.order_material_length_unit,
                            'order_material_width': this.draft_primary_material.order_material_width,
                            'order_material_width_unit': this.draft_primary_material.order_material_width_unit,
                            'order_material_margintb': this.draft_primary_material.order_material_margintb,
                            'order_material_margintb_unit': this.draft_primary_material.order_material_margintb_unit,
                            'order_material_marginlr': this.draft_primary_material.order_material_marginlr,
                            'order_material_marginlr_unit': this.draft_primary_material.order_material_marginlr_unit,
                            'order_pages': this.draft_primary_material.order_pages,
                            'order_rolls': this.draft_primary_material.order_rolls,
                            'order_material_cut': this.draft_primary_material.order_material_cut,
                            'order_material_slit': this.draft_primary_material.order_material_slit,
                            'order_material_cylinder': this.draft_primary_material.order_material_cylinder,
                            'order_material_layout_overlap': this.draft_primary_material.order_material_layout_overlap,
                            'order_material_layout_overlap_unit': this.draft_primary_material.order_material_layout_overlap_unit,
                            'order_material_outs': this.draft_primary_material.order_material_outs,
                        });

                        let material_object = _.find(this.materials, { 'id': this.draft_primary_material.material_id });

                        if(!_.find(this.printing_materials, { 'material_id': this.draft_primary_material.material_id })){
                            let printing_material_id = 1;
                            if(this.printing_materials.length > 0) printing_material_id = this.printing_materials[this.printing_materials.length - 1].id + 1;
                            this.printing_materials.push({
                                'id': printing_material_id,
                                'material_id': this.draft_primary_material.material_id,
                                'material_name': material_object.material_description,
                                'order_material_length': material_object.material_length,
                                'order_material_length_unit': material_object.material_length_unit,
                                'order_material_width': material_object.material_width,
                                'order_material_width_unit': material_object.material_width_unit,
                            });
                        }
                        this.initializePrimaryMaterialTable();

                        Swal.fire(
                            'Added to list!',
                            'Primary material successfully added to list.',
                            'success'
                        )
                    }
                    else{
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops!',
                            text: 'Invalid primary material.',
                        })
                    }
                });
            },
            initializePrimaryMaterialTable(){
                this.draft_primary_material = {
                    id: 1,
                    material_id: '',
                    material_name: '',
                    order_material_length: '',
                    order_material_length_unit: '',
                    order_material_width: '',
                    order_material_width_unit: '',
                    order_material_margintb: '',
                    order_material_margintb_unit: '',
                    order_material_marginlr: '',
                    order_material_marginlr_unit: '',
                    order_pages: '',
                    order_rolls: '',
                    order_material_cut: '',
                    order_material_slit: '',
                    order_material_cylinder: '',
                    order_material_layout_overlap: '',
                    order_material_layout_overlap_unit: '',
                    order_material_outs: '',
                }
            },
            deletePrimaryMaterialDraft(id){
                Swal.fire({
                    text: 'Are you sure you want to delete primary material from list?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Delete',
                    width: '25rem',
                })
                .then((result) => {
                    if(result.value){
                        let material_object = _.find(this.form.primary_materials, { 'id': id });

                        for(let i = 0; i < this.printing_materials.length; i++){
                            if(this.printing_materials[i].material_id == material_object.material_id) this.printing_materials.splice(i, 1);
                        }

                        for(let i = 0; i < this.form.primary_materials.length; i++){
                            if(this.form.primary_materials[i].id == id) this.form.primary_materials.splice(i, 1);
                        }

                        Swal.fire(
                            'Deleted!',
                            'Primary material has been deleted.',
                            'success'
                        )
                    }
                });
            },
            selectSecondaryMaterial(){
                let material_object = _.find(this.materials, { 'id': this.draft_secondary_material.material_id })
                this.draft_secondary_material.material_name = material_object.material_description;
            },
            addSecondaryMaterialToList(){
                Swal.fire({
                    text: 'Are you sure you want to add secondary material to list?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Add to list',
                    width: '25rem',
                })
                .then((result) => {
                    if(result.value && this.secondaryMaterialValidator){
                        if(this.form.secondary_materials.length > 0){
                            let id = '';
                            id = this.form.secondary_materials[this.form.secondary_materials.length - 1].id + 1;
                            this.draft_secondary_material.id = id;
                        }
                        this.form.secondary_materials.push({
                            'id': this.draft_secondary_material.id,
                            'material_id': this.draft_secondary_material.material_id,
                            'material_name': this.draft_secondary_material.material_name,
                            'order_material_length': this.draft_secondary_material.order_material_length,
                            'order_material_length_unit': this.draft_secondary_material.order_material_length_unit,
                            'order_material_width': this.draft_secondary_material.order_material_width,
                            'order_material_width_unit': this.draft_secondary_material.order_material_width_unit,
                        });

                        let material_object = _.find(this.materials, { 'id': this.draft_secondary_material.material_id });

                        if(!_.find(this.printing_materials, { 'material_id': this.draft_secondary_material.material_id })){
                            let printing_material_id = 1;
                            if(this.printing_materials.length > 0) printing_material_id = this.printing_materials[this.printing_materials.length - 1].id + 1;
                            this.printing_materials.push({
                                'id': printing_material_id,
                                'material_id': this.draft_secondary_material.material_id,
                                'material_name': material_object.material_description,
                                'order_material_length': material_object.material_length,
                                'order_material_length_unit': material_object.material_length_unit,
                                'order_material_width': material_object.material_width,
                                'order_material_width_unit': material_object.material_width_unit,
                            });
                        }
                        this.initializeSecondaryMaterialTable();

                        Swal.fire(
                            'Added to list!',
                            'Secondary material successfully added to list.',
                            'success'
                        )
                    }
                    else{
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops!',
                            text: 'Invalid secondary material.',
                        })
                    }
                });
            },
            initializeSecondaryMaterialTable(){
                this.draft_secondary_material = {
                    id: 1,
                    material_id: '',
                    material_name: '',
                    order_material_length: '',
                    order_material_length_unit: '',
                    order_material_width: '',
                    order_material_width_unit: '',
                }
            },
            deleteSecondaryMaterialDraft(id){
                Swal.fire({
                    text: 'Are you sure you want to delete secondary material from list?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Delete',
                    width: '25rem',
                })
                .then((result) => {
                    if(result.value){
                        let material_object = _.find(this.form.secondary_materials, { 'id': id });
                        for(let i = 0; i < this.printing_materials.length; i++){
                            if(this.printing_materials[i].material_id == material_object.material_id) this.printing_materials.splice(i, 1);
                        }

                        for(let i = 0; i < this.form.secondary_materials.length; i++){
                            if(this.form.secondary_materials[i].id == id) this.form.secondary_materials.splice(i, 1);
                        }

                        Swal.fire(
                            'Deleted!',
                            'Secondary material has been deleted.',
                            'success'
                        )
                    }
                });
            },
            initializeSubcontractorMaterialTable(){
                this.form.subcontractor_material = {
                    material_id: '',
                    material_name: '',
                    order_material_length: '',
                    order_material_length_unit: '',
                    order_material_width: '',
                    order_material_width_unit: '',
                }
            },
            initializeSubcontractorProcessTable(){
                this.draft_process_subcon  = {
                    description: '',
                    process_name: '',
                }
            },
            resetSubcontractorMaterial(){
                Swal.fire({
                    text: 'Are you sure you want to reset subcontractor material?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Reset',
                    width: '25rem',
                })
                .then((result) => {
                    if(result.value){
                        this.initializeSubcontractorMaterialTable();

                        Swal.fire(
                            'Reset!',
                            'Subcontractor material has been reset.',
                            'success'
                        )
                    }
                });
            },
            getSCMaterials(){
                axios.get('api/get-order-sc-materials')
                .then((res) => {
                    this.sc_materials = res.data;
                })
                .catch((error) => {
                    console.log(error);
                });
            },
            addPrintToList(){
                Swal.fire({
                    text: 'Are you sure you want to add print to list?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Add to list',
                    width: '25rem',
                })
                .then((result) => {
                    if(result.value && this.printingValidator){
                        let order_regular = parseInt(this.draft_print.order_print_front_regular) + parseInt(this.draft_print.order_print_back_regular);
                        let order_special = parseInt(this.draft_print.order_print_front_special) + parseInt(this.draft_print.order_print_back_special);
                        if(this.form.prints.length > 0){
                            let id = '';
                            id = this.form.prints[this.form.prints.length - 1].id + 1;
                            this.draft_print.id = id;
                        }
                        this.form.prints.push({
                            'id': this.draft_print.id,
                            'material_id': this.draft_print.material_id,
                            'material_name': this.draft_print.material_name,
                            'order_print_item': this.draft_print.order_print_item,
                            'order_print_machine_id': this.draft_print.order_print_machine_id,
                            'order_print_front': this.draft_print.order_print_front,
                            'order_print_front_regular': this.draft_print.order_print_front_regular,
                            'order_print_front_special': this.draft_print.order_print_front_special,
                            'order_print_back': this.draft_print.order_print_back,
                            'order_print_back_regular': this.draft_print.order_print_back_regular,
                            'order_print_back_special': this.draft_print.order_print_back_special,
                            'order_print_regular': order_regular,
                            'order_print_special': order_special,
                            'order_print_cutsize': this.draft_print.order_print_cutsize,
                            'order_print_outs': this.draft_print.order_print_outs,
                            'order_print_pages': this.draft_print.order_print_pages,
                            'order_print_wit': this.draft_print.order_print_wit,
                            'machine_name': this.draft_print.machine_name,
                        });
                        this.initializePrintingTable();

                        Swal.fire(
                            'Added to list!',
                            'Print successfully added to list.',
                            'success'
                        )
                    }
                    else{
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops!',
                            text: 'Invalid printing item.',
                        })
                    }
                });
            },
            initializePrintingTable(){
                this.draft_print = {
                    id: 1,
                    material_id: '',
                    order_print_item: '',
                    order_print_machine_id: '',
                    order_print_front: '',
                    order_print_front_regular: '',
                    order_print_front_special: '',
                    order_print_back: '',
                    order_print_back_regular: '',
                    order_print_back_special: '',
                    order_print_regular: '',
                    order_print_special: '',
                    order_print_cutsize: '',
                    order_print_outs: '',
                    order_print_pages: '',
                    order_print_wit: false,
                    machine_name: '',
                    material_name: '',
                }
            },
            deletePrintDraft(id){
                Swal.fire({
                    text: 'Are you sure you want to delete print from list?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Delete',
                    width: '25rem',
                })
                .then((result) => {
                    if(result.value){
                        for(let i = 0; i < this.form.prints.length; i++){
                            if(this.form.prints[i].id == id) this.form.prints.splice(i, 1);
                        }

                        Swal.fire(
                            'Deleted!',
                            'Print has been deleted.',
                            'success'
                        )
                    }
                });
            },
            addProcessToList(){
                Swal.fire({
                    text: 'Are you sure you want to add process to list?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Add to list',
                    width: '25rem',
                })
                .then((result) => {
                    if(result.value){
                        if(this.form.process_list.length > 0){
                            let id = '';
                            id = this.form.process_list[this.form.process_list.length - 1].id + 1;
                            this.draft_process.id = id;
                        }
                        this.form.process_list.push({
                            'id': this.draft_process.id,
                            'process_id': this.draft_process.process_id,
                            'order_process_description': this.draft_process.order_process_description,
                            'process_name': this.draft_process.process_name,
                            'process_cuts': this.draft_process.cuts,
                            'process_outs': this.draft_process.outs,
                            'process_folds': this.draft_process.folds,
                            'process_size': this.draft_process.size,
                            'process_sig': this.draft_process.sig,
                            'process_gsm': this.draft_process.gsm,
                        });
                        this.initializeProcessTable();

                        Swal.fire(
                            'Added to list!',
                            'Process was successfully added to list.',
                            'success'
                        )
                    }
                });
            },
            addSubconProcessToList(){
                Swal.fire({
                    text: 'Are you sure you want to add subcontractor process to list?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Add to list',
                    width: '25rem',
                })
                .then((result) => {
                    if(result.value){
                        if(this.form.process_subcon.length > 0){
                            let id = '';
                            id = this.form.process_subcon[this.form.process_subcon.length - 1].id + 1;
                            this.draft_process_subcon.id = id;
                        }   
                        this.form.process_subcon.push({
                            'id': this.draft_process_subcon.id,
                            'process_name': this.draft_process_subcon.process_name,
                            'description': this.draft_process_subcon.description,
                        });
                        this.initializeSubcontractorProcessTable();

                        Swal.fire(
                            'Added to list!',
                            'Subcontractor processes was successfully added to list.',
                            'success'
                        )
                    }
                });
            },
            initializeProcessTable(){
                this.draft_process = {
                    id: 1,
                    process_id: '',
                    process_name: '',
                    order_process_description: '',
                    process_cuts: '',
                    process_outs: '',
                    process_folds: '',
                    process_size: '',
                    process_sig: '',
                    process_gsm: '',
                }
            },
            getProcesses(){
                axios.get('api/get-processes')
                .then((res)=>{
                    this.processes = res.data;
                })
                .catch((error) => {
                    console.log(error);
                });
            },
            selectProcess(){
                let process_object = _.find(this.processes, { 'id': this.draft_process.process_id })
                this.draft_process.process_name = process_object.name;
            },
            deleteProcessToList(id){
                console.log(id)
                Swal.fire({
                    text: 'Are you sure you want to delete process to list?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Delete',
                    width: '25rem',
                })
                .then((result) => {
                    if(result.value){
                        for(let i = 0; i < this.form.process_list.length; i++){
                            if(this.form.process_list[i].id == id) this.form.process_list.splice(i, 1);
                        }

                        Swal.fire(
                            'Deleted!',
                            'Process has been deleted.',
                            'success'
                        )
                    }
                });
            },
            deleteSubconProcessToList(id){
                console.log(id)
                Swal.fire({
                    text: 'Are you sure you want to delete subcontractor process to list?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Delete',
                    width: '25rem',
                })
                .then((result) => {
                    if(result.value){
                        for(let i = 0; i < this.form.process_subcon.length; i++){
                            if(this.form.process_subcon[i].id == id) this.form.process_subcon.splice(i, 1);
                        }

                        Swal.fire(
                            'Deleted!',
                            'Subcontractor process has been deleted.',
                            'success'
                        )
                    }
                });
            },
            selectSubcontractMaterial(material_id){
                let material_object = _.find(this.sc_materials, { 'id': this.form.subcontractor_material.material_id });
                this.form.subcontractor_material.material_name = material_object.sc_material_name;
            },
            subconMaterialFormat(){
                if(_.find(this.sc_materials, { 'id': this.form.subcontractor_material.material_id })) return this.form.subcontractor_material.material_name + ' (' + _.find(this.sc_materials, { 'id': this.form.subcontractor_material.material_id }).sc_order_part_no + ')';
            },
            addProcess(){
                Swal.fire({
                    text: 'Are you sure you want to add process to list?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Add to list',
                    width: '25rem',
                })
                .then((result) => {
                    this.processForm.post('/api/process')
                    Swal.fire(
                        'Added!',
                        'Process was successfully added.',
                        'success'
                    )
                    this.getProcesses();
                })
                .catch((error) => {
                    console.log(error);
                });
            },
            selectSubconAsMaterial(){
                if(!this.subconMaterialValidator){
                    this.printSubconMaterialSelected = false;
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops!',
                        text: 'Invalid subcontractor material.',
                    })
                }

                if(this.printSubconMaterialSelected){
                    this.draft_print.material_id = this.form.subcontractor_material.material_id;
                    this.draft_print.material_name = this.form.subcontractor_material.material_name;
                }
                else{
                    this.draft_print.material_id = '';
                    this.draft_print.material_name = '';
                }
            },
            selectMachine(machine_id){
                let machine_object = _.find(this.$machines, { 'id': machine_id });
                this.draft_print.machine_name = machine_object.machine_name;
            },
            selectPrintRawMaterial(material_id){
                let material_object = _.find(this.materials, { 'id': material_id });
                this.draft_print.material_name = material_object.material_description;
            }
        },
        created(){
            this.getOrderTypeCodes();
            this.getMaterials();
            this.getSCMaterials();
            this.initializePrimaryMaterialTable();
            this.initializeSecondaryMaterialTable();
            this.initializeSubcontractorMaterialTable();
            this.initializeSubcontractorProcessTable();
            this.initializePrintingTable();
            this.getProcesses();
            this.initializeProcessTable();
        },
        computed: {
            primaryMaterialValidator(){
                if(this.draft_primary_material.material_id &&
                this.draft_primary_material.order_material_length &&
                this.draft_primary_material.order_material_length_unit &&
                this.draft_primary_material.order_material_width &&
                this.draft_primary_material.order_material_width_unit &&
                this.draft_primary_material.order_material_margintb &&
                this.draft_primary_material.order_material_margintb_unit &&
                this.draft_primary_material.order_material_marginlr &&
                this.draft_primary_material.order_material_marginlr_unit &&
                this.draft_primary_material.order_pages &&
                this.draft_primary_material.order_rolls &&
                this.draft_primary_material.order_material_cut &&
                this.draft_primary_material.order_material_slit &&
                this.draft_primary_material.order_material_cylinder &&
                this.draft_primary_material.order_material_layout_overlap &&
                this.draft_primary_material.order_material_layout_overlap_unit &&
                this.draft_primary_material.order_material_outs) return true;
                else false;
            },
            secondaryMaterialValidator(){
                if(this.draft_secondary_material.material_id &&
                this.draft_secondary_material.order_material_length &&
                this.draft_secondary_material.order_material_length_unit &&
                this.draft_secondary_material.order_material_width &&
                this.draft_secondary_material.order_material_width_unit) return true;
                else false;
            },
            subconMaterialValidator(){
                if(this.form.subcontractor_material.material_name &&
                this.form.subcontractor_material.order_material_length &&
                this.form.subcontractor_material.order_material_length_unit &&
                this.form.subcontractor_material.order_material_width &&
                this.form.subcontractor_material.order_material_width_unit) return true;
                else false;
            },
            printingValidator(){
                if(this.draft_print.material_id &&
                this.draft_print.order_print_item &&
                this.draft_print.order_print_machine_id &&
                this.draft_print.order_print_front_regular &&
                this.draft_print.order_print_front_special &&
                this.draft_print.order_print_back_regular &&
                this.draft_print.order_print_back_special &&
                this.draft_print.order_print_cutsize &&
                this.draft_print.order_print_outs &&
                this.draft_print.order_print_pages) return true;
                else false;
            },
            colorFront(){
                let front_regular = parseInt(this.draft_print.order_print_front_regular);
                let front_special =  parseInt(this.draft_print.order_print_front_special);
                if(isNaN(front_regular)) front_regular = 0;
                if(isNaN(front_special)) front_special = 0;
                return this.draft_print.order_print_front = front_regular + front_special;
            },
            colorBack(){
                let back_regular = parseInt(this.draft_print.order_print_back_regular);
                let back_special =  parseInt(this.draft_print.order_print_back_special);
                if(isNaN(back_regular)) back_regular = 0;
                if(isNaN(back_special)) back_special = 0;
                return this.draft_print.order_print_back =  back_regular + back_special;
            },
        }
    }
</script>
