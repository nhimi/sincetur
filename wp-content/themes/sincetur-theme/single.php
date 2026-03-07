<?php
/**
 * Single Tour / Hotel / Activity / Event template.
 */
get_header();
?>
<main class="site-content" role="main">
    <div class="container">
    <?php while ( have_posts() ) : the_post(); ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
            <?php if ( has_post_thumbnail() ) : ?>
            <div class="entry-thumbnail" style="margin-bottom:24px">
                <?php the_post_thumbnail( 'sinc-hero', [ 'style' => 'width:100%;border-radius:10px' ] ); ?>
            </div>
            <?php endif; ?>

            <h1 class="entry-title"><?php the_title(); ?></h1>

            <?php
            $post_type = get_post_type();

            // Show type-specific meta.
            if ( $post_type === 'sinc_tour' ) :
                $preco       = get_post_meta( get_the_ID(), 'sinc_tour_preco', true );
                $duracao     = get_post_meta( get_the_ID(), 'sinc_tour_duracao', true );
                $dificuldade = get_post_meta( get_the_ID(), 'sinc_tour_dificuldade', true );
                $max_pessoas = get_post_meta( get_the_ID(), 'sinc_tour_max_pessoas', true );
            ?>
            <div class="sinc-meta-bar">
                <?php if ( $preco )       : ?><span>💰 <?php echo esc_html( number_format( (float)$preco, 2, ',', '.' ) . ' AOA' ); ?></span><?php endif; ?>
                <?php if ( $duracao )     : ?><span>📅 <?php echo esc_html( $duracao ); ?> <?php esc_html_e( 'dias', 'sincetur-theme' ); ?></span><?php endif; ?>
                <?php if ( $dificuldade ) : ?><span>🏔 <?php echo esc_html( ucfirst( $dificuldade ) ); ?></span><?php endif; ?>
                <?php if ( $max_pessoas ) : ?><span>👥 <?php echo esc_html( $max_pessoas ); ?> <?php esc_html_e( 'pessoas máx.', 'sincetur-theme' ); ?></span><?php endif; ?>
            </div>

            <?php elseif ( $post_type === 'sinc_hotel' ) :
                $estrelas = (int) get_post_meta( get_the_ID(), 'sinc_hotel_estrelas', true );
                $preco    = get_post_meta( get_the_ID(), 'sinc_hotel_preco_noite', true );
                $endereco = get_post_meta( get_the_ID(), 'sinc_hotel_endereco', true );
            ?>
            <div class="sinc-meta-bar">
                <?php if ( $estrelas ) : ?><span><?php echo esc_html( str_repeat( '⭐', min( $estrelas, 5 ) ) ); ?></span><?php endif; ?>
                <?php if ( $preco )    : ?><span>💰 <?php esc_html_e( 'A partir de', 'sincetur-theme' ); ?> <?php echo esc_html( number_format( (float)$preco, 2, ',', '.' ) . ' AOA / noite' ); ?></span><?php endif; ?>
                <?php if ( $endereco ) : ?><span>📍 <?php echo esc_html( $endereco ); ?></span><?php endif; ?>
            </div>

            <?php elseif ( $post_type === 'sinc_evento' ) :
                $data_inicio  = get_post_meta( get_the_ID(), 'sinc_evento_data_inicio', true );
                $local        = get_post_meta( get_the_ID(), 'sinc_evento_local', true );
                $preco_geral  = get_post_meta( get_the_ID(), 'sinc_evento_preco_geral', true );
                $preco_vip    = get_post_meta( get_the_ID(), 'sinc_evento_preco_vip', true );
            ?>
            <div class="sinc-meta-bar">
                <?php if ( $data_inicio ) : ?><span>📅 <?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' H:i', strtotime( $data_inicio ) ) ); ?></span><?php endif; ?>
                <?php if ( $local )       : ?><span>📍 <?php echo esc_html( $local ); ?></span><?php endif; ?>
                <?php if ( $preco_geral ) : ?><span>🎟 <?php esc_html_e( 'Geral', 'sincetur-theme' ); ?>: <?php echo esc_html( number_format( (float)$preco_geral, 2, ',', '.' ) . ' AOA' ); ?></span><?php endif; ?>
                <?php if ( $preco_vip )   : ?><span>🎟 VIP: <?php echo esc_html( number_format( (float)$preco_vip, 2, ',', '.' ) . ' AOA' ); ?></span><?php endif; ?>
            </div>

            <?php if ( class_exists( 'Sincetur_Events' ) ) : ?>
                <?php echo do_shortcode( '[sincetur_comprar_ticket evento_id="' . get_the_ID() . '"]' ); ?>
            <?php endif; ?>

            <?php endif; // post type ?>

            <div class="entry-content">
                <?php the_content(); ?>
            </div>
        </article>
    <?php endwhile; ?>
    </div>
</main>

<style>
.sinc-meta-bar { display: flex; flex-wrap: wrap; gap: 16px; margin: 16px 0 24px; padding: 16px; background: #f7f8fa; border-radius: 8px; }
.sinc-meta-bar span { font-size: 15px; }
</style>

<?php
get_footer();
