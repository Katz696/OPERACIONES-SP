<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Stakeholder extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
    ];

    // Relaciones

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function requirements()
    {
        return $this->hasMany(Requirement::class);
    }
}
