<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Movimentacao extends Model
{
    protected $fillable = [
        'user_id',
        'categoria_id',
        'movimentacao_original_id',
        'valor',
        'data_registro',
        'tipo',
        'flg_credito',
        'recorrente',
        'faturamento_ym',
    ];

    protected function casts(): array
    {
        return [
            'valor' => 'decimal:2',
            'data_registro' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }

    public function original(): BelongsTo
    {
        return $this->belongsTo(Movimentacao::class, 'movimentacao_original_id');
    }

    public function parcelas(): HasMany
    {
        return $this->hasMany(Movimentacao::class, 'movimentacao_original_id');
    }
}
