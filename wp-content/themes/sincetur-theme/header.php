<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header class="site-header" role="banner">
    <div class="container site-header__inner">
        <a class="site-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>">
            <?php
            if ( has_custom_logo() ) {
                the_custom_logo();
            } else {
                $name  = get_bloginfo( 'name' );
                $parts = explode( ' ', $name, 2 );
                echo '<span>' . esc_html( $parts[0] ) . '</span>';
                if ( ! empty( $parts[1] ) ) {
                    echo ' ' . esc_html( $parts[1] );
                }
            }
            ?>
        </a>

        <nav class="site-nav" role="navigation" aria-label="<?php esc_attr_e( 'Menu Principal', 'sincetur-theme' ); ?>">
            <?php
            wp_nav_menu( [
                'theme_location' => 'primary',
                'container'      => false,
                'fallback_cb'    => function () {
                    echo '<ul>';
                    echo '<li><a href="' . esc_url( home_url( '/tours' ) ) . '">' . esc_html__( 'Tours', 'sincetur-theme' ) . '</a></li>';
                    echo '<li><a href="' . esc_url( home_url( '/hoteis' ) ) . '">' . esc_html__( 'Hotéis', 'sincetur-theme' ) . '</a></li>';
                    echo '<li><a href="' . esc_url( home_url( '/actividades' ) ) . '">' . esc_html__( 'Actividades', 'sincetur-theme' ) . '</a></li>';
                    echo '<li><a href="' . esc_url( home_url( '/eventos' ) ) . '">' . esc_html__( 'Eventos', 'sincetur-theme' ) . '</a></li>';
                    echo '<li><a href="' . esc_url( home_url( '/vistos' ) ) . '">' . esc_html__( 'Vistos', 'sincetur-theme' ) . '</a></li>';
                    echo '<li><a href="' . esc_url( home_url( '/contacto' ) ) . '">' . esc_html__( 'Contacto', 'sincetur-theme' ) . '</a></li>';
                    echo '</ul>';
                },
            ] );
            ?>
        </nav>
    </div>
</header>
