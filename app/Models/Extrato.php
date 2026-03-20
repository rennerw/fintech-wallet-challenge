<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Extrato extends Model
{
    protected $table = 'extratos';
    public $timestamps = false;

    protected $fillable = [
        'transacao_id',
        'status',
        'descricao',
        'posted_at',
        'created_at',
    ];

    public function transacao()
    {
        return $this->belongsTo(Transacao::class, 'transacao_id');
    }
}
