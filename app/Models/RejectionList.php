<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RejectionList extends Model
{
    protected $fillable = ['email', 'email_hash'];

    protected $casts = [
        'email' => 'encrypted',
    ];
    //
}
