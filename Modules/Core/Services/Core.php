<?php
namespace App\Modules\Core\Services;

use App\Modules\Core\Models\Album;
use App\Modules\Core\Models\File;
use App\Modules\Core\Models\Category;
use App\Modules\Core\Models\Page;
use App\Modules\Core\Models\Post;
use App\Modules\Core\Models\Theme;
use App\Modules\Users\Models\User;
use Auth;
use Cache;
use Debugbar;
use GuzzleHttp\Client;
use Module;
use Route;
use Settings;
use Storage;

class Core
{
    public $validModels = [
        // posts
        'posts' => '\App\Modules\Core\Models\Post',
        'pages' => '\App\Modules\Core\Models\Page',
        'tags' => '\App\Modules\Core\Models\Tag',
        'fields' => '\App\Modules\Core\Models\Field',
        'categories' => '\App\Modules\Core\Models\Category',
        // files
        'albums' => '\App\Modules\Core\Models\Album',
        'files' => '\App\Modules\Core\Models\File',
        // users
        'users' => '\App\Modules\Users\Models\User',
        // core
        'settings' => '\App\Modules\Settings\Models\Setting',
    ];

    public function getData($model, $type = 'all', $conditions = [], $order = [], $limit = null)
    {
        $response = [];
        if (empty($this->validModels[$model])) {
            abort(500, 'Invalid model of "' . $model . '" supplied for Core::getData.');
        } else {
            switch ($type) {
                case 'all':
                    $query = $this->validModels[$model]::where('id', '>', 0);
                    if (!empty($conditions)) {
                        foreach ($conditions as $row) {
                            $query->where($row[0], $row[1], $row[2]);
                        }
                    }
                    if (!empty($order)) {
                        $query->orderBy($order[0], $order[1]);
                    }
                    if (!empty($limit)) {
                        $query->limit($limit);
                    }
                    break;
            }
            $response = $query->get();
        }
        return $response;
    }

    public function getDateShort($date)
    {
        return date(Settings::get('date_format_short'), strtotime($date));
    }

    public function getDateLong($date)
    {
        return date(Settings::get('date_format_long'), strtotime($date));
    }

    public function getVersion()
    {
        if (Cache::has('adaptcms_version')) {
            $version = Cache::get('adaptcms_version');
        } else {
            $version = Storage::disk('base')->get('.version');
            Cache::forever('adaptcms_version', $version);
        }
        return $version;
    }

    public function clearCache()
    {
        $cacheDirectories = Storage::disk('framework-cache')->allDirectories();
        foreach ($cacheDirectories as $directory) {
            Storage::disk('framework-cache')->deleteDirectory($directory);
        }
        Storage::disk('framework-views')->delete(Storage::disk('framework-views')->files());
    }

    public function getAddonUpdates($type)
    {
        $updates = Cache::get($type . '_updates_list');
        $updates = json_decode($updates, true);
        return empty($updates) ? false : $updates;
    }

    public function getRequestActionName()
    {
        $name = explode('@', Route::getCurrentRoute()->getActionName());
        $name = $name[1];
        return $name;
    }

    public function syncWebsiteInit($step, $params = [])
    {
        // if we can't collect data, we ain't doin' nothin'
        /*if (!Cache::get('cms_collect_data', true)) {
            return false;
        }*/
        /*$client = new Client();

        $data = [
            'version' => $this->getVersion(),
            'url' => route('home'),
            'install_process' => $step,
            'cms_collect_data' => Cache::get('cms_collect_data', true),
            'webhost' => Cache::get('webhost')
        ];

        if (!empty($params)) {
            foreach ($params as $key => $val) {
                $data[$key] = $val;
            }
        }

        $res = $client->post(
            $this->getMarketplaceApiUrl() . '/sync/website/init',
            [
                'form_params' => $data,
                'http_errors' => false
            ]
        );

        $body = (string) $res->getBody();*/
        if (true) {
            Cache::forever('api_cms_website', $body);
        }
    }

    public function syncWebsite()
    {
        // if we can't collect data, we ain't doin' nothin'
        if (!Cache::get('cms_collect_data', true)) {
            return false;
        }
        $client = new Client();
        $meta_data = [
            'posts_count' => Post::count(),
            'users_count' => User::count(),
            'plugins_count' => Module::count(),
            'pages_count' => Page::count(),
            'categories_count' => Category::count(),
            'files_count' => File::count(),
            'albums_count' => Album::count(),
            'themes_count' => Theme::count(),
            'plugins' => Module::all()->toArray()
        ];
        $data = [
            'version' => $this->getVersion(),
            'url' => route('home'),
            'cms_collect_data' => Cache::get('cms_collect_data', true),
            'metadata' => $meta_data
        ];
        if (!empty($params)) {
            foreach ($params as $key => $val) {
                $data[$key] = $val;
            }
        }
        $res = $client->post(
            $this->getMarketplaceApiUrl() . '/sync/website',
            [
                'form_params' => $data,
                'http_errors' => false
            ]
        );
        $body = (string)$res->getBody();
        if ($res->getStatusCode() == 200) {
            Cache::forever('api_cms_website', $body);
        }
    }

    public function syncPermissions()
    {
        $modules = Module::all();
        $permissions = [];
        foreach ($module as $module) {
            dump($module);
        }
        $routes = Route::getRoutes();
        foreach ($routes as $route) {
            dump($route->getPath());
        }
        die();
    }

    public function getMarketplaceApiUrl()
    {
        return null;
    }

    public function debugEnable()
    {
        if (class_exists('Debugbar')) {
            Debugbar::enable();
        }
    }

    public function debugDisable()
    {
        if (class_exists('Debugbar')) {
            Debugbar::disable();
        }
    }

    public function getAdminPluginLinks()
    {
        $modules = Module::all();
        $links = [];
        foreach ($modules as $module) {
            $moduleLinks = Module::get($module['slug'] . '::admin_menu');
            if (!empty($moduleLinks)) {
                $links[] = [
                    'name' => $module['name'],
                    'links' => $moduleLinks
                ];
            }
        }
        return $links;
    }

    public function getUpdatesCount()
    {
        return Cache::get('cms_updates', 0) + Cache::get('plugin_updates', 0) + Cache::get('theme_updates', 0);
    }

    public function fireEvent($module, $class = '', $arg = '')
    {
        $class = '\App\Modules\\' . $module . '\\Events\\' . $class;
        $event = false;
        if (class_exists($class)) {
            if (!empty($arg)) {
                $event = event(new $class($arg));
            } else {
                $event = event(new $class);
            }
        }
        return $event;
    }

    public function getMarketplaceUserData()
    {

    }
}
