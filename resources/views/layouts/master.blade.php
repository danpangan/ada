<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="x-ua-compatible" content="ie=edge">

    <title>Adamay Web</title>
    <link rel="icon" href="{!! asset('/images/icon.png') !!}"/>
    <link rel="stylesheet" href="/css/app.css"/>

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<style>
    .navbar {
        height: 72px;
    }
</style>
<body class="hold-transition sidebar-mini">
<div class="wrapper" id="app">

<nav class="main-header navbar navbar-expand navbar-light navbar-white fixed-top" style="padding-bottom: 11px;">
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#">
                <i class="fas fa-bars" style="font-size: 25px"></i>
            </a>
        </li>
    </ul>

    <ul class="navbar-nav ml-auto">
        <li class="nav-item dropdown">
            <a class="nav-link" data-toggle="dropdown" href="#">
                <i class="fas fa-bell bell"></i>
                <span class="badge badge-warning navbar-badge">15</span>
            </a>
            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                <span class="dropdown-item dropdown-header">15 Notifications</span>
                <div class="dropdown-divider"></div>
                <a href="#" class="dropdown-item">
                <i class="fas fa-envelope mr-2"></i> New CS for approval
                <span class="float-right text-muted text-sm">3 mins</span>
                </a>
                <div class="dropdown-divider"></div>
                <a href="#" class="dropdown-item">
                <i class="fas fa-file mr-2"></i> New QS for approval
                <span class="float-right text-muted text-sm">20 mins</span>
                </a>
                <div class="dropdown-divider"></div>
                <a href="#" class="dropdown-item">
                <i class="fas fa-file mr-2"></i> New SS for approval
                <span class="float-right text-muted text-sm">1 hr</span>
                </a>
                <div class="dropdown-divider"></div>
                <a href="#" class="dropdown-item">
                <i class="fas fa-file mr-2"></i> New SI for approval
                <span class="float-right text-muted text-sm">2 hrs</span>
                </a>
                <div class="dropdown-divider"></div>
                <a href="#" class="dropdown-item dropdown-footer">See All Notifications</a>
            </div>
        </li>

        <li class="nav-item dropdown">
        <a class="nav-link" data-toggle="dropdown" href="#">
            <p class="employee_name">{{ Auth::user()->first_name." ".Auth::user()->last_name }} &nbsp; <i class="fas fa-caret-down"></i></p>
        </a>
        <div class="dropdown-menu dropdown-menu-md dropdown-menu-right">
            <router-link :to="'/my-account/'+{{ Auth::user()->id }}" class="dropdown-item">
                <i class="fas fa-cogs mr-2"></i> Account Setting
            </router-link>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item" href="{{ route('logout') }}"
                onclick="event.preventDefault();
                    document.getElementById('logout-form').submit();">
                <i class="fas fa-power-off mr-2"></i> {{ __('Logout') }}
            </a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                @csrf
            </form>
        </div>
        </li>
    </ul>
</nav>
<!-- /.navbar -->

