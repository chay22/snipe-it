<?php

namespace App\Http\Controllers\Accessories;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Accessory;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BulkAccessoriesController extends Controller
{
    /**
     * Display the bulk edit page.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @return View
     * @internal param int $assetId
     * @since [v2.0]
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function edit(Request $request)
    {
        $this->authorize('update', Accessory::class);

        if (!$request->filled('ids')) {
            return redirect()->back()->with('error', 'No accessories selected');
        }

        $accessory_ids = array_keys($request->input('ids'));

        if ($request->filled('bulk_actions')) {
            switch($request->input('bulk_actions')) {
                case 'delete':
                    $accessories = Accessory::find($accessory_ids);
                    $accessories->each(function ($accessory) {
                        $this->authorize('delete', $accessory);
                    });

                    return view('accessories/bulk-delete')->with('accessories', $accessories);
                case 'edit':
                    return view('accessories/bulk')
                        ->with('accessories', request('ids'))
                        ->with('item', new Accessory());
            }
        }
        return redirect()->back()->with('error', 'No action selected');
    }

    /**
     * Save bulk edits
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @return Redirect
     * @internal param array $assets
     * @since [v2.0]
     */
    public function update(Request $request)
    {
        $this->authorize('update', Accessory::class);

        \Log::debug($request->input('ids'));

        if(! $request->filled('ids') || count($request->input('ids')) <= 0) {
            return redirect()->route('accessories.index')->with('warning', trans('No accessories selected, so nothing was updated.'));
        }

        $accessories = array_keys($request->input('ids'));

        if (($request->filled('company_id'))
            || ($request->filled('category_id'))
            || ($request->filled('supplier_id'))
            || ($request->filled('manufacturer_id'))
            || ($request->filled('supplier_id'))
            || ($request->filled('purchase_cost'))
            || ($request->filled('purchase_date'))
            || ($request->filled('qty'))
            || ($request->filled('min_amt'))
        ) {
            foreach ($accessories as $accessoryId) {
                $this->update_array = [];

                $this->conditionallyAddItem('category_id')
                    ->conditionallyAddItem('supplier_id')
                    ->conditionallyAddItem('manufacturer_id')
                    ->conditionallyAddItem('supplier_id')
                    ->conditionallyAddItem('purchase_cost')
                    ->conditionallyAddItem('purchase_date')
                    ->conditionallyAddItem('qty')
                    ->conditionallyAddItem('min_amt');

                if ($request->filled('purchase_cost')) {
                    $this->update_array['purchase_cost'] =  Helper::ParseFloat($request->input('purchase_cost'));
                }

                if ($request->filled('company_id')) {
                    $this->update_array['company_id'] =  $request->input('company_id');
                    if ($request->input('company_id')=="clear") {
                        $this->update_array['company_id'] = null;
                    }
                }

                Accessory::find($accessoryId)->update($this->update_array);
                
            } // endforeach
            return redirect()->route('accessories.index')->with('success', trans('admin/accessories/message.update.success'));
        // no values given, nothing to update
        }
        return redirect()->route('accessories.index')->with('warning', trans('admin/accessories/message.update.nothing_updated'));

    }

    /**
     * Array to store update data per item
     * @var Array
     */
    private $update_array;

    /**
     * Adds parameter to update array for an item if it exists in request
     * @param  String $field field name
     * @return BulkAccessoriesController Model for Chaining
     */
    protected function conditionallyAddItem($field)
    {
        if (request()->filled($field)) {
            $this->update_array[$field] = request()->input($field);
        }
        
        return $this;
    }

    /**
     * Save bulk deleted.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @param Request $request
     * @return View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @internal param array $assets
     * @since [v2.0]
     */
    public function destroy(Request $request)
    {
        $this->authorize('delete', Accessory::class);

        if ($request->filled('ids')) {
            Accessory::whereIn('id', $request->ids)->delete();

            return redirect()->to('accessories')->with('success', trans('admin/accessories/message.delete.success'));
            // no values given, nothing to update
        }

        return redirect()->to('accessories')->with('info', trans('admin/accessories/message.delete.nothing_updated'));
    }
}
