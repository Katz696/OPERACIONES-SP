<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Upload extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'path',
        'name',
        'extension',
        'size',
    ];

    // Relaciones
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function uploadable()
    {
        return $this->morphTo();
    }
}
