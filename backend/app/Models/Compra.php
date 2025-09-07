<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Compra extends Model
{
    protected $fillable = [
        'fornecedor',
        'total'
    ];

    protected $casts = [
        'total' => 'decimal:2'
    ];

    public function produtos()
    {
        return $this->belongsToMany(Produto::class, 'compra_produto')
                    ->withPivot('quantidade', 'preco_unitario', 'subtotal')
                    ->withTimestamps();
    }

    public function calcularTotal()
    {
        $this->total = $this->produtos()->sum('compra_produto.subtotal');
        $this->save();
    }
}
