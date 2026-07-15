<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMerchandiseManagementTables extends Migration
{
    public function up()
    {
        Schema::create('merchandise_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('merchandises', function (Blueprint $table) {
            $table->id();
            $table->foreignId('merchandise_category_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('image')->nullable();
            $table->decimal('price', 12, 2);
            $table->decimal('discount_price', 12, 2)->nullable();
            $table->unsignedInteger('weight')->default(0);
            $table->unsignedInteger('qty_stock')->default(0);
            $table->string('summary')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('expeditions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('service_name')->nullable();
            $table->decimal('fee', 12, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('rek_name');
            $table->string('rek_bank_name');
            $table->string('rek_bank_no');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('app_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('app_settings');
        Schema::dropIfExists('bank_accounts');
        Schema::dropIfExists('expeditions');
        Schema::dropIfExists('merchandises');
        Schema::dropIfExists('merchandise_categories');
    }
}
