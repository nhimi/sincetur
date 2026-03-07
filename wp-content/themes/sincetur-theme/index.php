<?php
/**
 * Default template for posts and pages.
 */
get_header();
?>
<main class="site-content" role="main">
    <div class="container">
        <?php
        if ( have_posts() ) :
            while ( have_posts() ) :
                the_post();
                get_template_part( 'template-parts/content', get_post_type() );
            endwhile;

            the_posts_navigation();
        else :
            get_template_part( 'template-parts/content', 'none' );
        endif;
        ?>
    </div>
</main>
<?php
get_footer();
