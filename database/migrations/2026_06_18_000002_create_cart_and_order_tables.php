<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCartAndOrderTables extends Migration
{
    public function up()
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained()->cascadeOnDelete();
            $table->foreignId('merchandise_id')->constrained('merchandises')->cascadeOnDelete();
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('unit_price', 12, 2);
            $table->timestamps();
            $table->unique(['cart_id', 'merchandise_id']);
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('expedition_id')->nullable()->constrained()->nullOnDelete();
            $table->string('expedition_name')->nullable();
            $table->string('expedition_service_name')->nullable();
            $table->string('recipient_name');
            $table->string('recipient_email')->nullable();
            $table->string('recipient_phone');
            $table->string('province_name')->nullable();
            $table->string('city_name')->nullable();
            $table->string('district_name')->nullable();
            $table->string('village_name')->nullable();
            $table->string('postal_code')->nullable();
            $table->text('full_address');
            $table->text('notes')->nullable();
            $table->decimal('shipping_fee', 12, 2)->default(0);
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->string('status')->default('waiting_payment');
            $table->dateTime('payment_due_at')->nullable();
            $table->string('payment_proof_path')->nullable();
            $table->dateTime('payment_submitted_at')->nullable();
            $table->dateTime('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('verification_note')->nullable();
            $table->text('rejection_note')->nullable();
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('merchandise_id')->nullable()->constrained('merchandises')->nullOnDelete();
            $table->string('merchandise_name');
            $table->string('merchandise_slug')->nullable();
            $table->string('merchandise_image')->nullable();
            $table->decimal('unit_price', 12, 2);
            $table->unsignedInteger('quantity');
            $table->unsignedInteger('weight')->default(0);
            $table->decimal('subtotal', 12, 2);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('carts');
    }
}
