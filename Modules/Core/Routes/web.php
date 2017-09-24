<?php
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your module. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/
Route::group([ 'prefix' => 'install', 'namespace' => 'Install' ], function() {
    Route::any('/', [ 'uses' => 'InstallController@index', 'as' => 'install.index' ]);
    Route::any('/database', [ 'uses' => 'InstallController@database', 'as' => 'install.database' ]);
    Route::any('/me', [ 'uses' => 'InstallController@me', 'as' => 'install.me' ]);
    Route::any('/acount', [ 'uses' => 'InstallController@account', 'as' => 'install.account' ]);
    Route::any('/finished', [ 'uses' => 'InstallController@finished', 'as' => 'install.finished' ]);
});



Route::group([ 'prefix' => 'api' ], function() {
    Route::any('/{module}', [ 'uses' => '\App\Modules\Core\Http\Controllers\ApiController@index', 'as' => 'plugin.core.api.index' ]);
});





Route::group([ 'prefix' => 'admincp', 'namespace' => 'Admin', 'middleware' => 'role:admin' ], function() {

    // the actual dashboard
    Route::get('/', ['uses' => 'PagesController@dashboard', 'as' => 'admin.dashboard']);

    Route::group([ 'prefix' => 'api' ], function() {
        Route::any('/{module}', [ 'uses' => 'ApiController@index', 'as' => 'api.index' ]);
        Route::post('/{module}/post', [ 'uses' => 'ApiController@post', 'as' => 'api.post' ]);
        Route::put('/{module}/put/{id}', [ 'uses' => 'ApiController@put', 'as' => 'api.put' ]);
        Route::delete('/{module}/delete/{id}', [ 'uses' => 'ApiController@delete', 'as' => 'api.delete' ]);
    });

    Route::group([ 'prefix' => 'settings' ], function() {
        Route::any('/', [ 'uses' => 'SettingsController@index', 'as' => 'admin.settings.index' ]);
        Route::any('/add', [ 'uses' => 'SettingsController@add', 'as' => 'admin.settings.add' ]);
        Route::any('/add/category', [ 'uses' => 'SettingsController@addCategory', 'as' => 'admin.settings.add_category' ]);
        Route::any('/simple-save', [ 'uses' => 'SettingsController@simpleSave', 'as' => 'admin.settings.simple_save' ]);
    });
});



Route::group(['prefix' => 'admincp', 'namespace' => 'Admin',], function () {
    Route::group(['prefix' => 'plugins'], function () {
        Route::get('/', ['uses' => 'PluginsController@index', 'as' => 'admin.plugins.index']);
        Route::any('/install/{slug}', ['uses' => 'PluginsController@install', 'as' => 'admin.plugins.install']);
        Route::any('/uninstall/{slug}', ['uses' => 'PluginsController@uninstall', 'as' => 'admin.plugins.uninstall']);
    });
});
