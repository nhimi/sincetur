<?php
/**
 * Front Page Template – Homepage of SINCETUR Portal.
 */
get_header();
?>

<section class="site-hero">
    <div class="container">
        <h2><?php esc_html_e( 'Descubra Angola e o Mundo', 'sincetur-theme' ); ?></h2>
        <p><?php esc_html_e( 'Tours, Hotéis, Actividades, Eventos e muito mais. Somos a sua agência de viagens de confiança.', 'sincetur-theme' ); ?></p>
        <a class="hero-cta" href="<?php echo esc_url( home_url( '/tours' ) ); ?>"><?php esc_html_e( 'Ver Tours Disponíveis', 'sincetur-theme' ); ?></a>
    </div>
</section>

<!-- Services Overview -->
<section class="sinc-section">
    <div class="container">
        <div class="sinc-section__header">
            <h2><?php esc_html_e( 'Os Nossos Serviços', 'sincetur-theme' ); ?></h2>
            <p><?php esc_html_e( 'Tudo o que precisa para a sua viagem num só lugar.', 'sincetur-theme' ); ?></p>
        </div>
        <div class="sinc-features">
            <div class="sinc-feature">
                <span class="dashicons dashicons-palmtree"></span>
                <h3><?php esc_html_e( 'Tours', 'sincetur-theme' ); ?></h3>
                <p><?php esc_html_e( 'Pacotes turísticos nacionais e internacionais.', 'sincetur-theme' ); ?></p>
            </div>
            <div class="sinc-feature">
                <span class="dashicons dashicons-building"></span>
                <h3><?php esc_html_e( 'Hotéis', 'sincetur-theme' ); ?></h3>
                <p><?php esc_html_e( 'Reserva de alojamento com as melhores tarifas.', 'sincetur-theme' ); ?></p>
            </div>
            <div class="sinc-feature">
                <span class="dashicons dashicons-chart-area"></span>
                <h3><?php esc_html_e( 'Actividades', 'sincetur-theme' ); ?></h3>
                <p><?php esc_html_e( 'Experiências únicas no destino da sua escolha.', 'sincetur-theme' ); ?></p>
            </div>
            <div class="sinc-feature">
                <span class="dashicons dashicons-tickets-alt"></span>
                <h3><?php esc_html_e( 'Eventos', 'sincetur-theme' ); ?></h3>
                <p><?php esc_html_e( 'Bilhetes para os melhores eventos culturais e desportivos.', 'sincetur-theme' ); ?></p>
            </div>
            <div class="sinc-feature">
                <span class="dashicons dashicons-id-alt"></span>
                <h3><?php esc_html_e( 'Assessoria de Visto', 'sincetur-theme' ); ?></h3>
                <p><?php esc_html_e( 'Apoio completo no processo de pedido de visto.', 'sincetur-theme' ); ?></p>
            </div>
        </div>
    </div>
</section>

<!-- Featured Tours -->
<?php if ( class_exists( 'Sincetur_Tours' ) ) : ?>
<section class="sinc-section">
    <div class="container">
        <div class="sinc-section__header">
            <h2><?php esc_html_e( 'Tours em Destaque', 'sincetur-theme' ); ?></h2>
            <p><?php esc_html_e( 'Explore os nossos pacotes turísticos mais populares.', 'sincetur-theme' ); ?></p>
        </div>
        <?php echo do_shortcode( '[sincetur_tours_listing posts_per_page="3"]' ); ?>
        <p style="text-align:center;margin-top:24px">
            <a class="button" href="<?php echo esc_url( get_post_type_archive_link( 'sinc_tour' ) ); ?>"><?php esc_html_e( 'Ver Todos os Tours', 'sincetur-theme' ); ?></a>
        </p>
    </div>
</section>

<!-- Upcoming Events -->
<section class="sinc-section">
    <div class="container">
        <div class="sinc-section__header">
            <h2><?php esc_html_e( 'Próximos Eventos', 'sincetur-theme' ); ?></h2>
            <p><?php esc_html_e( 'Compre o seu bilhete online de forma rápida e segura.', 'sincetur-theme' ); ?></p>
        </div>
        <?php echo do_shortcode( '[sincetur_events_listing posts_per_page="3"]' ); ?>
        <p style="text-align:center;margin-top:24px">
            <a class="button" href="<?php echo esc_url( get_post_type_archive_link( 'sinc_evento' ) ); ?>"><?php esc_html_e( 'Ver Todos os Eventos', 'sincetur-theme' ); ?></a>
        </p>
    </div>
</section>
<?php endif; ?>

<?php
get_footer();
