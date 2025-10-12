<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Phase extends Model
{
    use HasFactory;
    protected $casts = [
        'i_depend' => 'array',
        'depend_me' => 'array',
    ];
    protected $fillable = [
        'project_id',
        'status_id',
        'substatus_id',
        'title',
        'index',
        'days',
        'comments',
        'percentage',
        'percentage_planned',
        'percentage_progress',
        'start_date',
        'end_date',
        'real_end_date',
        'restriction_start_date',
        'restriction_end_date',
        'depend_me',
        'i_depend',
    ];

    // Relaciones
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    public function substatus()
    {
        return $this->belongsTo(Substatus::class);
    }

    public function deliveries()
    {
        return $this->hasMany(Delivery::class)->orderBy('index');
    }
}
