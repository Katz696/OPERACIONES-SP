<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Activity extends Model
{
    use HasFactory;
    protected $casts = [
        'i_depend' => 'array',
        'depend_me' => 'array',
    ];
    protected $fillable = [
        'delivery_id',
        'status_id',
        'substatus_id',
        'priority_id',
        'user_id',
        'title',
        'percentage',
        'percentage_planned',
        'percentage_progress',
        'index',
        'days',
        'comments',
        'start_date',
        'end_date',
        'real_end_date',
        'restriction_start_date',
        'restriction_end_date',
        'depend_me',
        'i_depend'
    ];

    // Relaciones

    public function delivery()
    {
        return $this->belongsTo(Delivery::class);
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

    public function parentActivity()
    {
        return $this->belongsTo(Activity::class, 'activity_id');
    }

    public function childActivities()
    {
        return $this->hasMany(Activity::class, 'activity_id');
    }
}
