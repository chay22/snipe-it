<?php

/*
* Accessories
 */
Route::group([ 'prefix' => 'accessories', 'middleware' => ['auth']], function () {
    Route::get('{accessoryID}/checkout', [
        'as' => 'checkout/accessory',
        'uses' => 'Accessories\AccessoryCheckoutController@create'
    ]);
    
    Route::post('{accessoryID}/checkout', [
        'as' => 'checkout/accessory',
        'uses' => 'Accessories\AccessoryCheckoutController@store'
    ]);

    Route::get('{accessoryID}/checkin/{backto?}', [
        'as' => 'checkin/accessory',
        'uses' => 'Accessories\AccessoryCheckinController@create'
    ]);

    Route::post('{accessoryID}/checkin/{backto?}', [
        'as' => 'checkin/accessory', 'uses' => 'Accessories\AccessoryCheckinController@store'
    ]);

    Route::post('bulkedit', [
        'as'   => 'accessories/bulkedit',
        'uses' => 'Accessories\BulkAccessoriesController@edit'
    ]);
    
    Route::post('bulkdelete', [
        'as'   => 'accessories/bulkdelete',
        'uses' => 'Accessories\BulkAccessoriesController@destroy'
    ]);

    Route::post('bulksave', [
        'as'   => 'accessories/bulksave',
        'uses' => 'Accessories\BulkAccessoriesController@update'
    ]);
});

Route::resource('accessories', 'Accessories\AccessoriesController', [
    'middleware' => ['auth'],
    'parameters' => ['accessory' => 'accessory_id']
]);
