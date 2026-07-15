<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\VehicleRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['callsign', 'name', 'role'];

    public function duties(): BelongsToMany
    {
        return $this->belongsToMany(Duty::class, 'duty_vehicles');
    }

    protected function casts(): array
    {
        return [
            'role' => VehicleRole::class,
        ];
    }
}
