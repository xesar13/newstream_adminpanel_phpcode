<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tbl_ad_spaces', function (Blueprint $table) {
            $table->id();
            $table->integer('language_id')->index('language_id');
            $table->string('ad_space');
            $table->integer('ad_featured_section_id');
            $table->string('ad_image')->nullable();
            $table->string('web_ad_image')->nullable();
            $table->string('ad_url')->nullable();
            $table->date('date');
            $table->tinyInteger('status')->default(1)->comment('0-deactive, 1-active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_ad_spaces');
    }
};
