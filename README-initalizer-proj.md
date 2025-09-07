## Como rodar o projeto

1. **Pré-requisitos:**

   - Docker e Docker Compose instalados

2. **Suba os containers:**

   ```sh
   docker-compose up --build -d
   ```

3. **Acesse os serviços:**

   - **Frontend (Vue 3):** [http://localhost:5173](http://localhost:5173) ou [http://localhost:3000](http://localhost:3000)
   - **Backend (Laravel):** [http://localhost:8000](http://localhost:8000)
   - **MySQL:**
     - Host: `localhost`
     - Porta: `4306`
     - Usuário: `root`
     - Senha: `root123`
     - Banco: `fone_test`

4. **Rodar migrations e seeders:**
   Abra um terminal no container do backend:
   ```sh
   docker-compose exec backend bash
   php artisan migrate:fresh
   ```
   OU no próprio terminal do projeto:
    ```sh
    docker-compose exec backend php artisan migrate
    ```
