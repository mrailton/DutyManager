<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ClinicalLevel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Member extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['name', 'clinical_level', 'driver'];

    public function duties(): BelongsToMany
    {
        return $this->belongsToMany(Duty::class, 'duty_members');
    }

    protected function casts(): array
    {
        return [
            'clinical_level' => ClinicalLevel::class,
            'driver' => 'boolean',
        ];
    }
}
