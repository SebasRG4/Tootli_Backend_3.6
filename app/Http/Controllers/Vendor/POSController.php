<?php

namespace App\Http\Controllers\Vendor;

use App\Models\Item;
use App\Models\Order;
use App\Models\Vendor;
use App\Models\VendorEmployee;
use App\Models\StorePosCustomer;
use App\Models\Store;
use App\Mail\PlaceOrder;
use App\Models\Category;
use App\Models\DMVehicle;
use App\Scopes\StoreScope;
use App\Models\OrderDetail;
use App\Traits\PlaceNewOrder;
use Illuminate\Http\Request;
use App\CentralLogics\Helpers;
use App\Models\BusinessSetting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\CentralLogics\ProductLogic;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
class POSController extends Controller
{
    use PlaceNewOrder;

    /**
     * Consulta base de productos para el POS (categoría + palabras en nombre).
     */
    protected function posProductsBaseQuery(Request $request)
    {
        $category = (int) $request->input('category_id', 0);
        $keywordRaw = $request->input('keyword');
        $keyword = ($keywordRaw !== null && $keywordRaw !== '') ? $keywordRaw : false;
        $key = explode(' ', $keyword ?: '');

        $store_id = Helpers::get_store_data()->id;

        return Item::active()
            ->where('store_id', $store_id)
            ->when($category, function ($query) use ($category) {
                $query->whereHas('category', function ($q) use ($category) {
                    return $q->whereId($category)->orWhere('parent_id', $category);
                });
            })
            ->when($keyword, function ($query) use ($key) {
                return $query->where(function ($q) use ($key) {
                    foreach ($key as $value) {
                        $q->orWhere('name', 'like', "%{$value}%")
                          ->orWhere('barcode', 'like', "%{$value}%");
                    }
                });
            })
            ->latest();
    }

    /**
     * Auto-login desde la app móvil del vendedor usando su API token.
     * Crea una sesión web y redirige al POS.
     */
    public function appLaunch(Request $request)
    {
        $token = $request->bearerToken() ?: $request->query('token');

        if (!$token) {
            return redirect()->route('home')->withErrors(['Acceso no autorizado.']);
        }

        // Intentar como vendedor principal
        $vendor = Vendor::where('auth_token', $token)->first();
        if ($vendor) {
            Auth::guard('vendor')->login($vendor);
            $newToken = $vendor->login_remember_token ?? Str::random(60);
            $vendor->login_remember_token = $newToken;
            $vendor->save();
            session(['login_remember_token' => $newToken]);
            return redirect()->route('vendor.pos.index');
        }

        // Intentar como empleado de tienda
        $employee = VendorEmployee::where('auth_token', $token)->first();
        if ($employee) {
            Auth::guard('vendor_employee')->login($employee);
            $newToken = $employee->login_remember_token ?? Str::random(60);
            $employee->login_remember_token = $newToken;
            $employee->save();
            session(['login_remember_token' => $newToken]);
            return redirect()->route('vendor.pos.index');
        }

        return redirect()->route('home')->withErrors(['Token inválido.']);
    }

    public function tc_order_init(Request $request)
    {
        $address = [
            'contact_person_name' => $request->tc_name,
            'contact_person_number' => $request->tc_phone,
            'address' => $request->tc_address,
            'floor' => '',
            'road' => '',
            'house' => '',
            'longitude' => (string) $request->tc_lng,
            'latitude' => (string) $request->tc_lat,
            'address_type' => 'delivery',
            'distance' => 0,
            'delivery_fee' => 0,
        ];
        session()->put('address', $address);

        return redirect()->route('vendor.pos.index');
    }

    public function index(Request $request)
    {
        $category = (int) $request->input('category_id', 0);
        $store_data = Helpers::get_store_data();
        $categories = Category::active()
            ->module($store_data->module_id)
            ->whereHas('products', function ($q) use ($store_data) {
                $q->where('store_id', $store_data->id)->active();
            })
            ->get();
        $keywordRaw = $request->input('keyword');
        $keyword = ($keywordRaw !== null && $keywordRaw !== '') ? $keywordRaw : false;
        $store = Store::find($store_data->module_id);
        $products = $this->posProductsBaseQuery($request)->paginate(10)->withQueryString();

        return view('vendor-views.pos.index', compact('categories', 'products', 'store', 'category', 'keyword'));
    }

    /**
     * Fragmento HTML del grid de productos POS (sin recargar la página).
     */
    public function products_grid(Request $request)
    {
        $store_data = Helpers::get_store_data();
        $append = [];
        if ($request->filled('keyword')) {
            $append['keyword'] = $request->input('keyword');
        }
        if ((int) $request->input('category_id', 0) > 0) {
            $append['category_id'] = (int) $request->input('category_id');
        }

        $products = $this->posProductsBaseQuery($request)
            ->paginate(10)
            ->withPath(route('vendor.pos.products-grid', [], false))
            ->appends($append);

        $html = view('vendor-views.pos._products_grid', compact('products', 'store_data'))->render();

        return response()->json(['success' => true, 'html' => $html]);
    }

    public function quick_view(Request $request)
    {
        $product = Item::findOrFail($request->product_id);

        return response()->json([
            'success' => 1,
            'view' => view('vendor-views.pos._quick-view-data', compact('product'))->render(),
        ]);
    }

    public function quick_view_card_item(Request $request)
    {
        $product = Item::findOrFail($request->product_id);
        $item_key = $request->item_key;
        $cart_item = session()->get('cart')[$item_key];

        return response()->json([
            'success' => 1,
            'view' => view('vendor-views.pos._quick-view-cart-item', compact('product', 'cart_item', 'item_key'))->render(),
        ]);
    }

