<?php
namespace App\Modules\Posts\Models;

use Illuminate\Database\Eloquent\Model;
//use Laravel\Scout\Searchable;
use Cache;

class Category extends Model
{
    //use Searchable;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'categories';

    protected $fillable = [
        'name',
        'meta_keywords',
        'meta_description'
    ];

    public function posts()
    {
        return $this->hasMany('App\Modules\Posts\Models\Post');
    }

    public function fields()
    {
        return $this->hasMany('App\Modules\Posts\Models\Field');
    }

    public function user()
    {
        return $this->belongsTo('App\Modules\Users\Models\User');
    }

    public function searchLogic($searchData, $admin = false)
    {
        if (!empty($searchData['keyword'])) {
            $results = Category::search($searchData['keyword'])->get();
        } else {
            $results = [];
        }
        foreach ($results as $key => $row) {
            if ($admin) {
                $results[$key]->url = route('admin.categories.edit', ['id' => $row->id]);
            } else {
                $results[$key]->url = route('categories.view', ['slug' => $row->slug]);
            }
        }
        return $results;
    }

    public function simpleSave($data)
    {
        if (!empty($data['many'])) {
            $data['ids'] = json_decode($data['ids'], true);
            switch ($data['type']) {
                case 'delete':
                    Category::whereIn('id', $data['ids'])->delete();
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
        $this->slug = str_slug($this->name, '-');
        $this->user_id = $postArray['user_id'];
        $this->ord = Category::count();
        $this->meta_keywords = $postArray['meta_keywords'];
        $this->meta_description = $postArray['meta_description'];
        $this->save();
        // store the contents
        $path = Cache::get('theme', 'flatly') . '/views/categories/';
        if (!Storage::disk('themesbase')->exists($path . $this->slug . '.blade.php')) {
            Storage::disk('themesbase')->copy($path . 'view.blade.php', $path . $this->slug . '.blade.php');
        }
        return $this;
    }

    public function edit($postArray)
    {
        $this->name = $postArray['name'];
        $this->slug = str_slug($this->name, '-');
        $this->user_id = $postArray['user_id'];
        $this->meta_keywords = $postArray['meta_keywords'];
        $this->meta_description = $postArray['meta_description'];
        $this->save();
        // store the contents
        $path = Cache::get('theme', 'flatly') . '/views/categories/';
        if (!Storage::disk('themesbase')->exists($path . $this->slug . '.blade.php')) {
            Storage::disk('themesbase')->copy($path . 'view.blade.php', $path . $this->slug . '.blade.php');
        }
        return $this;
    }
}
