<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('uploads', function (Blueprint $table) {
            $table->id();
            $table->string('original_name');
            $table->string('stored_path');
            $table->enum('status', ['pending','processing','failed','completed'])->default('pending');
            $table->unsignedInteger('rows_processed')->default(0);
            $table->text('error')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('uploads');
    }
};
