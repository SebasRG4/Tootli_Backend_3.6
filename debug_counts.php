<?php

use App\Models\Wishlist;
use App\Models\UserListStore;
use App\Models\Store;

echo "--- Wishlists for Store 2 ---\n";
$wishlists = Wishlist::where('store_id', 2)->get();
echo "Count: " . $wishlists->count() . "\n";
foreach ($wishlists as $w) {
    echo "ID: {$w->id}, UserID: {$w->user_id}, StoreID: {$w->store_id}\n";
}

echo "\n--- UserListStores for Store 2 ---\n";
$uls = UserListStore::where('store_id', 2)->get();
echo "Count: " . $uls->count() . "\n";
foreach ($uls as $u) {
    echo "UserListID: {$u->user_list_id}, StoreID: {$u->store_id}\n";
}

echo "\n--- Store 2 Relationships ---\n";
$store = Store::withCount(['wishlists', 'userListStores'])->find(2);
echo "Store wishlists_count: " . $store->wishlists_count . "\n";
echo "Store user_list_stores_count: " . $store->user_list_stores_count . "\n";
