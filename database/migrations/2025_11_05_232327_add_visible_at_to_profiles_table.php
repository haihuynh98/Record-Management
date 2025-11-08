<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->timestamp('visible_at')->nullable()->after('created_at')
                ->index() // Important for query performance
                ->comment('Thời điểm profile sẽ hiển thị cho người phê duyệt');
        });
        
        // Backfill existing records: visible_at = created_at
        // Đối với các hồ sơ đã tồn tại, set visible_at = created_at để chúng hiển thị ngay
        DB::table('profiles')->whereNull('visible_at')->update([
            'visible_at' => DB::raw('created_at')
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropIndex(['visible_at']);
            $table->dropColumn('visible_at');
        });
    }
};
