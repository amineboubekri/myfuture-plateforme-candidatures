<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'university_name',
        'country',
        'program',
        'status',
        'priority_level',
        'estimated_completion_date',
    ];

    public function documents()
    {
        return $this->hasMany(Document::class);
    }
    public function steps()
    {
        return $this->hasMany(ApplicationStep::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
