<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('systems', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('english_name')->nullable();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('url')->nullable();
            $table->string('thumbnail')->nullable();
            $table->string('icon')->nullable();
            $table->string('color')->nullable();
            $table->integer('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('open_in_new_tab')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('systems');
    }
};
