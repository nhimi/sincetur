<?php
/**
 * Installer: creates database tables on activation and handles cleanup on deactivation.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Sincetur_Installer {

    /**
     * Tables created by this plugin.
     */
    public static function get_tables(): array {
        global $wpdb;
        $prefix = $wpdb->prefix . 'sinc_';

        return [
            // ERP – Facturas
            'invoices'         => $prefix . 'invoices',
            'invoice_items'    => $prefix . 'invoice_items',
            // ERP – Clientes & Fornecedores
            'erp_clients'      => $prefix . 'erp_clients',
            'erp_suppliers'    => $prefix . 'erp_suppliers',
            // ERP – Contabilidade PCGA-Angolano
            'chart_of_accounts'=> $prefix . 'chart_of_accounts',
            'journal_entries'  => $prefix . 'journal_entries',
            'journal_lines'    => $prefix . 'journal_lines',
            // CRM – Processos de Clientes
            'processes'        => $prefix . 'processes',
            'process_notes'    => $prefix . 'process_notes',
            // Tickets de Eventos
            'tickets'          => $prefix . 'tickets',
            // Assessoria de Visto
            'visa_requests'    => $prefix . 'visa_requests',
        ];
    }

    /**
     * Run on plugin activation.
     */
    public static function activate(): void {
        self::create_tables();
        self::seed_chart_of_accounts();
        flush_rewrite_rules();
        update_option( 'sincetur_version', SINCETUR_VERSION );
    }

    /**
     * Run on plugin deactivation.
     */
    public static function deactivate(): void {
        flush_rewrite_rules();
    }

    /**
     * Create all custom database tables.
     */
    private static function create_tables(): void {
        global $wpdb;
        $charset = $wpdb->get_charset_collate();
        $t       = self::get_tables();

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        // ── ERP: Facturas ──────────────────────────────────────────────
        dbDelta( "CREATE TABLE {$t['invoices']} (
            id            BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            numero        VARCHAR(30)         NOT NULL,
            tipo          ENUM('factura','nota_credito','recibo','pro_forma') NOT NULL DEFAULT 'factura',
            client_id     BIGINT(20) UNSIGNED NOT NULL,
            data_emissao  DATE                NOT NULL,
            data_vencimento DATE              DEFAULT NULL,
            subtotal      DECIMAL(15,2)       NOT NULL DEFAULT 0.00,
            iva_percentagem DECIMAL(5,2)      NOT NULL DEFAULT 14.00,
            iva_valor     DECIMAL(15,2)       NOT NULL DEFAULT 0.00,
            total         DECIMAL(15,2)       NOT NULL DEFAULT 0.00,
            moeda         CHAR(3)             NOT NULL DEFAULT 'AOA',
            estado        ENUM('rascunho','emitida','paga','cancelada') NOT NULL DEFAULT 'rascunho',
            observacoes   TEXT                DEFAULT NULL,
            created_at    DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at    DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY numero (numero)
        ) $charset;" );

        dbDelta( "CREATE TABLE {$t['invoice_items']} (
            id          BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            invoice_id  BIGINT(20) UNSIGNED NOT NULL,
            descricao   VARCHAR(255)        NOT NULL,
            quantidade  DECIMAL(10,2)       NOT NULL DEFAULT 1.00,
            preco_unit  DECIMAL(15,2)       NOT NULL DEFAULT 0.00,
            total       DECIMAL(15,2)       NOT NULL DEFAULT 0.00,
            conta_pcga  VARCHAR(20)         DEFAULT NULL,
            PRIMARY KEY (id),
            KEY invoice_id (invoice_id)
        ) $charset;" );

        // ── ERP: Clientes ──────────────────────────────────────────────
        dbDelta( "CREATE TABLE {$t['erp_clients']} (
            id            BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            nome          VARCHAR(150)        NOT NULL,
            nif           VARCHAR(20)         DEFAULT NULL,
            email         VARCHAR(100)        DEFAULT NULL,
            telefone      VARCHAR(30)         DEFAULT NULL,
            morada        TEXT                DEFAULT NULL,
            provincia     VARCHAR(50)         DEFAULT NULL,
            pais          VARCHAR(60)         NOT NULL DEFAULT 'Angola',
            tipo          ENUM('particular','empresa') NOT NULL DEFAULT 'particular',
            wp_user_id    BIGINT(20) UNSIGNED DEFAULT NULL,
            created_at    DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY nif (nif),
            KEY wp_user_id (wp_user_id)
        ) $charset;" );

        // ── ERP: Fornecedores ──────────────────────────────────────────
        dbDelta( "CREATE TABLE {$t['erp_suppliers']} (
            id            BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            nome          VARCHAR(150)        NOT NULL,
            nif           VARCHAR(20)         DEFAULT NULL,
            email         VARCHAR(100)        DEFAULT NULL,
            telefone      VARCHAR(30)         DEFAULT NULL,
            morada        TEXT                DEFAULT NULL,
            pais          VARCHAR(60)         NOT NULL DEFAULT 'Angola',
            categoria     VARCHAR(80)         DEFAULT NULL,
            created_at    DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY nif (nif)
        ) $charset;" );

        // ── ERP: Plano de Contas PCGA-Angolano ────────────────────────
        dbDelta( "CREATE TABLE {$t['chart_of_accounts']} (
            id          BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            codigo      VARCHAR(20)         NOT NULL,
            descricao   VARCHAR(200)        NOT NULL,
            tipo        ENUM('activo','passivo','capital','proveito','custo','resultado') NOT NULL,
            parent_id   BIGINT(20) UNSIGNED DEFAULT NULL,
            nivel       TINYINT(1)          NOT NULL DEFAULT 1,
            activa      TINYINT(1)          NOT NULL DEFAULT 1,
            PRIMARY KEY (id),
            UNIQUE KEY codigo (codigo),
            KEY parent_id (parent_id)
        ) $charset;" );

        // ── ERP: Diário Contabilístico ────────────────────────────────
        dbDelta( "CREATE TABLE {$t['journal_entries']} (
            id          BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            numero      VARCHAR(30)         NOT NULL,
            data        DATE                NOT NULL,
            descricao   VARCHAR(255)        DEFAULT NULL,
            invoice_id  BIGINT(20) UNSIGNED DEFAULT NULL,
            criado_por  BIGINT(20) UNSIGNED DEFAULT NULL,
            created_at  DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY numero (numero)
        ) $charset;" );

        dbDelta( "CREATE TABLE {$t['journal_lines']} (
            id          BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            entry_id    BIGINT(20) UNSIGNED NOT NULL,
            conta_id    BIGINT(20) UNSIGNED NOT NULL,
            debito      DECIMAL(15,2)       NOT NULL DEFAULT 0.00,
            credito     DECIMAL(15,2)       NOT NULL DEFAULT 0.00,
            descricao   VARCHAR(255)        DEFAULT NULL,
            PRIMARY KEY (id),
            KEY entry_id (entry_id),
            KEY conta_id (conta_id)
        ) $charset;" );

        // ── CRM: Processos ────────────────────────────────────────────
        dbDelta( "CREATE TABLE {$t['processes']} (
            id            BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            client_id     BIGINT(20) UNSIGNED NOT NULL,
            tipo          VARCHAR(80)         NOT NULL,
            referencia    VARCHAR(30)         NOT NULL,
            titulo        VARCHAR(200)        NOT NULL,
            estado        ENUM('aberto','em_curso','concluido','cancelado') NOT NULL DEFAULT 'aberto',
            prioridade    ENUM('baixa','normal','alta','urgente') NOT NULL DEFAULT 'normal',
            responsavel   BIGINT(20) UNSIGNED DEFAULT NULL,
            data_inicio   DATE                DEFAULT NULL,
            data_fim      DATE                DEFAULT NULL,
            descricao     TEXT                DEFAULT NULL,
            created_at    DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at    DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY referencia (referencia),
            KEY client_id (client_id)
        ) $charset;" );

        dbDelta( "CREATE TABLE {$t['process_notes']} (
            id          BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            process_id  BIGINT(20) UNSIGNED NOT NULL,
            autor_id    BIGINT(20) UNSIGNED NOT NULL,
            nota        TEXT                NOT NULL,
            created_at  DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY process_id (process_id)
        ) $charset;" );

        // ── Tickets de Eventos ────────────────────────────────────────
        dbDelta( "CREATE TABLE {$t['tickets']} (
            id            BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            evento_id     BIGINT(20) UNSIGNED NOT NULL,
            client_id     BIGINT(20) UNSIGNED DEFAULT NULL,
            nome_cliente  VARCHAR(150)        NOT NULL,
            email_cliente VARCHAR(100)        NOT NULL,
            telefone      VARCHAR(30)         DEFAULT NULL,
            tipo_bilhete  VARCHAR(80)         NOT NULL DEFAULT 'geral',
            preco         DECIMAL(10,2)       NOT NULL DEFAULT 0.00,
            moeda         CHAR(3)             NOT NULL DEFAULT 'AOA',
            codigo        VARCHAR(30)         NOT NULL,
            estado        ENUM('reservado','pago','cancelado','usado') NOT NULL DEFAULT 'reservado',
            data_compra   DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY codigo (codigo),
            KEY evento_id (evento_id)
        ) $charset;" );

        // ── Assessoria de Visto ───────────────────────────────────────
        dbDelta( "CREATE TABLE {$t['visa_requests']} (
            id              BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            client_id       BIGINT(20) UNSIGNED DEFAULT NULL,
            nome_completo   VARCHAR(150)        NOT NULL,
            email           VARCHAR(100)        NOT NULL,
            telefone        VARCHAR(30)         DEFAULT NULL,
            passaporte_num  VARCHAR(30)         DEFAULT NULL,
            pais_destino    VARCHAR(80)         NOT NULL,
            tipo_visto      VARCHAR(80)         NOT NULL,
            data_viagem     DATE                DEFAULT NULL,
            estado          ENUM('pendente','em_analise','aprovado','rejeitado','entregue') NOT NULL DEFAULT 'pendente',
            responsavel     BIGINT(20) UNSIGNED DEFAULT NULL,
            observacoes     TEXT                DEFAULT NULL,
            created_at      DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at      DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY client_id (client_id)
        ) $charset;" );
    }

    /**
     * Seed the chart of accounts with standard Angolan PCGA structure.
     * Only inserts if the table is empty.
     */
    private static function seed_chart_of_accounts(): void {
        global $wpdb;
        $table = self::get_tables()['chart_of_accounts'];

        if ( (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" ) > 0 ) {
            return;
        }

        $accounts = [
            // Classe 1 – Meios Monetários e Equivalentes
            [ '1',   'Meios Monetários e Equivalentes',              'activo',    null, 1 ],
            [ '11',  'Caixa',                                        'activo',    '1',  2 ],
            [ '12',  'Depósitos Bancários',                          'activo',    '1',  2 ],
            [ '13',  'Outros Meios Monetários',                      'activo',    '1',  2 ],
            // Classe 2 – Contas a Receber / Pagar
            [ '2',   'Contas a Receber e a Pagar',                   'activo',    null, 1 ],
            [ '21',  'Clientes',                                     'activo',    '2',  2 ],
            [ '22',  'Fornecedores',                                 'passivo',   '2',  2 ],
            [ '23',  'Pessoal',                                      'passivo',   '2',  2 ],
            [ '24',  'Estado e Outros Entes Públicos',               'passivo',   '2',  2 ],
            [ '25',  'Financiamentos Obtidos',                       'passivo',   '2',  2 ],
            [ '26',  'Accionistas / Sócios',                         'capital',   '2',  2 ],
            [ '27',  'Outras Contas a Receber e a Pagar',            'activo',    '2',  2 ],
            // Classe 3 – Inventários
            [ '3',   'Inventários',                                  'activo',    null, 1 ],
            [ '31',  'Compras',                                      'custo',     '3',  2 ],
            [ '32',  'Mercadorias',                                  'activo',    '3',  2 ],
            [ '33',  'Matérias-Primas e Subsidiárias',               'activo',    '3',  2 ],
            // Classe 4 – Investimentos
            [ '4',   'Investimentos',                                'activo',    null, 1 ],
            [ '41',  'Investimentos Financeiros',                    'activo',    '4',  2 ],
            [ '42',  'Propriedades de Investimento',                 'activo',    '4',  2 ],
            [ '43',  'Activos Fixos Tangíveis',                      'activo',    '4',  2 ],
            [ '44',  'Activos Intangíveis',                          'activo',    '4',  2 ],
            // Classe 5 – Capital Próprio
            [ '5',   'Capital Próprio',                              'capital',   null, 1 ],
            [ '51',  'Capital Social',                               'capital',   '5',  2 ],
            [ '52',  'Prestações Suplementares',                     'capital',   '5',  2 ],
            [ '53',  'Prémios de Emissão',                           'capital',   '5',  2 ],
            [ '54',  'Reservas',                                     'capital',   '5',  2 ],
            [ '55',  'Resultados Transitados',                       'capital',   '5',  2 ],
            [ '56',  'Ajustamentos / Activos Financeiros',           'capital',   '5',  2 ],
            [ '57',  'Excedentes de Revalorização',                  'capital',   '5',  2 ],
            [ '58',  'Outras Variações no Capital Próprio',          'capital',   '5',  2 ],
            // Classe 6 – Gastos
            [ '6',   'Gastos',                                       'custo',     null, 1 ],
            [ '61',  'Custo das Mercadorias Vendidas e M. Consumidas','custo',    '6',  2 ],
            [ '62',  'Fornecimentos e Serviços Externos',            'custo',     '6',  2 ],
            [ '63',  'Gastos com o Pessoal',                         'custo',     '6',  2 ],
            [ '64',  'Imparidade de Dívidas a Receber',              'custo',     '6',  2 ],
            [ '65',  'Perdas por Imparidade',                        'custo',     '6',  2 ],
            [ '66',  'Perdas em Subsidiárias e Associadas',          'custo',     '6',  2 ],
            [ '67',  'Gastos e Perdas de Financiamento',             'custo',     '6',  2 ],
            [ '68',  'Outros Gastos e Perdas',                       'custo',     '6',  2 ],
            [ '69',  'Gastos e Perdas de Anos Anteriores',           'custo',     '6',  2 ],
            // Classe 7 – Rendimentos
            [ '7',   'Rendimentos',                                  'proveito',  null, 1 ],
            [ '71',  'Vendas e Prestações de Serviços',              'proveito',  '7',  2 ],
            [ '711', 'Vendas de Tours',                              'proveito',  '71', 3 ],
            [ '712', 'Reservas de Hotéis',                           'proveito',  '71', 3 ],
            [ '713', 'Actividades Turísticas',                       'proveito',  '71', 3 ],
            [ '714', 'Venda de Bilhetes / Eventos',                  'proveito',  '71', 3 ],
            [ '715', 'Assessoria de Visto',                          'proveito',  '71', 3 ],
            [ '72',  'Subsídios de Exploração',                      'proveito',  '7',  2 ],
            [ '73',  'Trabalhos para a Própria Empresa',             'proveito',  '7',  2 ],
            [ '74',  'Variação nos Inventários de Produção',         'proveito',  '7',  2 ],
            [ '75',  'Ganhos em Subsidiárias e Associadas',          'proveito',  '7',  2 ],
            [ '76',  'Rendimentos e Ganhos de Financiamento',        'proveito',  '7',  2 ],
            [ '77',  'Ganhos por Aumento do Justo Valor',            'proveito',  '7',  2 ],
            [ '78',  'Outros Rendimentos e Ganhos',                  'proveito',  '7',  2 ],
            [ '79',  'Rendimentos e Ganhos de Anos Anteriores',      'proveito',  '7',  2 ],
            // Classe 8 – Resultados
            [ '8',   'Resultados',                                   'resultado', null, 1 ],
            [ '81',  'Resultado Antes de Impostos',                  'resultado', '8',  2 ],
            [ '82',  'Imposto sobre o Rendimento',                   'resultado', '8',  2 ],
            [ '88',  'Resultado Líquido do Período',                 'resultado', '8',  2 ],
            // Classe 9 – Contabilidade Analítica (opcional)
            [ '9',   'Contabilidade Analítica',                      'resultado', null, 1 ],
        ];

        // Build parent_id map (codigo → id inserted)
        $id_map = [];

        foreach ( $accounts as $row ) {
            [ $codigo, $descricao, $tipo, $parent_codigo, $nivel ] = $row;
            $parent_id = ( $parent_codigo && isset( $id_map[ $parent_codigo ] ) )
                ? $id_map[ $parent_codigo ]
                : null;

            $wpdb->insert(
                $table,
                [
                    'codigo'    => $codigo,
                    'descricao' => $descricao,
                    'tipo'      => $tipo,
                    'parent_id' => $parent_id,
                    'nivel'     => $nivel,
                    'activa'    => 1,
                ],
                [ '%s', '%s', '%s', '%d', '%d', '%d' ]
            );
            $id_map[ $codigo ] = (int) $wpdb->insert_id;
        }
    }
}