<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <router-link to="/home" class="brand-link-custom">
        <img src="/images/logo.png" alt="Adamay Logo" class="brand-image-custom">
    </router-link>

    <!-- Sidebar -->
    <div class="sidebar">

    <!-- Sidebar Menu -->
    <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
            <li class="nav-item">
                <router-link to="/home" class="nav-link">
                    <i class="nav-icon fas fa-home"></i>
                    <p>Home</p>
                </router-link>
            </li>

            <li class="nav-item has-treeview">
                <a href="#" class="nav-link">
                    <i class="nav-icon fas fa-tachometer-alt"></i>
                    <p>
                        Employee Management
                        <i class="right fas fa-angle-left"></i>
                    </p>
                </a>
                <ul class="nav nav-treeview">
                    <li class="nav-item">
                        <router-link to="/employees" class="nav-link">
                            <i class="fas fa-users nav-icon ml-3"></i>
                            <p>Employees</p>
                        </router-link>
                    </li>
                    <li class="nav-item">
                        <router-link to="/departments" class="nav-link">
                            <i class="fas fa-city nav-icon ml-3"></i>
                            <p>Departments</p>
                        </router-link>
                    </li>
                </ul>
            </li>

            <li class="nav-item has-treeview">
                <router-link to="/customer-management" class="nav-link">
                    <i class="nav-icon fas fa-user-friends"></i>
                    <p>Customer Management</p>
                </router-link>
            </li>

            <li class="nav-item has-treeview">
                <a href="#" class="nav-link">
                    <i class="nav-icon fas fa-warehouse"></i>
                    <p>
                        Inventory Management
                        <i class="right fas fa-angle-left"></i>
                    </p>
                </a>
                <ul class="nav nav-treeview">
                    <li class="nav-item">
                        <router-link to="/raw-materials" class="nav-link">
                            <i class="fas fa-box-open nav-icon ml-3"></i>
                            <p>Raw Materials</p>
                        </router-link>
                    </li>
                    <li class="nav-item">
                        <router-link to="/subcontract-materials" class="nav-link">
                            <i class="fas fa-boxes nav-icon ml-3"></i>
                            <p>Subcontract Materials</p>
                        </router-link>
                    </li>
                    <li class="nav-item">
                        <router-link to="/finish-products" class="nav-link">
                            <i class="fas fa-box nav-icon ml-3"></i>
                            <p>Finish Products</p>
                        </router-link>
                    </li>
                </ul>
            </li>

            <li class="nav-item has-treeview">
                <router-link to="/supplier-management"  class="nav-link">
                    <i class="nav-icon fas fa-user-friends"></i>
                    <p>
                        Supplier Management
                    </p>
                </router-link>
            </li>

            <li class="nav-item has-treeview">
                <router-link to="/order-management" class="nav-link">
                    <i class="nav-icon fas fa-luggage-cart"></i>
                    <p>Order Management</p>
                </router-link>
            </li>

            <li class="nav-item has-treeview">
                <a href="" class="nav-link">
                    <i class="nav-icon fas fa-chart-line"></i>
                    <p>
                        Sales Management
                        <i class="right fas fa-angle-left"></i>
                    </p>
                </a>
                <ul class="nav nav-treeview">
                    <li class="nav-item">
                        <router-link to="/computation-sheets" class="nav-link">
                            <i class="fas fa-file-invoice nav-icon ml-3"></i>
                            <p>Computation Sheets</p>
                        </router-link>
                    </li>
                    <li class="nav-item">
                        <router-link to="/quotations" class="nav-link">
                            <i class="fas fa-file-alt nav-icon ml-3"></i>
                            <p>Quotations</p>
                        </router-link>
                    </li>
                    <li class="nav-item">
                        <router-link to="/purchase-orders" class="nav-link">
                            <i class="fas fa-file-contract nav-icon ml-3"></i>
                            <p>Purchase Orders</p>
                        </router-link>
                    </li>
                    <li class="nav-item">
                        <router-link to="/sales-sheets" class="nav-link">
                            <i class="fas fa-file-invoice-dollar nav-icon ml-3"></i>
                            <p>Sales Sheets</p>
                        </router-link>
                    </li>
                    <li class="nav-item">
                        <router-link to="/invoices" class="nav-link">
                            <i class="fas fa-receipt nav-icon ml-3"></i>
                            <p>Invoices</p>
                        </router-link>
                    </li>
                    <li class="nav-item">
                        <router-link to="/delivery-monitoring" class="nav-link">
                            <i class="fas fa-truck-loading nav-icon ml-3"></i>
                            <p>Delivery Monitor</p>
                        </router-link>
                    </li>
                </ul>
            </li>

            <li class="nav-item has-treeview">
                <router-link to="/forecast-management" class="nav-link">
                    <i class="nav-icon fas fa-chart-bar"></i>
                    <p>Forecast Management</p>
                </router-link>
            </li>

            <li class="nav-item has-treeview">
                <router-link to="/forex-management" class="nav-link">
                    <i class="nav-icon fas fa-dollar-sign"></i>
                    <p>ForEx Management</p>
                </router-link>
            </li>

            <li class="nav-item has-treeview"> 
                <router-link to="/reports-management" class="nav-link">
                    <i class="nav-icon fas fa-list-alt"></i>
                    <p>Reports Management</p>
                </router-link>
            </li>

            <li class="nav-item has-treeview">
                <a href="" class="nav-link">
                    <i class="nav-icon fas fa-chart-line"></i>
                    <p>
                        Maintenance Management
                        <i class="right fas fa-angle-left"></i>
                    </p>
                </a>
                <ul class="nav nav-treeview">
                    <li class="nav-item">
                        <router-link to="/process-maintenance" class="nav-link">
                            <i class="fas fa-file-invoice nav-icon ml-3"></i>
                            <p>Process Maintenance</p>
                        </router-link>
                    </li>
                    <li class="nav-item">
                        <router-link to="/locations" class="nav-link">
                            <i class="fas fa-map-marked-alt nav-icon ml-3"></i>
                            <p>Location Maintenance</p>
                        </router-link>
                    </li>
                    <li class="nav-item">
                        <router-link to="/material-classifications-maintenance" class="nav-link">
                            <i class="fas fa-file-alt nav-icon ml-3"></i>
                            <p>Material Maintenance</p>
                        </router-link>
                    </li>
                </ul>
            </li>

            {{-- <li class="nav-item">
                <router-link to="/developer" class="nav-link">
                    <i class="nav-icon fab fa-dev"></i>
                    <p>
                        Developer
                    </p>
                </router-link>
            </li> --}}
        </ul>
    </nav>
    </div>
</aside> 

<div class="content-wrapper" style="margin-top: 70px">
    <router-view></router-view>
    <vue-progress-bar></vue-progress-bar>
</div>

<aside class="control-sidebar control-sidebar-dark">
</aside>

<!-- Main Footer -->
<footer class="main-footer mt-3">
    <strong>Copyright &copy;<?php echo date('Y'); ?> <a href="/home">Adamay Web</a>.</strong>
     All rights reserved.
    <div class="float-right d-none d-sm-inline-block">
        <b>Version</b> 1.0.0
    </div>
</footer>
</div>

<script src="/js/app.js"></script>
</body>
</html>
