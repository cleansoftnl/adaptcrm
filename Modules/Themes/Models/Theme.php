<?php
namespace App\Modules\Themes\Models;

use Artisan;
use Cache;
use Core;
use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\Model;
use Storage;
use Zipper;

//use Laravel\Scout\Searchable;
class Theme extends Model
{
    //use Searchable;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'themes';

    protected $fillable = [
        'name',
        'status'
    ];

    public function user()
    {
        return $this->belongsTo('App\Modules\Users\Models\User');
    }

    public function searchLogic($searchData)
    {
        if (!empty($searchData['keyword'])) {
            $results = Theme::search($searchData['keyword'])->get();
            foreach ($results as $key => $row) {
                $results[$key]->url = route('admin.themes.edit', ['id' => $row->id]);
            }
        } elseif (!empty($searchData['template_path'])) {
            $body = Storage::disk('themesbase')->get($searchData['template_path']);
            $results = [
                [
                    'body' => $body
                ]
            ];
        } else {
            $results = [];
        }
        return $results;
    }

    public function simpleSave($data)
    {
        if (!empty($data['many'])) {
            $data['ids'] = json_decode($data['ids'], true);
            switch ($data['type']) {
                case 'delete':
                    Theme::whereIn('id', $data['ids'])->delete();
                    break;
            }
        }
        return [
            'status' => true,
            'ids' => $data['ids']
        ];
    }

    public function add($postArray)
    {
        $this->name = $postArray['name'];
        $this->status = $postArray['status'];
        $this->user_id = $postArray['user_id'];
        $slug = str_slug($this->name, '-');
        $this->slug = $slug;
        $this->custom = 1;
        // enable via pkg manager
        //$this->enable();
        $files = Storage::disk('themesbase')->allFiles('default');
        $paths = [
            $slug,
            $slug . '/assets',
            $slug . '/layouts',
            $slug . '/views',
            $slug . '/partials',
            $slug . '/views/albums',
            $slug . '/views/categories',
            $slug . '/views/pages',
            $slug . '/views/posts',
            $slug . '/views/tags',
            $slug . '/views/users',
            $slug . '/views/files'
        ];


        /*
         *  Directories
         **/
        foreach ($paths as $path) {
            $full_path = base_path('Themes/default') . str_replace($slug . '/', 'default/', $path);
            if (!is_link($full_path) && !Storage::disk('themesbase')->exists($path)) {
                Storage::disk('themesbase')->makeDirectory($path);
            }
        }

        /*
         *  Repeat for the public path
         **/
        foreach ($paths as $path) {
            $full_path = public_path('themes/default') . str_replace($slug . '/', 'default/', $path);
            if (!is_link($full_path) && !Storage::disk('themes')->exists($path)) {
                Storage::disk('themes')->makeDirectory($path);
            }
        }

        /*
         *  Files
         **/
        foreach ($files as $file) {
            $new_path = str_replace('default', $slug, $file);
            if (!Storage::disk('themesbase')->exists($new_path)) {
                Storage::disk('themesbase')->copy($file, $new_path);
            }
        }

        /*
         *  Repeat for the public path
         **/
        foreach ($files as $file) {
            $new_path = str_replace('default', $slug, $file);
            if (!Storage::disk('themes')->exists($new_path)) {
                Storage::disk('themes')->copy($file, $new_path);
            }
        }

        $this->save();
        return $this;
    }

    public function edit($postArray)
    {
        $old_slug = $this->slug;
        $this->name = $postArray['name'];
        if ($this->id > 1) {
            $this->slug = str_slug($this->name, '-');
        }
        $this->user_id = $postArray['user_id'];
        $this->save();
        if (Storage::disk('themesbase')->exists($old_slug)) {
            Storage::disk('themesbase')->move($old_slug, $this->slug);
        }
        // re-enable theme
        $this->enable();
        return $this;
    }

    public function delete()
    {
        if ($this->id == 1) {
            return false;
        }
        if (Storage::disk('themesbase')->exists($this->slug)) {
            Storage::disk('themesbase')->deleteDirectory($this->slug);
        }
        return parent::delete();
    }

    public function getConfig($key)
    {
        $path = $this->slug . '/theme.json';
        // exists check
        if (!$file = Storage::disk('themesbase')->exists($path)) {
            return null;
        }
        // get the theme.json file
        $file = Storage::disk('themesbase')->get($path);
        $file = json_decode($file, true);
        return !isset($file[$key]) ? '' : $file[$key];
    }

    /**
     * Enable
     *
     * @return void
     */
    public function enable()
    {
        // try to find module
        /*$client = new Client;
        // get the module
        $res = $client->request('GET', Core::getMarketplaceApiUrl() . '/module/slug/theme/' . $this->slug, ['http_errors' => false]);
        if ($res->getStatusCode() == 200) {
            $module = json_decode((string)$res->getBody(), true);
            if (!empty($module)) {
                // increment install count for module
                $client->request('GET', Core::getMarketplaceApiUrl() . '/install/' . $module['module_type'] . '/' . $module['slug'], ['http_errors' => false]);
            }
        }
        try {
            $status = Artisan::call('vendor:publish');
        } catch (\Exception $e) {
            abort(500, 'Cannot publish module assets. Try chmodding the /public/assets/modules/ folder to 755 recursively.');
        }
        Cache::forget('theme_count');*/
    }

    /**
     * Disable
     *
     * @return void
     */
    public function disable()
    {
        try {
            $status = Artisan::call('vendor:publish');
        } catch (\Exception $e) {
            abort(500, 'Cannot publish module assets. Try chmodding the /public/assets/modules/ folder to 755 recursively.');
        }
        Cache::forget('theme_count');
    }

    public function install($id)
    {
        /*$client = new Client();
        // get the theme
        $res = $client->request('GET', Core::getMarketplaceApiUrl() . '/module/' . $id, ['http_errors' => false]);
        if ($res->getStatusCode() == 200) {
            $module = json_decode($res->getBody(), true);
        } else {
            abort(404);
        }*/

dd($id);

        // set slug
        $slug = ucfirst($module['slug']);
        // download the latest version
        //$res = $client->request('GET', $module['latest_version']['download_url']);





        if ($res->getStatusCode() == 200) {
            $filename = $module['slug'] . '.zip';
            Storage::disk('themesbase')->put($filename, $res->getBody(), 'public');
        } else {
            abort(404);
        }




        // make the folder
        if (!Storage::disk('themesbase')->exists($module['slug'])) {
            Storage::disk('themesbase')->makeDirectory($module['slug']);
        }
        // then attempt to extract contents
        $path = base_path() . '/Themes/' . $filename;
        $zip_folder = $module['module_type'] . '-' . $module['slug'] . '-' . $module['latest_version']['version'];
        Zipper::make($path)->folder($zip_folder)->extractTo(base_path() . '/Themes');
        // delete the ZIP
        if (Storage::disk('themesbase')->exists($filename)) {
            Storage::disk('themesbase')->delete($filename);
        }
        // once we've gotten the files all setup
        // lets run the upgrade event with the version #, if it exists
        //Core::fireEvent($slug, $slug . 'Update', $module['latest_version']['version']);
        /*
         *  Copy it over to the public directory
         **/
        // enable
        //$this->enable();
        Cache::forever('theme_updates', 0);
        Cache::forget('themes_updates_list');
    }

    /**
     * Get Count
     *
     * @return int
     */
    public static function getCount()
    {
        return Cache::remember('theme_count', 3600, function () {
            return Theme::count();
        });
    }
}
