<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import ProdutoForm from './components/ProdutoForm.vue'
import ProdutoLista from './components/ProdutoLista.vue'
import CompraForm from './components/CompraForm.vue'
import VendaForm from './components/VendaForm.vue'
import TransacaoHistorico from './components/TransacaoHistorico.vue'
import Login from './components/Login.vue'
import Register from './components/Register.vue'
import apiService from './services/api.js'

const isAuthenticated = ref(false)
const currentUser = ref(null)
const showRegister = ref(false)
const telaAtiva = ref('vendas')
const produtos = reactive([])
const compras = reactive([])
const vendas = reactive([])
const notificacoes = reactive([])
const produtoEditando = ref(null)
const carregando = ref(false)
let notificacaoId = 0

const checkAuthStatus = () => {
  const token = apiService.getToken()
  const user = apiService.getCurrentUser()
  
  if (token && user) {
    isAuthenticated.value = true
    currentUser.value = user
    
    if (user.role === 'admin') {
      telaAtiva.value = 'produtos'
    } else {
      telaAtiva.value = 'vendas'
    }
  } else {
    isAuthenticated.value = false
    currentUser.value = null
  }
}

const handleLogin = (user) => {
  currentUser.value = user
  isAuthenticated.value = true
  
  if (user.role === 'admin') {
    telaAtiva.value = 'produtos'
  } else {
    telaAtiva.value = 'vendas'
  }
  
  mostrarNotificacao(`Bem-vindo(a), ${user.name}!`, 'sucesso')
  
  loadAppData()
}

const handleRegister = (user) => {
  handleLogin(user)
  mostrarNotificacao('Conta criada com sucesso!', 'sucesso')
}

const handleLogout = async () => {
  try {
    await apiService.logout()
    isAuthenticated.value = false
    currentUser.value = null
    telaAtiva.value = 'vendas'
    
    produtos.splice(0, produtos.length)
    compras.splice(0, compras.length)
    vendas.splice(0, vendas.length)
    
    mostrarNotificacao('Logout realizado com sucesso!', 'sucesso')
  } catch (error) {
    console.error('Erro no logout:', error)
    mostrarNotificacao('Erro ao fazer logout', 'erro')
  }
}

const toggleAuthMode = () => {
  showRegister.value = !showRegister.value
}

const canAccessScreen = (screen) => {
  if (!currentUser.value) return false
  
  if (currentUser.value.role === 'admin') {
    return true
  }
  
  return screen === 'vendas'
}

const loadAppData = async () => {
  if (!isAuthenticated.value) return
  
  try {
    await carregarProdutos()
    
    if (canAccessScreen('compras')) {
      await carregarCompras()
    }
    
    if (canAccessScreen('vendas')) {
      await carregarVendas()
    }
  } catch (error) {
    console.error('Erro ao carregar dados:', error)
    mostrarNotificacao('Erro ao carregar dados da aplicação', 'erro')
  }
}

const adicionarProduto = async (produto) => {
  try {
    carregando.value = true
    console.log('App.vue: Recebido produto para adicionar:', produto)
    
    const response = await apiService.createProduto(produto)
    
    const novoProduto = {
      id: response.id,
      nome: response.nome,
      precoVenda: parseFloat(response.preco_venda),
      custoMedio: parseFloat(response.custo_medio),
      estoque: response.estoque_atual
    }
    
    produtos.push(novoProduto)
    mostrarNotificacao('Produto cadastrado com sucesso!', 'sucesso')
  } catch (error) {
    console.error('Erro ao cadastrar produto:', error)
    mostrarNotificacao(`Erro ao cadastrar produto: ${error.message}`, 'erro')
  } finally {
    carregando.value = false
  }
}

