<?php

namespace App;

use App\Support\PhoneNumber;
use Illuminate\Database\Eloquent\Model;

class Call extends Model
{
    protected $fillable = [
        'id',
        'client_telephone',
        'operator_id',
        'pbx_audio_url',
        'telegram_audio_url',
        'event',
        'direction',
        'call_duration',
        'dialog_duration',
        'uuid',
        'gateway',
        'date',
        'sent_sms',
        'created_at',
        'updated_at'
    ];

    public function feedback()
    {
        return $this->hasMany(Feedback::class);
    }

    public function operator()
    {
        return $this->belongsTo(Operator::class, 'operator_id');
    }

    public function getClientTelephoneAttribute($value)
    {
        return PhoneNumber::formatUz($value) ?? $value;
    }

    public function setClientTelephoneAttribute($value): void
    {
        $this->attributes['client_telephone'] = PhoneNumber::formatUz($value) ?? $value;
    }
}
