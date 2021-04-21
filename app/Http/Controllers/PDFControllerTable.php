<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PDF;
use DB;
use \stdClass;
use Carbon\Carbon;
use App\User;
use App\Department;
use App\Client;
use App\PermissionsUser;
use App\PermissionsDepartment;
use App\Municipalities;
use App\Provinces;
use App\ClientContacts;
use App\ClientDetails;
use App\ClientDetailContacts;
use App\ExchangeRate;
use App\ClientForecast;
use App\ClientForecastItems;
use App\Suppliers;
use App\Materials;
use App\TypeCode;
use App\MaterialClassifications;
use App\MaterialsTracking;
use App\SupplierMaterial;
use App\SCMaterials;
use App\SCMaterialsTracking;
use App\SupplierSCMaterial;
use App\Products;
use App\Process;
use App\Orders;
use App\OrderMaterialsPrimary;
use App\OrderMaterialsSecondary;
use App\OrderMaterialsSubcon;
use App\OrderProcess;
use App\OrderProcessSubcon;
use App\OrderPrints;
use App\ComputationSheet;
use App\ComputationSheetMaterialsPrimary;
use App\ComputationSheetMaterialsPrimarySupplier;
use App\ComputationSheetMaterialsSecondary;
use App\ComputationSheetMaterialsSecondarySupplier;
use App\ComputationSheetMaterialsSubcon;
use App\ComputationSheetMaterialsSubconSupplier;
use App\ComputationSheetPrinting;
use App\ComputationSheetPrintingSupplier;
use App\ComputationSheetProcesses;
use App\ComputationSheetProcessSubcon;
use App\ComputationSheetMisc;
use App\ComputationSheetMiscOthers;
use App\ComputationSheetEvaluation;
use App\Quotation;
use App\QuotationCS;
use App\QuotationEvaluation;
use App\QuotationInitialCharge;
use App\ClientPODeliveryMonitoring;
use App\ClientPO;


