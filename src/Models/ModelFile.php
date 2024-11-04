<?php

namespace Elseoclub\Modelfiler\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

class ModelFile extends Model {
    protected $table = 'model_files';

    public $incrementing = false;

    // Especifica el tipo de clave primaria
    protected $keyType = 'string';

    protected static function boot() {
        parent::boot();

        // Genera un UUID antes de crear el registro si no se ha definido un ID
        static::creating( function ( $model ) {
            if ( empty( $model->id ) ) {
                $model->id = (string) Str::uuid();
            }
        } );
    }

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
