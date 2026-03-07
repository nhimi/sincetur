<?php
/**
 * SINCETUR Theme – functions.php
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'SINC_THEME_VERSION', '1.0.0' );
define( 'SINC_THEME_DIR',     get_template_directory() );
define( 'SINC_THEME_URL',     get_template_directory_uri() );

// ─── Setup ────────────────────────────────────────────────────────────────────
add_action( 'after_setup_theme', function () {
    load_theme_textdomain( 'sincetur-theme', SINC_THEME_DIR . '/languages' );

    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'html5', [ 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ] );
    add_theme_support( 'customize-selective-refresh-widgets' );
    add_theme_support( 'responsive-embeds' );
    add_theme_support( 'wp-block-styles' );
    add_theme_support( 'align-wide' );

    add_image_size( 'sinc-card',  600, 400, true );
    add_image_size( 'sinc-hero',  1400, 600, true );
    add_image_size( 'sinc-thumb', 360, 240, true );

    register_nav_menus( [
        'primary' => __( 'Menu Principal', 'sincetur-theme' ),
        'footer'  => __( 'Menu Rodapé', 'sincetur-theme' ),
    ] );
} );

// ─── Enqueue Assets ──────────────────────────────────────────────────────────
add_action( 'wp_enqueue_scripts', function () {
    wp_enqueue_style(
        'sincetur-theme-style',
        SINC_THEME_URL . '/assets/css/style.css',
        [],
        SINC_THEME_VERSION
    );
    // Load WordPress Dashicons on front-end for feature icons.
    wp_enqueue_style( 'dashicons' );
} );

// ─── Widgets ─────────────────────────────────────────────────────────────────
add_action( 'widgets_init', function () {
    register_sidebar( [
        'name'          => __( 'Sidebar Principal', 'sincetur-theme' ),
        'id'            => 'sidebar-1',
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h4 class="widget-title">',
        'after_title'   => '</h4>',
    ] );
    register_sidebar( [
        'name'          => __( 'Rodapé Coluna 1', 'sincetur-theme' ),
        'id'            => 'footer-1',
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h4>',
        'after_title'   => '</h4>',
    ] );
} );
