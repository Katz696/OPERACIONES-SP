<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Requirement extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'stakeholder_id',
        'description',
    ];

    // Relaciones

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function stakeholder()
    {
        return $this->belongsTo(Stakeholder::class);
    }

    // Relación muchos a muchos con Delivery (por la tabla delivery_requirement)

    public function deliveries()
    {
        return $this->belongsToMany(Delivery::class, 'delivery_requirement');
    }
}
