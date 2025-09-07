<script setup>
import { ref, reactive, computed } from 'vue'
import ProdutoForm from './components/ProdutoForm.vue'
import ProdutoLista from './components/ProdutoLista.vue'
import CompraForm from './components/CompraForm.vue'
import VendaForm from './components/VendaForm.vue'
import TransacaoHistorico from './components/TransacaoHistorico.vue'

// Estado global da aplicação
const telaAtiva = ref('produtos')
const produtos = reactive([])
const compras = reactive([])
const vendas = reactive([])
const mensagem = reactive({ texto: '', tipo: '' })
const produtoEditando = ref(null)

// Funções para gerenciar produtos
const adicionarProduto = (produto) => {
  const novoProduto = {
    id: Date.now(),
    nome: produto.nome,
    precoVenda: produto.precoVenda,
    custoMedio: 0,
    estoque: 0
  }
  produtos.push(novoProduto)
  mostrarMensagem('Produto cadastrado com sucesso!', 'sucesso')
}

const atualizarProduto = (produtoId, novosDados) => {
  const produto = produtos.find(p => p.id === produtoId)
  if (produto) {
    Object.assign(produto, novosDados)
    mostrarMensagem('Produto atualizado com sucesso!', 'sucesso')
  }
}

const iniciarEdicaoProduto = (produto) => {
  produtoEditando.value = { ...produto }
}

const salvarEdicaoProduto = (produtoAtualizado) => {
  atualizarProduto(produtoEditando.value.id, produtoAtualizado)
  produtoEditando.value = null
}

const cancelarEdicaoProduto = () => {
  produtoEditando.value = null
}

// Funções para gerenciar compras
const registrarCompra = (compra) => {
  const novaCompra = {
    id: Date.now(),
    data: new Date().toLocaleDateString('pt-BR'),
    itens: compra.itens,
    total: compra.itens.reduce((sum, item) => sum + (item.quantidade * item.precoUnitario), 0)
  }
  
  // Atualizar estoque e custo médio para cada item
  compra.itens.forEach(item => {
    const produto = produtos.find(p => p.id === item.produtoId)
    if (produto) {
      // Calcular novo custo médio ponderado
      const estoqueAnterior = produto.estoque
      const custoAnterior = produto.custoMedio
      const novaQuantidade = item.quantidade
      const novoCusto = item.precoUnitario
      
      const estoqueTotal = estoqueAnterior + novaQuantidade
      const custoMedio = estoqueAnterior === 0 
        ? novoCusto 
        : ((estoqueAnterior * custoAnterior) + (novaQuantidade * novoCusto)) / estoqueTotal
      
      produto.estoque = estoqueTotal
      produto.custoMedio = custoMedio
    }
  })
  
  compras.push(novaCompra)
  mostrarMensagem('Compra registrada com sucesso!', 'sucesso')
}

// Funções para gerenciar vendas
const registrarVenda = (venda) => {
  // Verificar estoque disponível
  for (const item of venda.itens) {
    const produto = produtos.find(p => p.id === item.produtoId)
    if (!produto || produto.estoque < item.quantidade) {
      mostrarMensagem(`Estoque insuficiente para ${produto?.nome || 'produto'}`, 'erro')
      return false
    }
  }
  
  const novaVenda = {
    id: Date.now(),
    data: new Date().toLocaleDateString('pt-BR'),
    itens: venda.itens,
    total: venda.itens.reduce((sum, item) => {
      const produto = produtos.find(p => p.id === item.produtoId)
      return sum + (item.quantidade * produto.precoVenda)
    }, 0),
    lucro: venda.itens.reduce((sum, item) => {
      const produto = produtos.find(p => p.id === item.produtoId)
      const receita = item.quantidade * produto.precoVenda
      const custo = item.quantidade * produto.custoMedio
      return sum + (receita - custo)
    }, 0)
  }
  
  // Atualizar estoque
  venda.itens.forEach(item => {
    const produto = produtos.find(p => p.id === item.produtoId)
    if (produto) {
      produto.estoque -= item.quantidade
    }
  })
  
  vendas.push(novaVenda)
  mostrarMensagem(`Venda realizada! Total: R$ ${novaVenda.total.toFixed(2)}, Lucro: R$ ${novaVenda.lucro.toFixed(2)}`, 'sucesso')
  return true
}

// Função para mostrar mensagens
const mostrarMensagem = (texto, tipo) => {
  mensagem.texto = texto
  mensagem.tipo = tipo
  setTimeout(() => {
    mensagem.texto = ''
    mensagem.tipo = ''
  }, 3000)
}

// Computed para estatísticas
const estatisticas = computed(() => {
  const totalVendas = vendas.reduce((sum, venda) => sum + venda.total, 0)
  const totalLucro = vendas.reduce((sum, venda) => sum + venda.lucro, 0)
  const totalCompras = compras.reduce((sum, compra) => sum + compra.total, 0)
  
  return {
    totalVendas,
    totalLucro,
    totalCompras,
    produtosCadastrados: produtos.length
  }
})
</script>

