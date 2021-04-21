/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');
// require('./bootstrap');

window.Vue = require('vue');

import moment from 'moment';
import VueRouter from 'vue-router'
import { Form, HasError, AlertError } from 'vform'; 

import ElementUI from 'element-ui';
import 'element-ui/lib/theme-chalk/index.css';
import locale from 'element-ui/lib/locale/lang/en';

Vue.use(ElementUI, { locale });

window.Form = Form;

Vue.component(HasError.name, HasError)
Vue.component(AlertError.name, AlertError)

Vue.use(VueRouter)

import VueProgressBar from 'vue-progressbar'

const options = {
	color: '#bffaf3',
	failedColor: '#874b4b',
	thickness: '5px',
	transition: {
		speed: '0.2s',
		opacity: '0.6s',
		termination: 300
	},
	autoRevert: true,
	location: 'top',
	inverse: false
}

Vue.use(VueProgressBar, options)

import Swal from 'sweetalert2'

const Toast = Swal.mixin({
	toast: true,
	position: 'top-end',
	showConfirmButton: false,
	timer: 3000,
	timerProgressBar: true,
	onOpen: (toast) => {
		toast.addEventListener('mouseenter', Swal.stopTimer)
		toast.addEventListener('mouseleave', Swal.resumeTimer)
	}
})

window.Toast = Toast;
window.Swal = Swal;

window.Fire = new Vue();


