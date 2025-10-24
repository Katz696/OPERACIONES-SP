<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SnapProject extends Model
{
    use HasFactory;
    public $timestamps = true;
    protected $fillable = [
        'project_id',
        'user_id',
        'label',
        'percentage',
        'percentage_planned',
        'percentage_progress',
        'start_date',
        'end_date',
        'data',
    ];

    protected $casts = [
        'data' => 'array', // Esto te permite guardar el JSON de fases/entregables/actividades
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
