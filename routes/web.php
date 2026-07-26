<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/show_table', function() {
    return response()->json(Schema::connection('tenant')->getColumnListing('app_bill_ident_canceled'));
});
