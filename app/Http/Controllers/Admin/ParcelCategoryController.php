<?php

namespace App\Http\Controllers\Admin;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Models\ParcelCategory;
use App\Models\Translation;
use Illuminate\Http\Request;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Config;

class ParcelCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $module_id = Config::get('module.current_module_id');
        $taxData = Helpers::getTaxSystemType(getTaxVatList: true, tax_payer: 'parcel');
        $categoryWiseTax = $taxData['categoryWiseTax'];
        $parcel_categories = ParcelCategory::
            when($module_id, function ($query) use ($module_id) {
                $query->Module($module_id);
            })
            ->with($categoryWiseTax ? ['taxVats.tax'] : [])
            ->orderBy('name')->paginate(config('default_pagination'));
        $taxVats = $taxData['taxVats'];

        return view('admin-views.parcel.category.index', compact('parcel_categories', 'categoryWiseTax', 'taxVats'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|array',
            'name.0' => 'unique:parcel_categories,name',
            'name.*' => 'max:191|unique:parcel_categories,name',
            'image' => 'required|image',
            'image' => 'required|image',
            'buy_and_deliver' => 'nullable|boolean',
            'description' => 'required|array',
            'description.0' => 'required',
            'parcel_per_km_shipping_charge' => 'required_with:parcel_minimum_shipping_charge',
            'parcel_minimum_shipping_charge' => 'required_with:parcel_per_km_shipping_charge',
            'name.0' => 'required',
            'description.0' => 'required',
        ], [
            'name.0.required' => translate('default_name_is_required'),
            'description.0.required' => translate('default_description_is_required'),
        ]);

        $parcel_category = new ParcelCategory;
        $parcel_category->module_id = Config::get('module.current_module_id');
        $parcel_category->name = $request->name[array_search('default', $request->lang)];
        $parcel_category->description = $request->description[array_search('default', $request->lang)];
        $parcel_category->image = Helpers::upload('parcel_category/', 'png', $request->file('image'));
        $parcel_category->parcel_per_km_shipping_charge = $request->parcel_per_km_shipping_charge;
        $parcel_category->parcel_minimum_shipping_charge = $request->parcel_minimum_shipping_charge;
        $parcel_category->parcel_per_km_shipping_charge = $request->parcel_per_km_shipping_charge;
        $parcel_category->parcel_minimum_shipping_charge = $request->parcel_minimum_shipping_charge;
        $parcel_category->buy_and_deliver = $request->buy_and_deliver ?? 0;
        $parcel_category->save();

        if (addon_published_status('TaxModule')) {
            $SystemTaxVat = \Modules\TaxModule\Entities\SystemTaxSetup::where('is_active', 1)->where('tax_payer', 'parcel')->where('is_default', 1)->first();
            if ($SystemTaxVat?->tax_type == 'category_wise') {

                foreach ($request['tax_ids'] ?? [] as $tax_ids) {
                    \Modules\TaxModule\Entities\Taxable::create(
                        [
                            'taxable_type' => ParcelCategory::class,
                            'taxable_id' => $parcel_category->id,
                            'system_tax_setup_id' => $SystemTaxVat->id
                            ,
                            'tax_id' => $tax_ids
                        ],
                    );
                }

            }
        }

        $data = [];
        $default_lang = str_replace('_', '-', app()->getLocale());
        foreach ($request->lang as $index => $key) {
            if ($default_lang == $key && !($request->name[$index])) {
                if ($key != 'default') {
                    array_push($data, array(
                        'translationable_type' => 'App\Models\ParcelCategory',
                        'translationable_id' => $parcel_category->id,
                        'locale' => $key,
                        'key' => 'name',
                        'value' => $parcel_category->name,
                    ));
                }
            } else {
                if ($request->name[$index] && $key != 'default') {
                    array_push($data, array(
                        'translationable_type' => 'App\Models\ParcelCategory',
                        'translationable_id' => $parcel_category->id,
                        'locale' => $key,
                        'key' => 'name',
                        'value' => $request->name[$index],
                    ));
                }
            }

            if ($default_lang == $key && !($request->description[$index])) {
                if (isset($parcel_category->description) && $key != 'default') {
                    array_push($data, array(
                        'translationable_type' => 'App\Models\ParcelCategory',
                        'translationable_id' => $parcel_category->id,
                        'locale' => $key,
                        'key' => 'description',
                        'value' => $parcel_category->description,
                    ));
                }
            } else {
                if ($request->description[$index] && $key != 'default') {
                    array_push($data, array(
                        'translationable_type' => 'App\Models\ParcelCategory',
                        'translationable_id' => $parcel_category->id,
                        'locale' => $key,
                        'key' => 'description',
                        'value' => $request->description[$index],
                    ));
                }
            }
        }
        Translation::insert($data);

        $this->saveOptions($request, $parcel_category);

        Toastr::success(translate('messages.parcel_category_added_successfully'));
        return back();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $parcel_category = ParcelCategory::withoutGlobalScope('translate')->findOrFail($id);

        $taxData = Helpers::getTaxSystemType(getTaxVatList: true, tax_payer: 'parcel');
        $categoryWiseTax = $taxData['categoryWiseTax'];
        $taxVats = $taxData['taxVats'];
        $taxVatIds = $categoryWiseTax ? $parcel_category->taxVats()->pluck('tax_id')->toArray() : [];
        return view('admin-views.parcel.category.edit', compact('parcel_category', 'categoryWiseTax', 'taxVats', 'taxVatIds'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|array',
            'name.0' => 'unique:parcel_categories,name,' . $id,
            'name.*' => 'max:191',
            'description' => 'required|array',
            'parcel_per_km_shipping_charge' => 'required_with:parcel_minimum_shipping_charge',
            'parcel_minimum_shipping_charge' => 'required_with:parcel_per_km_shipping_charge',
            'name.0' => 'required',
            'description.0' => 'required',
        ], [
            'name.0.required' => translate('default_name_is_required'),
            'description.0.required' => translate('default_description_is_required'),
        ]);

        $parcel_category = ParcelCategory::findOrFail($id);
        // $parcel_category->module_id = $request->module_id;
        $parcel_category->name = $request->name[array_search('default', $request->lang)];
        $parcel_category->description = $request->description[array_search('default', $request->lang)];
        $parcel_category->image = Helpers::update('parcel_category/', $parcel_category->image, 'png', $request->file('image'));
        $parcel_category->parcel_per_km_shipping_charge = $request->parcel_per_km_shipping_charge;
        $parcel_category->parcel_minimum_shipping_charge = $request->parcel_minimum_shipping_charge;
        $parcel_category->parcel_per_km_shipping_charge = $request->parcel_per_km_shipping_charge;
        $parcel_category->parcel_minimum_shipping_charge = $request->parcel_minimum_shipping_charge;
        $parcel_category->buy_and_deliver = $request->buy_and_deliver ?? 0;
        $parcel_category->save();
        if (addon_published_status('TaxModule') && $parcel_category['position'] == 0) {
            $taxVatIds = $parcel_category->taxVats()->pluck('tax_id')->toArray() ?? [];
            $newTaxVatIds = array_map('intval', $request['tax_ids'] ?? []);
            sort($newTaxVatIds);
            sort($taxVatIds);
            if ($newTaxVatIds != $taxVatIds) {
                $parcel_category->taxVats()->delete();
                $SystemTaxVat = \Modules\TaxModule\Entities\SystemTaxSetup::where('is_active', 1)->where('tax_payer', 'parcel')->where('is_default', 1)->first();
                if ($SystemTaxVat?->tax_type == 'category_wise') {
                    foreach ($request['tax_ids'] ?? [] as $tax_ids) {
                        \Modules\TaxModule\Entities\Taxable::create(
                            [
                                'taxable_type' => ParcelCategory::class,
                                'taxable_id' => $parcel_category->id,
                                'system_tax_setup_id' => $SystemTaxVat->id
                                ,
                                'tax_id' => $tax_ids
                            ],
                        );
                    }

                }
            }
        }
        $default_lang = str_replace('_', '-', app()->getLocale());

        foreach ($request->lang as $index => $key) {
            if ($default_lang == $key && !(isset($request->name[$index]) && $request->name[$index])) {
                if ($key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'App\Models\ParcelCategory',
                            'translationable_id' => $parcel_category->id,
                            'locale' => $key,
                            'key' => 'name'
                        ],
                        ['value' => $parcel_category->name]
                    );
                }
            } else {

                if (isset($request->name[$index]) && $request->name[$index] && $key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'App\Models\ParcelCategory',
                            'translationable_id' => $parcel_category->id,
                            'locale' => $key,
                            'key' => 'name'
                        ],
                        ['value' => $request->name[$index]]
                    );
                }
            }
            if ($default_lang == $key && !(isset($request->description[$index]) && $request->description[$index])) {
                if (isset($parcel_category->description) && $key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'App\Models\ParcelCategory',
                            'translationable_id' => $parcel_category->id,
                            'locale' => $key,
                            'key' => 'description'
                        ],
                        ['value' => $parcel_category->description]
                    );
                }

            } else {

                if (isset($request->description[$index]) && $request->description[$index] && $key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'App\Models\ParcelCategory',
                            'translationable_id' => $parcel_category->id,
                            'locale' => $key,
                            'key' => 'description'
                        ],
                        ['value' => $request->description[$index]]
                    );
                }
            }
        }

        $this->saveOptions($request, $parcel_category);

        Toastr::success(translate('messages.parcel_category_updated_successfully'));
        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $parcel_category = ParcelCategory::findOrFail($id);
        if ($parcel_category->image) {
            Helpers::check_and_delete('parcel_category/', $parcel_category['image']);
        }
        $parcel_category?->taxVats()->delete();
        $parcel_category->translations()->delete();
        $parcel_category->delete();
        Toastr::success(translate('messages.parcel_category_deleted_successfully'));
        return back();
    }

    public function status(Request $request)
    {
        $parcel_category = ParcelCategory::findOrFail($request->id);
        $parcel_category->status = $request->status;
        $parcel_category->save();
        Toastr::success(translate('messages.parcel_category_status_updated'));
        return back();
    }

    private function saveOptions(Request $request, ParcelCategory $parcelCategory)
    {
        \Illuminate\Support\Facades\Log::info('Entering saveOptions');
        if ($request->has('options')) {
            $currentOptionsIds = [];
            foreach ($request->options as $key => $optionData) {
                \Illuminate\Support\Facades\Log::info("Processing option key: $key", $optionData);
                $option = null;
                // If ID exists, update, otherwise create
                if (isset($optionData['id'])) {
                    $option = \App\Models\ParcelUserOption::find($optionData['id']);
                }

                if (!$option) {
                    $option = new \App\Models\ParcelUserOption();
                    $option->parcel_category_id = $parcelCategory->id;
                }

                $option->charge_multiplier = $optionData['charge_multiplier'] ?? 1.0;
                $option->base_price = $optionData['base_price'] ?? 0;
                $option->service_type = $optionData['service_type'] ?? null;
                $option->tag = $optionData['tag'] ?? null;
                $option->tag_color = isset($optionData['tag_color']) ? $optionData['tag_color'] : null;
                $option->status = 1; // Default active

                // Handle Icon Upload
                // The 'icon' field in $optionData comes from file input array
                // We need to check $request->file("options.$key.icon")
                if ($request->hasFile("options.$key.icon")) {
                    \Illuminate\Support\Facades\Log::info("Uploading icon for key: $key");
                    $option->icon = Helpers::update('parcel_category/', $option->icon, 'png', $request->file("options.$key.icon"));
                }

                // Handle Translations (Title & Description) - Primary (default) language
                // The view sends titles/descriptions as arrays keyed by language index but flattened?
                // View structure: name="options[{{$key}}][title][]"
                // So $optionData['title'] is an array [0 => default, 1 => other...]
                // We need to match defaults.

                $langArray = $request->lang;
                // Remove duplicates if any (though we fixed the view, let's be safe)
                $langArray = array_unique($langArray);

                $defaultLangIndex = array_search('default', $langArray);
                if ($defaultLangIndex !== false && isset($optionData['title'][$defaultLangIndex])) {
                    $option->title = $optionData['title'][$defaultLangIndex];
                }
                if ($defaultLangIndex !== false && isset($optionData['description'][$defaultLangIndex])) {
                    $option->description = $optionData['description'][$defaultLangIndex];
                }

                \Illuminate\Support\Facades\Log::info("Saving option: " . json_encode($option));
                $option->save();
                $currentOptionsIds[] = $option->id;

                // Save Translations
                foreach ($langArray as $index => $langKey) {
                    if ($langKey == 'default')
                        continue;

                    if (isset($optionData['title'][$index]) && $optionData['title'][$index]) {
                        Translation::updateOrInsert(
                            ['translationable_type' => 'App\Models\ParcelUserOption', 'translationable_id' => $option->id, 'locale' => $langKey, 'key' => 'title'],
                            ['value' => $optionData['title'][$index]]
                        );
                    }

                    if (isset($optionData['description'][$index]) && $optionData['description'][$index]) {
                        Translation::updateOrInsert(
                            ['translationable_type' => 'App\Models\ParcelUserOption', 'translationable_id' => $option->id, 'locale' => $langKey, 'key' => 'description'],
                            ['value' => $optionData['description'][$index]]
                        );
                    }
                }
            }

            // Delete removed options
            \App\Models\ParcelUserOption::where('parcel_category_id', $parcelCategory->id)
                ->whereNotIn('id', $currentOptionsIds)
                ->delete();
        }
    }
}
