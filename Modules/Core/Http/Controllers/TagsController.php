<?php
namespace App\Modules\Core\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Core\Models\Post;
use App\Modules\Core\Models\Tag;
use Theme;
use Cache;

class TagsController extends Controller
{
    public function view($slug)
    {
        $post = new Post;
        $posts = $post->getAllByTagSlug($slug);
        $tag = $posts['tag'];
        $theme = Theme::uses(Cache::get('theme', 'flatly'))->layout('front');
        $theme->set('meta_keywords', $tag->meta_keywords);
        $theme->set('meta_description', $tag->meta_description);
        $theme->setTitle($tag->name);
        return $theme->scope('tags.view', compact('posts', 'tag'))->render();
    }
}
