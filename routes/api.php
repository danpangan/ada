<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
}); 

Route::get('table-data-generator-rm', 'API\TableDataController@RMQueries');
Route::get('table-data-generator-sc', 'API\TableDataController@SCQueries');
Route::get('table-data-generator-supplier', 'API\TableDataController@SupplierQueries');

Route::apiResources(['user' => 'API\UserController']);
Route::apiResources(['department' => 'API\DepartmentController']);
Route::apiResources(['client' => 'API\CustomerController']);
Route::apiResources(['exchange-rate' => 'API\ExchangeRateController']);
Route::apiResources(['client-forecast' => 'API\ClientForecastController']);
Route::apiResources(['client-forecast-item' => 'API\ClientForecastItemsController']);
Route::apiResources(['supplier' => 'API\SupplierController']);
Route::apiResources(['material' => 'API\MaterialController']);
Route::apiResources(['sc-material' => 'API\SCMaterialController']);
Route::apiResources(['product' => 'API\ProductController']);
Route::apiResources(['process' => 'API\ProcessesController']);
Route::apiResources(['locations' => 'API\LocationsController']);
Route::apiResources(['order' => 'API\OrderController']);
Route::apiResources(['cs' => 'API\ComputationSheetController']);
Route::apiResources(['quotation' => 'API\QuotationsController']);
Route::apiResources(['purchase-order' => 'API\ClientPOController']);
Route::apiResources(['sales-sheet' => 'API\SalesSheetController']);
Route::apiResources(['invoice' => 'API\ClientPOInvoiceController']);
Route::apiResources(['delivery-monitoring' => 'API\ClientPODeliveryMonitoringController']);

Route::get('profile', 'API\UserController@profile');
Route::get('get-all-departments', 'API\DepartmentController@getAllDepartments')->name('get-all-departments-dropdown');
Route::get('get-provinces', 'API\CustomerController@getProvinces')->name('get-provinces');
Route::get('get-municipalities', 'API\CustomerController@getMunicipalities')->name('get-municipalities');
Route::post('reset-password/{employee_number}', 'API\UserController@resetPassword')->name('reset-password');
Route::get('admin-validation', 'API\CustomerController@adminValidation')->name('admin-validation');

// customer-sales-monitoring
Route::get('/customer-sales-monitoring-po/{client_id}', 'API\CustomerController@getClientPO')->name('get-provinces');
Route::get('/customer-sales-monitoring-orders/{client_id}', 'API\CustomerController@getClientOrders')->name('get-provinces');
Route::get('/customer-sales-monitoring-dm/{client_id}', 'API\CustomerController@getClientDM')->name('get-provinces');
Route::get('/customer-sales-monitoring-cs/{client_id}', 'API\CustomerController@getClientCS')->name('get-provinces');
Route::get('/customer-sales-monitoring-quotations/{client_id}', 'API\CustomerController@getClientQuotation')->name('get-provinces');
Route::get('/customer-sales-monitoring-ss/{client_id}', 'API\CustomerController@getClientSS')->name('get-provinces');
Route::get('/customer-sales-monitoring-forecast/{client_id}', 'API\CustomerController@getClientForecast')->name('get-provinces');

Route::get('department-users/{department_id}', 'API\UserController@departmentUsers')->name('department-users');

Route::get('get-clients', 'API\CustomerController@getClients')->name('get-clients');

Route::get('get-material-classifications', 'API\MaterialController@getMaterialClassifications')->name('get-material-classifications');
Route::get('get-material-classification/{material_type_id}', 'API\MaterialController@getMaterialClassification')->name('get-material-classification');
Route::get('get-suppliers', 'API\MaterialController@getSuppliers')->name('get-suppliers');

//material controller
Route::get('get-rm-type-codes', 'API\MaterialController@getRMTypeCodes')->name('get-rm-type-codes');
Route::get('get-material-suppliers/{material_id}', 'API\MaterialController@getMaterialSuppliers')->name('get-material-suppliers');
Route::get('get-material-tracking', 'API\MaterialController@getMaterialsTracking')->name('get-material-tracking');
Route::post('transact-material-supplier/{material_id}', 'API\MaterialController@transactMaterialSupplier')->name('transact-material-supplier');
Route::post('add-material-classification/{type_code_id}', 'API\MaterialController@addMaterialClassification')->name('add-material-classification');

//subcontract material controller
Route::get('get-sc-material-classifications', 'API\SCMaterialController@getMaterialClassifications')->name('get-sc-material-classifications');
Route::get('get-sc-material-classification/{sc_material_type_id}', 'API\SCMaterialController@getMaterialClassification')->name('get-sc-material-classification');
Route::get('get-sc-type-codes', 'API\SCMaterialController@getSCTypeCodes')->name('get-sc-type-codes');
Route::get('get-sc-material-suppliers/{sc_material_id}', 'API\SCMaterialController@getMaterialSuppliers')->name('get-sc-material-suppliers');
Route::get('get-sc-material-tracking', 'API\SCMaterialController@getMaterialsTracking')->name('get-sc-material-tracking');
Route::post('store-sc-material-supplier/{sc_material_id}', 'API\SCMaterialController@storeMaterialSupplier')->name('store-sc-material-supplier');
Route::post('add-sc-material-classification/{type_code_id}', 'API\SCMaterialController@addMaterialClassification')->name('add-sc-material-classification');

//fg controller
Route::get('get-fg-classifications', 'API\ProductController@getMaterialClassifications')->name('get-fg-classifications');
Route::get('get-fg-classification/{fg_type_id}', 'API\ProductController@getMaterialClassification')->name('get-fg-classification');
Route::get('get-fg-type-codes', 'API\ProductController@getProductTypeCodes')->name('get-fg-type-codes');
Route::post('add-fg-material-classification/{type_code_id}', 'API\ProductController@addMaterialClassification')->name('add-fg-material-classification');
Route::get('get-product-customers', 'API\ProductController@getProductCustomers')->name('get-product-customers');


