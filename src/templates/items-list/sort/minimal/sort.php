<?php
/**
 * Minimal sort template.
 *
 * @var $args
 * @package @@plugin_name
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

?>

<div class="<?php echo esc_attr( $args['class'] ); ?> vp-sort__style-minimal">
    <?php
    // phpcs:ignore
    foreach ( $args['items'] as $item ) {
        ?>
        <div class="<?php echo esc_attr( $item['class'] ); ?>">
            <a href="<?php echo esc_url( $item['url'] ); ?>" data-vp-sort="<?php echo esc_attr( $item['sort'] ); ?>">
                <?php echo esc_html( $item['label'] ); ?>
            </a>
        </div>
        <?php
    }
    ?>
</div>
