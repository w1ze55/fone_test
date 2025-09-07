<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Compra;
use App\Models\Produto;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class CompraController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $compras = Compra::with('produtos')->orderBy('created_at', 'desc')->get();
        
        return response()->json($compras);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'fornecedor' => 'required|string',
            'produtos' => 'required|array|min:1',
            'produtos.*.id' => 'required|exists:produtos,id',
            'produtos.*.quantidade' => 'required|integer|min:1',
            'produtos.*.preco_unitario' => 'required|numeric|min:0.01'
        ]);

        try {
            DB::beginTransaction();

            $compra = Compra::create([
                'fornecedor' => $request->fornecedor,
                'total' => 0
            ]);

            $total = 0;

            foreach ($request->produtos as $produtoData) {
                $produto = Produto::find($produtoData['id']);
                $quantidade = $produtoData['quantidade'];
                $precoUnitario = $produtoData['preco_unitario'];
                $subtotal = $quantidade * $precoUnitario;
                
                // Anexar produto à compra
                $compra->produtos()->attach($produto->id, [
                    'quantidade' => $quantidade,
                    'preco_unitario' => $precoUnitario,
                    'subtotal' => $subtotal
                ]);

                // Atualizar estoque e custo médio
                $produto->atualizarCustoMedio($quantidade, $precoUnitario);
                
                $total += $subtotal;
            }

            $compra->total = $total;
            $compra->save();

            DB::commit();

            $compra->load('produtos');

            return response()->json([
                'message' => 'Compra registrada com sucesso',
                'compra' => $compra
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erro ao registrar compra',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $compra = Compra::with('produtos')->find($id);
        
        if (!$compra) {
            return response()->json(['message' => 'Compra não encontrada'], 404);
        }

        return response()->json($compra);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        return response()->json(['message' => 'Atualização de compra não permitida'], 405);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        return response()->json(['message' => 'Exclusão de compra não permitida'], 405);
    }
}
