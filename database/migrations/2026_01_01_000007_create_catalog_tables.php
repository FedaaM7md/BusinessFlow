<?php

/* BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('sku', 60)->unique();
            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->string('type', 20)->default('product'); // product|service
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('unit', 30)->default('unit');
            $table->unsignedBigInteger('price_minor');
            $table->unsignedBigInteger('cost_minor')->default(0);
            $table->unsignedInteger('tax_rate')->default(1400); // basis points
            $table->boolean('is_tracked')->default(true);
            $table->decimal('low_stock_threshold', 12, 2)->default(5);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['type', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
        Schema::dropIfExists('categories');
    }
};
