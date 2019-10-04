<?php


    # Consumables
    Route::group([ 'prefix' => 'consumables', 'middleware' => ['auth']], function () {
        Route::get(
            '{consumableID}/checkout',
            [ 'as' => 'checkout/consumable','uses' => 'Consumables\ConsumableCheckoutController@create' ]
        );
        Route::post(
            '{consumableID}/checkout',
            [ 'as' => 'checkout/consumable', 'uses' => 'Consumables\ConsumableCheckoutController@store' ]
        );

        Route::get('{consumableId}/restore', [
            'as' => 'restore/consumables',
            'uses' => 'Consumables\ConsumablesController@getRestore'
        ]);

        Route::post('bulkedit', [
            'as'   => 'consumables/bulkedit',
            'uses' => 'Consumables\BulkConsumablesController@edit'
        ]);
        
        Route::post('bulkdelete', [
            'as'   => 'consumables/bulkdelete',
            'uses' => 'Consumables\BulkConsumablesController@destroy'
        ]);

        Route::post('bulksave', [
            'as'   => 'consumables/bulksave',
            'uses' => 'Consumables\BulkConsumablesController@update'
        ]);        
    });

    Route::resource('consumables', 'Consumables\ConsumablesController', [
        'middleware' => ['auth'],
        'parameters' => ['consumable' => 'consumable_id']
    ]);
