<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import ProdutoForm from './components/ProdutoForm.vue'
import ProdutoLista from './components/ProdutoLista.vue'
import CompraForm from './components/CompraForm.vue'
import VendaForm from './components/VendaForm.vue'
import TransacaoHistorico from './components/TransacaoHistorico.vue'
import apiService from './services/api.js'

// Estado global da aplicação
const telaAtiva = ref('produtos')
const produtos = reactive([])
const compras = reactive([])
const vendas = reactive([])
const mensagem = reactive({ texto: '', tipo: '' })
const produtoEditando = ref(null)
const carregando = ref(false)

// Funções para gerenciar produtos
const adicionarProduto = async (produto) => {
  try {
    carregando.value = true
    console.log('🎯 App.vue: Recebido produto para adicionar:', produto)
    
    const response = await apiService.createProduto(produto)
    
    // Adicionar produto à lista local
    const novoProduto = {
      id: response.id,
      nome: response.nome,
      precoVenda: parseFloat(response.preco_venda),
      custoMedio: parseFloat(response.custo_medio),
      estoque: response.estoque_atual
    }
    
    produtos.push(novoProduto)
    mostrarMensagem('Produto cadastrado com sucesso!', 'sucesso')
  } catch (error) {
    console.error('Erro ao cadastrar produto:', error)
    mostrarMensagem(`Erro ao cadastrar produto: ${error.message}`, 'erro')
  } finally {
    carregando.value = false
  }
}

