<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRajaongkirFieldsToExpeditionsAndOrders extends Migration
{
    public function up()
    {
        Schema::table('expeditions', function (Blueprint $table) {
            $table->string('external_code')->nullable()->after('name');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('expedition_code')->nullable()->after('expedition_name');
            $table->string('expedition_service_code')->nullable()->after('expedition_service_name');
            $table->string('shipping_destination_id')->nullable()->after('postal_code');
            $table->string('shipping_etd')->nullable()->after('shipping_fee');
            $table->string('shipping_order_no')->nullable()->after('status');
            $table->string('shipping_airway_bill')->nullable()->after('shipping_order_no');
            $table->string('shipping_status')->nullable()->after('shipping_airway_bill');
            $table->string('shipping_status_label')->nullable()->after('shipping_status');
            $table->json('shipping_payload')->nullable()->after('shipping_status_label');
            $table->json('shipping_tracking_payload')->nullable()->after('shipping_payload');
            $table->dateTime('shipping_synced_at')->nullable()->after('shipping_tracking_payload');

            $table->index('shipping_order_no');
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['shipping_order_no']);
            $table->dropColumn([
                'expedition_code',
                'expedition_service_code',
                'shipping_destination_id',
                'shipping_etd',
                'shipping_order_no',
                'shipping_airway_bill',
                'shipping_status',
                'shipping_status_label',
                'shipping_payload',
                'shipping_tracking_payload',
                'shipping_synced_at',
            ]);
        });

        Schema::table('expeditions', function (Blueprint $table) {
            $table->dropColumn('external_code');
        });
    }
}
