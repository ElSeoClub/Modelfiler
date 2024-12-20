<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create( 'model_files', function ( Blueprint $table ) {
            $table->uuid( 'id' )->primary();
            $table->string('fileable_id');
            $table->string('fileable_type');
            $table->string( 'type' );
            $table->string( 'name' );
            $table->string( 'extension' );
            $table->string( 'storage' )->default( 'local' );
            $table->string( 'path' );
            $table->timestamps();
        } );
    }

    public function down(): void {
        Schema::dropIfExists( 'model_files' );
    }
};
