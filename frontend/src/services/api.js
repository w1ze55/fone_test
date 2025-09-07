// Serviço de API para comunicação com o backend Laravel
const API_BASE_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api'

class ApiService {
  constructor() {
    this.baseURL = API_BASE_URL
  }

  // Método genérico para fazer requisições
  async request(endpoint, options = {}) {
    const url = `${this.baseURL}${endpoint}`
    
    const defaultOptions = {
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
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

  // Métodos para Produtos
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

  // Métodos para Compras
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

  // Métodos para Vendas
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

  // Métodos para Estatísticas
  async getEstatisticasCompras() {
    return this.request('/compras/estatisticas')
  }

  async getEstatisticasVendas() {
    return this.request('/vendas/estatisticas')
  }

  // Teste de conexão
  async testConnection() {
    return this.request('/')
  }
}

// Instância única do serviço
const apiService = new ApiService()

export default apiService
