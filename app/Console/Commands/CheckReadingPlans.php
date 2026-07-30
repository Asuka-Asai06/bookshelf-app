<?php

namespace App\Console\Commands;

use App\Enums\ReadingPlanStatus;
use App\Models\ReadingPlan;
use App\Notifications\ReadingPlanReminderNotification;
use Illuminate\Console\Command;

class CheckReadingPlans extends Command
{
    protected $signature = 'reading-plans:check';

    protected $description = '読書計画の期限チェックと通知';

    public function handle(): int
    {
        $this->updateOverduePlans();

        $this->sendReminderNotifications();

        return Command::SUCCESS;
    }

    /**
     * 期限を過ぎた読書計画を更新する。
     */
    private function updateOverduePlans(): void
    {
        ReadingPlan::whereDate('target_date', '<', today())
            ->where('status', '!=', ReadingPlanStatus::Completed)
            ->update([
                'status' => ReadingPlanStatus::Overdue,
            ]);
    }

    /**
     * リマインダー通知を送信する。
     */
    private function sendReminderNotifications(): void
    {
        $this->sendThreeDaysBeforeNotification();

        $this->sendDueDateNotification();

        $this->sendThreeDaysAfterNotification();
    }

    /**
     * 期限3日前通知
     */
    private function sendThreeDaysBeforeNotification(): void
    {
        $plans = ReadingPlan::with(['user', 'book'])
            ->whereDate(
                'target_date',
                today()->addDays(3)
            )
            ->get();

        foreach ($plans as $plan) {
            if ($this->alreadySent($plan, 'three_days_before')) {
                continue;
            }
            $plan->user->notify(
                new ReadingPlanReminderNotification(
                    $plan,
                    'three_days_before'
                )
            );
        }
    }

    /**
     * 期限当日通知
     */
    private function sendDueDateNotification(): void
    {
        $plans = ReadingPlan::with(['user', 'book'])
            ->whereDate(
                'target_date',
                today()
            )
            ->get();

        foreach ($plans as $plan) {
            if ($this->alreadySent($plan, 'on_due_date')) {
                continue;
            }
            $plan->user->notify(
                new ReadingPlanReminderNotification(
                    $plan,
                    'on_due_date'
                )
            );
        }
    }

    /**
     * 期限3日超過通知
     */
    private function sendThreeDaysAfterNotification(): void
    {
        $plans = ReadingPlan::with(['user', 'book'])
            ->whereDate(
                'target_date',
                today()->subDays(3)
            )
            ->get();

        foreach ($plans as $plan) {
            if ($this->alreadySent($plan, 'on_due_date')) {
                continue;
            }
            $plan->user->notify(
                new ReadingPlanReminderNotification(
                    $plan,
                    'three_days_after'
                )
            );
        }
    }

    /**
     * 指定した読書計画・通知タイミングの通知が既に送信済みか確認する。
     *
     * @param  ReadingPlan  $plan  チェック対象の読書計画
     * @param  string  $timing  チェック対象の通知タイミング
     * @return bool 送信済みの場合true、未送信の場合false
     */
    private function alreadySent(ReadingPlan $plan, string $timing): bool
    {
        return $plan->user
            ->notifications()
            ->where('type', ReadingPlanReminderNotification::class)
            ->whereJsonContains(
                'data->reading_plan_id',
                $plan->id
            )
            ->whereJsonContains(
                'data->timing',
                $timing
            )
            ->exists();
    }
}
