<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Student extends Authenticatable
{
    use HasFactory, HasApiTokens;

    protected $fillable = [
        'nisn',
        'name',
        'password',
        'gender',
        'phone',
        'address',
        'photo',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function classes(): BelongsToMany
    {
        return $this->belongsToMany(ClassRoom::class, 'student_class', 'student_id', 'class_id')
            ->withPivot('is_active')
            ->withTimestamps();
    }

    public function activeClass()
    {
        return $this->classes()->wherePivot('is_active', true)->first();
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }
}

