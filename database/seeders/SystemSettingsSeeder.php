<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SystemSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tạo setting mặc định cho login block
        SystemSetting::setValue(
            'login_blocked',
            '0',
            'Trạng thái chặn đăng nhập hệ thống (0 = mở, 1 = chặn)'
        );
    }
}
