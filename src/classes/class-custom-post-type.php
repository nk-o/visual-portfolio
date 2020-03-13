<?php
/**
 * Register Custom Post Types.
 *
 * @package @@plugin_name/admin
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Visual_Portfolio_Custom_Post_Type
 */
class Visual_Portfolio_Custom_Post_Type {
    /**
     * Visual_Portfolio_Custom_Post_Type constructor.
     */
    public function __construct() {
        // custom post types.
        add_action( 'init', array( $this, 'add_custom_post_type' ) );
        add_action( 'restrict_manage_posts', array( $this, 'filter_custom_post_by_taxonomies' ), 10 );

        // add post formats.
        add_action( 'after_setup_theme', array( $this, 'add_video_post_format' ), 99 );
        add_action( 'add_meta_boxes', array( $this, 'add_post_format_metaboxes' ), 1 );
        add_action( 'save_post', array( $this, 'save_post_format_metaboxes' ) );

        // custom post roles.
        add_action( 'init', array( $this, 'add_role_caps' ) );

        // show blank state for portfolio list page.
        add_action( 'manage_posts_extra_tablenav', array( $this, 'maybe_render_blank_state' ) );

        // remove screen options from portfolio list page.
        add_action( 'screen_options_show_screen', array( $this, 'remove_screen_options' ), 10, 2 );

        // show thumbnail in portfolio list table.
        add_filter( 'manage_portfolio_posts_columns', array( $this, 'add_portfolio_img_column' ) );
        add_filter( 'manage_portfolio_posts_custom_column', array( $this, 'manage_portfolio_img_column' ), 10, 2 );

        // show shortcode in vp_lists table.
        add_filter( 'manage_vp_lists_posts_columns', array( $this, 'add_vp_lists_shortcode_column' ) );
        add_filter( 'manage_vp_lists_posts_custom_column', array( $this, 'manage_vp_lists_shortcode_column' ), 10, 2 );

        // highlight admin menu items.
        add_action( 'admin_menu', array( $this, 'admin_menu' ), 12 );

        // show admin menu dropdown with available portfolios on the current page.
        add_action( 'wp_before_admin_bar_render', array( $this, 'wp_before_admin_bar_render' ) );
    }

