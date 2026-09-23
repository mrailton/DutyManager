<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['name', 'organiser', 'start_time', 'end_time', 'covered', 'notes', 'confirmed'])]
class Duty extends Model
{
    use HasFactory;

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(Member::class, 'duty_members');
    }

    public function vehicles(): BelongsToMany
    {
        return $this->belongsToMany(Vehicle::class, 'duty_vehicles');
    }

    protected function casts(): array
    {
        return [
            'start_time' => 'datetime',
            'end_time' => 'datetime',
            'covered' => 'boolean',
            'confirmed' => 'boolean',
        ];
    }
}
