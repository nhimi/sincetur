<?php
/**
 * Plugin Name:       SINCETUR Portal
 * Plugin URI:        https://github.com/nhimi/sincetur
 * Description:       Portal de Agência de Viagens com módulos de Tours, Hotéis, Actividades, Eventos/Tickets, Assessoria de Visto e ERP (Facturação, Clientes, Fornecedores, Contabilidade PCGA-Angolano).
 * Version:           1.0.0
 * Author:            SINCETUR
 * Author URI:        #
 * License:           GPL-2.0+
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       sincetur-portal
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Prevent direct access.
}

// ─── Constants ────────────────────────────────────────────────────────────────
define( 'SINCETUR_VERSION',     '1.0.0' );
define( 'SINCETUR_PLUGIN_DIR',  plugin_dir_path( __FILE__ ) );
define( 'SINCETUR_PLUGIN_URL',  plugin_dir_url( __FILE__ ) );
define( 'SINCETUR_PLUGIN_FILE', __FILE__ );

// ─── Autoload Includes ────────────────────────────────────────────────────────
$includes = [
    'includes/class-sincetur-installer.php',
    'includes/class-sincetur-tours.php',
    'includes/class-sincetur-hotels.php',
    'includes/class-sincetur-activities.php',
    'includes/class-sincetur-events.php',
    'includes/class-sincetur-visa.php',
    'includes/class-sincetur-erp.php',
    'includes/class-sincetur-customers.php',
    'admin/class-sincetur-admin.php',
    'public/class-sincetur-public.php',
];

foreach ( $includes as $file ) {
    require_once SINCETUR_PLUGIN_DIR . $file;
}

// ─── Activation / Deactivation ────────────────────────────────────────────────
register_activation_hook(   __FILE__, [ 'Sincetur_Installer', 'activate' ] );
register_deactivation_hook( __FILE__, [ 'Sincetur_Installer', 'deactivate' ] );

// ─── Bootstrap ────────────────────────────────────────────────────────────────
add_action( 'plugins_loaded', 'sincetur_init' );

function sincetur_init() {
    load_plugin_textdomain( 'sincetur-portal', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

    // Register custom post types & taxonomies.
    new Sincetur_Tours();
    new Sincetur_Hotels();
    new Sincetur_Activities();
    new Sincetur_Events();
    new Sincetur_Visa();

    // ERP & CRM.
    new Sincetur_ERP();
    new Sincetur_Customers();

    // Admin panel.
    if ( is_admin() ) {
        new Sincetur_Admin();
    }

    // Front-end.
    new Sincetur_Public();
}
