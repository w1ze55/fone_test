<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Produto;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProdutoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $produtos = Produto::select('id', 'nome', 'custo_medio', 'preco_venda', 'estoque_atual')->get();
        
        return response()->json($produtos);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'nome' => 'required|string|min:3',
            'preco_venda' => 'required|numeric|min:0.01'
        ]);

        $produto = Produto::create([
            'nome' => $request->nome,
            'preco_venda' => $request->preco_venda,
            'custo_medio' => 0,
            'estoque_atual' => 0
        ]);

        return response()->json($produto, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $produto = Produto::find($id);
        
        if (!$produto) {
            return response()->json(['message' => 'Produto não encontrado'], 404);
        }

        return response()->json($produto);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $produto = Produto::find($id);
        
        if (!$produto) {
            return response()->json(['message' => 'Produto não encontrado'], 404);
        }

        $request->validate([
            'nome' => 'sometimes|string|min:3',
            'preco_venda' => 'sometimes|numeric|min:0.01'
        ]);

        $produto->update($request->only(['nome', 'preco_venda']));

        return response()->json($produto);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $produto = Produto::find($id);
        
        if (!$produto) {
            return response()->json(['message' => 'Produto não encontrado'], 404);
        }

        $produto->delete();

        return response()->json(['message' => 'Produto excluído com sucesso']);
    }
}