const atualizarProduto = async (produtoId, novosDados) => {
  try {
    carregando.value = true
    console.log('🔄 Atualizando produto:', produtoId, novosDados)
    
    const response = await apiService.updateProduto(produtoId, novosDados)
    
    if (response.success) {
      const produto = produtos.find(p => p.id === produtoId)
      if (produto) {
        produto.nome = response.data.nome
        produto.precoVenda = parseFloat(response.data.preco_venda)
        produto.custoMedio = parseFloat(response.data.custo_medio)
        produto.estoque = response.data.estoque
      }
      mostrarMensagem('Produto atualizado com sucesso!', 'sucesso')
    }
  } catch (error) {
    console.error('Erro ao atualizar produto:', error)
    mostrarMensagem(`Erro ao atualizar produto: ${error.message}`, 'erro')
  } finally {
    carregando.value = false
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

const excluirProduto = async (produto) => {
  try {
    carregando.value = true
    console.log('🗑️ Excluindo produto:', produto)
    
    const response = await apiService.deleteProduto(produto.id)
    
    if (response.success) {
      // Remover produto da lista local
      const index = produtos.findIndex(p => p.id === produto.id)
      if (index !== -1) {
        produtos.splice(index, 1)
      }
      mostrarMensagem('Produto excluído com sucesso!', 'sucesso')
    }
  } catch (error) {
    console.error('Erro ao excluir produto:', error)
    mostrarMensagem(`Erro ao excluir produto: ${error.message}`, 'erro')
  } finally {
    carregando.value = false
  }
}

// Funções para carregar dados da API
const carregarProdutos = async () => {
  try {
    carregando.value = true
    const response = await apiService.getProdutos()
    
    produtos.splice(0, produtos.length) // Limpar array
    response.forEach(produto => {
      produtos.push({
        id: produto.id,
        nome: produto.nome,
        precoVenda: parseFloat(produto.preco_venda),
        custoMedio: parseFloat(produto.custo_medio),
        estoque: produto.estoque_atual
      })
    })
  } catch (error) {
    console.error('Erro ao carregar produtos:', error)
    mostrarMensagem(`Erro ao carregar produtos: ${error.message}`, 'erro')
  } finally {
    carregando.value = false
  }
}

const carregarCompras = async () => {
  try {
    const response = await apiService.getCompras()
    
    if (response.success) {
      compras.splice(0, compras.length) // Limpar array
      response.data.forEach(compra => {
        compras.push({
          id: compra.id,
          data: new Date(compra.data_compra).toLocaleDateString('pt-BR'),
          fornecedor: compra.fornecedor,
          total: parseFloat(compra.total),
          itens: compra.itens.map(item => ({
            produtoId: item.produto_id,
            quantidade: item.quantidade,
            precoUnitario: parseFloat(item.preco_unitario),
            subtotal: parseFloat(item.subtotal)
          }))
        })
      })
    }
  } catch (error) {
    console.error('Erro ao carregar compras:', error)
  }
}

const carregarVendas = async () => {
  try {
    const response = await apiService.getVendas()
    
    if (response.success) {
      vendas.splice(0, vendas.length) // Limpar array
      response.data.forEach(venda => {
        vendas.push({
          id: venda.id,
          data: new Date(venda.data_venda).toLocaleDateString('pt-BR'),
          cliente: venda.cliente,
          total: parseFloat(venda.total),
          lucro: parseFloat(venda.lucro),
          status: venda.status,
          itens: venda.itens.map(item => ({
            produtoId: item.produto_id,
            quantidade: item.quantidade,
            precoUnitario: parseFloat(item.preco_unitario),
            subtotal: parseFloat(item.subtotal)
          }))
        })
      })
    }
  } catch (error) {
    console.error('Erro ao carregar vendas:', error)
  }
}

// Carregar dados iniciais
onMounted(async () => {
  console.log('🚀 Aplicação iniciada, carregando dados...')
  
  // Carregar dados
  await carregarProdutos()
  await carregarCompras()
  await carregarVendas()
})

// Funções para gerenciar compras
const registrarCompra = async (compra) => {
  try {
    carregando.value = true
    console.log('🛒 Registrando compra:', compra)
    
    const response = await apiService.createCompra(compra)
    
    if (response.success) {
      // Recarregar produtos para atualizar estoque e custo médio
      await carregarProdutos()
      // Recarregar compras
      await carregarCompras()
      
      mostrarMensagem('Compra registrada com sucesso!', 'sucesso')
    }
  } catch (error) {
    console.error('Erro ao registrar compra:', error)
    mostrarMensagem(`Erro ao registrar compra: ${error.message}`, 'erro')
  } finally {
    carregando.value = false
  }
}

// Funções para gerenciar vendas
const registrarVenda = async (venda) => {
  try {
    carregando.value = true
    console.log('💰 Registrando venda:', venda)
    
    const response = await apiService.createVenda(venda)
    
    if (response.success) {
      // Recarregar produtos para atualizar estoque
      await carregarProdutos()
      // Recarregar vendas
      await carregarVendas()
      
      const resumo = response.resumo
      mostrarMensagem(
        `Venda realizada! Total: R$ ${resumo.total_venda.toFixed(2)}, Lucro: R$ ${resumo.lucro_calculado.toFixed(2)}`, 
        'sucesso'
      )
      return true
    }
  } catch (error) {
    console.error('Erro ao registrar venda:', error)
    mostrarMensagem(`Erro ao registrar venda: ${error.message}`, 'erro')
    return false
  } finally {
    carregando.value = false
  }
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

    <!-- Indicador de carregamento -->
    <div v-if="carregando" class="loading-overlay">
      <div class="loading-spinner">
        <div class="spinner"></div>
        <p>Carregando...</p>
      </div>
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
            @excluir-produto="excluirProduto"
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
  position: relative;
  z-index: 10;
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
  position: relative;
  z-index: 5;
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

/* Indicador de carregamento */
.loading-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 9999;
}

.loading-spinner {
  background: white;
  padding: 2rem;
  border-radius: 12px;
  text-align: center;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
}

.spinner {
  width: 40px;
  height: 40px;
  border: 4px solid #f3f3f3;
  border-top: 4px solid #007bff;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 1rem auto;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.loading-spinner p {
  margin: 0;
  color: #333;
  font-weight: 500;
}
</style>
