<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id',
        'document_type',
        'file_path',
        'status',
        'comments',
        'uploaded_at',
        'validated_at',
        'validated_by',
    ];

    public function application()
    {
        return $this->belongsTo(Application::class);
    }
}
