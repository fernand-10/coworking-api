<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'space_id',
        'start_time',
        'end_time',
        'status',
        'notes',
    ];

    protected $appends = ['duration_hours', 'status_label'];

    protected function casts(): array
    {
        return [
            'start_time' => 'datetime',
            'end_time' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function space(): BelongsTo
    {
        return $this->belongsTo(Space::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function scopeOverlapping(Builder $query, $start, $end): Builder
    {
        return $query->whereIn('status', ['pending', 'confirmed'])
            ->where(function (Builder $q) use ($start, $end) {
                $q->where('start_time', '<', $end)
                  ->where('end_time', '>', $start);
            });
    }

    protected function durationHours(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->start_time || !$this->end_time) {
                    return 0;
                }
                return round($this->start_time->diffInMinutes($this->end_time) / 60, 2);
            }
        );
    }

    protected function statusLabel(): Attribute
    {
        return Attribute::make(
            get: fn () => match ($this->status) {
                'pending' => 'Pendiente',
                'confirmed' => 'Confirmada',
                'cancelled' => 'Cancelada',
                'completed' => 'Completada',
                default => 'Desconocido',
            }
        );
    }
}
