<?php
/**
 * Template: Generic Card (used by shortcode listing).
 * Available variables:
 *   $type   – 'tour'|'hotel'|'atividade'|'evento'
 *   $meta   – array with 'label', 'key', 'suffix' or null
 *   $price  – numeric price or null
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
?>
<article class="sinc-card sinc-card-<?php echo esc_attr( $type ); ?>">
    <?php if ( has_post_thumbnail() ) : ?>
    <a href="<?php the_permalink(); ?>" class="sinc-card__thumb">
        <?php the_post_thumbnail( 'medium', [ 'class' => 'sinc-card__img' ] ); ?>
    </a>
    <?php endif; ?>
    <div class="sinc-card__body">
        <h3 class="sinc-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
        <div class="sinc-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 15 ) ); ?></div>
        <?php if ( $price !== null && $price > 0 ) : ?>
        <p class="sinc-card__price">
            <strong><?php echo esc_html( $meta['label'] ); ?>:</strong>
            <?php echo esc_html( number_format( $price, 2, ',', '.' ) . $meta['suffix'] ); ?>
        </p>
        <?php endif; ?>
        <a class="button sinc-card__btn" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Ver Detalhes', 'sincetur-portal' ); ?></a>
    </div>
</article>