    public function setDirectMode(Request $request)
    {
        // POS interno: siempre opera con precio de menu (direct).
        Toastr::info(translate('messages.tootli_direct_pos_enabled'));

        return back();
    }

    public function variant_price(Request $request)
    {
        $product = Item::find($request->id);
        $pos_direct = true;
        $base_app = (float) $product->price;
        $base_unit = $pos_direct ? Helpers::item_price_for_context($product, 'direct') : $base_app;

        if($product->module->module_type == 'food' && $product->food_variations){
            $price = $base_unit;
            $addon_price = 0;
            if ($request['addon_id']) {
                foreach ($request['addon_id'] as $id) {
                    $addon_price += $request['addon-price' . $id] * $request['addon-quantity' . $id];
                }
            }
            $product_variations = json_decode($product->food_variations, true);
            if ($request->variations && $product_variations && count($product_variations)) {

                $price_total =  $price + Helpers::food_variation_price($product_variations, $request->variations);
                $price= $price_total - Helpers::product_discount_calculate($product, $price_total, $product->store)['discount_amount'];
            } else {
                $price = $base_unit - Helpers::product_discount_calculate($product, $base_unit, $product->store)['discount_amount'];
            }
        }else{

            $str = '';
            $quantity = 0;
            $price = 0;
            $addon_price = 0;

            foreach (json_decode($product->choice_options) as $key => $choice) {
                if ($str != null) {
                    $str .= '-' . str_replace(' ', '', $request[$choice->name]);
                } else {
                    $str .= str_replace(' ', '', $request[$choice->name]);
                }
            }

            if($request['addon_id'])
            {
                foreach($request['addon_id'] as $id)
                {
                    $addon_price+= $request['addon-price'.$id]*$request['addon-quantity'.$id];
                }
            }

            if ($str != null) {
                $count = count(json_decode($product->variations));
                for ($i = 0; $i < $count; $i++) {
                    if (json_decode($product->variations)[$i]->type == $str) {
                        $vp = (float) json_decode($product->variations)[$i]->price;
                        $line = $pos_direct
                            ? ($vp - $base_app + Helpers::item_price_for_context($product, 'direct'))
                            : $vp;
                        $price = $line - Helpers::product_discount_calculate($product, $line, Helpers::get_store_data())['discount_amount'];
                    }
                }
            } else {
                $price = $base_unit - Helpers::product_discount_calculate($product, $base_unit, Helpers::get_store_data())['discount_amount'];
            }
        }

        return array('price' => Helpers::format_currency(($price * $request->quantity)+$addon_price));
    }