// Maintenance Route
// Process
Route::post('/add-price-bracket', 'API\ProcessesController@addPriceBracket')->name('add-price-bracket');
Route::get('get-process-dropdowns', 'API\ProcessesController@getProcessDropDown')->name('get-process-dropdowns');
//delete price bracket
Route::delete('delete-price-bracket/{process_id}/source_table/{source_table}', 'API\ProcessesController@deletePriceBracket')->name('delete-price-bracket');
Route::get('add-price-bracket/{table_name}', 'API\ProcessesController@addPriceBracket')->name('add-price-bracket');
// Import excel
Route::post('/import', 'API\ProcessesController@importExcel')->name('import-excel');
// Location
Route::get('view-municipalities/{province_id}', 'API\LocationsController@show')->name('view-municipalities');
Route::delete('/delete-municipality/{province_id}', 'API\LocationsController@deleteMunicipality')->name('delete-municipality');

Route::post('update-province/{id}/{province_name}', 'API\LocationsController@updateProvince')->name('update-province');
// Route::post('add-province/{id}', 'API\LocationsController@addProvince')->name('add-province');

// Municipality
Route::get('get-municipality/{municipality_id}', 'API\LocationsController@getMunicipality')->name('get-municipality');
Route::post('add-municipality/{province_id}', 'API\LocationsController@addMunicipality')->name('add-municipality');
Route::put('update-municipality/{id}', 'API\LocationsController@updateMunicipality')->name('update-municipality');
Route::delete('delete-municipality/{id}', 'API\LocationsController@deleteMunicipality')->name('delete-municipality');

// Material Classifications
Route::get('material-classifications-list/{type_code_id}', 'API\MaterialController@showMaterialClassificationTable')->name('show-material-classification-table');
Route::post('update-material-classification/{material_classification_id}', 'API\MaterialController@updateClassification')->name('update-classification');
Route::delete('/delete-material-classification/{material_classification_id}', 'API\MaterialController@deleteClassification')->name('delete-classification');
// Type code
Route::get('type-code-list', 'API\MaterialController@showTypeCodeTable')->name('show-type-code-table');
Route::post('update-type-code/{type_code_id}', 'API\MaterialController@updateTypeCode')->name('update-type-code');
Route::delete('/delete-type-code/{type_code_id}', 'API\MaterialController@deleteTypeCode')->name('delete-type-code');
Route::post('add-type-code', 'API\MaterialController@addTypeCode')->name('add-type-code');

 //order controller
 Route::get('get-order-categories', 'API\OrderController@getOrderCategories')->name('get-order-categories');
 Route::get('get-order-type-codes', 'API\OrderController@getOrderTypeCodes')->name('get-order-type-codes');
 Route::get('get-order-classifications/{type_code_id}', 'API\OrderController@getOrderClassifications')->name('get-order-classifications');
 Route::get('get-order-primary-materials', 'API\OrderController@getOrderMaterials')->name('get-order-primary-materials');
 Route::get('get-order-sc-materials', 'API\OrderController@getOrderSCMaterials')->name('get-order-sc-materials');
 Route::get('get-processes', 'API\OrderController@getProcesses')->name('get-processes-dropdown');
 
 Route::get('get-orders', 'API\ComputationSheetController@getOrders')->name('get-orders');
 Route::get('get-cs-order/{order_id}', 'API\ComputationSheetController@getCSOrder')->name('get-cs-order');

 //evaluation controller
 Route::get('get-evaluators', 'API\EvaluationController@getEvaluators')->name('get-evaluators');

//  quotation
Route::get('get-orders/{client_id}', 'API\QuotationsController@getOrders')->name('get-quotation-orders');
Route::get('get-quotation-list/{client_id}', 'API\QuotationsController@selectControlNumberList')->name('get-quotation-list-with-selected');
Route::post('copy-quotation', 'API\QuotationsController@copyQuotation')->name('copy-quotation');
Route::put('/evaluate-quote', 'API\QuotationsController@evaluateQuote')->name('evaluate-quote');
Route::put('/order-selected/{order_id}', 'API\QuotationsController@orderSelected')->name('quote-order-selected');
Route::put('/update-evaluation-status/{quote_id}', 'API\QuotationsController@updateEvaluation')->name('evaluate-update');
// create-quote-copy
Route::post('/create-quote-copy/{quote_id}', 'API\QuotationsController@createCopy')->name('create-copy-quotation');

// po
Route::post('get-quotes', 'API\QuotationsController@copyQuotation')->name('po-get-quotes');
Route::post('/order-was-selected/{client_id}', 'API\ClientPOController@orderWasSelected')->name('po-details');
Route::get('/get-quotations/{client_id}', 'API\ClientPOController@orderWasSelected')->name('po-get-quotes');
//cs
Route::put('/evaluate-cs', 'API\ComputationSheetController@evaluateCS')->name('evaluate-cs');
Route::put('/reject-cs-evaluation', 'API\ComputationSheetController@rejectCSEvaluation')->name('reject-cs-evaluation');

Route::get('get-client-pic/{client_id}', 'API\QuotationsController@getClientPIC')->name('get-client-pic');
Route::get('get-client-pic/{client_id}', 'API\QuotationsController@getClientPIC')->name('get-client-pic');

Route::put('/evaluate-ss', 'API\SalesSheetController@evaluateSS')->name('evaluate-ss');
Route::put('/reject-ss-evaluation', 'API\SalesSheetController@rejectSSEvaluation')->name('reject-ss-evaluation');
