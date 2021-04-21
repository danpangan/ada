<?php

use App\User;
use Illuminate\Support\Facades\Hash;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('auth/login');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::get('/employees', 'HomeController@index')->name('employees');
Route::get('/view-employee/{employee_id}', 'HomeController@index')->name('view-employee');
Route::get('/add-employee', 'HomeController@index')->name('add-employee');
Route::get('/update-employee/{employee_id}', 'HomeController@index')->name('update-employee');
Route::get('/update-account/{employee_id}', 'HomeController@index')->name('update-account');
Route::get('/account-setting/{employee_id}', 'HomeController@index')->name('account-setting');
Route::get('/my-account/{employee_id}', 'HomeController@index')->name('my-account');
Route::get('/departments', 'HomeController@index')->name('departments');
Route::get('/get-departments', 'API\DepartmentController@index')->name('get-departments');
Route::get('/view-department/{department_id}', 'HomeController@index')->name('view-department');
Route::get('/add-department', 'HomeController@index')->name('add-department');
Route::get('/update-department/{department_id}', 'HomeController@index')->name('update-department');

Route::get('/customer-management', 'HomeController@index')->name('customer-management');
Route::get('/view-client/{client_id}', 'HomeController@index')->name('view-client');
Route::get('/add-client', 'HomeController@index')->name('add-client');
Route::get('/update-client/{client_id}', 'HomeController@index')->name('update-client');

Route::get('/raw-materials', 'HomeController@index')->name('raw-materials');
Route::get('/add-raw-material', 'HomeController@index')->name('add-raw-material');
Route::get('/update-raw-material/{raw_material_id}', 'HomeController@index')->name('update-raw-material');
Route::get('/view-raw-material/{raw_material_id}', 'HomeController@index')->name('view-raw-material');
Route::get('/raw-material-flow/{material_flow_id}/material-id/{supplier_material_id}', 'HomeController@index')->name('raw-material-flow');

Route::get('/subcontract-materials', 'HomeController@index')->name('subcontract-materials');
Route::get('/add-subcontract-material', 'HomeController@index')->name('add-subcontract-material');
Route::get('/update-subcontract-material/{sc_material_id}', 'HomeController@index')->name('update-subcontract-material');
Route::get('/view-subcontract-material/{sc_material_id}', 'HomeController@index')->name('view-subcontract-material');
Route::get('/subcontract-material-flow/{material_flow_id}/sc-material-id/{supplier_sc_material_id}', 'HomeController@index')->name('subcontract-material-flow');

Route::get('/finish-products', 'HomeController@index')->name('finish-products');
Route::get('/add-product', 'HomeController@index')->name('add-product');
Route::get('/update-product/{product_id}', 'HomeController@index')->name('update-product');
Route::get('/view-product/{product_id}', 'HomeController@index')->name('view-product');
Route::get('/product-flow/{product_flow_id}/product-id/{product_id}', 'HomeController@index')->name('product-flow');

Route::get('/supplier-management', 'HomeController@index')->name('supplier-management');
Route::get('/add-supplier', 'HomeController@index')->name('add-supplier');
Route::get('/update-supplier/{supplier_id}', 'HomeController@index')->name('update-supplier');
Route::get('/view-supplier/{supplier_id}', 'HomeController@index')->name('view-supplier');

Route::get('/order-management', 'HomeController@index')->name('order-management');
Route::get('/add-order', 'HomeController@index')->name('add-order');
Route::get('/update-order/{order_id}', 'HomeController@index')->name('update-order');
Route::get('/view-order/{order_id}', 'HomeController@index')->name('view-order');

Route::get('/computation-sheets', 'HomeController@index')->name('computation-sheets');
Route::get('/add-cs', 'HomeController@index')->name('add-cs');
Route::get('/update-cs/{cs_id}', 'HomeController@index')->name('update-cs');
Route::get('/view-cs/{cs_id}', 'HomeController@index')->name('view-cs');

Route::get('/quotations', 'HomeController@index')->name('quotations');
Route::get('/add-quotation', 'HomeController@index')->name('add-quotation');
Route::get('/update-quotation/{quotation_id}', 'HomeController@index')->name('update-quotation');
Route::get('/view-quotation/{quotation_id}', 'HomeController@index')->name('view-quotation');
Route::get('/copy-quotation/{quotation_id}', 'HomeController@index')->name('copy-quotation');

Route::get('/purchase-orders', 'HomeController@index')->name('purchase-orders');
Route::get('/add-po', 'HomeController@index')->name('add-po');
Route::get('/update-po/{po_id}', 'HomeController@index')->name('update-po');
Route::get('/view-po/{po_id}', 'HomeController@index')->name('view-po');

