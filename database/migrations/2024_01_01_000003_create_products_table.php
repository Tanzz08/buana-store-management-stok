<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('sku', 100)->unique();
            $table->string('name', 255);
            $table->decimal('purchase_price', 15, 2);
            $table->decimal('selling_price', 15, 2);
            $table->string('unit', 50);
            $table->unsignedInteger('current_stock')->default(0);
            $table->unsignedInteger('minimum_stock');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            // Indexes for search performance (Requirement 3.6: search < 500ms)
            $table->index('name');
            $table->index('sku');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
