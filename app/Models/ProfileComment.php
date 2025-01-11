<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfileComment extends Model
{
    protected $fillable = ['content', 'user_id', 'profile_user_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function profileUser()
    {
        return $this->belongsTo(User::class, 'profile_user_id');
    }

    public function replies()
    {
        return $this->hasMany(CommentReply::class, 'comment_id');
    }
} 