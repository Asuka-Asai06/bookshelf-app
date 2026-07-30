<?php

namespace App\Notifications;

use App\Models\ReadingPlan;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ReadingPlanReminderNotification extends Notification
{
    use Queueable;

    public function __construct(
        private ReadingPlan $readingPlan,
        private string $timing
    ) {}

    public function via(object $notifiable): array
    {
        return [
            'database',
        ];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'title' => '読書計画のお知らせ',
            'body' => $this->getMessage(),
            'timing' => $this->timing,
            'reading_plan_id' => $this->readingPlan->id,
        ];
    }

    private function getMessage(): string
    {
        return match ($this->timing) {
            'three_days_before' => "「{$this->readingPlan->book->title}」の読書期限まであと3日です。",
            'on_due_date' => "「{$this->readingPlan->book->title}」の読書期限は今日です。",
            'three_days_after' => "「{$this->readingPlan->book->title}」の読書期限を3日過ぎています。",
            default => '読書計画のお知らせがあります。',
        };
    }
}
