<template>
  <div class="login-container">
    <div class="login-card">
      <div class="login-header">
        <h1>🔐 ERP Ninja</h1>
        <p>Faça login para continuar</p>
      </div>

      <form @submit.prevent="handleLogin" class="login-form">
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
            placeholder="Digite sua senha"
            :disabled="loading"
          />
        </div>

        <button 
          type="submit" 
          class="login-btn"
          :disabled="loading"
        >
          <span v-if="loading" class="loading-spinner"></span>
          {{ loading ? 'Entrando...' : 'Entrar' }}
        </button>

        <div v-if="error" class="error-message">
          {{ error }}
        </div>
      </form>

      <div class="demo-credentials">
        <h3>🎯 Credenciais Demo:</h3>
        <div class="demo-users">
          <div class="demo-user">
            <strong>Admin:</strong>
            <button @click="fillCredentials('admin@test.com', 'admin123')" class="demo-btn">
              admin@test.com / admin123
            </button>
          </div>
          <div class="demo-user">
            <strong>Usuário:</strong>
            <button @click="fillCredentials('user@test.com', 'user123')" class="demo-btn">
              user@test.com / user123
            </button>
          </div>
        </div>
      </div>

      <div class="register-link">
        <p>Não tem conta? 
          <button @click="$emit('show-register')" class="link-btn">
            Criar conta
          </button>
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import apiService from '../services/api.js'

// Emits
const emit = defineEmits(['login-success', 'show-register'])

// Estado reativo
const loading = ref(false)
const error = ref('')

const form = reactive({
  email: '',
  password: ''
})

// Função para preencher credenciais demo
const fillCredentials = (email, password) => {
  form.email = email
  form.password = password
}

// Função de login
const handleLogin = async () => {
  try {
    loading.value = true
    error.value = ''

    const response = await apiService.login({
      email: form.email,
      password: form.password
    })

    if (response.success) {
      emit('login-success', response.data.user)
    } else {
      error.value = response.message || 'Erro ao fazer login'
    }
  } catch (err) {
    console.error('Erro no login:', err)
    error.value = err.message || 'Erro ao fazer login. Tente novamente.'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.login-container {
  min-height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
  background: rgb(233,253,238);
  padding: 1rem;
}

.login-card {
  background: white;
  padding: 2rem;
  border-radius: 16px;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
  width: 100%;
  max-width: 400px;
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

.login-header {
  text-align: center;
  margin-bottom: 2rem;
}

.login-header h1 {
  margin: 0 0 0.5rem 0;
  color: #333;
  font-size: 2rem;
}

.login-header p {
  margin: 0;
  color: #666;
  font-size: 0.95rem;
}

.login-form {
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

.form-group input {
  padding: 0.75rem;
  border: 2px solid #e1e5e9;
  border-radius: 8px;
  font-size: 1rem;
  transition: border-color 0.3s ease, box-shadow 0.3s ease;
}

.form-group input:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.form-group input:disabled {
  background-color: #f8f9fa;
  cursor: not-allowed;
}

.login-btn {
  padding: 0.875rem;
  background: rgb(51,154,79);
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

.login-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
}

.login-btn:disabled {
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

.demo-credentials {
  margin-top: 2rem;
  padding: 1rem;
  background: #f8f9fa;
  border-radius: 8px;
  border: 1px solid #e9ecef;
}

.demo-credentials h3 {
  margin: 0 0 1rem 0;
  color: #333;
  font-size: 0.9rem;
  text-align: center;
}

.demo-users {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.demo-user {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
  font-size: 0.85rem;
}

.demo-user strong {
  color: #495057;
}

.demo-btn {
  background: #e9ecef;
  border: 1px solid #dee2e6;
  padding: 0.5rem;
  border-radius: 6px;
  cursor: pointer;
  font-size: 0.8rem;
  transition: background-color 0.2s ease;
  font-family: monospace;
}

.demo-btn:hover {
  background: #dee2e6;
}

.register-link {
  text-align: center;
  margin-top: 1.5rem;
  padding-top: 1.5rem;
  border-top: 1px solid #e9ecef;
}

.register-link p {
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
  .login-card {
    padding: 1.5rem;
    margin: 0.5rem;
  }
  
  .demo-users {
    gap: 0.5rem;
  }
  
  .demo-user {
    font-size: 0.8rem;
  }
}
</style>
