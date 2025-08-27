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
        Schema::table('profiles', function (Blueprint $table) {
            $table->bigInteger('amount')->nullable()->after('code');
            $table->text('rejection_reason')->nullable()->after('amount');
            // Update status column to allow more values (0: pending, 1: approved, 2: rejected)
            $table->tinyInteger('status')->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn(['amount', 'rejection_reason']);
            $table->boolean('status')->default(false)->change();
        });
    }
};
