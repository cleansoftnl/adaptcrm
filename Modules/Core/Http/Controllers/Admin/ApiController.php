<?php
namespace App\Modules\Core\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    public $validModels = [
        // posts
        'posts' => '\App\Modules\Core\Models\Post',
        'pages' => '\App\Modules\Core\Models\Page',
        'tags' => '\App\Modules\Core\Models\Tag',
        'fields' => '\App\Modules\Core\Models\Field',
        'categories' => '\App\Modules\Core\Models\Category',
        'post_data' => '\App\Modules\Core\Models\PostData',
        // files
        'albums' => '\App\Modules\Core\Models\Album',
        'files' => '\App\Modules\Core\Models\File',
        // users
        'users' => '\App\Modules\Users\Models\User',
        // core
        'settings' => '\App\Modules\Settings\Models\Setting',
        // themes
        'themes' => '\App\Modules\Themes\Models\Theme',
    ];

    public function index(Request $request, $module)
    {
        $response = [];
        if (empty($this->validModels[$module])) {
            $response = [
                'status' => false,
                'message' => 'Invalid module of: ' . $module
            ];
        } else {
            $model = new $this->validModels[$module];
            $results = $model->searchLogic($request->all(), true);
            $response = [
                'status' => true,
                'results' => $results
            ];
        }
        return response()->json($response);
    }

    public function add(Request $request, $module)
    {
        $response = [];
        if (empty($this->validModels[$module])) {
            $response = [
                'status' => false,
                'message' => 'Invalid module of: ' . $module
            ];
        } else {
            $model = new $this->validModels[$module];
            $model->add($request->all());
            $response = [
                'status' => true,
                'model' => $model
            ];
        }
        return response()->json($response);
    }

    public function edit(Request $request, $module, $id)
    {
        $response = [];
        if (empty($this->validModels[$module])) {
            $response = [
                'status' => false,
                'message' => 'Invalid module of: ' . $module
            ];
        } else {
            $model = $this->validModels[$module]::find($id);
            $model->edit($request->all());
            $response = [
                'status' => true,
                'id' => $id,
                'model' => $model
            ];
        }
        return response()->json($response);
    }

    public function delete($module, $id)
    {
        $response = [];
        if (empty($this->validModels[$module])) {
            $response = [
                'status' => false,
                'message' => 'Invalid module of: ' . $module
            ];
        } else {
            $model = $this->validModels[$module]::find($id);
            $model->delete();
            $response = [
                'status' => true,
                'id' => $id,
                'model' => $model
            ];
        }
        return response()->json($response);
    }
}
