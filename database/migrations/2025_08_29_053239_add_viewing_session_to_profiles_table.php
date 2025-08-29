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
            $table->unsignedBigInteger('viewing_user_id')->nullable();
            $table->timestamp('viewing_started_at')->nullable();
            $table->string('viewing_session_id', 255)->nullable();
            
            // Index để tối ưu query
            $table->index(['viewing_user_id', 'viewing_started_at']);
            $table->index('viewing_session_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropIndex(['viewing_user_id', 'viewing_started_at']);
            $table->dropIndex(['viewing_session_id']);
            $table->dropColumn(['viewing_user_id', 'viewing_started_at', 'viewing_session_id']);
        });
    }
};
