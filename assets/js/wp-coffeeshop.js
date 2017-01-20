jQuery( document ).ready( function( $ ) {

    // Default to lat/lon of the Denver Museum of Nature & Science (because why not?)
    var postData = {
        'action': 'my_action',
        'wp_coffee_shop_lat': 39.7439847,
        'wp_coffee_shop_long': -104.9368033
    };

    // Request location
    if ("geolocation" in navigator) {
        navigator.geolocation.getCurrentPosition(function(position) {

            console.log(position);
            postData.wp_coffee_shop_lat = position.coords.latitude;
            postData.wp_coffee_shop_long = position.coords.longitude;

            jQuery.post(ajaxurl, postData, function(response) {

                response = jQuery.parseJSON(response);

                var contentToSet = '';
                if (response.errorMessage) {
                    contentToSet = '<strong>Uh oh</strong> ' + response.errorMessage;
                } else if (!response.locationUrl || !response.locationName){
                    contentToSet = '<strong>Uh oh</strong> Couldn\'t find any coffee shops nearby... better make your own';
                } else {
                    contentToSet = "<strong>It looks like you need some coffee.</strong> " +
                        "How about this nearby <a href='" + response.locationUrl+ "'>" + response.locationName+ "</a>?";
                }
                $('#wpCoffeeShop').html(
                    contentToSet
                );
            });
        });
    } else {
        $('#wpCoffeeShop').html('<strong>Uh oh</strong> Could not find your location to give you all the coffees');
    }

} );