<template>
  <div class="transacao-historico">
    <h3>📈 Histórico de Transações</h3>
    
    <div class="filtros">
      <div class="filtro-group">
        <label for="tipo-filtro">Filtrar por:</label>
        <select id="tipo-filtro" v-model="filtroTipo" class="form-select">
          <option value="todos">Todas as transações</option>
          <option value="compras">Apenas compras</option>
          <option value="vendas">Apenas vendas</option>
        </select>
      </div>
      
      <div class="filtro-group">
        <label for="produto-filtro">Produto:</label>
        <select id="produto-filtro" v-model="filtroProduto" class="form-select">
          <option value="">Todos os produtos</option>
          <option v-for="produto in produtos" :key="produto.id" :value="produto.id">
            {{ produto.nome }}
          </option>
        </select>
      </div>
    </div>
    
    <div v-if="transacoesFiltradas.length === 0" class="sem-transacoes">
      <p>Nenhuma transação encontrada.</p>
      <p>Registre compras e vendas para ver o histórico aqui!</p>
    </div>
    
    <div v-else class="transacoes-lista">
      <div 
        v-for="transacao in transacoesFiltradas" 
        :key="`${transacao.tipo}-${transacao.id}`" 
        class="transacao-card"
        :class="transacao.tipo"
      >
        <div class="transacao-header">
          <div class="transacao-info">
            <h4>{{ transacao.tipo === 'compra' ? '🛒 Compra' : '💰 Venda' }}</h4>
            <span class="transacao-data">{{ transacao.data }}</span>
          </div>
          <div class="transacao-total">
            <span class="total-label">Total:</span>
            <span class="total-value">R$ {{ transacao.total.toFixed(2) }}</span>
          </div>
        </div>
        
        <div class="transacao-detalhes">
          <div class="detalhe-item">
            <span class="label">{{ transacao.tipo === 'compra' ? 'Fornecedor:' : 'Cliente:' }}</span>
            <span class="valor">{{ transacao.clienteFornecedor }}</span>
          </div>
          
          <div v-if="transacao.lucro !== undefined" class="detalhe-item">
            <span class="label">Lucro:</span>
            <span class="valor lucro">R$ {{ transacao.lucro.toFixed(2) }}</span>
          </div>
        </div>
        
        <div class="itens-section">
          <h5>Itens:</h5>
          <div class="itens-lista">
            <div 
              v-for="item in transacao.itens" 
              :key="`${transacao.id}-${item.produtoId}`"
              class="item-historico"
            >
              <div class="item-info">
                <span class="produto-nome">{{ getNomeProduto(item.produtoId) }}</span>
                <span class="item-quantidade">{{ item.quantidade }}x</span>
              </div>
              <div class="item-valores">
                <span class="preco-unitario">R$ {{ item.precoUnitario.toFixed(2) }}</span>
                <span class="subtotal">R$ {{ item.subtotal.toFixed(2) }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <div class="resumo-total">
      <div class="resumo-card">
        <h4>Resumo Geral</h4>
        <div class="resumo-item">
          <span>Total de Compras:</span>
          <span>R$ {{ totalCompras.toFixed(2) }}</span>
        </div>
        <div class="resumo-item">
          <span>Total de Vendas:</span>
          <span>R$ {{ totalVendas.toFixed(2) }}</span>
        </div>
        <div class="resumo-item">
          <span>Lucro Total:</span>
          <span class="lucro">R$ {{ lucroTotal.toFixed(2) }}</span>
        </div>
        <div class="resumo-item">
          <span>Margem de Lucro:</span>
          <span class="lucro">{{ margemLucro.toFixed(1) }}%</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  compras: {
    type: Array,
    default: () => []
  },
  vendas: {
    type: Array,
    default: () => []
  },
  produtos: {
    type: Array,
    default: () => []
  }
})

const filtroTipo = ref('todos')
const filtroProduto = ref('')

const transacoesFiltradas = computed(() => {
  let transacoes = []
  
  // Adicionar compras
  if (filtroTipo.value === 'todos' || filtroTipo.value === 'compras') {
    transacoes.push(...props.compras.map(compra => ({
      ...compra,
      tipo: 'compra',
      clienteFornecedor: compra.fornecedor || 'N/A'
    })))
  }
  
  // Adicionar vendas
  if (filtroTipo.value === 'todos' || filtroTipo.value === 'vendas') {
    transacoes.push(...props.vendas.map(venda => ({
      ...venda,
      tipo: 'venda',
      clienteFornecedor: venda.cliente || 'N/A'
    })))
  }
  
  // Filtrar por produto se selecionado
  if (filtroProduto.value) {
    transacoes = transacoes.filter(transacao => 
      transacao.itens.some(item => item.produtoId === parseInt(filtroProduto.value))
    )
  }
  
  // Ordenar por data (mais recente primeiro)
  return transacoes.sort((a, b) => new Date(b.data) - new Date(a.data))
})

