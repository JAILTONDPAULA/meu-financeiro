<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Categoria extends Model
{
    protected $fillable = [
        'descricao',
        'icone',
    ];

    public function movimentacoes(): HasMany
    {
        return $this->hasMany(Movimentacao::class);
    }
}
