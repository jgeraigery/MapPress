(function ($) {
    'use strict';

    /**
     * All of the code for your admin-facing JavaScript source
     * should reside in this file.
     *
     * Note: It has been assumed you will write jQuery code here, so the
     * $ function reference has been prepared for usage within the scope
     * of this function.
     *
     * This enables you to define handlers, for when the DOM is ready:
     *
     * $(function() {
     *
     * });
     *
     * When the window is loaded:
     *
     * $( window ).load(function() {
     *
     * });
     *
     * ...and/or other possibilities.
     *
     * Ideally, it is not considered best practise to attach more than a
     * single DOM-ready or window-load handler for a particular page.
     * Although scripts in the WordPress core, Plugins and Themes may be
     * practising this, we should strive to set a better example in our own work.
     */

    $(document).on('keyup', '.leaflet-control-geocoder input', function (event) {

        event.preventDefault();
        var s = $(this).val();
        console.log(s);
        if (s.length > 3)
        {
            $(this).parent().parent().removeClass('blank');
            $('.leaflet-control-geocoder input').trigger('click');
        } else
        {
            console.log('hide');
            $(this).parent().parent().addClass('blank');
        }
    });

    $(document).on('click', '.osm-above .leaflet-control-geocoder-icon', function (event) {

        console.log('clicked');

        $(".osm-above").toggleClass("active");
    });



})(jQuery);
