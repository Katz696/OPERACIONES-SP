<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Agreement extends Model
{
    /** @use HasFactory<\Database\Factories\AgreementFactory> */
    use HasFactory;
    protected $fillable = [
        'user_id',
        'project_id',
        'agreement_type_id',
        'date',
        'description',
        'priority_id',
        'ac',
        'responsible',
        'agreed_date',
        'status_id'
    ];
    public function user(){
        return $this->belongsTo(User::class);
    }
    public function project(){
        return $this->belongsTo(Delivery::class);
    }
    public function priority(){
        return $this->belongsTo(Priority::class);
    }
    public function status(){
        return $this->belongsTo(Status::class);
    }
    public function agreement_type(){
        return $this->belongsTo(AgreementType::class);
    }
}
