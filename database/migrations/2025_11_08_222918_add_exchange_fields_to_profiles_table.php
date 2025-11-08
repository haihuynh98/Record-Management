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
            $table->boolean('is_exchange')->default(false)->after('character_id');
            $table->string('exchange_character_id', 100)->nullable()->after('is_exchange');
            $table->string('exchange_profile_code', 64)->nullable()->after('exchange_character_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn(['is_exchange', 'exchange_character_id', 'exchange_profile_code']);
        });
    }
};
