import re

file_path = "app/Http/Controllers/Admin/VendorController.php"
with open(file_path, "r") as f:
    content = f.read()

# 1. Update validation in store()
old_validation = """        $validator = Validator::make($request->all(), [
            'f_name' => 'required|max:100',
            'l_name' => 'nullable|max:100',
            'name.0' => 'required',
            'name.*' => 'max:191',
            'address.0' => 'required',
            'address.*' => 'max:1000',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'email' => 'required|unique:vendors',
            'phone' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:10|max:20|unique:vendors',
            'minimum_delivery_time' => 'required',
            'maximum_delivery_time' => 'required',
            'delivery_time_type' => 'required',
            'password' => [
                'required',
                Password::min(8)->mixedCase()->letters()->numbers()->symbols(),
                function ($attribute, $value, $fail) {
                    if (strpos($value, ' ') !== false) {
                        $fail('The :attribute cannot contain white spaces.');
                    }
                },
            ],
            'zone_id' => 'required',
            'logo' => 'required|image|max:2048|mimes:' . IMAGE_FORMAT_FOR_VALIDATION,
            'cover_photo' => 'nullable|image|max:2048|mimes:' . IMAGE_FORMAT_FOR_VALIDATION,

        ], ["""

new_validation = """        $isDirectory = $request->has('is_directory_only');
        $validator = Validator::make($request->all(), [
            'f_name' => $isDirectory ? 'nullable|max:100' : 'required|max:100',
            'l_name' => 'nullable|max:100',
            'name.0' => 'required',
            'name.*' => 'max:191',
            'address.0' => 'required',
            'address.*' => 'max:1000',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'email' => $isDirectory ? 'nullable' : 'required|unique:vendors',
            'phone' => $isDirectory ? 'nullable' : 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:10|max:20|unique:vendors',
            'minimum_delivery_time' => 'required',
            'maximum_delivery_time' => 'required',
            'delivery_time_type' => 'required',
            'password' => [
                $isDirectory ? 'nullable' : 'required',
                Password::min(8)->mixedCase()->letters()->numbers()->symbols(),
                function ($attribute, $value, $fail) {
                    if ($value && strpos($value, ' ') !== false) {
                        $fail('The :attribute cannot contain white spaces.');
                    }
                },
            ],
            'zone_id' => 'required',
            'logo' => 'required|image|max:2048|mimes:' . IMAGE_FORMAT_FOR_VALIDATION,
            'cover_photo' => 'nullable|image|max:2048|mimes:' . IMAGE_FORMAT_FOR_VALIDATION,

        ], ["""

content = content.replace(old_validation, new_validation)

# 2. Update Vendor creation
old_vendor_creation = """        $vendor = new Vendor();
        $vendor->f_name = $request->f_name;
        $vendor->l_name = $request->l_name;
        $vendor->email = $request->email;
        $vendor->phone = $request->phone;
        $vendor->password = bcrypt($request->password);
        $vendor->save();"""

new_vendor_creation = """        if ($isDirectory) {
            $vendor = Vendor::firstOrCreate(
                ['email' => 'directory@tootli.com'],
                [
                    'f_name' => 'Directory',
                    'l_name' => 'System',
                    'phone' => '0000000000',
                    'password' => bcrypt(\Illuminate\Support\Str::random(16)),
                ]
            );
        } else {
            $vendor = new Vendor();
            $vendor->f_name = $request->f_name;
            $vendor->l_name = $request->l_name;
            $vendor->email = $request->email;
            $vendor->phone = $request->phone;
            $vendor->password = bcrypt($request->password);
            $vendor->save();
        }"""

content = content.replace(old_vendor_creation, new_vendor_creation)

# 3. Add directory fields to store
old_store_save = """        $store->allow_next_day = $request->has('allow_next_day');
        $store->tootli_lana = true;"""

new_store_save = """        $store->allow_next_day = $request->has('allow_next_day');
        $store->tootli_lana = true;
        $store->is_directory_only = $isDirectory;
        $store->directory_description = $request->directory_description;"""

content = content.replace(old_store_save, new_store_save)

# 4. Do the same for update()
old_update_validation = """        $validator = Validator::make($request->all(), [
            'f_name' => 'required|max:100',
            'l_name' => 'nullable|max:100',
            'name.0' => 'required',
            'name.*' => 'max:191',
            'address.0' => 'required',
            'address.*' => 'max:1000',
            'email' => 'required|unique:vendors,email,' . $store->vendor->id,
            'phone' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:10|max:20|unique:vendors,phone,' . $store->vendor->id,"""

new_update_validation = """        $isDirectory = $request->has('is_directory_only');
        $validator = Validator::make($request->all(), [
            'f_name' => $isDirectory ? 'nullable|max:100' : 'required|max:100',
            'l_name' => 'nullable|max:100',
            'name.0' => 'required',
            'name.*' => 'max:191',
            'address.0' => 'required',
            'address.*' => 'max:1000',
            'email' => $isDirectory ? 'nullable' : 'required|unique:vendors,email,' . $store->vendor->id,
            'phone' => $isDirectory ? 'nullable' : 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:10|max:20|unique:vendors,phone,' . $store->vendor->id,"""
            
content = content.replace(old_update_validation, new_update_validation)

old_update_vendor = """        $vendor = Vendor::findOrFail($store->vendor->id);
        $vendor->f_name = $request->f_name;
        $vendor->l_name = $request->l_name;
        $vendor->email = $request->email;
        $vendor->phone = $request->phone;
        $vendor->password = strlen($request->password) > 1 ? bcrypt($request->password) : $store->vendor->password;
        $vendor->save();"""

new_update_vendor = """        if (!$isDirectory) {
            $vendor = Vendor::findOrFail($store->vendor->id);
            $vendor->f_name = $request->f_name;
            $vendor->l_name = $request->l_name;
            $vendor->email = $request->email;
            $vendor->phone = $request->phone;
            $vendor->password = strlen($request->password) > 1 ? bcrypt($request->password) : $store->vendor->password;
            $vendor->save();
        }"""
        
content = content.replace(old_update_vendor, new_update_vendor)

old_update_store = """        $store->tootli_lana = true;
        $store->save();"""

new_update_store = """        $store->tootli_lana = true;
        $store->is_directory_only = $isDirectory;
        $store->directory_description = $request->directory_description;
        $store->save();"""
        
content = content.replace(old_update_store, new_update_store)


with open(file_path, "w") as f:
    f.write(content)
print("Updated VendorController.php")
