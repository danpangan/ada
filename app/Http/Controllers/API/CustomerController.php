<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Client;
use App\ClientDetails;
use Carbon\Carbon;
use App\User;
use App\ClientContacts;
use App\ClientDetailContacts;
use App\Municipalities;
use App\Provinces;
use \stdClass;
use DB;
use App\ClientPO;
use App\Orders;
use App\ClientPODeliveryMonitoring;
use App\ComputationSheet;
use App\Quotation;
// use App\Quotation; Salesheets
use App\ClientForecast;
use App\ClientPOInvoice;
use App\ClientPOReceipt;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $sortProp = $request['sortProp'];
        $sortOrder = $request['sortOrder'];

        if($sortProp != 'undefined' && $sortOrder != 'undefined'){
            if($sortOrder === 'descending') $sortOrder = 'desc';
            else $sortOrder = 'asc';
        }
        else{
            $sortProp = 'client_name';
            $sortOrder = 'asc';
        }

        if($request['showDeleted'] == 'true'){
            return Client::onlyTrashed()
                ->where(function($query) use ($request){
                    $query->where('client_name','like','%'.$request['search'].'%')
                        ->orWhere('client_address','like','%'.$request['search'].'%')
                        ->orWhere('client_short_name','like','%'.$request['search'].'%')
                        ->orWhere('client_contact','like','%'.$request['search'].'%');
                })
                ->orderBy($sortProp, $sortOrder)
                ->paginate($request['take']);
        }
        else{
            return Client::where(function($query) use ($request){
                $query->where('client_name','like','%'.$request['search'].'%')
                    ->orWhere('client_address','like','%'.$request['search'].'%')
                    ->orWhere('client_short_name','like','%'.$request['search'].'%')
                    ->orWhere('client_contact','like','%'.$request['search'].'%');
            })
                ->orderBy($sortProp, $sortOrder)
                ->paginate($request['take']);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        // $this->validate($request, [
        //     'formClient.client_name' => 'required|regex:/^[\pL\s\-]+$/u|max:30',
        //     'formClient.client_address' => 'required|regex:/^[\pL\s\-]+$/u|max:30',
        //     'formClient.client_contact' => 'required|regex:/^[\pL\s\-]+$/u|max:30',
        //     'formClient.client_pay_terms' => 'required|regex:/^[\pL\s\-]+$/u|max:30',
        //     'formClient.client_province_id' => 'required|regex:/^[\pL\s\-]+$/u|max:30',
        //     'formClient.client_municipality_id' => 'required|regex:/^[\pL\s\-]+$/u|max:30',
        //     'formClient.pic.*' => 'required|regex:/^[\pL\s\-]+$/u|max:30',
        // ]);

        $client_province_id = $request['formClient']['client_province_id'];
        $client_municipality_id = $request['formClient']['client_municipality_id'];
        
        if($request['formClient']['client_province_id']){
            $series = count(Client::where('client_province_id', $client_province_id)->where('client_municipality_id', $client_municipality_id)->get()) + 1;

            $series_length = strlen($series);
            if($series_length == 1) $series_format = '000'.$series;
            else if($series_length == 2) $series_format = '00'.$series;
            else if($series_length == 3) $series_format = '0'.$series;
            else $series_format = $series;

            $province_code_length = strlen($client_province_id);
            if($province_code_length == 1) $province_code = '00'.$client_province_id;
            else if($province_code_length == 2) $province_code = '0'.$client_province_id;
            else $province_code = $client_province_id;

            $municipality_code_length = strlen($client_municipality_id);
            if($municipality_code_length == 1) $municipality_code = '000'.$client_municipality_id;
            else if($municipality_code_length == 2) $municipality_code = '00'.$client_municipality_id;
            else if($municipality_code_length == 3) $municipality_code = '0'.$client_municipality_id;
            else $municipality_code = $client_municipality_id;

            $client_number = '1-'.$province_code.'-'.$municipality_code.'-'.$series_format;
        }
        else{
            $series = count(Client::where('client_province_id', NULL)->where('client_municipality_id', NULL)->get()) + 1;

            $series_length = strlen($series);
            if($series_length == 1) $series_format = '000'.$series;
            else if($series_length == 2) $series_format = '00'.$series;
            else if($series_length == 3) $series_format = '0'.$series;
            else $series_format = $series;

            $client_number = '0-'.'000-'.'0000-'.$series_format;
        }

        try{
            DB::beginTransaction();

            $client_id = Client::insertGetId([
                'client_name' => $request['formClient']['client_name'],
                'client_short_name' => $request['formClient']['client_short_name'],
                'client_order_description' => $request['formClient']['client_order_description'],
                'client_number' => $client_number,
                'client_address' => $request['formClient']['client_address'],
                'client_contact' => $request['formClient']['client_contact'],
                'client_pay_terms' => $request['formClient']['client_pay_terms'],
                'client_province_id' => $request['formClient']['client_province_id'],
                'client_municipality_id' => $request['formClient']['client_municipality_id'],
                'is_temporary' => $request['isTemporaryCustomer'],
                'isSameAsCustomerDetails' => $request['isSameAsCustomerDetails'],
                'isSameAsBillingDetails' => $request['isSameAsBillingDetails'],
                'isCustomerConsignee' => $request['isCustomerConsignee'],
                'employee_id' => auth('api')->user()->id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            Provinces::where('id', $request['formClient']['client_province_id'])->update(['is_customer_active' => 1]);
            Municipalities::where('id', $request['formClient']['client_municipality_id'])->update(['is_customer_active' => 1]);
            
            for($i = 0; $i < count($request['formClient']['pic']); $i++){
                ClientContacts::create([
                    'client_id' => $client_id,
                    'client_pic' => $request['formClient']['pic'][$i]['client_pic'],
                    'client_pic_contact' => $request['formClient']['pic'][$i]['client_pic_contact'],
                    'client_pic_email' => $request['formClient']['pic'][$i]['client_pic_email'],
                    'client_pic_title' => $request['formClient']['pic'][$i]['client_pic_title'],
                    'client_pic_department' => $request['formClient']['pic'][$i]['client_pic_department'],
                    'employee_id' => auth('api')->user()->id,
                ]);
            }
            
            if($request['isTemporaryCustomer'] == false){
                $client_detail_billing_id = ClientDetails::insertGetId([
                    'client_id' => $client_id,
                    'client_name' => $request['formBilling']['client_name'],
                    'client_address' => $request['formBilling']['client_address'],
                    'client_province_id' => $request['formBilling']['client_province_id'],
                    'client_municipality_id' => $request['formBilling']['client_municipality_id'],
                    'client_detail_status' => $request['formBilling']['client_detail_status'],
                    'employee_id' => auth('api')->user()->id,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);

                for($i = 0; $i < count($request['formBilling']['pic']); $i++){
                    ClientDetailContacts::create([
                        'client_detail_id' => $client_detail_billing_id,
                        'client_pic' => $request['formBilling']['pic'][$i]['client_pic'],
                        'client_pic_contact' => $request['formBilling']['pic'][$i]['client_pic_contact'],
                        'client_pic_email' => $request['formBilling']['pic'][$i]['client_pic_email'],
                        'client_pic_title' => $request['formBilling']['pic'][$i]['client_pic_title'],
                        'client_pic_department' => $request['formBilling']['pic'][$i]['client_pic_department'],
                        'client_pic_type' => 1,
                        'employee_id' => auth('api')->user()->id,
                    ]);
                }
                
                if($request['formBilling']['client_detail_status'] != 1){
                    for($i = 0; $i < count($request['formDelivery']); $i++){
                        $client_detail_delivery_id = ClientDetails::insertGetId([
                            'client_id' => $client_id,
                            'client_name' => $request['formDelivery'][$i]['client_name'],
                            'client_address' => $request['formDelivery'][$i]['client_address'],
                            'client_province_id' => $request['formDelivery'][$i]['client_province_id'],
                            'client_municipality_id' => $request['formDelivery'][$i]['client_municipality_id'],
                            'client_detail_status' => $request['formDelivery'][$i]['client_detail_status'],
                            'employee_id' => auth('api')->user()->id,
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now(),
                        ]);

                        for($j = 0; $j < count($request['formDelivery'][$i]['pic']); $j++){
                            ClientDetailContacts::create([
                                'client_detail_id' => $client_detail_delivery_id,
                                'client_pic' => $request['formDelivery'][$i]['pic'][$j]['client_pic'],
                                'client_pic_contact' => $request['formDelivery'][$i]['pic'][$j]['client_pic_contact'],
                                'client_pic_email' => $request['formDelivery'][$i]['pic'][$j]['client_pic_email'],
                                'client_pic_title' => $request['formDelivery'][$i]['pic'][$j]['client_pic_title'],
                                'client_pic_department' => $request['formDelivery'][$i]['pic'][$j]['client_pic_department'],
                                'client_pic_type' => 1,
                                'employee_id' => auth('api')->user()->id,
                            ]);
                        }
                    }
                }
            }
    
            DB::commit();
            return ['message' => 'Client successfully registered.'];
        }
        catch(\Exception $e){
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($client)
    {
        // $clients = Client::get();
        // for($i = 0; $i < count($clients); $i++){
        //     ClientDetails::create([
        //         'client_id' => $clients[$i]['id'],
        //         'client_name' => $clients[$i]['client_name'],
        //         'client_province_id' => $clients[$i]['client_province_id'],
        //         'client_municipality_id' => $clients[$i]['client_municipality_id'],
        //         'client_address' => $clients[$i]['client_address'],
        //         'client_detail_status' => 1,
        //         'employee_id' => 1,
        //         'created_at' => Carbon::now(),
        //     ]);
        // }
        $client = (int)$client;
        $main_client = Client::select(
            'id',
            'client_name', 
            'client_short_name', 
            'client_order_description',
            'client_number',
            'client_province_id',
            'client_municipality_id',
            'client_address', 
            'client_contact', 
            'client_pay_terms', 
            'is_temporary',
            'isSameAsCustomerDetails',
            'isSameAsBillingDetails',
            'isCustomerConsignee'
        )->withTrashed()->where('id', $client)->first();

        $status_1 = ClientDetails::where('client_id', $client)->where('client_detail_status', 1)->first();
        $status_2 = ClientDetails::where('client_id', $client)->where('client_detail_status', 2)->first();
        $status_3 = ClientDetails::where('client_id', $client)->where('client_detail_status', 3)->get();
        $status_4 = ClientDetails::where('client_id', $client)->where('client_detail_status', 4)->first();

        $status_array = ClientDetails::where('client_id', $client)->orderBy('client_detail_status')->pluck('client_detail_status')->toArray();

        $form_client = new stdClass();
        $form_billing = new stdClass();
        $form_delivery = [];

        if($main_client->is_temporary == 0){
            if(in_array(1, $status_array)){
                $form_client = $status_1;
                $form_billing = $status_1;
                $form_delivery = ClientDetails::where('client_id', $client)->where('client_detail_status', 1)->get();
            }
            if(in_array(2, $status_array)){
                $form_client = $status_2;
                $form_billing = $status_2;
                $form_delivery = $status_3;
            }
            if(in_array(4, $status_array)){
                $form_client = $main_client;
                $form_billing = $status_4;
                $form_delivery = $status_3;
            }

            $formClient = $main_client;
            $formClient->detail_id = $form_client->id;
            $formClient->key = 1;
            $formClient->pic = ClientContacts::where('client_id', $formClient['id'])->get();

            $formBilling = new stdClass();
            $formBilling->client_name = $form_billing->client_name;
            $formBilling->client_address = $form_billing->client_address;
            $formBilling->client_province_id = $form_billing->client_province_id;
            $formBilling->client_municipality_id = $form_billing->client_municipality_id;
            $formBilling->client_detail_status = $form_billing->client_detail_status;
            $formBilling->detail_id = $form_billing->id;
            $formBilling->key = 1;
            $formBilling->pic = ClientDetailContacts::where('client_detail_id', $form_billing->id)->get();
            
            $form_delivery_array = [];
            $key = 1;
            for($i = 0; $i < count($form_delivery); $i++){
                $formDelivery = new stdClass();
                $formDelivery->client_name = $form_delivery[$i]->client_name;
                $formDelivery->client_address = $form_delivery[$i]->client_address;
                $formDelivery->client_province_id = $form_delivery[$i]->client_province_id;
                $formDelivery->client_municipality_id = $form_delivery[$i]->client_municipality_id;
                $formDelivery->client_detail_status = $form_delivery[$i]->client_detail_status;
                $formDelivery->detail_id = $form_delivery[$i]->id;
                $formDelivery->key = $key;
                $form_delivery_array[] = $formDelivery;

                $billing_pic = $formBilling->pic;
                $delivery_pic = ClientDetailContacts::where('client_detail_id', $form_delivery[$i]->id)->get();
                if(count($delivery_pic) > 0) $formDelivery->pic = $delivery_pic;
                else $formDelivery->pic = $delivery_pic;

                $key++;
            }
    
            $client_data = new stdClass();
            $client_data->formClient = $formClient;
            $client_data->formBilling = $formBilling;
            $client_data->formDelivery = $form_delivery_array;
        }
        else{
            $client_data = new stdClass();
            $client_data->formClient = $main_client;
        }

        $client_data->isSameAsCustomerDetails = $main_client->isSameAsCustomerDetails;
        $client_data->isSameAsBillingDetails = $main_client->isSameAsBillingDetails;
        $client_data->isCustomerConsignee = $main_client->isCustomerConsignee;

        return response()->json($client_data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $main_client = Client::withTrashed()->where('id', $id)->first();

        try{
            DB::beginTransaction();

            Client::where('id', $id)->update([
                'client_name' => $request['formClient']['client_name'],
                'client_short_name' => $request['formClient']['client_short_name'],
                'client_order_description' => $request['formClient']['client_order_description'],
                'client_number' => $request['formClient']['client_number'],
                'client_address' => $request['formClient']['client_address'],
                'client_province_id' => $request['formClient']['client_province_id'],
                'client_municipality_id' => $request['formClient']['client_municipality_id'],
                'client_contact' => $request['formClient']['client_contact'],
                'client_pay_terms' => $request['formClient']['client_pay_terms'],
                'is_temporary' => $request['isTemporaryCustomer'],
                'isSameAsCustomerDetails' => $request['isSameAsCustomerDetails'],
                'isSameAsBillingDetails' => $request['isSameAsBillingDetails'],
                'isCustomerConsignee' => $request['isCustomerConsignee'],
                'employee_id' => auth('api')->user()->id,
                'updated_at' => Carbon::now(),
            ]);

            Provinces::where('id', $request['formClient']['client_province_id'])->update(['is_customer_active' => 1]);
            Municipalities::where('id', $request['formClient']['client_municipality_id'])->update(['is_customer_active' => 1]);

            for($i = 0; $i < count($request['formClient']['pic']); $i++){
                if($request['formClient']['pic'][$i]['deleted_at'] != NULL){
                    ClientContacts::where('id', $request['formClient']['pic'][$i]['id'])->delete();
                }
                else{
                    if($request['formClient']['pic'][$i]['id'] <= 0){
                        ClientContacts::create([
                            'client_id' => $id,
                            'client_pic' => $request['formClient']['pic'][$i]['client_pic'],
                            'client_pic_contact' => $request['formClient']['pic'][$i]['client_pic_contact'],
                            'client_pic_email' => $request['formClient']['pic'][$i]['client_pic_email'],
                            'client_pic_title' => $request['formClient']['pic'][$i]['client_pic_title'],
                            'client_pic_department' => $request['formClient']['pic'][$i]['client_pic_department'],
                            'employee_id' => auth('api')->user()->id,
                        ]);
                    }
                    else{
                        ClientContacts::where('id', $request['formClient']['pic'][$i]['id'])->update([
                            'client_id' => $request['formClient']['pic'][$i]['client_id'],
                            'client_pic' => $request['formClient']['pic'][$i]['client_pic'],
                            'client_pic_contact' => $request['formClient']['pic'][$i]['client_pic_contact'],
                            'client_pic_email' => $request['formClient']['pic'][$i]['client_pic_email'],
                            'client_pic_title' => $request['formClient']['pic'][$i]['client_pic_title'],
                            'client_pic_department' => $request['formClient']['pic'][$i]['client_pic_department'],
                            'employee_id' => auth('api')->user()->id,
                        ]);
                    }
                }
            }
    
            if($request['isTemporaryCustomer'] == true){
                ClientDetails::withTrashed()->where('client_id', $id)->delete();
                Client::withTrashed()->where('id', $id)->update([
                    'client_province_id' => 0,
                    'client_municipality_id' => 0,
                    'client_pay_terms' => 0,
                    'isSameAsCustomerDetails' => 0,
                    'isSameAsBillingDetails' => 0,
                    'isCustomerConsignee' => 0,
                ]);
            }
            else{
                $billing_details = ClientDetails::where('id', $request['formBilling']['detail_id'])->update([
                    'client_id' => $id,
                    'client_name' => $request['formBilling']['client_name'],
                    'client_address' => $request['formBilling']['client_address'],
                    'client_province_id' => $request['formBilling']['client_province_id'],
                    'client_municipality_id' => $request['formBilling']['client_municipality_id'],
                    'client_detail_status' => $request['formBilling']['client_detail_status'],
                    'employee_id' => auth('api')->user()->id,
                ]);
                
                if($request['formBilling']['client_detail_status'] != 1){

                    for($i = 0; $i < count($request['formDelivery']); $i++){
                        $delivery_details = ClientDetails::updateOrCreate([
                            'client_id' => $id,
                            'client_name' => $request['formDelivery'][$i]['client_name'],
                            'client_address' => $request['formDelivery'][$i]['client_address'],
                            'client_province_id' => $request['formDelivery'][$i]['client_province_id'],
                            'client_municipality_id' => $request['formDelivery'][$i]['client_municipality_id'],
                            'client_detail_status' => 3,
                            'employee_id' => auth('api')->user()->id,
                        ]);

                        for($j = 0; $j < count($request['formDelivery'][$i]['pic']); $j++){
                            if($request['formDelivery'][$i]['pic'][$j]['deleted_at'] != NULL){
                                ClientDetailContacts::where('id', $request['formDelivery'][$i]['pic'][$j]['id'])->delete();
                            }
                            else{
                                if($request['formDelivery'][$i]['pic'][$j]['id'] <= 0){
                                    ClientDetailContacts::create([
                                        'client_detail_id' => $delivery_details->id,
                                        'client_pic' => $request['formDelivery'][$i]['pic'][$j]['client_pic'],
                                        'client_pic_contact' => $request['formDelivery'][$i]['pic'][$j]['client_pic_contact'],
                                        'client_pic_email' => $request['formDelivery'][$i]['pic'][$j]['client_pic_email'],
                                        'client_pic_title' => $request['formDelivery'][$i]['pic'][$j]['client_pic_title'],
                                        'client_pic_department' => $request['formDelivery'][$i]['pic'][$j]['client_pic_department'],
                                        'employee_id' => auth('api')->user()->id,
                                    ]);
                                }
                                else{
                                    ClientDetailContacts::where('id', $request['formDelivery'][$i]['pic'][$j]['id'])->update([
                                        'client_detail_id' => $delivery_details->id,
                                        'client_pic' => $request['formDelivery'][$i]['pic'][$j]['client_pic'],
                                        'client_pic_contact' => $request['formDelivery'][$i]['pic'][$j]['client_pic_contact'],
                                        'client_pic_email' => $request['formDelivery'][$i]['pic'][$j]['client_pic_email'],
                                        'client_pic_title' => $request['formDelivery'][$i]['pic'][$j]['client_pic_title'],
                                        'client_pic_department' => $request['formDelivery'][$i]['pic'][$j]['client_pic_department'],
                                        'employee_id' => auth('api')->user()->id,
                                    ]);
                                }
                            }
                        }
                    }
                }
                // else{
                //     $detail_ids = ClientDetails::where('client_id', $id)->pluck('id')->toArray();
                //     ClientDetailContacts::whereIn('client_detail_id', $detail_ids)->delete();
                //     ClientDetails::where('client_id', $id)->where('client_detail_status', 3)->delete();
                // }

                for($i = 0; $i < count($request['formBilling']['pic']); $i++){
                    if($request['formBilling']['pic'][$i]['deleted_at'] != NULL){
                        ClientDetailContacts::where('id', $request['formBilling']['pic'][$i]['id'])->delete();
                    }
                    else{
                        if($request['formBilling']['pic'][$i]['id'] <= 0){
                            ClientDetailContacts::create([
                                'client_detail_id' => $request['formBilling']['detail_id'],
                                'client_pic' => $request['formBilling']['pic'][$i]['client_pic'],
                                'client_pic_contact' => $request['formBilling']['pic'][$i]['client_pic_contact'],
                                'client_pic_email' => $request['formBilling']['pic'][$i]['client_pic_email'],
                                'client_pic_title' => $request['formBilling']['pic'][$i]['client_pic_title'],
                                'client_pic_department' => $request['formBilling']['pic'][$i]['client_pic_department'],
                                'employee_id' => auth('api')->user()->id,
                            ]);
                        }
                        else{
                            ClientDetailContacts::where('id', $request['formBilling']['pic'][$i]['id'])->update([
                                'client_detail_id' => $request['formBilling']['detail_id'],
                                'client_pic' => $request['formBilling']['pic'][$i]['client_pic'],
                                'client_pic_contact' => $request['formBilling']['pic'][$i]['client_pic_contact'],
                                'client_pic_email' => $request['formBilling']['pic'][$i]['client_pic_email'],
                                'client_pic_title' => $request['formBilling']['pic'][$i]['client_pic_title'],
                                'client_pic_department' => $request['formBilling']['pic'][$i]['client_pic_department'],
                                'employee_id' => auth('api')->user()->id,
                            ]);
                        }
                    }
                }
            }

            DB::commit();
            return ['message' => 'Client successfully updated.'];
        }
        catch(\Exception $e){
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try{
            DB::beginTransaction();

            $client = Client::withTrashed()->findOrFail($id);

            if($client->deleted_at){
                $client->restore();
            }
            else{
                $client->delete();
            }
    
            DB::commit();
            return ['message' => 'Client deleted'];
        }
        catch(\Exception $e){
            DB::rollback();
            throw $e;
        }
    }

    public function adminValidation(Request $request){
        $admin_username = $request['username'];
        $admin_password = $request['password'];

        $admin = User::where('username', $admin_username)->first();

        if(!\Hash::check($admin_password, $admin->password) || $admin->user_role > 1){
            return response('Invalidated', 400);
        }
        else{
            return response('Validated', 200);
        }
    }

    public function getProvinces(){
        return Provinces::select('id', 'province_name')->get();
    }

    public function getMunicipalities(Request $request){
        if($request['province_id'] > 0) return Municipalities::select('id', 'municipality_name')->where('province_id', $request['province_id'])->get();
        else return Municipalities::select('id', 'municipality_name', 'province_id')->get();
    }

    public function getClients(){
        return Client::select('id', 'client_number', 'client_name', 'client_short_name', 'client_address')->get();
    } 

    // Customer Sales Monitoring*****************************************************************************************************************

    public function getClientPO(Request $request, $id){
        
        $sortProp = $request['sortProp'];
        $sortOrder = $request['sortOrder'];

        if($sortProp != 'undefined' && $sortOrder != 'undefined'){
            if($sortOrder === 'descending') $sortOrder = 'desc';
            else $sortOrder = 'asc';
        }
        else{
            $sortProp = 'id';
            $sortOrder = 'asc';
        }

        if($request['showDeleted'] == 'true'){
            $po = ClientPO::onlyTrashed()->select('client_po.*', 'clients.client_short_name as client_short_name', 'client_contacts.client_pic as pic_name')
                ->where('client_po.client_id', $id)
                ->where(function($query) use ($request){
                    $query->where('client_po.client_po_number','like','%'.$request['search'].'%');
                })
                ->leftJoin('clients', 'clients.id', 'client_po.client_id')
                ->leftJoin('client_contacts', 'client_contacts.id', 'client_po.client_pic')
                ->orderBy($sortProp, $sortOrder)
                ->paginate($request['take']);
        }
        else{
            $po = ClientPO::select('client_po.*', 'clients.client_short_name as client_short_name', 'client_contacts.client_pic as pic_name')
                ->where('client_po.client_id', $id)
                ->where(function($query) use ($request){
                    $query->where('client_po.client_po_number','like','%'.$request['search'].'%');
                })
                ->leftJoin('clients', 'clients.id', 'client_po.client_id')
                ->leftJoin('client_contacts', 'client_contacts.id', 'client_po.client_pic')
                ->orderBy($sortProp, $sortOrder)
                ->paginate($request['take']);
        }

        return ['id'=>$id, 'type' => 'PO', 'po' => $po];
    }
    public function getClientOrders(Request $request, $id){
        
        $sortProp = $request['sortProp'];
        $sortOrder = $request['sortOrder'];

        if($sortProp != 'undefined' && $sortOrder != 'undefined'){
            if($sortOrder === 'descending') $sortOrder = 'desc';
            else $sortOrder = 'asc';
        }
        else{
            $sortProp = 'order_part_number';
            $sortOrder = 'asc';
        }

        if($request['showDeleted'] == 'true'){
            $orders = Orders::onlyTrashed()
            ->where('computation_sheet.cs_client_id', $id)
            ->leftJoin('computation_sheet', 'computation_sheet.client_order_id', 'orders.id')
            ->orderBy($sortProp, $sortOrder)
            ->paginate($request['take']);
        }
        else{
            $orders = Orders::leftJoin('computation_sheet', 'computation_sheet.client_order_id', 'orders.id')
            ->where('computation_sheet.cs_client_id', $id)
            ->orderBy($sortProp, $sortOrder)
            ->paginate($request['take']);
        }

        return ['id'=>$id, 'type' => 'Order', 'orders' => $orders];
    }
    public function getClientDM(Request $request, $id){

        $sortProp = $request['sortProp'];
        $sortOrder = $request['sortOrder'];

        if($sortProp != 'undefined' && $sortOrder != 'undefined'){
            if($sortOrder === 'descending') $sortOrder = 'desc';
            else $sortOrder = 'asc';
        }
        else{
            $sortProp = 'po_id';
            $sortOrder = 'asc';
        }

        if($request['showDeleted'] == 'true'){
            $dm = ClientPODeliveryMonitoring::onlyTrashed()
            ->leftJoin('client_po', 'client_po.id', 'client_po_delivery_monitoring.po_id')
            ->leftJoin('client_po_invoice', 'client_po_invoice.id', 'client_po_delivery_monitoring.invoice_id')
            ->leftJoin('client_po_receipt', 'client_po_receipt.id', 'client_po_delivery_monitoring.receipt_id')
            ->leftJoin('orders', 'orders.id', 'client_po_delivery_monitoring.order_id')
            ->where('client_po.client_id', $id)
            ->orderBy($sortProp, $sortOrder)
            ->paginate($request['take']);
        }
        else{
            $dm = ClientPODeliveryMonitoring::select('client_po_delivery_monitoring.*', 'client_po_invoice.si_number as si_number', 'client_po.client_po_number', 'client_po_receipt.dr_number')->leftJoin('client_po', 'client_po.id', 'client_po_delivery_monitoring.po_id')
            ->leftJoin('client_po_invoice', 'client_po_invoice.id', 'client_po_delivery_monitoring.invoice_id')
            ->leftJoin('client_po_receipt', 'client_po_receipt.id', 'client_po_delivery_monitoring.receipt_id')
            ->leftJoin('orders', 'orders.id', 'client_po_delivery_monitoring.order_id')
            ->where('client_po.client_id', $id)
            ->orderBy($sortProp, $sortOrder)
            ->paginate($request['take']);
        }

        return ['id'=>$id, 'type' => 'DM', 'dm' => $dm ];
    }
    public function getClientCS(Request $request, $id){

        $sortProp = $request['sortProp'];
        $sortOrder = $request['sortOrder'];

        if($sortProp != 'undefined' && $sortOrder != 'undefined'){
            if($sortOrder === 'descending') $sortOrder = 'desc';
            else $sortOrder = 'asc';
        }
        else{
            $sortProp = 'cs_control_number';
            $sortOrder = 'asc';
        }

        $cs_six_months_ago = ComputationSheet::where('cs_client_id', $id)->where('created_at', '<', Carbon::now()->addMonths(-6))->where('cs_is_valid', 1)->pluck('id');
        ComputationSheet::whereIn('id', $cs_six_months_ago)->update(['cs_is_valid' => 0]);

        if($request['showDeleted'] == 'true'){
            $cs = ComputationSheet::onlyTrashed()
            ->where('cs_client_id', $id)
            ->leftJoin('orders', 'orders.id', 'computation_sheet.client_order_id')
            ->orderBy($sortProp, $sortOrder)
            ->paginate($request['take']);
        }
        else {
            $cs = ComputationSheet::where('cs_client_id', $id)
            ->leftJoin('orders', 'orders.id', 'computation_sheet.client_order_id')
            ->orderBy($sortProp, $sortOrder)
            ->paginate($request['take']);
        }

        return ['id'=>$id, 'type' => 'CS', 'cs' => $cs];
    }
    public function getClientQuotation(Request $request, $id){

        $sortProp = $request['sortProp'];
        $sortOrder = $request['sortOrder'];

        $valid_quote = Quotation::where('created_at', '<', Carbon::now()->addMonths(-1))->where('quotation_is_valid', 1)->pluck('id');
        Quotation::whereIn('id', $valid_quote)->update(['quotation_is_valid' => 0, 'quotation_is_valid' => 0]);
        
        if($sortProp != 'undefined' && $sortOrder != 'undefined'){
            if($sortOrder === 'descending') $sortOrder = 'desc';
            else $sortOrder = 'asc';
        }
        else{
            $sortProp = 'order_item_description';
            $sortOrder = 'asc';
        }

        if($request['showDeleted'] == 'true'){
            $quotes = Quotation::onlyTrashed()
            ->where('client_id', $id)
            ->orderBy($sortProp, $sortOrder)
            ->paginate($request['take']);
        }  
        else {
            $quotes = Quotation::where('client_id', $id)
                ->orderBy($sortProp, $sortOrder)
                ->paginate($request['take']);
        }

        return ['id' => $id, 'type' => 'Quotation', 'quotes' => $quotes];
    }
    public function getClientSS(Request $request, $id){
        $sortProp = $request['sortProp'];
        $sortOrder = $request['sortOrder'];

        if($sortProp != 'undefined' && $sortOrder != 'undefined'){
            if($sortOrder === 'descending') $sortOrder = 'desc';
            else $sortOrder = 'asc';
        }
        else{
            $sortProp = 'ss_reference_number';
            $sortOrder = 'asc';
        }

        if($request['showDeleted'] == 'true'){
            $ss = DB::table('sales_sheet')->leftJoin('computation_sheet', 'computation_sheet.id', 'sales_sheet.cs_id')
            ->where('computation_sheet.cs_client_id', $id)
            ->where('sales_sheet.deleted_at', '!=', NULL)
            ->orderBy($sortProp, $sortOrder)
            ->paginate($request['take']);
        }  
        else {
            $ss = DB::table('sales_sheet')->leftJoin('computation_sheet', 'computation_sheet.id', 'sales_sheet.cs_id')
            ->where('computation_sheet.cs_client_id', $id)
            ->where('sales_sheet.deleted_at', NULL)
            ->orderBy($sortProp, $sortOrder)
            ->paginate($request['take']);
        }
        return ['id'=>$id, 'type' => 'SS', 'ss' => $ss];
    }
    public function getClientForecast(Request $request, $id){
        $sortProp = $request['sortProp'];
        $sortOrder = $request['sortOrder'];

        if($sortProp != 'undefined' && $sortOrder != 'undefined'){
            if($sortOrder === 'descending') $sortOrder = 'desc';
            else $sortOrder = 'asc';
        }
        else{
            $sortProp = 'forecast_number';
            $sortOrder = 'asc';
        }

        if($request['showDeleted'] == 'true'){
            $forecast = ClientForecast::onlyTrashed()->where('client_id', $id)
                ->orderBy($sortProp, $sortOrder)
                ->paginate($request['take']);
        }  
        else {
            $forecast = ClientForecast::where('client_id', $id)
                ->orderBy($sortProp, $sortOrder)
                ->paginate($request['take']);
        }

        
        return ['id'=>$id, 'type' => 'Forecast', 'forecast' => $forecast];
    }
}