Route::get('/sales-sheets', 'HomeController@index')->name('sales-sheets');
Route::get('/add-ss', 'HomeController@index')->name('add-ss');
Route::get('/update-ss/{ss_id}', 'HomeController@index')->name('update-ss');
Route::get('/view-ss/{ss_id}', 'HomeController@index')->name('view-ss');

Route::get('/invoices', 'HomeController@index')->name('invoices');
Route::get('/add-invoice', 'HomeController@index')->name('add-invoice');
Route::get('/update-invoice/{invoice_id}', 'HomeController@index')->name('update-invoice');
Route::get('/view-invoice/{invoice_id}', 'HomeController@index')->name('view-invoice');

Route::get('/delivery-monitoring', 'HomeController@index')->name('delivery-monitoring');
Route::get('/delivery-monitoring-po-items/{po_number}', 'HomeController@index')->name('delivery-monitoring-po-items');
Route::get('/delivery-monitoring-po-item/{po_number_item_id}', 'HomeController@index')->name('delivery-monitoring-po-item');

Route::get('/forecast-management', 'HomeController@index')->name('forecast-management');
Route::get('/add-client-forecast', 'HomeController@index')->name('add-client-forecast');
Route::get('/update-client-forecast/{client_forecast_id}', 'HomeController@index')->name('update-client-forecast');
Route::get('/view-client-forecast/{client_forecast_id}', 'HomeController@index')->name('view-client-forecast');

Route::get('/forex-management', 'HomeController@index')->name('forex-management');

Route::get('/customer-sales-monitoring/{page_type}/client/{client_id}', 'HomeController@index')->name('customer-sales-monitoring');

Route::get('/reports-management', 'HomeController@index')->name('reports-management');

Route::get('/process-maintenance', 'HomeController@index')->name('process-maintenance');
Route::get('/view-process/{process_id}/process/{source_table}', 'HomeController@index')->name('view-process');
Route::get('/add-price-bracket/{process_id}/name/{name}/show-all-bundle/{show_all_bundle}', 'HomeController@index')->name('add-price-bracket');

Route::get('/locations', 'HomeController@index')->name('locations');
Route::get('/view-province/{province_id}/{province_name}', 'HomeController@index')->name('view-province');
Route::get('/update-province/{province_id}/{province_name}', 'HomeController@index')->name('update-province');
Route::get('/update-municipality/{municipality_id}', 'HomeController@index')->name('update-municipality');
Route::get('/view-municipalities/{province_id}', 'API\LocationsController@show')->name('view-municipalities');

Route::get('/update-classification/{classification_id}', 'HomeController@index')->name('update-classification');
Route::get('/material-classifications-maintenance', 'HomeController@index')->name('material-classifications-maintenance');
Route::get('/view-material-classification/{type_code_id}/type-code/{type_code}/{descriptions}', 'HomeController@index')->name('view-material-classification');

