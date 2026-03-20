<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Carteira extends Model
{
    protected $table = 'carteiras';

    protected $fillable = [
        'user_id',
        'valor_atual',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
