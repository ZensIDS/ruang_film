<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddGuestSupportToCartsAndOrders extends Migration
{
    public function up()
    {
        // Keranjang & order boleh tidak punya user (pembeli tamu)
        $this->setUserIdNullable('carts', true);
        $this->setUserIdNullable('orders', true);

        Schema::table('carts', function (Blueprint $table) {
            // Token acak di cookie pembeli tamu (bukan ID berurutan)
            $table->string('guest_token', 64)->nullable()->unique()->after('user_id');
        });

        Schema::table('orders', function (Blueprint $table) {
            // Token acak panjang: satu-satunya "kunci" tamu untuk membuka invoice & upload bukti bayar
            $table->string('access_token', 64)->nullable()->unique()->after('invoice_number');
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique(['access_token']);
            $table->dropColumn('access_token');
        });

        Schema::table('carts', function (Blueprint $table) {
            $table->dropUnique(['guest_token']);
            $table->dropColumn('guest_token');
        });

        // Data tamu tidak punya user, hapus dulu supaya kolom bisa kembali NOT NULL
        DB::table('orders')->whereNull('user_id')->delete();
        DB::table('carts')->whereNull('user_id')->delete();

        $this->setUserIdNullable('orders', false);
        $this->setUserIdNullable('carts', false);
    }

    protected function setUserIdNullable($tableName, $nullable)
    {
        $driver = DB::getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement(
                'ALTER TABLE `' . $tableName . '` MODIFY `user_id` BIGINT UNSIGNED ' . ($nullable ? 'NULL' : 'NOT NULL')
            );

            return;
        }

        // Driver lain (mis. sqlite/pgsql): butuh doctrine/dbal untuk ->change() di Laravel < 10
        Schema::table($tableName, function (Blueprint $table) use ($nullable) {
            $column = $table->unsignedBigInteger('user_id');

            if ($nullable) {
                $column->nullable();
            }

            $column->change();
        });
    }
}