    /**
     * Add custom post type
     */
    public function add_custom_post_type() {
        $custom_slug = Visual_Portfolio_Settings::get_option( 'portfolio_slug', 'vp_general', 'portfolio' );

        // portfolio items post type.
        register_post_type(
            'portfolio',
            array(
                'labels'             => array(
                    'name'               => _x( 'Portfolio Items', 'Post Type General Name', '@@text_domain' ),
                    'singular_name'      => _x( 'Portfolio Item', 'Post Type Singular Name', '@@text_domain' ),
                    'menu_name'          => __( 'Visual Portfolio', '@@text_domain' ),
                    'parent_item_colon'  => __( 'Parent Portfolio Item', '@@text_domain' ),
                    'all_items'          => __( 'Portfolio Items', '@@text_domain' ),
                    'view_item'          => __( 'View Portfolio Item', '@@text_domain' ),
                    'add_new_item'       => __( 'Add New Portfolio Item', '@@text_domain' ),
                    'add_new'            => __( 'Add New', '@@text_domain' ),
                    'edit_item'          => __( 'Edit Portfolio Item', '@@text_domain' ),
                    'update_item'        => __( 'Update Portfolio Item', '@@text_domain' ),
                    'search_items'       => __( 'Search Portfolio Item', '@@text_domain' ),
                    'not_found'          => __( 'Not Found', '@@text_domain' ),
                    'not_found_in_trash' => __( 'Not found in Trash', '@@text_domain' ),
                ),
                'public'             => true,
                'publicly_queryable' => true,
                'has_archive'        => false,
                'show_ui'            => true,

                // adding to custom menu manually.
                'show_in_menu'       => true,
                'show_in_admin_bar'  => true,
                'show_in_rest'       => true,
                'menu_icon'          => 'dashicons-visual-portfolio',
                'taxonomies'         => array(
                    'portfolio_category',
                    'portfolio_tag',
                ),
                'map_meta_cap'       => true,
                'capability_type'    => 'portfolio',
                'rewrite'            => array(
                    'slug'       => $custom_slug,
                    'with_front' => false,
                ),
                'supports'           => array(
                    'title',
                    'editor',
                    'author',
                    'thumbnail',
                    'revisions',
                    'excerpt',
                    'post-formats',
                    'page-attributes',
                ),
            )
        );

        register_taxonomy(
            'portfolio_category',
            'portfolio',
            array(
                'label'              => esc_html__( 'Portfolio Categories', '@@text_domain' ),
                'labels'             => array(
                    'menu_name' => esc_html__( 'Categories', '@@text_domain' ),
                ),
                'rewrite'            => array(
                    'slug' => 'portfolio-category',
                ),
                'hierarchical'       => true,
                'publicly_queryable' => false,
                'show_in_nav_menus'  => true,
                'show_in_rest'       => true,
                'show_admin_column'  => true,
                'map_meta_cap'       => true,
                'capability_type'    => 'portfolio',
            )
        );
        register_taxonomy(
            'portfolio_tag',
            'portfolio',
            array(
                'label'              => esc_html__( 'Portfolio Tags', '@@text_domain' ),
                'labels'             => array(
                    'menu_name' => esc_html__( 'Tags', '@@text_domain' ),
                ),
                'rewrite'            => array(
                    'slug' => 'portfolio-tag',
                ),
                'hierarchical'       => false,
                'publicly_queryable' => false,
                'show_in_nav_menus'  => true,
                'show_in_rest'       => true,
                'show_admin_column'  => true,
                'map_meta_cap'       => true,
                'capability_type'    => 'portfolio',
            )
        );

        // portfolio lists post type.
        register_post_type(
            'vp_lists',
            array(
                'labels'          => array(
                    'name'               => _x( 'Portfolio Layouts', 'Post Type General Name', '@@text_domain' ),
                    'singular_name'      => _x( 'Portfolio Layout', 'Post Type Singular Name', '@@text_domain' ),
                    'menu_name'          => __( 'Visual Portfolio', '@@text_domain' ),
                    'parent_item_colon'  => __( 'Parent Portfolio Item', '@@text_domain' ),
                    'all_items'          => __( 'Portfolio Layouts', '@@text_domain' ),
                    'view_item'          => __( 'View Portfolio Layout', '@@text_domain' ),
                    'add_new_item'       => __( 'Add New Portfolio Layout', '@@text_domain' ),
                    'add_new'            => __( 'Add New', '@@text_domain' ),
                    'edit_item'          => __( 'Edit Portfolio Layout', '@@text_domain' ),
                    'update_item'        => __( 'Update Portfolio Layout', '@@text_domain' ),
                    'search_items'       => __( 'Search Portfolio Layout', '@@text_domain' ),
                    'not_found'          => __( 'Not Found', '@@text_domain' ),
                    'not_found_in_trash' => __( 'Not found in Trash', '@@text_domain' ),
                ),
                'public'          => false,
                'has_archive'     => false,
                'show_ui'         => true,

                // adding to custom menu manually.
                'show_in_menu'    => 'edit.php?post_type=portfolio',
                'show_in_rest'    => true,
                'map_meta_cap'    => true,
                'capability_type' => 'vp_list',
                'rewrite'         => true,
                'supports'        => array(
                    'title',
                    'revisions',
                ),
            )
        );
    }

    /**
     * Add filter by custom taxonomies
     *
     * @param String $post_type - post type name.
     */
    public function filter_custom_post_by_taxonomies( $post_type ) {
        // Apply this only on a specific post type.
        if ( 'portfolio' !== $post_type ) {
            return;
        }

        // A list of taxonomy slugs to filter by.
        $taxonomies = array( 'portfolio_category', 'portfolio_tag' );

        foreach ( $taxonomies as $taxonomy_slug ) {
            // Retrieve taxonomy data.
            $taxonomy_obj  = get_taxonomy( $taxonomy_slug );
            $taxonomy_name = $taxonomy_obj->labels->name;

            // Retrieve taxonomy terms.
            $terms = get_terms( $taxonomy_slug );

            // Display filter HTML.
            echo '<select name="' . esc_attr( $taxonomy_slug ) . '" id="' . esc_attr( $taxonomy_slug ) . '" class="postform">';
            // translators: %s - taxonomy name.
            echo '<option value="">' . sprintf( esc_html__( 'Show All %s', '@@text_domain' ), esc_html( $taxonomy_name ) ) . '</option>';
            foreach ( $terms as $term ) {
                printf(
                    '<option value="%1$s" %2$s>%3$s (%4$s)</option>',
                    esc_attr( $term->slug ),
                    // phpcs:ignore
                    isset( $_GET[ $taxonomy_slug ] ) && $_GET[ $taxonomy_slug ] === $term->slug ? ' selected="selected"' : '',
                    esc_html( $term->name ),
                    esc_html( $term->count )
                );
            }
            echo '</select>';
        }
    }