const totalCompras = computed(() => {
  return props.compras.reduce((sum, compra) => sum + compra.total, 0)
})

const totalVendas = computed(() => {
  return props.vendas.reduce((sum, venda) => sum + venda.total, 0)
})

const lucroTotal = computed(() => {
  return props.vendas.reduce((sum, venda) => sum + (venda.lucro || 0), 0)
})

const margemLucro = computed(() => {
  if (totalVendas.value === 0) return 0
  return (lucroTotal.value / totalVendas.value) * 100
})

const getNomeProduto = (produtoId) => {
  const produto = props.produtos.find(p => p.id === produtoId)
  return produto ? produto.nome : 'Produto não encontrado'
}
</script>

<style scoped>
.transacao-historico {
  background: #f8f9fa;
  padding: 2rem;
  border-radius: 12px;
  border: 2px solid #e9ecef;
}

.transacao-historico h3 {
  margin: 0 0 1.5rem 0;
  color: #495057;
  text-align: center;
}

.filtros {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
  margin-bottom: 2rem;
  padding: 1rem;
  background: white;
  border-radius: 8px;
  border: 1px solid #dee2e6;
}

.filtro-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.filtro-group label {
  font-weight: 600;
  color: #495057;
  font-size: 0.9rem;
}

.form-select {
  padding: 0.5rem;
  border: 2px solid #dee2e6;
  border-radius: 6px;
  font-size: 0.9rem;
  transition: border-color 0.3s ease;
}

.form-select:focus {
  outline: none;
  border-color: #007bff;
  box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
}

.sem-transacoes {
  text-align: center;
  padding: 3rem;
  color: #6c757d;
  background: white;
  border-radius: 8px;
  border: 1px solid #dee2e6;
}

.sem-transacoes p {
  margin: 0.5rem 0;
}

.transacoes-lista {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  margin-bottom: 2rem;
}

.transacao-card {
  background: white;
  border-radius: 8px;
  padding: 1.5rem;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.transacao-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.transacao-card.compra {
  border-left: 4px solid #007bff;
}

.transacao-card.venda {
  border-left: 4px solid #28a745;
}

.transacao-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
  padding-bottom: 0.5rem;
  border-bottom: 1px solid #e9ecef;
}

.transacao-info h4 {
  margin: 0;
  color: #333;
  font-size: 1.1rem;
}

.transacao-data {
  color: #6c757d;
  font-size: 0.9rem;
}

.transacao-total {
  text-align: right;
}

.total-label {
  color: #6c757d;
  font-size: 0.9rem;
  display: block;
}

.total-value {
  font-weight: bold;
  color: #333;
  font-size: 1.2rem;
}

.transacao-detalhes {
  margin-bottom: 1rem;
}

.detalhe-item {
  display: flex;
  justify-content: space-between;
  margin-bottom: 0.5rem;
}

.detalhe-item .label {
  color: #6c757d;
  font-size: 0.9rem;
}

.detalhe-item .valor {
  font-weight: 600;
  color: #333;
}

.detalhe-item .valor.lucro {
  color: #28a745;
}

.itens-section h5 {
  margin: 0 0 0.5rem 0;
  color: #495057;
  font-size: 1rem;
}

.itens-lista {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.item-historico {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.5rem;
  background: #f8f9fa;
  border-radius: 4px;
}

.item-info {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.produto-nome {
  font-weight: 500;
  color: #333;
}

.item-quantidade {
  background: #007bff;
  color: white;
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  font-size: 0.8rem;
  font-weight: 600;
}

.item-valores {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.preco-unitario {
  color: #6c757d;
  font-size: 0.9rem;
}

.subtotal {
  font-weight: 600;
  color: #333;
}

.resumo-total {
  margin-top: 2rem;
}

.resumo-card {
  background: white;
  padding: 1.5rem;
  border-radius: 8px;
  border: 2px solid #28a745;
}

.resumo-card h4 {
  margin: 0 0 1rem 0;
  color: #333;
  text-align: center;
}

.resumo-item {
  display: flex;
  justify-content: space-between;
  margin-bottom: 0.5rem;
  padding: 0.5rem 0;
}

.resumo-item:not(:last-child) {
  border-bottom: 1px solid #e9ecef;
}

.resumo-item .lucro {
  color: #28a745;
  font-weight: bold;
}

@media (max-width: 768px) {
  .transacao-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.5rem;
  }
  
  .transacao-total {
    text-align: left;
  }
  
  .item-historico {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.5rem;
  }
  
  .item-valores {
    align-self: flex-end;
  }
}
</style>