let routes = [
    { 
        path: '/developer', 
        component: require('./components/Developer.vue').default 
    },

    { 
        path: '/account-setting/:employee_id', 
        component: require('./components/employee-management/Employee.vue').default 
    },
    { 
        path: '/my-account/:employee_id', 
        component: require('./components/employee-management/MyAccount.vue').default 
    },

    { 
        path: '/home', 
        component: require('./components/home/Home.vue').default 
    },

    { 
        path: '/employees', 
        component: require('./components/employee-management/Employees.vue').default 
    },
    { 
        path: '/view-employee/:employee_id', 
        component: require('./components/employee-management/Employee.vue').default 
    },
    { 
        path: '/add-employee', 
        component: require('./components/employee-management/AddEmployee.vue').default 
    },
    { 
        path: '/update-employee/:employee_id', 
        component: require('./components/employee-management/Employee.vue').default 
    },
    { 
        path: '/update-account/:employee_id', 
        component: require('./components/employee-management/MyAccount.vue').default 
    },
    { 
        path: '/departments', 
        component: require('./components/employee-management/Departments.vue').default 
    },
    { 
        path: '/view-department/:department_id', 
        component: require('./components/employee-management/Department.vue').default 
    },
    { 
        path: '/add-department', 
        component: require('./components/employee-management/AddDepartment.vue').default 
    },
    { 
        path: '/update-department/:department_id', 
        component: require('./components/employee-management/Department.vue').default 
    },

    { 
        path: '/customer-management', 
        component: require('./components/customer-management/CustomerManagement.vue').default 
    },
    { 
        path: '/view-client/:client_id', 
        component: require('./components/customer-management/Customer.vue').default 
    },
    { 
        path: '/add-client', 
        component: require('./components/customer-management/AddCustomer.vue').default 
    },
    { 
        path: '/update-client/:client_id', 
        component: require('./components/customer-management/Customer.vue').default 
    },

    { 
        path: '/raw-materials', 
        component: require('./components/inventory-management/RawMaterials.vue').default 
    },
    { 
        path: '/add-raw-material', 
        component: require('./components/inventory-management/AddRawMaterial.vue').default 
    },
    { 
        path: '/view-raw-material/:material_id', 
        component: require('./components/inventory-management/RawMaterial.vue').default 
    },
    { 
        path: '/update-raw-material/:material_id', 
        component: require('./components/inventory-management/RawMaterial.vue').default 
    },
    { 
        path: '/raw-material-flow/:material_flow_id/material-id/:material_id', 
        component: require('./components/inventory-management/RawMaterialFlow.vue').default 
    },

    { 
        path: '/subcontract-materials', 
        component: require('./components/inventory-management/SubcontractMaterials.vue').default 
    },
    { 
        path: '/add-subcontract-material',
        component: require('./components/inventory-management/AddSubcontractMaterial.vue').default 
    },
    { 
        path: '/view-subcontract-material/:sc_material_id', 
        component: require('./components/inventory-management/SubcontractMaterial.vue').default 
    },
    { 
        path: '/update-subcontract-material/:sc_material_id', 
        component: require('./components/inventory-management/SubcontractMaterial.vue').default 
    },
    { 
        path: '/subcontract-material-flow/:material_flow_id/sc-material-id/:sc_material_id', 
        component: require('./components/inventory-management/SubcontractMaterialFlow.vue').default 
    },

    { 
        path: '/finish-products', 
        component: require('./components/inventory-management/FinishProducts.vue').default 
    },
    { 
        path: '/add-product',
        component: require('./components/inventory-management/AddProduct.vue').default 
    },
    { 
        path: '/view-product/:product_id', 
        component: require('./components/inventory-management/Product.vue').default 
    },
    { 
        path: '/update-product/:product_id', 
        component: require('./components/inventory-management/Product.vue').default 
    },
    { 
        path: '/product-flow/:product_flow_id/product-id/:product_id', 
        component: require('./components/inventory-management/ProductFlow.vue').default 
    },
    
    { 
        path: '/supplier-management', 
        component: require('./components/supplier-management/SupplierManagement.vue').default 
    },
    { 
        path: '/add-supplier', 
        component: require('./components/supplier-management/AddSupplier.vue').default 
    },
    { 
        path: '/view-supplier/:supplier_id', 
        component: require('./components/supplier-management/Supplier.vue').default 
    },
    { 
        path: '/update-supplier/:supplier_id', 
        component: require('./components/supplier-management/Supplier.vue').default 
    },

    { 
        path: '/order-management', 
        component: require('./components/order-management/OrderManagement.vue').default 
    },
    { 
        path: '/add-order', 
        component: require('./components/order-management/AddOrder.vue').default 
    },
    { 
        path: '/view-order/:order_id', 
        component: require('./components/order-management/Order.vue').default 
    },
    { 
        path: '/update-order/:order_id', 
        component: require('./components/order-management/Order.vue').default 
    },

    { 
        path: '/computation-sheets', 
        component: require('./components/sales-management/ComputationSheets.vue').default 
    },
    { 
        path: '/add-cs', 
        component: require('./components/sales-management/AddComputationSheet.vue').default 
    },
    { 
        path: '/view-cs/:cs_id', 
        component: require('./components/sales-management/ComputationSheet.vue').default 
    },
    { 
        path: '/update-cs/:cs_id', 
        component: require('./components/sales-management/ComputationSheet.vue').default 
    },

    { 
        path: '/quotations', 
        component: require('./components/sales-management/Quotations.vue').default 
    },
    { 
        path: '/add-quotation', 
        component: require('./components/sales-management/AddQuotation.vue').default 
    },
    { 
        path: '/view-quotation/:quotation_id', 
        component: require('./components/sales-management/Quotation.vue').default 
    },
    { 
        path: '/update-quotation/:quotation_id', 
        component: require('./components/sales-management/Quotation.vue').default 
    },
    { 
        path: '/copy-quotation/:quotation_id', 
        component: require('./components/sales-management/Quotation.vue').default 
    },

    { 
        path: '/purchase-orders', 
        component: require('./components/sales-management/PurchaseOrders.vue').default 
    },
    { 
        path: '/add-po', 
        component: require('./components/sales-management/AddPurchaseOrder.vue').default 
    },
    { 
        path: '/view-po/:po_id', 
        component: require('./components/sales-management/PurchaseOrder.vue').default 
    },
    { 
        path: '/update-po/:po_id', 
        component: require('./components/sales-management/PurchaseOrder.vue').default 
    },

    { 
        path: '/sales-sheets', 
        component: require('./components/sales-management/SalesSheets.vue').default 
    },
    { 
        path: '/add-ss', 
        component: require('./components/sales-management/AddSalesSheet.vue').default 
    },
    { 
        path: '/view-ss/:ss_id', 
        component: require('./components/sales-management/SalesSheet.vue').default 
    },
    { 
        path: '/update-ss/:ss_id', 
        component: require('./components/sales-management/SalesSheet.vue').default 
    },

    { 
        path: '/invoices', 
        component: require('./components/sales-management/Invoices.vue').default 
    },
    { 
        path: '/add-invoice', 
        component: require('./components/sales-management/AddInvoice.vue').default 
    },
    { 
        path: '/view-invoice/:invoice_id', 
        component: require('./components/sales-management/Invoice.vue').default 
    },
    { 
        path: '/update-invoice/:invoice_id', 
        component: require('./components/sales-management/Invoice.vue').default 
    },

    { 
        path: '/delivery-monitoring', 
        component: require('./components/sales-management/DeliveryMonitoring.vue').default 
    },
    { 
        path: '/delivery-monitoring-po-items/:po_number', 
        component: require('./components/sales-management/DeliveryMonitoringPOItems.vue').default 
    },
    { 
        path: '/delivery-monitoring-po-item/:po_number_item_id', 
        component: require('./components/sales-management/DeliveryMonitoringPOItem.vue').default 
    },
    // { 
    //     path: '/customer-sales-monitoring/:page_type/client/:client_id', 
    //     component: require('./components/sales-management/CustomerSalesMonitoring.vue').default 
    // },
    { 
        path: '/customer-sales-monitoring/purchase-orders/client/:client_id', 
        component: require('./components/sales-management/customer-sales-monitoring/ClientPO.vue').default 
    },
    { 
        path: '/customer-sales-monitoring/computation-sheets/client/:client_id', 
        component: require('./components/sales-management/customer-sales-monitoring/ClientCS.vue').default 
    },
    { 
        path: '/customer-sales-monitoring/delivery-monitoring/client/:client_id', 
        component: require('./components/sales-management/customer-sales-monitoring/ClientDM.vue').default 
    },
    { 
        path: '/customer-sales-monitoring/forecast/client/:client_id', 
        component: require('./components/sales-management/customer-sales-monitoring/ClientForecast.vue').default 
    },
    { 
        path: '/customer-sales-monitoring/orders/client/:client_id', 
        component: require('./components/sales-management/customer-sales-monitoring/ClientOrders.vue').default 
    },
    { 
        path: '/customer-sales-monitoring/quotations/client/:client_id', 
        component: require('./components/sales-management/customer-sales-monitoring/ClientQuotations.vue').default 
    },
    { 
        path: '/customer-sales-monitoring/sales-sheets/client/:client_id', 
        component: require('./components/sales-management/customer-sales-monitoring/ClientSS.vue').default 
    },
    { 
        path: '/forecast-management', 
        component: require('./components/forecast-management/ForecastManagement.vue').default 
    },

    { 
        path: '/add-client-forecast', 
        component: require('./components/forecast-management/AddClientForecast.vue').default 
    },

    { 
        path: '/view-client-forecast/:client_forecast_id', 
        component: require('./components/forecast-management/ClientForecast.vue').default 
    },

    { 
        path: '/update-client-forecast/:client_forecast_id', 
        component: require('./components/forecast-management/ClientForecast.vue').default 
    },

    { 
        path: '/forex-management', 
        component: require('./components/forex-management/ForexManagement.vue').default 
    },

    { 
        path: '/reports-management', 
        component: require('./components/reports-management/ReportsManagement.vue').default 
    },
    
    { 
        path: '/process-maintenance', 
        component: require('./components/maintenance-management/ProcessMaintenance.vue').default 
    },
    { 
        path: '/view-process/:process_id/process/:source_table', 
        component: require('./components/maintenance-management/Process.vue').default 
    },
    { 
        path: '/add-price-bracket/:process_id/name/:name/show-all-bundle/:show_all_bundle', 
        component: require('./components/maintenance-management/Process.vue').default 
    },

    //Location Maintenance 
    { 
        path: '/locations', 
        component: require('./components/maintenance-management/Locations.vue').default 
    },
    { 
        path: '/view-province/:province_id/:province_name', 
        component: require('./components/maintenance-management/Location.vue').default 
    },
    { 
        path: '/update-province/:province_id/:province_name', 
        component: require('./components/maintenance-management/Location.vue').default 
    },
    { 
        path: '/update-municipality/:municipality_id', 
        component: require('./components/maintenance-management/Municipality.vue').default 
    },
    { 
        path: '/update-classification/:classification_id', 
        component: require('./components/maintenance-management/Location.vue').default 
    },
    { 
        path: '/material-classifications-maintenance', 
        component: require('./components/maintenance-management/TypeMaintenance.vue').default 
    },
    { 
        path: '/view-material-classification/:type_code_id/type-code/:type_code/:descriptions', 
        component: require('./components/maintenance-management/MaterialClassifications.vue').default 
    },
    
  ] 

