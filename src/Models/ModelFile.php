<?php

namespace Elseoclub\Modelfiler\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use InvalidArgumentException;

class ModelFile extends Model {
    protected $table = 'model_files';

    protected $fillable = [
        'fileable_id',
        'fileable_type',
        'type',
        'name',
        'extension',
        'storage',
        'path',
    ];

    public function fileable(): MorphTo {
        return $this->morphTo();
    }

}
