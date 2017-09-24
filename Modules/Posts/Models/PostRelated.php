<?php
namespace App\Modules\Posts\Models;

use Illuminate\Database\Eloquent\Model;

class PostRelated extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'post_related';

    public function toPost()
    {
        return $this->belongsTo('App\Modules\Posts\Models\Post', 'to_post_id');
    }

    public function fromPost()
    {
        return $this->belongsTo('App\Modules\Posts\Models\Post', 'from_post_id');
    }
}