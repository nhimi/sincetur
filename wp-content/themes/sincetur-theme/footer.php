    </div><!-- .site-content -->
</main>

<footer class="site-footer" role="contentinfo">
    <div class="container">
        <div class="site-footer__grid">
            <!-- Brand Column -->
            <div>
                <h4><?php bloginfo( 'name' ); ?></h4>
                <p><?php bloginfo( 'description' ); ?></p>
                <p style="margin-top:14px;font-size:13px">
                    <?php esc_html_e( 'Agência de Viagens certificada. Luanda, Angola.', 'sincetur-theme' ); ?>
                </p>
            </div>

            <!-- Quick Links -->
            <div>
                <h4><?php esc_html_e( 'Links Rápidos', 'sincetur-theme' ); ?></h4>
                <ul>
                    <li><a href="<?php echo esc_url( home_url( '/tours' ) ); ?>"><?php esc_html_e( 'Tours', 'sincetur-theme' ); ?></a></li>
                    <li><a href="<?php echo esc_url( home_url( '/hoteis' ) ); ?>"><?php esc_html_e( 'Hotéis', 'sincetur-theme' ); ?></a></li>
                    <li><a href="<?php echo esc_url( home_url( '/actividades' ) ); ?>"><?php esc_html_e( 'Actividades', 'sincetur-theme' ); ?></a></li>
                    <li><a href="<?php echo esc_url( home_url( '/eventos' ) ); ?>"><?php esc_html_e( 'Eventos', 'sincetur-theme' ); ?></a></li>
                    <li><a href="<?php echo esc_url( home_url( '/vistos' ) ); ?>"><?php esc_html_e( 'Pedido de Visto', 'sincetur-theme' ); ?></a></li>
                </ul>
            </div>

            <!-- Contact -->
            <div>
                <h4><?php esc_html_e( 'Contacto', 'sincetur-theme' ); ?></h4>
                <ul>
                    <li><?php echo esc_html( get_theme_mod( 'sinc_address', 'Luanda, Angola' ) ); ?></li>
                    <li><a href="tel:<?php echo esc_attr( get_theme_mod( 'sinc_phone', '+244 900 000 000' ) ); ?>"><?php echo esc_html( get_theme_mod( 'sinc_phone', '+244 900 000 000' ) ); ?></a></li>
                    <li><a href="mailto:<?php echo esc_attr( get_theme_mod( 'sinc_email', 'info@sincetur.ao' ) ); ?>"><?php echo esc_html( get_theme_mod( 'sinc_email', 'info@sincetur.ao' ) ); ?></a></li>
                </ul>
            </div>
        </div>

        <div class="site-footer__bottom">
            <p>
                &copy; <?php echo esc_html( current_time( 'Y' ) ); ?>
                <?php bloginfo( 'name' ); ?>.
                <?php esc_html_e( 'Todos os direitos reservados.', 'sincetur-theme' ); ?>
            </p>
        </div>
    </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
