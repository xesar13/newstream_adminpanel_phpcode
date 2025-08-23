<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Si la tabla tbl_settings no existe, no hacer nada
        if (!Schema::hasTable('tbl_settings')) return;
        // Agrega el registro webhook_publish_endpoint_url si no existe
        $exists = DB::table('tbl_settings')->where('type', 'webhook_publish_endpoint_url')->exists();
        if (!$exists) {
            DB::table('tbl_settings')->insert([
                'type' => 'webhook_publish_endpoint_url',
                'message' => 'https://n8n-v2.qnzwva.easypanel.host/webhook/send_post', // valor por defecto
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasTable('tbl_settings')) return;
        DB::table('tbl_settings')->where('type', 'webhook_publish_endpoint_url')->delete();
    }
};
