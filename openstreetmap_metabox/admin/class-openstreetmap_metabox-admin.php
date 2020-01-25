<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://wppb.me/
 * @since      1.0.0
 *
 * @package    Openstreetmap_metabox
 * @subpackage Openstreetmap_metabox/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Openstreetmap_metabox
 * @subpackage Openstreetmap_metabox/admin
 * @author     sunil <sunil@gmail.com>
 */
class Openstreetmap_metabox_Admin {

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
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version) {

        $this->plugin_name = $plugin_name;
        $this->version = $version;
        add_action('admin_footer', array($this, 'opestreetmap_marker_script'));

        add_action('add_meta_boxes', array($this, 'open_street_map_metabox'));

        add_action('save_post', array($this, 'open_street_map_metabox_save_meta'));
    }

    /**
     * Add marker html script
     *
     * @since    1.0.0
     */
    public function opestreetmap_marker_script() {
        ?>
        <script type="text/html" id="tmpl-osm-marker-input">
            <div class="locate">
                <a class="dashicons dashicons-location" data-name="locate-marker"><span class="screen-reader-text">Locate Marker</span></a>
            </div>
            <div class="input">
                <input type="text" data-name="label" />
            </div>
            <div class="tools">
                <a class="dashicons dashicons-minus small light acf-js-tooltip" href="#" data-name="remove-marker" title="Remove Marker"></a>
            </div>
        </script> 
        <?php
    }

    /**
     * Register the stylesheets for the admin area.
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
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/openstreetmap_metabox-admin.css', array(), $this->version, 'all');
    }

    /**
     * Register the JavaScript for the admin area.
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
        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/openstreetmap_metabox-admin.js', array('jquery'), $this->version, false);

        wp_enqueue_script('osm_input_field', plugin_dir_url(__FILE__) . 'js/base-input.min.js', array('jquery'), $this->version, false);


        /**
         * 	Marker Icon HTML. Return false to use image icon (either leaflet default or return value of filter ``)
         *
         * 	@param $marker_icon_html string Additional Icon HTML.
         */
        $marker_html = apply_filters('osm_marker_html', false);

        if ($marker_html !== false) {
            $marker_html = wp_kses_post($marker_html);
        }

        wp_register_script('osm-frontend', $this->get_asset_url('js/osm-frontend.min.js'), array('jquery'), $this->version, true);

        wp_localize_script('osm-frontend', 'osm', array(
            'options' => array(
                'layer_config' => get_option('osm_provider_tokens', array()),
                'marker' => array(
                    'html' => $marker_html,
                    /**
                     * 	HTML Marker Icon css class
                     *
                     * 	@param $classname string Class name for HTML icon. Default 'osm-marker-icon'
                     */
                    'className' => sanitize_html_class(apply_filters('osm_marker_classname', 'osm-marker-icon')),
                    /**
                     * 	Return leaflet icon options.
                     *
                     * 	@see https://leafletjs.com/reference-1.3.2.html#icon
                     *
                     * 	@param $icon_options false (leaflet default icon or HTML icon) or array(
                     * 		'iconUrl'			=> image URL
                     * 		'iconRetinaUrl'		=> image URL
                     * 		'iconSize'			=> array( int, int )
                     * 		'iconAnchor'		=> array( int, int )
                     * 		'popupAnchor'		=> array( int, int )
                     * 		'tooltipAnchor'		=> array( int, int )
                     * 		'shadowUrl'			=> image URL
                     * 		'shadowRetinaUrl'	=> image URL
                     * 		'shadowSize'		=> array( int, int )
                     * 		'shadowAnchor'		=> array( int, int )
                     * 		'className'			=> string
                     * 	)
                     */
                    'icon' => apply_filters('osm_marker_icon', false),
                ),
            ),
            'providers' => $this->get_leaflet_providers(),
        ));

        // field js
        wp_register_script('input-osm', $this->get_asset_url('js/input-osm.min.js'), array('wp-backbone'), $this->version, true);
        wp_localize_script('input-osm', 'osm_admin', array(
            'options' => array(
                'osm_layers' => $this->get_osm_layers(),
                'leaflet_layers' => $this->get_leaflet_providers(),
                'accuracy' => 7,
            ),
            'i18n' => array(
                'search' => __('Search...', 'openstreetmap_metabox'),
                'nothing_found' => __('Nothing found...', 'openstreetmap_metabox'),
                'my_location' => __('My location', 'openstreetmap_metabox'),
                'add_marker_at_location'
                => __('Add Marker at location', 'openstreetmap_metabox'),
                'address_format' => array(
                    /* translators: address format for marker labels (street level). Available placeholders {building} {road} {house_number} {postcode} {city} {town} {village} {hamlet} {state} {country} */
                    'street' => __('{building} {road} {house_number}', 'openstreetmap_metabox'),
                    /* translators: address format for marker labels (city level). Available placeholders {building} {road} {house_number} {postcode} {city} {town} {village} {hamlet} {state} {country} */
                    'city' => __('{city} ', 'openstreetmap_metabox'),
                    /* translators: address format for marker labels (country level). Available placeholders {building} {road} {house_number} {postcode} {city} {town} {village} {hamlet} {state} {country} */
                    'country' => __('{state} {country}', 'openstreetmap_metabox'),
                )
            ),
        ));

        wp_enqueue_media();

        wp_enqueue_script('input-osm');

        wp_enqueue_script('osm-frontend');
    }

    /**
     * 	get default OpenStreetMap Layers
     *
     * 	@return array(
     * 		'layer_id' => 'Layer Label',
     * 		...
     * 	)
     */
    public function get_osm_layers($context = 'iframe') {
        /*
          mapnik
          cyclemap C 	Cycle
          transportmap T	Transport
          hot H	Humantarian
         */
        if ('iframe' === $context) {
            return array(
                'mapnik' => 'OpenStreetMap',
                'cyclemap' => 'Thunderforest.OpenCycleMap',
                'transportmap' => 'Thunderforest.Transport',
                'hot' => 'OpenStreetMap.HOT',
            );
        } else if ('link' === $context) {
            return array(
                'H' => 'OpenStreetMap.HOT',
                'T' => 'Thunderforest.Transport',
                'C' => 'Thunderforest.OpenCycleMap',
            );
        }
    }

    /**
     * 	returns leaflet providers with configured access tokens
     * 	@return array
     */
    public function get_leaflet_providers() {

        $leaflet_providers = json_decode(file_get_contents(OPENSTREETMAP_METABOX_DIR . 'admin/js/leaflet-providers.json'), true);

        return $leaflet_providers;
    }

    /**
     * 	Get asset url for this plugin
     *
     * 	@param	string	$asset	URL part relative to plugin class
     * 	@return wp_enqueue_editor
     */
    public function get_asset_url($asset) {
        return plugin_dir_url(__FILE__) . $asset;
    }

    public function open_street_map_metabox() {
        add_meta_box(
                'open_street_map_metabox', __('Map', 'opensteetmap_metabox'), array($this, 'open_street_map_metabox_callback'), 'post', 'side', 'default'
        );
    } 

    public function open_street_map_metabox_callback($post) {

        wp_nonce_field('open_street_map_metabox_nonce', 'open_street_map_nonce');

        $osm_metabox = get_post_meta($post->ID, 'osm_metabox', true);

        if ($osm_metabox == '') {
            $osm_metabox = '{"lat":53.5629478,"lng":9.9561024,"zoom":13,"markers":[],"address":"","layers":["OpenStreetMap"]}';
        }
        ?>
        <div class="field-open-street-map" data-name="map" data-type="open_street_map" data-key="">
            <div class="">
                <input type="hidden" id="osm_metabox" name="osm_metabox" value='<?php echo $osm_metabox; ?>' class="osm-json">
                <div data-editor-config="{&quot;allow_providers&quot;:1,&quot;restrict_providers&quot;:[&quot;OpenStreetMap&quot;,&quot;OpenStreetMap.DE&quot;,&quot;OpenStreetMap.France&quot;,&quot;OpenStreetMap.HOT&quot;,&quot;OpenSeaMap&quot;,&quot;OpenPtMap&quot;,&quot;OpenTopoMap&quot;,&quot;OpenRailwayMap&quot;,&quot;OpenFireMap&quot;,&quot;SafeCast&quot;,&quot;Thunderforest.OpenCycleMap&quot;,&quot;Thunderforest.Transport&quot;,&quot;Thunderforest.TransportDark&quot;,&quot;Thunderforest.SpinalMap&quot;,&quot;Thunderforest.Landscape&quot;,&quot;Thunderforest.Outdoors&quot;,&quot;Thunderforest.Pioneer&quot;,&quot;Thunderforest.MobileAtlas&quot;,&quot;Thunderforest.Neighbourhood&quot;,&quot;OpenMapSurfer.Roads&quot;,&quot;OpenMapSurfer.Hybrid&quot;,&quot;OpenMapSurfer.AdminBounds&quot;,&quot;OpenMapSurfer.ContourLines&quot;,&quot;OpenMapSurfer.Hillshade&quot;,&quot;OpenMapSurfer.ElementsAtRisk&quot;,&quot;Hydda.Full&quot;,&quot;Hydda.Base&quot;,&quot;Hydda.RoadsAndLabels&quot;,&quot;MapBox.Streets&quot;,&quot;MapBox.Light&quot;,&quot;MapBox.Dark&quot;,&quot;MapBox.Satellite&quot;,&quot;MapBox.Streets-Satellite&quot;,&quot;MapBox.Wheatpaste&quot;,&quot;MapBox.Streets-Basic&quot;,&quot;MapBox.Comic&quot;,&quot;MapBox.Outdoors&quot;,&quot;MapBox.Run-Bike-Hike&quot;,&quot;MapBox.Pencil&quot;,&quot;MapBox.Pirates&quot;,&quot;MapBox.Emerald&quot;,&quot;MapBox.High-Contrast&quot;,&quot;Stamen.Toner&quot;,&quot;Stamen.TonerBackground&quot;,&quot;Stamen.TonerHybrid&quot;,&quot;Stamen.TonerLines&quot;,&quot;Stamen.TonerLabels&quot;,&quot;Stamen.TonerLite&quot;,&quot;Stamen.Watercolor&quot;,&quot;Stamen.Terrain&quot;,&quot;Stamen.TerrainBackground&quot;,&quot;Stamen.TerrainLabels&quot;,&quot;TomTom.Basic&quot;,&quot;TomTom.Hybrid&quot;,&quot;TomTom.Labels&quot;,&quot;Esri.WorldStreetMap&quot;,&quot;Esri.DeLorme&quot;,&quot;Esri.WorldTopoMap&quot;,&quot;Esri.WorldImagery&quot;,&quot;Esri.WorldTerrain&quot;,&quot;Esri.WorldShadedRelief&quot;,&quot;Esri.WorldPhysical&quot;,&quot;Esri.OceanBasemap&quot;,&quot;Esri.NatGeoWorldMap&quot;,&quot;Esri.WorldGrayCanvas&quot;,&quot;OpenWeatherMap.Clouds&quot;,&quot;OpenWeatherMap.CloudsClassic&quot;,&quot;OpenWeatherMap.Precipitation&quot;,&quot;OpenWeatherMap.PrecipitationClassic&quot;,&quot;OpenWeatherMap.Rain&quot;,&quot;OpenWeatherMap.RainClassic&quot;,&quot;OpenWeatherMap.Pressure&quot;,&quot;OpenWeatherMap.PressureContour&quot;,&quot;OpenWeatherMap.Wind&quot;,&quot;OpenWeatherMap.Temperature&quot;,&quot;OpenWeatherMap.Snow&quot;,&quot;HERE.normalDay&quot;,&quot;HERE.normalDayCustom&quot;,&quot;HERE.normalDayGrey&quot;,&quot;HERE.normalDayMobile&quot;,&quot;HERE.normalDayGreyMobile&quot;,&quot;HERE.normalDayTransit&quot;,&quot;HERE.normalDayTransitMobile&quot;,&quot;HERE.normalDayTraffic&quot;,&quot;HERE.normalNight&quot;,&quot;HERE.normalNightMobile&quot;,&quot;HERE.normalNightGrey&quot;,&quot;HERE.normalNightGreyMobile&quot;,&quot;HERE.normalNightTransit&quot;,&quot;HERE.normalNightTransitMobile&quot;,&quot;HERE.reducedDay&quot;,&quot;HERE.reducedNight&quot;,&quot;HERE.basicMap&quot;,&quot;HERE.mapLabels&quot;,&quot;HERE.trafficFlow&quot;,&quot;HERE.carnavDayGrey&quot;,&quot;HERE.hybridDay&quot;,&quot;HERE.hybridDayMobile&quot;,&quot;HERE.hybridDayTransit&quot;,&quot;HERE.hybridDayGrey&quot;,&quot;HERE.hybridDayTraffic&quot;,&quot;HERE.pedestrianDay&quot;,&quot;HERE.pedestrianNight&quot;,&quot;HERE.satelliteDay&quot;,&quot;HERE.terrainDay&quot;,&quot;HERE.terrainDayMobile&quot;,&quot;MtbMap&quot;,&quot;CartoDB.Positron&quot;,&quot;CartoDB.PositronNoLabels&quot;,&quot;CartoDB.PositronOnlyLabels&quot;,&quot;CartoDB.DarkMatter&quot;,&quot;CartoDB.DarkMatterNoLabels&quot;,&quot;CartoDB.DarkMatterOnlyLabels&quot;,&quot;CartoDB.Voyager&quot;,&quot;CartoDB.VoyagerNoLabels&quot;,&quot;CartoDB.VoyagerOnlyLabels&quot;,&quot;CartoDB.VoyagerLabelsUnder&quot;,&quot;HikeBike&quot;,&quot;HikeBike.HillShading&quot;,&quot;Wikimedia&quot;],&quot;max_markers&quot;:false,&quot;name_prefix&quot;:&quot;osm_field&quot;}" data-map-lat="40.712776" data-map-lng="-74.005974" data-map-zoom="11" class="leaflet-map" data-height="400" data-map="leaflet" data-map-layers="[&quot;OpenStreetMap&quot;]" data-map-markers="">
                </div>						
                <div class="markers-instruction">
                    <p class="description">
                        <span class="add-marker-instructions marker-on-dblclick can-add-marker">
                            Double click to add Marker.						</span>
                        <span class="add-marker-instructions marker-on-taphold can-add-marker">
                            Tap and hold to add Marker.						</span>
                        <span class="has-markers">Drag Marker to move.</span>
                    </p>
                </div>
                <div class="osm-markers">
                </div>
            </div>
        </div> 

        <?php
    }

    public function open_street_map_metabox_save_meta($post_id) {

        if (!isset($_POST['open_street_map_nonce']) || !wp_verify_nonce($_POST['open_street_map_nonce'], 'open_street_map_metabox_nonce'))
            return;

        if (!current_user_can('edit_post', $post_id))
            return;

        if (isset($_POST['osm_metabox'])) {
            update_post_meta($post_id, 'osm_metabox', sanitize_text_field($_POST['osm_metabox']));
        }
    }

}
