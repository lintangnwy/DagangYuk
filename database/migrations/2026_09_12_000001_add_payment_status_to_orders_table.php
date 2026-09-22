<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_status')->default('paid')->after('payment_method');
            $table->string('midtrans_token')->nullable()->after('payment_status');
            $table->string('midtrans_url')->nullable()->after('midtrans_token');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['payment_status', 'midtrans_token', 'midtrans_url']);
        });
    }
};