const atualizarProduto = async (produtoId, novosDados) => {
  try {
    carregando.value = true
    console.log('Atualizando produto:', produtoId, novosDados)
    
    const response = await apiService.updateProduto(produtoId, novosDados)
    
    if (response.id) {
      const produto = produtos.find(p => p.id === produtoId)
      if (produto) {
        produto.nome = response.nome
        produto.precoVenda = parseFloat(response.preco_venda)
        produto.custoMedio = parseFloat(response.custo_medio)
        produto.estoque = response.estoque_atual
      }
      
      await carregarProdutos(false)
      
      mostrarNotificacao('Produto atualizado com sucesso!', 'sucesso')
    }
  } catch (error) {
    console.error('Erro ao atualizar produto:', error)
    mostrarNotificacao(`Erro ao atualizar produto: ${error.message}`, 'erro')
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
    
    if (response.message) {
      const index = produtos.findIndex(p => p.id === produto.id)
      if (index !== -1) {
        produtos.splice(index, 1)
      }
      
      await carregarProdutos(false)
      
      mostrarNotificacao(response.message, 'sucesso')
    }
  } catch (error) {
    console.error('Erro ao excluir produto:', error)
    mostrarNotificacao(`Erro ao excluir produto: ${error.message}`, 'erro')
  } finally {
    carregando.value = false
  }
}

const carregarProdutos = async (mostrarCarregando = true) => {
  try {
    if (mostrarCarregando) {
      carregando.value = true
    }
    console.log('Carregando produtos...')
    const response = await apiService.getProdutos()
    console.log('Produtos recebidos:', response)
    
    produtos.splice(0, produtos.length)
    response.forEach(produto => {
      produtos.push({
        id: produto.id,
        nome: produto.nome,
        precoVenda: parseFloat(produto.preco_venda),
        custoMedio: parseFloat(produto.custo_medio),
        estoque: produto.estoque_atual
      })
    })
    console.log('Produtos carregados no estado:', produtos.length)
  } catch (error) {
    console.error('Erro ao carregar produtos:', error)
    mostrarNotificacao(`Erro ao carregar produtos: ${error.message}`, 'erro')
  } finally {
    if (mostrarCarregando) {
      carregando.value = false
    }
  }
}

