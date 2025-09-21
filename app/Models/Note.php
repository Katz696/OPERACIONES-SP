<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Note extends Model
{
    use HasFactory;

    protected $fillable = [
        'type_note_id',
        'impact_id',
        'content',
    ];

    // Relaciones

    public function typeNote()
    {
        return $this->belongsTo(TypeNote::class);
    }

    public function impact()
    {
        return $this->belongsTo(Impact::class);
    }

    /**
     * Polimórfica: notable (puede ser cualquier modelo que tenga notas)
     */
    public function notable()
    {
        return $this->morphTo();
    }
}
