<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tbl_news_view', function (Blueprint $table) {
            $table->integer('user_id')->nullable()->change();
        });

        Schema::table('tbl_breaking_news_view', function (Blueprint $table) {
            $table->integer('user_id')->nullable()->change();
        });

        DB::table('tbl_settings')->insert([
            [
                'type' => 'views_auth_mode',
                'message' => '1',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'type' => 'video_type_preference',
                'message' => 'normal_style',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_news_view', function (Blueprint $table) {
            $table->integer('user_id')->nullable(false)->change();
        });

        Schema::table('tbl_breaking_news_view', function (Blueprint $table) {
            $table->integer('user_id')->nullable(false)->change();
        });

        DB::table('tbl_settings')->whereIn('type', ['views_auth_mode', 'video_type_preference'])->delete();

    }
};
