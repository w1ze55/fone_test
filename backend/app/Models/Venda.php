<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Venda extends Model
{
    protected $fillable = [
        'cliente',
        'total',
        'lucro',
        'cancelada'
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'lucro' => 'decimal:2',
        'cancelada' => 'boolean'
    ];

    public function produtos()
    {
        return $this->belongsToMany(Produto::class, 'venda_produto')
                    ->withPivot('quantidade', 'preco_unitario', 'custo_unitario', 'subtotal', 'lucro_unitario')
                    ->withTimestamps();
    }

    public function calcularTotalELucro()
    {
        $this->total = $this->produtos()->sum('venda_produto.subtotal');
        $this->lucro = $this->produtos()->sum('venda_produto.lucro_unitario');
        $this->save();
    }

    public function cancelar()
    {
        if (!$this->cancelada) {
            // Reverter estoque
            foreach ($this->produtos as $produto) {
                $quantidade = $produto->pivot->quantidade;
                $produto->adicionarEstoque($quantidade);
            }
            
            $this->cancelada = true;
            $this->save();
        }
    }
}
