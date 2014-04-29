<?php
// Exit if accessed directly
if ( !defined ( 'ABSPATH' ) )
    exit;

class WP_Tiles_GridTemplates
{
    const POST_TYPE = 'grid_template';

    private $_default_template = "AA.B\nAA.B\n.CC.";

    public static function get_instance() {
        static $instance = false;

        if ( !$instance ) {
            $class = get_called_class();
            $instance = new $class();
        }

        return $instance;
    }

    protected function __construct() {
        add_action( 'init', array( &$this, 'register_post_type' ) );
        add_action( 'add_meta_boxes_' . self::POST_TYPE, array( &$this, 'setup_admin_page' ) );
        add_action( 'save_post_' . self::POST_TYPE, array( &$this, 'maybe_save_post' ) );
    }

    public function register_post_type() {
        register_post_type( self::POST_TYPE, apply_filters( 'wp_tiles/grid_template_post_type', array(
            'label'              => 'Grids',
            'public'             => false,
            'publicly_queryable' => false,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => false,
            'rewrite'            => false,
            'capability_type'    => 'post',
            'has_archive'        => false,
            'hierarchical'       => false,
            'menu_position'      => null,
            'supports'           => array( 'title' )
        ) ) );
    }

    public function maybe_save_post( $post_id ) {
        static $saving = false;

        if ( $saving )
            return;

        if ( !isset( $_POST['save_grid_template'] ) || !isset( $_POST['grid_template'] ) )
            return;

        if ( ! wp_verify_nonce( $_POST['save_grid_template'], 'save_grid_template' ) )
            return;

        // @todo Do we want to autosave?
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        if ( !current_user_can( 'edit_post', $post_id ) )
            return;

        // You still here?
        $saving = true;

        $this->_save_template( $post_id );

        $saving = false;
    }

        private function _save_template( $post_id ) {
            $grid_template = wp_kses_post( $_POST['grid_template'] );
            wp_update_post( array(
                'ID'           => $post_id,
                'post_content' => $grid_template
            ) );
        }

    public function setup_admin_page() {
        //add_action( 'edit_form_after_title', array( &$this, 'render_template_editor' ) );
        add_meta_box( 'wp_tiles_template_preview', __( 'Preview', 'wp-tiles' ), array( &$this, 'render_template_preview' ), null, 'normal' );
        add_meta_box( 'wp_tiles_template_editor', __( 'Grid Editor', 'wp-tiles' ), array( &$this, 'render_template_editor' ), null, 'side' );

        $screen = get_current_screen();
        $screen->add_help_tab( array(
           'id'       => 'grid_editor',            //unique id for the tab
           'title'    => "Grid Editor",      //unique visible title for the tab
           'content'  =>  "<h4>" . __( "Grid Editor" ) . "</h4>"
                        . "<p>" . __( "Use the grid editor below to create a tiles template. Play around with it, the example will be automatically updated." ) . "</p>"
                        . "<p>" . __( "Here's how it works: A template is an array of strings where each string represents a row in the grid. We parse that array and form a 'tile' whenever the we detect repeated characters in adjacent cells.") . "</p>"
                        . "<p>" . __("So, in the example to the left the A's form a 2x2 tile, the B's for a 1x2 tile and the C's form a 2x1 tile. You can use just about any character you want but we tend to use capital letters as a convention. ") . "</p>"
                        . "<p>" . __("Periods are special. they're reserved to always mean single cells. All white space is ignored (it turns out that these are a lot easier to read if you pad the characters a little).") . "</p>",
           //'callback' => $callback //optional function to callback
        ) );

        // @todo Register script in main tiles class
        wp_enqueue_script( 'tilesjs',  WP_TILES_ASSETS_URL . 'js/tiles.js', array( "jquery" ),  "2013-05-18",    true );
        wp_enqueue_style( 'wp-tiles', WP_TILES_ASSETS_URL . '/css/wp-tiles.css', false, WP_TILES_VERSION );

        wp_enqueue_script( 'jquery-autosize',  WP_TILES_ASSETS_URL . 'js/jquery.autosize.js', array( "jquery" ),  "1.16.17", true );
        wp_enqueue_script( 'wp_tiles_grid_templates', WP_TILES_ASSETS_URL . 'js/admin-grid-templates.js', array( 'jquery', 'tilesjs', 'jquery-autosize' ), WP_TILES_VERSION, true );

    }

    public function render_template_editor( $post ) {
        $template = $post->post_content ? $post->post_content : $this->_default_template;

        wp_nonce_field( 'save_grid_template', 'save_grid_template' );
        echo "<textarea name='grid_template' id='grid_template' class='grid-template-editor' spellcheck='false'>" . $template . "</textarea>";
        echo "<p><a href='#contextual-help-wrap' class='wp-tiles-show-help'>Need help?</a></p>";
    }

    public function render_template_preview() {
        echo "<div class='wp-tiles-container'><div id='grid-template-demo' class='wp-tiles-grid'></div></div>";
    }
}