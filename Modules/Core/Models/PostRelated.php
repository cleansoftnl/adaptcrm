<?php
namespace App\Modules\Core\Models;

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
        return $this->belongsTo('App\Modules\Core\Models\Post', 'to_post_id');
    }

    public function fromPost()
    {
        return $this->belongsTo('App\Modules\Core\Models\Post', 'from_post_id');
    }
}