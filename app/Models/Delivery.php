<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Delivery extends Model
{
    use HasFactory;
    protected $casts = [
        'i_depend' => 'array',
        'depend_me' => 'array',
    ];
    protected $fillable = [
        'project_id',
        'phase_id',
        'status_id',
        'substatus_id',
        'priority_id',
        'user_id',
        'sprint_id',
        'index',
        'title',
        'percentage',
        'percentage_planned',
        'percentage_progress',
        'days',
        'start_date',
        'end_date',
        'real_end_date',
        'restriction_start_date',
        'restriction_end_date',
        'depend_me',
        'i_depend'
    ];

    // Relaciones

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function phase()
    {
        return $this->belongsTo(Phase::class, 'phase_id');
    }

    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    public function substatus()
    {
        return $this->belongsTo(Substatus::class);
    }

    public function priority()
    {
        return $this->belongsTo(Priority::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function sprint()
    {
        return $this->belongsTo(Sprint::class);
    }

    public function parentDelivery()
    {
        return $this->belongsTo(Delivery::class, 'delivery_id');
    }

    public function childDeliveries()
    {
        return $this->hasMany(Delivery::class, 'delivery_id');
    }

    public function activities()
    {
        return $this->hasMany(Activity::class)->orderBy('index');
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'delivery_tag');
    }

    public function requirements()
    {
        return $this->belongsToMany(Requirement::class, 'delivery_requirement');
    }
}
