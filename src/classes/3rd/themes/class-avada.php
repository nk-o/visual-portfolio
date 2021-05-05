<?php
/**
 * Avada Theme.
 *
 * @package @@plugin_name
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Visual_Portfolio_3rd_Avada
 */
class Visual_Portfolio_3rd_Avada {
    /**
     * Visual_Portfolio_3rd_Avada constructor.
     */
    public function __construct() {
        if ( is_admin() ) {
            return;
        }

        $avada_options = get_option( 'fusion_options' );

        if ( 'avada' !== get_template() || ! isset( $avada_options['lazy_load'] ) || 'avada' !== $avada_options['lazy_load'] ) {
            return;
        }

        // Disable our lazyload if Avada's lazyload used.
        add_filter( 'vpf_images_lazyload', '__return_false' );
    }
}

new Visual_Portfolio_3rd_Avada();
