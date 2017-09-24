<?php
namespace App\Modules\Core\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;
use App\Modules\Core\Models\Page;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Modules\Posts\Models\Post;
use Theme;
use Storage;
use Auth;
use Cache;
use Validator;

class PagesController extends Controller
{

    public function example()
    {
        $theme = Theme::uses(Cache::get('theme', 'default'))->layout('front');
        try {
            $page = Page::where('slug', '=', 'home')->firstOrFail();
            $page->body = Storage::disk('themesbase')->get('flatly/views/pages/' . $page->slug . '.blade.php');
            $theme->set('meta_keywords', $page->meta_keywords);
            $theme->set('meta_description', $page->meta_description);
            $theme->setTitle('Home');
        } catch (ModelNotFoundException $e) {
            abort(404);
        }
        $post = new Post;
        $posts = $post->getAll();
        return $theme->scope('pages.home', compact('page', 'posts'))->render();
    }



    public function dashboard()
    {
        $theme = Theme::uses('coreui')->layout('coreui');
        ///$theme = Theme::uses('coreui')->layout('admincp');
        return $theme->scope('Admin/Pages/dashboard')->render();
        //return view('Admin/Pages/dashboard');

/*
$theme = Theme::uses('flatly')->layout('front');
return $theme->scope('pages.home', compact('page', 'posts'))->render();
**/


    }

    public function index(Request $request)
    {
        $items = Page::orderBy('name', 'ASC');
        if ($request->get('status') != '') {
            $items->where('status', '=', $request->get('status'));
        }
        $items = $items->paginate(15);
        return view('posts::Admin/Pages/index', ['items' => $items]);
    }

    public function add(Request $request)
    {
        $model = new Page();
        if ($request->getMethod() == 'POST') {
            $this->validate($request, [
                'name' => 'required|unique:pages|max:255',
                'body' => 'required',
                'slug' => 'unique:pages'
            ]);
            $data = $request->all();
            $data['user_id'] = Auth::user()->id;
            $model->add($data);
            return redirect()->route('admin.pages.index')->with('success', 'Page has been saved');
        }
        return view('posts::Admin/Pages/add', ['model' => $model]);
    }

    public function edit(Request $request, $id)
    {
        $model = Page::find($id);
        $errors = [];
        if ($request->getMethod() == 'POST') {
            $validator = Validator::make($request->all(), [
                'name' => [
                    'required',
                    Rule::unique('pages')->ignore($model->id)
                ],
                'slug' => [
                    Rule::unique('pages')->ignore($model->id)
                ],
                'body' => 'required'
            ]);
            if (!$validator->fails()) {
                $data = $request->all();
                $data['user_id'] = Auth::user()->id;
                $model->edit($data);
                return redirect()->route('admin.pages.index')->with('success', 'Page has been saved');
            } else {
                $errors = $validator->errors()->getMessages();
            }
        }
        $model->body = Storage::disk('themesbase')->get('default/views/pages/' . $model->slug . '.blade.php');
        return view('posts::Admin/Pages/edit', ['model' => $model, 'errors' => $errors]);
    }

    public function delete($id)
    {
        $model = Page::find($id)->delete();
        return redirect()->route('admin.pages.index')->with('success', 'Page has been saved');
    }

    public function status($id)
    {
        $model = Page::find($id);
        $model->status = !$model->status;
        $model->save();
        return redirect()->route('admin.pages.index')->with('success', 'Page has been saved');
    }

    public function order(Request $request)
    {
        if ($request->getMethod() == 'POST') {
            $items = json_decode($request->get('items'), true);
            foreach ($items as $index => $id) {
                $item = Page::find($id);
                $item->ord = $index;
                $item->save();
            }
            return response()->json([
                'status' => true
            ]);
        }
        $items = Page::orderBy('ord', 'ASC')->get();
        return view('posts::Admin/Pages/order', ['items' => $items]);
    }

    public function simpleSave(Request $request)
    {
        $model = new Page;
        $response = $model->simpleSave($request->all());
        return response()->json($response);
    }
}
