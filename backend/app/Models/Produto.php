<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Produto extends Model
{
    protected $fillable = [
        'nome',
        'preco_venda',
        'custo_medio',
        'estoque_atual'
    ];

    protected $casts = [
        'preco_venda' => 'decimal:2',
        'custo_medio' => 'decimal:2',
        'estoque_atual' => 'integer'
    ];

    public function compras()
    {
        return $this->belongsToMany(Compra::class, 'compra_produto')
                    ->withPivot('quantidade', 'preco_unitario', 'subtotal')
                    ->withTimestamps();
    }

    public function vendas()
    {
        return $this->belongsToMany(Venda::class, 'venda_produto')
                    ->withPivot('quantidade', 'preco_unitario', 'custo_unitario', 'subtotal', 'lucro_unitario')
                    ->withTimestamps();
    }

    public function atualizarCustoMedio($novaQuantidade, $novoPrecoUnitario)
    {
        $estoqueAnterior = $this->estoque_atual;
        $custoAnterior = $this->custo_medio;
        
        $valorAnterior = $estoqueAnterior * $custoAnterior;
        $valorNovo = $novaQuantidade * $novoPrecoUnitario;
        $estoqueTotal = $estoqueAnterior + $novaQuantidade;
        
        if ($estoqueTotal > 0) {
            $this->custo_medio = ($valorAnterior + $valorNovo) / $estoqueTotal;
        }
        
        $this->estoque_atual = $estoqueTotal;
        $this->save();
    }

    public function baixarEstoque($quantidade)
    {
        if ($this->estoque_atual >= $quantidade) {
            $this->estoque_atual -= $quantidade;
            $this->save();
            return true;
        }
        return false;
    }

    public function adicionarEstoque($quantidade)
    {
        $this->estoque_atual += $quantidade;
        $this->save();
    }
}
