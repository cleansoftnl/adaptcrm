<?php
namespace App\Modules\Core\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;
use App\Modules\Core\Models\Album;
use Auth;
use Validator;

class AlbumsController extends Controller
{
    public function index()
    {
        $items = Album::orderBy('created_at', 'DESC')->paginate(15);
        return view('files::Admin/Albums/index', ['items' => $items]);
    }

    public function add(Request $request)
    {
        $model = new Album();
        if ($request->getMethod() == 'POST') {
            $this->validate($request, [
                'name' => 'required|unique:albums|max:255'
            ]);
            $data = $request->all();
            $data['user_id'] = Auth::user()->id;
            $model->add($data);
            return redirect()->route('admin.albums.index')->with('success', 'Album has been saved');
        }
        return view('files::Admin/Albums/add', ['model' => $model]);
    }

    public function edit(Request $request, $id)
    {
        $model = Album::find($id);
        $errors = [];
        if ($request->getMethod() == 'POST') {
            $validator = Validator::make($request->all(), [
                'name' => [
                    'required',
                    Rule::unique('albums')->ignore($model->id)
                ]
            ]);
            if (!$validator->fails()) {
                $data = $request->all();
                $data['user_id'] = Auth::user()->id;
                $model->edit($data);
                return redirect()->route('admin.albums.index')->with('success', 'Album has been saved');
            } else {
                $errors = $validator->errors()->getMessages();
            }
        }
        return view('files::Admin/Albums/edit', ['model' => $model, 'errors' => $errors]);
    }

    public function delete($id)
    {
        $model = Album::find($id)->delete();
        return redirect()->route('admin.albums.index')->with('success', 'Album has been saved');
    }

    public function simpleSave(Request $request)
    {
        $model = new Album;
        $response = $model->simpleSave($request->all());
        return response()->json($response);
    }
}
