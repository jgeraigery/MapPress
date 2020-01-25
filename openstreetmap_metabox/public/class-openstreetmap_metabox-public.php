<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://wppb.me/
 * @since      1.0.0
 *
 * @package    Openstreetmap_metabox
 * @subpackage Openstreetmap_metabox/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Openstreetmap_metabox
 * @subpackage Openstreetmap_metabox/public
 * @author     sunil <sunil@gmail.com>
 */
class Openstreetmap_metabox_Public {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of the plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version) {

        $this->plugin_name = $plugin_name;
        $this->version = $version;

        add_filter('post_class', array($this, 'set_row_post_class'), 10, 3);
        add_action('after_map', array($this, 'markers_list_json'), 10);
        add_filter('page_template', array($this, 'add_map_template'));
        add_filter('theme_page_templates', array($this, 'wpse_288589_add_template_to_select'), 10, 4);
        add_action('after_map', array($this, 'map_script'), 10);
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Openstreetmap_metabox_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Openstreetmap_metabox_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/openstreetmap_metabox-public.css', array(), $this->version, 'all');
        wp_enqueue_style('leaflet-css', 'https://d19vzq90twjlae.cloudfront.net/leaflet-0.7.3/leaflet.css');
        wp_enqueue_style('magnific-popup-css', 'https://cdnjs.cloudflare.com/ajax/libs/magnific-popup.js/1.1.0/magnific-popup.min.css');
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Openstreetmap_metabox_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Openstreetmap_metabox_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        wp_enqueue_script('leaflet-js', 'https://d19vzq90twjlae.cloudfront.net/leaflet-0.7.3/leaflet.js', array('jquery'), $this->version, false);
        wp_enqueue_script('magnific-popup-js', 'https://cdnjs.cloudflare.com/ajax/libs/magnific-popup.js/1.1.0/jquery.magnific-popup.min.js', array('jquery'), $this->version, false);
//      wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/openstreetmap_metabox-public.js', array('jquery'), $this->version, false);
    }

    public function set_row_post_class($classes, $class, $post_id) {


        $template = get_page_template_slug(get_queried_object_id());

        if ('page-map.php' == $template) {
            $classes[] = ' white-popup mfp-hide'; //add a custom class to highlight this row in the table
        }

        return $classes;
    }

    public function markers_list_json() {

        $template = get_page_template_slug(get_queried_object_id());
        if ('page-map.php' == $template) {
            $args = array(
                'numberposts' => -1,
                'orderby' => 'date',
                'order' => 'DESC',
                'post_type' => 'post',
                'meta_query' => array(
                    array(
                        'key' => 'osm_metabox',
                        'compare' => '!=',
                        'value' => ''
                    )
                ),
                'fields' => 'ids'
            );

            $posts = get_posts($args);

            $markersJson = array();

            if (!empty($posts)) {
                foreach ($posts as $post) {
                    $osm_metabox = get_post_meta($post, 'osm_metabox', true);
                    if ($osm_metabox != '') {
                        $osm_metabox = json_decode($osm_metabox);
                        if (!empty($osm_metabox->markers)) {
                            $post = get_post($post);
                            $id = $post->ID;
                            $img = get_the_post_thumbnail_url($post->ID);
                            if ($img == '') {
                                $img = 'http://placehold.jp/000000/000000/150x150.png';
                            }
                            $link = get_permalink($post->ID);
                            foreach ($osm_metabox->markers as $marker) {
                                $marker->id = $id;
                                $marker->img = $img;
                                $marker->link = $link;

                                $markersJson[] = $marker;
                            }
                        }
                    }
                }

                $markersJson = json_encode($markersJson);
                ?>
                <div id="jasonList" data-markerJson='<?php echo $markersJson; ?>'></div>
                <?php
            }
        }
    }

    public function add_map_template($page_template) {
		
        if (get_page_template_slug() == 'page-map.php') {
             $template = plugin_dir_path(__FILE__) . 'page-map.php';
			 return $template;
        } 
    }

    public function wpse_288589_add_template_to_select($post_templates, $wp_theme, $post, $post_type) {

        // Add custom template named template-custom.php to select dropdown 
        $post_templates['page-map.php'] = __('Map Page tempalte');

        return $post_templates;
    }

    public function map_script() {
        ?>
        <script>
            (function ($) {
	            var minzoonm=0;				 
				if ($(window).width() < 768) {
  					minzoonm=2;	
                }
				if ($(window).width() > 768) {
                 	minzoonm=2;	
                }
				var southWest = L.latLng(-90, -180),
    northEast = L.latLng(90, 180);
var bounds = L.latLngBounds(southWest, northEast);

				
				var $height=$(window).height();
				$('#map').css('height',$height);
				
				
                //  create map object, tell it to live in 'map' div and give initial latitude, longitude, zoom values 
                var map = L.map('map', {scrollWheelZoom: true, animate: false,  minZoom:minzoonm, maxZoom: 15,scrollWheelZoom: false}).setView([43.64701, -79.39425], 3);
				
 /*var map = L.map('map', {
     center: this.center,
     zoom: 14,
     zoomAnimation: false,
     fadeAnimation: false,
     markerZoomAnimation: false,
     zoomAnimationThreshold: false,
     animate: false,
  maxBounds: bounds
   }).setView([43.64701, -79.39425], 3);*/
				 
                // add base map tiles from OpenStreetMap and attribution info to 'map' div
               L.tileLayer('https://api.tiles.mapbox.com/v4/{id}/{z}/{x}/{y}.png?access_token=pk.eyJ1IjoibWFwYm94IiwiYSI6ImNpejY4NXVycTA2emYycXBndHRqcmZ3N3gifQ.rJcFIG214AriISLbB6B5aw', {		
					id: 'mapbox.streets'
                    // attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors',
                }).addTo(map);

				
                var $markers = document.getElementById('jasonList').getAttribute('data-markerJson');

                jQuery.each(JSON.parse($markers), function () {
                  //  console.log(this.link);

                    var marker = L.marker([this.lat, this.lng], {icon: L.icon({
                            iconUrl: this.img,
                            iconSize: [50, 50], // size of the icon
                        }), alt: this.id});

                    marker.on('click', function (event) {
                        var el = event.target.options;
                      
						
						$.magnificPopup.open({
                            items: {
                                src: '<div id="map_post_content"></div>'
                            },
                            type: 'inline',
                            closeOnContentClick: false,
                            showCloseBtn: true,
                            closeOnBgClick: false,
                            callbacks: {
                                beforeOpen: function () {
                                   // console.log('beforeOpen');
                                    $.ajax({
                                        type: "POST",
                                        url: "<?php echo admin_url('admin-ajax.php'); ?>",
                                        data: {
                                            'action': 'ajax_request_load_map_post',
                                            'id': el.alt
                                        },
                                        success: function (html) {
                                          //  console.log('ssuccess');
                                          //  console.log(html);
                                            $('#map_post_content').html(html);
                                        }
                                    });
                                }
                            }
                        }, 0);
						
						/*$.magnificPopup.open({
                            items: {
                                src: el.alt
                            },
                            type: 'ajax',
                            closeOnContentClick: false,
                            showCloseBtn: true,
                            closeOnBgClick: false,
							preloader: true,
							fixedContentPos:true
                        }, 0);*/
						
						
                    });

                    marker.addTo(map);
                });
 
            })(jQuery);
        </script>
        <?php
    }

}


add_action('wp_ajax_nopriv_ajax_request_load_map_post', 'ajax_request_load_map_post');
add_action('wp_ajax_ajax_request_load_map_post', 'ajax_request_load_map_post');

function ajax_request_load_map_post() {
    if (isset($_REQUEST) && $_REQUEST['action'] == 'ajax_request_load_map_post' && $_REQUEST['id'] != '') {
        $id = $_REQUEST['id'];
//    $id = 1;
        $args = array('p' => $id);
        $post_query = new WP_Query($args);
        while ($post_query->have_posts()) : $post_query->the_post();
            ?>
            <main id="site-content">

                <?php
                get_template_part('content', get_post_type());

                // Display related posts
                get_template_part('parts/related-posts');
                ?>

            </main>
            <button title="Close (Esc)" type="button" class="mfp-close">×</button>
            <?php
        endwhile;
        exit();
    }
}
