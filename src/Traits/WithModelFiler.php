<?php

namespace Elseoclub\Modelfiler\Traits;

use Elseoclub\Modelfiler\Models\ModelFile;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

trait WithModelFiler {

    // Static method to get file types
    public static function getFileTypes(): array {
        return self::$fileTypes;
    }

    public function modelFiles(): MorphMany {
        return $this->morphMany( ModelFile::class, 'fileable' );
    }

    public function addFile( UploadedFile $file, string $type, string|null $storage = null ): ModelFile {
        if ( ! preg_match( '/^[a-zA-Z0-9_-]+$/', $type ) ) {
            throw new \InvalidArgumentException( "File type '$type' must consist only of letters, numbers, hyphens, or underscores." );
        }

        if ( ! isset( self::$fileTypes[ $type ] ) ) {
            if ( property_exists( $this, 'acceptAnyFile' ) && $this->acceptAnyFile ) {
                $fileTypeConfig = [
                    'accept'   => null,
                    'storage'  => $storage ?? 'local',
                    'max_size' => 102400,
                    'unique'   => false,
                    'path'     => strtolower( class_basename( $this ) ) . '/' . $type,
                ];
            } else {
                throw new \InvalidArgumentException( "File type '$type' is not defined and 'acceptAnyFile' is false." );
            }
        } else {
            $fileTypeConfig = self::$fileTypes[ $type ];
        }

        if ( empty( $fileTypeConfig['path'] ) ) {
            $fileTypeConfig['path'] = strtolower( class_basename( $this ) ) . '/' . $type;
        }
        if ( empty( $fileTypeConfig['unique'] ) ) {
            $fileTypeConfig['unique'] = false;
        }
        if ( empty( $fileTypeConfig['max_size'] ) ) {
            $fileTypeConfig['max_size'] = 102400;
        }
        if ( empty( $fileTypeConfig['storage'] ) ) {
            $fileTypeConfig['storage'] = 'local';
        }

        if ( $file->getSize() > ( $fileTypeConfig['max_size'] * 1024 ) ) {
            throw new \InvalidArgumentException( "File size exceeds maximum limit of {$fileTypeConfig['max_size']} KB." );
        }

        // Validate the extension for specific types
        $extension = $file->getClientOriginalExtension();
        if ( $fileTypeConfig['accept'] !== null && $fileTypeConfig['accept'] !== '*/*' ) {
            // Create an array of accepted extensions
            $acceptedExtensions = array_map( 'trim', explode( ',', $fileTypeConfig['accept'] ) );
            // Prepend a dot to each extension if not already present
            $acceptedExtensions = array_map( function ( $ext ) {
                return ltrim( $ext, '.' ); // Remove any leading dots
            }, $acceptedExtensions );

            if ( ! in_array( $extension, $acceptedExtensions ) ) {
                throw new \InvalidArgumentException( "File type '$extension' is not allowed for '$type'. Accepted types: " . implode( ', ', $acceptedExtensions ) );
            }
        }

        // Handle uniqueness if applicable
        if ( $fileTypeConfig['unique'] && $this->modelFiles()->where( 'type', $type )->exists() ) {
            $existingFile = $this->modelFiles()->where( 'type', $type )->first();

            // Remove the old file from storage
            Storage::disk( $existingFile->storage )->delete( $existingFile->path );

            // Remove the old file record from the database
            $existingFile->delete();
        }

        // Store the new file in the designated path
        $path = $file->store( $fileTypeConfig['path'], $fileTypeConfig['storage'] );

        // Remove multiple slashes with a single one
        $path = preg_replace( '/\/+/', '/', $path );

        // Create a new file record in the database
        return $this->modelFiles()->create( [
            'type'      => $type,
            'name'      => $file->getClientOriginalName(),
            'extension' => $extension,
            'storage'   => $fileTypeConfig['storage'],
            'path'      => $path,
        ] );
    }

    public function files( string|null $type = null ): MorphMany {
        return $this->modelFiles()->when( $type, function ( $query ) use ( $type ) {
            return $query->where( 'type', $type );
        } );
    }

    public function clearFiles( string|null $type = null ): void {
        if ( $type === null ) {
            $this->modelFiles()->get()->each( function ( ModelFile $file ) {
                Storage::disk( $file->storage )->delete( $file->path );
                $file->delete();
            } );
        } else {
            $this->modelFiles()->where( 'type', $type )->get()->each( function ( ModelFile $file ) {
                Storage::disk( $file->storage )->delete( $file->path );
                $file->delete();
            } );
        }
    }

    public function deleteFile( ModelFile|int $file ): void {
        if ( is_int( $file ) ) {
            $file = $this->modelFiles()->findOrFail( $file );
        }
        if ( $file->fileable_type !== get_class( $this ) || $file->fileable_id !== $this->id ) {
            throw new \InvalidArgumentException( "The file does not belong to this model." );
        }

        Storage::disk( $file->storage )->delete( $file->path );
        $file->delete();
    }

    public static function fileAccept( string|null $type ): string {
        if ( ! isset( self::$fileTypes[ $type ] ) ) {
            return '';
        }

        return self::$fileTypes[ $type ]['accept'] ?? '';
    }

    public static function fileRule( string|null $type, bool $required = false ): string {
        $rules = [];

        if ( $required ) {
            $rules[] = 'required';
        }

        $rules[] = 'file';

        if ( isset( self::$fileTypes[ $type ] ) && self::$fileTypes[ $type ]['accept'] !== null ) {
            $mimes   = str_replace( '.', '', self::$fileTypes[ $type ]['accept'] );
            $mimes   = str_replace( ' ', '', $mimes );
            $rules[] = 'mimes:' . $mimes;
        }

        $rules[] = 'max:' . ( self::$fileTypes[ $type ]['max_size'] ?? 102400 );

        return implode( '|', $rules );
    }
}
