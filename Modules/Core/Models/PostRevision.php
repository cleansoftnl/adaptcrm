<?php
namespace App\Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;

class PostRevision extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'post_revisions';

    public function post()
    {
        return $this->belongsTo('App\Modules\Core\Models\Post');
    }
}