# 🐳 ERP Estoque - Configuração Docker

Este projeto inclui uma configuração completa do Docker para executar o sistema ERP de estoque com Laravel (backend) e Vue.js (frontend).

## 📋 Pré-requisitos

- Docker Desktop instalado
- Docker Compose v2.0+
- Pelo menos 4GB de RAM disponível

## 🚀 Início Rápido

### Opção 1: Script Automático (Recomendado)

**Windows:**
```bash
docker-start.bat
```

**Linux/Mac:**
```bash
chmod +x docker-start.sh
./docker-start.sh
```

### Opção 2: Manual

```bash
# 1. Construir e iniciar todos os serviços
docker-compose up -d --build

# 2. Executar migrations (aguarde ~30s após iniciar)
docker-compose exec backend php artisan migrate --force

# 3. Verificar status
docker-compose ps
```

## 🌐 Acessos

Após a inicialização completa:

- **Frontend (Vue.js):** http://localhost:3000
- **Backend API:** http://localhost:8000/api
- **MySQL:** localhost:3307
  - Database: `fone_test`
  - User: `root`
  - Password: `root123`

## 📦 Serviços Incluídos

### Principais
- **backend**: Laravel API (porta 8000)
- **frontend**: Vue.js SPA (porta 3000)
- **mysql**: MySQL 8.0 (porta 3306)

### Opcionais
- **redis**: Cache Redis (porta 6379)
- **nginx-proxy**: Proxy reverso Nginx (porta 80)

## 🔧 Comandos Úteis

### Gerenciamento de Containers
```bash
# Ver logs em tempo real
docker-compose logs -f

# Ver logs de um serviço específico
docker-compose logs -f backend

# Parar todos os serviços
docker-compose down

# Parar e remover volumes
docker-compose down -v

# Reconstruir imagens
docker-compose build --no-cache
```

### Laravel (Backend)
```bash
# Executar comandos Artisan
docker-compose exec backend php artisan migrate
docker-compose exec backend php artisan db:seed
docker-compose exec backend php artisan cache:clear

# Acessar container do backend
docker-compose exec backend bash

# Ver logs do Laravel
docker-compose exec backend tail -f storage/logs/laravel.log
```

### MySQL
```bash
# Conectar ao MySQL
docker-compose exec mysql mysql -u root -proot123 fone_test

# Backup do banco
docker-compose exec mysql mysqldump -u root -proot123 fone_test > backup.sql

# Restaurar backup
docker-compose exec -T mysql mysql -u root -proot123 fone_test < backup.sql
```

## 🔄 Perfis Docker Compose

### Desenvolvimento (Padrão)
```bash
docker-compose up -d
```

### Com Redis
```bash
docker-compose --profile redis up -d
```

### Com Proxy Nginx
```bash
docker-compose --profile proxy up -d
# Acesso: http://localhost (porta 80)
```

### Produção Completa
```bash
docker-compose --profile redis --profile proxy up -d
```

## 🛠️ Desenvolvimento

### Hot Reload (Frontend)
Para desenvolvimento com hot reload do Vue.js:

```bash
# Parar apenas o frontend
docker-compose stop frontend

# Executar frontend localmente
cd frontend
npm install
npm run dev
```

### Volumes de Desenvolvimento
Os volumes estão configurados para sincronizar código local:
- `./backend:/var/www/html` - Código Laravel
- `./frontend:/app` - Código Vue.js (durante build)

## 🐛 Troubleshooting

### Backend não inicia
```bash
# Verificar logs
docker-compose logs backend

# Recriar container
docker-compose up -d --force-recreate backend
```

### MySQL não conecta
```bash
# Aguardar inicialização completa
docker-compose logs mysql

# Verificar health check
docker-compose ps mysql
```

### Frontend não carrega
```bash
# Verificar se backend está respondendo
curl http://localhost:8000/api/produtos

# Reconstruir frontend
docker-compose build --no-cache frontend
docker-compose up -d --force-recreate frontend
```

### Limpar tudo e recomeçar
```bash
# Parar tudo
docker-compose down -v

# Remover imagens
docker rmi $(docker images -q "fone_test*")

# Reconstruir tudo
docker-compose up -d --build
```

## 📊 Monitoramento

### Verificar saúde dos serviços
```bash
# Status geral
docker-compose ps

# Logs em tempo real
docker-compose logs -f

# Uso de recursos
docker stats
```

### Endpoints de Health Check
- Backend: http://localhost:8000/api/produtos
- Frontend: http://localhost:3000
- MySQL: `docker-compose exec mysql mysqladmin ping`

## 🔒 Segurança

### Produção
Para produção, altere:

1. **Senhas do MySQL** no `docker-compose.yml`
2. **APP_KEY** do Laravel
3. **APP_DEBUG=false** no backend
4. Use **volumes nomeados** ao invés de bind mounts

### Exemplo de configuração de produção:
```yaml
environment:
  - APP_ENV=production
  - APP_DEBUG=false
  - DB_PASSWORD=senha_super_segura_aqui
```

## 📝 Notas Importantes

1. **Primeira execução**: Pode levar alguns minutos para baixar todas as imagens
2. **MySQL**: Aguarde ~30s para inicialização completa antes de executar migrations
3. **Volumes**: Os dados do MySQL persistem entre reinicializações
4. **Logs**: Verifique sempre os logs em caso de problemas

## 🤝 Contribuição

Para contribuir com melhorias na configuração Docker:

1. Teste suas alterações localmente
2. Documente mudanças neste README
3. Considere compatibilidade entre sistemas operacionais
