<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory;
    use Notifiable;
    use SoftDeletes;

    protected $fillable = ['name', 'email', 'password', 'last_login_at'];
    protected $hidden = ['password', 'remember_token'];

    public function getGravatarAttribute(): string
    {
        $hash = $this->attributes['email']
                |> mb_trim(...)
                |> mb_strtolower(...)
                |> md5(...);
        return "https://www.gravatar.com/avatar/{$hash}";
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
