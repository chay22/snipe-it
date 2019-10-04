<?php


# Licenses
Route::group([ 'prefix' => 'licenses', 'middleware' => ['auth'] ], function () {

    Route::get('{licenseId}/clone', [ 'as' => 'clone/license', 'uses' => 'Licenses\LicensesController@getClone' ]);

    Route::get('{licenseId}/freecheckout', [
        'as' => 'licenses.freecheckout',
        'uses' => 'Licenses\LicensesController@getFreeLicense'
    ]);
    
    Route::get('{licenseId}/checkout/{seatId?}', [
        'as' => 'licenses.checkout',
        'uses' => 'Licenses\LicenseCheckoutController@create'
    ]);
    
    Route::post('{licenseId}/checkout/{seatId?}', [
        'as' => 'licenses.checkout',
        'uses' => 'Licenses\LicenseCheckoutController@store'
    ]);
    
    Route::get('{licenseId}/checkin/{backto?}', [
        'as' => 'licenses.checkin',
        'uses' => 'Licenses\LicenseCheckinController@create'
    ]);

    Route::post('{licenseId}/checkin/{backto?}', [
        'as' => 'licenses.checkin.save',
        'uses' => 'Licenses\LicenseCheckinController@store'
    ]);

    Route::post('{licenseId}/upload', [
        'as' => 'upload/license',
        'uses' => 'Licenses\LicenseFilesController@store'
    ]);
    
    Route::delete('{licenseId}/deletefile/{fileId}', [
        'as' => 'delete/licensefile',
        'uses' => 'Licenses\LicenseFilesController@destroy'
    ]);

    Route::get('{licenseId}/showfile/{fileId}/{download?}', [
        'as' => 'show.licensefile',
        'uses' => 'Licenses\LicenseFilesController@show'
    ]);

    Route::post('bulkedit', [
        'as'   => 'licenses/bulkedit',
        'uses' => 'Licenses\BulkLicensesController@edit'
    ]);
    
    Route::post('bulkdelete', [
        'as'   => 'licenses/bulkdelete',
        'uses' => 'Licenses\BulkLicensesController@destroy'
    ]);

    Route::post('bulksave', [
        'as'   => 'licenses/bulksave',
        'uses' => 'Licenses\BulkLicensesController@update'
    ]);

    # Bulk checkout / checkin
    Route::get( 'bulkcheckout',  [
             'as' => 'licenses/bulkcheckout',
             'uses' => 'Licenses\BulkLicensesController@showCheckout'
    ]);
    
    Route::post( 'bulkcheckout',  [
        'as' => 'licenses/bulkcheckout',
        'uses' => 'Licenses\BulkLicensesController@storeCheckout'
    ]);    
});

Route::resource('licenses', 'Licenses\LicensesController', [
    'middleware' => ['auth'],
    'parameters' => ['license' => 'license_id']
]);
