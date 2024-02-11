<?php

namespace App\Models\Misc;

use App\Models\Account\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reviews extends Model
{
    use HasFactory;
    protected $with=["user"];


    public function user()
    {
        return $this->belongsTo(User::class, "account_id", "account_id");
    }
}