const carregarCompras = async () => {
  try {
    const response = await apiService.getCompras()
    
    if (Array.isArray(response)) {
      compras.splice(0, compras.length)
      response.forEach(compra => {
        compras.push({
          id: compra.id,
          data: new Date(compra.created_at).toLocaleDateString('pt-BR'),
          fornecedor: compra.fornecedor,
          total: parseFloat(compra.total),
          itens: compra.produtos ? compra.produtos.map(produto => ({
            produtoId: produto.id,
            produtoNome: produto.nome,
            quantidade: produto.pivot.quantidade,
            precoUnitario: parseFloat(produto.pivot.preco_unitario),
            subtotal: parseFloat(produto.pivot.subtotal)
          })) : []
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
    
    if (Array.isArray(response)) {
      vendas.splice(0, vendas.length)
      response.forEach(venda => {
        vendas.push({
          id: venda.id,
          data: new Date(venda.created_at).toLocaleDateString('pt-BR'),
          cliente: venda.cliente,
          total: parseFloat(venda.total),
          lucro: parseFloat(venda.lucro),
          cancelada: venda.cancelada,
          itens: venda.produtos ? venda.produtos.map(produto => ({
            produtoId: produto.id,
            produtoNome: produto.nome,
            quantidade: produto.pivot.quantidade,
            precoUnitario: parseFloat(produto.pivot.preco_unitario),
            custoUnitario: parseFloat(produto.pivot.custo_unitario),
            subtotal: parseFloat(produto.pivot.subtotal),
            lucroUnitario: parseFloat(produto.pivot.lucro_unitario)
          })) : []
        })
      })
    }
  } catch (error) {
    console.error('Erro ao carregar vendas:', error)
  }
}

onMounted(async () => {
  console.log('🚀 Aplicação iniciada, verificando autenticação...')
  
  checkAuthStatus()
  
  if (isAuthenticated.value) {
    await loadAppData()
  }
})

const registrarCompra = async (compra) => {
  try {
    carregando.value = true
    console.log('🛒 Registrando compra:', compra)
    
    const response = await apiService.createCompra(compra)
    
  if (response.message && response.compra) {
      await carregarProdutos(false)
      await carregarCompras()
      
      mostrarNotificacao(response.message, 'sucesso')
    }
  } catch (error) {
    console.error('Erro ao registrar compra:', error)
    mostrarNotificacao(`Erro ao registrar compra: ${error.message}`, 'erro')
  } finally {
    carregando.value = false
  }
}

const registrarVenda = async (venda) => {
  try {
    carregando.value = true
    console.log('💰 Registrando venda:', venda)
    
    const response = await apiService.createVenda(venda)
    
    if (response.message && response.venda) {
      await carregarProdutos(false)
      await carregarVendas()
      
      mostrarNotificacao(
        `${response.message} - Total: R$ ${response.total.toFixed(2)}, Lucro: R$ ${response.lucro.toFixed(2)}`, 
        'sucesso'
      )
      return true
    }
  } catch (error) {
    console.error('Erro ao registrar venda:', error)
    
    if (error.message.includes('Estoque insuficiente')) {
      mostrarNotificacao(`❌ ${error.message}`, 'erro')
    } else {
      mostrarNotificacao(`Erro ao registrar venda: ${error.message}`, 'erro')
    }
    return false
  } finally {
    carregando.value = false
  }
}

const mostrarNotificacao = (texto, tipo) => {
  const id = ++notificacaoId
  const notificacao = {
    id,
    texto,
    tipo
  }
  
  notificacoes.push(notificacao)
  
  setTimeout(() => {
    removerNotificacao(id)
  }, 5000)
}

const removerNotificacao = (id) => {
  const index = notificacoes.findIndex(n => n.id === id)
  if (index !== -1) {
    notificacoes.splice(index, 1)
  }
}

const estatisticas = computed(() => {
  const totalVendas = vendas.filter(venda => !venda.cancelada).reduce((sum, venda) => sum + venda.total, 0)
  const totalCompras = compras.reduce((sum, compra) => sum + compra.total, 0)
  const totalLucro = totalVendas - totalCompras
  
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
    <div v-if="!isAuthenticated">
      <Login 
        v-if="!showRegister"
        @login-success="handleLogin"
        @show-register="toggleAuthMode"
      />
      <Register 
        v-else
        @register-success="handleRegister"
        @show-login="toggleAuthMode"
      />
    </div>

    <div v-else>
      <header class="header">
        <div class="header-content">
          <h1>ERP Ninja - Estoque</h1>
          <div class="user-info">
            <span class="user-welcome">
              Olá, {{ currentUser.name }} 
              <span class="user-role">({{ currentUser.role === 'admin' ? 'Admin' : 'Usuário' }})</span>
            </span>
            <button @click="handleLogout" class="logout-btn">
              🚪 Sair
            </button>
          </div>
        </div>
        <nav class="nav">
          <button 
            v-if="canAccessScreen('produtos')"
            @click="telaAtiva = 'produtos'" 
            :class="{ active: telaAtiva === 'produtos' }"
            class="nav-btn"
          >
            📦 Produtos
          </button>
          <button 
            v-if="canAccessScreen('compras')"
            @click="telaAtiva = 'compras'" 
            :class="{ active: telaAtiva === 'compras' }"
            class="nav-btn"
          >
            🛒 Compras
          </button>
          <button 
            v-if="canAccessScreen('vendas')"
            @click="telaAtiva = 'vendas'" 
            :class="{ active: telaAtiva === 'vendas' }"
            class="nav-btn"
          >
            💰 Pedido de Venda
          </button>
          <button 
            v-if="canAccessScreen('vendas')"
            @click="telaAtiva = 'historico'" 
            :class="{ active: telaAtiva === 'historico' }"
            class="nav-btn"
          >
            📈 Histórico
          </button>
        </nav>
      </header>

    <div class="notifications-container">
      <transition-group name="notification" tag="div">
        <div 
          v-for="notificacao in notificacoes" 
          :key="notificacao.id"
          :class="['notification-popup', notificacao.tipo]"
        >
          <div class="notification-content">
            <div class="notification-icon">
              {{ notificacao.tipo === 'sucesso' ? '✅' : notificacao.tipo === 'erro' ? '❌' : 'ℹ️' }}
            </div>
            <div class="notification-text">
              {{ notificacao.texto }}
            </div>
            <button 
              @click="removerNotificacao(notificacao.id)" 
              class="notification-close"
            >
              ×
            </button>
          </div>
          <div class="notification-progress">
            <div 
              class="progress-bar" 
              :style="{ animationDuration: '5s' }"
            ></div>
          </div>
        </div>
      </transition-group>
    </div>

    <div v-if="carregando" class="loading-overlay">
      <div class="loading-spinner">
        <div class="spinner"></div>
        <p>Carregando...</p>
      </div>
    </div>

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
        <h3>{{ estatisticas.totalLucro >= 0 ? 'Lucro Total' : 'Prejuízo Total' }}</h3>
        <p :style="{ color: estatisticas.totalLucro >= 0 ? '#28a745' : '#dc3545' }">
          R$ {{ Math.abs(estatisticas.totalLucro).toFixed(2) }}
        </p>
      </div>
      <div class="stat-card">
        <h3>Total Compras</h3>
        <p>R$ {{ estatisticas.totalCompras.toFixed(2) }}</p>
      </div>
    </div>

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
  </div>
</template>

<style scoped>
#app {
  min-height: 100vh;
  background: rgb(233,253,238);
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

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.header h1 {
  margin: 0;
  color: #333;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.user-welcome {
  color: #666;
  font-size: 0.9rem;
}

.user-role {
  color: #28a745;
  font-weight: 600;
}

.logout-btn {
  padding: 0.5rem 1rem;
  background: #dc3545;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 0.85rem;
  transition: background-color 0.3s ease;
}

.logout-btn:hover {
  background: #c82333;
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
  background: rgb(51,154,79);
  color: white;
}

/* Notificações Popup */
.notifications-container {
  position: fixed;
  top: 2rem;
  right: 2rem;
  z-index: 10000;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  max-width: 400px;
}

.notification-popup {
  background: white;
  border-radius: 12px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
  border: 1px solid #e0e0e0;
  overflow: hidden;
  min-width: 320px;
  position: relative;
}

.notification-popup.sucesso {
  border-left: 4px solid rgb(51,154,79);
}

.notification-popup.erro {
  border-left: 4px solid #dc3545;
}

.notification-popup.info {
  border-left: 4px solid #17a2b8;
}

.notification-content {
  display: flex;
  align-items: flex-start;
  padding: 1rem;
  gap: 0.75rem;
}

.notification-icon {
  font-size: 1.25rem;
  flex-shrink: 0;
  margin-top: 0.125rem;
}

.notification-text {
  flex: 1;
  font-size: 0.9rem;
  line-height: 1.4;
  color: #333;
  word-wrap: break-word;
}

.notification-close {
  background: none;
  border: none;
  font-size: 1.5rem;
  color: #999;
  cursor: pointer;
  padding: 0;
  width: 24px;
  height: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  transition: all 0.2s ease;
  flex-shrink: 0;
}

.notification-close:hover {
  background: #f5f5f5;
  color: #666;
}

.notification-progress {
  height: 3px;
  background: #f0f0f0;
  position: relative;
  overflow: hidden;
}

.progress-bar {
  height: 100%;
  background: linear-gradient(90deg, #28a745, #20c997);
  width: 100%;
  animation: progress-countdown linear forwards;
  transform-origin: left;
}

.notification-popup.erro .progress-bar {
  background: linear-gradient(90deg, #dc3545, #e74c3c);
}

.notification-popup.info .progress-bar {
  background: linear-gradient(90deg, #17a2b8, #20c997);
}

@keyframes progress-countdown {
  from {
    transform: scaleX(1);
  }
  to {
    transform: scaleX(0);
  }
}

/* Animações de transição */
.notification-enter-active {
  transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.notification-leave-active {
  transition: all 0.3s cubic-bezier(0.55, 0, 0.1, 1);
}

.notification-enter-from {
  opacity: 0;
  transform: translateX(100%) scale(0.8);
}

.notification-leave-to {
  opacity: 0;
  transform: translateX(100%) scale(0.8);
}

.notification-move {
  transition: transform 0.3s ease;
}

/* Responsividade */
@media (max-width: 768px) {
  .header-content {
    flex-direction: column;
    gap: 1rem;
    text-align: center;
  }
  
  .user-info {
    flex-direction: column;
    gap: 0.5rem;
  }
  
  .notifications-container {
    top: 1rem;
    right: 1rem;
    left: 1rem;
    max-width: none;
  }
  
  .notification-popup {
    min-width: auto;
  }
  
  .notification-text {
    font-size: 0.85rem;
  }
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
  border-top: 4px solid rgb(51,154,79);
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
