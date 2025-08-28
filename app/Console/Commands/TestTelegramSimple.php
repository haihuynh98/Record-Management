<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TestTelegramSimple extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:test-simple';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Telegram API with simple message';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $token = config('services.telegram.bot_token');
        $chatId = config('services.telegram.chat_id');
        
        $this->info("Testing Telegram API...");
        $this->info("Token: " . substr($token, 0, 20) . "...");
        $this->info("Chat ID: " . $chatId);
        
        // Test getMe
        $response = Http::get("https://api.telegram.org/bot{$token}/getMe");
        $this->info("GetMe response: " . $response->body());
        
        if ($response->ok()) {
            // Test send message
            $message = "🧪 Test message from Laravel at " . now()->format('Y-m-d H:i:s');
            
            $response = Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'HTML'
            ]);
            
            $this->info("SendMessage response: " . $response->body());
            
            if ($response->ok()) {
                $this->info("✅ Telegram test successful!");
            } else {
                $this->error("❌ Failed to send message: " . $response->body());
            }
        } else {
            $this->error("❌ Bot token invalid: " . $response->body());
        }
    }
}
