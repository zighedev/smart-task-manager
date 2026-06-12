<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Enums\TaskStatus;

class Task extends Model
{

    protected $fillable = ['title', 'description', 'status', 'user_id'];
    protected $hidden = ['updated_at'];

    protected function casts(): array 
    {
        return [
            'status' => TaskStatus::class,
        ];
    }

    protected function title(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => ucfirst($value),
            set: fn (string $value) => strtolower($value)
        );
    }

    public function scopeOfStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }


}
