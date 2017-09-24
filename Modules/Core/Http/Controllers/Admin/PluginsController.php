<?php
namespace App\Modules\Core\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Core\Models\Plugin;
use Storage;
use Module;
use Cache;

class PluginsController extends Controller
{
    public function index()
    {
        // current plugins
        $items = Module::all();
        $core_modules = Plugin::getCoreModules();
        return view('plugins::Admin/Plugins/index', compact('items', 'core_modules'));
    }

    public function install($slug)
    {
        $this->fireEvent($slug, $slug . 'Install');
        Plugin::enable($slug);
        return redirect()->route('admin.plugins.index')->with('status', 'Plugin has been enabled.');
    }

    public function uninstall($slug)
    {
        $this->fireEvent($slug, $slug . 'Uninstall');
        Plugin::disable($slug);
        return redirect()->route('admin.plugins.index')->with('status', 'Plugin has been disabled.');
    }
}