const router = new VueRouter({
    mode: 'history',
    routes,
})

Vue.filter('upperCaseFirstLetter', function(text){
    return text.charAt(0).toUpperCase() + text.slice(1)
});

Vue.filter('dateFormat', function(created){
    return moment(created).format('MMMM DD, YYYY')
});

Vue.component(
	'passport-clients',
	require('./components/passport/Clients.vue').default
);

Vue.component(
	'passport-authorized-clients',
	require('./components/passport/AuthorizedClients.vue').default
);

Vue.component(
	'passport-personal-access-tokens',
	require('./components/passport/PersonalAccessTokens.vue').default
);
 
Vue.prototype.$units_dimension = [
    { 'id': 1, 'unit_name': 'microns' },
    { 'id': 2, 'unit_name': 'mm' },
    { 'id': 3, 'unit_name': 'cm' },
    { 'id': 4, 'unit_name': 'm' },
    { 'id': 5, 'unit_name': 'in' },
    { 'id': 6, 'unit_name': 'ft' },
    { 'id': 7, 'unit_name': 'yrds' },
]

Vue.prototype.$units_quantity = [
    { 'id': 1, 'unit_name': 'pcs' },
    { 'id': 2, 'unit_name': 'box' },
    { 'id': 3, 'unit_name': 'roll' },
    { 'id': 4, 'unit_name': 'sheet' },
]

