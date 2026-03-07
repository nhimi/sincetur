<?php
/**
 * Archive template for all SINCETUR custom post types.
 */
get_header();
?>
<main class="site-content" role="main">
    <div class="container">
        <header class="sinc-archive-header">
            <h1 class="entry-title"><?php post_type_archive_title(); ?></h1>
            <?php the_archive_description( '<div class="archive-description">', '</div>' ); ?>
        </header>

        <?php
        $post_type = get_query_var( 'post_type' );
        $sc_map = [
            'sinc_tour'     => '[sincetur_tours_listing posts_per_page="-1"]',
            'sinc_hotel'    => '[sincetur_hotels_listing posts_per_page="-1"]',
            'sinc_atividade'=> '[sincetur_activities_listing posts_per_page="-1"]',
            'sinc_evento'   => '[sincetur_events_listing posts_per_page="-1"]',
        ];

        if ( isset( $sc_map[ $post_type ] ) && class_exists( 'Sincetur_Public' ) ) {
            echo do_shortcode( $sc_map[ $post_type ] );
        } else {
            if ( have_posts() ) {
                echo '<div class="sinc-listing">';
                while ( have_posts() ) :
                    the_post();
                    get_template_part( 'template-parts/content', 'card' );
                endwhile;
                echo '</div>';
                the_posts_navigation();
            } else {
                echo '<p>' . esc_html__( 'Nenhum resultado encontrado.', 'sincetur-theme' ) . '</p>';
            }
        }
        ?>
    </div>
</main>
<?php
get_footer();
