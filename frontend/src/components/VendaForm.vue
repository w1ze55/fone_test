<script setup>
import { reactive, computed } from 'vue'

const props = defineProps({
  produtos: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['venda-registrada'])

const form = reactive({
  cliente: '',
  itens: [{
    produtoId: '',
    quantidade: 1,
    subtotal: 0,
    lucro: 0
  }]
})

const totalVenda = computed(() => {
  return form.itens.reduce((sum, item) => sum + item.subtotal, 0)
})

const totalLucro = computed(() => {
  return form.itens.reduce((sum, item) => sum + item.lucro, 0)
})

const adicionarItem = () => {
  form.itens.push({
    produtoId: '',
    quantidade: 1,
    subtotal: 0,
    lucro: 0
  })
}

const removerItem = (index) => {
  if (form.itens.length > 1) {
    form.itens.splice(index, 1)
  }
}

const getPrecoProduto = (produtoId) => {
  const produto = props.produtos.find(p => p.id === produtoId)
  return produto ? produto.precoVenda : 0
}

const getCustoProduto = (produtoId) => {
  const produto = props.produtos.find(p => p.id === produtoId)
  return produto ? produto.custoMedio : 0
}

const getMaxQuantidade = (produtoId) => {
  const produto = props.produtos.find(p => p.id === produtoId)
  return produto ? produto.estoque : 0
}

const atualizarItem = (index) => {
  const item = form.itens[index]
  const produto = props.produtos.find(p => p.id === item.produtoId)
  
  if (produto) {
    item.subtotal = item.quantidade * produto.precoVenda
    item.lucro = item.quantidade * (produto.precoVenda - produto.custoMedio)
  } else {
    item.subtotal = 0
    item.lucro = 0
  }
}

const registrarVenda = () => {
  if (!form.cliente.trim()) {
    alert('Por favor, informe o nome do cliente.')
    return false
  }
  
  if (!form.itens.every(item => item.produtoId && item.quantidade > 0)) {
    alert('Por favor, preencha todos os itens da venda.')
    return false
  }
  
  for (const item of form.itens) {
    const produto = props.produtos.find(p => p.id === item.produtoId)
    if (!produto) {
      alert('Produto não encontrado.')
      return false
    }
    
    if (produto.estoque < item.quantidade) {
      alert(`Estoque insuficiente para ${produto.nome}!\n\nDisponível: ${produto.estoque}\nSolicitado: ${item.quantidade}`)
      return false
    }
  }
  
  const produtoQuantidades = {}
  for (const item of form.itens) {
    if (!produtoQuantidades[item.produtoId]) {
      produtoQuantidades[item.produtoId] = 0
    }
    produtoQuantidades[item.produtoId] += item.quantidade
  }
  
  for (const [produtoId, quantidadeTotal] of Object.entries(produtoQuantidades)) {
    const produto = props.produtos.find(p => p.id === parseInt(produtoId))
    if (produto && produto.estoque < quantidadeTotal) {
      alert(`Estoque insuficiente para ${produto.nome}!\n\nVocê está tentando vender ${quantidadeTotal} unidades, mas só há ${produto.estoque} disponíveis.`)
      return false
    }
  }
  
  emit('venda-registrada', {
    cliente: form.cliente.trim(),
    itens: form.itens.map(item => {
      const produto = props.produtos.find(p => p.id === item.produtoId)
      return {
        produtoId: parseInt(item.produtoId),
        quantidade: item.quantidade,
        precoUnitario: produto ? produto.precoVenda : 0
      }
    })
  })
  
  form.cliente = ''
  form.itens = [{
    produtoId: '',
    quantidade: 1,
    subtotal: 0,
    lucro: 0
  }]
  
  return true
}
</script>

<template>
  <div class="venda-form">
    <h3>💰 Registrar Venda</h3>
    
    <div v-if="produtos.length === 0" class="sem-produtos">
      <p>⚠️ Nenhum produto cadastrado!</p>
      <p>Cadastre produtos primeiro na aba "Produtos".</p>
    </div>
    
    <form v-else @submit.prevent="registrarVenda" class="form">
      <div class="form-group">
        <label for="cliente">Cliente *</label>
        <input 
          id="cliente"
          v-model="form.cliente" 
          type="text" 
          required 
          placeholder="Ex: João Silva"
          class="form-input"
        />
      </div>
      
      <div class="itens-section">
        <h4>Itens da Venda</h4>
        
        <div 
          v-for="(item, index) in form.itens" 
          :key="index" 
          class="item-row"
        >
          <div class="item-produto">
            <label>Produto</label>
            <select 
              v-model="item.produtoId" 
              @change="atualizarItem(index)"
              required
              class="form-select"
            >
              <option value="">Selecione um produto</option>
              <option 
                v-for="produto in produtos" 
                :key="produto.id" 
                :value="produto.id"
                :disabled="produto.estoque === 0"
              >
                {{ produto.nome }} (Estoque: {{ produto.estoque }})
              </option>
            </select>
          </div>
          
          <div class="item-quantidade">
            <label>Quantidade</label>
            <input 
              v-model.number="item.quantidade" 
              @input="atualizarItem(index)"
              type="number" 
              min="1"
              :max="getMaxQuantidade(item.produtoId)"
              required
              class="form-input"
              :class="{ 'estoque-insuficiente': item.produtoId && item.quantidade > getMaxQuantidade(item.produtoId) }"
            />
            <small v-if="item.produtoId && item.quantidade > getMaxQuantidade(item.produtoId)" class="erro-estoque">
              ⚠️ Estoque insuficiente! Máximo: {{ getMaxQuantidade(item.produtoId) }}
            </small>
          </div>
          
          <div class="item-preco">
            <label>Preço Unitário</label>
            <input 
              :value="getPrecoProduto(item.produtoId).toFixed(2)" 
              readonly
              class="form-input readonly"
            />
          </div>
          
          <div class="item-custo">
            <label>Custo Unitário</label>
            <input 
              :value="getCustoProduto(item.produtoId).toFixed(2)" 
              readonly
              class="form-input readonly"
            />
          </div>
          
          <div class="item-subtotal">
            <label>Subtotal</label>
            <input 
              :value="item.subtotal.toFixed(2)" 
              readonly
              class="form-input readonly"
            />
          </div>
          
          <div class="item-lucro">
            <label>Lucro</label>
            <input 
              :value="item.lucro.toFixed(2)" 
              readonly
              class="form-input readonly lucro"
            />
          </div>
          
          <div class="item-actions">
            <button 
              type="button" 
              @click="removerItem(index)"
              class="btn btn-danger btn-sm"
              :disabled="form.itens.length === 1"
            >
              ❌
            </button>
          </div>
        </div>
        
        <button 
          type="button" 
          @click="adicionarItem"
          class="btn btn-secondary"
        >
          ➕ Adicionar Item
        </button>
      </div>
      
      <div class="totais-section">
        <div class="total-row">
          <span class="total-label">Total da Venda:</span>
          <span class="total-value">R$ {{ totalVenda.toFixed(2) }}</span>
        </div>
        <div class="total-row">
          <span class="total-label">Lucro Total:</span>
          <span class="total-value lucro">R$ {{ totalLucro.toFixed(2) }}</span>
        </div>
      </div>
      
      <button type="submit" class="btn btn-primary">
        ✅ Registrar Venda
      </button>
    </form>
  </div>
</template>

<style scoped>
.venda-form {
  background: #f8f9fa;
  padding: 2rem;
  border-radius: 12px;
  border: 2px solid #e9ecef;
}

.venda-form h3 {
  margin: 0 0 1.5rem 0;
  color: #495057;
  text-align: center;
}

.sem-produtos {
  text-align: center;
  padding: 2rem;
  color: #dc3545;
  background: #f8d7da;
  border-radius: 8px;
  border: 1px solid #f5c6cb;
}

.form {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.form-group label {
  font-weight: 600;
  color: #495057;
  font-size: 0.9rem;
}

.form-input, .form-select {
  padding: 0.75rem;
  border: 2px solid #dee2e6;
  border-radius: 8px;
  font-size: 1rem;
  transition: border-color 0.3s ease;
}

.form-input:focus, .form-select:focus {
  outline: none;
  border-color: rgb(51,154,79);
  box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
}

.form-input.readonly {
  background: #f8f9fa;
  color: #6c757d;
}

.form-input.readonly.lucro {
  color: #28a745;
  font-weight: 600;
}

.itens-section h4 {
  margin: 0 0 1rem 0;
  color: #495057;
  text-align: center;
}

.item-row {
  display: grid;
  grid-template-columns: 2fr 1fr 1fr 1fr 1fr 1fr auto;
  gap: 1rem;
  align-items: end;
  padding: 1rem;
  background: white;
  border-radius: 8px;
  border: 1px solid #dee2e6;
  margin-bottom: 1rem;
}

.item-row label {
  font-weight: 600;
  color: #495057;
  font-size: 0.8rem;
  margin-bottom: 0.25rem;
  display: block;
}

.item-produto, .item-quantidade, .item-preco, .item-custo, .item-subtotal, .item-lucro {
  display: flex;
  flex-direction: column;
}

.item-actions {
  display: flex;
  align-items: end;
}

.totais-section {
  background: white;
  padding: 1rem;
  border-radius: 8px;
  border: 2px solid #28a745;
}

.total-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.5rem;
}

.total-row:last-child {
  margin-bottom: 0;
  padding-top: 0.5rem;
  border-top: 1px solid #dee2e6;
}

.total-label {
  font-weight: 600;
  color: #495057;
  font-size: 1.1rem;
}

.total-value {
  font-weight: bold;
  color: #28a745;
  font-size: 1.2rem;
}

.total-value.lucro {
  color: #28a745;
}

.btn {
  padding: 1rem;
  border: none;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.btn-sm {
  padding: 0.5rem;
  font-size: 0.8rem;
}

.btn-primary {
  background: rgb(51,154,79);
  color: white;
}

.btn-primary:hover {
  background: rgb(36, 110, 56);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
}

.btn-secondary {
  background: #6c757d;
  color: white;
}

.btn-secondary:hover {
  background: #545b62;
}

.btn-danger {
  background: #b90013;
  color: white;
}

.btn-danger:hover {
  background: #c82333;
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.form-input.estoque-insuficiente {
  border-color: #dc3545;
  background-color: #f8d7da;
}

.erro-estoque {
  color: #dc3545;
  font-size: 0.8rem;
  font-weight: 600;
  margin-top: 0.25rem;
  display: block;
}

@media (max-width: 1200px) {
  .item-row {
    grid-template-columns: 1fr;
    gap: 0.5rem;
  }
}
</style>
