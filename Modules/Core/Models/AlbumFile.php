<?php
namespace App\Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;

class AlbumFile extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'album_files';

    public function album()
    {
        return $this->belongsTo('App\Modules\Core\Models\Album');
    }

    public function file()
    {
        return $this->belongsTo('App\Modules\Core\Models\File');
    }
}