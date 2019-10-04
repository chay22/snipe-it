<?php

namespace App\Http\Controllers\Licenses;

use App\Helpers\Helper;
use App\Http\Controllers\CheckInOutRequest;
use App\Http\Controllers\Controller;
use App\Models\License;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BulkLicensesController extends Controller
{
    use CheckInOutRequest;

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
        $this->authorize('update', License::class);

        if (!$request->filled('ids')) {
            return redirect()->back()->with('error', 'No licenses selected');
        }

        $license_ids = array_keys($request->input('ids'));

        if ($request->filled('bulk_actions')) {
            switch($request->input('bulk_actions')) {
                case 'delete':
                    $licenses = License::with('licenseseats', 'freeSeats')->find($license_ids);
                    $licenses->each(function ($license) {
                        $this->authorize('delete', $license);
                    });
                    return view('licenses/bulk-delete')->with('licenses', $licenses);
                case 'edit':
                    return view('licenses/bulk')
                        ->with('licenses', request('ids'))
                        ->with('item', new License());
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
        $this->authorize('update', License::class);

        \Log::debug($request->input('ids'));

        if(! $request->filled('ids') || count($request->input('ids')) <= 0) {
            return redirect()->route('licenses.index')->with('warning', trans('No licenses selected, so nothing was updated.'));
        }

        $licenses = array_keys($request->input('ids'));

        if (($request->filled('company_id'))
            || ($request->filled('manufacturer_id'))
            || ($request->filled('license_name'))
            || ($request->filled('license_email'))
            || ($request->filled('reassignable'))
            || ($request->filled('supplier_id'))
            || ($request->filled('purchase_cost'))
            || ($request->filled('purchase_date'))
            || ($request->filled('termination_date'))
            || ($request->filled('purchase_order'))
            || ($request->filled('maintained'))
        ) {
            foreach ($licenses as $licenseId) {
                $this->update_array = [];

                $this->conditionallyAddItem('manufacturer_id')
                    ->conditionallyAddItem('license_name')
                    ->conditionallyAddItem('license_email')
                    ->conditionallyAddItem('reassignable')
                    ->conditionallyAddItem('supplier_id')
                    ->conditionallyAddItem('supplier_id')
                    ->conditionallyAddItem('purchase_date')
                    ->conditionallyAddItem('termination_date')
                    ->conditionallyAddItem('purchase_order');

                if ($request->filled('purchase_cost')) {
                    $this->update_array['purchase_cost'] =  Helper::ParseFloat($request->input('purchase_cost'));
                }

                if ($request->filled('company_id')) {
                    $this->update_array['company_id'] =  $request->input('company_id');
                    if ($request->input('company_id')=="clear") {
                        $this->update_array['company_id'] = null;
                    }
                }

                if ($request->filled('maintained')) {
                    $this->updated_array['maintained']  = $request->maintained;
                }

                DB::table('licenses')
                    ->where('id', $licenseId)
                    ->update($this->update_array);
            } // endforeach
            return redirect()->route('licenses.index')->with('success', trans('admin/licenses/message.update.success'));
        // no values given, nothing to update
        }
        return redirect()->route('licenses.index')->with('warning', trans('admin/licenses/message.update.nothing_updated'));

    }

    /**
     * Array to store update data per item
     * @var Array
     */
    private $update_array;

    /**
     * Adds parameter to update array for an item if it exists in request
     * @param  String $field field name
     * @return BulkLicensesController Model for Chaining
     */
    protected function conditionallyAddItem($field)
    {
        if(request()->filled($field)) {
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
        $this->authorize('delete', License::class);

        if ($request->filled('ids')) {
            License::whereIn('id', $request->ids)->get()->map(function ($license) {
                $license->licenseseats()->delete();
                $license->delete();
            });

            return redirect()->to('licenses')->with('success', trans('admin/licenses/message.delete.success'));
            // no values given, nothing to update
        }

        return redirect()->to('licenses')->with('info', trans('admin/licenses/message.delete.nothing_updated'));
    }

    /**
     * Show Bulk Checkout Page
     * @return View View to checkout multiple assets
     */
    public function showCheckout()
    {
        $this->authorize('checkout', Asset::class);
        // Filter out assets that are not deployable.

        return view('licenses/bulk-checkout');
    }

    /**
     * Process Multiple Checkout Request
     * @return View
     */
    public function storeCheckout(Request $request)
    {
        try {
            $admin = Auth::user();

            $target = $this->determineCheckoutTarget();

            if (!is_array($request->get('selected_assets'))) {
                return redirect()->route('licenses/bulkcheckout')->withInput()->with('error', trans('admin/licenses/message.checkout.no_assets_selected'));
            }

            $asset_ids = array_filter($request->get('selected_assets'));

            foreach ($asset_ids as $asset_id) {
                if ($target->id == $asset_id && request('checkout_to_type') =='asset') {
                    return redirect()->back()->with('error', 'You cannot check an asset out to itself.');
                }
            }
            $checkout_at = date("Y-m-d H:i:s");
            if (($request->filled('checkout_at')) && ($request->get('checkout_at')!= date("Y-m-d"))) {
                $checkout_at = e($request->get('checkout_at'));
            }

            $expected_checkin = '';

            if ($request->filled('expected_checkin')) {
                $expected_checkin = e($request->get('expected_checkin'));
            }

            $errors = [];
            DB::transaction(function () use ($target, $admin, $checkout_at, $expected_checkin, $errors, $asset_ids, $request) {

                foreach ($asset_ids as $asset_id) {
                    $asset = Asset::findOrFail($asset_id);
                    $this->authorize('checkout', $asset);
                    $error = $asset->checkOut($target, $admin, $checkout_at, $expected_checkin, e($request->get('note')), null);

                    if ($target->location_id!='') {
                        $asset->location_id = $target->location_id;
                        $asset->unsetEventDispatcher();
                        $asset->save();
                    }

                    if ($error) {
                        array_merge_recursive($errors, $asset->getErrors()->toArray());
                    }
                }
            });

            if (!$errors) {
              // Redirect to the new asset page
                return redirect()->to('licenses')->with('success', trans('admin/licenses/message.checkout.success'));
            }
            // Redirect to the asset management page with error
            return redirect()->to('licenses/bulk-checkout')->with('error', trans('admin/licenses/message.checkout.error'))->withErrors($errors);
        } catch (ModelNotFoundException $e) {
            return redirect()->to('licenses/bulk-checkout')->with('error', $e->getErrors());
        }
    }
}
