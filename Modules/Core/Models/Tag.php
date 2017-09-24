<?php
namespace App\Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;
// Laravel\Scout\Searchable;
use Storage;
use Cache;

class Tag extends Model
{
    //use Searchable;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tags';

    protected $fillable = [
        'name',
        'body',
        'meta_keywords',
        'meta_description'
    ];

    public function user()
    {
        return $this->belongsTo('App\Modules\Core\Models\User');
    }

    public function postTags()
    {
        return $this->hasMany('App\Modules\Core\Models\PostTag');
    }

    public function add($postArray)
    {
        $this->name = $postArray['name'];
        $this->slug = str_slug($this->name, '-');
        $this->user_id = $postArray['user_id'];
        $this->meta_keywords = $postArray['meta_keywords'];
        $this->meta_description = $postArray['meta_description'];
        $this->save();
        // store the contents
        $path = Cache::get('theme', 'flatly') . '/views/tags/';
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
        $path = Cache::get('theme', 'flatly') . '/views/tags/';
        if (!Storage::disk('themesbase')->exists($path . $this->slug . '.blade.php')) {
            Storage::disk('themesbase')->copy($path . 'view.blade.php', $path . $this->slug . '.blade.php');
        }
        return $this;
    }

    public function simpleSave($data)
    {
        if (!empty($data['many'])) {
            $data['ids'] = json_decode($data['ids'], true);
            switch ($data['type']) {
                case 'delete':
                    Tag::whereIn('id', $data['ids'])->delete();
                    break;
                case 'toggle-statuses':
                    $active_items = Tag::whereIn('id', $data['ids'])->where('status', '=', 1)->get();
                    $pending_items = Tag::whereIn('id', $data['ids'])->where('status', '=', 0)->get();
                    foreach ($active_items as $item) {
                        $item->status = 0;
                        $item->save();
                    }
                    foreach ($pending_items as $item) {
                        $item->status = 1;
                        $item->save();
                    }
                    break;
            }
        }
        return [
            'status' => true,
            'ids' => $data['ids']
        ];
    }

    public function delete()
    {
        $path = Cache::get('theme', 'flatly') . '/views/tags/' . $this->slug . '.blade.php';
        if (Storage::disk('themesbase')->exists($path)) {
            Storage::disk('themesbase')->delete($path);
        }
        return parent::delete();
    }

    public function searchLogic($searchData, $admin = false)
    {
        if (!empty($searchData['keyword'])) {
            $results = Tag::search($searchData['keyword'])->get();
        } else {
            $results = [];
        }
        foreach ($results as $key => $row) {
            if ($admin) {
                $results[$key]->url = route('admin.tags.edit', ['id' => $row->id]);
            } else {
                $results[$key]->url = route('tags.view', ['slug' => $row->slug]);
            }
        }
        return $results;
    }
}
