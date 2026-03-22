<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transacao extends Model
{

    protected $table = 'transacoes';
    public $timestamps = false;

    protected $fillable = [
        'id',
        'de_user_id',
        'para_user_id',
        'valor',
        'status', // 'pendente', 'concluida', 'falhou'
        'tipo',
        'completed_at',
        'motivo_falha',
        'created_at'
    ];

    public function deUser()
    {
        return $this->belongsTo(User::class, 'de_user_id');
    }

    public function paraUser()
    {
        return $this->belongsTo(User::class, 'para_user_id');
    }

    public function extrato()
    {
        return $this->hasOne(Extrato::class, 'transacao_id', 'id');
    }
}
