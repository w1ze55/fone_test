<template>
  <div class="produto-form">
    <h3>{{ isEditando ? '✏️ Editar Produto' : '📝 Cadastrar Produto' }}</h3>
    <form @submit.prevent="handleSubmit" class="form">
      <div class="form-group">
        <label for="nome">Nome do Produto *</label>
        <input 
          id="nome"
          v-model="form.nome" 
          type="text" 
          required 
          placeholder="Ex: Notebook Dell"
          class="form-input"
        />
      </div>
      
      <div class="form-group">
        <label for="precoVenda">Preço de Venda Sugerido *</label>
        <input 
          id="precoVenda"
          v-model="form.precoVenda" 
          type="number" 
          step="0.01" 
          min="0"
          required 
          placeholder="0.00"
          class="form-input"
          @input="form.precoVenda = parseFloat($event.target.value) || 0"
        />
      </div>
      
      <div class="form-actions">
        <button type="submit" class="btn btn-primary">
          {{ isEditando ? '💾 Salvar Alterações' : '✅ Cadastrar Produto' }}
        </button>
        <button 
          v-if="isEditando" 
          type="button" 
          @click="cancelarEdicao"
          class="btn btn-secondary"
        >
          ❌ Cancelar
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { reactive, computed, watch } from 'vue'

const props = defineProps({
  produtoEditando: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['produto-adicionado', 'produto-atualizado', 'cancelar-edicao'])

const form = reactive({
  nome: '',
  precoVenda: 0
})

const limparFormulario = () => {
  form.nome = ''
  form.precoVenda = 0
}

const isEditando = computed(() => props.produtoEditando !== null)

const handleSubmit = (event) => {
  if (isEditando.value) {
    salvarEdicao()
  } else {
    adicionarProduto()
  }
}

// Observar mudanças no produto sendo editado
watch(() => props.produtoEditando, (novoProduto) => {
  if (novoProduto) {
    form.nome = novoProduto.nome
    form.precoVenda = novoProduto.precoVenda
  } else {
    limparFormulario()
  }
}, { immediate: true })

const adicionarProduto = () => {
  if (!form.nome.trim()) {
    return
  }
  
  if (!form.precoVenda || form.precoVenda <= 0) {
    return
  }
  
  emit('produto-adicionado', {
    nome: form.nome.trim(),
    precoVenda: form.precoVenda
  })
  
  limparFormulario()
}

const salvarEdicao = () => {
  if (form.nome.trim() && form.precoVenda > 0) {
    emit('produto-atualizado', {
      nome: form.nome.trim(),
      precoVenda: form.precoVenda
    })
  }
}

const cancelarEdicao = () => {
  emit('cancelar-edicao')
}

</script>

<style scoped>
.produto-form {
  background: #f8f9fa;
  padding: 2rem;
  border-radius: 12px;
  border: 2px solid #e9ecef;
}

.produto-form h3 {
  margin: 0 0 1.5rem 0;
  color: #495057;
  text-align: center;
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

.form-input {
  padding: 0.75rem;
  border: 2px solid #dee2e6;
  border-radius: 8px;
  font-size: 1rem;
  transition: border-color 0.3s ease;
}

.form-input:focus {
  outline: none;
  border-color: rgb(51,154,79);
  box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
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
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(108, 117, 125, 0.3);
}

.form-actions {
  display: flex;
  gap: 1rem;
  justify-content: center;
}
</style>