Vue.prototype.$units_amount = [
    { 'id': 1, 'unit_name': 'JPY' },
    { 'id': 2, 'unit_name': 'PHP' },
    { 'id': 3, 'unit_name': 'USD' },
]

Vue.prototype.$currency_conversions = [
    { 'id': 1, 'label': 'PHP to USD', 'convert_to': 'USD' },
    { 'id': 2, 'label': 'USD to PHP', 'convert_to': 'PHP' },
    { 'id': 3, 'label': 'JPY to USD', 'convert_to': 'USD' },
    { 'id': 4, 'label': 'JPY to PHP', 'convert_to': 'PHP' },
]

Vue.prototype.$material_flows = [
    { 'id': 1, 'material_flow_name': 'Purchased' },
    { 'id': 2, 'material_flow_name': 'Transfered In' },
    { 'id': 3, 'material_flow_name': 'Used' },
    { 'id': 4, 'material_flow_name': 'Transfer Out' },
]

Vue.prototype.$material_flows_title = [
    { 'id': 1, 'material_flow_name': 'Purchase' },
    { 'id': 2, 'material_flow_name': 'Transfer In' },
    { 'id': 3, 'material_flow_name': 'Use' },
    { 'id': 4, 'material_flow_name': 'Transfer Out' },
]

Vue.prototype.$product_flows = [
    { 'id': 1, 'flow_name': 'Store Product' },
    { 'id': 2, 'flow_name': 'Release Product' },
    { 'id': 3, 'flow_name': 'Undelivered Product' },
    { 'id': 4, 'flow_name': 'Return Product' },
    { 'id': 5, 'flow_name': 'Remove Product' },
]

