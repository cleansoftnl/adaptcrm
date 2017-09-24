<?php
namespace App\Modules\Core\Http\Controllers;

use App\Modules\Plugins\Models\Plugin;
use App\Modules\Themes\Models\Theme;
use App\Modules\Users\Models\Permission;
use App\Modules\Users\Models\Role;
use Cache;
use Core;
use GuzzleHttp\Client;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Notifications\Notifiable;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Schema;
use Module;
use Route;
use Settings;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;
use Storage;

class BaseController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests, Notifiable;

    public function __construct()
    {
        if (Schema::hasTable('settings')) {
            // CMS Update Checks
            //$this->checkForCmsUpdates();
            //$this->checkForPluginUpdates();
            //$this->checkForThemeUpdates();
            // sync site metadata
            //$this->syncWebsite();
            //sync permissions
            //$this->syncPermissions();
        }
    }

    public function checkForCmsUpdates()
    {
        /*$apiUrl = Core::getMarketplaceApiUrl();
        Cache::remember('core_cms_updates', Settings::get('check_for_updates_every_x_minutes', 15), function () use ($apiUrl) {
            $cms_updates = 0;
            $client = new Client();
            // get the cms versions
            $res = $client->request('GET', $apiUrl . '/cms/versions', ['http_errors' => false]);
            if ($res->getStatusCode() == 200) {
                $versions = json_decode($res->getBody(), true);
            } else {
                return false;
            }
            // try to get the installed version
            // from the API and the latest
            $current_version = array_where($versions, function ($val, $key) {
                return $val['branch_slug'] == Core::getVersion() ? $val : false;
            });
            $current_version = reset($current_version);
            // sort the versions by ID ASC
            // flip it to DESC order
            // grab the latest one
            $latest_version = array_reverse(array_sort($versions, function ($value) {
                return $value['id'];
            }));
            $latest_version = reset($latest_version);
            // if empty somehow, 404
            if (empty($current_version) || empty($latest_version)) {
                return false;
            }
            Cache::forever('bleedinge_edge_update', 0);
            // for new installs, let's set it up
            if (!$this->getCommitHash()) {
                // and lastly, set the latest commit hash and commit version data
                $this->setCommitHash($current_version['commit_hash']);
                Cache::forever('cms_current_version', json_encode($current_version));
            } elseif ($current_version['commit_hash'] != $this->getCommitHash()) {
                // if it's not the most recent
                // increment the notification value for bleeding edge
                if (env('APP_DEBUG')) {
                    $cms_updates++;
                    Cache::forever('bleedinge_edge_update', 1);
                    Cache::forever('cms_current_version', json_encode($current_version));
                }
            }
            // check for normal upgrades
            if ($current_version['id'] != $latest_version['id']) {
                $cms_updates++;
                Cache::forever('cms_latest_version_name', $latest_version['version']);
                Cache::forever('cms_latest_version', json_encode($latest_version));
            }
            Cache::forever('cms_updates', $cms_updates);
            return true;
        });*/


        return true;

    }

    public function checkForPluginUpdates()
    {
        $apiUrl = Core::getMarketplaceApiUrl();
        Cache::remember('plugin_updates', Settings::get('check_for_updates_every_x_minutes', 15), function () use ($apiUrl) {
            // set the client
            $client = new Client();
            $plugins = Module::all();
            $plugin_updates = 0;
            // empty out module updates data
            $modules_updates_list = [];
            foreach ($plugins as $plugin) {
                // get the module
                $res = $client->request('GET', $apiUrl . '/module/slug/plugin/' . $plugin['slug'], ['http_errors' => false]);
                if ($res->getStatusCode() == 200) {
                    $module = json_decode($res->getBody(), true);
                } else {
                    continue;
                }
                if ($module['latest_version']['version'] != Module::get($plugin['slug'] . '::version')) {
                    $plugin_updates++;
                    // add module for updates index
                    $modules_updates_list[] = $module;
                }
            }
            Cache::forever('plugin_updates', $plugin_updates);
            Cache::forever('plugins_updates_list', json_encode($modules_updates_list));
            return true;
        });
    }

    public function checkForThemeUpdates()
    {
        $apiUrl = Core::getMarketplaceApiUrl();
        Cache::remember('theme_updates', Settings::get('check_for_updates_every_x_minutes', 15), function () use ($apiUrl) {
            // set the client
            $client = new Client();
            $themes = Theme::all();
            $theme_updates = 0;
            $modules_updates_list = [];
            foreach ($themes as $theme) {
                // get the module
                $res = $client->request('GET', $apiUrl . '/module/slug/theme/' . $theme['slug'], ['http_errors' => false]);
                if ($res->getStatusCode() == 200) {
                    $module = json_decode($res->getBody(), true);
                } else {
                    continue;
                }
                if ($module['latest_version']['version'] != $theme->getConfig('version')) {
                    $theme_updates++;
                    $module['theme'] = $theme;
                    // add module for updates index
                    $modules_updates_list[] = $module;
                }
            }
            Cache::forever('theme_updates', $theme_updates);
            Cache::forever('themes_updates_list', json_encode($modules_updates_list));
            return true;
        });
    }

    public function syncWebsite()
    {
        // every 3 days
        /*$minutes = (1440 * 3);
        Cache::remember('sync_website', $minutes, function () {
            Core::syncWebsite();
            return true;
        });*/
    }

    public function syncPermissions()
    {
        // every 3 days
        $minutes = (1440 * 3);
        Cache::remember('sync_permissions', $minutes, function () {
            // Database check
            if (!Schema::hasColumn('permissions', 'access')) {
                Schema::table('permissions', function (Blueprint $table) {
                    $table->integer('access')->default(0);
                });
            }
            $all_routes_list = [];
            $valid_routes_list = [];
            $routes = [];
            $modules = [];
            $permissions = [];
            foreach (Module::all() as $module) {
                $modules[$module['slug']] = Plugin::getConfig($module['basename']);
                if (!empty($modules[$module['slug']]->permissions)) {
                    $routes[$module['slug']] = [];
                    $permissions[$module['slug']] = $modules[$module['slug']]->permissions;
                }
            }
            $roles = Role::all();
            $new_permissions = [];
            $roles_by_level = [];
            $core_role_levels = $roles[0]->core_role_levels;
            foreach ($roles as $role) {
                $new_permissions[$role->name] = [];
                if ($role->core_role) {
                    $roles_by_level[$role->level] = $role;
                }
            }
            $routes_list = Route::getRoutes();
            $orig_routes_list = (array)$routes_list;
            foreach ($modules as $module) {
                // get a list of route prefixes to match for
                $route_prefixes = [
                    'plugin.' . $module->slug,
                    'admin.' . $module->slug,
                    $module->slug . '.'
                ];
                $route_prefixes_orig = $route_prefixes;
                if (!empty($module->route_prefixes)) {
                    foreach ($module->route_prefixes as $custom_prefix) {
                        $prefix = $route_prefixes_orig;
                        foreach ($prefix as $val) {
                            $route_prefixes[] = str_replace($module->slug, $custom_prefix, $val);
                        }
                    }
                }
                // build the list of routes
                foreach ($routes_list as $key => $route) {
                    $name = $route->getName();
                    if (!empty($name)) {
                        foreach ($route_prefixes as $prefix) {
                            if (strstr($name, $prefix)) {
                                $routes[$module->slug][] = $name;
                            }
                        }
                    }
                }
            }
            // now we have a list of permissions for each module and
            // a list of routes by module
            $tmp_permissions = [];
            foreach ($routes as $module_slug => $route_list) {
                // no module permissions
                if (!isset($permissions[$module_slug])) {
                    continue;
                }
                $permission_list = $permissions[$module_slug];
                $module = $modules[$module_slug];
                // loop through module routes
                foreach ($route_list as $route_name) {
                    $all_routes_list[] = $route_name;
                    // and loop through roles from permissions
                    foreach ($permission_list as $role_slug => $module_permissions) {
                        foreach ($module_permissions as $path => $access) {
                            $path_clean = str_replace('*', '', $path);
                            if ($route_name == $path || starts_with($route_name, $path_clean)) {
                                $permission_values = [
                                    'name' => $route_name,
                                    'access' => (int)$access
                                ];
                                if (!empty($tmp_permissions[$route_name])) {
                                    $permission = $tmp_permissions[$route_name];
                                } else {
                                    // returns a permission object
                                    // if exists, returns it and otherwise
                                    // creates and returns an object
                                    try {
                                        $permission = Permission::findByName($permission_values['name']);
                                    } catch (PermissionDoesNotExist $e) {
                                        $permission = Permission::create($permission_values);
                                    }
                                    $tmp_permissions[$route_name] = $permission;
                                }
                                if (!in_array($route_name, $valid_routes_list)) {
                                    $valid_routes_list[] = $route_name;
                                }
                                $new_permissions[$role_slug][] = $permission;
                            }
                        }
                    }
                }
            }
            // we want to loop through all routes not covered by a module
            // if the route name has 'admin', then we require admin access.
            // otherwise, base guest access
            $missing_routes = array_diff($all_routes_list, $valid_routes_list);
            foreach ($missing_routes as $route_name) {
                if (str_contains($route_name, 'admin.')) {
                    $permission_values = [
                        'name' => $route_name,
                        'access' => 1
                    ];
                    if (!empty($tmp_permissions[$route_name])) {
                        $permission = $tmp_permissions[$route_name];
                    } else {
                        // returns a permission object
                        // if exists, returns it and otherwise
                        // creates and returns an object
                        try {
                            $permission = Permission::findByName($permission_values['name']);
                        } catch (PermissionDoesNotExist $e) {
                            $permission = Permission::create($permission_values);
                        }
                        $tmp_permissions[$route_name] = $permission;
                    }
                    if (!in_array($route_name, $valid_routes_list)) {
                        $valid_routes_list[] = $route_name;
                    }
                    $role_slug = $roles_by_level[$core_role_levels['admin']]->slug;
                    $new_permissions[$role_slug][] = $permission;
                }
            }
            // assign permissions to roles
            // we use sync to delete old relationships
            // and add the new valid ones
            $invalid_access_permissions = [];
            foreach ($roles as $role) {
                $valid_access_permissions = [];
                foreach ($new_permissions[$role->name] as $permission) {
                    if ($permission->access > 0) {
                        $valid_access_permissions[] = $permission->id;
                    } else {
                        $invalid_access_permissions[] = $permission->name;
                    }
                }
                $role->permissions()->sync($valid_access_permissions);
            }
            return true;
        });
    }

    public function fireEvent($module, $class = '', $arg = '')
    {
        return Core::fireEvent($module, $class, $arg);
    }

    public function setCommitHash($hash)
    {
        return Storage::disk('base')->put('.commit_hash', $hash, 'public');
    }

    public function getCommitHash()
    {
        return Storage::disk('base')->get('.commit_hash');
    }
}
