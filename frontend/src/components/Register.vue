<script setup>
import { ref, reactive, computed } from 'vue'
import apiService from '../services/api.js'

const emit = defineEmits(['register-success', 'show-login'])

const loading = ref(false)
const error = ref('')
const success = ref('')

const form = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  role: 'user'
})

const isFormValid = computed(() => {
  return form.name && 
         form.email && 
         form.password && 
         form.password_confirmation && 
         form.password === form.password_confirmation &&
         form.password.length >= 6
})

const handleRegister = async () => {
  try {
    loading.value = true
    error.value = ''
    success.value = ''

    if (form.password !== form.password_confirmation) {
      error.value = 'As senhas não coincidem'
      return
    }

    if (form.password.length < 6) {
      error.value = 'A senha deve ter pelo menos 6 caracteres'
      return
    }

    const response = await apiService.register({
      name: form.name,
      email: form.email,
      password: form.password,
      password_confirmation: form.password_confirmation,
      role: form.role
    })

    if (response.success) {
      success.value = 'Conta criada com sucesso! Redirecionando...'
      
      setTimeout(() => {
        emit('register-success', response.data.user)
      }, 1500)
    } else {
      error.value = response.message || 'Erro ao criar conta'
    }
  } catch (err) {
    console.error('Erro no registro:', err)
    
    if (err.message && err.message.includes('errors')) {
      error.value = 'Dados inválidos. Verifique os campos e tente novamente.'
    } else {
      error.value = err.message || 'Erro ao criar conta. Tente novamente.'
    }
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="register-container">
    <div class="register-card">
      <div class="register-header">
        <h1>📝 Criar Conta</h1>
        <p>Preencha os dados para criar sua conta</p>
      </div>

      <form @submit.prevent="handleRegister" class="register-form">
        <div class="form-group">
          <label for="name">Nome Completo:</label>
          <input 
            id="name"
            v-model="form.name" 
            type="text" 
            required 
            placeholder="Digite seu nome completo"
            :disabled="loading"
          />
        </div>

        <div class="form-group">
          <label for="email">Email:</label>
          <input 
            id="email"
            v-model="form.email" 
            type="email" 
            required 
            placeholder="Digite seu email"
            :disabled="loading"
          />
        </div>

        <div class="form-group">
          <label for="password">Senha:</label>
          <input 
            id="password"
            v-model="form.password" 
            type="password" 
            required 
            placeholder="Digite sua senha (mín. 6 caracteres)"
            :disabled="loading"
            minlength="6"
          />
        </div>

        <div class="form-group">
          <label for="password_confirmation">Confirmar Senha:</label>
          <input 
            id="password_confirmation"
            v-model="form.password_confirmation" 
            type="password" 
            required 
            placeholder="Confirme sua senha"
            :disabled="loading"
          />
        </div>

        <div class="form-group">
          <label for="role">Tipo de Usuário:</label>
          <select 
            id="role"
            v-model="form.role" 
            :disabled="loading"
          >
            <option value="user">Usuário</option>
            <option value="admin">Administrador</option>
          </select>
        </div>

        <button 
          type="submit" 
          class="register-btn"
          :disabled="loading || !isFormValid"
        >
          <span v-if="loading" class="loading-spinner"></span>
          {{ loading ? 'Criando conta...' : 'Criar Conta' }}
        </button>

        <div v-if="error" class="error-message">
          {{ error }}
        </div>

        <div v-if="success" class="success-message">
          {{ success }}
        </div>
      </form>

      <div class="login-link">
        <p>Já tem conta? 
          <button @click="$emit('show-login')" class="link-btn">
            Fazer login
          </button>
        </p>
      </div>
    </div>
  </div>
</template>

<style scoped>
.register-container {
  min-height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
  background: rgb(217, 255, 228);
  padding: 1rem;
}

.register-card {
  background: white;
  padding: 2rem;
  border-radius: 16px;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
  width: 100%;
  max-width: 450px;
  animation: slideUp 0.5s ease-out;
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.register-header {
  text-align: center;
  margin-bottom: 2rem;
}

.register-header h1 {
  margin: 0 0 0.5rem 0;
  color: #333;
  font-size: 2rem;
}

.register-header p {
  margin: 0;
  color: #666;
  font-size: 0.95rem;
}

.register-form {
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
  color: #333;
  font-size: 0.9rem;
}

.form-group input,
.form-group select {
  padding: 0.75rem;
  border: 2px solid #e1e5e9;
  border-radius: 8px;
  font-size: 1rem;
  transition: border-color 0.3s ease, box-shadow 0.3s ease;
}

.form-group input:focus,
.form-group select:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.form-group input:disabled,
.form-group select:disabled {
  background-color: #f8f9fa;
  cursor: not-allowed;
}

.register-btn {
  padding: 0.875rem;
  background: rgb(36, 110, 56);
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}

.register-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
}

.register-btn:disabled {
  opacity: 0.7;
  cursor: not-allowed;
  transform: none;
}

.loading-spinner {
  width: 16px;
  height: 16px;
  border: 2px solid transparent;
  border-top: 2px solid white;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.error-message {
  background: #fee;
  color: #c33;
  padding: 0.75rem;
  border-radius: 8px;
  border: 1px solid #fcc;
  font-size: 0.9rem;
  text-align: center;
}

.success-message {
  background: #efe;
  color: #363;
  padding: 0.75rem;
  border-radius: 8px;
  border: 1px solid #cfc;
  font-size: 0.9rem;
  text-align: center;
}

.login-link {
  text-align: center;
  margin-top: 1.5rem;
  padding-top: 1.5rem;
  border-top: 1px solid #e9ecef;
}

.login-link p {
  margin: 0;
  color: #666;
  font-size: 0.9rem;
}

.link-btn {
  background: none;
  border: none;
  color: #667eea;
  cursor: pointer;
  text-decoration: underline;
  font-size: inherit;
}

.link-btn:hover {
  color: #5a6fd8;
}

/* Responsividade */
@media (max-width: 480px) {
  .register-card {
    padding: 1.5rem;
    margin: 0.5rem;
  }
}
</style>
