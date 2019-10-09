<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BulkCategoriesController extends Controller
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
        $this->authorize('update', Category::class);

        if (!$request->filled('ids')) {
            return redirect()->back()->with('error', 'No categories selected');
        }

        $category_ids = array_keys($request->input('ids'));

        if ($request->filled('bulk_actions')) {
            switch($request->input('bulk_actions')) {
                case 'delete':
                    $categories = Category::find($category_ids);
                    $categories->each(function ($category) {
                        $this->authorize('delete', $category);
                    });
                    return view('categories/bulk-delete')->with('categories', $categories);
                case 'edit':
                    return view('categories/bulk')
                        ->with('categories', request('ids'))
                        ->with('category_types', Helper::categoryTypeList())
                        ->with('item', new Category());
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
        $this->authorize('update', Category::class);

        \Log::debug($request->input('ids'));

        if(! $request->filled('ids') || count($request->input('ids')) <= 0) {
            return redirect()->route('categories.index')->with('warning', trans('No categories selected, so nothing was updated.'));
        }

        $categories = array_keys($request->input('ids'));

        if (($request->filled('category_type'))
            || ($request->filled('eula_text'))
            || ($request->filled('use_default_eula'))
            || ($request->filled('require_acceptance'))
        ) {
            foreach ($categories as $categoryId) {
                $this->update_array = [];

                $this->conditionallyAddItem('category_type')
                    ->conditionallyAddItem('eula_text')
                    ->conditionallyAddItem('use_default_eula')
                    ->conditionallyAddItem('require_acceptance');

                Category::find($categoryId)->update($this->update_array);

            } // endforeach
            return redirect()->route('categories.index')->with('success', trans('admin/categories/message.update.success'));
        // no values given, nothing to update
        }
        return redirect()->route('categories.index')->with('warning', trans('admin/categories/message.update.nothing_updated'));

    }

    /**
     * Array to store update data per item
     * @var Array
     */
    private $update_array;

    /**
     * Adds parameter to update array for an item if it exists in request
     * @param  String $field field name
     * @return BulkCategoriesController Model for Chaining
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
        $this->authorize('delete', Category::class);

        if ($request->filled('ids')) {
            Category::whereIn('id', $request->ids)->delete();

            return redirect()->to('categories')->with('success', trans('admin/categories/message.delete.success'));
            // no values given, nothing to update
        }

        return redirect()->to('categories')->with('info', trans('admin/categories/message.delete.nothing_updated'));
    }
}
