<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVariantColumnsToCartAndOrderItems extends Migration
{
    public function up()
    {
        // 1) Keranjang: tambah kolom varian + unique index baru
        Schema::table('cart_items', function (Blueprint $table) {
            $table->foreignId('merchandise_variant_id')
                ->nullable()
                ->after('merchandise_id')
                ->constrained('merchandise_variants')
                ->cascadeOnDelete();

            // Index baru dibuat dulu (diawali cart_id) supaya FK cart_id tetap punya index
            $table->unique(
                ['cart_id', 'merchandise_id', 'merchandise_variant_id'],
                'cart_items_cart_merch_variant_unique'
            );
        });

        // 2) Baru hapus unique lama (cart_id, merchandise_id)
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropUnique(['cart_id', 'merchandise_id']);
        });

        // 3) Order items: simpan snapshot varian
        Schema::table('order_items', function (Blueprint $table) {
            $table->foreignId('merchandise_variant_id')
                ->nullable()
                ->after('merchandise_id')
                ->constrained('merchandise_variants')
                ->nullOnDelete();
            $table->string('variant_name', 100)->nullable()->after('merchandise_name');
        });
    }

    public function down()
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['merchandise_variant_id']);
            $table->dropColumn(['merchandise_variant_id', 'variant_name']);
        });

        Schema::table('cart_items', function (Blueprint $table) {
            $table->unique(['cart_id', 'merchandise_id']);
        });

        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropUnique('cart_items_cart_merch_variant_unique');
            $table->dropForeign(['merchandise_variant_id']);
            $table->dropColumn('merchandise_variant_id');
        });
    }
}