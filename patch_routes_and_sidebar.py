import re

# 1. Update routes/admin.php
routes_file = "routes/admin.php"
with open(routes_file, "r") as f:
    routes_content = f.read()

marker_routes = """            // Dineout Categories"""
injection_routes = """            // Directory Places (Sin dueño)
            Route::group(['prefix' => 'directory-places', 'as' => 'directory-places.'], function () {
                Route::get('/', 'Sabores\DirectoryPlaceController@index')->name('index');
                Route::get('/create', 'Sabores\DirectoryPlaceController@create')->name('create');
                Route::post('/store', 'Sabores\DirectoryPlaceController@store')->name('store');
                Route::get('/{id}/edit', 'Sabores\DirectoryPlaceController@edit')->name('edit');
                Route::post('/{id}/update', 'Sabores\DirectoryPlaceController@update')->name('update');
                Route::delete('/{id}/delete', 'Sabores\DirectoryPlaceController@destroy')->name('delete');
            });

            // Dineout Categories"""
            
routes_content = routes_content.replace(marker_routes, injection_routes)

with open(routes_file, "w") as f:
    f.write(routes_content)

# 2. Update sidebar
sidebar_file = "resources/views/layouts/admin/partials/_sidebar_sabores.blade.php"
with open(sidebar_file, "r") as f:
    sidebar_content = f.read()

marker_sidebar = """                    <!-- Dineout Categories -->"""
injection_sidebar = """                    <!-- Directory Places -->
                    <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/sabores/directory-places*') ? 'active' : '' }}">
                        <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:" title="{{ 'Lugares de Directorio' }}">
                            <i class="tio-poi nav-icon"></i>
                            <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                Lugares (Sin dueño)
                            </span>
                        </a>
                        <ul class="js-navbar-vertical-aside-submenu nav nav-sub" style="display:{{ Request::is('admin/sabores/directory-places*') ? 'block' : 'none' }}">
                            <li class="nav-item {{ Request::is('admin/sabores/directory-places') && !Request::is('admin/sabores/directory-places/create') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('admin.sabores.directory-places.index') }}" title="Todos los lugares">
                                    <span class="tio-circle nav-indicator-icon"></span>
                                    <span class="text-truncate">Todos los lugares</span>
                                </a>
                            </li>
                            <li class="nav-item {{ Request::is('admin/sabores/directory-places/create') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('admin.sabores.directory-places.create') }}" title="Agregar nuevo">
                                    <span class="tio-circle nav-indicator-icon"></span>
                                    <span class="text-truncate">Agregar nuevo</span>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- Dineout Categories -->"""

sidebar_content = sidebar_content.replace(marker_sidebar, injection_sidebar)

with open(sidebar_file, "w") as f:
    f.write(sidebar_content)

print("Updated routes and sidebar")
