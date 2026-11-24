<?php

/* BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_settings', function (Blueprint $table) {
            $table->id();
            $table->string('company_name_ar')->default('شركتي');
            $table->string('company_name_en')->default('My Company');
            $table->string('address_ar')->nullable();
            $table->string('address_en')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('tax_number')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('currency_code', 3)->default('EGP');
            $table->string('currency_symbol_ar', 10)->default('ج.م');
            $table->string('currency_symbol_en', 10)->default('EGP');
            $table->unsignedInteger('default_tax_rate')->default(1400); // basis points: 1400 = 14%
            $table->string('quotation_prefix', 10)->default('QUO');
            $table->string('invoice_prefix', 10)->default('INV');
            $table->string('payment_prefix', 10)->default('PAY');
            $table->string('expense_prefix', 10)->default('EXP');
            $table->string('default_locale', 2)->default('ar');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_settings');
    }
};