class PDFControllerTable extends Controller
{
    public function exportPDFEmployees(Request $request){
        $filename = 'Employees_'.date('Y:m:d H:i:s');

        $sortProp = $request['sortProp'];
        $sortOrder = $request['sortOrder'];
        if($sortProp != 'undefined' && $sortOrder != 'undefined'){
            if($sortOrder === 'descending') $sortOrder = 'desc';
            else $sortOrder = 'asc';
        }
        else{
            $sortProp = 'employee_number';
            $sortOrder = 'asc';
        }

        if($request['showDeleted'] == 'true'){
            $users =  User::select('users.*', 'departments.department_name as department_name')
                ->onlyTrashed()
                ->leftJoin('departments', 'departments.id', 'department_id')
                ->where(function($query) use ($request){
                    $query->where('username','like','%'.$request['keywords'].'%')
                        ->orWhere('first_name','like','%'.$request['keywords'].'%')
                        ->orWhere('last_name','like','%'.$request['keywords'].'%')
                        ->orWhere('address','like','%'.$request['keywords'].'%')
                        ->orWhere('contact','like','%'.$request['keywords'].'%')
                        ->orWhere('departments.department_name','like','%'.$request['keywords'].'%')
                        ->orWhere('email','like','%'.$request['keywords'].'%');
                })
                ->orderBy($sortProp, $sortOrder)
                // ->take(100)
                ->get();
        }
        else{
            $users = User::select('users.*', 'departments.department_name as department_name')
                ->leftJoin('departments', 'departments.id', 'department_id')
                ->where(function($query) use ($request){
                    $query->where('username','like','%'.$request['keywords'].'%')
                        ->orWhere('first_name','like','%'.$request['keywords'].'%')
                        ->orWhere('last_name','like','%'.$request['keywords'].'%')
                        ->orWhere('address','like','%'.$request['keywords'].'%')
                        ->orWhere('contact','like','%'.$request['keywords'].'%')
                        ->orWhere('departments.department_name','like','%'.$request['keywords'].'%')
                        ->orWhere('email','like','%'.$request['keywords'].'%');
                })
                ->orderBy($sortProp, $sortOrder)
                // ->take(100)
                ->get();
        }

        if($request['sortProp'] || $request['sortOrder']) $sort_class = 'block';
        else $sort_class = 'none';

        if(strlen($request['keywords']) > 0) $search_filter_class = 'block';
        else $search_filter_class = 'none';

        $table_contents = '';                
        foreach($users as $user){
            $table_contents .= '
                <tr>
                    <td>'.$user->employee_number.'</td>
                    <td>'.$user->username.'</td>
                    <td>'.$user->first_name.'</td>
                    <td>'.$user->last_name.'</td>
                    <td>'.$user->address.'</td>
                    <td>'.$user->email.'</td>
                    <td>'.$user->contact.'</td>
                    <td>'.$user->department_name.'</td>
                </tr>
            ';
        }

        $html = '
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <meta http-equiv="X-UA-Compatible" content="ie=edge">
                <title>Employees</title>
            </head>
            <style>
                p {
                    font-size: 12px;
                }
                table, th, td {
                    border: 1px solid black;
                    border-collapse: collapse;
                }
                th {
                    padding: 5px;
                    text-align: center;
                    font-size: 14px;
                }
                td {
                    padding: 5px;
                    font-size: 12px;
                }
                table {
                    width: 100%;
                }
                body {
                    font-family: Arial, Helvetica, sans-serif;
                }
                .logo {
                    width: 250px;
                }
                #footer {
                    position: fixed;
                    left: 0;
                    right: 0;
                    color: #aaa;
                    font-size: 0.9em;
                }
                #header {
                    top: 0;
                    border-bottom: 0.1pt solid #aaa;
                }
                #footer {
                    bottom: 0;
                    border-top: 0.1pt solid #aaa;
                }
                .page-number:before {
                    content: "Page " counter(page);
                }
            </style>
            <body>
                <img src="./images/logo.png" alt="Adamay Logo" class="logo">
                <h2 style="text-align: center">Employees</h2>
                <p style="display: '.$search_filter_class.'">Search Filter: "'.$request['keywords'].'"</p>
                <p style="display: '.$sort_class.'">Sort by: '.$sortProp.", ".$sortOrder.'</p>
                <table>
                    <tr>
                        <th>Employee No.</th>
                        <th>Username</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Address</th>
                        <th>Email</th>
                        <th>Contact</th>
                        <th>Department</th>
                    </tr>
                    '.$table_contents.'
                </table>
                <div id="footer">
                    <div class="page-number"></div>
                </div>
            </body>
            </html>
            <script type="text/php">
                if (isset($pdf)) {
                    $text = "page {PAGE_NUM} / {PAGE_COUNT}";
                    $size = 10;
                    $font = $fontMetrics->getFont("Arial");
                    $width = $fontMetrics->get_text_width($text, $font, $size) / 2;
                    $x = ($pdf->get_width() - $width) / 2;
                    $y = $pdf->get_height() - 35;
                    $pdf->page_text($x, $y, $text, $font, $size);
                }
            </script>
        ';

        $pdf = PDF::loadHTML($html);
        $pdf->setPaper('A4', 'landscape');
        return $pdf->stream('Adamay_'.$filename.'.pdf');
    }

    public function exportPDFEmployee(Request $request){
        $employee_number = $request['employee_number'];
        $filename = 'Employee_'.$employee_number.'_'.date('Y:m:d H:i:s');

        $employee = User::where('employee_number', $employee_number)->first();

        $birthdate = Carbon::parse($employee->birthdate)->format('F d, Y');

        $gender = '';
        if($employee->gender == 1) $gender = 'Male';
        else $gender = 'Female';

        $department = Department::where('id', $employee->department_id)->first();

        $permissions = PermissionsUser::where('employee_number', $employee_number)->orderBy('item_id')->get();

        $permissions_content = '<table>';

        $count = 0;
        foreach($permissions as $permission){
            $permission_category_count = PermissionsUser::where('employee_number', $employee_number)->where('category', $permission->category)->count();

            if($count >= $permission_category_count) $count = 1;
            else $count++;

            $permissions_content .= '<tr>';
            if($count == 1){
                $permissions_content .= '<td bgcolor="#D5D8DC">'.$permission->category.'</td>';
            }
            else{
                $permissions_content .= '<td>'.$permission->description.'</td>';
            }
            $permissions_content .= '</tr>';
        }

        $permissions_content .= '</table>';

        $table_contents = '
            <h3>Account Information</h3>
            <table>
                <tr>
                    <td width="25%">Employee Number</td>
                    <td>'.$employee->employee_number.'</td>
                </tr>
                <tr>
                    <td>Username</td>
                    <td>'.$employee->username.'</td>
                </tr>
            </table>

            <br>

            <h3>Personal Information</h3>
            <table>
                <tr>
                    <td width="25%">First Name</td>
                    <td>'.$employee->first_name.'</td>
                </tr>
                <tr>
                    <td>Middle Name</td>
                    <td>'.$employee->middle_name.'</td>
                </tr>
                <tr>
                    <td>Last Name</td>
                    <td>'.$employee->last_name.'</td>
                </tr>
                <tr>
                    <td>Address</td>
                    <td>'.$employee->address.'</td>
                </tr>
                <tr>
                    <td>Email</td>
                    <td>'.$employee->email.'</td>
                </tr>
                <tr>
                    <td>Contact</td>
                    <td>'.$employee->contact.'</td>
                </tr>
                <tr>
                    <td>Birthday</td>
                    <td>'.$birthdate.'</td>
                </tr>
                <tr>
                    <td>Gender</td>
                    <td>'.$gender.'</td>
                </tr>
                <tr>
                    <td>Department</td>
                    <td>'.$department->department_name.'</td>
                </tr>
            </table>
        ';                

        $html = '
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <meta http-equiv="X-UA-Compatible" content="ie=edge">
                <title>Employee</title>
            </head>
            <style>
                p {
                    font-size: 12px;
                }
                table, th, td {
                    border: 1px solid black;
                    border-collapse: collapse;
                }
                th {
                    padding: 5px;
                    text-align: center;
                    font-size: 14px;
                }
                td {
                    padding: 5px;
                    font-size: 12px;
                }
                table {
                    width: 100%;
                }
                body {
                    font-family: Arial, Helvetica, sans-serif;
                }
                .logo {
                    width: 250px;
                }
                #footer {
                    position: fixed;
                    left: 0;
                    right: 0;
                    color: #aaa;
                    font-size: 0.9em;
                }
                #header {
                    top: 0;
                    border-bottom: 0.1pt solid #aaa;
                }
                #footer {
                    bottom: 0;
                    border-top: 0.1pt solid #aaa;
                }
                .page-number:before {
                    content: "Page " counter(page);
                }
            </style>
            <body>
                <img src="./images/logo.png" alt="Adamay Logo" class="logo">
                <h2 style="text-align: center">Employee Information</h2>
                '.$table_contents.'
                <div id="footer">
                    <div class="page-number"></div>
                </div>
            </body>
            </html>
        ';

        $pdf = PDF::loadHTML($html);
        $pdf->setPaper('A4', 'portrait');
        return $pdf->stream('Adamay_'.$filename.'.pdf');
    }

    public function exportPDFDepartments(Request $request){
        $filename = 'Departments_'.date('Y:m:d H:i:s');

        $sortProp = $request['sortProp'];
        $sortOrder = $request['sortOrder'];
        if($sortProp != 'undefined' && $sortOrder != 'undefined'){
            if($sortOrder === 'descending') $sortOrder = 'desc';
            else $sortOrder = 'asc';
        }
        else{
            $sortProp = 'department_name';
            $sortOrder = 'asc';
        }

        if($request['showDeleted'] == 'true'){
            $departments =  Department::onlyTrashed()
                ->where(function($query) use ($request){
                    $query->where('department_name','like','%'.$request['keywords'].'%')
                        ->orWhere('department_contact','like','%'.$request['keywords'].'%');
                })
                ->orderBy($sortProp, $sortOrder)
                // ->take(100)
                ->get();
            $search_filter_class = 'block';
        }
        else{
            $departments = Department::where(function($query) use ($request){
                $query->where('department_name','like','%'.$request['keywords'].'%')
                    ->orWhere('department_contact','like','%'.$request['keywords'].'%');
            })
                ->orderBy($sortProp, $sortOrder)
                // ->take(100)
                ->get();
            $search_filter_class = 'none';
        }

        if($request['sortProp'] || $request['sortOrder']) $sort_class = 'block';
        else $sort_class = 'none';

        if(strlen($request['keywords']) > 0) $search_filter_class = 'block';
        else $search_filter_class = 'none';

        $table_contents = '';                
        foreach($departments as $department){
            $table_contents .= '
                <tr>
                    <td>'.$department->department_name.'</td>
                    <td>'.$department->department_contact.'</td>
                </tr>
            ';
        }

        $html = '
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <meta http-equiv="X-UA-Compatible" content="ie=edge">
                <title>Departments</title>
            </head>
            <style>
                p {
                    font-size: 12px;
                }
                table, th, td {
                    border: 1px solid black;
                    border-collapse: collapse;
                }
                th {
                    padding: 5px;
                    text-align: center;
                    font-size: 14px;
                }
                td {
                    padding: 5px;
                    font-size: 12px;
                }
                table {
                    width: 100%;
                }
                body {
                    font-family: Arial, Helvetica, sans-serif;
                }
                .logo {
                    width: 250px;
                }
                #footer {
                    position: fixed;
                    left: 0;
                    right: 0;
                    color: #aaa;
                    font-size: 0.9em;
                }
                #header {
                    top: 0;
                    border-bottom: 0.1pt solid #aaa;
                }
                #footer {
                    bottom: 0;
                    border-top: 0.1pt solid #aaa;
                }
                .page-number:before {
                    content: "Page " counter(page);
                }
            </style>
            <body>
                <img src="./images/logo.png" alt="Adamay Logo" class="logo">
                <h2 style="text-align: center">Departments</h2>
                <p style="display: '.$search_filter_class.'">Search Filter: "'.$request['keywords'].'"</p>
                <p style="display: '.$sort_class.'">Sort by: '.$sortProp.", ".$sortOrder.'</p>
                <table>
                    <tr>
                        <th>Department Name</th>
                        <th>Department Contact</th>
                    </tr>
                    '.$table_contents.'
                </table>
                <div id="footer">
                    <div class="page-number"></div>
                </div>
            </body>
            </html>
            <script type="text/php">
                if (isset($pdf)) {
                    $text = "page {PAGE_NUM} / {PAGE_COUNT}";
                    $size = 10;
                    $font = $fontMetrics->getFont("Arial");
                    $width = $fontMetrics->get_text_width($text, $font, $size) / 2;
                    $x = ($pdf->get_width() - $width) / 2;
                    $y = $pdf->get_height() - 35;
                    $pdf->page_text($x, $y, $text, $font, $size);
                }
            </script>
        ';

        $pdf = PDF::loadHTML($html);
        $pdf->setPaper('A4', 'landscape');
        return $pdf->stream('Adamay_'.$filename.'.pdf');
    }

    public function exportPDFDepartment(Request $request){
        $department_id = $request['department_id'];
        $department = Department::where('id', $department_id)->first();
        $filename = 'Department_'.$department->department_name.'_'.date('Y:m:d H:i:s');

        $employees = User::where('department_id', $department_id)->get();

        $department_employees = '<table>';
        $department_employees .= '
            <tr bgcolor="#D5D8DC">
                <th width="20%">Employee Number</th>
                <th width="20%">Username</th>
                <th width="20%">Name</th>
                <th width="20%">Email</th>
                <th width="20%">Contact</th>
            </tr>
        ';

        foreach($employees as $employee){
            $department_employees .= '<tr>';
            $department_employees .= '<td>'.$employee->employee_number.'</td>';
            $department_employees .= '<td>'.$employee->username.'</td>';
            $department_employees .= '<td>'.$employee->first_name.' '.$employee->last_name.'</td>';
            $department_employees .= '<td>'.$employee->email.'</td>';
            $department_employees .= '<td>'.$employee->contact.'</td>';
            $department_employees .= '</tr>';
        }

        $department_employees .= '</table>';

        $permissions = PermissionsDepartment::where('department_id', $department_id)->orderBy('item_id')->get();

        $table_contents = '
            <h3>Department Contact</h3>
            <table>
                <tr>
                    <td width="25%">Department Name</td>
                    <td>'.$department->department_name.'</td>
                </tr>
                <tr>
                    <td>Department Contact</td>
                    <td>'.$department->department_contact.'</td>
                </tr>
            </table>

            <br>
            
            <h3>Department Employees</h3>
            '.$department_employees.'
        ';                

        $html = '
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <meta http-equiv="X-UA-Compatible" content="ie=edge">
                <title>Department</title>
            </head>
            <style>
                p {
                    font-size: 12px;
                }
                table, th, td {
                    border: 1px solid black;
                    border-collapse: collapse;
                }
                th {
                    padding: 5px;
                    text-align: center;
                    font-size: 12px;
                }
                td {
                    padding: 5px;
                    font-size: 12px;
                }
                table {
                    width: 100%;
                }
                body {
                    font-family: Arial, Helvetica, sans-serif;
                }
                .logo {
                    width: 250px;
                }
                #footer {
                    position: fixed;
                    left: 0;
                    right: 0;
                    color: #aaa;
                    font-size: 0.9em;
                }
                #header {
                    top: 0;
                    border-bottom: 0.1pt solid #aaa;
                }
                #footer {
                    bottom: 0;
                    border-top: 0.1pt solid #aaa;
                }
                .page-number:before {
                    content: "Page " counter(page);
                }
            </style>
            <body>
                <img src="./images/logo.png" alt="Adamay Logo" class="logo">
                <h2 style="text-align: center">Department Information</h2>
                '.$table_contents.'
                <div id="footer">
                    <div class="page-number"></div>
                </div>
            </body>
            </html>
        ';

        $pdf = PDF::loadHTML($html);
        $pdf->setPaper('A4', 'portrait');
        return $pdf->stream('Adamay_'.$filename.'.pdf');
    }

    public function exportPDFClients(Request $request){
        $filename = 'Customers_'.date('Y:m:d H:i:s');

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
            $clients =  Client::onlyTrashed()
                ->where(function($query) use ($request){
                    $query->where('client_name','like','%'.$request['keywords'].'%')
                        ->orWhere('client_address','like','%'.$request['keywords'].'%')
                        ->orWhere('client_contact','like','%'.$request['keywords'].'%')
                        ->orWhere('client_pay_terms','like','%'.$request['keywords'].'%');
                })
                ->orderBy($sortProp, $sortOrder)
                // ->take(100)
                ->get();
            $search_filter_class = 'block';
        }
        else{
            $clients = Client::where(function($query) use ($request){
                $query->where('client_name','like','%'.$request['keywords'].'%')
                ->orWhere('client_address','like','%'.$request['keywords'].'%')
                ->orWhere('client_contact','like','%'.$request['keywords'].'%')
                ->orWhere('client_pay_terms','like','%'.$request['keywords'].'%');
            })
                ->orderBy($sortProp, $sortOrder)
                // ->take(100)
                ->get();
            $search_filter_class = 'none';
        }

        if($request['sortProp'] || $request['sortOrder']) $sort_class = 'block';
        else $sort_class = 'none';

        if(strlen($request['keywords']) > 0) $search_filter_class = 'block';
        else $search_filter_class = 'none';

        $table_contents = '';                
        foreach($clients as $client){
            if($client->is_temporary) $status = 'Temporary';
            else $status = 'Approved';
            $table_contents .= '
                <tr>
                    <td>'.$client->client_name.'</td>
                    <td>'.$client->client_address.'</td>
                    <td>'.$client->client_contact.'</td>
                    <td>'.$client->client_pay_terms.'</td>
                    <td>'.$status.'</td>
                </tr>
            ';
        }

        $html = '
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <meta http-equiv="X-UA-Compatible" content="ie=edge">
                <title>Customers</title>
            </head>
            <style>
                p {
                    font-size: 12px;
                }
                table, th, td {
                    border: 1px solid black;
                    border-collapse: collapse;
                }
                th {
                    padding: 5px;
                    text-align: center;
                    font-size: 14px;
                }
                td {
                    padding: 5px;
                    font-size: 12px;
                }
                table {
                    width: 100%;
                }
                body {
                    font-family: Arial, Helvetica, sans-serif;
                }
                .logo {
                    width: 250px;
                }
                #footer {
                    position: fixed;
                    left: 0;
                    right: 0;
                    color: #aaa;
                    font-size: 0.9em;
                }
                #header {
                    top: 0;
                    border-bottom: 0.1pt solid #aaa;
                }
                #footer {
                    bottom: 0;
                    border-top: 0.1pt solid #aaa;
                }
                .page-number:before {
                    content: "Page " counter(page);
                }
            </style>
            <body>
                <img src="./images/logo.png" alt="Adamay Logo" class="logo">
                <h2 style="text-align: center">Customers</h2>
                <p style="display: '.$search_filter_class.'">Search Filter: "'.$request['keywords'].'"</p>
                <p style="display: '.$sort_class.'">Sort by: '.$sortProp.", ".$sortOrder.'</p>
                <table>
                    <tr>
                        <th>Customer Name</th>
                        <th>Address</th>
                        <th>Contact</th>
                        <th>Payment Terms</th>
                        <th>Status</th>
                    </tr>
                    '.$table_contents.'
                </table>
                <div id="footer">
                    <div class="page-number"></div>
                </div>
            </body>
            </html>
            <script type="text/php">
                if (isset($pdf)) {
                    $text = "page {PAGE_NUM} / {PAGE_COUNT}";
                    $size = 10;
                    $font = $fontMetrics->getFont("Arial");
                    $width = $fontMetrics->get_text_width($text, $font, $size) / 2;
                    $x = ($pdf->get_width() - $width) / 2;
                    $y = $pdf->get_height() - 35;
                    $pdf->page_text($x, $y, $text, $font, $size);
                }
            </script>
        ';

        $pdf = PDF::loadHTML($html);
        $pdf->setPaper('A4', 'landscape');
        return $pdf->stream('Adamay_'.$filename.'.pdf');
    }

    public function exportPDFClient(Request $request){
        $client_id = $request['client_id'];
        $client = Client::where('id', $client_id)->first();
        $filename = 'Client_'.$client->client_name.'_'.date('Y:m:d H:i:s');

        $client_municipality = Municipalities::where('id', $client->client_municipality_id)->first();
        $client_province = Provinces::where('id', $client->client_province_id)->first();

        $client_pics = ClientContacts::where('client_id', $client_id)->get();

        $client_pic_tables = '';

        foreach($client_pics as $client_pic){
            $client_pic_tables .= '
                <table>
                    <tr>
                        <td width="25%">Person-In-Charge</td>
                        <td>'.$client_pic->client_pic.'</td>
                    </tr>
                    <tr>
                        <td width="25%">Contact</td>
                        <td>'.$client_pic->client_pic_contact.'</td>
                    </tr>
                    <tr>
                        <td width="25%">Email</td>
                        <td>'.$client_pic->client_pic_email.'</td>
                    </tr>
                    <tr>
                        <td width="25%">Department</td>
                        <td>'.$client_pic->client_pic_department.'</td>
                    </tr>
                </table>
                <br>
            ';
        }

        $billing = ClientDetails::where('client_id', $client_id)->where('client_detail_status', '!=', 3)->first();
    
        $billing_municipality = Municipalities::where('id', $billing->client_municipality_id)->first();
        $billing_province = Provinces::where('id', $billing->client_province_id)->first();

        $billing_pics = ClientDetailContacts::where('client_detail_id', $billing->id)->get();

        $billing_pic_tables = '';

        foreach($billing_pics as $billing_pic){
            $billing_pic_tables .= '
                <table>
                    <tr>
                        <td width="25%">Person-In-Charge</td>
                        <td>'.$billing_pic->client_pic.'</td>
                    </tr>
                    <tr>
                        <td width="25%">Contact</td>
                        <td>'.$billing_pic->client_pic_contact.'</td>
                    </tr>
                    <tr>
                        <td width="25%">Email</td>
                        <td>'.$billing_pic->client_pic_email.'</td>
                    </tr>
                    <tr>
                        <td width="25%">Department</td>
                        <td>'.$billing_pic->client_pic_department.'</td>
                    </tr>
                </table>
                <br>
            ';
        }

        $deliveries = ClientDetails::where('client_id', $client_id)->whereNotIn('client_detail_status', [2, 4])->get();

        $delivery_informations = '';

        foreach($deliveries as $delivery){
            $delivery_municipality = Municipalities::where('id', $delivery->client_municipality_id)->first();
            $delivery_province = Provinces::where('id', $delivery->client_province_id)->first();

            $delivery_pics = ClientDetailContacts::where('client_detail_id', $delivery->id)->get();

            $delivery_pic_table = '';

            foreach($delivery_pics as $delivery_pic){
                $delivery_pic_table .= '
                    <table>
                        <tr>
                            <td width="25%">Person-In-Charge</td>
                            <td>'.$delivery_pic->client_pic.'</td>
                        </tr>
                        <tr>
                            <td width="25%">Contact</td>
                            <td>'.$delivery_pic->client_pic_contact.'</td>
                        </tr>
                        <tr>
                            <td width="25%">Email</td>
                            <td>'.$delivery_pic->client_pic_email.'</td>
                        </tr>
                        <tr>
                            <td width="25%">Department</td>
                            <td>'.$delivery_pic->client_pic_department.'</td>
                        </tr>
                    </table>
                    <br>
                ';
            }

            $delivery_informations .= '
                <table>
                    <tr>
                        <td width="25%">Customer Name</td>
                        <td>'.$delivery->client_name.'</td>
                    </tr>
                    <tr>
                        <td width="25%">Address</td>
                        <td>'.$delivery->client_address.', '.$delivery_municipality->municipality_name.', '.$delivery_province->province_name.'</td>
                    </tr>
                </table>
                <br>
                '.$delivery_pic_table.'
                <br>
            ';
        }

        $table_contents = '
            <h3>Customer Information</h3>
            <table>
                <tr>
                    <td width="25%">Customer Name</td>
                    <td>'.$client->client_name.'</td>
                </tr>
                <tr>
                    <td width="25%">Address</td>
                    <td>'.$client->client_address.', '.$client_municipality->municipality_name.', '.$client_province->province_name.'</td>
                </tr>
                <tr>
                    <td width="25%">Contact</td>
                    <td>'.$client->client_contact.'</td>
                </tr>
            </table>
            <br>
            '.$client_pic_tables.'

            <h3>Billing Information</h3>
            <table>
                <tr>
                    <td width="25%">Customer Name</td>
                    <td>'.$billing->client_name.'</td>
                </tr>
                <tr>
                    <td width="25%">Address</td>
                    <td>'.$billing->client_address.', '.$billing_municipality->municipality_name.', '.$billing_province->province_name.'</td>
                </tr>
            </table>
            <br>
            '.$billing_pic_tables.'

            <h3>Delivery Information</h3>
            '.$delivery_informations.'
            <br>
        ';                

        $html = '
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <meta http-equiv="X-UA-Compatible" content="ie=edge">
                <title>Customer</title>
            </head>
            <style>
                p {
                    font-size: 12px;
                }
                table, th, td {
                    border: 1px solid black;
                    border-collapse: collapse;
                }
                th {
                    padding: 5px;
                    text-align: center;
                    font-size: 14px;
                }
                td {
                    padding: 5px;
                    font-size: 12px;
                }
                table {
                    width: 100%;
                }
                body {
                    font-family: Arial, Helvetica, sans-serif;
                }
                .logo {
                    width: 250px;
                }
                #footer {
                    position: fixed;
                    left: 0;
                    right: 0;
                    color: #aaa;
                    font-size: 0.9em;
                }
                #header {
                    top: 0;
                    border-bottom: 0.1pt solid #aaa;
                }
                #footer {
                    bottom: 0;
                    border-top: 0.1pt solid #aaa;
                }
                .page-number:before {
                    content: "Page " counter(page);
                }
            </style>
            <body>
                <img src="./images/logo.png" alt="Adamay Logo" class="logo">
                <h2 style="text-align: center">Customer Information</h2>
                '.$table_contents.'
                <div id="footer">
                    <div class="page-number"></div>
                </div>
            </body>
            </html>
        ';

        $pdf = PDF::loadHTML($html);
        $pdf->setPaper('A4', 'portrait');
        return $pdf->stream('Adamay_'.$filename.'.pdf');
    }

    public function exportPDFExchangeRateLogs(Request $request){
        $filename = 'ExchangeRateHistoryLogs_'.date('Y:m:d H:i:s');

        if($request['conversion_id']) $logs = ExchangeRate::where('conversion_id', $request['conversion_id'])->get();
        else $logs = ExchangeRate::get();

        $filter = '';
        $filter_class = 'block';
        if($request['conversion_id'] == 1) $filter = 'PHP to USD';
        if($request['conversion_id'] == 2) $filter = 'USD to PHP';
        if($request['conversion_id'] == 3) $filter = 'JPY to USD';
        if($request['conversion_id'] == 4) $filter = 'JPY to PHP';
        if(!$request['conversion_id']){
            $filter = '';
            $filter_class = 'none';
        }

        $history_logs = '';

        foreach($logs as $log){
            $exchange_currency = '';
            $conversion_rate = '';

            if($log->conversion_id == 1){
                $exchange_currency = 'PHP to USD';
                $conversion_rate = $log->rate.' USD';
            }
            if($log->conversion_id == 2){
                $exchange_currency = 'USD to PHP';
                $conversion_rate = $log->rate.' PHP';
            }
            if($log->conversion_id == 3){
                $exchange_currency = 'JPY to USD';
                $conversion_rate = $log->rate.' USD';
            }
            if($log->conversion_id == 4){
                $exchange_currency = 'JPY to PHP';
                $conversion_rate = $log->rate.' PHP';
            }

            $employee_name = '';
            $employee = User::where('id', $log->employee_id)->first();
            $employee_name = $employee->first_name.' '.$employee->last_name;

            $update_date = Carbon::parse($log->created_at)->format('m/d/Y H:i');

            $history_logs .= '
                <tr>
                    <td>'.$exchange_currency.'</td>
                    <td style="text-align: right">'.$conversion_rate.'</td>
                    <td>'.$employee_name.'</td>
                    <td>'.$update_date.'</td>
                </tr>
            ';
        }

        $table_contents = '';

        $table_contents .= '
            <h3>Update History</h3>
            <p style="display: '.$filter_class.'">Filter: '.$filter.'</p>
            <table>
                <tr>
                    <th>Exchange Currency</th>
                    <th>Conversion Rate</th>
                    <th>Employee Name</th>
                    <th>Update Date</th>
                </tr>
                '.$history_logs.'
            </table>
            <br>
        ';

        $html = '
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <meta http-equiv="X-UA-Compatible" content="ie=edge">
                <title>Exchange Rate Logs</title>
            </head>
            <style>
                p {
                    font-size: 12px;
                }
                table, th, td {
                    border: 1px solid black;
                    border-collapse: collapse;
                }
                th {
                    padding: 5px;
                    text-align: center;
                    font-size: 14px;
                }
                td {
                    padding: 5px;
                    font-size: 12px;
                }
                table {
                    width: 100%;
                }
                body {
                    font-family: Arial, Helvetica, sans-serif;
                }
                .logo {
                    width: 250px;
                }
                #footer {
                    position: fixed;
                    left: 0;
                    right: 0;
                    color: #aaa;
                    font-size: 0.9em;
                }
                #header {
                    top: 0;
                    border-bottom: 0.1pt solid #aaa;
                }
                #footer {
                    bottom: 0;
                    border-top: 0.1pt solid #aaa;
                }
                .page-number:before {
                    content: "Page " counter(page);
                }
            </style>
            <body>
                <img src="./images/logo.png" alt="Adamay Logo" class="logo">
                <h2 style="text-align: center">Exchange Rates</h2>
                '.$table_contents.'
                <div id="footer">
                    <div class="page-number"></div>
                </div>
            </body>
            </html>
        ';

        $pdf = PDF::loadHTML($html);
        $pdf->setPaper('A4', 'portrait');
        return $pdf->stream('Adamay_'.$filename.'.pdf');
    }

    public function exportPDFClientForecasts(Request $request){
        $filename = 'CustomerForecasts_'.date('Y:m:d H:i:s');
        $client_id = $request['clientId'];

        $client = Client::where('id', $client_id)->first();

        $sortProp = $request['sortProp'];
        $sortOrder = $request['sortOrder'];
        if($sortProp != 'undefined' && $sortOrder != 'undefined'){
            if($sortOrder === 'descending') $sortOrder = 'desc';
            else $sortOrder = 'asc';
        }
        else{
            $sortProp = 'id';
            $sortOrder = 'desc';
        }

        if($request['showDeleted'] == 'true'){
            $forecasts =  ClientForecast::select('id', 'forecast_number')
                ->onlyTrashed()
                ->where('client_id', $client_id)
                ->where(function($query) use ($request){
                    $query->where('forecast_number','like','%'.$request['keywords'].'%');
                })
                ->orderBy($sortProp, $sortOrder)
                ->get();
        }
        else{
            $forecasts = ClientForecast::select('id', 'forecast_number')
                ->where('client_id', $client_id)
                ->where(function($query) use ($request){
                    $query->where('forecast_number','like','%'.$request['keywords'].'%');
                })
                ->orderBy($sortProp, $sortOrder)
                ->get();
        }

        if($request['sortProp'] || $request['sortOrder']) $sort_class = 'block';
        else $sort_class = 'none';

        if(strlen($request['keywords']) > 0) $search_filter_class = 'block';
        else $search_filter_class = 'none';

        $table_contents = '';                
        foreach($forecasts as $forecast){
            $table_contents .= '
                <tr>
                    <td>'.$forecast->forecast_number.'</td>
                </tr>
            ';
        }

        $html = '
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <meta http-equiv="X-UA-Compatible" content="ie=edge">
                <title>Customer Forecasts</title>
            </head>
            <style>
                p {
                    font-size: 12px;
                }
                table, th, td {
                    border: 1px solid black;
                    border-collapse: collapse;
                }
                th {
                    padding: 5px;
                    text-align: center;
                    font-size: 14px;
                }
                td {
                    padding: 5px;
                    font-size: 12px;
                }
                table {
                    width: 100%;
                }
                body {
                    font-family: Arial, Helvetica, sans-serif;
                }
                .logo {
                    width: 250px;
                }
                #footer {
                    position: fixed;
                    left: 0;
                    right: 0;
                    color: #aaa;
                    font-size: 0.9em;
                }
                #header {
                    top: 0;
                    border-bottom: 0.1pt solid #aaa;
                }
                #footer {
                    bottom: 0;
                    border-top: 0.1pt solid #aaa;
                }
                .page-number:before {
                    content: "Page " counter(page);
                }
            </style>
            <body>
                <img src="./images/logo.png" alt="Adamay Logo" class="logo">
                <h2 style="text-align: center">Customer Forecasts</h2>
                <table>
                    <tr>
                        <td style="width: 30%"><strong>Customer Name</strong></td>
                        <td>'.$client->client_name.'</td>
                    </tr>
                    <tr>
                        <td style="width: 30%"><strong>Customer Number</strong></td>
                        <td>'.$client->client_number.'</td>
                    </tr>
                </table>
                <br>
                <p style="display: '.$search_filter_class.'">Search Filter: "'.$request['keywords'].'"</p>
                <p style="display: '.$sort_class.'">Sort by: '.$sortProp.", ".$sortOrder.'</p>
                <table>
                    <tr>
                        <th>Forecast Number</th>
                    </tr>
                    '.$table_contents.'
                </table>
                <div id="footer">
                    <div class="page-number"></div>
                </div>
            </body>
            </html>
            <script type="text/php">
                if (isset($pdf)) {
                    $text = "page {PAGE_NUM} / {PAGE_COUNT}";
                    $size = 10;
                    $font = $fontMetrics->getFont("Arial");
                    $width = $fontMetrics->get_text_width($text, $font, $size) / 2;
                    $x = ($pdf->get_width() - $width) / 2;
                    $y = $pdf->get_height() - 35;
                    $pdf->page_text($x, $y, $text, $font, $size);
                }
            </script>
        ';

        $pdf = PDF::loadHTML($html);
        $pdf->setPaper('A4', 'portrait');
        return $pdf->stream('Adamay_'.$filename.'.pdf');
    }

    public function exportPDFClientForecastItems(Request $request){
        $filename = 'CustomerForecasts_'.date('Y:m:d H:i:s');
        $client_id = $request['clientId'];
        $forecast_id = $request['clientForecastId'];

        $client = Client::where('id', $client_id)->first();
        $forecast = ClientForecast::where('id', $forecast_id)->first();

        $sortProp = $request['sortProp'];
        $sortOrder = $request['sortOrder'];
        if($sortProp != 'undefined' && $sortOrder != 'undefined'){
            if($sortOrder === 'descending') $sortOrder = 'desc';
            else $sortOrder = 'asc';
        }
        else{
            $sortProp = 'id';
            $sortOrder = 'desc';
        }

        if($request['showDeleted'] == 'true'){
            $forecast_items =  ClientForecastItems::onlyTrashed()
                ->where('forecast_id', $forecast_id)
                ->where(function($query) use ($request){
                    $query->where('order_id','like','%'.$request['keywords'].'%');
                })
                ->orderBy($sortProp, $sortOrder)
                ->get();
        }
        else{
            $forecast_items = ClientForecastItems::where('forecast_id', $forecast_id)
                ->where(function($query) use ($request){
                    $query->where('order_id','like','%'.$request['keywords'].'%');
                })
                ->orderBy($sortProp, $sortOrder)
                ->get();
        }

        if($request['sortProp'] || $request['sortOrder']) $sort_class = 'block';
        else $sort_class = 'none';

        if(strlen($request['keywords']) > 0) $search_filter_class = 'block';
        else $search_filter_class = 'none';

        $table_contents = '';                
        foreach($forecast_items as $forecast_item){
            $unit = '';
            if($forecast_item->forecast_quantity_unit == 1) $unit = 'pcs';
            else if($forecast_item->forecast_quantity_unit == 2) $unit = 'boxes';
            else if($forecast_item->forecast_quantity_unit == 3) $unit = 'rolls';
            else  $unit = 'sheets';

            $month = '';
            if($forecast_item->forecast_month < 10) $month = '0'.$forecast_item->forecast_month;
            else $month = $forecast_item;

            $table_contents .= '
                <tr>
                    <td>'.$forecast_item->order_id.'</td>
                    <td>'.$forecast_item->forecast_quantity.' '.$unit.'</td>
                    <td>'.$month.'/'.$forecast_item->forecast_year.'</td>
                </tr>
            ';
        }

        $html = '
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <meta http-equiv="X-UA-Compatible" content="ie=edge">
                <title>Customer Forecast Items</title>
            </head>
            <style>
                p {
                    font-size: 12px;
                }
                table, th, td {
                    border: 1px solid black;
                    border-collapse: collapse;
                }
                th {
                    padding: 5px;
                    text-align: center;
                    font-size: 14px;
                }
                td {
                    padding: 5px;
                    font-size: 12px;
                }
                table {
                    width: 100%;
                }
                body {
                    font-family: Arial, Helvetica, sans-serif;
                }
                .logo {
                    width: 250px;
                }
                #footer {
                    position: fixed;
                    left: 0;
                    right: 0;
                    color: #aaa;
                    font-size: 0.9em;
                }
                #header {
                    top: 0;
                    border-bottom: 0.1pt solid #aaa;
                }
                #footer {
                    bottom: 0;
                    border-top: 0.1pt solid #aaa;
                }
                .page-number:before {
                    content: "Page " counter(page);
                }
            </style>
            <body>
                <img src="./images/logo.png" alt="Adamay Logo" class="logo">
                <h2 style="text-align: center">Customer Forecast Items</h2>
                <table>
                    <tr>
                        <td style="width: 30%"><strong>Forecast Number</strong></td>
                        <td>'.$forecast->forecast_number.'</td>
                    </tr>
                    <tr>
                        <td style="width: 30%"><strong>Customer Name</strong></td>
                        <td>'.$client->client_name.'</td>
                    </tr>
                    <tr>
                        <td style="width: 30%"><strong>Customer Number</strong></td>
                        <td>'.$client->client_number.'</td>
                    </tr>
                </table>
                <br>
                <p style="display: '.$search_filter_class.'">Search Filter: "'.$request['keywords'].'"</p>
                <p style="display: '.$sort_class.'">Sort by: '.$sortProp.", ".$sortOrder.'</p>
                <table>
                    <tr>
                        <th style="width: 30%">Order Number</th>
                        <th style="width: 30%">Quantity</th>
                        <th style="width: 40%">Delivery Date</th>
                    </tr>
                    '.$table_contents.'
                </table>
                <div id="footer">
                    <div class="page-number"></div>
                </div>
            </body>
            </html>
            <script type="text/php">
                if (isset($pdf)) {
                    $text = "page {PAGE_NUM} / {PAGE_COUNT}";
                    $size = 10;
                    $font = $fontMetrics->getFont("Arial");
                    $width = $fontMetrics->get_text_width($text, $font, $size) / 2;
                    $x = ($pdf->get_width() - $width) / 2;
                    $y = $pdf->get_height() - 35;
                    $pdf->page_text($x, $y, $text, $font, $size);
                }
            </script>
        ';

        $pdf = PDF::loadHTML($html);
        $pdf->setPaper('A4', 'portrait');
        return $pdf->stream('Adamay_'.$filename.'.pdf');
    }

    public function exportPDFSuppliers(Request $request){
        $filename = 'Suppliers_'.date('Y:m:d H:i:s');

        $sortProp = $request['sortProp'];
        $sortOrder = $request['sortOrder'];
        if($sortProp != 'undefined' && $sortOrder != 'undefined'){
            if($sortOrder === 'descending') $sortOrder = 'desc';
            else $sortOrder = 'asc';
        }
        else{
            $sortProp = 'supplier_name';
            $sortOrder = 'asc';
        }

        if($request['showDeleted'] == 'true'){
            $suppliers =  Suppliers::onlyTrashed()
                ->where(function($query) use ($request){
                    $query->where('supplier_name','like','%'.$request['keywords'].'%')
                        ->orWhere('supplier_code','like','%'.$request['keywords'].'%');
                })
                ->orderBy($sortProp, $sortOrder)
                // ->take(100)
                ->get();
            $search_filter_class = 'block';
        }
        else{
            $suppliers = Suppliers::where(function($query) use ($request){
                $query->where('supplier_name','like','%'.$request['keywords'].'%')
                    ->orWhere('supplier_code','like','%'.$request['keywords'].'%');
            })
                ->orderBy($sortProp, $sortOrder)
                // ->take(100)
                ->get();
            $search_filter_class = 'none';
        }

        if($request['sortProp'] || $request['sortOrder']) $sort_class = 'block';
        else $sort_class = 'none';

        if(strlen($request['keywords']) > 0) $search_filter_class = 'block';
        else $search_filter_class = 'none';

        $table_contents = '';                
        foreach($suppliers as $supplier){
            $supplier_category = '';
            if($supplier->supplier_category == 0) $supplier_category = 'Overseas Supplier';
            else $supplier_category = 'Domestic Supplier';

            $supplier_classification = '';
            if($supplier->supplier_classification_id == 0) $supplier_classification = 'Direct Material';
            else if($supplier->supplier_classification_id == 1) $supplier_classification = 'Indirect Material';
            else if($supplier->supplier_classification_id == 2) $supplier_classification = 'Sub-contractor';
            else if($supplier->supplier_classification_id == 3) $supplier_classification = 'Service Provider';
            else if($supplier->supplier_classification_id == 4) $supplier_classification = 'Warehousing';
            else $supplier_classification = 'Dispatch contractor';

            $table_contents .= '
                <tr>
                    <td>'.$supplier->supplier_code.'</td>
                    <td>'.$supplier->supplier_name.'</td>
                    <td>'.$supplier_category.'</td>
                    <td>'.$supplier_classification.'</td>
                    <td>'.$supplier->supplier_contact.'</td>
                    <td>'.$supplier->supplier_fax.'</td>
                </tr>
            ';
        }

        $html = '
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <meta http-equiv="X-UA-Compatible" content="ie=edge">
                <title>Suppliers</title>
            </head>
            <style>
                p {
                    font-size: 12px;
                }
                table, th, td {
                    border: 1px solid black;
                    border-collapse: collapse;
                }
                th {
                    padding: 5px;
                    text-align: center;
                    font-size: 14px;
                }
                td {
                    padding: 5px;
                    font-size: 12px;
                }
                table {
                    width: 100%;
                }
                body {
                    font-family: Arial, Helvetica, sans-serif;
                }
                .logo {
                    width: 250px;
                }
                #footer {
                    position: fixed;
                    left: 0;
                    right: 0;
                    color: #aaa;
                    font-size: 0.9em;
                }
                #header {
                    top: 0;
                    border-bottom: 0.1pt solid #aaa;
                }
                #footer {
                    bottom: 0;
                    border-top: 0.1pt solid #aaa;
                }
                .page-number:before {
                    content: "Page " counter(page);
                }
            </style>
            <body>
                <img src="./images/logo.png" alt="Adamay Logo" class="logo">
                <h2 style="text-align: center">Suppliers</h2>
                <p style="display: '.$search_filter_class.'">Search Filter: "'.$request['keywords'].'"</p>
                <p style="display: '.$sort_class.'">Sort by: '.$sortProp.", ".$sortOrder.'</p>
                <table>
                    <tr>
                        <th>Supplier Code</th>
                        <th>Supplier Name</th>
                        <th>Category</th>
                        <th>Classification</th>
                        <th>Contact No.</th>
                        <th>Fax No.</th>
                    </tr>
                    '.$table_contents.'
                </table>
                <div id="footer">
                    <div class="page-number"></div>
                </div>
            </body>
            </html>
            <script type="text/php">
                if (isset($pdf)) {
                    $text = "page {PAGE_NUM} / {PAGE_COUNT}";
                    $size = 10;
                    $font = $fontMetrics->getFont("Arial");
                    $width = $fontMetrics->get_text_width($text, $font, $size) / 2;
                    $x = ($pdf->get_width() - $width) / 2;
                    $y = $pdf->get_height() - 35;
                    $pdf->page_text($x, $y, $text, $font, $size);
                }
            </script>
        ';

        $pdf = PDF::loadHTML($html);
        $pdf->setPaper('A4', 'landscape');
        return $pdf->stream('Adamay_'.$filename.'.pdf');
    }

    public function exportPDFSupplier(Request $request){
        $supplier_id = $request['supplier_id'];
        $supplier = Suppliers::where('id', $supplier_id)->first();
        $filename = 'Supplier_'.$supplier->supplier_name.'_'.date('Y:m:d H:i:s');

        $supplier_category = '';
        if($supplier->supplier_category == 0) $supplier_category = 'Overseas Supplier';
        else $supplier_category = 'Domestic Supplier';

        $supplier_classification = '';
        if($supplier->supplier_classification_id == 0) $supplier_classification = 'Direct Material';
        else if($supplier->supplier_classification_id == 1) $supplier_classification = 'Indirect Material';
        else if($supplier->supplier_classification_id == 2) $supplier_classification = 'Sub-contractor';
        else if($supplier->supplier_classification_id == 3) $supplier_classification = 'Service Provider';
        else if($supplier->supplier_classification_id == 4) $supplier_classification = 'Warehousing';
        else $supplier_classification = 'Dispatch contractor';

        $table_contents = '
            <h3>Supplier Contact</h3>
            <table>
                <tr>
                    <td width="25%">Supplier Name</td>
                    <td>'.$supplier->supplier_name.'</td>
                </tr>
                <tr>
                    <td>Address</td>
                    <td>'.$supplier->supplier_address.'</td>
                </tr>
                <tr>
                    <td>Email</td>
                    <td>'.$supplier->supplier_email.'</td>
                </tr>
                <tr>
                    <td>Contact No.</td>
                    <td>'.$supplier->supplier_contact.'</td>
                </tr>
                <tr>
                    <td>Fax No.</td>
                    <td>'.$supplier->supplier_fax.'</td>
                </tr>
                <tr>
                    <td>Contact Person</td>
                    <td>'.$supplier->supplier_contact_person.'</td>
                </tr>
                <tr>
                    <td>Materials</td>
                    <td>'.$supplier->supplier_materials.'</td>
                </tr>
                <tr>
                    <td>Category</td>
                    <td>'.$supplier_category.'</td>
                </tr>
                <tr>
                    <td>Classification</td>
                    <td>'.$supplier_classification.'</td>
                </tr>
            </table>
        ';                

        $html = '
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <meta http-equiv="X-UA-Compatible" content="ie=edge">
                <title>Supplier</title>
            </head>
            <style>
                p {
                    font-size: 12px;
                }
                table, th, td {
                    border: 1px solid black;
                    border-collapse: collapse;
                }
                th {
                    padding: 5px;
                    text-align: center;
                    font-size: 12px;
                }
                td {
                    padding: 5px;
                    font-size: 12px;
                }
                table {
                    width: 100%;
                }
                body {
                    font-family: Arial, Helvetica, sans-serif;
                }
                .logo {
                    width: 250px;
                }
                #footer {
                    position: fixed;
                    left: 0;
                    right: 0;
                    color: #aaa;
                    font-size: 0.9em;
                }
                #header {
                    top: 0;
                    border-bottom: 0.1pt solid #aaa;
                }
                #footer {
                    bottom: 0;
                    border-top: 0.1pt solid #aaa;
                }
                .page-number:before {
                    content: "Page " counter(page);
                }
            </style>
            <body>
                <img src="./images/logo.png" alt="Adamay Logo" class="logo">
                <h2 style="text-align: center">Supplier Information</h2>
                '.$table_contents.'
                <div id="footer">
                    <div class="page-number"></div>
                </div>
            </body>
            </html>
        ';

        $pdf = PDF::loadHTML($html);
        $pdf->setPaper('A4', 'portrait');
        return $pdf->stream('Adamay_'.$filename.'.pdf');
    }

    public function exportPDFRawMaterials(Request $request){
        $filename = 'RawMaterials_'.date('Y:m:d H:i:s');

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
            $materials =  Materials::onlyTrashed()
                ->where(function($query) use ($request){
                    $query->where('material_description','like','%'.$request['keywords'].'%');
                })
                ->orderBy($sortProp, $sortOrder)
                ->get();
            $search_filter_class = 'block';
        }
        else{
            $materials = Materials::where(function($query) use ($request){
                $query->where('material_description','like','%'.$request['keywords'].'%');
            })
                ->orderBy($sortProp, $sortOrder)
                ->get();
            $search_filter_class = 'none';
        }

        if($request['sortProp'] || $request['sortOrder']) $sort_class = 'block';
        else $sort_class = 'none';

        if(strlen($request['keywords']) > 0) $search_filter_class = 'block';
        else $search_filter_class = 'none';

        $table_contents = '';
        $test = '';
        foreach($materials as $material){

            $type_code = TypeCode::where('id', $material->material_type_id)->first();
            $classification = MaterialClassifications::where('id', $material->material_classification_id)->first();

            $dimension = $material->material_length.' '.$material->material_length_unit.' x '.$material->material_width.' '.$material->material_width_unit;

            $table_contents .= '
                <tr>
                    <td>'.$material->material_description.'</td>
                    <td>'.$type_code->type_code.' - '.$type_code->type_code_description.'</td>
                    <td>'.$classification['material_classification_name'].'</td>
                    <td>'.$dimension.'</td>
                </tr>
            ';
        }

        $html = '
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <meta http-equiv="X-UA-Compatible" content="ie=edge">
                <title>Raw Materials</title>
            </head>
            <style>
                p {
                    font-size: 12px;
                }
                table, th, td {
                    border: 1px solid black;
                    border-collapse: collapse;
                }
                th {
                    padding: 5px;
                    text-align: center;
                    font-size: 14px;
                }
                td {
                    padding: 5px;
                    font-size: 12px;
                }
                table {
                    width: 100%;
                }
                body {
                    font-family: Arial, Helvetica, sans-serif;
                }
                .logo {
                    width: 250px;
                }
                #footer {
                    position: fixed;
                    left: 0;
                    right: 0;
                    color: #aaa;
                    font-size: 0.9em;
                }
                #header {
                    top: 0;
                    border-bottom: 0.1pt solid #aaa;
                }
                #footer {
                    bottom: 0;
                    border-top: 0.1pt solid #aaa;
                }
                .page-number:before {
                    content: "Page " counter(page);
                }
            </style>
            <body>
                <img src="./images/logo.png" alt="Adamay Logo" class="logo">
                <h2 style="text-align: center">Raw Materials</h2>
                <p style="display: '.$search_filter_class.'">Search Filter: "'.$request['keywords'].'"</p>
                <p style="display: '.$sort_class.'">Sort by: '.$sortProp.", ".$sortOrder.'</p>
                <table>
                    <tr>
                        <th>Material Description</th>
                        <th>Material Type</th>
                        <th>Classification</th>
                        <th>Dimension</th>
                    </tr>
                    '.$table_contents.'
                </table>
                <div id="footer">
                    <div class="page-number"></div>
                </div>
            </body>
            </html>
            <script type="text/php">
                if (isset($pdf)) {
                    $text = "page {PAGE_NUM} / {PAGE_COUNT}";
                    $size = 10;
                    $font = $fontMetrics->getFont("Arial");
                    $width = $fontMetrics->get_text_width($text, $font, $size) / 2;
                    $x = ($pdf->get_width() - $width) / 2;
                    $y = $pdf->get_height() - 35;
                    $pdf->page_text($x, $y, $text, $font, $size);
                }
            </script>
        ';

        $pdf = PDF::loadHTML($html);
        $pdf->setPaper('A4', 'landscape');
        return $pdf->stream('Adamay_'.$filename.'.pdf');
    }

    public function exportPDFRawMaterial(Request $request){
        $material_id = $request['material_id'];
        $material = Materials::where('id', $material_id)->first();
        $filename = 'RawMaterial_'.$material->material_description.'_'.date('Y:m:d H:i:s');

        $type_code = TypeCode::where('id', $material->material_type_id)->first();
        $classification = MaterialClassifications::where('id', $material->material_classification_id)->first();
        $dimension = $material->material_length.' '.$material->material_length_unit.' x '.$material->material_width.' '.$material->material_width_unit;

        $flows = MaterialsTracking::where('material_id', $material_id)->pluck('material_flow')->toArray();
        $in_flows = 0;
        $out_flows = 0;

        if(in_array(1, $flows) || in_array(2, $flows)) $in_flows = array_sum(MaterialsTracking::where('material_id', $material_id)->whereIn('material_flow', [1, 2])->pluck('material_quantity')->toArray());
        if(in_array(3, $flows) || in_array(4, $flows)) $out_flows = array_sum(MaterialsTracking::where('material_id', $material_id)->whereIn('material_flow', [3, 4])->pluck('material_quantity')->toArray());

        $material_unit_id =  MaterialsTracking::where('material_id', $material_id)->first()->material_quantity_unit;
        $quantity_units = [
            ['id' => 1, 'unit' => 'pcs'], 
            ['id' => 2, 'unit' => 'boxes'], 
            ['id' => 3, 'unit' => 'rolls'], 
            ['id' => 4, 'unit' => 'sheets']
        ];
        
        for($i = 0; $i < count($quantity_units); $i++){
            if($quantity_units[$i]['id'] == $material_unit_id) $quantity_unit = $quantity_units[$i]['unit'];
        }

        $material_quantity = $in_flows - $out_flows.' '.$quantity_unit;

        $table_contents = '
            <table>
                <tr>
                    <td width="25%">Material Description</td>
                    <td>'.$material->material_description.'</td>
                </tr>
                <tr>
                    <td>Type Code</td>
                    <td>'.$type_code->type_code.' - '.$type_code->type_code_description.'</td>
                </tr>
                <tr>
                    <td>Classification</td>
                    <td>'.$classification['material_classification_name'].'</td>
                </tr>
                <tr>
                    <td>Dimension</td>
                    <td>'.$dimension.'</td>
                </tr>
                <tr>
                    <td>Last In</td>
                    <td>'.Carbon::parse($material->material_last_in)->format('m/d/Y H:i').'</td>
                </tr>
                <tr>
                    <td>Last Out</td>
                    <td>'.Carbon::parse($material->material_last_out)->format('m/d/Y H:i').'</td>
                </tr>
                <tr>
                    <td>Quantity</td>
                    <td>'.$material_quantity.'</td>
                </tr>
                <tr>
                    <td>Location</td>
                    <td>'.$material->material_location.'</td>
                </tr>
            </table>
        ';                

        $html = '
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <meta http-equiv="X-UA-Compatible" content="ie=edge">
                <title>Raw Material</title>
            </head>
            <style>
                p {
                    font-size: 12px;
                }
                table, th, td {
                    border: 1px solid black;
                    border-collapse: collapse;
                }
                th {
                    padding: 5px;
                    text-align: center;
                    font-size: 12px;
                }
                td {
                    padding: 5px;
                    font-size: 12px;
                }
                table {
                    width: 100%;
                }
                body {
                    font-family: Arial, Helvetica, sans-serif;
                }
                .logo {
                    width: 250px;
                }
                #footer {
                    position: fixed;
                    left: 0;
                    right: 0;
                    color: #aaa;
                    font-size: 0.9em;
                }
                #header {
                    top: 0;
                    border-bottom: 0.1pt solid #aaa;
                }
                #footer {
                    bottom: 0;
                    border-top: 0.1pt solid #aaa;
                }
                .page-number:before {
                    content: "Page " counter(page);
                }
            </style>
            <body>
                <img src="./images/logo.png" alt="Adamay Logo" class="logo">
                <h2 style="text-align: center">Raw Material Information</h2>
                '.$table_contents.'
                <div id="footer">
                    <div class="page-number"></div>
                </div>
            </body>
            </html>
        ';

        $pdf = PDF::loadHTML($html);
        $pdf->setPaper('A4', 'portrait');
        return $pdf->stream('Adamay_'.$filename.'.pdf');
    }

    public function exportPDFRawMaterialTracking(Request $request){
        $filename = 'RawMaterialTracking_'.date('Y:m:d H:i:s');

        $material_id = $request['material_id'];
        $filter = [$request['filter']];

        $material_flows = [
            [ 'id' => 1, 'material_flow_name' => 'Purchased' ],
            [ 'id' => 2, 'material_flow_name' => 'Transfered In' ],
            [ 'id' => 3, 'material_flow_name' => 'Used' ],
            [ 'id' => 4, 'material_flow_name' => 'Transfer Out' ],
        ];
        $quantity_units = [
            ['id' => 1, 'unit' => 'pcs'], 
            ['id' => 2, 'unit' => 'boxes'], 
            ['id' => 3, 'unit' => 'rolls'], 
            ['id' => 4, 'unit' => 'sheets']
        ];

        $filter_name = '';
        if($filter[0] == NULL){
            $filter = [1, 2, 3, 4];
            $filter_name = 'All';
        }
        else{
            for($i = 0; $i < count($material_flows); $i++){
                if($material_flows[$i]['id'] == $filter[0]){
                    $filter_name = $material_flows[$i]['material_flow_name'];
                }
            }
        }

        $tracks = MaterialsTracking::where('material_id', $material_id)->whereIn('material_flow', $filter)->orderBy('created_at', 'desc')->get();

        $table_contents = '';                
        foreach($tracks as $track){
            for($i = 0; $i < count($material_flows); $i++){
                if($material_flows[$i]['id'] == $track->material_flow) $transaction = $material_flows[$i]['material_flow_name'];
            }

            $supplier_material = SupplierMaterial::where('id', $track->supplier_material_id)->first();
            $supplier = Suppliers::where('id', $supplier_material->supplier_id)->first();

            for($i = 0; $i < count($quantity_units); $i++){
                if($quantity_units[$i]['id'] == $track->material_quantity_unit) $quantity_unit = $quantity_units[$i]['unit'];
            }

            if((int)$track->material_unit_amount == 0) $amount = '-';
            else $amount = $track->material_unit_amount.' '.$track->material_unit_amount_currency;

            $table_contents .= '
                <tr>
                    <td>'.$transaction.'</td>
                    <td>'.$supplier_material->material_code.'</td>
                    <td>'.$supplier->supplier_name.'</td>
                    <td>'.$track->material_quantity.' '.$quantity_unit.'</td>
                    <td style="text-align: right">'.$amount.'</td>
                    <td>'.Carbon::parse($track->created_at)->format('m/d/Y H:i').'</td>
                </tr>
            ';
        }

        $html = '
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <meta http-equiv="X-UA-Compatible" content="ie=edge">
                <title></title>
            </head>
            <style>
                p {
                    font-size: 12px;
                }
                table, th, td {
                    border: 1px solid black;
                    border-collapse: collapse;
                }
                th {
                    padding: 5px;
                    text-align: center;
                    font-size: 14px;
                }
                td {
                    padding: 5px;
                    font-size: 12px;
                }
                table {
                    width: 100%;
                }
                body {
                    font-family: Arial, Helvetica, sans-serif;
                }
                .logo {
                    width: 250px;
                }
                #footer {
                    position: fixed;
                    left: 0;
                    right: 0;
                    color: #aaa;
                    font-size: 0.9em;
                }
                #header {
                    top: 0;
                    border-bottom: 0.1pt solid #aaa;
                }
                #footer {
                    bottom: 0;
                    border-top: 0.1pt solid #aaa;
                }
                .page-number:before {
                    content: "Page " counter(page);
                }
            </style>
            <body>
                <img src="./images/logo.png" alt="Adamay Logo" class="logo">
                <h2 style="text-align: center">Raw Material Tracking</h2>
                <p style="display: block">Show: "'.$filter_name.'"</p>
                <table>
                    <tr>
                        <th>Transaction</th>
                        <th>Material Code</th>
                        <th>Supplier</th>
                        <th>Quantity</th>
                        <th>Amount</th>
                        <th>Date</th>
                    </tr>
                    '.$table_contents.'
                </table>
                <div id="footer">
                    <div class="page-number"></div>
                </div>
            </body>
            </html>
            <script type="text/php">
                if (isset($pdf)) {
                    $text = "page {PAGE_NUM} / {PAGE_COUNT}";
                    $size = 10;
                    $font = $fontMetrics->getFont("Arial");
                    $width = $fontMetrics->get_text_width($text, $font, $size) / 2;
                    $x = ($pdf->get_width() - $width) / 2;
                    $y = $pdf->get_height() - 35;
                    $pdf->page_text($x, $y, $text, $font, $size);
                }
            </script>
        ';

        $pdf = PDF::loadHTML($html);
        $pdf->setPaper('A4', 'landscape');
        return $pdf->stream('Adamay_'.$filename.'.pdf');
    }

    public function exportPDFRawMaterialSuppliers(Request $request){
        $filename = 'RawMaterialSuppliers_'.date('Y:m:d H:i:s');

        $material_id = $request['material_id'];
        $filter = [$request['filter']];

        $material_flows = [
            [ 'id' => 1, 'material_flow_name' => 'Purchased' ],
            [ 'id' => 2, 'material_flow_name' => 'Transfered In' ],
            [ 'id' => 3, 'material_flow_name' => 'Used' ],
            [ 'id' => 4, 'material_flow_name' => 'Transfer Out' ],
        ];
        $quantity_units = [
            ['id' => 1, 'unit' => 'pcs'], 
            ['id' => 2, 'unit' => 'boxes'], 
            ['id' => 3, 'unit' => 'rolls'], 
            ['id' => 4, 'unit' => 'sheets']
        ];

        $suppliers = SupplierMaterial::select('supplier_material.*', 'suppliers.id as supplier_id', 'suppliers.supplier_code', 'suppliers.supplier_name')
            ->where('supplier_material.material_id', $material_id)
            ->leftJoin('suppliers', 'suppliers.id', 'supplier_material.supplier_id')
            ->get();

        $tracking_details = MaterialsTracking::select('id', DB::raw("SUM(material_quantity) as total_quantity"), 'material_quantity_unit', 'material_unit_amount', 'material_unit_amount_currency','supplier_material_id', 'created_at')
            ->where('material_id', $material_id)
            ->whereIn('material_flow', [1, 2])
            ->groupBy('supplier_material_id')
            ->get();

        $table_contents = '';                
        foreach($suppliers as $supplier){
            for($i = 0; $i < count($tracking_details); $i++){
                if($tracking_details[$i]['supplier_material_id'] == $supplier->id){
                    for($j = 0; $j < count($quantity_units); $j++) { 
                        if($quantity_units[$j]['id'] == $tracking_details[$i]['material_quantity_unit']) $quantity_unit = $quantity_units[$j]['unit'];
                    }
                    $quantity = $tracking_details[$i]['total_quantity'].' '.$quantity_unit;
                    $amount = $tracking_details[$i]['material_unit_amount'].' '.$tracking_details[$i]['material_unit_amount_currency'];
                    $table_contents .= '
                        <tr>
                            <td>'.$supplier->supplier_code.'</td>
                            <td>'.$supplier->supplier_name.'</td>
                            <td>'.$supplier->material_code.'</td>
                            <td>'.$quantity.'</td>
                            <td style="text-align: right">'.$amount.'</td>
                            <td>'.$tracking_details[$i]['created_at'].'</td>
                        </tr>
                    ';
                }
            }
        }

        $html = '
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <meta http-equiv="X-UA-Compatible" content="ie=edge">
                <title>Raw Material Suppliers</title>
            </head>
            <style>
                p {
                    font-size: 12px;
                }
                table, th, td {
                    border: 1px solid black;
                    border-collapse: collapse;
                }
                th {
                    padding: 5px;
                    text-align: center;
                    font-size: 14px;
                }
                td {
                    padding: 5px;
                    font-size: 12px;
                }
                table {
                    width: 100%;
                }
                body {
                    font-family: Arial, Helvetica, sans-serif;
                }
                .logo {
                    width: 250px;
                }
                #footer {
                    position: fixed;
                    left: 0;
                    right: 0;
                    color: #aaa;
                    font-size: 0.9em;
                }
                #header {
                    top: 0;
                    border-bottom: 0.1pt solid #aaa;
                }
                #footer {
                    bottom: 0;
                    border-top: 0.1pt solid #aaa;
                }
                .page-number:before {
                    content: "Page " counter(page);
                }
            </style>
            <body>
                <img src="./images/logo.png" alt="Adamay Logo" class="logo">
                <h2 style="text-align: center">Raw Material Suppliers</h2>
                <table>
                    <tr>
                        <th>Supplier Code</th>
                        <th>Supplier Name</th>
                        <th>Material Code</th>
                        <th>Quantity</th>
                        <th>Amount</th>
                        <th>Date</th>
                    </tr>
                    '.$table_contents.'
                </table>
                <div id="footer">
                    <div class="page-number"></div>
                </div>
            </body>
            </html>
            <script type="text/php">
                if (isset($pdf)) {
                    $text = "page {PAGE_NUM} / {PAGE_COUNT}";
                    $size = 10;
                    $font = $fontMetrics->getFont("Arial");
                    $width = $fontMetrics->get_text_width($text, $font, $size) / 2;
                    $x = ($pdf->get_width() - $width) / 2;
                    $y = $pdf->get_height() - 35;
                    $pdf->page_text($x, $y, $text, $font, $size);
                }
            </script>
        ';

        $pdf = PDF::loadHTML($html);
        $pdf->setPaper('A4', 'landscape');
        return $pdf->stream('Adamay_'.$filename.'.pdf');
    }

    public function exportPDFSCMaterials(Request $request){
        $filename = 'SubcontractMaterials_'.date('Y:m:d H:i:s');

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
            $materials =  SCMaterials::onlyTrashed()
                ->where(function($query) use ($request){
                    $query->where('sc_material_name','like','%'.$request['keywords'].'%');
                })
                ->orderBy($sortProp, $sortOrder)
                ->get();
            $search_filter_class = 'block';
        }
        else{
            $sc_materials = SCMaterials::where(function($query) use ($request){
                $query->where('sc_material_name','like','%'.$request['keywords'].'%');
            })
                ->orderBy($sortProp, $sortOrder)
                ->get();
            $search_filter_class = 'none';
        }

        if($request['sortProp'] || $request['sortOrder']) $sort_class = 'block';
        else $sort_class = 'none';

        if(strlen($request['keywords']) > 0) $search_filter_class = 'block';
        else $search_filter_class = 'none';

        $table_contents = '';
        $test = '';
        foreach($sc_materials as $sc_material){

            $type_code = TypeCode::where('id', $sc_material->sc_material_type_id)->first();
            $classification = MaterialClassifications::where('id', $sc_material->sc_material_classification_id)->first();

            $table_contents .= '
                <tr>
                    <td>'.$sc_material->sc_material_name.'</td>
                    <td>'.$sc_material->sc_order_part_no.'</td>
                    <td>'.$type_code->type_code.' - '.$type_code->type_code_description.'</td>
                    <td>'.$classification['material_classification_name'].'</td>
                </tr>
            ';
        }

        $html = '
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <meta http-equiv="X-UA-Compatible" content="ie=edge">
                <title>Subcontract Materials</title>
            </head>
            <style>
                p {
                    font-size: 12px;
                }
                table, th, td {
                    border: 1px solid black;
                    border-collapse: collapse;
                }
                th {
                    padding: 5px;
                    text-align: center;
                    font-size: 14px;
                }
                td {
                    padding: 5px;
                    font-size: 12px;
                }
                table {
                    width: 100%;
                }
                body {
                    font-family: Arial, Helvetica, sans-serif;
                }
                .logo {
                    width: 250px;
                }
                #footer {
                    position: fixed;
                    left: 0;
                    right: 0;
                    color: #aaa;
                    font-size: 0.9em;
                }
                #header {
                    top: 0;
                    border-bottom: 0.1pt solid #aaa;
                }
                #footer {
                    bottom: 0;
                    border-top: 0.1pt solid #aaa;
                }
                .page-number:before {
                    content: "Page " counter(page);
                }
            </style>
            <body>
                <img src="./images/logo.png" alt="Adamay Logo" class="logo">
                <h2 style="text-align: center">Subcontract Materials</h2>
                <p style="display: '.$search_filter_class.'">Search Filter: "'.$request['keywords'].'"</p>
                <p style="display: '.$sort_class.'">Sort by: '.$sortProp.", ".$sortOrder.'</p>
                <table>
                    <tr>
                        <th>Material Name</th>
                        <th>Part Number</th>
                        <th>Material Type</th>
                        <th>Classification</th>
                    </tr>
                    '.$table_contents.'
                </table>
                <div id="footer">
                    <div class="page-number"></div>
                </div>
            </body>
            </html>
            <script type="text/php">
                if (isset($pdf)) {
                    $text = "page {PAGE_NUM} / {PAGE_COUNT}";
                    $size = 10;
                    $font = $fontMetrics->getFont("Arial");
                    $width = $fontMetrics->get_text_width($text, $font, $size) / 2;
                    $x = ($pdf->get_width() - $width) / 2;
                    $y = $pdf->get_height() - 35;
                    $pdf->page_text($x, $y, $text, $font, $size);
                }
            </script>
        ';

        $pdf = PDF::loadHTML($html);
        $pdf->setPaper('A4', 'landscape');
        return $pdf->stream('Adamay_'.$filename.'.pdf');
    }

    public function exportPDFSCMaterial(Request $request){
        $sc_material_id = $request['sc_material_id'];
        $sc_material = SCMaterials::where('id', $sc_material_id)->first();
        $filename = 'SubcontractMaterial_'.$sc_material->sc_material_name.'_'.date('Y:m:d H:i:s');

        $type_code = TypeCode::where('id', $sc_material->sc_material_type_id)->first();
        $classification = MaterialClassifications::where('id', $sc_material->sc_material_classification_id)->first();

        $flows = SCMaterialsTracking::where('sc_material_id', $sc_material_id)->pluck('sc_material_flow')->toArray();
        $in_flows = 0;
        $out_flows = 0;

        if(in_array(1, $flows) || in_array(2, $flows)) $in_flows = array_sum(SCMaterialsTracking::where('sc_material_id', $sc_material_id)->whereIn('sc_material_flow', [1, 2])->pluck('sc_material_quantity')->toArray());
        if(in_array(3, $flows) || in_array(4, $flows)) $out_flows = array_sum(SCMaterialsTracking::where('sc_material_id', $sc_material_id)->whereIn('sc_material_flow', [3, 4])->pluck('sc_material_quantity')->toArray());

        $sc_material_unit_id =  SCMaterialsTracking::where('sc_material_id', $sc_material_id)->first()->sc_material_quantity_unit;
        $quantity_units = [
            ['id' => 1, 'unit' => 'pcs'], 
            ['id' => 2, 'unit' => 'boxes'], 
            ['id' => 3, 'unit' => 'rolls'], 
            ['id' => 4, 'unit' => 'sheets']
        ];
        
        for($i = 0; $i < count($quantity_units); $i++){
            if($quantity_units[$i]['id'] == $sc_material_unit_id) $sc_quantity_unit = $quantity_units[$i]['unit'];
        }

        $sc_material_quantity = $in_flows - $out_flows.' '.$sc_quantity_unit;

        $table_contents = '
            <table>
                <tr>
                    <td width="25%">Material Name</td>
                    <td>'.$sc_material->sc_material_name.'</td>
                </tr>
                <tr>
                    <td width="25%">Part Number</td>
                    <td>'.$sc_material->sc_order_part_no.'</td>
                </tr>
                <tr>
                    <td>Type Code</td>
                    <td>'.$type_code->type_code.' - '.$type_code->type_code_description.'</td>
                </tr>
                <tr>
                    <td>Classification</td>
                    <td>'.$classification['material_classification_name'].'</td>
                </tr>
                <tr>
                    <td>Last In</td>
                    <td>'.Carbon::parse($sc_material->sc_material_last_in)->format('m/d/Y H:i').'</td>
                </tr>
                <tr>
                    <td>Last Out</td>
                    <td>'.Carbon::parse($sc_material->sc_material_last_out)->format('m/d/Y H:i').'</td>
                </tr>
                <tr>
                    <td>Quantity</td>
                    <td>'.$sc_material_quantity.'</td>
                </tr>
                <tr>
                    <td>Location</td>
                    <td>'.$sc_material->sc_material_location.'</td>
                </tr>
            </table>
        ';                

        $html = '
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <meta http-equiv="X-UA-Compatible" content="ie=edge">
                <title>Subcontract Material</title>
            </head>
            <style>
                p {
                    font-size: 12px;
                }
                table, th, td {
                    border: 1px solid black;
                    border-collapse: collapse;
                }
                th {
                    padding: 5px;
                    text-align: center;
                    font-size: 12px;
                }
                td {
                    padding: 5px;
                    font-size: 12px;
                }
                table {
                    width: 100%;
                }
                body {
                    font-family: Arial, Helvetica, sans-serif;
                }
                .logo {
                    width: 250px;
                }
                #footer {
                    position: fixed;
                    left: 0;
                    right: 0;
                    color: #aaa;
                    font-size: 0.9em;
                }
                #header {
                    top: 0;
                    border-bottom: 0.1pt solid #aaa;
                }
                #footer {
                    bottom: 0;
                    border-top: 0.1pt solid #aaa;
                }
                .page-number:before {
                    content: "Page " counter(page);
                }
            </style>
            <body>
                <img src="./images/logo.png" alt="Adamay Logo" class="logo">
                <h2 style="text-align: center">Subcontract Material Information</h2>
                '.$table_contents.'
                <div id="footer">
                    <div class="page-number"></div>
                </div>
            </body>
            </html>
        ';

        $pdf = PDF::loadHTML($html);
        $pdf->setPaper('A4', 'portrait');
        return $pdf->stream('Adamay_'.$filename.'.pdf');
    }

    public function exportPDFSCMaterialTracking(Request $request){
        $filename = 'SubcontractMaterialTracking_'.date('Y:m:d H:i:s');

        $sc_material_id = $request['sc_material_id'];
        $filter = [$request['filter']];

        $material_flows = [
            [ 'id' => 1, 'material_flow_name' => 'Purchased' ],
            [ 'id' => 2, 'material_flow_name' => 'Transfered In' ],
            [ 'id' => 3, 'material_flow_name' => 'Used' ],
            [ 'id' => 4, 'material_flow_name' => 'Transfer Out' ],
        ];

        $quantity_units = [
            ['id' => 1, 'unit' => 'pcs'], 
            ['id' => 2, 'unit' => 'boxes'], 
            ['id' => 3, 'unit' => 'rolls'], 
            ['id' => 4, 'unit' => 'sheets']
        ];

        $filter_name = '';
        if($filter[0] == NULL){
            $filter = [1, 2, 3, 4];
            $filter_name = 'All';
        }
        else{
            for($i = 0; $i < count($material_flows); $i++){
                if($material_flows[$i]['id'] == $filter[0]){
                    $filter_name = $material_flows[$i]['material_flow_name'];
                }
            }
        }

        $tracks = SCMaterialsTracking::where('sc_material_id', $sc_material_id)->whereIn('sc_material_flow', $filter)->orderBy('created_at', 'desc')->get();

        $table_contents = '';                
        foreach($tracks as $track){
            for($i = 0; $i < count($material_flows); $i++){
                if($material_flows[$i]['id'] == $track->sc_material_flow) $transaction = $material_flows[$i]['material_flow_name'];
            }

            $sc_supplier_material = SupplierSCMaterial::where('id', $track->supplier_sc_material_id)->first();
            $supplier = Suppliers::where('id', $sc_supplier_material->supplier_id)->first();

            for($i = 0; $i < count($quantity_units); $i++){
                if($quantity_units[$i]['id'] == $track->sc_material_quantity_unit) $quantity_unit = $quantity_units[$i]['unit'];
            }

            if((int)$track->sc_material_unit_amount == 0) $amount = '-';
            else $amount = $track->sc_material_unit_amount.' '.$track->sc_material_unit_amount_currency;

            $table_contents .= '
                <tr>
                    <td>'.$transaction.'</td>
                    <td>'.$sc_supplier_material->sc_material_code.'</td>
                    <td>'.$supplier->supplier_name.'</td>
                    <td>'.$track->sc_material_quantity.' '.$quantity_unit.'</td>
                    <td style="text-align: right">'.$amount.'</td>
                    <td>'.Carbon::parse($track->created_at)->format('m/d/Y H:i').'</td>
                </tr>
            ';
        }

        $html = '
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <meta http-equiv="X-UA-Compatible" content="ie=edge">
                <title></title>
            </head>
            <style>
                p {
                    font-size: 12px;
                }
                table, th, td {
                    border: 1px solid black;
                    border-collapse: collapse;
                }
                th {
                    padding: 5px;
                    text-align: center;
                    font-size: 14px;
                }
                td {
                    padding: 5px;
                    font-size: 12px;
                }
                table {
                    width: 100%;
                }
                body {
                    font-family: Arial, Helvetica, sans-serif;
                }
                .logo {
                    width: 250px;
                }
                #footer {
                    position: fixed;
                    left: 0;
                    right: 0;
                    color: #aaa;
                    font-size: 0.9em;
                }
                #header {
                    top: 0;
                    border-bottom: 0.1pt solid #aaa;
                }
                #footer {
                    bottom: 0;
                    border-top: 0.1pt solid #aaa;
                }
                .page-number:before {
                    content: "Page " counter(page);
                }
            </style>
            <body>
                <img src="./images/logo.png" alt="Adamay Logo" class="logo">
                <h2 style="text-align: center">Raw Material Tracking</h2>
                <p style="display: block">Show: "'.$filter_name.'"</p>
                <table>
                    <tr>
                        <th>Transaction</th>
                        <th>Material Code</th>
                        <th>Supplier</th>
                        <th>Quantity</th>
                        <th>Amount</th>
                        <th>Date</th>
                    </tr>
                    '.$table_contents.'
                </table>
                <div id="footer">
                    <div class="page-number"></div>
                </div>
            </body>
            </html>
            <script type="text/php">
                if (isset($pdf)) {
                    $text = "page {PAGE_NUM} / {PAGE_COUNT}";
                    $size = 10;
                    $font = $fontMetrics->getFont("Arial");
                    $width = $fontMetrics->get_text_width($text, $font, $size) / 2;
                    $x = ($pdf->get_width() - $width) / 2;
                    $y = $pdf->get_height() - 35;
                    $pdf->page_text($x, $y, $text, $font, $size);
                }
            </script>
        ';

        $pdf = PDF::loadHTML($html);
        $pdf->setPaper('A4', 'landscape');
        return $pdf->stream('Adamay_'.$filename.'.pdf');
    }

    public function exportPDFSCMaterialSuppliers(Request $request){
        $filename = 'SubcontractorMaterialSuppliers_'.date('Y:m:d H:i:s');

        $sc_material_id = $request['sc_material_id'];
        $filter = [$request['filter']];

        $material_flows = [
            [ 'id' => 1, 'material_flow_name' => 'Purchased' ],
            [ 'id' => 2, 'material_flow_name' => 'Transfered In' ],
            [ 'id' => 3, 'material_flow_name' => 'Used' ],
            [ 'id' => 4, 'material_flow_name' => 'Transfer Out' ],
        ];
        $quantity_units = [
            ['id' => 1, 'unit' => 'pcs'], 
            ['id' => 2, 'unit' => 'boxes'], 
            ['id' => 3, 'unit' => 'rolls'], 
            ['id' => 4, 'unit' => 'sheets']
        ];

        $suppliers = SupplierSCMaterial::select('supplier_sc_material.*', 'suppliers.id as supplier_id', 'suppliers.supplier_code', 'suppliers.supplier_name')
            ->where('supplier_sc_material.sc_material_id', $sc_material_id)
            ->leftJoin('suppliers', 'suppliers.id', 'supplier_sc_material.supplier_id')
            ->get();

        $tracking_details = SCMaterialsTracking::select('id', DB::raw("SUM(sc_material_quantity) as total_quantity"), 'sc_material_quantity_unit', 'sc_material_unit_amount', 'sc_material_unit_amount_currency','supplier_sc_material_id', 'created_at')
            ->where('sc_material_id', $sc_material_id)
            ->whereIn('sc_material_flow', [1, 2])
            ->groupBy('supplier_sc_material_id')
            ->get();

        $table_contents = '';                
        foreach($suppliers as $supplier){
            for($i = 0; $i < count($tracking_details); $i++){
                if($tracking_details[$i]['supplier_sc_material_id'] == $supplier->id){
                    for($j = 0; $j < count($quantity_units); $j++) { 
                        if($quantity_units[$j]['id'] == $tracking_details[$i]['sc_material_quantity_unit']) $quantity_unit = $quantity_units[$j]['unit'];
                    }
                    $quantity = $tracking_details[$i]['total_quantity'].' '.$quantity_unit;
                    $amount = $tracking_details[$i]['sc_material_unit_amount'].' '.$tracking_details[$i]['sc_material_unit_amount_currency'];
                    $table_contents .= '
                        <tr>
                            <td>'.$supplier->supplier_code.'</td>
                            <td>'.$supplier->supplier_name.'</td>
                            <td>'.$supplier->sc_material_code.'</td>
                            <td>'.$quantity.'</td>
                            <td style="text-align: right">'.$amount.'</td>
                            <td>'.$tracking_details[$i]['created_at'].'</td>
                        </tr>
                    ';
                }
            }
        }

        $html = '
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <meta http-equiv="X-UA-Compatible" content="ie=edge">
                <title>Raw Material Suppliers</title>
            </head>
            <style>
                p {
                    font-size: 12px;
                }
                table, th, td {
                    border: 1px solid black;
                    border-collapse: collapse;
                }
                th {
                    padding: 5px;
                    text-align: center;
                    font-size: 14px;
                }
                td {
                    padding: 5px;
                    font-size: 12px;
                }
                table {
                    width: 100%;
                }
                body {
                    font-family: Arial, Helvetica, sans-serif;
                }
                .logo {
                    width: 250px;
                }
                #footer {
                    position: fixed;
                    left: 0;
                    right: 0;
                    color: #aaa;
                    font-size: 0.9em;
                }
                #header {
                    top: 0;
                    border-bottom: 0.1pt solid #aaa;
                }
                #footer {
                    bottom: 0;
                    border-top: 0.1pt solid #aaa;
                }
                .page-number:before {
                    content: "Page " counter(page);
                }
            </style>
            <body>
                <img src="./images/logo.png" alt="Adamay Logo" class="logo">
                <h2 style="text-align: center">Raw Material Suppliers</h2>
                <table>
                    <tr>
                        <th>Supplier Code</th>
                        <th>Supplier Name</th>
                        <th>Material Code</th>
                        <th>Quantity</th>
                        <th>Amount</th>
                        <th>Date</th>
                    </tr>
                    '.$table_contents.'
                </table>
                <div id="footer">
                    <div class="page-number"></div>
                </div>
            </body>
            </html>
            <script type="text/php">
                if (isset($pdf)) {
                    $text = "page {PAGE_NUM} / {PAGE_COUNT}";
                    $size = 10;
                    $font = $fontMetrics->getFont("Arial");
                    $width = $fontMetrics->get_text_width($text, $font, $size) / 2;
                    $x = ($pdf->get_width() - $width) / 2;
                    $y = $pdf->get_height() - 35;
                    $pdf->page_text($x, $y, $text, $font, $size);
                }
            </script>
        ';

        $pdf = PDF::loadHTML($html);
        $pdf->setPaper('A4', 'landscape');
        return $pdf->stream('Adamay_'.$filename.'.pdf');
    }

    public function exportPDFProducts(Request $request){
        $filename = 'Products_'.date('Y:m:d H:i:s');

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
            $products =  Products::onlyTrashed()
                ->where(function($query) use ($request){
                    $query->where('product_code','like','%'.$request['search'].'%')
                    ->orWhere('product_part_number','like','%'.$request['search'].'%')
                    ->orWhere('product_name','like','%'.$request['search'].'%');
                })
                ->orderBy($sortProp, $sortOrder)
                ->get();
            $search_filter_class = 'block';
        }
        else{
            $products = Products::where(function($query) use ($request){
                    $query->where('product_code','like','%'.$request['search'].'%')
                    ->orWhere('product_part_number','like','%'.$request['search'].'%')
                    ->orWhere('product_name','like','%'.$request['search'].'%');
                })
                ->orderBy($sortProp, $sortOrder)
                ->get();
            $search_filter_class = 'none';
        }

        if($request['sortProp'] || $request['sortOrder']) $sort_class = 'block';
        else $sort_class = 'none';

        if(strlen($request['keywords']) > 0) $search_filter_class = 'block';
        else $search_filter_class = 'none';

        $table_contents = '';
        $test = '';
        foreach($products as $product){
            $classification = MaterialClassifications::where('id', $product->product_classification_id)->first();

            $table_contents .= '
                <tr>
                    <td>'.$product->product_code.'</td>
                    <td>'.$product->product_part_number.'</td>
                    <td>'.$product->product_name.'</td>
                    <td>'.$classification['material_classification_name'].'</td>
                </tr>
            ';
        }

        $html = '
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <meta http-equiv="X-UA-Compatible" content="ie=edge">
                <title>Finish Products</title>
            </head>
            <style>
                p {
                    font-size: 12px;
                }
                table, th, td {
                    border: 1px solid black;
                    border-collapse: collapse;
                }
                th {
                    padding: 5px;
                    text-align: center;
                    font-size: 14px;
                }
                td {
                    padding: 5px;
                    font-size: 12px;
                }
                table {
                    width: 100%;
                }
                body {
                    font-family: Arial, Helvetica, sans-serif;
                }
                .logo {
                    width: 250px;
                }
                #footer {
                    position: fixed;
                    left: 0;
                    right: 0;
                    color: #aaa;
                    font-size: 0.9em;
                }
                #header {
                    top: 0;
                    border-bottom: 0.1pt solid #aaa;
                }
                #footer {
                    bottom: 0;
                    border-top: 0.1pt solid #aaa;
                }
                .page-number:before {
                    content: "Page " counter(page);
                }
            </style>
            <body>
                <img src="./images/logo.png" alt="Adamay Logo" class="logo">
                <h2 style="text-align: center">Finish Products</h2>
                <p style="display: '.$search_filter_class.'">Search Filter: "'.$request['keywords'].'"</p>
                <p style="display: '.$sort_class.'">Sort by: '.$sortProp.", ".$sortOrder.'</p>
                <table>
                    <tr>
                        <th>Product Code</th>
                        <th>Product Part Number</th>
                        <th>Product Name</th>
                        <th>Classification</th>
                    </tr>
                    '.$table_contents.'
                </table>
                <div id="footer">
                    <div class="page-number"></div>
                </div>
            </body>
            </html>
            <script type="text/php">
                if (isset($pdf)) {
                    $text = "page {PAGE_NUM} / {PAGE_COUNT}";
                    $size = 10;
                    $font = $fontMetrics->getFont("Arial");
                    $width = $fontMetrics->get_text_width($text, $font, $size) / 2;
                    $x = ($pdf->get_width() - $width) / 2;
                    $y = $pdf->get_height() - 35;
                    $pdf->page_text($x, $y, $text, $font, $size);
                }
            </script>
        ';

        $pdf = PDF::loadHTML($html);
        $pdf->setPaper('A4', 'landscape');
        return $pdf->stream('Adamay_'.$filename.'.pdf');
    }

    public function exportPDFProduct(Request $request){
        $product_id = $request['product_id'];
        $product = Products::where('id', $product_id)->first();
        $filename = 'Product_'.$product->product_code.'_'.date('Y:m:d H:i:s');

        $type_code = TypeCode::where('id', $product->product_type_code_id)->first();
        $classification = MaterialClassifications::where('id', $product->product_classification_id)->first();

        $quantity_units = [
            ['id' => 1, 'unit' => 'pcs'], 
            ['id' => 2, 'unit' => 'boxes'], 
            ['id' => 3, 'unit' => 'rolls'], 
            ['id' => 4, 'unit' => 'sheets']
        ];
        
        for($i = 0; $i < count($quantity_units); $i++){
            if($quantity_units[$i]['id'] == $product->product_stock_quantity_unit) $product_quantity_unit = $quantity_units[$i]['unit'];
        }

        $table_contents = '
            <table>
                <tr>
                    <td width="25%">SS Reference No.</td>
                    <td>'.$product->ss_reference_no.'</td>
                </tr>
                <tr>
                    <td width="25%">Order Item Code</td>
                    <td>'.$product->order_item_code.'</td>
                </tr>
                <tr>
                    <td>Product Code</td>
                    <td>'.$product->product_code.'</td>
                </tr>
                <tr>
                    <td>Product Part Number</td>
                    <td>'.$product->product_part_number.'</td>
                </tr>
                <tr>
                    <td>Part Name</td>
                    <td>'.$product->product_name.'</td>
                </tr>
                <tr>
                    <td>Type Code</td>
                    <td>'.$type_code->type_code.' - '.$type_code->type_code_description.'</td>
                </tr>
                <tr>
                    <td>Classification</td>
                    <td>'.$classification['material_classification_name'].'</td>
                </tr>
                <tr>
                    <td>Last In</td>
                    <td>'.Carbon::parse($product->product_last_in)->format('m/d/Y H:i').'</td>
                </tr>
                <tr>
                    <td>Last Out</td>
                    <td>'.Carbon::parse($product->product_last_out)->format('m/d/Y H:i').'</td>
                </tr>
                <tr>
                    <td>Quantity</td>
                    <td>'.$product->product_stock_quantity.' '.$product_quantity_unit.'</td>
                </tr>
                <tr>
                    <td>Location</td>
                    <td>'.$product->product_location.'</td>
                </tr>
            </table>
        ';                

        $html = '
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <meta http-equiv="X-UA-Compatible" content="ie=edge">
                <title>Finish Product</title>
            </head>
            <style>
                p {
                    font-size: 12px;
                }
                table, th, td {
                    border: 1px solid black;
                    border-collapse: collapse;
                }
                th {
                    padding: 5px;
                    text-align: center;
                    font-size: 12px;
                }
                td {
                    padding: 5px;
                    font-size: 12px;
                }
                table {
                    width: 100%;
                }
                body {
                    font-family: Arial, Helvetica, sans-serif;
                }
                .logo {
                    width: 250px;
                }
                #footer {
                    position: fixed;
                    left: 0;
                    right: 0;
                    color: #aaa;
                    font-size: 0.9em;
                }
                #header {
                    top: 0;
                    border-bottom: 0.1pt solid #aaa;
                }
                #footer {
                    bottom: 0;
                    border-top: 0.1pt solid #aaa;
                }
                .page-number:before {
                    content: "Page " counter(page);
                }
            </style>
            <body>
                <img src="./images/logo.png" alt="Adamay Logo" class="logo">
                <h2 style="text-align: center">Product Information</h2>
                '.$table_contents.'
                <div id="footer">
                    <div class="page-number"></div>
                </div>
            </body>
            </html>
        ';

        $pdf = PDF::loadHTML($html);
        $pdf->setPaper('A4', 'portrait');
        return $pdf->stream('Adamay_'.$filename.'.pdf');
    }

    public function exportPDFProcess(Request $request)
    {
        $process_name = $request['process_name'];
        $filename = $process_name.'_'.date('Y:m:d H:i:s');

        $sortProp = $request['sortProp'];
        $sortOrder = $request['sortOrder'];
        $drop_down_name = $request['dropDownName'];
        $processesTable =  json_decode($request['processesTable']);
        $matrix_process = $request['matrix_process'];
        $bundled = $request['bundled'];
        $bundled_number = $request['bundle_number'];
        $show_all_bundle = $request['show_all_bundle'];
        $cs_identifier = $request['identifier'];

        if($sortProp != 'undefined' && $sortOrder != 'undefined'){
            if($sortOrder === 'descending') $sortOrder = 'desc';
            else $sortOrder = 'asc';
        }
        else{
            $sortProp = 'price';
            $sortOrder = 'asc';
        }

        $table_gsm ='';
        $table_printing = '';
        $table_bundle = '';
        $table_other = '';
        $table_contents ='';
        $table_header = '';
        $table_show_deleted = '';

        $table_all_process = DB::table($matrix_process)
        ->orderBy($sortProp, $sortOrder)
        ->get();

        $table_show_deleted = DB::table($matrix_process)
        ->orderBy($sortProp, $sortOrder)
        ->get()
        ->where('deleted_at', '!=', NULL);

        if($request['showDeleted'] == 'true'){
            if($show_all_bundle == 1){
                foreach ($table_show_deleted as $row){
                    $table_contents .='
                        <tr>
                            <td>'.$row->qty.'</td>
                            <td>'.$row->price.'</td>
                            <td>'.$row->bundle.'</td>
                        </tr>
                    ';
                }
                $table_header .='
                        <tr>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Bundle</th>
                        </tr>
                    ';
            }
            elseif($bundled == 'true' && $show_all_bundle == 'undefined'){
                foreach ($table_show_deleted as $row){
                    $table_contents .='
                        <tr>
                            <td>'.$row->qty.'</td>
                            <td>'.$row->price.'</td>
                        </tr>
                    ';
                }
                $table_header .='
                        <tr>
                            <th>Quantity</th>
                            <th>Price</th>
                        </tr>
                    ';
            }
            elseif($matrix_process == 'matrix_printing'){
                foreach ($table_show_deleted as $row){
                    $table_contents .='
                        <tr>
                            <td>'.$row->qty.'</td>
                            <td>'.$row->color_type.'</td>
                            <td>'.$row->price.'</td>
                        </tr>
                    ';
                }
                $table_header .='
                        <tr>
                            <th>Quantity</th>
                            <th>Color Type</th>
                            <th>Price</th>
                        </tr>
                    ';
            }

            else {
                foreach($table_show_deleted as $row){
                    $table_contents .='
                        <tr>
                            <td>'.$row->qty.'</td>
                            <td>'.$row->price.'</td>
                            <td>'.$row->paper_size.'</td>
                            <td>'.$row->status.'</td>
                        </tr>
                    ';
                }
                $table_header .='
                        <tr>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Paper Size</th>
                            <th>Status</th>
                        </tr>
                    ';
            }

            $bundled_number = $bundled_number .=" (Deleted)";
        }

        else {
                if($show_all_bundle == 1){
                    foreach ($table_all_process as $row){
                        $table_contents .='
                            <tr>
                                <td>'.$row->qty.'</td>
                                <td>'.$row->price.'</td>
                                <td>'.$row->bundle.'</td>
                            </tr>
                        ';
                    }
                    $table_header .='
                            <tr>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Bundle</th>
                            </tr>
                        ';
                }
    
                elseif($bundled == 'true' && $show_all_bundle == 'undefined'){
                    foreach ($table_all_process as $row){
                        $table_contents .='
                            <tr>
                                <td>'.$row->qty.'</td>
                                <td>'.$row->price.'</td>
                            </tr>
                        ';
                    }
                    $table_header .='
                            <tr>
                                <th>Quantity</th>
                                <th>Price</th>
                            </tr>
                        ';
                }
    
                elseif($matrix_process == 'matrix_printing'){
                    foreach ($table_all_process as $row){
                        $table_contents .='
                            <tr>
                                <td>'.$row->qty.'</td>
                                <td>'.$row->color_type.'</td>
                                <td>'.$row->price.'</td>
                            </tr>
                        ';
                    }
                    $table_header .='
                            <tr>
                                <th>Quantity</th>
                                <th>Color Type</th>
                                <th>Price</th>
                            </tr>
                        ';
                }
    
                else {
                    foreach($table_all_process as $row){
                        $table_contents .='
                            <tr>
                                <td>'.$row->qty.'</td>
                                <td>'.$row->price.'</td>
                                <td>'.$row->paper_size.'</td>
                                <td>'.$row->status.'</td>
                            </tr>
                        ';
                    }
                    $table_header .='
                            <tr>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Paper Size</th>
                                <th>Status</th>
                            </tr>
                        ';
                }
        }
        
        



        $html = '
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <meta http-equiv="X-UA-Compatible" content="ie=edge">
                <title></title>
            </head>
            <style>
                p {
                    font-size: 12px;
                }
                table, th, td {
                    border: 1px solid black;
                    border-collapse: collapse;
                }
                th {
                    padding: 5px;
                    text-align: center;
                    font-size: 14px;
                }
                td {
                    padding: 5px;
                    font-size: 12px;
                }
                table {
                    width: 100%;
                }
                body {
                    font-family: Arial, Helvetica, sans-serif;
                }
                .logo {
                    width: 250px;
                }
                #footer {
                    position: fixed;
                    left: 0;
                    right: 0;
                    color: #aaa;
                    font-size: 0.9em;
                }
                #header {
                    top: 0;
                    border-bottom: 0.1pt solid #aaa;
                }
                #footer {
                    bottom: 0;
                    border-top: 0.1pt solid #aaa;
                }
                .page-number:before {
                    content: "Page " counter(page);
                }
            </style>
            <body>
                <img src="./images/logo.png" alt="Adamay Logo" class="logo">
                <h2 style="text-align: center">Processes</h2>
                <h5 style="text-align: center">'.$process_name.' </h5>
                <h6>Number of Bundle: '.$bundled_number.'</h6>
                <table>
                    '.$table_header.'
                    '.$table_contents.' 
                </table>
                <div id="footer">
                    <div class="page-number"></div>
                </div>
            </body>
            </html>
            <script type="text/php">
                if (isset($pdf)) {
                    $text = "page {PAGE_NUM} / {PAGE_COUNT}";
                    $size = 10;
                    $font = $fontMetrics->getFont("Arial");
                    $width = $fontMetrics->get_text_width($text, $font, $size) / 2;
                    $x = ($pdf->get_width() - $width) / 2;
                    $y = $pdf->get_height() - 35;
                    $pdf->page_text($x, $y, $text, $font, $size);
                }
            </script>
        ';

        $pdf = PDF::loadHTML($html);
        $pdf->setPaper('A4', 'landscape');
        return $pdf->stream('Adamay_'.$filename.'.pdf');
    
    }

    public function exportPDFProcesses(Request $request)
    {
        $show_deleted = $request['showDeleted'];
        $page_name = 'Show All Process';
        $table_processes = Process::get();
        

        $filename = $page_name.'_'.date('Y:m:d H:i:s');

        $sortProp = $request['sortProp'];
        $sortOrder = $request['sortOrder'];

        if($sortProp != 'undefined' && $sortOrder != 'undefined'){
            if($sortOrder === 'descending') $sortOrder = 'desc';
            else $sortOrder = 'asc';
        }
        else{
            $sortProp = 'price';
            $sortOrder = 'asc';
        }

        $category = '';
       
        $table_contents = '';
        $table_header = '';
        foreach($table_processes as $row){
            
                if($row->id <=12) $category = 'Pre-defined Process';
                elseif($row->source_table == 'matrix_independent') $category = 'Independent Process';
                else $category = 'Bundled';
        
            $table_contents .='
                    <tr>
                        <td>'.$row->name.'</td>
                        <td>'.$category.'</td>
                        <td>'.$row->description.'</td>
                    </tr>
                ';
        }
        
        $table_header .='
                    <tr>
                        <th>Process Name</th>
                        <th>Category</th>
                        <th>Description</th>
                    </tr>
                ';
        
        $html = '
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <meta http-equiv="X-UA-Compatible" content="ie=edge">
                <title></title>
            </head>
            <style>
                p {
                    font-size: 12px;
                }
                table, th, td {
                    border: 1px solid black;
                    border-collapse: collapse;
                }
                th {
                    padding: 5px;
                    text-align: center;
                    font-size: 14px;
                }
                td {
                    padding: 5px;
                    font-size: 12px;
                }
                table {
                    width: 100%;
                }
                body {
                    font-family: Arial, Helvetica, sans-serif;
                }
                .logo {
                    width: 250px;
                }
                #footer {
                    position: fixed;
                    left: 0;
                    right: 0;
                    color: #aaa;
                    font-size: 0.9em;
                }
                #header {
                    top: 0;
                    border-bottom: 0.1pt solid #aaa;
                }
                #footer {
                    bottom: 0;
                    border-top: 0.1pt solid #aaa;
                }
                .page-number:before {
                    content: "Page " counter(page);
                }
            </style>
            <body>
                <img src="./images/logo.png" alt="Adamay Logo" class="logo">
                <h2 style="text-align: center">Processes</h2>
                <h5 style="text-align: center">'.$page_name.' </h5>

                <table>
                    '.$table_header.'
                    '.$table_contents.' 
                </table>
                <div id="footer">
                    <div class="page-number"></div>
                </div>
            </body>
            </html>
            <script type="text/php">
                if (isset($pdf)) {
                    $text = "page {PAGE_NUM} / {PAGE_COUNT}";
                    $size = 10;
                    $font = $fontMetrics->getFont("Arial");
                    $width = $fontMetrics->get_text_width($text, $font, $size) / 2;
                    $x = ($pdf->get_width() - $width) / 2;
                    $y = $pdf->get_height() - 35;
                    $pdf->page_text($x, $y, $text, $font, $size);
                }
            </script>
        ';

        $pdf = PDF::loadHTML($html);
        $pdf->setPaper('A4', 'landscape');
        return $pdf->stream('Adamay_'.$filename.'.pdf');
    }

    public function exportPDFLocation(Request $request){
        $province_id = $request['province_id'];
        $province = Provinces::where('id', $province_id)->first();
        $filename = 'Province_'.$province->province_name.'_'.date('Y:m:d H:i:s');

        // $municipalities = Municipalities::where('province_id', $province_id)->get();

        $sortProp = $request['sortProp'];
        $sortOrder = $request['sortOrder'];

        if($request['filter'] == 1){
            $customer_filter = [1];
            $supplier_filter = [0, 1];
        }
        else if($request['filter'] == 2){
            $customer_filter = [0, 1];
            $supplier_filter = [1];
        }
        else{
            $customer_filter = [0, 1];
            $supplier_filter = [0, 1];
        }

        if($sortProp != 'undefined' && $sortOrder != 'undefined'){
            if($sortOrder === 'descending') $sortOrder = 'desc';
            else $sortOrder = 'asc';
        }
        else{
            $sortProp = 'municipality_name';
            $sortOrder = 'asc';
        }

        if($request['showDeleted'] == 'true'){
            $municipalities = Municipalities::where('province_id', $province_id)->onlyTrashed()->
                where(function($query) use ($request){
                    $query->where('municipality_name','like','%'.$request['keywords'].'%');
                })
                ->whereIn('is_customer_active', $customer_filter)
                ->whereIn('is_supplier_active', $supplier_filter)
                ->orderBy($sortProp, $sortOrder)
                ->paginate($request['take']);
        }
        else{
            $municipalities = Municipalities::where('province_id', $province_id)
                ->where(function($query) use ($request){
                    $query->where('municipality_name','like','%'.$request['keywords'].'%');
                })
                ->whereIn('is_customer_active', $customer_filter)
                ->whereIn('is_supplier_active', $supplier_filter)
                ->orderBy($sortProp, $sortOrder)
                ->paginate($request['take']);
        }

        $table_municipalities = '<table width="50%">';
        $table_municipalities .= '
            <tr bgcolor="#D5D8DC">
                <th>Type Codes Name</th>
            </tr>
        ';

        foreach($municipalities as $municipality){
            $table_municipalities .= '<tr>';
            $table_municipalities .= '<td>'.$municipality->municipality_name.'</td>';
            $table_municipalities .= '</tr>';
        }

        $table_municipalities .= '</table>';   
        
        $table_contents = '
            <h3>Province</h3>
            <table width="100%">
                <tr>
                    <td width="25%">Province Name</td>
                    <td>'.$province->province_name.'</td>
                </tr>
            </table>

            <br>
            
            <h3>Municipalities</h3>
            '.$table_municipalities.'
        '; 

        $html = '
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <meta http-equiv="X-UA-Compatible" content="ie=edge">
                <title>Municipalities</title>
            </head>
            <style>
                p {
                    font-size: 12px;
                }
                table, th, td {
                    border: 1px solid black;
                    border-collapse: collapse;
                }
                th {
                    padding: 5px;
                    text-align: center;
                    font-size: 12px;
                }
                td {
                    padding: 5px;
                    font-size: 12px;
                }
                body {
                    font-family: Arial, Helvetica, sans-serif;
                }
                .logo {
                    width: 250px;
                }
                #footer {
                    position: fixed;
                    left: 0;
                    right: 0;
                    color: #aaa;
                    font-size: 0.9em;
                }
                #header {
                    top: 0;
                    border-bottom: 0.1pt solid #aaa;
                }
                #footer {
                    bottom: 0;
                    border-top: 0.1pt solid #aaa;
                }
                .page-number:before {
                    content: "Page " counter(page);
                }
            </style>
            <body>
                <img src="./images/logo.png" alt="Adamay Logo" class="logo">
                <h2 style="text-align: center">Location Information</h2>
                '.$table_contents.'
                <div id="footer">
                    <div class="page-number"></div>
                </div>
            </body>
            </html>
        ';

        $pdf = PDF::loadHTML($html);
        $pdf->setPaper('A4', 'portrait');
        return $pdf->stream('Adamay_'.$filename.'.pdf');
    }

    public function exportPDFmaterialType(Request $request){
        $show_deleted = $request['showDeleted'];
        $page_name = 'Type Codes';
        

        $filename = $page_name.'_'.date('Y:m:d H:i:s');

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
            $type_codes = TypeCode::onlyTrashed()
                ->where(function($query) use ($request){
                    $query->where('type_code','like','%'.$request['search'].'%')
                    ->orWhere('type_code_description','like','%'.$request['search'].'%')
                    ->orWhere('is_rm','like','%'.$request['search'].'%')
                    ->orWhere('is_sc','like','%'.$request['search'].'%')
                    ->orWhere('is_fg','like','%'.$request['search'].'%');
                })
                ->orderBy($sortProp, $sortOrder)
                ->paginate($request['take']);
        }
        else{
            $type_codes = TypeCode::where(function($query) use ($request){
                $query->where('type_code','like','%'.$request['search'].'%')
                ->orWhere('type_code_description','like','%'.$request['search'].'%')
                ->orWhere('is_rm','like','%'.$request['search'].'%')
                ->orWhere('is_sc','like','%'.$request['search'].'%')
                ->orWhere('is_fg','like','%'.$request['search'].'%');
                })
                ->orderBy($sortProp, $sortOrder)
                ->paginate($request['take']);
        }

        $category = '';
       
        $table_contents = '';
        $table_header = '';
        $is_fg = '';
        $is_rm = '';
        $is_sc = '';

            foreach($type_codes as $row){
            
            if($row->is_rm == 1) $is_rm = '/';
            else 'X';
            if($row->is_sc == 1) $is_sc = '/';
            else 'X';
            if($row->is_fg == 1) $is_fg = 'X';
            else '/';

            $table_contents .='
            <tr>
                <td>'.$row->type_code.'</td>
                <td>'.$row->type_code_description.'</td>
                <td>'.$is_rm.'</td>
                <td>'.$is_sc.'</td>
                <td>'.$is_fg.'</td>
            </tr>
 
            ';
        }
        
        $table_header .='
                    <tr>
                        <th>Type Code</th>
                        <th>Description</th>
                        <th>RM</th>
                        <th>SC</th>
                        <th>FG</th>
                    </tr>
                ';
        
        $html = '
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <meta http-equiv="X-UA-Compatible" content="ie=edge">
                <title></title>
            </head>
            <style>
                p {
                    font-size: 12px;
                }
                table, th, td {
                    border: 1px solid black;
                    border-collapse: collapse;
                }
                th {
                    padding: 5px;
                    text-align: center;
                    font-size: 14px;
                }
                td {
                    padding: 5px;
                    font-size: 12px;
                }
                table {
                    width: 100%;
                }
                body {
                    font-family: Arial, Helvetica, sans-serif;
                }
                .logo {
                    width: 250px;
                }
                #footer {
                    position: fixed;
                    left: 0;
                    right: 0;
                    color: #aaa;
                    font-size: 0.9em;
                }
                #header {
                    top: 0;
                    border-bottom: 0.1pt solid #aaa;
                }
                #footer {
                    bottom: 0;
                    border-top: 0.1pt solid #aaa;
                }
                .page-number:before {
                    content: "Page " counter(page);
                }
            </style>
            <body>
                <img src="./images/logo.png" alt="Adamay Logo" class="logo">
                <h2 style="text-align: center">Material Type Code List</h2>
                <h5 style="text-align: center">'.$page_name.' </h5>

                <table>
                    '.$table_header.'
                    '.$table_contents.' 
                </table>
                <div id="footer">
                    <div class="page-number"></div>
                </div>
            </body>
            </html>
            <script type="text/php">
                if (isset($pdf)) {
                    $text = "page {PAGE_NUM} / {PAGE_COUNT}";
                    $size = 10;
                    $font = $fontMetrics->getFont("Arial");
                    $width = $fontMetrics->get_text_width($text, $font, $size) / 2;
                    $x = ($pdf->get_width() - $width) / 2;
                    $y = $pdf->get_height() - 35;
                    $pdf->page_text($x, $y, $text, $font, $size);
                }
            </script>
        ';

        $pdf = PDF::loadHTML($html);
        $pdf->setPaper('A4', 'landscape');
        return $pdf->stream('Adamay_'.$filename.'.pdf');
    }
    
    public function exportPDFmaterialClassifications(Request $request){
        $show_deleted = $request['showDeleted'];
        $type_code = $request['typeCode'];
        $type_code_descriptions = $request['typeDescription'];
        $id = $request['typeCodeId'];

 
        $page_name = 'Material Classifications'.'-'.$type_code;
        

        $filename = $page_name.'_'.date('Y:m:d H:i:s');

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
            $classifications = MaterialClassifications::onlyTrashed()->where('type_code_id', $id)
                ->where(function($query) use ($request){
                    $query->where('material_classification_name','like','%'.$request['search'].'%')
                    ->orWhere('type_code_id','like','%'.$request['search'].'%');
                })
                ->orderBy($sortProp, $sortOrder)
                ->paginate($request['take']);
        }
        else{
            $classifications = MaterialClassifications::where('type_code_id', $id)->where(function($query) use ($request){
                    $query->where('material_classification_name','like','%'.$request['search'].'%')
                    ->orWhere('type_code_id','like','%'.$request['search'].'%');
                })
                ->orderBy($sortProp, $sortOrder)
                ->paginate($request['take']);
        }
        
        $category = '';
       
        $table_contents = '';
        $table_header = '';

        foreach($classifications as $row){
        $table_contents .='
        <tr>
            <td>'.$row->material_classification_name.'</td>
        </tr>

        ';
        }
        
        $table_header .='
                    <tr>
                        <th>'.$type_code.'</th>
                    </tr>
                ';
        
        $html = '
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <meta http-equiv="X-UA-Compatible" content="ie=edge">
                <title></title>
            </head>
            <style>
                p {
                    font-size: 12px;
                }
                table, th, td {
                    border: 1px solid black;
                    border-collapse: collapse;
                }
                th {
                    padding: 5px;
                    text-align: center;
                    font-size: 14px;
                }
                td {
                    padding: 5px;
                    font-size: 12px;
                }
                table {
                    width: 100%;
                }
                body {
                    font-family: Arial, Helvetica, sans-serif;
                }
                .logo {
                    width: 250px;
                }
                #footer {
                    position: fixed;
                    left: 0;
                    right: 0;
                    color: #aaa;
                    font-size: 0.9em;
                }
                #header {
                    top: 0;
                    border-bottom: 0.1pt solid #aaa;
                }
                #footer {
                    bottom: 0;
                    border-top: 0.1pt solid #aaa;
                }
                .page-number:before {
                    content: "Page " counter(page);
                }
            </style>
            <body>
                <img src="./images/logo.png" alt="Adamay Logo" class="logo">
                <h2 style="text-align: center">Material Type Code List</h2>
                <h5 style="text-align: center">'.$page_name.' </h5>

                <h6 style="text-align: left">Type Code:'.$type_code.'      '.'Descriptions:'.$type_code_descriptions.' </h6>
                <table>
                    '.$table_header.'
                    '.$table_contents.' 
                </table>
                <div id="footer">
                    <div class="page-number"></div>
                </div>
            </body>
            </html>
            <script type="text/php">
                if (isset($pdf)) {
                    $text = "page {PAGE_NUM} / {PAGE_COUNT}";
                    $size = 10;
                    $font = $fontMetrics->getFont("Arial");
                    $width = $fontMetrics->get_text_width($text, $font, $size) / 2;
                    $x = ($pdf->get_width() - $width) / 2;
                    $y = $pdf->get_height() - 35;
                    $pdf->page_text($x, $y, $text, $font, $size);
                }
            </script>
        ';

        $pdf = PDF::loadHTML($html);
        $pdf->setPaper('A4', 'landscape');
        return $pdf->stream('Adamay_'.$filename.'.pdf');
    }
    
    public function exportPDFOrders(Request $request){
        $filename = 'Orders_'.date('Y:m:d H:i:s');

        $sortProp = $request['sortProp'];
        $sortOrder = $request['sortOrder'];
        if($sortProp != 'undefined' && $sortOrder != 'undefined'){
            if($sortOrder === 'descending') $sortOrder = 'desc';
            else $sortOrder = 'asc';
        }
        else{
            $sortProp = 'order_item_code';
            $sortOrder = 'asc';
        }

        if($request['showDeleted'] == 'true'){
            $orders =  Orders::onlyTrashed()
                ->where(function($query) use ($request){
                    $query->where('order_item_code','like','%'.$request['keywords'].'%')
                        ->orWhere('order_item_description','like','%'.$request['keywords'].'%')
                        ->orWhere('order_part_number','like','%'.$request['keywords'].'%');
                })
                ->orderBy($sortProp, $sortOrder)
                ->get();
            $search_filter_class = 'block';
        }
        else{
            $orders = Orders::where(function($query) use ($request){
                $query->where('order_item_code','like','%'.$request['keywords'].'%')
                    ->orWhere('order_item_description','like','%'.$request['keywords'].'%')
                    ->orWhere('order_part_number','like','%'.$request['keywords'].'%');
            })
                ->orderBy($sortProp, $sortOrder)
                ->get();
            $search_filter_class = 'none';
        }

        if($request['sortProp'] || $request['sortOrder']) $sort_class = 'block';
        else $sort_class = 'none';

        if(strlen($request['keywords']) > 0) $search_filter_class = 'block';
        else $search_filter_class = 'none';

        $table_contents = '';                
        foreach($orders as $order){
            $order_categories = [
                [ 'id' => 1, 'category_name' => 'Manual' ],
                [ 'id' => 2, 'category_name' => 'Manual (Sheeted)' ],
                [ 'id' => 3, 'category_name' => 'Instruction Sheet' ],
                [ 'id' => 4, 'category_name' => 'Label (Blank)' ],
                [ 'id' => 5, 'category_name' => 'Label (Rolled)' ],
                [ 'id' => 6, 'category_name' => 'Label (Sheeted)' ],
                [ 'id' => 7, 'category_name' => 'Leaflet' ],
                [ 'id' => 8, 'category_name' => 'Continuous Form' ],
                [ 'id' => 9, 'category_name' => 'Envelope (Big)' ],
                [ 'id' => 10, 'category_name' => 'Envelope (Regular)' ],
                [ 'id' => 11, 'category_name' => 'Envelope (Standard)' ],
                [ 'id' => 12, 'category_name' => 'Stretch Film' ],
                [ 'id' => 13, 'category_name' => 'Sato' ],
            ];

            for ($i = 0; $i < count($order_categories) ; $i++) { 
                if($order_categories[$i]['id'] == $order->order_category) $order_category = $order_categories[$i]['category_name'];
            }

            $type_code = TypeCode::where('id', $order->order_item_type_code)->first();
            $classification = MaterialClassifications::where('id', $order->order_item_classification)->first();

            $table_contents .= '
                <tr>
                    <td>'.$order->order_item_code.'</td>
                    <td>'.$order->order_item_description.'</td>
                    <td>'.$order->order_part_number.'</td>
                    <td>'.$order_category.'</td>
                    <td>'.$type_code->type_code.' - '.$type_code->type_code_description.'</td>
                    <td>'.$classification->material_classification_name.'</td>
                </tr>
            ';
        }

        $html = '
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <meta http-equiv="X-UA-Compatible" content="ie=edge">
                <title>Orders</title>
            </head>
            <style>
                p {
                    font-size: 12px;
                }
                table, th, td {
                    border: 1px solid black;
                    border-collapse: collapse;
                }
                th {
                    padding: 5px;
                    text-align: center;
                    font-size: 14px;
                }
                td {
                    padding: 5px;
                    font-size: 12px;
                }
                table {
                    width: 100%;
                }
                body {
                    font-family: Arial, Helvetica, sans-serif;
                }
                .logo {
                    width: 250px;
                }
                #footer {
                    position: fixed;
                    left: 0;
                    right: 0;
                    color: #aaa;
                    font-size: 0.9em;
                }
                #header {
                    top: 0;
                    border-bottom: 0.1pt solid #aaa;
                }
                #footer {
                    bottom: 0;
                    border-top: 0.1pt solid #aaa;
                }
                .page-number:before {
                    content: "Page " counter(page);
                }
            </style>
            <body>
                <img src="./images/logo.png" alt="Adamay Logo" class="logo">
                <h2 style="text-align: center">Orders</h2>
                <p style="display: '.$search_filter_class.'">Search Filter: "'.$request['keywords'].'"</p>
                <p style="display: '.$sort_class.'">Sort by: '.$sortProp.", ".$sortOrder.'</p>
                <table>
                    <tr>
                        <th>Order Code</th>
                        <th>Order Description</th>
                        <th>Part Number</th>
                        <th>Category</th>
                        <th>Type Code</th>
                        <th>Classification</th>
                    </tr>
                    '.$table_contents.'
                </table>
                <div id="footer">
                    <div class="page-number"></div>
                </div>
            </body>
            </html>
            <script type="text/php">
                if (isset($pdf)) {
                    $text = "page {PAGE_NUM} / {PAGE_COUNT}";
                    $size = 10;
                    $font = $fontMetrics->getFont("Arial");
                    $width = $fontMetrics->get_text_width($text, $font, $size) / 2;
                    $x = ($pdf->get_width() - $width) / 2;
                    $y = $pdf->get_height() - 35;
                    $pdf->page_text($x, $y, $text, $font, $size);
                }
            </script>
        ';

        $pdf = PDF::loadHTML($html);
        $pdf->setPaper('A4', 'landscape');
        return $pdf->stream('Adamay_'.$filename.'.pdf');
    }

    public function exportPDFOrder(Request $request){
        $order_id = $request['order_id'];
        $order = Orders::where('id', $order_id)->first();
        $filename = 'Order_'.$order->order_item_code.'_'.date('Y:m:d H:i:s');

        $order_categories = [
            [ 'id' => 1, 'category_name' => 'Manual' ],
            [ 'id' => 2, 'category_name' => 'Manual (Sheeted)' ],
            [ 'id' => 3, 'category_name' => 'Instruction Sheet' ],
            [ 'id' => 4, 'category_name' => 'Label (Blank)' ],
            [ 'id' => 5, 'category_name' => 'Label (Rolled)' ],
            [ 'id' => 6, 'category_name' => 'Label (Sheeted)' ],
            [ 'id' => 7, 'category_name' => 'Leaflet' ],
            [ 'id' => 8, 'category_name' => 'Continuous Form' ],
            [ 'id' => 9, 'category_name' => 'Envelope (Big)' ],
            [ 'id' => 10, 'category_name' => 'Envelope (Regular)' ],
            [ 'id' => 11, 'category_name' => 'Envelope (Standard)' ],
            [ 'id' => 12, 'category_name' => 'Stretch Film' ],
            [ 'id' => 13, 'category_name' => 'Sato' ],
        ];

        $machines = [
            [ 'id' => 1, 'machine_name' => 'Machine 1' ],
            [ 'id' => 2, 'machine_name' => 'Machine 2' ],
            [ 'id' => 3, 'machine_name' => 'Machine 3' ],
            [ 'id' => 4, 'machine_name' => 'Machine 4' ],
            [ 'id' => 5, 'machine_name' => 'Machine 5' ],
        ];

        for ($i = 0; $i < count($order_categories) ; $i++) { 
            if($order_categories[$i]['id'] == $order->order_category) $order_category = $order_categories[$i]['category_name'];
        }

        $type_code = TypeCode::where('id', $order->order_item_type_code)->first();
        $classification = MaterialClassifications::where('id', $order->order_item_classification)->first();

        $primary_materials = OrderMaterialsPrimary::select('order_materials_primary.*', 'materials.material_description as material_name')
            ->where('order_materials_primary.order_id', $order_id)
            ->leftJoin('materials', 'materials.id', 'order_materials_primary.material_id')
            ->get();

        $secondary_materials = OrderMaterialsSecondary::select('order_materials_secondary.*', 'materials.material_description as material_name')
            ->where('order_materials_secondary.order_id', $order_id)
            ->leftJoin('materials', 'materials.id', 'order_materials_secondary.material_id')
            ->get();

        $subcontractor_material = OrderMaterialsSubcon::select('order_materials_subcon.*', 'sc_materials.sc_material_name as material_name')
            ->where('order_materials_subcon.order_id', $order_id)
            ->leftJoin('sc_materials', 'sc_materials.id', 'order_materials_subcon.material_id')
            ->first();

        $printings = OrderPrints::select('order_prints.*', 'materials.material_description as material_name')
            ->where('order_prints.order_id', $order_id)
            ->leftJoin('materials', 'materials.id', 'order_prints.material_id')
            ->get();

        $processings = OrderProcess::select('order_processes.*', 'process.name as process_name')
            ->where('order_processes.order_id', $order_id)
            ->leftJoin('process', 'process.id', 'order_processes.order_process_id')
            ->get();
        $process_subcon = OrderProcessSubcon::where('order_id', $order_id)->get();

        $primary_materials_table = '';
        foreach($primary_materials as $primary_material){ 
            $primary_materials_table .= '
            <tr>
                <td>'.$primary_material->material_name.'</td>
                <td>'.$primary_material->order_material_length.' '.$primary_material->order_material_length_unit.' x '.$primary_material->order_material_width.' '.$primary_material->order_material_width_unit.'</td>
                <td>'.$primary_material->order_material_marginlr.' '.$primary_material->order_material_marginlr_unit.' x '.$primary_material->order_material_margintb.' '.$primary_material->order_material_margintb_unit.'</td>
                <td>
                    Pages: '.$primary_material->order_pages.' <br>
                    Rolls: '.$primary_material->order_rolls.' <br>
                    Cut Size: '.$primary_material->order_material_cut.' <br>
                    Slit: '.$primary_material->order_material_slit.' <br>
                    Cylinder: '.$primary_material->order_material_cylinder.' <br>
                    Outs: '.$primary_material->order_material_outs.' <br>
                    Overlap: '.$primary_material->order_material_layout_overlap.' '.$primary_material->order_material_layout_overlap_unit.'
                </td>
            </tr>';
        }

        $secondary_materials_table = '';
        foreach($secondary_materials as $secondary_material){ 
            $secondary_materials_table .= '
            <tr>
                <td>'.$secondary_material->material_name.'</td>
                <td>'.$secondary_material->order_material_length.' '.$secondary_material->order_material_length_unit.' x '.$secondary_material->order_material_width.' '.$secondary_material->order_material_width_unit.'</td>
            </tr>';
        }

        $subcontractor_material_table = '
            <tr>
                <td>'.$subcontractor_material->material_name.'</td>
                <td>'.$subcontractor_material->order_material_length.' '.$subcontractor_material->order_material_length_unit.' x '.$subcontractor_material->order_material_width.' '.$subcontractor_material->order_material_width_unit.'</td>
            </tr>';

        $printings_table = '';
        foreach($printings as $printing){
            $machine_name = '';
            for ($i = 0; $i < count($machines); $i++) { 
                if($machines[$i]['id'] == $printing->order_print_machine_id) $machine_name = $machines[$i]['machine_name'];
            }

            $work_in_turn = '';
            if($printing->order_print_wit == 1) $work_in_turn = 'Yes';
            else $work_in_turn = 'No';

            $printings_table .= '
            <tr>
                <td>'.$printing->order_print_item.'</td>
                <td>'.$printing->material_name.'</td>
                <td>
                    Regular: '.$printing->order_print_front_regular.' <br>
                    Special: '.$printing->order_print_front_special.' <br>
                    Total: '.$printing->order_print_front.'
                </td>
                <td>
                    Regular: '.$printing->order_print_back_regular.' <br>
                    Special: '.$printing->order_print_back_special.' <br>
                    Total: '.$printing->order_print_back.'
                </td>
                <td>
                    Machine: '.$machine_name.' <br>
                    Cut Size: '.$printing->order_print_cutsize.' <br>
                    Outs: '.$printing->order_print_outs.' <br>
                    Pages: '.$printing->order_print_pages.' <br>
                    Work-In-Turn: '.$work_in_turn.'
                </td>
            </tr>';
        }

        $processings_table = '';
        foreach($processings as $process){
            $processings_table .= '
            <tr>
                <td>'.$process->process_name.'</td>
                <td>'.$process->order_process_cuts.'</td>
                <td>'.$process->order_process_folds.'</td>
                <td>'.$process->order_process_outs.'</td>
                <td>'.$process->order_process_paper_size.'</td>
                <td>'.$process->order_process_signature.'</td>
                <td>'.$process->order_process_gsm.'</td>
            </tr>';
        }

        $process_subcon_table = '';
        foreach($process_subcon as $subcon){
            $process_subcon_table .= '
                <tr>
                    <td>'.$subcon->order_process_subcon.'</td>
                    <td>'.$subcon->order_process_subcon_description.'</td>
                   
                </tr>';
        }

        $table_contents = '
            <h3>Order Details</h3>
            <table>
                <tr>
                    <td width="25%">Item Code</td>
                    <td>'.$order->order_item_code.'</td>
                </tr>
                <tr>
                    <td>Description</td>
                    <td>'.$order->order_item_description.'</td>
                </tr>
                <tr>
                    <td>Part Number</td>
                    <td>'.$order->order_part_number.'</td>
                </tr>
                <tr>
                    <td>Category</td>
                    <td>'.$order_category.'</td>
                </tr>
                <tr>
                    <td>Type Code</td>
                    <td>'.$type_code->type_code.' - '.$type_code->type_code_description.'</td>
                </tr>
                <tr>
                    <td>Classification</td>
                    <td>'.$classification->material_classification_name.'</td>
                </tr>
                <tr>
                    <td>Dimension</td>
                    <td>'.$order->order_length.' '.$order->order_length_unit.' x '.$order->order_width.' '.$order->order_width_unit.'</td>
                </tr>
                <tr>
                    <td>Gap</td>
                    <td>'.$order->order_gaplr.' '.$order->order_gaplr_unit.' x '.$order->order_gaptb.' '.$order->order_gaptb_unit.'</td>
                </tr>
                <tr>
                    <td>Remarks</td>
                    <td>'.$order->order_remarks.'</td>
                </tr>
            </table>
            <br>
            <h3>Material Details</h3>
            <table>
                <tr>
                    <td colspan="4"><strong>Primary Materials</strong></td>
                </tr>
                <tr>
                    <th width="40%">Material Name</th>
                    <th width="20%">Dimension</th>
                    <th width="20%">Margin</th>
                    <th width="20%">More Info</th>
                </tr>
                '.$primary_materials_table.'
            </table>
            <br>
            <table>
                <tr>
                    <td colspan="2"><strong>Secondary Materials</strong></td>
                </tr>
                <tr>
                    <th width="60%">Material Name</th>
                    <th>Dimension</th>
                </tr>
                '.$secondary_materials_table.'
            </table>
            <br>
            <table>
                <tr>
                    <td colspan="2"><strong>Subcontractor Material</strong></td>
                </tr>
                <tr>
                    <th width="60%">Material Name</th>
                    <th>Dimension</th>
                </tr>
                '.$subcontractor_material_table.'
            </table>
            <br>
            <h3>Printing Details</h3>
            <table>
                <tr>
                    <th width="25%">Item Part</th>
                    <th width="25%">Material</th>
                    <th width="15%">Color Front</th>
                    <th width="15%">Color Back</th>
                    <th>More Info</th>
                </tr>
                '.$printings_table.'
            </table>
            <br>
            <h3>Processing Details</h3>
            <table>
                <tr>
                    <th width="25%">Process Name</th>
                    <th>Cuts</th>
                    <th>Folds</th>
                    <th>Outs</th>
                    <th>Paper size</th>
                    <th>Signature</th>
                    <th>Gsm</th>
                </tr>
                '.$processings_table.'
            </table>
            <br>
            <table>
            <tr>
                <td colspan="2"><strong>Subcontractor Processing Details</strong></td>
            </tr>
            <tr>
                <th width="40%">Process Name</th>
                <th>Description</th>
            </tr>
            '.$process_subcon_table.'
        </table>
        ';                

        $html = '
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <meta http-equiv="X-UA-Compatible" content="ie=edge">
                <title>Order - '.$order->order_item_code.'</title>
            </head>
            <style>
                p {
                    font-size: 12px;
                }
                table, th, td {
                    border: 1px solid black;
                    border-collapse: collapse;
                }
                table {
                    width: 100%;
                }
                th {
                    padding: 5px;
                    text-align: center;
                    font-size: 12px;
                }
                td {
                    padding: 5px;
                    font-size: 12px;
                }
                body {
                    font-family: Arial, Helvetica, sans-serif;
                }
                .logo {
                    width: 250px;
                }
                #footer {
                    position: fixed;
                    left: 0;
                    right: 0;
                    color: #aaa;
                    font-size: 0.9em;
                }
                #header {
                    top: 0;
                    border-bottom: 0.1pt solid #aaa;
                }
                #footer {
                    bottom: 0;
                    border-top: 0.1pt solid #aaa;
                }
                .page-number:before {
                    content: "Page " counter(page);
                }
            </style>
            <body>
                <img src="./images/logo.png" alt="Adamay Logo" class="logo">
                <h2 style="text-align: center">Order Information</h2>
                '.$table_contents.'
                <div id="footer">
                    <div class="page-number"></div>
                </div>
            </body>
            </html>
        ';

        $pdf = PDF::loadHTML($html);
        $pdf->setPaper('A4', 'portrait');
        return $pdf->stream('Adamay_'.$filename.'.pdf');
    }

    public function exportPDFComputationSheets(Request $request){
        $filename = 'CS_'.date('Y:m:d H:i:s');

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

        if($request['showDeleted'] == 'true'){
            $computation_sheets =  ComputationSheet::select('computation_sheet.id','computation_sheet.cs_control_number', 'computation_sheet.cs_version_number', 'computation_sheet.cs_total_amount', 'computation_sheet.cs_currency', 'orders.order_part_number', 'clients.client_short_name')
            ->onlyTrashed()
            ->where(function($query) use ($request){
                $query->where('computation_sheet.cs_control_number','like','%'.$request['search'].'%');
            })
            ->leftJoin('orders', 'orders.id', 'computation_sheet.client_order_id')
            ->leftJoin('clients', 'clients.id', 'computation_sheet.cs_client_id')
            ->orderBy($sortProp, $sortOrder)
            ->get();
            $search_filter_class = 'block';
        }
        else{
            $computation_sheets = ComputationSheet::select('computation_sheet.id','computation_sheet.cs_control_number', 'computation_sheet.cs_version_number', 'computation_sheet.cs_total_amount', 'computation_sheet.cs_currency', 'orders.order_part_number', 'clients.client_short_name')
            ->where(function($query) use ($request){
                $query->where('computation_sheet.cs_control_number','like','%'.$request['search'].'%');
            })
            ->leftJoin('orders', 'orders.id', 'computation_sheet.client_order_id')
            ->leftJoin('clients', 'clients.id', 'computation_sheet.cs_client_id')
            ->orderBy($sortProp, $sortOrder)
            ->get();
            $search_filter_class = 'none';
        }

        if($request['sortProp'] || $request['sortOrder']) $sort_class = 'block';
        else $sort_class = 'none';

        if(strlen($request['keywords']) > 0) $search_filter_class = 'block';
        else $search_filter_class = 'none';

        $table_contents = '';                
        foreach($computation_sheets as $computation_sheet){
            $table_contents .= '
                <tr>
                    <td>'.$computation_sheet->cs_control_number.'</td>
                    <td>'.'v'.$computation_sheet->cs_version_number.'</td>
                    <td>'.$computation_sheet->client_short_name.'</td>
                    <td>'.$computation_sheet->order_part_number.'</td>
                    <td style="text-align:right">'.$computation_sheet->cs_currency.' '.$computation_sheet->cs_total_amount.'</td>
                </tr>
            ';
        }

        $html = '
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <meta http-equiv="X-UA-Compatible" content="ie=edge">
                <title>Computation Sheets</title>
            </head>
            <style>
                p {
                    font-size: 12px;
                }
                table, th, td {
                    border: 1px solid black;
                    border-collapse: collapse;
                }
                th {
                    padding: 5px;
                    text-align: center;
                    font-size: 14px;
                }
                td {
                    padding: 5px;
                    font-size: 12px;
                }
                table {
                    width: 100%;
                }
                body {
                    font-family: Arial, Helvetica, sans-serif;
                }
                .logo {
                    width: 250px;
                }
                #footer {
                    position: fixed;
                    left: 0;
                    right: 0;
                    color: #aaa;
                    font-size: 0.9em;
                }
                #header {
                    top: 0;
                    border-bottom: 0.1pt solid #aaa;
                }
                #footer {
                    bottom: 0;
                    border-top: 0.1pt solid #aaa;
                }
                .page-number:before {
                    content: "Page " counter(page);
                }
            </style>
            <body>
                <img src="./images/logo.png" alt="Adamay Logo" class="logo">
                <h2 style="text-align: center">Computation Sheets</h2>
                <p style="display: '.$search_filter_class.'">Search Filter: "'.$request['keywords'].'"</p>
                <p style="display: '.$sort_class.'">Sort by: '.$sortProp.", ".$sortOrder.'</p>
                <table>
                    <tr>
                        <th>Control Number</th>
                        <th>Version</th>
                        <th>Customer</th>
                        <th>Part Number</th>
                        <th>Amount</th>
                    </tr>
                    '.$table_contents.'
                </table>
                <div id="footer">
                    <div class="page-number"></div>
                </div>
            </body>
            </html>
            <script type="text/php">
                if (isset($pdf)) {
                    $text = "page {PAGE_NUM} / {PAGE_COUNT}";
                    $size = 10;
                    $font = $fontMetrics->getFont("Arial");
                    $width = $fontMetrics->get_text_width($text, $font, $size) / 2;
                    $x = ($pdf->get_width() - $width) / 2;
                    $y = $pdf->get_height() - 35;
                    $pdf->page_text($x, $y, $text, $font, $size);
                }
            </script>
        ';

        $pdf = PDF::loadHTML($html);
        $pdf->setPaper('A4', 'landscape');
        return $pdf->stream('Adamay_'.$filename.'.pdf');
    }

    public function exportPDFComputationSheet(Request $request){
        $cs_id = $request['cs_id'];
        $cs = ComputationSheet::where('id', $cs_id)->first();
        $filename = 'ComputationSheet_'.$cs->cs_control_number.'_'.date('Y:m:d H:i:s');

        $cs_contents = '<table>';
        $cs_contents .= '
            <tr>
                <th colspan="8"><strong style="font-size: 15px">Computation Sheet</strong><br>Control No.: '.$cs->cs_control_number.'</th>
            </tr>
            <tr class="category">
                <th rowspan="2" width="24%">Materials</th>
                <th colspan="2" width="15%">Spec.</th>
                <th rowspan="2" width="8%">Actual</th>
                <th rowspan="2" width="8%">P.R.M.</th>
                <th rowspan="2" width="14%">Amount</th>
                <th colspan="2">Formula - Shinohara</th>
            </tr>
            <tr class="category">
                <th>L</th>
                <th>W</th>
                <th width="13%">@</th>
                <th width="13%">Net Cost</th>
            </tr>
        ';

        $order = Orders::where('id', $cs->client_order_id)->first();

        $quantity_units = [
            ['id' => 1, 'unit' => 'pcs'], 
            ['id' => 2, 'unit' => 'boxes'], 
            ['id' => 3, 'unit' => 'rolls'], 
            ['id' => 4, 'unit' => 'sheets']
        ];

        $cs_quantity_unit = '';
        for($i = 0; $i < count($quantity_units); $i++){
            if($quantity_units[$i]['id'] == $cs->cs_qty_unit) $cs_quantity_unit = $quantity_units[$i]['unit'];
        }

        $primary_materials = OrderMaterialsPrimary::with('supplier_materials.supplier', 'supplier_materials.tracking', 'material', 'order')
            ->select('order_materials_primary.*', 'materials.material_description as material_name')
            ->where('order_materials_primary.order_id', $cs->client_order_id)
            ->leftJoin('materials', 'materials.id', 'order_materials_primary.material_id')
            ->get();

        foreach($primary_materials as $primary_material){
            $cs_materials_primary = ComputationSheetMaterialsPrimary::where('cs_id', $cs_id)->where('cs_order_materials_primary_id', $primary_material->id)->first();

            $primary_material->cs_material_outs = $cs_materials_primary->cs_material_outs;
            $primary_material->cs_material_actual = $cs_materials_primary->cs_material_actual;
            $primary_material->cs_material_prm = $cs_materials_primary->cs_material_prm;
            $primary_material->total_material_quantity = $cs_materials_primary->cs_total_material_quantity;
            $primary_material->total_material_amount = number_format($cs_materials_primary->cs_subtotal_material_amount, 4);

            foreach($primary_material->supplier_materials as $supplier_material){
                $cs_materials_primary_supplier = ComputationSheetMaterialsPrimarySupplier::where('cs_id', $cs_id)->where('cs_materials_primary_id', $cs_materials_primary->id)->where('cs_materials_primary_supplier_id', $supplier_material->supplier_id)->first();

                $supplier_material->supplier_material_quantity = $cs_materials_primary_supplier->supplier_material_quantity;
                $supplier_material->supplier_material_amount = number_format($cs_materials_primary_supplier->supplier_material_amount, 4);
                $supplier_material->supplier_material_total = number_format($cs_materials_primary_supplier->supplier_material_total, 4);
                $supplier_material->is_selected = boolval($cs_materials_primary_supplier->is_selected);
            }
        }

        for($i = 0; $i < count($primary_materials); $i++){
            $material_quantity_unit = '';
            for($j = 0; $j < count($quantity_units); $j++){
                if($quantity_units[$j]['id'] == $primary_materials[$i]['supplier_materials'][0]['tracking'][0]['material_quantity_unit']) $material_quantity_unit = $quantity_units[$j]['unit'];
            }

            if($primary_materials[$i]['total_material_quantity'] > 0){
                $cs_contents .= '
                    <tr>
                        <td>'.$primary_materials[$i]['material_name'].'</td>
                        <td class="text-center">'.
                            $primary_materials[$i]['order_material_length'].' '.
                            $primary_materials[$i]['order_material_length_unit']
                        .'</td>
                        <td class="text-center">'.
                            $primary_materials[$i]['order_material_width'].' '.
                            $primary_materials[$i]['order_material_width_unit']
                        .'</td>
                        <td class="text-center">'.$primary_materials[$i]['cs_material_actual'].'</td>
                        <td class="text-center">'.$primary_materials[$i]['cs_material_prm'].'</td>
                        <td class="text-right">'.$cs->cs_currency.' '.$primary_materials[$i]['total_material_amount'] / $primary_materials[$i]['total_material_quantity'].'</td>
                        <td class="text-center">'.$primary_materials[$i]['total_material_quantity'].' '.$material_quantity_unit.'</td>
                        <td class="text-right">'.$cs->cs_currency.' '.$primary_materials[$i]['total_material_amount'].'</td>
                    </tr>
                ';
            }
        }

        $secondary_materials = OrderMaterialsSecondary::with('supplier_materials.supplier', 'supplier_materials.tracking', 'material', 'order')
            ->select('order_materials_secondary.*', 'materials.material_description as material_name')
            ->where('order_materials_secondary.order_id', $cs->client_order_id)
            ->leftJoin('materials', 'materials.id', 'order_materials_secondary.material_id')
            ->get();

        foreach($secondary_materials as $secondary_material){
            $cs_materials_secondary = ComputationSheetMaterialsSecondary::where('cs_id', $cs_id)->where('cs_order_materials_secondary_id', $secondary_material->id)->first();

            $secondary_material->total_material_quantity = $cs_materials_secondary->cs_total_material_quantity;
            $secondary_material->total_material_amount = number_format($cs_materials_secondary->cs_subtotal_material_amount, 4);

            foreach($secondary_material->supplier_materials as $supplier_material){
                $cs_materials_secondary_supplier = ComputationSheetMaterialsSecondarySupplier::where('cs_id', $cs_id)->where('cs_materials_secondary_id', $cs_materials_secondary->id)->where('cs_materials_secondary_supplier_id', $supplier_material->supplier_id)->first();

                $supplier_material->supplier_material_quantity = $cs_materials_secondary_supplier->supplier_material_quantity;
                $supplier_material->supplier_material_amount = number_format($cs_materials_secondary_supplier->supplier_material_amount, 4);
                $supplier_material->supplier_material_total = number_format($cs_materials_secondary_supplier->supplier_material_total, 4);
                $supplier_material->is_selected = boolval($cs_materials_secondary_supplier->is_selected);
            }
        }

        for($i = 0; $i < count($secondary_materials); $i++){
            $material_quantity_unit = '';
            for($j = 0; $j < count($quantity_units); $j++){
                if($quantity_units[$j]['id'] == $secondary_materials[$i]['supplier_materials'][0]['tracking'][0]['material_quantity_unit']) $material_quantity_unit = $quantity_units[$j]['unit'];
            }

            if($secondary_materials[$i]['total_material_quantity'] > 0){
                $cs_contents .= '
                    <tr>
                        <td>'.$secondary_materials[$i]['material_name'].'</td>
                        <td class="text-center">'.
                            $secondary_materials[$i]['order_material_length'].' '.
                            $secondary_materials[$i]['order_material_length_unit']
                        .'</td>
                        <td class="text-center">'.
                            $secondary_materials[$i]['order_material_width'].' '.
                            $secondary_materials[$i]['order_material_width_unit']
                        .'</td>
                        <td></td>
                        <td></td>
                        <td class="text-right">'.$cs->cs_currency.' '.$secondary_materials[$i]['total_material_amount'] / $secondary_materials[$i]['total_material_quantity'].'</td>
                        <td class="text-center">'.$secondary_materials[$i]['total_material_quantity'].' '.$material_quantity_unit.'</td>
                        <td class="text-right">'.$cs->cs_currency.' '.$secondary_materials[$i]['total_material_amount'].'</td>
                    </tr>
                ';
            }
        }

        $subcontractor_material = OrderMaterialsSubcon::with('supplier_sc_materials.supplier', 'supplier_sc_materials.tracking', 'sc_material', 'order')
            ->select('order_materials_subcon.*', 'sc_materials.sc_material_name as material_name')
            ->where('order_materials_subcon.order_id', $cs->client_order_id)
            ->leftJoin('sc_materials', 'sc_materials.id', 'order_materials_subcon.material_id')
            ->get();

        $cs_material_subcon = ComputationSheetMaterialsSubcon::where('cs_id', $cs_id)->where('cs_order_materials_subcon_id', $subcontractor_material[0]['id'])->first();

        $subcontractor_material[0]->total_sc_material_quantity = $cs_material_subcon->cs_total_material_quantity;
        $subcontractor_material[0]->total_sc_material_amount = number_format($cs_material_subcon->cs_subtotal_material_amount, 4);
        foreach($subcontractor_material[0]->supplier_sc_materials as $supplier_sc_material){
            $cs_material_subcon_supplier = ComputationSheetMaterialsSubconSupplier::where('cs_id', $cs_id)->where('cs_material_subcon_id', $cs_material_subcon->id)->where('cs_material_subcon_supplier_id', $supplier_sc_material->supplier_id)->first();

            $supplier_sc_material->supplier_sc_material_quantity = $cs_material_subcon_supplier->supplier_sc_material_quantity;
            $supplier_sc_material->supplier_sc_material_amount = number_format($cs_material_subcon_supplier->supplier_sc_material_amount, 4);
            $supplier_sc_material->supplier_sc_material_total = number_format($cs_material_subcon_supplier->supplier_sc_material_total, 4);
            $supplier_sc_material->is_selected = boolval($cs_material_subcon_supplier->is_selected);
        }

        $material_quantity_unit = '';
        for($j = 0; $j < count($quantity_units); $j++){
            if($quantity_units[$j]['id'] == $subcontractor_material[0]['supplier_sc_materials'][0]['tracking'][0]['sc_material_quantity_unit']) $sc_material_quantity_unit = $quantity_units[$j]['unit'];
        }
        for($i = 0; $i < count($subcontractor_material); $i++){
            if($subcontractor_material[$i]['total_sc_material_quantity'] > 0){
                $cs_contents .= '
                    <tr>
                        <td>'.$subcontractor_material[$i]['material_name'].'</td>
                        <td class="text-center">'.
                            $subcontractor_material[$i]['order_material_length'].' '.
                            $subcontractor_material[$i]['order_material_length_unit']
                        .'</td>
                        <td class="text-center">'.
                            $subcontractor_material[$i]['order_material_width'].' '.
                            $subcontractor_material[$i]['order_material_width_unit']
                        .'</td>
                        <td></td>
                        <td></td>
                        <td class="text-right">'.$cs->cs_currency.' '.$subcontractor_material[$i]['total_sc_material_amount'] / $subcontractor_material[$i]['total_sc_material_quantity'].'</td>
                        <td class="text-center">'.$subcontractor_material[$i]['total_sc_material_quantity'].' '.$sc_material_quantity_unit.'</td>
                        <td class="text-right">'.$cs->cs_currency.' '.$subcontractor_material[$i]['total_sc_material_amount'].'</td>
                    </tr>
                ';
            }
        }

        $cs_contents .= '
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td></td>
                <td colspan="4"></td>
                <td></td>
                <th class="text-left"><span style="font-size: 9px">Material Loss (10%)</span></th>
                <td class="text-right text-bold">'.$cs->cs_currency.' '.$cs->cs_material_loss.'</td>
            </tr>
            <tr>
                <td></td>
                <td colspan="4"></td>
                <td></td>
                <th class="text-left">Subtotal</th>
                <th class="text-right subtotal">'.$cs->cs_currency.' '.$cs->cs_material_total.'</th>
            </tr>
            <tr class="category">
                <th>Printing Description</th>
                <th>Outs</th>
                <th>Color</th>
                <th></th>
                <th></th>
                <th>Quantity</th>
                <th></th>
                <th></th>
            </tr>
        ';

        $prints = OrderPrints::with('supplier_materials.supplier', 'supplier_materials.tracking', 'material', 'order')
            ->select('order_prints.*', 'materials.material_description as material_name')
            ->where('order_prints.order_id', $cs->client_order_id)
            ->leftJoin('materials', 'materials.id', 'order_prints.material_id')
            ->get();

        foreach($prints as $print){
            $cs_printing = ComputationSheetPrinting::where('cs_id', $cs_id)->where('cs_order_prints_id', $print->id)->first();

            $print->total_material_quantity = $cs_printing->cs_total_material_quantity;
            $print->total_material_amount = number_format($cs_printing->cs_total_material_amount, 4);

            foreach($print->supplier_materials as $supplier_material){
                $cs_printing_supplier = ComputationSheetPrintingSupplier::where('cs_id', $cs_id)->where('cs_printing_id', $cs_printing->id)->where('cs_printing_supplier_id', $supplier_material->supplier_id)->first();

                $supplier_material->supplier_material_quantity = $cs_printing_supplier->supplier_material_quantity;
                $supplier_material->supplier_material_amount = number_format($cs_printing_supplier->supplier_material_amount, 4);
                $supplier_material->supplier_material_total = number_format($cs_printing_supplier->supplier_material_total, 4);
                $supplier_material->is_selected = boolval($cs_printing_supplier->is_selected);
            }
        }

        for($i = 0; $i < count($prints); $i++){
            $material_quantity_unit = '';
            for($j = 0; $j < count($quantity_units); $j++){
                if($quantity_units[$j]['id'] == $prints[$i]['supplier_materials'][0]['tracking'][0]['material_quantity_unit']) $material_quantity_unit = $quantity_units[$j]['unit'];
            }

            if($prints[$i]['total_material_quantity'] > 0){
                $cs_contents .= '
                    <tr>
                        <td>'.$prints[$i]['material_name'].'</td>
                        <td class="text-center">'.$prints[$i]['order_print_outs'].'</td>
                        <td class="text-center">'.
                            $prints[$i]['order_print_front'].' / '.
                            $prints[$i]['order_print_back']
                        .'</td>
                        <td></td>
                        <td></td>
                        <td class="text-center">'.$prints[$i]['total_material_quantity'].' '.$material_quantity_unit.'</td>
                        <td class="text-right">'.$cs->cs_currency.' '.$prints[$i]['total_material_amount'] / $prints[$i]['total_material_quantity'].'</td>
                        <td class="text-right">'.$cs->cs_currency.' '.$prints[$i]['total_material_amount'].'</td>
                    </tr>
                ';
            }
        }

        $cs_contents .= '
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td></td>
                <td colspan="4"></td>
                <td></td>
                <th class="text-left">Subtotal</th>
                <th class="text-right subtotal">'.$cs->cs_currency.' '.$cs->cs_printing_total.'</th>
            </tr>
            <tr class="category">
                <th>Processing Work</th>
                <th colspan="4">Description</th>
                <th>Quantity</th>
                <th></th>
                <th></th>
            </tr>
        ';

        $process_list = OrderProcess::select('order_processes.*', 'process.name as process_name')
            ->where('order_processes.order_id', $cs->client_order_id)
            ->leftJoin('process', 'process.id', 'order_processes.order_process_id')
            ->get();

        foreach($process_list as $proces){
            $cs_processes = ComputationSheetProcesses::where('cs_id', $cs_id)->where('cs_order_processes_id', $proces->id)->first();

            $proces->total_process_quantity = $cs_processes->cs_process_qty;
            $proces->total_process_amount = $cs_processes->cs_process_unit_amount;
            $proces->process_subtotal = $cs_processes->cs_process_total_amount;
        }

        for($i = 0; $i < count($process_list); $i++){
            if($process_list[$i]['total_process_quantity'] > 0){
                $cs_contents .= '
                    <tr>
                        <td>'.$process_list[$i]['process_name'].'</td>
                        <td colspan="4">'.$process_list[$i]['order_process_description'].'</td>
                        <td class="text-center">'.$process_list[$i]['total_process_quantity'].'</td>
                        <td class="text-right">'.$cs->cs_currency.' '.$process_list[$i]['total_process_amount'].'</td>
                        <td class="text-right">'.$cs->cs_currency.' '.$process_list[$i]['process_subtotal'].'</td>
                    </td>
                ';
            }
        }

        $process_subcons = OrderProcessSubcon::where('order_id', $cs->client_order_id)->get();

        foreach($process_subcons as $process_subcon){
            $cs_process_subcon = ComputationSheetProcessSubcon::where('cs_id', $cs_id)->where('cs_order_process_subcon_id', $process_subcon->id)->first();

            $process_subcon->total_process_quantity = $cs_process_subcon->cs_process_subcon_qty;
            $process_subcon->total_process_amount = $cs_process_subcon->cs_process_subcon_unit_amount;
            $process_subcon->process_subtotal = $cs_process_subcon->cs_process_subcon_total_amount;
        }

        for($i = 0; $i < count($process_subcons); $i++){
            if($process_subcons[$i]['total_process_quantity'] > 0){
                $cs_contents .= '
                    <tr>
                        <td>'.$process_subcons[$i]['order_process_subcon'].'</td>
                        <td colspan="4">'.$process_subcons[$i]['order_process_subcon_description'].'</td>
                        <td class="text-center">'.$process_subcons[$i]['total_process_quantity'].'</td>
                        <td class="text-right">'.$cs->cs_currency.' '.$process_subcons[$i]['total_process_amount'].'</td>
                        <td class="text-right">'.$cs->cs_currency.' '.$process_subcons[$i]['process_subtotal'].'</td>
                    </tr>
                ';
            }
        }

        $cs_contents .= '
            <tr>
                <td></td>
                <td colspan="4"></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td></td>
                <td colspan="4"></td>
                <td></td>
                <th class="text-left">Subtotal</th>
                <th class="text-right subtotal">'.$cs->cs_currency.' '.$cs->cs_process_total.'</th>
            </tr>
            <tr class="category">
                <th>Others</th>
                <th colspan="4">Description</th>
                <th>Quantity</th>
                <th></th>
                <th></th>
            </tr>
        ';

        $cs_misc = ComputationSheetMisc::where('cs_id', $cs_id)->first();

        if(boolval($cs_misc->cs_is_label)){
            $description = '';
            if(boolval($cs_misc->cs_is_package_label)) $description .= 'Package Label';
            if(boolval($cs_misc->cs_is_item_label)) $description .= ', Item Label';

            $cs_contents .= '
                <tr>
                    <td>Label</td>
                    <td colspan="4">'.$description.'</td>
                    <td class="text-center">'.$cs_misc->cs_qty_label.' '.$cs_quantity_unit.'</td>
                    <td class="text-right">'.$cs->cs_currency.' '.$cs_misc->cs_unit_amt_label.'</td>
                    <td class="text-right">'.$cs->cs_currency.' '.$cs_misc->cs_total_amt_label.'</td>
                </tr>
            ';
        }

        if(boolval($cs_misc->cs_is_package)){
            $description = '';
            if(boolval($cs_misc->cs_is_packaging_cartoon)) $description .= 'Cartoon';
            if(boolval($cs_misc->cs_is_packaging_others)) $description .= ', Others: '.$cs_misc->cs_packing_others_desc;
            if($cs_misc->cs_packaging_cartoon_qty_per_box > 0) $description .= ', Qty/box:'.$cs_misc->cs_packaging_cartoon_qty_per_box;

            $cs_contents .= '
                <tr>
                    <td>Packaging</td>
                    <td colspan="4">'.$description.'</td>
                    <td class="text-center">'.$cs_misc->cs_qty_package.' '.$cs_quantity_unit.'</td>
                    <td class="text-right">'.$cs->cs_currency.' '.$cs_misc->cs_unit_amt_package.'</td>
                    <td class="text-right">'.$cs->cs_currency.' '.$cs_misc->cs_total_amt_package.'</td>
                </tr>
            ';
        }

        if(boolval($cs_misc->cs_is_transpo)){
            $cs_contents .= '
                <tr>
                    <td>Transportation</td>
                    <td colspan="4">'.$cs_misc->cs_transpo_desc.'</td>
                    <td class="text-center">'.$cs_misc->cs_qty_transpo.' '.$cs_quantity_unit.'</td>
                    <td class="text-right">'.$cs->cs_currency.' '.$cs_misc->cs_unit_amt_transpo.'</td>
                    <td class="text-right">'.$cs->cs_currency.' '.$cs_misc->cs_total_amt_transpo.'</td>
                </tr>
            ';
        }

        if(boolval($cs_misc->cs_is_layout)){
            $cs_contents .= '
                <tr>
                    <td>Layout</td>
                    <td colspan="4">'.$cs_misc->cs_layout_desc.'</td>
                    <td class="text-center">'.$cs_misc->cs_qty_layout.' '.$cs_quantity_unit.'</td>
                    <td class="text-right">'.$cs->cs_currency.' '.$cs_misc->cs_unit_amt_layout.'</td>
                    <td class="text-right">'.$cs->cs_currency.' '.$cs_misc->cs_total_amt_layout.'</td>
                </tr>
            ';
        }

        if(boolval($cs_misc->cs_is_film)){
            $cs_contents .= '
                <tr>
                    <td>Film</td>
                    <td colspan="4">'.$cs_misc->cs_film_desc.'</td>
                    <td class="text-center">'.$cs_misc->cs_qty_film.' '.$cs_quantity_unit.'</td>
                    <td class="text-right">'.$cs->cs_currency.' '.$cs_misc->cs_unit_amt_film.'</td>
                    <td class="text-right">'.$cs->cs_currency.' '.$cs_misc->cs_total_amt_film.'</td>
                </tr>
            ';
        }

        if(boolval($cs_misc->cs_is_plate)){
            $cs_contents .= '
                <tr>
                    <td>Plate</td>
                    <td colspan="4">'.$cs_misc->cs_plate_desc.'</td>
                    <td class="text-center">'.$cs_misc->cs_qty_plate.' '.$cs_quantity_unit.'</td>
                    <td class="text-right">'.$cs->cs_currency.' '.$cs_misc->cs_unit_amt_plate.'</td>
                    <td class="text-right">'.$cs->cs_currency.' '.$cs_misc->cs_total_amt_plate.'</td>
                </tr>
            ';
        }

        if(boolval($cs_misc->cs_is_diecut)){
            $cs_contents .= '
                <tr>
                    <td>Die Cut</td>
                    <td colspan="4">'.$cs_misc->cs_diecut_desc.'</td>
                    <td class="text-center">'.$cs_misc->cs_qty_diecut.' '.$cs_quantity_unit.'</td>
                    <td class="text-right">'.$cs->cs_currency.' '.$cs_misc->cs_unit_amt_diecut.'</td>
                    <td class="text-right">'.$cs->cs_currency.' '.$cs_misc->cs_total_amt_diecut.'</td>
                </tr>
            ';
        }

        if(boolval($cs_misc->cs_is_peza)){
            $cs_contents .= '
                <tr>
                    <td>PEZA</td>
                    <td colspan="4">'.$cs_misc->cs_peza_desc.'</td>
                    <td class="text-center">'.$cs_misc->cs_qty_peza.' '.$cs_quantity_unit.'</td>
                    <td class="text-right">'.$cs->cs_currency.' '.$cs_misc->cs_unit_amt_peza.'</td>
                    <td class="text-right">'.$cs->cs_currency.' '.$cs_misc->cs_total_amt_peza.'</td>
                </tr>
            ';
        }

        $cs_misc_others = ComputationSheetMiscOthers::where('cs_misc_id', $cs_misc->id)->get();

        for($i = 0; $i < count($cs_misc_others); $i++){
            $cs_contents .= '
                <tr>
                    <td></td>
                    <td colspan="4">'.$cs_misc_others[$i]['other_misc_description'].'</td>
                    <td class="text-center">'.$cs_misc_others[$i]['other_misc_quantity'].' '.$cs_quantity_unit.'</td>
                    <td class="text-right">'.$cs->cs_currency.' '.$cs_misc_others[$i]['other_misc_price'].'</td>
                    <td class="text-right">'.$cs->cs_currency.' '.$cs_misc_others[$i]['other_misc_total'].'</td>
                </tr>
            ';
        }

        $cs_contents .= '
            <tr>
                <td></td>
                <td colspan="4"></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td></td>
                <td colspan="4"></td>
                <td></td>
                <th class="text-left">Subtotal</th>
                <th class="text-right subtotal">'.$cs->cs_currency.' '.$cs->cs_miscellaneous_total.'</th>
            </tr>

        ';

        $client_name = Client::where('id', $cs->cs_client_id)->first()->client_name;

        $quantity_units_single = [
            ['id' => 1, 'unit' => 'pc'], 
            ['id' => 2, 'unit' => 'box'], 
            ['id' => 3, 'unit' => 'roll'], 
            ['id' => 4, 'unit' => 'sheet']
        ];

        $cs_quantity_unit_single = '';
        for($i = 0; $i < count($quantity_units_single); $i++){
            if($quantity_units_single[$i]['id'] == $cs->cs_qty_unit) $cs_quantity_unit_single = $quantity_units_single[$i]['unit'];
        }

        $preparer_object = User::where('id', $cs->employee_id)->first();
        $preparer = $preparer_object->first_name.' '.$preparer_object->last_name.' ('.$preparer_object->employee_number.')';

        $exchange_rates = [];
        $exchange_rate_label = ['PHPToUSD', 'USDToPHP', 'JPYToUSD', 'JPYToPHP'];
        for ($i = 1; $i <= 4; $i++) {
            $exchange_rate = ExchangeRate::where('conversion_id', $i)->orderBy('id', 'desc')->first();
            $exchange_rates[$exchange_rate_label[$i - 1]] = $exchange_rate;
        }

        $cs_contents .= '
            <tr>
                <td>Client</td>
                <td colspan="4">'.$client_name.'</td>
                <td >Total Cost:</td>
                <td class="text-right" colspan="2" >'.$cs->cs_currency.' '.$cs->cs_total_amount.'</td>
            </tr>
            <tr>
                <td></td>
                <td colspan="4"></td>
                <td class="text-right" colspan="3">'.$cs->cs_currency.' '.number_format($cs->cs_total_amount / $cs->cs_qty, 4).' / '.$cs_quantity_unit_single.'</td>
            </tr>
            <tr>
                <td>Item</td>
                <td colspan="4">//Black Label</td>
                <td class="text-right" colspan="3"></td>
            </tr>
            <tr>
                <td></td>
                <td colspan="4"></td>
                <td class="text-right" colspan="3">PHP to USD: '.$exchange_rates["PHPToUSD"]["rate"].'</td>
            </tr>
            <tr>
                <td>Size</td>
                <td colspan="4">//153 mm (W) X 102 mm (L)</td>
                <td class="category text-center">Sales Price</td>
                <td class="category text-center" colspan="2">Formula</td>
            </tr>
            <tr>
                <td></td>
                <td colspan="4"></td>
                <td class="category text-center">25%</td>
                <td class="category text-right" colspan="2">//PHP 763.9837</td>
            </tr>
            <tr>
                <td>Quantity</td>
                <td colspan="4">//10 rolls</td>
                <td class="category text-center">20%</td>
                <td class="category text-right" colspan="2">//PHP 763.9837</td>
            </tr>
            <tr>
                <td></td>
                <td colspan="4"></td>
                <td class="category text-center">15%</td>
                <td class="category text-right" colspan="2">//PHP 763.9837</td>
            </tr>
            <tr>
                <td>Date</td>
                <td colspan="4">'.Carbon::parse($cs->created_at)->format("m/d/Y H:i").'</td>
                <td class="category text-center">10%</td>
                <td class="category text-right" colspan="2">//PHP 763.9837</td>
            </tr>
            <tr>
                <td>Prepared By:</td>
                <td colspan="4">'.$preparer.'</td>
                <td class="category text-center">//Others</td>
                <td class="category text-right" colspan="2">//PHP 763.9837</td>
            </tr>
            <tr>
                <td></td>
                <td colspan="4"></td>
                <td></td>
                <td colspan="2"></td>
            </tr>
        ';

        $cs_contents .= '</table>';

        $html = '
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <meta http-equiv="X-UA-Compatible" content="ie=edge">
                <title>Computation Sheet - '.$cs->cs_control_number.'</title>
            </head>
            <style>
                p {
                    font-size: 9px;
                }
                table, th, td {
                    border: 1px solid black;
                    border-collapse: collapse;
                }
                table {
                    width: 100%;
                }
                th {
                    padding: 5px;
                    text-align: center;
                    font-size: 10px;
                }
                td {
                    padding: 5px;
                    font-size: 9px;
                }
                body {
                    font-family: Arial, Helvetica, sans-serif;
                }
                .logo {
                    width: 250px;
                }
                #footer {
                    position: fixed;
                    left: 0;
                    right: 0;
                    color: #aaa;
                    font-size: 0.9em;
                }
                #header {
                    top: 0;
                    border-bottom: 0.1pt solid #aaa;
                }
                #footer {
                    bottom: 0;
                    border-top: 0.1pt solid #aaa;
                }
                .page-number:before {
                    content: "Page " counter(page);
                }
                .text-left {
                    text-align: left
                }
                .text-center {
                    text-align: center
                }
                .text-right {
                    text-align: right
                }
                .subtotal {
                    color: white;
                    background-color: black
                }
                .text-bold {
                    font-weight: bold
                }
                .category {
                    background-color: #d3d3d3
                }
            </style>
            <body>
                <img src="./images/logo.png" alt="Adamay Logo" class="logo">
                '.$cs_contents.'
                <div id="footer">
                    <div class="page-number"></div>
                </div>
            </body>
            </html>
        ';

        $pdf = PDF::loadHTML($html);
        $pdf->setPaper('A4', 'portrait');
        return $pdf->stream('Adamay_'.$filename.'.pdf');
    }

    public function exportPDFQuotations(Request $request){
        $filename = 'Quotations_'.date('Y:m:d H:i:s');

        $sortProp = $request['sortProp'];
        $sortOrder = $request['sortOrder'];
        if($sortProp != 'undefined' && $sortOrder != 'undefined'){
            if($sortOrder === 'descending') $sortOrder = 'desc';
            else $sortOrder = 'asc';
        }
        else{
            $sortProp = 'control_number';
            $sortOrder = 'asc';
        }

        if($request['showDeleted'] == 'true'){
            $quote = Quotation::onlyTrashed()
                ->leftJoin('clients', 'clients.id', 'quotation.client_id')
                ->leftJoin('client_contacts', 'client_contacts.id', 'quotation.client_pic_id')
                ->orderBy($sortProp, $sortOrder)
                ->paginate($request['take']);
        }
        else{
            $quote = Quotation::leftJoin('clients', 'clients.id', 'quotation.client_id')
                ->leftJoin('client_contacts', 'client_contacts.id', 'quotation.client_pic_id')
                ->orderBy($sortProp, $sortOrder)
                ->paginate($request['take']);
        }

        if($request['sortProp'] || $request['sortOrder']) $sort_class = 'block';
        else $sort_class = 'none';

        if(strlen($request['keywords']) > 0) $search_filter_class = 'block';
        else $search_filter_class = 'none';

        $table_contents = '';                
        foreach($quote as $row){
            $table_contents .= '
                <tr>
                    <td>'.$row->quotation_number.'</td>
                    <td>'.$row->client_name.'</td>
                    <td>'.$row->client_pic.'</td>
                    <td>'.$row->quotation_qty_unit.'</td>
                    <td>'.$row->order_qty_per_quotation_unit.'</td>
                    <td>'.$row->quotation_specs.'</td>
                    <td>'.$row->quotation_status.'</td>
                   
                </tr>
            ';
        }

        $html = '
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <meta http-equiv="X-UA-Compatible" content="ie=edge">
                <title>Quotations</title>
            </head>
            <style>
                p {
                    font-size: 12px;
                }
                table, th, td {
                    border: 1px solid black;
                    border-collapse: collapse;
                }
                th {
                    padding: 5px;
                    text-align: center;
                    font-size: 14px;
                }
                td {
                    padding: 5px;
                    font-size: 12px;
                }
                table {
                    width: 100%;
                }
                body {
                    font-family: Arial, Helvetica, sans-serif;
                }
                .logo {
                    width: 250px;
                }
                #footer {
                    position: fixed;
                    left: 0;
                    right: 0;
                    color: #aaa;
                    font-size: 0.9em;
                }
                #header {
                    top: 0;
                    border-bottom: 0.1pt solid #aaa;
                }
                #footer {
                    bottom: 0;
                    border-top: 0.1pt solid #aaa;
                }
                .page-number:before {
                    content: "Page " counter(page);
                }
            </style>
            <body>
                <img src="./images/logo.png" alt="Adamay Logo" class="logo">
                <h2 style="text-align: center">Quotation</h2>
                <p style="display: '.$search_filter_class.'">Search Filter: "'.$request['keywords'].'"</p>
                <p style="display: '.$sort_class.'">Sort by: '.$sortProp.", ".$sortOrder.'</p>
                <table>
                    <tr>
                        <th>Quotation Number</th>
                        <th>Client Name</th>
                        <th>PIC</th>
                        <th>Quantity Unit</th>
                        <th>Quantity Per Unit</th>
                        <th>Specs</th>
                        <th>Status</th>
                        
                    </tr>
                    '.$table_contents.'
                </table>
                <div id="footer">
                    <div class="page-number"></div>
                </div>
            </body>
            </html>
            <script type="text/php">
                if (isset($pdf)) {
                    $text = "page {PAGE_NUM} / {PAGE_COUNT}";
                    $size = 10;
                    $font = $fontMetrics->getFont("Arial");
                    $width = $fontMetrics->get_text_width($text, $font, $size) / 2;
                    $x = ($pdf->get_width() - $width) / 2;
                    $y = $pdf->get_height() - 35;
                    $pdf->page_text($x, $y, $text, $font, $size);
                }
            </script>
        ';

        $pdf = PDF::loadHTML($html);
        $pdf->setPaper('A4', 'landscape');
        return $pdf->stream('Adamay_'.$filename.'.pdf');
    }

    public function exportPDFQuotation(Request $request){

        $filename = 'Quotation_'.date('Y:m:d H:i:s');

        $quote = Quotation::where('id', $request['quotation_id'])->first();
        
        $client = Client::where('id', $quote['client_id'])->first();
        $quote_cs = QuotationCS::where('quotation_id', $request['quotation_id'])->get();
        $evaluation = QuotationEvaluation::where('quotation_id', $request['quotation_id'])->get();
        $cs = ComputationSheet::where('cs_client_id', $quote['client_id'])->where('cs_is_valid', 1)->get();

        $client_municipality = Municipalities::where('id', $client->client_municipality_id)->first();
        $client_province = Provinces::where('id', $client->client_province_id)->first();

        $cs_id = [];
        for($i = 0; $i < count($quote_cs); $i++){
           $cs_id = ComputationSheet::where('id', $quote_cs[$i]['cs_id'])->get();
        }
        // return $cs;
        if($cs_id && $cs){
            $order = Orders::where('id', $cs[0]['client_order_id'])->first();
        }

        $initial_charge = QuotationInitialCharge::where('quotation_id', $request['quotation_id'])->get();

        $checkers = QuotationEvaluation::select('quotation_evaluation.*', 'users.id as user_id', 'users.username', 'users.first_name', 'users.last_name')
        ->leftJoin('users', 'users.id', 'quotation_evaluation.evaluator_id')
        ->where('quotation_id', $request['quotation_id'])
        ->where('evaluation_type', 1)
        ->get();

        $approvers = QuotationEvaluation::select('quotation_evaluation.*', 'users.id as user_id', 'users.username', 'users.first_name', 'users.last_name')
        ->leftJoin('users', 'users.id', 'quotation_evaluation.evaluator_id')
        ->where('quotation_id', $request['quotation_id'])
        ->where('evaluation_type', 2)
        ->get();

        $unit = [
            ['id' => 1, 'unit' => 'pieces'], 
            ['id' => 2, 'unit' => 'boxes'], 
            ['id' => 3, 'unit' => 'rolls'], 
            ['id' => 4, 'unit' => 'sheets']
        ];
    
            for ($i=0; $i < count($unit) ; $i++) { 
               if($unit[$i]['id'] == $quote->quotation_qty_unit)
               $quote->quotation_qty_unit = $unit[$i]['unit'];
            }
        
        $computation_sheets_table = '';
        foreach($quote_cs as $row){
            $computation_sheets_table .= '
                <tr>
                    <td>'.$row->cs_control_no.'</td>
                    <td>'.$row->cs_qty.'</td>
                    <td>'.$row->cs_unit_amount.'</td>
                    <td>'.$row->cs_unit_amount_currency.' '.$row->cs_unit_amount_php.'</td>
                    
                </tr>
            ';
        }

        $initial_charge_table = '';
        foreach($initial_charge as $row){
            $initial_charge_table .= '
                <tr>
                    <td>'.$row->item_name.'</td>
                    <td>'.$row->item_qty.'</td>
                    <td>'.$row->item_unit_amt.'</td>
                    <td>'.$row->item_total_amt.'</td>
                    
                </tr>
            ';
        }

        $table_contents = '
        <h3>Customer Details</h3>
        <table>
            <tr>
                <td width="25%">Quotation Number</td>
                <td>'.$quote->quotation_number.'</td>
            </tr>
            <tr>
                <td width="25%">Customer Name</td>
                <td>'.$client->client_name.'</td>
            </tr>
            <tr>
                <td width="25%">Address</td>
                <td>'.$client->client_address.', '.$client_municipality->municipality_name.', '.$client_province->province_name.'</td>
            </tr>
            <tr>
                <td width="25%">Contact</td>
                <td>'.$client->client_contact.'</td>
            </tr>
        </table>
        <br>
        

        <h3>Computation Sheets</h3>
        <h4>Order Details</h4>
        <table>
            <tr>
                <td width="25%">Part Number</td>
                <td width="25%">'.$order->order_part_number.'</td>
                <td width="25%">Quantity Unit</td>
                <td width="25%">'.$quote->quotation_qty_unit.'</td>
            </tr>
            <tr>
                <td width="25%">Description</td>
                <td width="25%">'.$order->order_item_description.'</td>
                <td width="25%">Quantity Per Quotation Unit</td>
                <td width="25%">'.$quote->order_qty_per_quotation_unit.'</td>
            </tr>
        </table>
      
        <h4>Control Number List</h4>
        <table>
        <tr>
            <th>Control Number</th>
            <th>Quantity</th>
            <th>Unit Price</th>
            <th>Total</th>
        </tr>
            '.$computation_sheets_table.'
        </table>
        <br>
       

        <h3>Initial Charge</h3>
        <table>
        <tr>
            <th>Item Name</th>
            <th>Quantity</th>
            <th>Unit Price</th>
            <th>Total</th>
        </tr>
            '.$initial_charge_table.'
        </table>
        <br>

        ';   
     

        $html = '
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <meta http-equiv="X-UA-Compatible" content="ie=edge">
                <title>Quotation</title>
            </head>
            <style>
                p {
                    font-size: 12px;
                }
                table, th, td {
                    border: 1px solid black;
                    border-collapse: collapse;
                }
                th {
                    padding: 5px;
                    text-align: center;
                    font-size: 14px;
                }
                td {
                    padding: 5px;
                    font-size: 12px;
                }
                table {
                    width: 100%;
                }
                body {
                    font-family: Arial, Helvetica, sans-serif;
                }
                .logo {
                    width: 250px;
                }
                #footer {
                    position: fixed;
                    left: 0;
                    right: 0;
                    color: #aaa;
                    font-size: 0.9em;
                }
                #header {
                    top: 0;
                    border-bottom: 0.1pt solid #aaa;
                }
                #footer {
                    bottom: 0;
                    border-top: 0.1pt solid #aaa;
                }
                .page-number:before {
                    content: "Page " counter(page);
                }
            </style>
            <body>
                <img src="./images/logo.png" alt="Adamay Logo" class="logo">
                <h2 style="text-align: center">Quotation Information</h2>
                '.$table_contents.'
                <div id="footer">
                    <div class="page-number"></div>
                </div>
            </body>
            </html>
            <script type="text/php">
                if (isset($pdf)) {
                    $text = "page {PAGE_NUM} / {PAGE_COUNT}";
                    $size = 10;
                    $font = $fontMetrics->getFont("Arial");
                    $width = $fontMetrics->get_text_width($text, $font, $size) / 2;
                    $x = ($pdf->get_width() - $width) / 2;
                    $y = $pdf->get_height() - 35;
                    $pdf->page_text($x, $y, $text, $font, $size);
                }
            </script>
        ';

        $pdf = PDF::loadHTML($html);
        $pdf->setPaper('A4', 'portrait');
        return $pdf->stream('Adamay_'.$filename.'.pdf');
    }

    public function exportPDFSalesSheets(Request $request){
        $filename = 'SalesSheets_'.date('Y:m:d H:i:s');

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

        $sales_sheets = '';

        if($request['showDeleted'] == 'true'){
            $sales_sheets = SalesSheet::onlyTrashed()
                ->orderBy($sortProp, $sortOrder)
                ->paginate($request['take']);
        }
        else{
            $sales_sheets = SalesSheet::orderBy($sortProp, $sortOrder)
                ->paginate($request['take']);
        }

        if($request['sortProp'] || $request['sortOrder']) $sort_class = 'block';
        else $sort_class = 'none';

        if(strlen($request['keywords']) > 0) $search_filter_class = 'block';
        else $search_filter_class = 'none';

        $table_contents = '';

        foreach($sales_sheets as $sales_sheet){
            $table_contents .= '
                <tr>
                    <td>'.$sales_sheet->ss_reference_number.'</td>
                    <td>'.$sales_sheet->ss_reference_number.'</td>
                    <td>'.$sales_sheet->ss_reference_number.'</td>
                    <td>'.$sales_sheet->ss_reference_number.'</td>
                </tr>
            ';
        }

        $html = '
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <meta http-equiv="X-UA-Compatible" content="ie=edge">
                <title>Sales Sheets</title>
            </head>
            <style>
                p {
                    font-size: 12px;
                }
                table, th, td {
                    border: 1px solid black;
                    border-collapse: collapse;
                }
                th {
                    padding: 5px;
                    text-align: center;
                    font-size: 14px;
                }
                td {
                    padding: 5px;
                    font-size: 12px;
                }
                table {
                    width: 100%;
                }
                body {
                    font-family: Arial, Helvetica, sans-serif;
                }
                .logo {
                    width: 250px;
                }
                #footer {
                    position: fixed;
                    left: 0;
                    right: 0;
                    color: #aaa;
                    font-size: 0.9em;
                }
                #header {
                    top: 0;
                    border-bottom: 0.1pt solid #aaa;
                }
                #footer {
                    bottom: 0;
                    border-top: 0.1pt solid #aaa;
                }
                .page-number:before {
                    content: "Page " counter(page);
                }
            </style>
            <body>
                <img src="./images/logo.png" alt="Adamay Logo" class="logo">
                <h2 style="text-align: center">Sales Sheets</h2>
                <p style="display: '.$search_filter_class.'">Search Filter: "'.$request['keywords'].'"</p>
                <p style="display: '.$sort_class.'">Sort by: '.$sortProp.", ".$sortOrder.'</p>
                <table>
                    <tr>
                        <th>Reference No.</th>
                        <th>Column</th>
                        <th>Column</th>
                        <th>Column</th>
                    </tr>
                    '.$table_contents.'
                </table>
                <div id="footer">
                    <div class="page-number"></div>
                </div>
            </body>
            </html>
            <script type="text/php">
                if (isset($pdf)) {
                    $text = "page {PAGE_NUM} / {PAGE_COUNT}";
                    $size = 10;
                    $font = $fontMetrics->getFont("Arial");
                    $width = $fontMetrics->get_text_width($text, $font, $size) / 2;
                    $x = ($pdf->get_width() - $width) / 2;
                    $y = $pdf->get_height() - 35;
                    $pdf->page_text($x, $y, $text, $font, $size);
                }
            </script>
        ';

        $pdf = PDF::loadHTML($html);
        $pdf->setPaper('A4', 'landscape');
        return $pdf->stream('Adamay_'.$filename.'.pdf');
    }

    public function exportPDFSalesSheet(Request $request){
        $ss_id = $request['ss_id'];
        $ss = SalesSheet::where('id', $ss_id)->first();
        $filename = 'SalesSheet_'.$ss->ss_reference_number.'_'.date('Y:m:d H:i:s');

        $table_contents = '
            <table>
                <tr>
                    <td width="25%">Reference Number</td>
                    <td>'.$ss->ss_reference_number.'</td>
                </tr>
                <tr>
                    <td width="25%">Reference Number</td>
                    <td>'.$ss->ss_reference_number.'</td>
                </tr>
                <tr>
                    <td width="25%">Reference Number</td>
                    <td>'.$ss->ss_reference_number.'</td>
                </tr>
                <tr>
                    <td width="25%">Reference Number</td>
                    <td>'.$ss->ss_reference_number.'</td>
                </tr>
                <tr>
                    <td width="25%">Reference Number</td>
                    <td>'.$ss->ss_reference_number.'</td>
                </tr>
                <tr>
                    <td width="25%">Reference Number</td>
                    <td>'.$ss->ss_reference_number.'</td>
                </tr>
                <tr>
                    <td width="25%">Reference Number</td>
                    <td>'.$ss->ss_reference_number.'</td>
                </tr>
                <tr>
                    <td width="25%">Reference Number</td>
                    <td>'.$ss->ss_reference_number.'</td>
                </tr>
            </table>
        ';                

        $html = '
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <meta http-equiv="X-UA-Compatible" content="ie=edge">
                <title>Sales Sheet</title>
            </head>
            <style>
                p {
                    font-size: 12px;
                }
                table, th, td {
                    border: 1px solid black;
                    border-collapse: collapse;
                }
                th {
                    padding: 5px;
                    text-align: center;
                    font-size: 12px;
                }
                td {
                    padding: 5px;
                    font-size: 12px;
                }
                table {
                    width: 100%;
                }
                body {
                    font-family: Arial, Helvetica, sans-serif;
                }
                .logo {
                    width: 250px;
                }
                #footer {
                    position: fixed;
                    left: 0;
                    right: 0;
                    color: #aaa;
                    font-size: 0.9em;
                }
                #header {
                    top: 0;
                    border-bottom: 0.1pt solid #aaa;
                }
                #footer {
                    bottom: 0;
                    border-top: 0.1pt solid #aaa;
                }
                .page-number:before {
                    content: "Page " counter(page);
                }
            </style>
            <body>
                <img src="./images/logo.png" alt="Adamay Logo" class="logo">
                <h2 style="text-align: center">Sales Sheet Information</h2>
                '.$table_contents.'
                <div id="footer">
                    <div class="page-number"></div>
                </div>
            </body>
            </html>
        ';

        $pdf = PDF::loadHTML($html);
        $pdf->setPaper('A4', 'portrait');
        return $pdf->stream('Adamay_'.$filename.'.pdf');
    }

    public function exportPDFInvoices(Request $request){
        $filename = 'Invoices_'.date('Y:m:d H:i:s');

        $sortProp = $request['sortProp'];
        $sortOrder = $request['sortOrder'];

        if($sortProp != 'undefined' && $sortOrder != 'undefined'){
            if($sortOrder === 'descending') $sortOrder = 'desc';
            else $sortOrder = 'asc';
        }
        else{
            $sortProp = 'si_number';
            $sortOrder = 'asc';
        }

        $invoices = '';

        if($request['showDeleted'] == 'true'){
            $invoices = ClientPOInvoice::onlyTrashed()
                ->orderBy($sortProp, $sortOrder)
                ->paginate($request['take']);
        }
        else{
            $invoices = ClientPOInvoice::orderBy($sortProp, $sortOrder)
                ->paginate($request['take']);
        }

        if($request['sortProp'] || $request['sortOrder']) $sort_class = 'block';
        else $sort_class = 'none';

        if(strlen($request['keywords']) > 0) $search_filter_class = 'block';
        else $search_filter_class = 'none';

        $table_contents = '';

        foreach($invoices as $invoice){
            $table_contents .= '
                <tr>
                    <td>'.$invoice->si_number.'</td>
                    <td>'.$invoice->si_number.'</td>
                    <td>'.$invoice->si_number.'</td>
                    <td>'.$invoice->si_number.'</td>
                </tr>
            ';
        }

        $html = '
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <meta http-equiv="X-UA-Compatible" content="ie=edge">
                <title>Invoices</title>
            </head>
            <style>
                p {
                    font-size: 12px;
                }
                table, th, td {
                    border: 1px solid black;
                    border-collapse: collapse;
                }
                th {
                    padding: 5px;
                    text-align: center;
                    font-size: 14px;
                }
                td {
                    padding: 5px;
                    font-size: 12px;
                }
                table {
                    width: 100%;
                }
                body {
                    font-family: Arial, Helvetica, sans-serif;
                }
                .logo {
                    width: 250px;
                }
                #footer {
                    position: fixed;
                    left: 0;
                    right: 0;
                    color: #aaa;
                    font-size: 0.9em;
                }
                #header {
                    top: 0;
                    border-bottom: 0.1pt solid #aaa;
                }
                #footer {
                    bottom: 0;
                    border-top: 0.1pt solid #aaa;
                }
                .page-number:before {
                    content: "Page " counter(page);
                }
            </style>
            <body>
                <img src="./images/logo.png" alt="Adamay Logo" class="logo">
                <h2 style="text-align: center">Invoices</h2>
                <p style="display: '.$search_filter_class.'">Search Filter: "'.$request['keywords'].'"</p>
                <p style="display: '.$sort_class.'">Sort by: '.$sortProp.", ".$sortOrder.'</p>
                <table>
                    <tr>
                        <th>SI Number</th>
                        <th>Column</th>
                        <th>Column</th>
                        <th>Column</th>
                    </tr>
                    '.$table_contents.'
                </table>
                <div id="footer">
                    <div class="page-number"></div>
                </div>
            </body>
            </html>
            <script type="text/php">
                if (isset($pdf)) {
                    $text = "page {PAGE_NUM} / {PAGE_COUNT}";
                    $size = 10;
                    $font = $fontMetrics->getFont("Arial");
                    $width = $fontMetrics->get_text_width($text, $font, $size) / 2;
                    $x = ($pdf->get_width() - $width) / 2;
                    $y = $pdf->get_height() - 35;
                    $pdf->page_text($x, $y, $text, $font, $size);
                }
            </script>
        ';

        $pdf = PDF::loadHTML($html);
        $pdf->setPaper('A4', 'landscape');
        return $pdf->stream('Adamay_'.$filename.'.pdf');
    }

    public function exportPDFInvoice(Request $request){
        $invoice_id = $request['invoice_id'];
        $invoice = ClientPOInvoice::where('id', $invoice_id)->first();
        $filename = 'Invoice_'.$invoice->si_number.'_'.date('Y:m:d H:i:s');

        $table_contents = '
            <table>
                <tr>
                    <td width="25%">SI Number</td>
                    <td>'.$invoice->si_number.'</td>
                </tr>
                <tr>
                    <td width="25%">SI Number</td>
                    <td>'.$invoice->si_number.'</td>
                </tr>
                <tr>
                    <td width="25%">SI Number</td>
                    <td>'.$invoice->si_number.'</td>
                </tr>
                <tr>
                    <td width="25%">SI Number</td>
                    <td>'.$invoice->si_number.'</td>
                </tr>
                <tr>
                    <td width="25%">SI Number</td>
                    <td>'.$invoice->si_number.'</td>
                </tr>
                </tr>
            </table>
        ';                

        $html = '
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <meta http-equiv="X-UA-Compatible" content="ie=edge">
                <title>Sales Invoice</title>
            </head>
            <style>
                p {
                    font-size: 12px;
                }
                table, th, td {
                    border: 1px solid black;
                    border-collapse: collapse;
                }
                th {
                    padding: 5px;
                    text-align: center;
                    font-size: 12px;
                }
                td {
                    padding: 5px;
                    font-size: 12px;
                }
                table {
                    width: 100%;
                }
                body {
                    font-family: Arial, Helvetica, sans-serif;
                }
                .logo {
                    width: 250px;
                }
                #footer {
                    position: fixed;
                    left: 0;
                    right: 0;
                    color: #aaa;
                    font-size: 0.9em;
                }
                #header {
                    top: 0;
                    border-bottom: 0.1pt solid #aaa;
                }
                #footer {
                    bottom: 0;
                    border-top: 0.1pt solid #aaa;
                }
                .page-number:before {
                    content: "Page " counter(page);
                }
            </style>
            <body>
                <img src="./images/logo.png" alt="Adamay Logo" class="logo">
                <h2 style="text-align: center">Sales Invoice Information</h2>
                '.$table_contents.'
                <div id="footer">
                    <div class="page-number"></div>
                </div>
            </body>
            </html>
        ';

        $pdf = PDF::loadHTML($html);
        $pdf->setPaper('A4', 'portrait');
        return $pdf->stream('Adamay_'.$filename.'.pdf');
    }

    public function exportPDFDeliveryMonitoring(Request $request){
        $filename = 'DeliveryMonitoring_'.date('Y:m:d H:i:s');

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

        $delivery_monitoring = '';
        if($request['showDeleted'] == 'true'){
            $delivery_monitoring = ClientPODeliveryMonitoring::onlyTrashed()
                ->where(function($query) use ($request){
                    $query->where('po_id','like','%'.$request['search'].'%');
                })
                ->orderBy($sortProp, $sortOrder)
                ->paginate($request['take']);
        }
        else{
            $delivery_monitoring = ClientPODeliveryMonitoring::where(function($query) use ($request){
                    $query->where('po_id','like','%'.$request['search'].'%');
                })
                ->orderBy($sortProp, $sortOrder)
                ->paginate($request['take']);
        }

        if($request['sortProp'] || $request['sortOrder']) $sort_class = 'block';
        else $sort_class = 'none';

        if(strlen($request['keywords']) > 0) $search_filter_class = 'block';
        else $search_filter_class = 'none';

        $table_contents = '';

        foreach($delivery_monitoring as $dm){
            $table_contents .= '
                <tr>
                    <td>'.$dm->po_id.'</td>
                    <td>'.$dm->po_id.'</td>
                    <td>'.$dm->po_id.'</td>
                    <td>'.$dm->po_id.'</td>
                </tr>
            ';
        }

        $html = '
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <meta http-equiv="X-UA-Compatible" content="ie=edge">
                <title>Delivery Monitoring</title>
            </head>
            <style>
                p {
                    font-size: 12px;
                }
                table, th, td {
                    border: 1px solid black;
                    border-collapse: collapse;
                }
                th {
                    padding: 5px;
                    text-align: center;
                    font-size: 14px;
                }
                td {
                    padding: 5px;
                    font-size: 12px;
                }
                table {
                    width: 100%;
                }
                body {
                    font-family: Arial, Helvetica, sans-serif;
                }
                .logo {
                    width: 250px;
                }
                #footer {
                    position: fixed;
                    left: 0;
                    right: 0;
                    color: #aaa;
                    font-size: 0.9em;
                }
                #header {
                    top: 0;
                    border-bottom: 0.1pt solid #aaa;
                }
                #footer {
                    bottom: 0;
                    border-top: 0.1pt solid #aaa;
                }
                .page-number:before {
                    content: "Page " counter(page);
                }
            </style>
            <body>
                <img src="./images/logo.png" alt="Adamay Logo" class="logo">
                <h2 style="text-align: center">Delivery Monitoring</h2>
                <p style="display: '.$search_filter_class.'">Search Filter: "'.$request['keywords'].'"</p>
                <p style="display: '.$sort_class.'">Sort by: '.$sortProp.", ".$sortOrder.'</p>
                <table>
                    <tr>
                        <th>PO ID</th>
                        <th>Invoice ID</th>
                        <th>Receipt ID</th>
                        <th>Order ID</th>
                    </tr>
                    '.$table_contents.'
                </table>
                <div id="footer">
                    <div class="page-number"></div>
                </div>
            </body>
            </html>
            <script type="text/php">
                if (isset($pdf)) {
                    $text = "page {PAGE_NUM} / {PAGE_COUNT}";
                    $size = 10;
                    $font = $fontMetrics->getFont("Arial");
                    $width = $fontMetrics->get_text_width($text, $font, $size) / 2;
                    $x = ($pdf->get_width() - $width) / 2;
                    $y = $pdf->get_height() - 35;
                    $pdf->page_text($x, $y, $text, $font, $size);
                }
            </script>
        ';

        $pdf = PDF::loadHTML($html);
        $pdf->setPaper('A4', 'landscape');
        return $pdf->stream('Adamay_'.$filename.'.pdf');
    }

