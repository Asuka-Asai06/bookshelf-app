<?php

namespace App\Models;

use App\Enums\ReadingPlanStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReadingPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'book_id',
        'target_date',
        'status',
        'completed_at',
    ];

    protected $casts = [
        'status' => ReadingPlanStatus::class,
        'target_date' => 'date',
        'completed_at' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    /**
     * 読書状態で絞り込む。
     *
     * @param  mixed  $query
     * @param  mixed  $status
     */
    public function scopeStatus(Builder $query, ?string $status): Builder
    {
        return $query->when(
            $status,
            fn ($query) => $query->where('status', $status)
        );
    }

    /**
     * 当日期限を表示する
     */
    public function displayStatus(): string
    {
        if (
            $this->status === ReadingPlanStatus::In_Progress
            && $this->target_date->isToday()
        ) {
            return '本日期限';
        }

        return $this->status->label();
    }

    /**
     * 本日期限の色分け
     */
    public function displayStatusClass(): string
    {
        if (
            $this->status === ReadingPlanStatus::In_Progress
            && $this->target_date->isToday()
        ) {
            return 'bg-yellow-100 text-yellow-800';
        }

        return $this->status->badgeClass();
    }
}