    public function addDeliveryInfo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'contact_person_name' => 'required',
            'contact_person_number' => 'required',
            'floor' => 'nullable|string|max:191',
            'road' => 'required|string|max:191',
            'house' => 'nullable|string|max:191',
            'longitude' => 'required',
            'latitude' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)]);
        }

        $address = [
            'contact_person_name' => $request->contact_person_name,
            'contact_person_number' => $request->contact_person_number,
            'address_type' => 'delivery',
            'address' => $request->address,
            'floor' => $request->input('floor', '') ?? '',
            'road' => $request->road,
            'house' => $request->input('house', '') ?? '',
            'distance' => $request->distance??0,
            'delivery_fee' => $request->delivery_fee?:0,
            'original_delivery_fee' => $request->filled('original_delivery_fee') ? $request->original_delivery_fee : ($request->delivery_fee ?: 0),
            'longitude' => (string)$request->longitude,
            'latitude' => (string)$request->latitude,
        ];

        $request->session()->put('address', $address);

        if ($request->filled('internal_customer_id')) {
            $ic = StorePosCustomer::where('store_id', Helpers::get_store_data()->id)
                ->whereKey($request->internal_customer_id)
                ->first();
            if ($ic) {
                $ic->delivery_address = $address;
                $ic->save();
            }
        }

        return response()->json([
            'data' => $address,
            'view' => view('vendor-views.pos._address', compact('address'))->render(),
        ]);
    }

    /**
     * Resuelve un enlace de Google Maps (incl. cortos tipo maps.app.goo.gl) y devuelve lat/lng para el POS.
     */
    public function resolveGoogleMapsLink(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'url' => 'required|string|max:2048',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 422);
        }

        $url = trim($request->input('url'));
        $parsedUrl = parse_url($url);
        $scheme = strtolower($parsedUrl['scheme'] ?? '');
        if ($scheme !== 'https' && $scheme !== 'http') {
            return response()->json(['errors' => [['code' => 'url', 'message' => translate('messages.invalid_maps_link')]]], 422);
        }

        $host = strtolower($parsedUrl['host'] ?? '');
        if (! $this->isAllowedGoogleMapsLinkHost($host)) {
            return response()->json(['errors' => [['code' => 'url', 'message' => translate('messages.invalid_maps_link')]]], 422);
        }

        $finalUrl = $url;
        if (preg_match('/(goo\.gl|maps\.app\.goo\.gl)/i', $host)) {
            $finalUrl = $this->followRedirectsToFinalUrl($url);
            if ($finalUrl === $url) {
                return response()->json(['errors' => [['code' => 'url', 'message' => translate('messages.maps_link_resolve_failed')]]], 422);
            }
            $finalHost = strtolower((string) parse_url($finalUrl, PHP_URL_HOST));
            if (! $this->isAllowedResolvedGoogleMapsHost($finalHost)) {
                return response()->json(['errors' => [['code' => 'url', 'message' => translate('messages.invalid_maps_link')]]], 422);
            }
        }

        $parsed = $this->parseLatLngFromGoogleMapsUrlString($finalUrl);
        if ($parsed === null && $finalUrl !== $url) {
            $parsed = $this->parseLatLngFromGoogleMapsUrlString($url);
        }
        if ($parsed === null) {
            return response()->json(['errors' => [['code' => 'url', 'message' => translate('messages.maps_link_no_coordinates')]]], 422);
        }

        if ($parsed['lat'] < -90 || $parsed['lat'] > 90 || $parsed['lng'] < -180 || $parsed['lng'] > 180) {
            return response()->json(['errors' => [['code' => 'url', 'message' => translate('messages.maps_link_no_coordinates')]]], 422);
        }

        return response()->json(['lat' => $parsed['lat'], 'lng' => $parsed['lng']]);
    }

    /**
     * Carga en sesión la dirección guardada del cliente interno (Tootli Direct POS).
     */
    public function loadInternalCustomerAddress(Request $request)
    {
        $store = Helpers::get_store_data();
        $id = $request->input('internal_customer_id');

        if ($request->boolean('prefill_contact_only')) {
            if ($id === null || $id === '') {
                return response()->json(['form' => null]);
            }
            $customer = StorePosCustomer::where('store_id', $store->id)->whereKey($id)->first();
            if (! $customer) {
                return response()->json(['form' => null]);
            }
            $name = trim($customer->f_name.' '.($customer->l_name ?? ''));

            return response()->json([
                'form' => [
                    'contact_person_name' => $name,
                    'contact_person_number' => $customer->phone,
                    'phone' => $customer->phone,
                ],
            ]);
        }

        if ($id === null || $id === '') {
            session()->forget('address');

            return response()->json([
                'view' => view('vendor-views.pos._address')->render(),
                'form' => null,
            ]);
        }

        $customer = StorePosCustomer::where('store_id', $store->id)->whereKey($id)->first();
        if (! $customer) {
            session()->forget('address');

            return response()->json([
                'view' => view('vendor-views.pos._address')->render(),
                'form' => null,
            ]);
        }

        $saved = $customer->delivery_address;
        if (! is_array($saved) || count($saved) === 0) {
            session()->forget('address');
            $name = trim($customer->f_name.' '.($customer->l_name ?? ''));

            return response()->json([
                'view' => view('vendor-views.pos._address')->render(),
                'form' => [
                    'contact_person_name' => $name,
                    'contact_person_number' => $customer->phone,
                    'phone' => $customer->phone,
                ],
            ]);
        }

        $address = $this->mergeInternalCustomerContactIntoAddress($customer, $saved);
        session()->put('address', $address);

        return response()->json([
            'view' => view('vendor-views.pos._address')->render(),
            'form' => $address,
        ]);
    }

    private function mergeInternalCustomerContactIntoAddress(StorePosCustomer $customer, array $address): array
    {
        $name = trim($customer->f_name.' '.($customer->l_name ?? ''));
        if ($name !== '') {
            $address['contact_person_name'] = $name;
        }
        if ($customer->phone) {
            $address['contact_person_number'] = $customer->phone;
            $address['phone'] = $customer->phone;
        }

        return $address;
    }

    private function get_stocks($product,$selected_item){
        try {
            if($product->module->module_type == 'food'){
                return null;
            }
            $choice_options=   json_decode($product?->choice_options, true);
            $variation=  json_decode($product?->variations, true);

            if(is_array($choice_options) && is_array($variation)  &&  count($choice_options) == 0 && count($variation) == 0 ){
                return $product->stock ?? null ;
            }

            $choiceNames = array_column($choice_options, 'name');
            $variations = array_map(function ($choiceName) use ($selected_item) {
                return str_replace(' ', '', $selected_item[$choiceName]);
            }, $choiceNames);
            $resultString = implode('-', $variations);
            $stockVariations = json_decode($product->variations, true);
            foreach ($stockVariations as $variation) {
                if ($variation['type'] == $resultString) {
                    $stock = $variation['stock'];
                    break;
                }
            }
        } catch (\Throwable $th) {
            info($th->getMessage());
        }

        return $stock ?? null ;
    }
    public function addToCart(Request $request)
    {
        $product = Item::find($request->id);
        $pos_direct = true;
        $base_app = (float) $product->price;

        if($product->module->module_type == 'food' && $product->food_variations){
        $data = array();
        $data['id'] = $product->id;
        $str = '';
        $variations = [];
        $price = 0;
        $addon_price = 0;
        $variation_price=0;

        $product_variations = json_decode($product->food_variations, true);
        if ($request->variations && $product_variations && count($product_variations)) {
            foreach($request->variations  as $key=> $value ){

                if($value['required'] == 'on' &&  isset($value['values']) == false){
                    return response()->json([
                        'data' => 'variation_error',
                        'message' => translate('Please select items from') . ' ' . $value['name'],
                    ]);
                }
                if(isset($value['values'])  && $value['min'] != 0 && $value['min'] > count($value['values']['label'])){
                    return response()->json([
                        'data' => 'variation_error',
                        'message' => translate('Please select minimum ').$value['min'].translate(' For ').$value['name'].'.',
                    ]);
                }
                if(isset($value['values']) && $value['max'] != 0 && $value['max'] < count($value['values']['label'])){
                    return response()->json([
                        'data' => 'variation_error',
                        'message' => translate('Please select maximum ').$value['max'].translate(' For ').$value['name'].'.',
                    ]);
                }
            }
            $variation_data = Helpers::get_varient($product_variations, $request->variations);
            $variation_price = $variation_data['price'];
            $variations = $request->variations;
        }

        $data['variations'] = $variations;
        $data['variant'] = $str;

        $unit_base = $pos_direct ? Helpers::item_price_for_context($product, 'direct') : $base_app;
        $price = $unit_base + $variation_price;
        $data['variation_price'] = $variation_price;

        $data['quantity'] = $request['quantity'];
        $data['price'] = $price;
        $data['name'] = $product->name;
        $data['discount'] = Helpers::product_discount_calculate($product, $price,Helpers::get_store_data())['discount_amount'];
        $data['image'] = $product->image;
        $data['image_full_url'] = $product->image_full_url;
        $data['storage'] = $product->storage?->toArray();
        $data['add_ons'] = [];
        $data['add_on_qtys'] = [];
        $data['maximum_cart_quantity'] = $product->maximum_cart_quantity;

        if($request['addon_id'])
        {
            foreach($request['addon_id'] as $id)
            {
                $addon_price+= $request['addon-price'.$id]*$request['addon-quantity'.$id];
                $data['add_on_qtys'][]=$request['addon-quantity'.$id];
            }
            $data['add_ons'] = $request['addon_id'];
        }

        $data['addon_price'] = $addon_price;

        if ($request->session()->has('cart')) {
            $cart = $request->session()->get('cart', collect([]));
            if(isset($request->cart_item_key))
            {
                $cart[$request->cart_item_key] = $data;
                $data = 2;
            }
            else
            {
                $cart->push($data);
            }

        } else {
            $cart = collect([$data]);
            $request->session()->put('cart', $cart);
        }
    }else{

        $data = array();
        $data['id'] = $product->id;
        $str = '';
        $variations = [];
        $price = 0;
        $addon_price = 0;


            $selected_item = $request->all();
            $stock= $this->get_stocks($product,$selected_item);
            if($product?->maximum_cart_quantity > 0){
            if(((isset($stock) && min($stock, $product?->maximum_cart_quantity) < $request->quantity )||  $product?->maximum_cart_quantity <  $request->quantity  ) ){
                    return response()->json([
                        'data' => 0
                    ]);
                }
            }


        //Gets all the choice values of customer choice option and generate a string like Black-S-Cotton
        foreach (json_decode($product->choice_options) as $key => $choice) {
            $data[$choice->name] = $request[$choice->name];
            $variations[$choice->title] = $request[$choice->name];
            if ($str != null) {
                $str .= '-' . str_replace(' ', '', $request[$choice->name]);
            } else {
                $str .= str_replace(' ', '', $request[$choice->name]);
            }
        }
        $data['variations'] = $variations;
        $data['variant'] = $str;
        if ($request->session()->has('cart') && !isset($request->cart_item_key)) {
            if (count($request->session()->get('cart')) > 0) {
                foreach ($request->session()->get('cart') as $key => $cartItem) {
                    if (is_array($cartItem) && $cartItem['id'] == $request['id'] && $cartItem['variant'] == $str) {
                        return response()->json([
                            'data' => 1
                        ]);
                    }
                }

            }
        }
        //Check the string and decreases quantity for the stock
        if ($str != null) {
            $count = count(json_decode($product->variations));
            for ($i = 0; $i < $count; $i++) {
                if (json_decode($product->variations)[$i]->type == $str) {
                    $vp = (float) json_decode($product->variations)[$i]->price;
                    $price = $pos_direct
                        ? ($vp - $base_app + Helpers::item_price_for_context($product, 'direct'))
                        : $vp;
                    $data['variations'] = json_decode($product->variations, true)[$i];
                }
            }
        } else {
            $price = $pos_direct ? Helpers::item_price_for_context($product, 'direct') : $base_app;
        }

        $data['quantity'] = $request['quantity'];
        $data['price'] = $price;
        $data['name'] = $product->name;
        $data['discount'] = Helpers::product_discount_calculate($product, $price,Helpers::get_store_data())['discount_amount'];
        $data['image'] = $product->image;
        $data['image_full_url'] = $product->image_full_url;
        $data['storage'] = $product->storage?->toArray();
        $data['add_ons'] = [];
        $data['add_on_qtys'] = [];
        $data['maximum_cart_quantity'] = $product->maximum_cart_quantity;

        if($request['addon_id'])
        {
            foreach($request['addon_id'] as $id)
            {
                $addon_price+= $request['addon-price'.$id]*$request['addon-quantity'.$id];
                $data['add_on_qtys'][]=$request['addon-quantity'.$id];
            }
            $data['add_ons'] = $request['addon_id'];
        }

        $data['addon_price'] = $addon_price;

        if ($request->session()->has('cart')) {
            $cart = $request->session()->get('cart', collect([]));
            if(isset($request->cart_item_key))
            {
                $cart[$request->cart_item_key] = $data;
                $data = 2;
            }
            else
            {
                $cart->push($data);
            }

        } else {
            $cart = collect([$data]);
            $request->session()->put('cart', $cart);
        }
    }

        $this->setPosCalculatedTax($product->store);
        return response()->json([
            'data' => $data
        ]);
    }

    public function cart_items()
    {
        return view('vendor-views.pos._cart');
    }

    //removes from Cart
    public function removeFromCart(Request $request)
    {
        if ($request->session()->has('cart')) {
            $cart = $request->session()->get('cart', collect([]));
            $item_id = $cart[$request->key]['id'];
            $cart->forget($request->key);
            $request->session()->put('cart', $cart);
        }

        $product = Item::withoutGlobalScope(StoreScope::class)->with('store')->find($item_id);
        if ($product && $product->store) {
            $this->setPosCalculatedTax($product->store);
        }

        return response()->json([],200);
    }

    //updated the quantity for a cart item
    public function updateQuantity(Request $request)
    {
        $cart = $request->session()->get('cart', collect([]));
        $cart = $cart->map(function ($object, $key) use ($request) {
            if ($key == $request->key) {
                $object['quantity'] = $request->quantity;
            }
            return $object;
        });

        $request->session()->put('cart', $cart);

        try {
            $product_id = $cart[$request->key]['id'];
            $product = Item::withoutGlobalScope(StoreScope::class)->with('store')->find($product_id);
            if ($product && $product->store) {
                $this->setPosCalculatedTax($product->store);
            }
        } catch (\Exception $e) {
            \Log::error('Failed to recalculate tax after quantity update: ' . $e->getMessage());
        }

        return response()->json([],200);
    }

    //empty Cart
    public function emptyCart(Request $request)
    {
        session()->forget('cart');
        session()->forget('tax_amount');
        session()->forget('tax_included');
        session()->forget('address');
        return response()->json([],200);
    }

    public function update_tax(Request $request)
    {
        $cart = $request->session()->get('cart', collect([]));
        $cart['tax'] = $request->tax;
        $request->session()->put('cart', $cart);
        return back();
    }

    public function update_discount(Request $request)
    {
        $cart = $request->session()->get('cart', collect([]));
        $cart['discount'] = $request->discount;
        $cart['discount_type'] = $request->type;
        $request->session()->put('cart', $cart);
        return back();
    }

    public function update_paid(Request $request)
    {
        $cart = $request->session()->get('cart', collect([]));
        $cart['paid'] = $request->paid;
        $request->session()->put('cart', $cart);
        return back();
    }

    public function get_customers(Request $request)
    {
        $store = Helpers::get_store_data();
        $key = array_values(array_filter(explode(' ', trim((string) $request->input('q', '')))));

        $data = collect();

        $internalsQuery = StorePosCustomer::where('store_id', $store->id)->latest();
        if (count($key) > 0) {
            $internalsQuery->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere(function ($qq) use ($value) {
                        $qq->where('f_name', 'like', "%{$value}%")
                            ->orWhere('l_name', 'like', "%{$value}%")
                            ->orWhere('phone', 'like', "%{$value}%");
                    });
                }
            });
        }
        foreach ($internalsQuery->limit(16)->get() as $ic) {
            $name = trim($ic->f_name.' '.($ic->l_name ?? ''));
            $data->push((object) [
                'id' => 'internal:'.$ic->id,
                'text' => $name.' ('.$ic->phone.') — '.translate('messages.store_internal_customer'),
            ]);
        }

        $data->push((object) ['id' => false, 'text' => translate('messages.walk_in_customer')]);

        return response()->json($data->reverse()->values()->all());
    }

    public function place_order(Request $request)
    {
        if (! $request->type) {
            Toastr::error(translate('No payment method selected'));
            return back();
        }

        if ($request->session()->has('cart')) {
            if (count($request->session()->get('cart')) < 1) {
                Toastr::error(translate('messages.cart_empty_warning'));
                return back();
            }
        } else {
            Toastr::error(translate('messages.cart_empty_warning'));
            return back();
        }

        $userIdRaw = $request->input('user_id');
        if (is_string($userIdRaw) && preg_match('/^\s*internal:(\d+)\s*$/', $userIdRaw, $m)) {
            $request->merge([
                'user_id' => null,
                'internal_customer_id' => $m[1],
            ]);
        }

        $has_address = $request->session()->has('address') && is_array($request->session()->get('address'))
            && count($request->session()->get('address')) > 0;
        $tootli_pos_direct = $has_address;

        $allowed_types = $has_address
            ? ['cash_on_delivery', 'card_tootli_direct', 'paid_at_restaurant']
            : ['cash', 'card_in_store', 'bank_transfer_in_store'];

        if (! in_array($request->type, $allowed_types, true)) {
            Toastr::error(translate('messages.invalid_payment_method'));
            return back();
        }
        if (in_array($request->type, ['card_in_store', 'card_tootli_direct'], true)) {
            $request->validate([
                'card_fee_percent' => 'required|numeric|min:0|max:100',
                'card_fee_vat_percent' => 'required|numeric|min:0|max:100',
                'card_gross_amount' => 'required|numeric|min:0',
            ]);
        }
        $selected_service_type = in_array($request->input('service_type'), ['take_away', 'dine_in'], true)
            ? $request->input('service_type')
            : 'take_away';

        $address = null;
        if ($has_address) {
            if (! $request->user_id && ! $request->internal_customer_id) {
                Toastr::error(translate('messages.no_customer_selected'));
                return back();
            }
            $address = $request->session()->get('address');
        }

        if ($tootli_pos_direct && (! $address || (! $request->user_id && ! $request->internal_customer_id))) {
            Toastr::error(translate('messages.tootli_direct_pos_requires_address_and_customer'));
            return back();
        }

        if ($request->filled('user_id') && $request->filled('internal_customer_id')) {
            Toastr::error(translate('messages.invalid_customer'));
            return back();
        }

        $distance_data = isset($address) ? $address['distance'] : 0;

        $store = Helpers::get_store_data();

        $internal_customer = null;
        if ($request->filled('internal_customer_id')) {
            $internal_customer = StorePosCustomer::where('store_id', $store->id)
                ->whereKey($request->internal_customer_id)
                ->first();
            if (! $internal_customer) {
                Toastr::error(translate('messages.invalid_customer'));
                return back();
            }
        }

        if ($internal_customer && is_array($address)) {
            if (empty(trim((string) ($address['contact_person_name'] ?? '')))) {
                $address['contact_person_name'] = trim($internal_customer->f_name.' '.($internal_customer->l_name ?? ''));
            }
            if (empty(trim((string) ($address['contact_person_number'] ?? '')))) {
                $address['contact_person_number'] = $internal_customer->phone;
            }
        }

        $self_delivery_status = $store->self_delivery_system;
        $store_sub=$store?->store_sub;
        if ($store->is_valid_subscription) {

            $self_delivery_status = $store_sub->self_delivery;

            if($store_sub->max_order != "unlimited" && $store_sub->max_order <= 0){
                Toastr::error(translate('messages.you_have_reached_the_maximum_number_of_orders'));
                return back();
            }
        } elseif($store->store_business_model == 'unsubscribed'){
            Toastr::error(translate('messages.you_are_not_subscribed_or_subscription_has_expired'));
            return back();
        }


        $extra_charges = 0;
        $vehicle_id = null;


        if($self_delivery_status != 1){

            $data =  DMVehicle::where(function ($query) use ($distance_data) {
                $query->where('starting_coverage_area', '<=', $distance_data)->where('maximum_coverage_area', '>=', $distance_data)
                ->orWhere(function ($query) use ($distance_data) {
                    $query->where('starting_coverage_area', '>=', $distance_data);
                });
            })
            ->active()
                ->orderBy('starting_coverage_area')->first();

            $extra_charges = (float) (isset($data) ? $data->extra_charges  : 0);
            $vehicle_id = (isset($data) ? $data->id  : null);
        }


        $cart = $request->session()->get('cart');

        $total_addon_price = 0;
        $product_price = 0;
        $store_discount_amount = 0;

        $order_details = [];
        $product_data = [];

        $order = new Order();
        $order->id = 100000 + Order::count() + 1;
        if (Order::find($order->id)) {
            $order->id = Order::latest()->first()->id + 1;
        }
        $order->tootli_direct = $tootli_pos_direct;
        if (isset($address)) {
            $order->payment_status = $request->type === 'paid_at_restaurant' ? 'paid' : 'unpaid';
        } else {
            $order->payment_status = 'paid';
        }
        if ($request->user_id || $internal_customer) {

            $order->order_status = isset($address)?'confirmed':'delivered';
            $order->order_type = isset($address) ? 'delivery' : $selected_service_type;
        }else{
            $order->order_status = 'delivered';
            $order->order_type = $selected_service_type;
        }
        if(in_array($order->order_type, ['take_away', 'dine_in'], true)){
            $order->delivered = now();
        }
        $order->distance = isset($address) ? $address['distance'] : 0;
        $order->payment_method = $request->type;
        $order->store_id = $store->id;
        $order->module_id = Helpers::get_store_data()->module_id;
        if ($internal_customer) {
            $order->user_id = null;
            $order->is_guest = true;
            $order->store_pos_customer_id = $internal_customer->id;
        } elseif ($tootli_pos_direct && $request->filled('user_id')) {
            // Tootli Direct: aunque elijan un usuario de la app en el POS, el pedido va como invitado (sin user_id).
            $order->user_id = null;
            $order->is_guest = true;
            $order->store_pos_customer_id = null;
        } else {
            $order->user_id = $request->user_id;
            $order->is_guest = false;
            $order->store_pos_customer_id = null;
        }

        if ($tootli_pos_direct && $address) {
            $cust = (float) ($address['delivery_fee'] ?? 0);
            $orig = isset($address['original_delivery_fee']) && $address['original_delivery_fee'] !== '' && $address['original_delivery_fee'] !== null
                ? (float) $address['original_delivery_fee']
                : $cust;
            $order->delivery_charge = $cust;
            $order->original_delivery_charge = max($orig, $cust);
        } else {
            $order->delivery_charge = isset($address)?$address['delivery_fee']:0;
            $order->original_delivery_charge = isset($address)?$address['delivery_fee']:0;
        }
        $order->delivery_address = isset($address)?json_encode($address):null;
        $order->dm_vehicle_id = $vehicle_id;
        $order->checked = 1;
        $order->created_at = now();
        $order->schedule_at = now();
        $order->updated_at = now();
        $order->zone_id = $store->zone_id;
        $order->otp = rand(1000, 9999);

        $additionalCharges = [];
        $settings = BusinessSetting::whereIn('key', [
            'additional_charge_status',
            'additional_charge',
            'extra_packaging_data',
        ])->pluck('value', 'key');

        $additional_charge_status  = $settings['additional_charge_status'] ?? null;
        $additional_charge         = $settings['additional_charge'] ?? null;

        // if ($additional_charge_status == 1) {
        //     $additionalCharges['tax_on_additional_charge'] = $additional_charge ?? 0;
        // }

        $order_details = $this->makePosOrderDetails($cart, null, $store);

        if (data_get($order_details, 'status_code') === 403) {
            DB::rollBack();
            return response()->json([
                'errors' => [
                    ['code' => data_get($order_details, 'code'), 'message' => data_get($order_details, 'message')]
                ]
            ], data_get($order_details, 'status_code'));
        }

        $total_addon_price = $order_details['total_addon_price'];
        $product_price = $order_details['product_price'];
        $store_discount_amount = $order_details['store_discount_amount'];
        $flash_sale_admin_discount_amount = $order_details['flash_sale_admin_discount_amount'];
        $flash_sale_vendor_discount_amount = $order_details['flash_sale_vendor_discount_amount'];
        $product_data = $order_details['product_data'];
        $order_details = $order_details['order_details'];

        $total_price = $product_price + $total_addon_price - $store_discount_amount - $flash_sale_admin_discount_amount - $flash_sale_vendor_discount_amount;
        $totalDiscount = $store_discount_amount + $flash_sale_admin_discount_amount + $flash_sale_vendor_discount_amount;
        $finalCalculatedTax =  Helpers::getFinalCalculatedTax($order_details, $additionalCharges, $totalDiscount, $total_price, $store->id);
        $order->flash_admin_discount_amount = round($flash_sale_admin_discount_amount, config('round_up_to_digit'));
        $order->flash_store_discount_amount = round($flash_sale_vendor_discount_amount, config('round_up_to_digit'));

        $tax_amount = $finalCalculatedTax['tax_amount'];
        $tax_status = $finalCalculatedTax['tax_status'];
        $taxMap = $finalCalculatedTax['taxMap'];
        $orderTaxIds = data_get($finalCalculatedTax ,'taxData.orderTaxIds',[] );
        $taxType=  data_get($finalCalculatedTax ,'taxType');
        $order->tax_type = $taxType;
        $order->tax_status = $tax_status;

        try {
            $order->store_discount_amount= $store_discount_amount;
            $order->tax_percentage = 0;
            $order->total_tax_amount = $tax_amount;
            $order->order_amount = $total_price + $tax_amount + $order->delivery_charge;
            if (in_array($request->type, ['card_in_store', 'bank_transfer_in_store', 'card_tootli_direct', 'paid_at_restaurant'], true)) {
                $order->adjusment = 0;
            } else {
                $paid_in = (float) ($request->amount ?? 0);
                $order->adjusment = $paid_in - ($total_price + $tax_amount + $order->delivery_charge);
            }
            $order->payment_method = $request->type;
            $card_gross = null;
            $card_fee = null;
            $card_net = null;
            $card_fee_percent = null;
            $card_fee_vat_percent = null;
            if (in_array($request->type, ['card_in_store', 'card_tootli_direct'], true)) {
                $card_fee_percent = (float) $request->input('card_fee_percent', 0);
                $card_fee_vat_percent = (float) $request->input('card_fee_vat_percent', 0);
                $card_gross = (float) $request->input('card_gross_amount', $order->order_amount);
                $base_fee_rate = $card_fee_percent / 100;
                $vat_rate = $card_fee_vat_percent / 100;
                $effective_rate = $base_fee_rate + ($base_fee_rate * $vat_rate);
                $card_fee = round($card_gross * $effective_rate, 2);
                $card_net = round($card_gross - $card_fee, 2);
            }
            $order->pos_payment_meta = json_encode([
                'type' => $request->type,
                'receiver' => in_array($request->type, ['card_tootli_direct', 'cash_on_delivery'], true) ? 'tootli' : 'store',
                'card_fee_percent' => $card_fee_percent,
                'card_fee_vat_percent' => $card_fee_vat_percent,
                'card_gross_amount' => $card_gross,
                'card_fee_amount' => $card_fee,
                'card_net_amount' => $card_net,
            ]);
            if ($order->order_type === 'delivery' && $order->order_status === 'confirmed') {
                $order->confirmed = now();
                $order->pending = now();
            }
            $order->save();

            if ($request->order_type !== 'parcel') {
                $taxMapCollection = collect($taxMap);
                foreach ($order_details as $key => $item) {
                    $order_details[$key]['order_id'] = $order->id;

                    if ($item['item_id']) {
                        $item_id = $item['item_id'];
                    } else {
                        $item_id = $item['item_campaign_id'];
                    }
                    $index = $taxMapCollection->search(function ($tax) use ($item_id) {
                        return $tax['product_id'] == $item_id;
                    });
                    if ($index !== false) {
                        $matchedTax = $taxMapCollection->pull($index);
                        $order_details[$key]['tax_status'] = $matchedTax['include'] == 1 ? 'included' : 'excluded';
                        $order_details[$key]['tax_amount'] = $matchedTax['totalTaxamount'];
                    }
                }

                OrderDetail::insert($order_details);
                if (count($orderTaxIds)) {
                    \Modules\TaxModule\Services\CalculateTaxService::updateOrderTaxData(
                        orderId: $order->id,
                        orderTaxIds: $orderTaxIds,
                    );
                }
                if (count($product_data) > 0) {
                    foreach ($product_data as $item) {
                        ProductLogic::update_stock($item['item'], $item['quantity'], $item['variant'])->save();
                        ProductLogic::update_flash_stock($item['item'], $item['quantity'])?->save();
                    }
                }
                $store->increment('total_order');
            }

            session()->forget('cart');
            session()->forget('tax_amount');
            session()->forget('tax_include');
            if ($internal_customer && isset($address) && is_array($address)) {
                $internal_customer->delivery_address = $address;
                $internal_customer->save();
                $internal_customer->refresh();
                $saved = $internal_customer->delivery_address;
                if (is_array($saved) && count($saved) > 0) {
                    session()->put('address', $this->mergeInternalCustomerContactIntoAddress($internal_customer, $saved));
                }
            } else {
                session()->forget('address');
            }
            session(['last_order' => $order->id]);
            if ($order->order_status === 'confirmed') {
                $should_notify = (bool) $order->user_id
                    || ($order->order_type === 'delivery' && $tootli_pos_direct);
                if ($should_notify) {
                    $order->load(['store', 'store.vendor', 'zone', 'module', 'delivery_man']);
                    Helpers::send_order_notification($order);
                }
                if ($order->user) {
                    $mail_status = Helpers::get_mail_status('place_order_mail_status_user');
                    try {
                        if ($order->order_status == 'pending' && config('mail.status') && $mail_status == '1' && Helpers::getNotificationStatusData('customer', 'customer_order_notification', 'mail_status')) {
                            Mail::to($order->customer->email)->send(new PlaceOrder($order->id));
                        }
                    } catch (\Exception $ex) {
                        info($ex->getMessage());
                    }
                }
            }

            if ($store?->is_valid_subscription && $store_sub->max_order != "unlimited" && $store_sub->max_order > 0 ) {
                $store_sub->decrement('max_order' , 1);
            }

            Toastr::success(translate('messages.order_placed_successfully'));
            return back();
        } catch (\Exception $e) {
            info($e->getMessage());
        }
        Toastr::warning(translate('messages.failed_to_place_order'));
        return back();
    }



    public function customer_store(Request $request)
    {
        Toastr::error(translate('messages.pos_app_customer_disabled'));

        return back();
    }

    public function internal_customer_store(Request $request)
    {
        $store = Helpers::get_store_data();
        $request->validate([
            'f_name' => 'required|string|max:100',
            'l_name' => 'nullable|string|max:100',
            'phone' => 'required|string|max:20',
        ]);

        $phone = trim($request->phone);

        $existing = StorePosCustomer::where('store_id', $store->id)
            ->where('phone', $phone)
            ->first();

        if ($existing) {
            $existing->update([
                'f_name' => $request->f_name,
                'l_name' => $request->filled('l_name') ? $request->l_name : $existing->l_name,
            ]);
            $existing->refresh();
            session()->flash('pos_preselect_internal_customer', [
                'id' => $existing->id,
                'f_name' => $existing->f_name,
                'l_name' => $existing->l_name,
                'phone' => $existing->phone,
            ]);
            Toastr::info(translate('messages.internal_customer_found_by_phone'));

            return back();
        }

        $created = StorePosCustomer::create([
            'store_id' => $store->id,
            'f_name' => $request->f_name,
            'l_name' => $request->l_name ?? '',
            'phone' => $phone,
        ]);
        session()->flash('pos_preselect_internal_customer', [
            'id' => $created->id,
            'f_name' => $created->f_name,
            'l_name' => $created->l_name,
            'phone' => $created->phone,
        ]);
        Toastr::success(translate('messages.internal_customer_added_successfully'));

        return back();
    }

    public function extra_charge(Request $request)
    {
        $distance_data = $request->distancMileResult ?? 1;
        $self_delivery_status = $request->self_delivery_status;
        $extra_charges = 0;
        if($self_delivery_status != 1){
        $data=  DMVehicle::where(function($query)use($distance_data) {
                $query->where('starting_coverage_area','<=' , $distance_data )->where('maximum_coverage_area','>=', $distance_data);
            })
            ->orWhere(function ($query) use ($distance_data) {
                $query->where('starting_coverage_area', '>=', $distance_data);
            })
            ->active()
            ->orderBy('starting_coverage_area')->first();
        }
            $extra_charges = (float) (isset($data) ? $data->extra_charges  : 0);
            return response()->json($extra_charges,200);
    }

    protected function isAllowedGoogleMapsLinkHost(string $host): bool
    {
        if ($host === '') {
            return false;
        }
        if (in_array($host, ['maps.app.goo.gl', 'goo.gl', 'www.goo.gl'], true)) {
            return true;
        }

        return (bool) preg_match('/(^|\.)google\.[a-z.]{2,}$/i', $host);
    }

    protected function isAllowedResolvedGoogleMapsHost(string $host): bool
    {
        if ($host === '') {
            return false;
        }

        return (bool) preg_match('/(^|\.)google\.[a-z.]{2,}$/i', $host);
    }

    protected function followRedirectsToFinalUrl(string $url): string
    {
        if (! function_exists('curl_init')) {
            return $url;
        }
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; POS/1.0)',
            CURLOPT_HEADER => false,
            CURLOPT_WRITEFUNCTION => static function ($curl, $data) {
                return strlen($data);
            },
        ]);
        curl_exec($ch);
        $effective = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        curl_close($ch);

        return (is_string($effective) && $effective !== '') ? $effective : $url;
    }

    protected function parseLatLngFromGoogleMapsUrlString(string $url): ?array
    {
        $decoded = rawurldecode($url);
        if (preg_match('/@(-?\d+\.?\d*),(-?\d+\.?\d*)/', $decoded, $m)) {
            return ['lat' => (float) $m[1], 'lng' => (float) $m[2]];
        }
        if (preg_match('/[?&](?:q|query)=(-?\d+\.?\d*),\s*(-?\d+\.?\d*)/', $decoded, $m)) {
            return ['lat' => (float) $m[1], 'lng' => (float) $m[2]];
        }
        if (preg_match('/[?&]ll=(-?\d+\.?\d*),(-?\d+\.?\d*)/', $decoded, $m)) {
            return ['lat' => (float) $m[1], 'lng' => (float) $m[2]];
        }
        if (preg_match('/[?&]center=(-?\d+\.?\d*)%2C(-?\d+\.?\d*)/i', $decoded, $m)) {
            return ['lat' => (float) $m[1], 'lng' => (float) $m[2]];
        }
        if (preg_match('/[?&]center=(-?\d+\.?\d*),(-?\d+\.?\d*)/i', $decoded, $m)) {
            return ['lat' => (float) $m[1], 'lng' => (float) $m[2]];
        }

        return null;
    }
}
