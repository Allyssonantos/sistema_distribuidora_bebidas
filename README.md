# 🍺 Adora Bebidas — Sistema de PDV

Sistema de ponto de venda desenvolvido para distribuidoras de bebidas, com controle de caixa, estoque, relatórios e painel administrativo completo.

---

## 📸 Telas

### PDV — Caixa
Interface do operador para realizar vendas, com carrinho, busca de produtos e vendas recentes na lateral.
<img width="1904" height="835" alt="image" src="https://github.com/user-attachments/assets/2ff602a8-9358-4c2f-9a7b-ebe63932b903" />

### Painel Administrativo
Gestão completa de produtos, categorias, estoque, relatórios de vendas e lucratividade.

---

## ✅ Funcionalidades

### PDV / Caixa
- Abertura e fechamento de caixa com nome do operador
- Busca de produtos por nome ou código de barras
- Modal de quantidade ao selecionar produto
- Desconto por venda (R$ ou %)
- Formas de pagamento: PIX, Dinheiro, Cartão Débito, Cartão Crédito
- Cálculo automático de troco
- Impressão automática do cupom ao finalizar venda
- Vendas recentes na lateral com horário e forma de pagamento
- Cancelamento de venda com autenticação do administrador
- Relatório de fechamento de caixa com impressão

### Administrativo
- Cadastro e edição de produtos com estoque mínimo e preço de custo/venda
- Categorias de produtos
- Movimentação de estoque (entrada, perda, ajuste de inventário)
- Alerta visual de produtos com estoque baixo
- Relatório de vendas por período com totais por forma de pagamento
- Relatório de descontos concedidos
- Relatório de lucratividade (faturamento, custo, perdas, lucro bruto)
- Relatório de movimentação de estoque
- Fechamento de caixa por sessão

### Cupom de venda
- Impressão em papel 80mm
- Exibe nome do operador, forma de pagamento, itens, desconto e troco
- Impressão automática ao finalizar ou manual via botão

---

## 🛠️ Tecnologias

| Camada | Tecnologia |
|--------|-----------|
| Backend | PHP 8+ |
| Banco de dados | MySQL / MariaDB |
| Frontend | HTML, CSS, JavaScript (vanilla) |
| Servidor local | XAMPP |
| Impressão | CSS `@media print` — 80mm |

---

## 📁 Estrutura de pastas

```
sistema_distribuidora_bebidas/
├── admin/
│   ├── login.php
│   ├── produtos.php
│   ├── categorias.php
│   ├── estoque.php
│   ├── estoque_relatorios.php
│   ├── relatorios.php
│   ├── lucro.php
│   └── fechamento_caixa.php
├── pdv/
│   ├── caixa.php
│   ├── imprimir.php
│   └── imprimir_fechamento.php
├── api/
│   ├── produtos_listar.php
│   ├── produtos_salvar.php
│   ├── produtos_buscar.php
│   ├── categorias_listar.php
│   ├── categorias_salvar.php
│   ├── categorias_excluir.php
│   ├── vendas_salvar.php
│   ├── vendas_listar.php
│   ├── vendas_cancelar.php
│   ├── caixa_abrir.php
│   ├── caixa_fechar.php
│   ├── caixa_status.php
│   ├── caixa_resumo.php
│   ├── caixa_sessoes_listar.php
│   ├── estoque_movimentar.php
│   ├── mov_estoque_relatorio.php
│   ├── fechamento_caixa.php
│   └── lucro_relatorio.php
└── config/
    └── db.php
```

---

## ⚙️ Instalação

### Pré-requisitos
- XAMPP (PHP 8+ e MySQL)
- Navegador moderno

### Passo a passo

**1. Clone o repositório**
```bash
git clone https://github.com/seu-usuario/sistema_distribuidora_bebidas.git
```

**2. Mova para a pasta do XAMPP**
```
C:\xampp\htdocs\sistema_distribuidora_bebidas\
```

**3. Importe o banco de dados**

Acesse o phpMyAdmin (`http://localhost/phpmyadmin`), crie um banco chamado `adora_bebidas` e importe o arquivo `banco.sql`.

**4. Configure a conexão**

Edite o arquivo `config/db.php`:
```php
$host = "localhost";
$db   = "adora_bebidas";
$user = "root";
$pass = "";
```

**5. Acesse o sistema**

| Tela | URL |
|------|-----|
| Login admin | `http://localhost/sistema_distribuidora_bebidas/admin/login.php` |
| PDV / Caixa | `http://localhost/sistema_distribuidora_bebidas/pdv/caixa.php` |

**Credenciais padrão**
```
Usuário: admin
Senha:   admin123
```

---

## 🗄️ Banco de dados

### Tabelas principais

| Tabela | Descrição |
|--------|-----------|
| `produtos` | Cadastro de produtos com preço, custo e estoque |
| `categorias` | Categorias de produtos |
| `vendas` | Registro de vendas com subtotal, desconto e total |
| `venda_itens` | Itens de cada venda |
| `caixa_sessoes` | Sessões de abertura/fechamento de caixa com operador |
| `estoque_movimentacoes` | Histórico de entradas, perdas e ajustes |
| `admins` | Usuários administradores |

### Colunas importantes

```sql
-- Adicionar coluna de operador nas sessões (se ainda não existir)
ALTER TABLE caixa_sessoes ADD COLUMN operador_nome VARCHAR(100) NULL;

-- Adicionar coluna de cancelamento nas vendas (se ainda não existir)
ALTER TABLE vendas ADD COLUMN cancelado_em DATETIME NULL;
```

---

## 🔐 Segurança

- Cancelamento de venda exige senha do administrador
- Sessões PHP protegem todas as páginas do painel admin
- Transações PDO garantem integridade no banco em caso de erro

---

## 🚧 Próximas melhorias

- [ ] Atalhos de teclado no PDV (F2 buscar, F4 finalizar, ESC fechar)
- [ ] Ranking de produtos mais vendidos
- [ ] Alerta de estoque mínimo em tempo real
- [ ] Exportação de relatórios para Excel/CSV
- [ ] Login com múltiplos operadores
- [ ] Pagamento misto (ex: PIX + Dinheiro)

---

## 👨‍💻 Desenvolvido por

**Allysson** — Estudante de Sistema de Informação, Posse - GO  
Projeto pessoal desenvolvido com auxílio de IA (Claude - Anthropic)

---

> Sistema desenvolvido para uso local em ambiente XAMPP. Não recomendado para produção sem implementar HTTPS e hashing de senhas.
