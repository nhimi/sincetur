# SINCETUR – Portal de Agência de Viagens

Portal de agência de viagens baseado em **WordPress**, construído com um plugin personalizado (`sincetur-portal`) e um tema (`sincetur-theme`).

---

## Módulos Implementados

| Módulo | Descrição |
|---|---|
| **Tours** | CPT `sinc_tour` com preço, duração, itinerário, dificuldade, destinos e tipos |
| **Hotéis** | CPT `sinc_hotel` com estrelas, preço/noite, comodidades, localização |
| **Actividades** | CPT `sinc_atividade` com preço/pessoa, duração em horas, requisitos |
| **Eventos + Tickets** | CPT `sinc_evento` com venda de bilhetes (Geral/VIP) via AJAX; formulário embebível por shortcode |
| **Assessoria de Visto** | CPT `sinc_visto` (guias) + formulário de pedido via AJAX + painel de gestão no admin |
| **ERP – Facturação** | Facturas, Notas de Crédito, Recibos e Pro-Forma com IVA (14 %) e multi-moeda (AOA/USD/EUR) |
| **ERP – Clientes** | Ficha de cliente com NIF, morada, província angolana, tipo (Particular/Empresa) |
| **ERP – Fornecedores** | Registo de fornecedores com NIF e categoria |
| **ERP – Contabilidade (PCGA Angola)** | Plano de Contas pré-carregado (Classes 1-9), Diário Contabilístico em partidas dobradas |
| **ERP – Relatórios** | Balancete de Verificação, Receitas por Período, Extracto de Clientes |
| **CRM – Processos** | Acompanhamento de processos por cliente com estados, prioridades e notas de acompanhamento |

---

## Estrutura do Repositório

```
wp-content/
├── plugins/
│   └── sincetur-portal/            ← Plugin principal
│       ├── sincetur-portal.php     ← Ponto de entrada
│       ├── includes/
│       │   ├── class-sincetur-installer.php   ← Criação de tabelas DB + PCGA seed
│       │   ├── class-sincetur-tours.php
│       │   ├── class-sincetur-hotels.php
│       │   ├── class-sincetur-activities.php
│       │   ├── class-sincetur-events.php      ← + AJAX ticket
│       │   ├── class-sincetur-visa.php        ← + AJAX pedido de visto
│       │   ├── class-sincetur-erp.php         ← Facturação, fornecedores, contabilidade
│       │   └── class-sincetur-customers.php   ← CRM, processos
│       ├── admin/
│       │   ├── class-sincetur-admin.php
│       │   └── views/
│       │       ├── dashboard.php
│       │       ├── erp-invoices.php
│       │       ├── erp-suppliers.php
│       │       ├── erp-chart-accounts.php
│       │       ├── erp-journal.php
│       │       ├── erp-reports.php
│       │       ├── clients.php
│       │       ├── processes.php
│       │       ├── visa-requests.php
│       │       └── tickets.php
│       ├── public/
│       │   ├── class-sincetur-public.php
│       │   └── templates/
│       │       ├── card.php
│       │       ├── ticket-form.php
│       │       └── visa-form.php
│       └── assets/
│           ├── css/admin.css
│           ├── css/public.css
│           ├── js/admin.js
│           └── js/public.js
│
└── themes/
    └── sincetur-theme/             ← Tema WordPress
        ├── style.css
        ├── functions.php
        ├── header.php
        ├── footer.php
        ├── index.php
        ├── single.php
        ├── archive.php
        ├── front-page.php
        ├── template-parts/
        │   ├── content.php
        │   ├── content-card.php
        │   └── content-none.php
        └── assets/css/style.css
```

---

## Instalação

### Pré-requisitos
- WordPress ≥ 6.0
- PHP ≥ 8.0
- MySQL ≥ 5.7 / MariaDB ≥ 10.3

### Passos
1. Copie `wp-content/plugins/sincetur-portal/` para o directório `wp-content/plugins/` da instalação WordPress.
2. Copie `wp-content/themes/sincetur-theme/` para `wp-content/themes/`.
3. No painel WordPress → **Plugins** → active o **SINCETUR Portal**.
4. Em **Aparência → Temas** → active o **SINCETUR Theme**.
5. O plugin cria automaticamente as tabelas de base de dados e carrega o Plano de Contas PCGA angolano.

---

## Shortcodes Disponíveis

| Shortcode | Descrição |
|---|---|
| `[sincetur_tours_listing posts_per_page="6"]` | Lista de tours em grelha |
| `[sincetur_hotels_listing posts_per_page="6"]` | Lista de hotéis |
| `[sincetur_activities_listing posts_per_page="6"]` | Lista de actividades |
| `[sincetur_events_listing posts_per_page="6"]` | Próximos eventos |
| `[sincetur_comprar_ticket evento_id="123"]` | Formulário de compra de bilhete |
| `[sincetur_pedido_visto]` | Formulário de pedido de assessoria de visto |

---

## Tabelas de Base de Dados Criadas

| Tabela | Conteúdo |
|---|---|
| `{prefix}sinc_invoices` | Facturas / Documentos comerciais |
| `{prefix}sinc_invoice_items` | Linhas de factura |
| `{prefix}sinc_erp_clients` | Clientes ERP (ligados a WP users opcionalmente) |
| `{prefix}sinc_erp_suppliers` | Fornecedores |
| `{prefix}sinc_chart_of_accounts` | Plano de Contas PCGA Angola |
| `{prefix}sinc_journal_entries` | Lançamentos contabilísticos |
| `{prefix}sinc_journal_lines` | Linhas dos lançamentos (partidas dobradas) |
| `{prefix}sinc_processes` | Processos / Expedientes de clientes |
| `{prefix}sinc_process_notes` | Notas de acompanhamento de processos |
| `{prefix}sinc_tickets` | Bilhetes de eventos |
| `{prefix}sinc_visa_requests` | Pedidos de assessoria de visto |

---

## Licença

GPL-2.0+
