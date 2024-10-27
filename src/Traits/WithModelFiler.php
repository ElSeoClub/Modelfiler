<?php

namespace Elseoclub\Modelfiler\Traits;

use Elseoclub\ModelFiler\Models\ModelFile;
use Illuminate\Database\Eloquent\Relations\MorphMany;


trait WithModelFiler
{
    public function modelFiles(): MorphMany
    {
        return $this->morphMany(ModelFile::class, 'fileable');
    }


    public function addFile(string $type, string $name, string $extension, string $path, string $storage = 'local', ?string $accept = null ): ModelFile
    {
        return $this->modelFiles()->create([
            'type' => $type,
            'name' => $name,
            'extension' => $extension,
            'storage' => $storage,
            'accept' => $accept,
            'path' => $path,
        ]);
    }

    public function clearFiles(): void
    {
        $this->modelFiles()->delete();
    }

    public function deleteFile(ModelFile $file): void
    {
        $file->delete();
    }
}