    /**
     * Add video post format.
     */
    public function add_video_post_format() {
        global $_wp_theme_features;
        $formats = array( 'video' );

        // Add existing formats.
        if ( isset( $_wp_theme_features['post-formats'] ) && isset( $_wp_theme_features['post-formats'][0] ) ) {
            $formats = array_merge( (array) $_wp_theme_features['post-formats'][0], $formats );
        }
        $formats = array_unique( $formats );

        add_theme_support( 'post-formats', $formats );
    }

    /**
     * Add post format metaboxes.
     *
     * @param string $post_type post type.
     */
    public function add_post_format_metaboxes( $post_type ) {
        if ( post_type_supports( $post_type, 'post-formats' ) ) {
            add_meta_box(
                'vp_format_video',
                esc_html__( 'Video', '@@text_domain' ),
                array( $this, 'add_video_format_metabox' ),
                null,
                'side',
                'default'
            );
        }
    }

    /**
     * Add Video Format metabox
     *
     * @param object $post The post object.
     */
    public function add_video_format_metabox( $post ) {
        wp_nonce_field( basename( __FILE__ ), 'vp_format_video_nonce' );

        $video_url   = get_post_meta( $post->ID, 'video_url', true );
        $oembed_html = false;

        $wpkses_iframe = array(
            'iframe' => array(
                'src'             => array(),
                'height'          => array(),
                'width'           => array(),
                'frameborder'     => array(),
                'allowfullscreen' => array(),
            ),
        );

        if ( $video_url ) {
            $oembed = visual_portfolio()->get_oembed_data( $video_url );

            if ( $oembed && isset( $oembed['html'] ) ) {
                $oembed_html = $oembed['html'];
            }
        }
        ?>

        <p></p>
        <input class="vp-input" name="video_url" type="url" id="video_url" value="<?php echo esc_attr( $video_url ); ?>" placeholder="<?php echo esc_attr__( 'https://', '@@text_domain' ); ?>">
        <div class="vp-oembed-preview">
            <?php
            if ( $oembed_html ) {
                echo wp_kses( $oembed_html, $wpkses_iframe );
            }
            ?>
        </div>
        <style>
            #vp_format_video {
                display: <?php echo has_post_format( 'video' ) ? 'block' : 'none'; ?>;
            }
        </style>
        <?php
    }

    /**
     * Save Format metabox
     *
     * @param int $post_id The post ID.
     */
    public static function save_post_format_metaboxes( $post_id ) {
        if ( ! isset( $_POST['vp_format_video_nonce'] ) ) {
            return;
        }

        if ( ! wp_verify_nonce( sanitize_key( $_POST['vp_format_video_nonce'] ), basename( __FILE__ ) ) ) {
            return;
        }

        $meta = array(
            'video_url',
        );

        foreach ( $meta as $item ) {
            if ( isset( $_POST[ $item ] ) ) {
                // phpcs:ignore
                if ( is_array( $_POST[ $item ] ) ) {
                    $result = array_map( 'sanitize_text_field', wp_unslash( $_POST[ $item ] ) );
                } else {
                    $result = sanitize_text_field( wp_unslash( $_POST[ $item ] ) );
                }

                update_post_meta( $post_id, $item, $result );
            } else {
                update_post_meta( $post_id, $item, false );
            }
        }
    }

    /**
     * Add Roles
     */
    public function add_role_caps() {
        if ( ! is_blog_installed() ) {
            return;
        }
        if ( get_option( 'visual_portfolio_updated_caps' ) === '@@plugin_version' ) {
            return;
        }

        $wp_roles = wp_roles();

        if ( ! isset( $wp_roles ) || empty( $wp_roles ) || ! $wp_roles ) {
            return;
        }

        $author = $wp_roles->get_role( 'author' );

        $wp_roles->add_role(
            'portfolio_manager',
            __( 'Portfolio Manager', '@@text_domain' ),
            $author->capabilities
        );
        $wp_roles->add_role(
            'portfolio_author',
            __( 'Portfolio Author', '@@text_domain' ),
            $author->capabilities
        );

        $portfolio_cap = array(
            'read_portfolio',
            'read_private_portfolio',
            'read_private_portfolios',
            'edit_portfolio',
            'edit_portfolios',
            'edit_others_portfolios',
            'edit_private_portfolios',
            'edit_published_portfolios',
            'delete_portfolio',
            'delete_portfolios',
            'delete_others_portfolios',
            'delete_private_portfolios',
            'delete_published_portfolios',
            'publish_portfolios',

            // Terms.
            'manage_portfolio_terms',
            'edit_portfolio_terms',
            'delete_portfolio_terms',
            'assign_portfolio_terms',
        );

        $lists_cap = array(
            'read_vp_list',
            'read_private_vp_list',
            'read_private_vp_lists',
            'edit_vp_list',
            'edit_vp_lists',
            'edit_others_vp_lists',
            'edit_private_vp_lists',
            'edit_published_vp_lists',
            'delete_vp_list',
            'delete_vp_lists',
            'delete_others_vp_lists',
            'delete_private_vp_lists',
            'delete_published_vp_lists',
            'publish_vp_lists',
        );

        /**
         * Add capacities
         */
        foreach ( $portfolio_cap as $cap ) {
            $wp_roles->add_cap( 'portfolio_manager', $cap );
            $wp_roles->add_cap( 'portfolio_author', $cap );
            $wp_roles->add_cap( 'administrator', $cap );
            $wp_roles->add_cap( 'editor', $cap );
        }
        foreach ( $lists_cap as $cap ) {
            $wp_roles->add_cap( 'portfolio_manager', $cap );
            $wp_roles->add_cap( 'administrator', $cap );
        }

        update_option( 'visual_portfolio_updated_caps', '@@plugin_version' );
    }

    /**
     * Add blank page for portfolio lists
     *
     * @param string $which position.
     */
    public function maybe_render_blank_state( $which ) {
        global $post_type;

        if ( in_array( $post_type, array( 'vp_lists' ), true ) && 'bottom' === $which ) {
            $counts = (array) wp_count_posts( $post_type );
            unset( $counts['auto-draft'] );
            $count = array_sum( $counts );

            if ( 0 < $count ) {
                return;
            }
            ?>
            <div class="vp-portfolio-list">
                <div class="vp-portfolio-list__icon">
                    <span class="dashicons-visual-portfolio-gray"></span>
                </div>
                <div class="vp-portfolio-list__text">
                    <p><?php echo esc_html__( 'Ready to add your awesome portfolio?', '@@text_domain' ); ?></p>
                    <a class="button button-primary button-hero" href="<?php echo esc_url( admin_url( 'post-new.php?post_type=vp_lists' ) ); ?>"><?php echo esc_html__( 'Create your first portfolio list!', '@@text_domain' ); ?></a>
                </div>
            </div>
            <style type="text/css">
                #posts-filter .wp-list-table,
                #posts-filter .tablenav.top,
                .tablenav.bottom .actions, .wrap .subsubsub,
                .wp-heading-inline + .page-title-action {
                    display: none;
                }
            </style>
            <?php
        }
    }

    /**
     * Remove screen options from vp list page.
     *
     * @param bool   $return  return default value.
     * @param object $screen_object screen object.
     *
     * @return bool
     */
    public function remove_screen_options( $return, $screen_object ) {
        if ( 'vp_lists' === $screen_object->id ) {
            return false;
        }
        return $return;
    }

    /**
     * Add featured image in portfolio list
     *
     * @param array $columns columns of the table.
     *
     * @return array
     */
    public function add_portfolio_img_column( $columns = array() ) {
        $column_meta = array(
            'portfolio_post_thumbs' => esc_html__( 'Thumbnail', '@@text_domain' ),
        );

        // insert after first column.
        $columns = array_slice( $columns, 0, 1, true ) + $column_meta + array_slice( $columns, 1, null, true );

        return $columns;
    }

    /**
     * Add thumb to the column
     *
     * @param bool $column_name column name.
     */
    public function manage_portfolio_img_column( $column_name = false ) {
        if ( 'portfolio_post_thumbs' === $column_name ) {
            echo '<a href="' . esc_url( get_edit_post_link() ) . '" class="vp-portfolio__thumbnail">';
            if ( has_post_thumbnail() ) {
                the_post_thumbnail( 'thumbnail' );
            }
            echo '</a>';
        }
    }

    /**
     * Add shortcode example in vp_lists
     *
     * @param array $columns columns of the table.
     *
     * @return array
     */
    public function add_vp_lists_shortcode_column( $columns = array() ) {
        $column_meta = array(
            'vp_lists_post_shortcode' => esc_html__( 'Shortcode', '@@text_domain' ),
        );

        // insert before last column.
        $columns = array_slice( $columns, 0, count( $columns ) - 1, true ) + $column_meta + array_slice( $columns, count( $columns ) - 1, null, true );

        return $columns;
    }

    /**
     * Add shortcode example in vp_lists column
     *
     * @param bool $column_name column name.
     */
    public function manage_vp_lists_shortcode_column( $column_name = false ) {
        if ( 'vp_lists_post_shortcode' === $column_name ) {
            echo '<code class="vp-onclick-selection">';
            echo '[visual_portfolio id="' . get_the_ID() . '"]';
            echo '</code>';
        }
    }

    /**
     * Add admin dropdown menu with all used Layouts on the current page.
     */
    public function wp_before_admin_bar_render() {
        global $wp_admin_bar;

        if ( ! is_super_admin() || ! is_admin_bar_showing() ) {
            return;
        }

        // add all nodes of all Slider.
        $layouts = Visual_Portfolio_Get::get_all_used_layouts();
        $layouts = array_unique( $layouts );

        if ( ! empty( $layouts ) ) {
            $wp_admin_bar->add_node(
                array(
                    'parent' => false,
                    'id'     => 'visual_portfolio',
                    'title'  => esc_html__( 'Visual Portfolio', '@@text_domain' ),
                    'href'   => admin_url( 'edit.php?post_type=vp_lists' ),
                )
            );

            // get visual-portfolio post types by IDs.
            // Don't use WP_Query on the admin side https://core.trac.wordpress.org/ticket/18408 .
            $vp_query = get_posts(
                array(
                    'post_type'      => 'vp_lists',
                    'posts_per_page' => -1,
                    'showposts'      => -1,
                    'paged'          => -1,
                    'post__in'       => $layouts,
                )
            );
            foreach ( $vp_query as $post ) {
                $wp_admin_bar->add_node(
                    array(
                        'parent' => 'visual_portfolio',
                        'id'     => 'vp_list_' . esc_html( $post->ID ),
                        'title'  => esc_html( $post->post_title ),
                        'href'   => admin_url( 'post.php?post=' . $post->ID ) . '&action=edit',
                    )
                );
            }
        }
    }

    /**
     * Add Admin Page
     */
    public function admin_menu() {
        // Remove Add New submenu item.
        remove_submenu_page( 'edit.php?post_type=portfolio', 'post-new.php?post_type=portfolio' );

        // Reorder Portfolio Layouts submenu item.
        global $submenu;
        foreach ( $submenu as $page => $items ) {
            if ( 'edit.php?post_type=portfolio' === $page ) {
                foreach ( $items as $id => $meta ) {
                    if ( isset( $meta[2] ) && 'edit.php?post_type=vp_lists' === $meta[2] ) {
	                    // phpcs:ignore
                        $submenu[ $page ][6] = $submenu[ $page ][ $id ];
                        unset( $submenu[ $page ][ $id ] );
                        ksort( $submenu[ $page ] );
                        break;
                    }
                }
            }
        }

        // Documentation menu link.
        add_submenu_page(
            'edit.php?post_type=portfolio',
            esc_html__( 'Documentation', '@@text_domain' ),
            esc_html__( 'Documentation', '@@text_domain' ),
            'manage_options',
            'https://visualportfolio.co/documentation/getting-started/'
        );
    }
}

new Visual_Portfolio_Custom_Post_Type();