// Customer Sales Monitoring ********************************************************************

    public function clientQuotations(Request $request){
        $filename = 'Customer_Sales_Monitoring_Quotation'.date('Y:m:d H:i:s');

        $client = Client::where('id', $request['clientId'])->first();

        $sortProp = $request['sortProp'];
        $sortOrder = $request['sortOrder'];
        if($sortProp != 'undefined' && $sortOrder != 'undefined'){
            if($sortOrder === 'descending') $sortOrder = 'desc';
            else $sortOrder = 'asc';
        }
        else{
            $sortProp = 'quotation_number';
            $sortOrder = 'asc';
        }

        if($request['showDeleted'] == 'true'){
            $quotes = Quotation::onlyTrashed()->where('client_id', $request['clientId'])
                // ->leftJoin('clients', 'clients.id', 'quotation.client_id')
                // ->leftJoin('client_contacts', 'client_contacts.id', 'quotation.client_pic_id')
                ->orderBy($sortProp, $sortOrder)
                ->paginate($request['take']);
        }
        else{
            $quotes = Quotation::where('client_id', $request['clientId'])
                // leftJoin('clients', 'clients.id', '=' ,'quotation.client_id')
                // ->leftJoin('client_contacts', 'client_contacts.id', '=' , 'quotation.client_pic_id')
                ->orderBy($sortProp, $sortOrder)
                ->paginate($request['take']);
        }

        if($request['sortProp'] || $request['sortOrder']) $sort_class = 'block';
        else $sort_class = 'none';

        if(strlen($request['keywords']) > 0) $search_filter_class = 'block';
        else $search_filter_class = 'none';

       
        
        $quantity_units_single = [
            ['id' => 1, 'unit' => 'pc'], 
            ['id' => 2, 'unit' => 'box'], 
            ['id' => 3, 'unit' => 'roll'], 
            ['id' => 4, 'unit' => 'sheet']
        ];

        
        $client = Client::where('id', $request['clientId'])->first();
        
        $client_municipality = Municipalities::where('id', $client->client_municipality_id)->first();
        $client_province = Provinces::where('id', $client->client_province_id)->first();

        $pic = ClientContacts::where('client_id', $request['clientId'])->get();
        $quotations = '';
        foreach($quotes as $row){

            for ($i=0; $i < count($pic) ; $i++) { 
                if($row->client_pic_id == $pic[$i]['id']) $row->client_pic = $pic[$i]['client_pic'];
            }

            for ($i=0; $i < count($quantity_units_single) ; $i++) { 
                if($row->quotation_qty_unit == $quantity_units_single[$i]['id']) $row->quotation_qty_unit = $quantity_units_single[$i]['unit'];
            }

            $quotations .= '
                <tr>
                    <td>'.$row->quotation_number.'</td>
                    <td>'.$row->client_pic.'</td>
                    <td>'.$row->order_qty_per_quotation_unit.'/'.$row->quotation_qty_unit.'</td>
                    <td>'.$row->quotation_specs.'</td>
                    <td>'.$row->quotation_status.'</td>
                    <td>'.$row->remarks.'</td>
                    
                </tr>
            ';
        }
        
        $html = '
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <meta http-equiv="X-UA-Compatible" content="ie=edge">
                <title>Customer Sales Monitoring - Quotations</title>
            </head>
            <style>
                p {
                    font-size: 12px;
                }
                table, th, td {
                    border: 1px solid black;
                    border-collapse: collapse;
                }
                th {
                    padding: 5px;
                    text-align: center;
                    font-size: 14px;
                }
                td {
                    padding: 5px;
                    font-size: 12px;
                }
                table {
                    width: 100%;
                }
                body {
                    font-family: Arial, Helvetica, sans-serif;
                }
                .logo {
                    width: 250px;
                }
                #footer {
                    position: fixed;
                    left: 0;
                    right: 0;
                    color: #aaa;
                    font-size: 0.9em;
                }
                #header {
                    top: 0;
                    border-bottom: 0.1pt solid #aaa;
                }
                #footer {
                    bottom: 0;
                    border-top: 0.1pt solid #aaa;
                }
                .page-number:before {
                    content: "Page " counter(page);
                }
            </style>
            <body>
                <img src="./images/logo.png" alt="Adamay Logo" class="logo">
                <h2 style="text-align: center">Customer Quotations</h2>
                <table>
                    <tr>
                        <td style="width: 30%"><strong>Customer Name</strong></td>
                        <td>'.$client->client_name.'</td>
                    </tr>
                    <tr>
                        <td style="width: 30%"><strong>Customer Number</strong></td>
                        <td>'.$client->client_number.'</td>
                    </tr>
                </table>
                <br>
                <table>
                <tr>   
                    <td>Quotation No.</td>
                    <td>PIC</td>
                    <td>Order Qty/Unit</td>
                    <td>Specs</td>
                    <td>Status</td>
                    <td>Remarks</td>
                </tr>
                    '.$quotations.'
                </table>
                <div id="footer">
                    <div class="page-number"></div>
                </div>
            </body>
            </html>
            <script type="text/php">
                if (isset($pdf)) {
                    $text = "page {PAGE_NUM} / {PAGE_COUNT}";
                    $size = 10;
                    $font = $fontMetrics->getFont("Arial");
                    $width = $fontMetrics->get_text_width($text, $font, $size) / 2;
                    $x = ($pdf->get_width() - $width) / 2;
                    $y = $pdf->get_height() - 35;
                    $pdf->page_text($x, $y, $text, $font, $size);
                }
            </script>
        ';

        $pdf = PDF::loadHTML($html);
        $pdf->setPaper('A4', 'portrait');
        return $pdf->stream('Adamay_'.$filename.'.pdf');
    }

    public function clientOrders(Request $request){
        $filename = 'Customer_Sales_Monitoring_Orders'.date('Y:m:d H:i:s');
        $client = Client::where('id', $request['clientId'])->first();
        $sortProp = $request['sortProp'];
        $sortOrder = $request['sortOrder'];
        if($sortProp != 'undefined' && $sortOrder != 'undefined'){
            if($sortOrder === 'descending') $sortOrder = 'desc';
            else $sortOrder = 'asc';
        }
        else{
            $sortProp = 'order_item_code';
            $sortOrder = 'asc';
        }

        if($request['showDeleted'] == 'true'){
            $orders =  Orders::onlyTrashed()
                ->where(function($query) use ($request){
                    $query->where('order_item_code','like','%'.$request['keywords'].'%')
                        ->orWhere('order_item_description','like','%'.$request['keywords'].'%')
                        ->orWhere('order_part_number','like','%'.$request['keywords'].'%');
                })
                ->where('computation_sheet.cs_client_id', $request['clientId'])
                ->leftJoin('computation_sheet', 'computation_sheet.client_order_id', 'orders.id')
                ->orderBy($sortProp, $sortOrder)
                ->get();
            $search_filter_class = 'block';
        }
        else{
            $orders = Orders::where(function($query) use ($request){
                $query->where('order_item_code','like','%'.$request['keywords'].'%')
                    ->orWhere('order_item_description','like','%'.$request['keywords'].'%')
                    ->orWhere('order_part_number','like','%'.$request['keywords'].'%');
            })
            ->where('computation_sheet.cs_client_id', $request['clientId'])
            ->leftJoin('computation_sheet', 'computation_sheet.client_order_id', 'orders.id')
                ->orderBy($sortProp, $sortOrder)
                ->get();
            $search_filter_class = 'none';
        }

        if($request['sortProp'] || $request['sortOrder']) $sort_class = 'block';
        else $sort_class = 'none';

        if(strlen($request['keywords']) > 0) $search_filter_class = 'block';
        else $search_filter_class = 'none';

        $table_contents = '';                
        foreach($orders as $order){
            $order_categories = [
                [ 'id' => 1, 'category_name' => 'Manual' ],
                [ 'id' => 2, 'category_name' => 'Manual (Sheeted)' ],
                [ 'id' => 3, 'category_name' => 'Instruction Sheet' ],
                [ 'id' => 4, 'category_name' => 'Label (Blank)' ],
                [ 'id' => 5, 'category_name' => 'Label (Rolled)' ],
                [ 'id' => 6, 'category_name' => 'Label (Sheeted)' ],
                [ 'id' => 7, 'category_name' => 'Leaflet' ],
                [ 'id' => 8, 'category_name' => 'Continuous Form' ],
                [ 'id' => 9, 'category_name' => 'Envelope (Big)' ],
                [ 'id' => 10, 'category_name' => 'Envelope (Regular)' ],
                [ 'id' => 11, 'category_name' => 'Envelope (Standard)' ],
                [ 'id' => 12, 'category_name' => 'Stretch Film' ],
                [ 'id' => 13, 'category_name' => 'Sato' ],
            ];

            for ($i = 0; $i < count($order_categories) ; $i++) { 
                if($order_categories[$i]['id'] == $order->order_category) $order_category = $order_categories[$i]['category_name'];
            }

            $type_code = TypeCode::where('id', $order->order_item_type_code)->first();
            $classification = MaterialClassifications::where('id', $order->order_item_classification)->first();

            $table_contents .= '
                <tr>
                    <td>'.$order->order_item_code.'</td>
                    <td>'.$order->order_item_description.'</td>
                    <td>'.$order->order_part_number.'</td>
                    <td>'.$order_category.'</td>
                    <td>'.$type_code->type_code.' - '.$type_code->type_code_description.'</td>
                    <td>'.$classification->material_classification_name.'</td>
                </tr>
            ';
        }

        $html = '
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <meta http-equiv="X-UA-Compatible" content="ie=edge">
                <title>Customer Sales Monitoring - Orders</title>
            </head>
            <style>
                p {
                    font-size: 12px;
                }
                table, th, td {
                    border: 1px solid black;
                    border-collapse: collapse;
                }
                th {
                    padding: 5px;
                    text-align: center;
                    font-size: 14px;
                }
                td {
                    padding: 5px;
                    font-size: 12px;
                }
                table {
                    width: 100%;
                }
                body {
                    font-family: Arial, Helvetica, sans-serif;
                }
                .logo {
                    width: 250px;
                }
                #footer {
                    position: fixed;
                    left: 0;
                    right: 0;
                    color: #aaa;
                    font-size: 0.9em;
                }
                #header {
                    top: 0;
                    border-bottom: 0.1pt solid #aaa;
                }
                #footer {
                    bottom: 0;
                    border-top: 0.1pt solid #aaa;
                }
                .page-number:before {
                    content: "Page " counter(page);
                }
            </style>
            <body>
                <img src="./images/logo.png" alt="Adamay Logo" class="logo">
                <h2 style="text-align: center">Customer Orders</h2>

                <table>
                    <tr>
                        <td style="width: 30%"><strong>Customer Name</strong></td>
                        <td>'.$client->client_name.'</td>
                    </tr>
                    <tr>
                        <td style="width: 30%"><strong>Customer Number</strong></td>
                        <td>'.$client->client_number.'</td>
                    </tr>
                </table>
                <br>

                <table>
                <tr>
                    <th>Order Code</th>
                    <th>Order Description</th>
                    <th>Part Number</th>
                    <th>Category</th>
                    <th>Type Code</th>
                    <th>Classification</th>
                </tr>
                '.$table_contents.'
            </table>
                <div id="footer">
                    <div class="page-number"></div>
                </div>
            </body>
            </html>
            <script type="text/php">
                if (isset($pdf)) {
                    $text = "page {PAGE_NUM} / {PAGE_COUNT}";
                    $size = 10;
                    $font = $fontMetrics->getFont("Arial");
                    $width = $fontMetrics->get_text_width($text, $font, $size) / 2;
                    $x = ($pdf->get_width() - $width) / 2;
                    $y = $pdf->get_height() - 35;
                    $pdf->page_text($x, $y, $text, $font, $size);
                }
            </script>
        ';

        $pdf = PDF::loadHTML($html);
        $pdf->setPaper('A4', 'portrait');
        return $pdf->stream('Adamay_'.$filename.'.pdf');
    }
    public function clientPO(Request $request){
        $filename = 'Customer_Sales_Monitoring_PO'.date('Y:m:d H:i:s');
        $client = Client::where('id', $request['clientId'])->first();
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
                ->where('client_po.client_id', $request['clientId'])
                ->where(function($query) use ($request){
                    $query->where('client_po.client_po_number','like','%'.$request['search'].'%');
                })
                ->leftJoin('clients', 'clients.id', 'client_po.client_id')
                ->leftJoin('client_contacts', 'client_contacts.id', 'client_po.client_pic')
                ->get();
        }
        else{
            $po = ClientPO::select('client_po.*', 'clients.client_short_name as client_short_name', 'client_contacts.client_pic as pic_name')
                ->where('client_po.client_id', $request['clientId'])
                ->where(function($query) use ($request){
                    $query->where('client_po.client_po_number','like','%'.$request['search'].'%');
                })
                ->leftJoin('clients', 'clients.id', 'client_po.client_id')
                ->leftJoin('client_contacts', 'client_contacts.id', 'client_po.client_pic')
                ->get();
        }

        
        $pic = ClientContacts::where('client_id', $request['clientId'])->get();
        $table_contents = '';
        foreach($po as $row){

            for ($i=0; $i < count($pic) ; $i++) { 
                if($row->client_pic == $pic[$i]['id']) $row->client_pic = $pic[$i]['client_pic'];
                else $row->client_pic = '';
            }


            $table_contents .= '
                <tr>
                    <td>'.$row->client_po_number.'</td>
                    <td>'.$row->client_pic.'</td>
                    <td>'.$row->client_po_date.'</td>
                    <td>'.$row->client_po_received_date.'</td>
                    <td>'.$row->client_po_delivery_date.'</td>
                    <td>'.$row->client_po_amount_currency.' '.$row->client_po_amount.'</td>
                    <td>'.$row->client_po_status.'</td>
                    
                </tr>
            ';
        }



        
        
        $html = '
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <meta http-equiv="X-UA-Compatible" content="ie=edge">
                <title>Customer Sales Monitoring - Purchase Order</title>
            </head>
            <style>
                p {
                    font-size: 12px;
                }
                table, th, td {
                    border: 1px solid black;
                    border-collapse: collapse;
                }
                th {
                    padding: 5px;
                    text-align: center;
                    font-size: 14px;
                }
                td {
                    padding: 5px;
                    font-size: 12px;
                }
                table {
                    width: 100%;
                }
                body {
                    font-family: Arial, Helvetica, sans-serif;
                }
                .logo {
                    width: 250px;
                }
                #footer {
                    position: fixed;
                    left: 0;
                    right: 0;
                    color: #aaa;
                    font-size: 0.9em;
                }
                #header {
                    top: 0;
                    border-bottom: 0.1pt solid #aaa;
                }
                #footer {
                    bottom: 0;
                    border-top: 0.1pt solid #aaa;
                }
                .page-number:before {
                    content: "Page " counter(page);
                }
            </style>
            <body>
                <img src="./images/logo.png" alt="Adamay Logo" class="logo">
                <h2 style="text-align: center">Customer Purchase Order</h2>

                <table>
                    <tr>
                        <td style="width: 30%"><strong>Customer Name</strong></td>
                        <td>'.$client->client_name.'</td>
                    </tr>
                    <tr>
                        <td style="width: 30%"><strong>Customer Number</strong></td>
                        <td>'.$client->client_number.'</td>
                    </tr>
                </table>
                <br>

                <table>
                <tr>   
                    <td>PO No.</td>
                    <td>PIC</td>
                    <td>PO Date</td>
                    <td>Received Date</td>
                    <td>Delivery Date</td>
                    <td>Total Amount</td>
                    <td>Status</td>
                </tr>
                    '.$table_contents.'
                </table>
                <div id="footer">
                    <div class="page-number"></div>
                </div>
            </body>
            </html>
            <script type="text/php">
                if (isset($pdf)) {
                    $text = "page {PAGE_NUM} / {PAGE_COUNT}";
                    $size = 10;
                    $font = $fontMetrics->getFont("Arial");
                    $width = $fontMetrics->get_text_width($text, $font, $size) / 2;
                    $x = ($pdf->get_width() - $width) / 2;
                    $y = $pdf->get_height() - 35;
                    $pdf->page_text($x, $y, $text, $font, $size);
                }
            </script>
        ';

        $pdf = PDF::loadHTML($html);
        $pdf->setPaper('A4', 'portrait');
        return $pdf->stream('Adamay_'.$filename.'.pdf');
    }
    public function clientCS(Request $request){
        $filename = 'Customer_Sales_Monitoring_CS'.date('Y:m:d H:i:s');
        $client = Client::where('id', $request['clientId'])->first();
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

        if($request['showDeleted'] == 'true'){
            $computation_sheets =  ComputationSheet::select('computation_sheet.id','computation_sheet.cs_control_number', 'computation_sheet.cs_version_number', 'computation_sheet.cs_total_amount', 'computation_sheet.cs_currency', 'orders.order_part_number', 'clients.client_short_name')
            ->onlyTrashed()
            ->where(function($query) use ($request){
                $query->where('computation_sheet.cs_control_number','like','%'.$request['search'].'%');
            })
            ->leftJoin('orders', 'orders.id', 'computation_sheet.client_order_id')
            ->leftJoin('clients', 'clients.id', 'computation_sheet.cs_client_id')
            ->orderBy($sortProp, $sortOrder)
            ->where('cs_client_id', $request['clientId'])
            ->get();
            $search_filter_class = 'block';
        }
        else{
            $computation_sheets = ComputationSheet::select('computation_sheet.id','computation_sheet.cs_control_number', 'computation_sheet.cs_version_number', 'computation_sheet.cs_total_amount', 'computation_sheet.cs_currency', 'orders.order_part_number', 'clients.client_short_name')
            ->where(function($query) use ($request){
                $query->where('computation_sheet.cs_control_number','like','%'.$request['search'].'%');
            })
            ->leftJoin('orders', 'orders.id', 'computation_sheet.client_order_id')
            ->leftJoin('clients', 'clients.id', 'computation_sheet.cs_client_id')
            ->orderBy($sortProp, $sortOrder)
            ->where('cs_client_id', $request['clientId'])
            ->get();
            $search_filter_class = 'none';
        }

        if($request['sortProp'] || $request['sortOrder']) $sort_class = 'block';
        else $sort_class = 'none';

        if(strlen($request['keywords']) > 0) $search_filter_class = 'block';
        else $search_filter_class = 'none';

        $table_contents = '';                
        foreach($computation_sheets as $row){
            $table_contents .= '
                <tr>
                    <td>'.$row->cs_control_number.'</td>
                    <td>'.'v'.$row->cs_version_number.'</td>
                    <td>'.$row->order_part_number.'</td>
                    <td style="text-align:right">'.$row->cs_currency.' '.$row->cs_total_amount.'</td>
                </tr>
            ';
        }

        $html = '
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <meta http-equiv="X-UA-Compatible" content="ie=edge">
                <title>Computation Sheets</title>
            </head>
            <style>
                p {
                    font-size: 12px;
                }
                table, th, td {
                    border: 1px solid black;
                    border-collapse: collapse;
                }
                th {
                    padding: 5px;
                    text-align: center;
                    font-size: 14px;
                }
                td {
                    padding: 5px;
                    font-size: 12px;
                }
                table {
                    width: 100%;
                }
                body {
                    font-family: Arial, Helvetica, sans-serif;
                }
                .logo {
                    width: 250px;
                }
                #footer {
                    position: fixed;
                    left: 0;
                    right: 0;
                    color: #aaa;
                    font-size: 0.9em;
                }
                #header {
                    top: 0;
                    border-bottom: 0.1pt solid #aaa;
                }
                #footer {
                    bottom: 0;
                    border-top: 0.1pt solid #aaa;
                }
                .page-number:before {
                    content: "Page " counter(page);
                }
            </style>
            <body>
                <img src="./images/logo.png" alt="Adamay Logo" class="logo">
                <h2 style="text-align: center">Customer Computation Sheets</h2>
                <p style="display: '.$search_filter_class.'">Search Filter: "'.$request['keywords'].'"</p>
                <p style="display: '.$sort_class.'">Sort by: '.$sortProp.", ".$sortOrder.'</p>
                
                <table>
                    <tr>
                        <td style="width: 30%"><strong>Customer Name</strong></td>
                        <td>'.$client->client_name.'</td>
                    </tr>
                    <tr>
                        <td style="width: 30%"><strong>Customer Number</strong></td>
                        <td>'.$client->client_number.'</td>
                    </tr>
                </table>
                <br>

                <table>
                    <tr>
                        <th>Control Number</th>
                        <th>Version</th>
                        <th>Part Number</th>
                        <th>Amount</th>
                    </tr>
                    '.$table_contents.'
                </table>
                <div id="footer">
                    <div class="page-number"></div>
                </div>
            </body>
            </html>
            <script type="text/php">
                if (isset($pdf)) {
                    $text = "page {PAGE_NUM} / {PAGE_COUNT}";
                    $size = 10;
                    $font = $fontMetrics->getFont("Arial");
                    $width = $fontMetrics->get_text_width($text, $font, $size) / 2;
                    $x = ($pdf->get_width() - $width) / 2;
                    $y = $pdf->get_height() - 35;
                    $pdf->page_text($x, $y, $text, $font, $size);
                }
            </script>
        ';

        $pdf = PDF::loadHTML($html);
        $pdf->setPaper('A4', 'portrait');
        return $pdf->stream('Adamay_'.$filename.'.pdf');
    }
    public function clientSS(Request $request){
        $filename = 'Customer_Sales_Monitoring_SS'.date('Y:m:d H:i:s');
        $client = Client::where('id', $request['clientId'])->first();
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
            ->where('computation_sheet.cs_client_id', $request['clientId'])
            ->where('sales_sheet.deleted_at', '!=', NULL)
            ->get();
        }  
        else {
            $ss = DB::table('sales_sheet')->leftJoin('computation_sheet', 'computation_sheet.id', 'sales_sheet.cs_id')
            ->where('computation_sheet.cs_client_id', $request['clientId'])
            ->where('sales_sheet.deleted_at', NULL)
            ->get();
        }

        $table_contents = '';                
        foreach($ss as $row){
            $table_contents .= '
                <tr>
                    <td>'.$row->ss_reference_number.'</td>
                    <td>'.$row->cs_control_number.'</td>
                    <td>'.$row->ss_qty.'</td>
                    <td>'.$row->ss_qty_remaining.'</td>
                    <td>'.$row->ss_payment_method.'</td>
                    <td>'.$row->ss_status.'</td>
                </tr>
            ';
        }
        
        
        $html = '
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <meta http-equiv="X-UA-Compatible" content="ie=edge">
                <title>Customer Sales Monitoring - SS</title>
            </head>
            <style>
                p {
                    font-size: 12px;
                }
                table, th, td {
                    border: 1px solid black;
                    border-collapse: collapse;
                }
                th {
                    padding: 5px;
                    text-align: center;
                    font-size: 14px;
                }
                td {
                    padding: 5px;
                    font-size: 12px;
                }
                table {
                    width: 100%;
                }
                body {
                    font-family: Arial, Helvetica, sans-serif;
                }
                .logo {
                    width: 250px;
                }
                #footer {
                    position: fixed;
                    left: 0;
                    right: 0;
                    color: #aaa;
                    font-size: 0.9em;
                }
                #header {
                    top: 0;
                    border-bottom: 0.1pt solid #aaa;
                }
                #footer {
                    bottom: 0;
                    border-top: 0.1pt solid #aaa;
                }
                .page-number:before {
                    content: "Page " counter(page);
                }
            </style>
            <body>
                <img src="./images/logo.png" alt="Adamay Logo" class="logo">
                <h2 style="text-align: center">Customer Sales Sheets</h2>

                <table>
                    <tr>
                        <td style="width: 30%"><strong>Customer Name</strong></td>
                        <td>'.$client->client_name.'</td>
                    </tr>
                    <tr>
                        <td style="width: 30%"><strong>Customer Number</strong></td>
                        <td>'.$client->client_number.'</td>
                    </tr>
                </table>
                <br>

                <table>
                <tr>   
                    <td>SS Ref No.</td>
                    <td>Computation Sheet No.</td>
                    <td>SS Quantity</td>
                    <td>SS Remaining</td>
                    <td>Payment Method</td>
                    <td>SS Status</td>
                </tr>
                    '.$table_contents.'
                </table>
                <div id="footer">
                    <div class="page-number"></div>
                </div>
            </body>
            </html>
            <script type="text/php">
                if (isset($pdf)) {
                    $text = "page {PAGE_NUM} / {PAGE_COUNT}";
                    $size = 10;
                    $font = $fontMetrics->getFont("Arial");
                    $width = $fontMetrics->get_text_width($text, $font, $size) / 2;
                    $x = ($pdf->get_width() - $width) / 2;
                    $y = $pdf->get_height() - 35;
                    $pdf->page_text($x, $y, $text, $font, $size);
                }
            </script>
        ';

        $pdf = PDF::loadHTML($html);
        $pdf->setPaper('A4', 'portrait');
        return $pdf->stream('Adamay_'.$filename.'.pdf');
    }
    public function clientDM(Request $request){
        $filename = 'Customer_Sales_Monitoring_Delivery_Monitoring'.date('Y:m:d H:i:s');
        $client = Client::where('id', $request['clientId'])->first();

        $sortProp = $request['sortProp'];
        $sortOrder = $request['sortOrder'];
        if($sortProp != 'undefined' && $sortOrder != 'undefined'){
            if($sortOrder === 'descending') $sortOrder = 'desc';
            else $sortOrder = 'asc';
        }
        else{
            $sortProp = 'part_number';
            $sortOrder = 'asc';
        }


        if($request['showDeleted'] == 'true'){
            $dm = ClientPODeliveryMonitoring::onlyTrashed()
            ->leftJoin('client_po', 'client_po.id', 'client_po_delivery_monitoring.po_id')
            ->leftJoin('client_po_invoice', 'client_po_invoice.id', 'client_po_delivery_monitoring.invoice_id')
            ->leftJoin('client_po_receipt', 'client_po_receipt.id', 'client_po_delivery_monitoring.receipt_id')
            ->leftJoin('orders', 'orders.id', 'client_po_delivery_monitoring.order_id')
            ->where('client_po.client_id', $request['clientId'])
            ->get();
        }
        else{
            $dm = ClientPODeliveryMonitoring::select('client_po_delivery_monitoring.*', 'client_po_invoice.si_number as si_number', 'client_po.client_po_number', 'client_po_receipt.dr_number')->leftJoin('client_po', 'client_po.id', 'client_po_delivery_monitoring.po_id')
            ->leftJoin('client_po_invoice', 'client_po_invoice.id', 'client_po_delivery_monitoring.invoice_id')
            ->leftJoin('client_po_receipt', 'client_po_receipt.id', 'client_po_delivery_monitoring.receipt_id')
            ->leftJoin('orders', 'orders.id', 'client_po_delivery_monitoring.order_id')
            ->where('client_po.client_id', $request['clientId'])
            ->get();
        }

        $table_contents = '';                
        foreach($dm as $row){

            $units_quantity = [
                [ 'id' => 1, 'unit_name' => 'pcs' ],
                [ 'id' => 2, 'unit_name' => 'box' ],
                [ 'id' => 3, 'unit_name' => 'roll' ],
                [ 'id' => 4, 'unit_name' => 'sheet' ],
            ];

            for ($i = 0; $i < count($units_quantity) ; $i++) { 
                if($units_quantity[$i]['id'] == $row->quantity_unit) $row->unit = $units_quantity[$i]['unit_name'];
            }

            
            $table_contents .= '
                <tr>
                    <td>'.$row->client_po_number.'</td>
                    <td>'.$row->si_number.'</td>
                    <td>'.$row->dr_number.'</td>
                    <td>'.$row->delivery_qty.' '.$row->unit.'</td>
                    <td>'.$row->del_qty.'</td>
                    <td>'.$row->delivered_at.'</td>
                </tr>
            ';
        }

       
        
        $html = '
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <meta http-equiv="X-UA-Compatible" content="ie=edge">
                <title>Customer Sales Monitoring - Delivery Monitoring</title>
            </head>
            <style>
                p {
                    font-size: 12px;
                }
                table, th, td {
                    border: 1px solid black;
                    border-collapse: collapse;
                }
                th {
                    padding: 5px;
                    text-align: center;
                    font-size: 14px;
                }
                td {
                    padding: 5px;
                    font-size: 12px;
                }
                table {
                    width: 100%;
                }
                body {
                    font-family: Arial, Helvetica, sans-serif;
                }
                .logo {
                    width: 250px;
                }
                #footer {
                    position: fixed;
                    left: 0;
                    right: 0;
                    color: #aaa;
                    font-size: 0.9em;
                }
                #header {
                    top: 0;
                    border-bottom: 0.1pt solid #aaa;
                }
                #footer {
                    bottom: 0;
                    border-top: 0.1pt solid #aaa;
                }
                .page-number:before {
                    content: "Page " counter(page);
                }
            </style>
            <body>
                <img src="./images/logo.png" alt="Adamay Logo" class="logo">
                <h2 style="text-align: center">Customer Delivery Monitoring</h2>

                <table>
                    <tr>
                        <td style="width: 30%"><strong>Customer Name</strong></td>
                        <td>'.$client->client_name.'</td>
                    </tr>
                    <tr>
                        <td style="width: 30%"><strong>Customer Number</strong></td>
                        <td>'.$client->client_number.'</td>
                    </tr>
                </table>
                <br>


                <table>
                <tr> 
                    <td>PO No.</td>
                    <td>SI No.</td>
                    <td>Delivery Receipt/Unit</td>
                    <td>Delivery Quantity</td>
                    <td>Delivered</td>
                    <td>Delivered Date</td>
                </tr>
                    '.$table_contents.'
                </table>
                <div id="footer">
                    <div class="page-number"></div>
                </div>
            </body>
            </html>
            <script type="text/php">
                if (isset($pdf)) {
                    $text = "page {PAGE_NUM} / {PAGE_COUNT}";
                    $size = 10;
                    $font = $fontMetrics->getFont("Arial");
                    $width = $fontMetrics->get_text_width($text, $font, $size) / 2;
                    $x = ($pdf->get_width() - $width) / 2;
                    $y = $pdf->get_height() - 35;
                    $pdf->page_text($x, $y, $text, $font, $size);
                }
            </script>
        ';

        $pdf = PDF::loadHTML($html);
        $pdf->setPaper('A4', 'portrait');
        return $pdf->stream('Adamay_'.$filename.'.pdf');
    }
}
