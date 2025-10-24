<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Illuminate\Support\Facades\Auth;
class Project extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'customer_id',
        'user_id',
        'index',
        'title',
        'status_id',
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
        'slack'
    ];

    /**
     * Configuración del log
     */
    public function getActivitylogOptions(): LogOptions
{
    return LogOptions::defaults()
        ->useLogName('projects')
        ->logOnly([
            'title', 
            'status_id', 
            'percentage', 
            'percentage_planned', 
            'start_date', 
            'end_date'
        ])
        ->setDescriptionForEvent(function (string $eventName) {
            $user = Auth::user();
            $username = $user ? $user->name : 'Un usuario desconocido';
            $fecha = now()->format('d/m/Y h:i A');

            return match ($eventName) {
                'created' => "$username creó el proyecto el $fecha.",
                'updated' => "$username actualizó el proyecto el $fecha.",
                'deleted' => "$username eliminó el proyecto el $fecha.",
                default => "$username realizó una acción desconocida el $fecha.",
            };
        })
        ->logOnlyDirty()
        ->dontSubmitEmptyLogs();
}

    // Relaciones
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    public function phases()
    {
        return $this->hasMany(Phase::class)->orderBy('index');
    }

    public function deliveries()
    {
        return $this->hasMany(Delivery::class)->orderBy('index');
    }

    public function stakeholders()
    {
        return $this->hasMany(Stakeholder::class);
    }

    public function requirements()
    {
        return $this->hasMany(Requirement::class);
    }
    public function agreements(){
        return $this->hasMany(Agreement::class)->orderBy('date');
    }
}
