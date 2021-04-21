<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use Carbon\Carbon;
use App\Materials;
use App\MaterialsTracking;
use App\RollMaterialTracking;
use App\TypeCode;
use App\MaterialClassifications;
use App\Client;
use App\Suppliers;
use App\SupplierMaterial;
use App\SCMaterials;
use App\SCMaterialsTracking;
use App\SupplierSCMaterial;

class TableDataController extends Controller
{
    public function RMQueries()
    {
        $data = DB::table('rm_items')->get();
        $clients = Client::get();
        $type_codes = TypeCode::get();
        $classifications = MaterialClassifications::get();
        $suppliers = Suppliers::get();
        
        Materials::truncate();
        MaterialsTracking::truncate();
        RollMaterialTracking::truncate();

        for($i = 0; $i < count($data); $i++){
            $material_type_id = 0;
            for($j = 0; $j < count($type_codes); $j++){
                if($type_codes[$j]->type_code == $data[$i]->type_code){
                    $material_type_id = $type_codes[$j]->id;
                }
            }

            $material_classification_id = 0;
            for($k = 0; $k < count($classifications); $k++){
                if(strcasecmp($classifications[$k]->material_classification_name, $data[$i]->material_classification) == 0){
                    $material_classification_id = $classifications[$k]->id;
                }
            }

            $material_length = 0;
            $material_length_unit = 'xx';
            $material_width = 0;
            $material_width_unit = 'xx';
            $dimension = 0;

            $dimension = explode("x", $data[$i]->dimension);
            $unit_array = explode("x", preg_replace('/[0-9]+/', '', $data[$i]->dimension));
            $value_array = explode("x", $data[$i]->dimension);

            $counter = 1;
            foreach($value_array as $array){
                $value = preg_replace('~\D~', '', $array);
                if($counter == 1) $material_length = $value;
                else $material_width = $value;
                $counter++;
            }

            // return $suppliers;

            $material_id = Materials::insertGetId([
                'material_description' => $data[$i]->material_description,
                'material_type_id' => $material_type_id,
                'material_classification_id' => $material_classification_id,
                'material_length' => $data[$i]->length,
                'material_length_unit' => $data[$i]->length_unit,
                'material_width' => $data[$i]->width,
                'material_width_unit' => $data[$i]->width_unit,
                'material_thickness' => $data[$i]->thickness,
                'material_thickness_unit' => $data[$i]->thickness_unit,
                'material_last_in' => Carbon::now(),
                'material_location' => 'FOR UPDATE',
                'employee_id' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            $supplier_material_id = SupplierMaterial::insertGetId([
                'material_id' => $material_id,
                'supplier_id' => 1,
                'material_code' => $data[$i]->item_code,
                'employee_id' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            $material_tracking_id = MaterialsTracking::insertGetId([
                'material_id' => $material_id,
                'supplier_material_id' => $supplier_material_id,
                'material_flow' => 1,
                'material_quantity' => 1,
                'material_quantity_unit' => 1,
                'material_unit_amount' => 1,
                'material_unit_amount_currency' => 'PHP',
                'employee_id' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            if($material_type_id == 8){
                RollMaterialTracking::create([
                    'material_id' => $material_id,
                    'material_tracking_id' => $material_tracking_id,
                    'roll_length' => 1,
                    'roll_length_unit' => 'm',
                    'stock_roll_length' => 1,
                    'stock_roll_quantity' => 1,
                    'employee_id' => 1,
                ]);
            }
        }

        // foreach($data as $item){
        //     // DB::table('rm_items')->where('id', $item->id)->update([
        //     //     'dimension' => str_replace('x', 'X', $item->dimension)
        //     // ]);
        //     // DB::table('rm_items')->where('id', $item->id)->update([
        //     //     'dimension' => str_replace(' ', '', $item->dimension)
        //     // ]);
        //     // DB::table('rm_items')->where('id', $item->id)->update([
        //     //     'dimension' => str_replace("''", 'in', $item->dimension)
        //     // ]);
        //     // DB::table('rm_items')->where('id', $item->id)->update([
        //     //     'dimension' => str_replace('"', 'in', $item->dimension)
        //     // ]);
        //     // DB::table('rm_items')->where('id', $item->id)->update([
        //     //     'dimension' => strtolower($item->dimension)
        //     // ]);  
        //     // DB::table('rm_items')->where('id', $item->id)->update([
        //     //     'dimension' => str_replace(',', '', $item->dimension)
        //     // ]);

        //     $dimension = explode("x", $item->dimension);
        //     $unit_array = explode("x", preg_replace('/[0-9]+/', '', $item->dimension));
        //     $value_array = explode("x", $item->dimension);
        //     $counter = 1;
        //     foreach($value_array as $array){
        //         $value = preg_replace('/[^0-9,.]/', '', $array);
        //         if($counter == 1) $length = $value;
        //         else $width = $value;
        //         $counter++;
        //     }
            
        //     $first_string = substr($item->item_code, -12, -6);
        //     $second_string = substr($item->item_code, strpos($item->item_code, '-') + 3);

        //     DB::table('rm_items')->where('id', $item->id)->update([
        //         'item_code' => $first_string.'0'.$second_string,
        //         'length' => $length,
        //         'length_unit' => $unit_array[0],
        //         'width' => $width,
        //         'width_unit' => $unit_array[1]
        //     ]);

        //     DB::table('rm_items')->where('id', $item->id)->update([
        //         'length_unit' => str_replace('.in', 'in', $item->length_unit),
        //         'width_unit' => str_replace('.in', 'in', $item->width_unit)
        //     ]);
        // }

        return ['message' => 'query successfully executed'];
    }

    public function SupplierQueries()
    {
        $suppliers = Suppliers::get();

        for($i = 0; $i < count($suppliers); $i++){
            $series = $i + 1;
            Suppliers::where('id', $suppliers[$i]->id)->update([
                'supplier_short_name' => 'ADAMAY-'.$series
            ]);
        }
        return ['message' => 'query successfully executed'];
    }

    public function SCQueries()
    {
        $data = DB::table('sc_items')->get();
        $clients = Client::get();
        $type_codes = TypeCode::get();
        $classifications = MaterialClassifications::get();
        $suppliers = Suppliers::get();
        
        SCMaterials::truncate();
        SupplierSCMaterial::truncate();
        SCMaterialsTracking::truncate();

        for($i = 0; $i < count($data); $i++){
            $material_type_id = 0;
            for($j = 0; $j < count($type_codes); $j++){
                if($type_codes[$j]->type_code == $data[$i]->type_code){
                    $material_type_id = $type_codes[$j]->id;
                }
            }

            $material_classification_id = 0;
            for($k = 0; $k < count($classifications); $k++){
                if(strcasecmp($classifications[$k]->material_classification_name, $data[$i]->material_classification) == 0){
                    $material_classification_id = $classifications[$k]->id;
                }
            }

            $sc_material_id = SCMaterials::insertGetId([
                'sc_material_name' => $data[$i]->description,
                'sc_order_part_no' => $data[$i]->part_number,
                'sc_material_classification_id' => $material_classification_id,
                'sc_material_type_id' => $material_type_id,
                'sc_material_last_in' => Carbon::now(),
                'sc_material_last_out' => NULL,
                'sc_material_location' => 'FOR UPDATE',
                'employee_id' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
            
            $supplier_sc_material_id = SupplierSCMaterial::insertGetId([
                'sc_material_id' => $sc_material_id,
                'supplier_id' => 1,
                'sc_material_code' => $data[$i]->item_code,
                'employee_id' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'deleted_at' => NULL
            ]);

            $material_tracking_id = SCMaterialsTracking::insertGetId([
                'sc_material_id' => $sc_material_id,
                'supplier_sc_material_id' => $supplier_sc_material_id,
                'sc_material_flow' => 1,
                'sc_material_quantity' => 1,
                'sc_material_quantity_unit' => 1,
                'sc_material_unit_amount' => 1,
                'sc_material_unit_amount_currency' => 'PHP',
                'employee_id' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }

        // foreach($data as $item){            
        //     $first_string = substr($item->item_code, -12, -6);
        //     $second_string = substr($item->item_code, strpos($item->item_code, '-') + 3);
        //     // return $first_string.'0'.$second_string;
        //     DB::table('sc_items')->where('id', $item->id)->update([
        //         'item_code' => $first_string.'0'.$second_string
        //     ]);
        // }

        return ['message' => 'query successfully executed'];
    }
}
