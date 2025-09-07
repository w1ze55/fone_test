<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Venda;
use App\Models\Produto;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class VendaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $vendas = Venda::with('produtos')->orderBy('created_at', 'desc')->get();
        
        return response()->json($vendas);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'cliente' => 'required|string',
            'produtos' => 'required|array|min:1',
            'produtos.*.id' => 'required|exists:produtos,id',
            'produtos.*.quantidade' => 'required|integer|min:1',
            'produtos.*.preco_unitario' => 'required|numeric|min:0.01'
        ]);

        try {
            DB::beginTransaction();

            // Agrupar produtos por ID para verificar quantidade total solicitada
            $produtoQuantidades = [];
            foreach ($request->produtos as $produtoData) {
                $produtoId = $produtoData['id'];
                if (!isset($produtoQuantidades[$produtoId])) {
                    $produtoQuantidades[$produtoId] = 0;
                }
                $produtoQuantidades[$produtoId] += $produtoData['quantidade'];
            }

            // Verificar estoque antes de processar (com lock para evitar concorrência)
            foreach ($produtoQuantidades as $produtoId => $quantidadeTotal) {
                $produto = Produto::lockForUpdate()->find($produtoId);
                if (!$produto) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Produto não encontrado',
                        'produto_id' => $produtoId
                    ], 404);
                }
                
                if ($produto->estoque_atual < $quantidadeTotal) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Estoque insuficiente',
                        'produto' => $produto->nome,
                        'estoque_disponivel' => $produto->estoque_atual,
                        'quantidade_solicitada' => $quantidadeTotal,
                        'detalhes' => "Você está tentando vender {$quantidadeTotal} unidades, mas só há {$produto->estoque_atual} disponíveis."
                    ], 400);
                }
            }

            $venda = Venda::create([
                'cliente' => $request->cliente,
                'total' => 0,
                'lucro' => 0,
                'cancelada' => false
            ]);

            $total = 0;
            $lucroTotal = 0;

            foreach ($request->produtos as $produtoData) {
                $produto = Produto::find($produtoData['id']);
                $quantidade = $produtoData['quantidade'];
                $precoUnitario = $produtoData['preco_unitario'];
                $custoUnitario = $produto->custo_medio;
                $subtotal = $quantidade * $precoUnitario;
                $lucroUnitario = $quantidade * ($precoUnitario - $custoUnitario);
                
                // Anexar produto à venda
                $venda->produtos()->attach($produto->id, [
                    'quantidade' => $quantidade,
                    'preco_unitario' => $precoUnitario,
                    'custo_unitario' => $custoUnitario,
                    'subtotal' => $subtotal,
                    'lucro_unitario' => $lucroUnitario
                ]);

                // Baixar estoque
                $produto->baixarEstoque($quantidade);
                
                $total += $subtotal;
                $lucroTotal += $lucroUnitario;
            }

            $venda->total = $total;
            $venda->lucro = $lucroTotal;
            $venda->save();

            DB::commit();

            $venda->load('produtos');

            return response()->json([
                'message' => 'Venda registrada com sucesso',
                'venda' => $venda,
                'total' => $total,
                'lucro' => $lucroTotal
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erro ao registrar venda',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $venda = Venda::with('produtos')->find($id);
        
        if (!$venda) {
            return response()->json(['message' => 'Venda não encontrada'], 404);
        }

        return response()->json($venda);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        return response()->json(['message' => 'Atualização de venda não permitida'], 405);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        return response()->json(['message' => 'Exclusão de venda não permitida'], 405);
    }

    /**
     * Cancelar uma venda
     */
    public function cancelar(string $id): JsonResponse
    {
        $venda = Venda::with('produtos')->find($id);
        
        if (!$venda) {
            return response()->json(['message' => 'Venda não encontrada'], 404);
        }

        if ($venda->cancelada) {
            return response()->json(['message' => 'Venda já está cancelada'], 400);
        }

        try {
            DB::beginTransaction();
            
            $venda->cancelar();
            
            DB::commit();

            return response()->json([
                'message' => 'Venda cancelada com sucesso',
                'venda' => $venda
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erro ao cancelar venda',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
