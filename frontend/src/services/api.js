const API_BASE_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api'

class ApiService {
  constructor() {
    this.baseURL = API_BASE_URL
    this.token = localStorage.getItem('auth_token')
  }

  async request(endpoint, options = {}) {
    const url = `${this.baseURL}${endpoint}`
    
    const defaultOptions = {
      credentials: 'include',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        ...(this.token && { 'Authorization': `Bearer ${this.token}` }),
      },
    }

    const config = {
      ...defaultOptions,
      ...options,
      headers: {
        ...defaultOptions.headers,
        ...options.headers,
      },
    }

    try {
      console.log(`🌐 API Request: ${config.method || 'GET'} ${url}`)
      
      const response = await fetch(url, config)
      const data = await response.json()

      console.log(`📡 API Response:`, data)

      if (!response.ok) {
        throw new Error(data.message || `HTTP ${response.status}`)
      }

      return data
    } catch (error) {
      console.error(`❌ API Error:`, error)
      throw error
    }
  }

  async getProdutos() {
    return this.request('/produtos')
  }

  async createProduto(produto) {
    return this.request('/produtos', {
      method: 'POST',
      body: JSON.stringify({
        nome: produto.nome,
        preco_venda: produto.precoVenda
      })
    })
  }

  async updateProduto(id, produto) {
    return this.request(`/produtos/${id}`, {
      method: 'PUT',
      body: JSON.stringify({
        nome: produto.nome,
        preco_venda: produto.precoVenda
      })
    })
  }

  async deleteProduto(id) {
    return this.request(`/produtos/${id}`, {
      method: 'DELETE'
    })
  }

  async getCompras() {
    return this.request('/compras')
  }

  async createCompra(compra) {
    return this.request('/compras', {
      method: 'POST',
      body: JSON.stringify({
        fornecedor: compra.fornecedor,
        produtos: compra.itens.map(item => ({
          id: item.produtoId,
          quantidade: item.quantidade,
          preco_unitario: item.precoUnitario
        }))
      })
    })
  }

  async getVendas() {
    return this.request('/vendas')
  }

  async createVenda(venda) {
    return this.request('/vendas', {
      method: 'POST',
      body: JSON.stringify({
        cliente: venda.cliente,
        produtos: venda.itens.map(item => ({
          id: item.produtoId,
          quantidade: item.quantidade,
          preco_unitario: item.precoUnitario
        }))
      })
    })
  }

  async getEstatisticasCompras() {
    return this.request('/compras/estatisticas')
  }

  async getEstatisticasVendas() {
    return this.request('/vendas/estatisticas')
  }

  async testConnection() {
    return this.request('/')
  }

  async login(credentials) {
    try {
      await fetch(`${this.baseURL.replace('/api', '')}/sanctum/csrf-cookie`, {
        credentials: 'include',
        headers: {
          'Accept': 'application/json',
        }
      }).catch(() => {});
    } catch (error) {
      console.log('CSRF cookie request failed, continuing...', error);
    }

    const response = await this.request('/login', {
      method: 'POST',
      body: JSON.stringify(credentials)
    })
    
    if (response.success && response.data.token) {
      this.token = response.data.token
      localStorage.setItem('auth_token', this.token)
      localStorage.setItem('user', JSON.stringify(response.data.user))
    }
    
    return response
  }

  async register(userData) {
    const response = await this.request('/register', {
      method: 'POST',
      body: JSON.stringify(userData)
    })
    
    if (response.success && response.data.token) {
      this.token = response.data.token
      localStorage.setItem('auth_token', this.token)
      localStorage.setItem('user', JSON.stringify(response.data.user))
    }
    
    return response
  }

  async logout() {
    if (this.token) {
      await this.request('/logout', {
        method: 'POST'
      })
    }
    
    this.token = null
    localStorage.removeItem('auth_token')
    localStorage.removeItem('user')
  }

  async getMe() {
    return this.request('/me')
  }

  setToken(token) {
    this.token = token
    localStorage.setItem('auth_token', token)
  }

  getToken() {
    return this.token
  }

  isAuthenticated() {
    return !!this.token
  }

  getCurrentUser() {
    const user = localStorage.getItem('user')
    return user ? JSON.parse(user) : null
  }
}

const apiService = new ApiService()

export default apiService
