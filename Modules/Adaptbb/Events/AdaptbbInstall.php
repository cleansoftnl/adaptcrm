<?php
namespace App\Modules\Adaptbb\Events;

use Cache;
use Storage;

class AdaptbbInstall
{
    public function __construct()
    {
        $theme = Cache::get('theme', 'default');
        $origViewsPath = 'Adaptbb/Resources/Views/';
        $newViewsPath = $theme . '/views/adaptbb/';
        $partialsViewsPath = $theme . '/partials/adaptbb/';
        if (!Storage::disk('themesbase')->exists($newViewsPath)) {
            Storage::disk('themesbase')->makeDirectory($newViewsPath);
        }
        $folders = Storage::disk('plugins')->directories($origViewsPath);
        foreach ($folders as $folder) {
            $folder = basename($folder);
            $files = Storage::disk('plugins')->files($origViewsPath . $folder);
            if ($folder == 'Partials') {
                if (!Storage::disk('themesbase')->exists($partialsViewsPath)) {
                    Storage::disk('themesbase')->makeDirectory($partialsViewsPath);
                }
                if (!empty($files)) {
                    foreach ($files as $file) {
                        $file = basename($file);
                        $contents = Storage::disk('plugins')->get($origViewsPath . $folder . '/' . $file);
                        Storage::disk('themesbase')->put($partialsViewsPath . '/' . $file, $contents);
                    }
                }
            } else {
                if (!Storage::disk('themesbase')->exists($newViewsPath . str_slug($folder))) {
                    Storage::disk('themesbase')->makeDirectory($newViewsPath . str_slug($folder));
                }
                if (!empty($files)) {
                    foreach ($files as $file) {
                        $file = basename($file);
                        $contents = Storage::disk('plugins')->get($origViewsPath . $folder . '/' . $file);
                        Storage::disk('themesbase')->put($newViewsPath . str_slug($folder) . '/' . $file, $contents);
                    }
                }
            }
        }
    }
}
