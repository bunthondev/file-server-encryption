<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('files')) {
            Schema::create('files', function (Blueprint $table) {
                $table->id();
                $table->foreignId('bucket_id')->constrained()->onDelete('cascade');
                $table->string('original_name');
                $table->string('encrypted_name');
                $table->string('mime_type');
                $table->integer('size');
                $table->string('path');
                $table->timestamps();
            });
        } else {
            // If table exists, modify it
            Schema::table('files', function (Blueprint $table) {
                // Add bucket_id if it doesn't exist
                if (!Schema::hasColumn('files', 'bucket_id')) {
                    $table->foreignId('bucket_id')->constrained()->onDelete('cascade');
                }
            });

            // Drop user_id column if it exists
            if (Schema::hasColumn('files', 'user_id')) {
                // For SQLite, we need to recreate the table
                DB::statement('CREATE TABLE files_new (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    bucket_id INTEGER NOT NULL,
                    original_name VARCHAR NOT NULL,
                    encrypted_name VARCHAR NOT NULL,
                    mime_type VARCHAR NOT NULL,
                    size INTEGER NOT NULL,
                    path VARCHAR NOT NULL,
                    created_at DATETIME,
                    updated_at DATETIME,
                    FOREIGN KEY (bucket_id) REFERENCES buckets(id) ON DELETE CASCADE
                )');

                // Copy data from old table to new table
                DB::statement('INSERT INTO files_new (id, bucket_id, original_name, encrypted_name, mime_type, size, path, created_at, updated_at)
                    SELECT id, bucket_id, original_name, encrypted_name, mime_type, size, path, created_at, updated_at FROM files');

                // Drop old table and rename new table
                Schema::drop('files');
                DB::statement('ALTER TABLE files_new RENAME TO files');
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('files');
    }
}; 