<template>
  <div id="app">
    <header class="header">
      <h1>ERP Ninja - Estoque</h1>
      <nav class="nav">
        <button 
          @click="telaAtiva = 'produtos'" 
          :class="{ active: telaAtiva === 'produtos' }"
          class="nav-btn"
        >
          📦 Produtos
        </button>
        <button 
          @click="telaAtiva = 'compras'" 
          :class="{ active: telaAtiva === 'compras' }"
          class="nav-btn"
        >
          🛒 Compras
        </button>
        <button 
          @click="telaAtiva = 'vendas'" 
          :class="{ active: telaAtiva === 'vendas' }"
          class="nav-btn"
        >
          💰 Vendas
        </button>
        <button 
          @click="telaAtiva = 'historico'" 
          :class="{ active: telaAtiva === 'historico' }"
          class="nav-btn"
        >
          📈 Histórico
        </button>
      </nav>
    </header>

    <!-- Mensagens de feedback -->
    <div v-if="mensagem.texto" :class="['mensagem', mensagem.tipo]">
      {{ mensagem.texto }}
    </div>

    <!-- Estatísticas resumidas -->
    <div class="estatisticas">
      <div class="stat-card">
        <h3>Produtos</h3>
        <p>{{ estatisticas.produtosCadastrados }}</p>
      </div>
      <div class="stat-card">
        <h3>Total Vendas</h3>
        <p>R$ {{ estatisticas.totalVendas.toFixed(2) }}</p>
      </div>
      <div class="stat-card">
        <h3>Lucro Total</h3>
        <p>R$ {{ estatisticas.totalLucro.toFixed(2) }}</p>
      </div>
      <div class="stat-card">
        <h3>Total Compras</h3>
        <p>R$ {{ estatisticas.totalCompras.toFixed(2) }}</p>
      </div>
    </div>

    <!-- Conteúdo das telas -->
    <main class="main-content">
      <div v-if="telaAtiva === 'produtos'" class="tela">
        <h2>Gerenciar Produtos</h2>
        <div class="tela-content">
          <ProdutoForm 
            @produto-adicionado="adicionarProduto"
            :produto-editando="produtoEditando"
            @produto-atualizado="salvarEdicaoProduto"
            @cancelar-edicao="cancelarEdicaoProduto"
          />
          <ProdutoLista 
            :produtos="produtos" 
            @editar-produto="iniciarEdicaoProduto"
          />
        </div>
      </div>

      <div v-if="telaAtiva === 'compras'" class="tela">
        <h2>Registrar Compras</h2>
        <CompraForm 
          :produtos="produtos" 
          @compra-registrada="registrarCompra"
        />
      </div>

      <div v-if="telaAtiva === 'vendas'" class="tela">
        <h2>Registrar Vendas</h2>
        <VendaForm 
          :produtos="produtos" 
          @venda-registrada="registrarVenda"
        />
      </div>

      <div v-if="telaAtiva === 'historico'" class="tela">
        <h2>Histórico de Transações</h2>
        <TransacaoHistorico 
          :compras="compras" 
          :vendas="vendas" 
          :produtos="produtos"
        />
      </div>
    </main>
  </div>
</template>

<style scoped>
#app {
  min-height: 100vh;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  position: relative;
  z-index: 1;
}

.header {
  background: white;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
  padding: 1rem 2rem;
  margin-bottom: 2rem;
}

.header h1 {
  margin: 0 0 1rem 0;
  color: #333;
  text-align: center;
}

.nav {
  display: flex;
  gap: 1rem;
  justify-content: center;
  flex-wrap: wrap;
}

.nav-btn {
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 8px;
  background: #f8f9fa;
  color: #495057;
  cursor: pointer;
  transition: all 0.3s ease;
  font-weight: 500;
}

.nav-btn:hover {
  background: #e9ecef;
  transform: translateY(-2px);
}

.nav-btn.active {
  background: #007bff;
  color: white;
}

.mensagem {
  margin: 1rem 2rem;
  padding: 1rem;
  border-radius: 8px;
  text-align: center;
  font-weight: 500;
}

.mensagem.sucesso {
  background: #d4edda;
  color: #155724;
  border: 1px solid #c3e6cb;
}

.mensagem.erro {
  background: #f8d7da;
  color: #721c24;
  border: 1px solid #f5c6cb;
}

.estatisticas {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
  margin: 0 2rem 2rem 2rem;
}

.stat-card {
  background: white;
  padding: 1.5rem;
  border-radius: 12px;
  text-align: center;
  box-shadow: 0 4px 6px rgba(0,0,0,0.1);
  transition: transform 0.3s ease;
}

.stat-card:hover {
  transform: translateY(-4px);
}

.stat-card h3 {
  margin: 0 0 0.5rem 0;
  color: #666;
  font-size: 0.9rem;
  text-transform: uppercase;
  letter-spacing: 1px;
}

.stat-card p {
  margin: 0;
  font-size: 1.5rem;
  font-weight: bold;
  color: #333;
}

.main-content {
  padding: 0 2rem 2rem 2rem;
}

.tela {
  background: white;
  border-radius: 12px;
  padding: 2rem;
  box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.tela h2 {
  margin: 0 0 2rem 0;
  color: #333;
  text-align: center;
}

.tela-content {
  display: grid;
  gap: 2rem;
}

@media (min-width: 768px) {
  .tela-content {
    grid-template-columns: 1fr 1fr;
  }
}
</style>
