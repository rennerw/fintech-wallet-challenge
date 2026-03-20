<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transacao extends Model
{

    protected $table = 'transacoes';

    protected $fillable = [
        'de_user_id',
        'para_user_id',
        'valor',
        'status', // 'pendente', 'concluida', 'falhou'
        'tipo',
        'completed_at',
        'motivo_falha',
    ];

    public function deUser()
    {
        return $this->belongsTo(User::class, 'de_user_id');
    }

    public function paraUser()
    {
        return $this->belongsTo(User::class, 'para_user_id');
    }
}
