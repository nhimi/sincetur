<?php
/**
 * Template part: generic card (used in archive fallback).
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
?>
<article class="sinc-card">
    <?php if ( has_post_thumbnail() ) : ?>
    <a href="<?php the_permalink(); ?>" class="sinc-card__thumb">
        <?php the_post_thumbnail( 'sinc-card', [ 'class' => 'sinc-card__img' ] ); ?>
    </a>
    <?php endif; ?>
    <div class="sinc-card__body">
        <h3 class="sinc-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
        <div class="sinc-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 18 ) ); ?></div>
        <a class="sinc-card__btn" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Ver Detalhes', 'sincetur-theme' ); ?></a>
    </div>
</article>
