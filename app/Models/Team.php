<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Team extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'team_status',
        'looking_for_description',
    ];

    public function participants()
    {
        return $this->hasMany(Participant::class);
    }
}