Vue.prototype.$months = [
    { id: 1, month_name: 'January' }, 
    { id: 2, month_name: 'February' }, 
    { id: 3, month_name: 'March' }, 
    { id: 4, month_name: 'April' }, 
    { id: 5, month_name: 'May' }, 
    { id: 6, month_name: 'June' }, 
    { id: 7, month_name: 'July' }, 
    { id: 8, month_name: 'August' }, 
    { id: 9, month_name: 'September' }, 
    { id: 10, month_name: 'October' }, 
    { id: 11, month_name: 'November' }, 
    { id: 12, month_name: 'December' }
]

Vue.prototype.$user_roles = [
    { id: 1, role_name: 'Developer' },
    { id: 2, role_name: 'Admin' },
    { id: 3, role_name: 'Manager' },
    { id: 4, role_name: 'Employee' },
]

Vue.prototype.$genders = [
    { id: 1, gender_name: 'Male' },
    { id: 2, gender_name: 'Female' },
]

Vue.prototype.$supplier_categories = [
    { 'id': 0, 'category_name': 'Overseas Supplier' },
    { 'id': 1, 'category_name': 'Domestic Supplier' },
]

Vue.prototype.$supplier_classifications = [
    { 'id': 0, 'classification_name': 'Direct Material' },
    { 'id': 1, 'classification_name': 'Indirect Material' },
    { 'id': 2, 'classification_name': 'Sub-contractor' },
    { 'id': 3, 'classification_name': 'Service Provider' },
    { 'id': 4, 'classification_name': 'Warehousing' },
    { 'id': 8, 'classification_name': 'Dispatch Contractor' },
]

Vue.prototype.$product_categories = [
    { 'id': 1, 'category_name': 'Semi-finished Goods' },
    { 'id': 2, 'category_name': 'Subcontractor Product' },
]

Vue.prototype.$dummy_dropdowns = [
    { 'id': 1, 'category_name': 'Selection 1' },
    { 'id': 2, 'category_name': 'Selection 2' },
    { 'id': 3, 'category_name': 'Selection 3' },
    { 'id': 4, 'category_name': 'Selection 4' },
]

Vue.prototype.$location_filters = [
    { 'id': 1, 'filter_name': 'Active Customers' },
    { 'id': 2, 'filter_name': 'Active Suppliers' },
]

Vue.prototype.$order_categories = [
    { 'id': 1, 'category_name': 'Manual' },
    { 'id': 2, 'category_name': 'Manual (Sheeted)' },
    { 'id': 3, 'category_name': 'Instruction Sheet' },
    { 'id': 4, 'category_name': 'Label (Blank)' },
    { 'id': 5, 'category_name': 'Label (Rolled)' },
    { 'id': 6, 'category_name': 'Label (Sheeted)' },
    { 'id': 7, 'category_name': 'Leaflet' },
    { 'id': 8, 'category_name': 'Continuous Form' },
    { 'id': 9, 'category_name': 'Envelope (Big)' },
    { 'id': 10, 'category_name': 'Envelope (Regular)' },
    { 'id': 11, 'category_name': 'Envelope (Standard)' },
    { 'id': 12, 'category_name': 'Stretch Film' },
    { 'id': 13, 'category_name': 'Sato' },
]

Vue.prototype.$machines = [
    { 'id': 1, 'machine_name': 'Machine 1' },
    { 'id': 2, 'machine_name': 'Machine 2' },
    { 'id': 3, 'machine_name': 'Machine 3' },
    { 'id': 4, 'machine_name': 'Machine 4' },
    { 'id': 5, 'machine_name': 'Machine 5' },
]

Vue.prototype.$evaluation_status = [
    { 'id': 1, 'evaluation_status': 'For check' },
    { 'id': 2, 'evaluation_status': 'Checked' },
    { 'id': 3, 'evaluation_status': 'For approval' },
    { 'id': 4, 'evaluation_status': 'Approved' },
    { 'id': 5, 'evaluation_status': 'Rejected' },
]

const app = new Vue({
    router
}).$mount('#app')