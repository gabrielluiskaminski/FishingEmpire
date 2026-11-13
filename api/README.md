# FishingEmpire — backend Laravel integrado a partir do ZIP original

Esta pasta foi montada **sobre o arquivo `FishingEmpire-main.zip` enviado pelo usuário**, preservando os Models, as migrations, as Seeders e o `composer.lock` originais. Não é uma reconstrução genérica.

## Escopo incluído

- Catálogo público (busca, categoria, preço, recentes e paginação)
- Carrinho de visitante e autenticado, alteração/remoção e fusão no login e cadastro
- Autenticação Sanctum por cookie/sessão, registro, login, logout e perfil
- Endereços e histórico de pedidos
- Checkout transacional: bloqueio de estoque, cópia de endereço e valores, baixa de estoque, esvaziamento do carrinho
- Pagamento simulado, cancelamento, devolução de estoque, transição de status
- Administração de produtos, categorias e pedidos com proteção de administrador
- Testes de checkout/estoque e permissões

**Swagger/OpenAPI e frontend foram intencionalmente deixados de fora.** O código original do Laravel em `resources/` foi mantido sem uso para o frontend da loja.

## Teste isolado com Docker (recomendado)

As portas externas são **8001 para API** e **3307 para MySQL**, evitando conflito com o projeto original rodando em 8000/3306.

```bash
cp .env.example .env
docker compose up -d --build
docker compose exec php php artisan key:generate
docker compose exec php php artisan migrate --seed
docker compose exec php php artisan route:list --path=api
```

Atenção: use somente um **banco descartável** para testar. O projeto original não é alterado. O volume `composer_vendor` mantém as dependências do build visíveis ao PHP apesar do bind mount.

Para testar a API: `http://localhost:8001/api/products`.
Para o Sanctum: `http://localhost:8001/sanctum/csrf-cookie`, `POST http://localhost:8001/api/auth/login` e `GET http://localhost:8001/api/user` com cookie/CSRF e `Origin: http://localhost:5500`.

O frontend HTML/CSS/JS deverá usar a URL `http://localhost:8001`, com Axios `withCredentials: true` e `withXSRFToken: true`.

## Testes automatizados

O `Dockerfile.dev` inclui `pdo_sqlite`, portanto o `phpunit.xml` original usa SQLite isolado em memória e não precisa tocar no banco MySQL:

```bash
docker compose exec php php artisan test --filter=OrderCheckoutTest
```

Este passo **não foi executado** no ambiente de criação do pacote porque não havia Docker nem extensões de banco disponíveis.

## Seeders

Foram **preservados sem substituição** os Seeders originais e sua ordem no `DatabaseSeeder`. Para conferir usuário e senha de teste, consulte `database/seeders/UserSeeder.php`. Não execute `migrate:fresh` no banco que deseja preservar.

## Regras importantes

- Carrinho: quantidade zero no PATCH remove um item.
- Fusão: soma itens, limita ao estoque e sinaliza indisponibilidade, remove carrinho visitante e expira cookie após concluir.
- Frete: R$ 15,00 até subtotal de R$ 199,99; grátis a partir de R$ 200,00 (regra simples implementada no `OrderService`).
- Status dos pedidos: `pendente` → `pago` → `enviado` → `entregue`; de `pendente` para `cancelado` devolve o estoque.
- Exclusão de produto na administração desativa, não remove fisicamente, preservando os pedidos antigos.

**Atenção:** a integração passou por verificação de sintaxe e inspeção estática das rotas, não por testes de integração completos neste ambiente. Veja `VERIFICACAO.md`.
