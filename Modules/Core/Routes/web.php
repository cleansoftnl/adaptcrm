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




Route::group(['prefix' => 'admincp'], function () {
    Route::group(['prefix' => 'themes', 'namespace' => 'Admin',], function () {
        Route::get('/', ['uses' => 'ThemesController@index', 'as' => 'admin.themes.index']);
        Route::any('/add', ['uses' => 'ThemesController@add', 'as' => 'admin.themes.add']);
        Route::any('/build/{step?}', ['uses' => 'ThemesController@build', 'as' => 'admin.themes.build']);
        Route::any('/edit/{id}', ['uses' => 'ThemesController@edit', 'as' => 'admin.themes.edit']);
        Route::any('/edit_templates/{id}', ['uses' => 'ThemesController@edit_templates', 'as' => 'admin.themes.edit_templates']);
        Route::any('/edit_template/{id}/{path}', ['uses' => 'ThemesController@edit_template', 'as' => 'admin.themes.edit_template']);
        Route::get('/delete/{id}', ['uses' => 'ThemesController@delete', 'as' => 'admin.themes.delete']);
        Route::any('/status/{id}', ['uses' => 'ThemesController@status', 'as' => 'admin.themes.status']);
        Route::any('/activate/{slug}', ['uses' => 'ThemesController@activate', 'as' => 'admin.themes.activate']);
        Route::post('/simple-save', ['uses' => 'ThemesController@simpleSave', 'as' => 'admin.themes.simple_save']);
    });
});