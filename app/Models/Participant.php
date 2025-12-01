<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Participant extends Model
{
    protected $fillable = [
        'team_id',
        'name',
        'age',
        'email',
        'phone',
        'gender',
        'company_name',
        'portfolio_url',
        'linkedin_url',
        'role',
        'years_of_experience',
        'background',
        'tshirt_size',
        'dietary_restrictions',
        'mandatory_attendance_confirmed',
        'looking_for_job',
        'resume_path',
    ];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }
}
