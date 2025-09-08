<script setup>

const props = defineProps({
  produtos: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['editar-produto', 'excluir-produto'])

const editarProduto = (produto) => {
  emit('editar-produto', produto)
}

const confirmarExclusao = (produto) => {
  const confirmacao = confirm(`Tem certeza que deseja excluir o produto "${produto.nome}"?\n\nEsta ação não pode ser desfeita.`)
  
  if (confirmacao) {
    emit('excluir-produto', produto)
  }
}
</script>

<template>
  <div class="produto-lista">
    <h3>📋 Lista de Produtos</h3>
    
    <div v-if="produtos.length === 0" class="lista-vazia">
      <p>Nenhum produto cadastrado ainda.</p>
      <p>Use o formulário ao lado para cadastrar o primeiro produto!</p>
    </div>
    
    <div v-else class="produtos-grid">
      <div 
        v-for="produto in produtos" 
        :key="produto.id" 
        class="produto-card"
      >
        <div class="produto-header">
          <h4>{{ produto.nome }}</h4>
          <span class="estoque-badge" :class="{ 'estoque-baixo': produto.estoque < 10 }">
            Estoque: {{ produto.estoque }}
          </span>
        </div>
        
        <div class="produto-info">
          <div class="info-item">
            <span class="label">Preço de Venda:</span>
            <span class="valor">R$ {{ produto.precoVenda.toFixed(2) }}</span>
          </div>
          
          <div class="info-item">
            <span class="label">Custo Médio:</span>
            <span class="valor">R$ {{ produto.custoMedio.toFixed(2) }}</span>
          </div>
          
          <div class="info-item">
            <span class="label">Margem:</span>
            <span class="valor" :class="{ 'lucro': produto.custoMedio > 0 }">
              {{ produto.custoMedio > 0 ? 
                ((produto.precoVenda - produto.custoMedio) / produto.custoMedio * 100).toFixed(1) + '%' : 
                'N/A' 
              }}
            </span>
          </div>
        </div>
        
        <div class="produto-actions">
          <button 
            @click="editarProduto(produto)" 
            class="btn btn-sm btn-secondary"
          >
            ✏️ Editar
          </button>
          <button 
            @click="confirmarExclusao(produto)" 
            class="btn btn-sm btn-danger"
          >
            🗑️ Excluir
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.produto-lista {
  background: #f8f9fa;
  padding: 2rem;
  border-radius: 12px;
  border: 2px solid #e9ecef;
}

.produto-lista h3 {
  margin: 0 0 1.5rem 0;
  color: #495057;
  text-align: center;
}

.lista-vazia {
  text-align: center;
  padding: 2rem;
  color: #6c757d;
}

.lista-vazia p {
  margin: 0.5rem 0;
}

.produtos-grid {
  display: grid;
  gap: 1rem;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
}

.produto-card {
  background: white;
  border-radius: 8px;
  padding: 1.5rem;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.produto-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.produto-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
  padding-bottom: 0.5rem;
  border-bottom: 1px solid #e9ecef;
}

.produto-header h4 {
  margin: 0;
  color: #333;
  font-size: 1.1rem;
}

.estoque-badge {
  background: #28a745;
  color: white;
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  font-size: 0.8rem;
  font-weight: 600;
}

.estoque-badge.estoque-baixo {
  background: #dc3545;
}

.produto-info {
  margin-bottom: 1rem;
}

.info-item {
  display: flex;
  justify-content: space-between;
  margin-bottom: 0.5rem;
}

.label {
  color: #6c757d;
  font-size: 0.9rem;
}

.valor {
  font-weight: 600;
  color: #333;
}

.valor.lucro {
  color: #28a745;
}

.produto-actions {
  display: flex;
  gap: 0.5rem;
}

.btn {
  padding: 0.5rem 1rem;
  border: none;
  border-radius: 4px;
  font-size: 0.9rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-sm {
  padding: 0.375rem 0.75rem;
  font-size: 0.8rem;
}

.btn-secondary {
  background: #6c757d;
  color: white;
}

.btn-secondary:hover {
  background: #545b62;
  transform: translateY(-1px);
}

.btn-danger {
  background: #dc3545;
  color: white;
}

.btn-danger:hover {
  background: #c82333;
  transform: translateY(-1px);
}
</style>
