<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Registro extends Model
{
    protected $table = 'registros';
    public $timestamps = false;

    protected $fillable = [
        'extrato_id',
        'carteira_id',
        'user_id',
        'debito',
        'credito',
        'descricao',
        'created_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
