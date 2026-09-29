<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'invoice_imports',
            function (Blueprint $table) {
                $table->id();

                /*
                | Keep these as indexed IDs rather than foreign-key constraints.
                | This avoids unnecessary ALTER/constraint issues on shared
                | hosting while tenant ownership is still enforced in Laravel.
                */
                $table
                    ->unsignedBigInteger('business_id')
                    ->index();

                $table
                    ->unsignedBigInteger('uploaded_by')
                    ->index();

                $table->string(
                    'original_filename',
                    255
                );

                $table->string(
                    'stored_filename',
                    255
                );

                $table
                    ->string('storage_disk', 50)
                    ->default('local');

                $table->string(
                    'storage_path',
                    1000
                );

                $table
                    ->string('mime_type', 100);

                $table
                    ->string('file_extension', 20);

                $table
                    ->string('file_kind', 20)
                    ->index();

                $table
                    ->string('source', 20)
                    ->default('upload');

                $table
                    ->unsignedBigInteger('size_bytes')
                    ->default(0);

                $table
                    ->string('status', 40)
                    ->default('uploaded')
                    ->index();

                /*
                | Reserved for the next phases:
                | direct PDF text extraction / OCR / Vision / structured parsing.
                */
                $table
                    ->longText('extracted_text')
                    ->nullable();

                $table
                    ->json('parsed_data')
                    ->nullable();

                $table
                    ->text('failure_message')
                    ->nullable();

                $table
                    ->timestamp('processed_at')
                    ->nullable();

                $table->timestamps();

                $table->index([
                    'business_id',
                    'created_at',
                ]);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'invoice_imports'
        );
    }
};
