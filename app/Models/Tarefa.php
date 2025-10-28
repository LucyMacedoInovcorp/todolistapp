<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tarefa extends Model
{
    protected $fillable = [
        'titulo',
        'descricao',
        'concluida',
        'data_vencimento',
        'prioridade',
        'user_id',
        'session_id'
    ];

    protected $casts = [
        'concluida' => 'boolean',
        'data_vencimento' => 'date',
    ];

    /**
     * Relacionamento com o usuário
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
