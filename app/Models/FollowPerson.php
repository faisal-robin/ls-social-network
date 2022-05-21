<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FollowPerson extends Model
{
    use HasFactory;
    protected $table = 'follow_persons';
    protected $fillable = [
        'user_id',
        'following_user_id',
    ];
}
