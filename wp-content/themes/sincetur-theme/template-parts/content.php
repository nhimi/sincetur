<?php
/**
 * Template part: default content.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
?>
<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <h2 class="entry-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
    <div class="entry-content">
        <?php the_excerpt(); ?>
    </div>
</article>
