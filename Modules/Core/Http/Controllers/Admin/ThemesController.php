<?php
namespace App\Modules\Core\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;
use Validator;
use App\Modules\Core\Models\Theme;
use Storage;
use Auth;
use Cache;

class ThemesController extends Controller
{
    public function index()
    {
        $items = Theme::orderBy('name', 'ASC')->get();
        // build array of themes by slug
        $items_array = [];
        foreach ($items as $item) {
            $items_array[$item->slug] = $item;
        }
        // loop through directories and create base model
        // to display themes not in DB
        $dirs = Storage::disk('themesbase')->directories();
        foreach ($dirs as $dir) {
            if (empty($items_array[$dir])) {
                $theme = new Theme;
                $theme->name = str_replace('-', '', ucfirst($dir));
                $theme->slug = $dir;
                $theme->status = 0;
                $items[] = $theme;
            }
        }
        return view('core::Admin/Themes/index', ['items' => $items]);
    }

    public function add(Request $request)
    {
        $model = new Theme();
        if ($request->getMethod() == 'POST') {
            $this->validate($request, [
                'name' => 'required|unique:themes|max:255'
            ]);
            $data = $request->all();
            $data['user_id'] = Auth::user()->id;
            $model->add($data);
            return redirect()->route('admin.themes.index')->with('success', 'Theme has been saved');
        }
        return view('core::Admin/Themes/add', ['model' => $model]);
    }

    public function edit(Request $request, $id)
    {
        $model = Theme::find($id);
        $errors = [];
        if ($request->getMethod() == 'POST') {
            $validator = Validator::make($request->all(), [
                'name' => [
                    'required',
                    Rule::unique('themes')->ignore($model->id)
                ]
            ]);
            if (!$validator->fails()) {
                $data = $request->all();
                $data['user_id'] = Auth::user()->id;
                $model->edit($data);
            } else {
                $errors = $validator->errors()->getMessages();
            }
            return redirect()->route('admin.themes.index')->with('success', 'Theme has been saved');
        }
        return view('core::Admin/Themes/edit', ['model' => $model, 'errors' => $errors]);
    }

    public function edit_templates($id)
    {
        $theme = Theme::find($id);
        $files = Storage::disk('themesbase')->allFiles($theme->slug);
        return view('core::Admin/Themes/edit_templates', ['theme' => $theme, 'files' => $files]);
    }

    public function edit_template(Request $request, $id, $path)
    {
        $path = base64_decode($path);
        $theme = Theme::find($id);
        if ($request->getMethod() == 'POST') {
            if ($request->get('body')) {
                $body = trim($request->get('body'));
                $body = html_entity_decode($body);
                $body = str_replace("\r\n", PHP_EOL, $body);
                Storage::disk('themesbase')->put($path, $body);
                return redirect()->route('admin.themes.edit_templates', ['id' => $id])->with('success', 'Template has been saved');
            }
        }
        return view('core::Admin/Themes/edit_template', [
            'theme' => $theme,
            'path' => $path
        ]);
    }

    public function delete($id)
    {
        $model = Theme::find($id)->delete();
        return redirect()->route('admin.themes.index')->with('success', 'Theme has been saved');
    }

    public function status($id)
    {
        $model = Theme::find($id);
        $model->status = !$model->status;
        $model->save();
        if ($model->status) {
            foreach (Theme::where('status', '=', 1)->where('id', '!=', $model->id)->get() as $theme) {
                $theme->status = 0;
                $theme->save();
            }
            Cache::forever('theme', $model->slug);
        }
        return redirect()->route('admin.themes.index')->with('success', 'Theme has been made active.');
    }

    public function activate($slug)
    {
        $theme = new Theme;
        $theme->slug = $slug;
        $theme->name = str_replace('-', ' ', ucfirst($slug));
        $theme->user_id = Auth::user()->id;
        $theme->status = 0;
        $theme->custom = 0;
        $theme->save();
        return redirect()->route('admin.themes.index')->with('success', 'Theme has been activated.');
    }

    public function simpleSave(Request $request)
    {
        $model = new Theme;
        $response = $model->simpleSave($request->all());
        return response()->json($response);
    }

    public function build(Request $request, $step = null)
    {
        if ($request->getMethod() == 'POST') {
            switch ($request->get('step')) {
                case 'customize':
                case 'snippets':
                    $request->session()->put('theme_builder', json_encode($request->all()));
                    return response()->json([
                        'status' => true
                    ]);
                    break;
                case 'completed':
                    break;
            }
        } elseif ($request->get('retrieve')) {
            return response()->json([
                'status' => true,
                'data' => json_decode($request->session()->get('theme_builder'))
            ]);
        }
        return view('core::Admin/Themes/build', ['ignore_vuejs' => true]);
    }
}