//PDF Controllers
Route::get('/export-pdf-employees', 'PDFControllerTable@exportPDFEmployees')->name('export-pdf-employees');
Route::get('/export-pdf-employee', 'PDFControllerTable@exportPDFEmployee')->name('export-pdf-employee');
Route::get('/export-pdf-departments', 'PDFControllerTable@exportPDFDepartments')->name('export-pdf-departments');
Route::get('/export-pdf-department', 'PDFControllerTable@exportPDFDepartment')->name('export-pdf-department');
Route::get('/export-pdf-clients', 'PDFControllerTable@exportPDFClients')->name('export-pdf-clients');
Route::get('/export-pdf-client', 'PDFControllerTable@exportPDFClient')->name('export-pdf-client');
Route::get('/export-pdf-exchange-rate-logs', 'PDFControllerTable@exportPDFExchangeRateLogs')->name('export-pdf-exchange-rate-logs');
Route::get('/export-pdf-client-forecasts', 'PDFControllerTable@exportPDFClientForecasts')->name('export-pdf-client-forecasts');
Route::get('/export-pdf-client-forecast-items', 'PDFControllerTable@exportPDFClientForecastItems')->name('export-pdf-client-forecast-items');
Route::get('/export-pdf-suppliers', 'PDFControllerTable@exportPDFSuppliers')->name('export-pdf-suppliers');
Route::get('/export-pdf-supplier', 'PDFControllerTable@exportPDFSupplier')->name('export-pdf-supplier');
Route::get('/export-pdf-raw-materials', 'PDFControllerTable@exportPDFRawMaterials')->name('export-pdf-raw-materials');
Route::get('/export-pdf-raw-material', 'PDFControllerTable@exportPDFRawMaterial')->name('export-pdf-raw-material');
Route::get('/export-pdf-raw-material-tracking', 'PDFControllerTable@exportPDFRawMaterialTracking')->name('export-pdf-raw-material-tracking');
Route::get('/export-pdf-raw-material-suppliers', 'PDFControllerTable@exportPDFRawMaterialSuppliers')->name('export-pdf-raw-material-suppliers');
Route::get('/export-pdf-sc-materials', 'PDFControllerTable@exportPDFSCMaterials')->name('export-pdf-sc-materials');
Route::get('/export-pdf-sc-material', 'PDFControllerTable@exportPDFSCMaterial')->name('export-pdf-sc-material');
Route::get('/export-pdf-sc-material-tracking', 'PDFControllerTable@exportPDFSCMaterialTracking')->name('export-pdf-sc-material-tracking');
Route::get('/export-pdf-sc-material-suppliers', 'PDFControllerTable@exportPDFSCMaterialSuppliers')->name('export-pdf-sc-material-suppliers');
Route::get('/export-pdf-products', 'PDFControllerTable@exportPDFProducts')->name('export-pdf-products');
Route::get('/export-pdf-product', 'PDFControllerTable@exportPDFProduct')->name('export-pdf-product');
Route::get('/export-pdf-processes', 'PDFControllerTable@exportPDFProcesses')->name('export-pdf-processes');
Route::get('/export-pdf-process', 'PDFControllerTable@exportPDFProcess')->name('export-pdf-process');
Route::get('/view-municipalities/{province_id}', 'API\LocationsController@show')->name('view-municipalities');
Route::get('/export-pdf-locations', 'PDFControllerTable@exportPDFLocations')->name('export-pdf-locations');
Route::get('/export-pdf-location', 'PDFControllerTable@exportPDFLocation')->name('export-pdf-location');
Route::get('/export-pdf-material-maintenanace-type', 'PDFControllerTable@exportPDFmaterialType')->name('export-pdf-material-type');
Route::get('/export-pdf-material-classifications', 'PDFControllerTable@exportPDFmaterialClassifications')->name('export-pdf-material-classifications');
Route::get('/export-pdf-orders', 'PDFControllerTable@exportPDFOrders')->name('export-pdf-orders');
Route::get('/export-pdf-order', 'PDFControllerTable@exportPDFOrder')->name('export-pdf-order');
Route::get('/export-pdf-computation-sheets', 'PDFControllerTable@exportPDFComputationSheets')->name('export-pdf-computation-sheets');
Route::get('/export-pdf-computation-sheet/{id}', 'PDFControllerTable@exportPDFComputationSheet')->name('export-pdf-computation-sheet');
Route::get('/export-pdf-quotations', 'PDFControllerTable@exportPDFQuotations')->name('export-pdf-quotations');
Route::get('/export-pdf-quotation', 'PDFControllerTable@exportPDFQuotation')->name('export-pdf-quotation');
Route::get('/export-pdf-cs', 'PDFControllerTable@exportPDFComputationSheet')->name('export-pdf-cs');


// customer sales monitoring
Route::get('/export-pdf-customer-sales-monitoring-po', 'PDFControllerTable@clientPO')->name('customer-sales-monitoring-po');
Route::get('/export-pdf-customer-sales-monitoring-dm', 'PDFControllerTable@clientDM')->name('customer-sales-monitoring-dm');
Route::get('/export-pdf-customer-sales-monitoring-orders', 'PDFControllerTable@clientOrders')->name('customer-sales-monitoring-orders');
Route::get('/export-pdf-customer-sales-monitoring-cs', 'PDFControllerTable@clientCS')->name('customer-sales-monitoring-cs');
Route::get('/export-pdf-customer-sales-monitoring-quotations', 'PDFControllerTable@clientQuotations')->name('customer-sales-monitoring-quotations');
Route::get('/export-pdf-customer-sales-monitoring-ss', 'PDFControllerTable@clientSS')->name('customer-sales-monitoring-ss');
Route::get('/export-pdf-customer-sales-monitoring-forecast', 'PDFControllerTable@clientForecast')->name('customer-sales-monitoring-forecast');
Route::get('/export-pdf-sales-sheets', 'PDFControllerTable@exportPDFSalesSheets')->name('export-pdf-sales-sheets');
Route::get('/export-pdf-sales-sheet', 'PDFControllerTable@exportPDFSalesSheet')->name('export-pdf-sales-sheets');

Route::get('/export-pdf-invoices', 'PDFControllerTable@exportPDFInvoices')->name('export-pdf-invoices');
Route::get('/export-pdf-invoice', 'PDFControllerTable@exportPDFInvoice')->name('export-pdf-invoice');

Route::get('/export-pdf-dm', 'PDFControllerTable@exportPDFDeliveryMonitoring')->name('export-pdf-dm');
