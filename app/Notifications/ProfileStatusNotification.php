<?php

namespace App\Notifications;

use App\Models\Profile;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ProfileStatusNotification extends Notification
{
    use Queueable;



    protected $profile;
    protected $type;
    protected $title;
    protected $body;

    /**
     * Create a new notification instance.
     */
    public function __construct(Profile $profile, string $type, string $title, string $body)
    {
        $this->profile = $profile;
        $this->type = $type;
        $this->title = $title;
        $this->body = $body;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase(object $notifiable): array
    {
        $data = [
            'profile_id' => $this->profile->id,
            'profile_code' => $this->profile->code,
            'type' => $this->type,
            'title' => $this->title,
            'body' => $this->body,
            'url' => config('app.url') . '/admin/profiles',
        ];

        // Thêm icon và color dựa trên type
        switch ($this->type) {
            case 'rejected':
                $data['icon'] = 'heroicon-o-x-circle';
                $data['color'] = 'danger';
                break;
            case 'cancelled':
                $data['icon'] = 'heroicon-o-exclamation-triangle';
                $data['color'] = 'warning';
                break;
            default:
                $data['icon'] = 'heroicon-o-information-circle';
                $data['color'] = 'info';
        }

        return $data;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'profile_id' => $this->profile->id,
            'profile_code' => $this->profile->code,
            'type' => $this->type,
            'title' => $this->title,
            'body' => $this->body,
            'url' => config('app.url') . '/admin/profiles',
        ];
    }
}
