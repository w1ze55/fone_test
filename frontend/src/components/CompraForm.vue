<script setup>
import { reactive, computed } from 'vue'

const props = defineProps({
  produtos: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['compra-registrada'])

const form = reactive({
  fornecedor: '',
  itens: [{
    produtoId: '',
    quantidade: 1,
    precoUnitario: 0,
    subtotal: 0
  }]
})

const totalCompra = computed(() => {
  return form.itens.reduce((sum, item) => sum + item.subtotal, 0)
})

const adicionarItem = () => {
  form.itens.push({
    produtoId: '',
    quantidade: 1,
    precoUnitario: 0,
    subtotal: 0
  })
}

const removerItem = (index) => {
  if (form.itens.length > 1) {
    form.itens.splice(index, 1)
  }
}

const atualizarItem = (index) => {
  const item = form.itens[index]
  item.subtotal = item.quantidade * item.precoUnitario
}

const registrarCompra = () => {
  if (form.fornecedor.trim() && form.itens.every(item => item.produtoId && item.quantidade > 0 && item.precoUnitario > 0)) {
    emit('compra-registrada', {
      fornecedor: form.fornecedor.trim(),
      itens: form.itens.map(item => ({
        produtoId: parseInt(item.produtoId),
        quantidade: item.quantidade,
        precoUnitario: item.precoUnitario
      }))
    })
    
    // limpar form
    form.fornecedor = ''
    form.itens = [{
      produtoId: '',
      quantidade: 1,
      precoUnitario: 0,
      subtotal: 0
    }]
  }
}
</script>

<template>
  <div class="compra-form">
    <h3>🛒 Registrar Compra</h3>
    
    <div v-if="produtos.length === 0" class="sem-produtos">
      <p>⚠️ Nenhum produto cadastrado!</p>
      <p>Cadastre produtos primeiro na aba "Produtos".</p>
    </div>
    
    <form v-else @submit.prevent="registrarCompra" class="form">
      <div class="form-group">
        <label for="fornecedor">Fornecedor *</label>
        <input 
          id="fornecedor"
          v-model="form.fornecedor" 
          type="text" 
          required 
          placeholder="Ex: Distribuidora ABC"
          class="form-input"
        />
      </div>
      
      <div class="itens-section">
        <h4>Itens da Compra</h4>
        
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
              required
              class="form-input"
            />
          </div>
          
          <div class="item-preco">
            <label>Preço Unitário</label>
            <input 
              v-model.number="item.precoUnitario" 
              @input="atualizarItem(index)"
              type="number" 
              step="0.01"
              min="0"
              required
              class="form-input"
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
      
      <div class="total-section">
        <div class="total-row">
          <span class="total-label">Total da Compra:</span>
          <span class="total-value">R$ {{ totalCompra.toFixed(2) }}</span>
        </div>
      </div>
      
      <button type="submit" class="btn btn-primary">
        ✅ Registrar Compra
      </button>
    </form>
  </div>
</template>

<style scoped>
.compra-form {
  background: #f8f9fa;
  padding: 2rem;
  border-radius: 12px;
  border: 2px solid #e9ecef;
}

.compra-form h3 {
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

.itens-section h4 {
  margin: 0 0 1rem 0;
  color: #495057;
  text-align: center;
}

.item-row {
  display: grid;
  grid-template-columns: 2fr 1fr 1fr 1fr auto;
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

.item-produto, .item-quantidade, .item-preco, .item-subtotal {
  display: flex;
  flex-direction: column;
}

.item-actions {
  display: flex;
  align-items: end;
}

.total-section {
  background: white;
  padding: 1rem;
  border-radius: 8px;
  border: 2px solid #28a745;
}

.total-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
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

@media (max-width: 768px) {
  .item-row {
    grid-template-columns: 1fr;
    gap: 0.5rem;
  }
}
</style>
