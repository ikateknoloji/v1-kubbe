<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourierNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'message',
        'is_read',
        'read_by_user_id',
    ];

    public function readByUser()
    {
        return $this->belongsTo(User::class, 'read_by_user_id');
    }

}
