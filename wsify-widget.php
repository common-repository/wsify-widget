<?php
/*
  Plugin Name: WSIFY Widget
  Plugin URI: https://www.wsify.com/
  Description: Plugin for Whatsapp chat for users to give support to their customers on the fly.
  Author: WSIFY - Sales can fly
  Version: 1.0
  Author URI: https://www.wsify.com/
*/

function wsifywidget_admin_actions() {
	// add_options_page("WSIFY Whatsapp Chat", "WSIFY Whatsapp Chat", "manage_options", "wsify_whatsapp_chat", "wsifychat_admin");
  add_menu_page( 'WSIFY Widget', 'WSIFY Widget', 'manage_options', 'wsify-widget/widget-admin.php', '', plugins_url( 'wsify-widget/images/widget_logo.png' ), 82.7 );
}

add_action('admin_menu', 'wsifywidget_admin_actions');

function wsifywidget_enqueue_script(){
    wp_enqueue_script('jquery');
}

add_action('wp_enqueue_scripts', 'wsifywidget_enqueue_script');

function show_wsify_widget() {
  $current_website_admin_id = get_option("wsify_site_admin_id");

  $curr_user_account_secret = get_user_meta($current_website_admin_id, "user_wsify_account_secret", true);

  if($curr_user_account_secret != "" && !is_admin()){
    ?>
    <script type="text/javascript" src="https://maps.google.com/maps/api/js"></script>
    <script type="text/javascript" src="<?php echo plugins_url( 'wsify-widget/js/geometa.js' ); ?>"></script>
    <script type="text/javascript">
      var map;

      var userLocationCountry = "userLocationCountry";
      var userLocationCountryFullName = "userLocationCountryFullName";
      var userLocationCity = "userLocationCity";

      function initialise() {
          var latlng = new google.maps.LatLng(-25.363882,131.044922);
          var myOptions = {
            zoom: 4,
            center: latlng,
            mapTypeId: google.maps.MapTypeId.TERRAIN,
            disableDefaultUI: true
          }
          map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);
          prepareGeolocation();
          doGeolocation();
      }

      function doGeolocation() {
        if (navigator.geolocation) {
          navigator.geolocation.getCurrentPosition(positionSuccess, positionError);
        } else {
          positionError(-1);
        }
      }

      function positionError(err) {
        var msg;
        switch(err.code) {
          case err.UNKNOWN_ERROR:
            msg = "Unable to find your location";
            break;
          case err.PERMISSION_DENINED:
            msg = "Permission denied in finding your location";
            break;
          case err.POSITION_UNAVAILABLE:
            msg = "Your location is currently unknown";
            break;
          case err.BREAK:
            msg = "Attempt to find location took too long";
            break;
          default:
            msg = "Location detection not supported in browser";
        }
        document.getElementById('info').innerHTML = msg;
      }

      function positionSuccess(position) {
        // Centre the map on the new location
        var coords = position.coords || position.coordinate || position;
        var latLng = new google.maps.LatLng(coords.latitude, coords.longitude);
        map.setCenter(latLng);
        map.setZoom(12);
        var marker = new google.maps.Marker({
    	    map: map,
    	    position: latLng,
    	    title: 'Why, there you are!'
        });
        document.getElementById('info').innerHTML = 'Looking for <b>' +
            coords.latitude + ', ' + coords.longitude + '</b>...';

        // And reverse geocode.
        (new google.maps.Geocoder()).geocode({latLng: latLng, region:"uk"}, function(resp) {
    		  var place = "You're around here somewhere!";
    		  if (resp[0]) {
    			  var bits = [];
    			  for (var i = 0, I = resp[0].address_components.length; i < I; ++i) {
    				  var component = resp[0].address_components[i];
    				  if (contains(component.types, 'political')) {
    					  bits.push('<b>' + component.long_name + '</b>');
    					}

              if (contains(component.types, 'country')) {
                var country_name_user = getCountryName(component.short_name);
                document.getElementById('country').value = country_name_user;

                document.getElementById('country_code').value = getCountryDialCode(country_name_user);
              }
    				}
    				if (bits.length) {
    					place = bits.join(' > ');
    				}
    				marker.setTitle(resp[0].formatted_address);
    			}
    			document.getElementById('info').innerHTML = place;

          // location.reload();
          // location.reload();
    	  });
      }

      function contains(array, item) {
    	  for (var i = 0, I = array.length; i < I; ++i) {
    		  if (array[i] == item) return true;
    		}
    		return false;
    	}
    </script>
    <style>
    .wsify-widget-chat {
      position: fixed;
      bottom: 2em;
      right: 5em;
      font-size: 12px;
      width: 48px;
      height: 48px;
      z-index: 1;
      text-decoration: none;
    }

    .wsify-widget-chat:hover {
      text-decoration: none;
    }

    #intercom-container {
      visibility: hidden;
    }

    .wsify-chat-sidebar {
      z-index: 2147483000;
      visibility: hidden;
      position: fixed;
      height: 355px;
      width: 368px;
      bottom: 0;
      right: 0;
    }

    .wsify-chat-sidebar-header {
      z-index: 2147483002;
      box-shadow: 0 1px 2px 0 rgba(0,0,0,.12);
      background: #fff;
      overflow: hidden;
      position: absolute;
      top: 0;
      right: 0;
      width: 100%;
      height: 48px;
    }

    .wsify-chat-sidebar-body {
      z-index: 2147483000;
      background: #fafafb;
      background: rgba(250,250,251,.98);
      border-left: 1px solid #dadee2;
      box-shadow: 0 0 4px 1px rgba(0,0,0,.08);
      position: absolute;
      top: 0;
      right: 0;
      bottom: 0;
      width: 100%;
    }

    #intercom-container .intercom-composer {
        z-index: 2147483001;
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        max-width: 336px;
        margin: 0 auto;
        padding: 0px;
        padding-bottom: 16px;
    }

    #intercom-container .intercom-composer-textarea-container {
        min-height: 32px;
    }

    #intercom-container .intercom-composer-textarea {
        position: relative;
        overflow: hidden;
        border-radius: 4px;
        border: none;
    }

    #intercom-container .intercom-composer-textarea pre, #intercom-container .intercom-composer-textarea input {
        box-sizing: border-box;
        font-family: Helvetica Neue,Helvetica,Arial,sans-serif;
        font-size: 14px;
        line-height: 20px;
        min-height: 40px;
        max-height: 200px;
        width: 100%;
        height: 100%;
        padding: 5px 70px 5px 14px;
        border-radius: 4px;
        margin-bottom: 0em;
    }

    /*#intercom-container .intercom-composer-textarea textarea {
        background: #fff;
        position: absolute;
        top: 0;
        left: 0;
        font-weight: 400;
        color: #455a64;
        resize: none;
        border: none;
    }*/

    #intercom-container .intercom-composer-textarea pre>span, #intercom-container .intercom-composer-textarea input {
        white-space: pre;
        white-space: pre-wrap;
        word-wrap: break-word;
    }

    #intercom-container .intercom-sheet-header-generic-title, #intercom-container .intercom-sheet-header-title {
      font-family: Helvetica Neue,Helvetica,Arial,sans-serif;
      font-size: 15px;
      line-height: 48px;
      font-weight: 500;
      color: #465c66;
      letter-spacing: .2px;
      display: inline-block;
      max-width: 100%;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      width: 100%;
      text-align: center;
    }

    #intercom-container .intercom-sheet-header-close-button {
        float: right;
        margin-left: 15px;
    }

    #intercom-container .intercom-sheet-header-button {
      z-index: 2147483001;
      position: absolute;
      margin: 0 20px;
      height: 48px;
      right: 0;
      top: 0;
    }

    #intercom-container .intercom-sheet-header-close-button .intercom-sheet-header-button-icon {
        background-image: url("<?php echo plugins_url( 'wsify-widget/images/cross.png' ); ?>");
        background-size: 24px 24px;
        background-repeat: no-repeat;
        width: 24px;
        opacity: 1;
    }

    #intercom-container .intercom-sheet-header-button-icon {
        height: 100%;
        background-position: center center;
    }

    #intercom-container .intercom-sheet-content {
        z-index: 2147483001;
        position: absolute;
        top: 48px;
        right: 0;
        bottom: 0;
        width: 100%;
        -webkit-transform: translateZ(0);
    }

    #intercom-container .intercom-sheet-active .intercom-sheet-content {
        overflow-y: auto;
    }

    #intercom-container .intercom-sheet-content-container {
        box-sizing: border-box;
        position: relative;
        min-height: 100%;
        max-width: 620px;
        margin: 0 auto;
    }

    #intercom-container .intercom-app-profile-container {
        padding: 70px 16px 0;
    }

    #intercom-container .intercom-app-profile {
        padding: 30px 12px 30px;
        background-color: #fff;
        overflow: hidden;
        box-shadow: 0 0 3px rgba(0,0,0,.2);
        border-radius: 5px;
    }

    #intercom-container .intercom-active-admins {
        text-align: center;
        color: #364850;
        padding-top: 24px;
    }

    #intercom-container .intercom-app-profile-text, #intercom-container .intercom-app-profile-text .intercom-comment-body {
        font-size: 14px;
        font-weight: 400;
        line-height: 20px;
    }

    #intercom-container .intercom-app-profile-text {
        padding: 0px 20px 0;
        text-align: center;
        color: #455a64;
        font-size: 13px;
        color: #78909c;
        line-height: 19px;
    }

    #intercom-container .intercom-app-profile-team {
        text-align: center;
        color: #455a64;
        font-weight: 500;
        font-size: 15px;
        line-height: 1.8;
    }

    #intercom-container .intercom-active-admins {
        text-align: center;
        color: #364850;
        padding-top: 24px;
    }

    #intercom-container .intercom-active-admin {
        display: inline-block;
    }

    #intercom-container .intercom-admin-avatar, #intercom-container .intercom-admin-avatar img {
        margin: 0 auto;
        border-radius: 50%;
    }

    #intercom-container .intercom-admin-avatar {
        overflow: hidden;
        text-align: center;
        background-color: #fff;
    }

    #intercom-container .intercom-admin-avatar, #intercom-container .intercom-admin-avatar img {
        width: 48px;
        height: 48px;
    }

    #intercom-container .intercom-admin-avatar, #intercom-container .intercom-admin-avatar img {
        width: 48px;
        height: 48px;
    }

    #intercom-container .intercom-admin-avatar, #intercom-container .intercom-admin-avatar img {
        margin: 0 auto;
        border-radius: 50%;
    }

    #intercom-container .intercom-active-admin-name {
        font-size: 12px;
        color: #90a4ae;
        text-align: center;
        padding-top: 7px;
        width: 80px;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    #success_message .intercom-app-profile {
      background-color: green;
    }

    #success_message .intercom-app-profile .intercom-app-profile-text {
      color: white;
    }

    #failure_message .intercom-app-profile {
      background-color: red;
    }

    #failure_message .intercom-app-profile .intercom-app-profile-text {
      color: white;
    }
    </style>

    <div id="map_canvas" class="hidden"></div>
    <div id="info" class="lightbox hidden">Detecting your location...</div>

    <a href="#" class="wsify-widget-chat"><img src="<?php echo plugins_url( 'wsify-widget/images/whatsapp.png' ); ?>" width="48" height="48" /></a>
    <div id="intercom-container" class="intercom-container intercom-reset intercom-acquire">
      <div class="wsify-chat-sidebar">
        <div class="wsify-chat-sidebar-header">
          <div class="intercom-sheet-header-generic-title">
            <img src="<?php echo plugins_url( 'wsify-widget/images/whatsapp.png' ); ?>" width="48" height="48">&nbsp;WhatsApp Us
          </div>
          <a id="wsify-close-sidebar" class="intercom-sheet-header-button intercom-sheet-header-close-button" href="#">
            <div class="intercom-sheet-header-button-icon"></div>
          </a>
        </div>
        <div class="wsify-chat-sidebar-body">

        </div>
        <div class="intercom-sheet-content" style="bottom: 74px;">
          <div class="intercom-sheet-content-container">
            <div id="success_message" class="intercom-app-profile-container" style="display: none;"><div class="intercom-app-profile">
              <div class="intercom-app-profile-text">
                <p style="font-size: 1em;">We got your message, You will get reply to your WhatsApp soon.</p>
              </div>
              </div>
            </div>
            <div id="failure_message" class="intercom-app-profile-container" style="display: none;"><div class="intercom-app-profile">
              <div class="intercom-app-profile-text">
                <p style="font-size: 1em;">There is some problem sending your message. Try again later.</p>
              </div>
              </div>
            </div>
            <div class="intercom-conversation-parts-container">
              <div class="intercom-conversation-parts"></div>
            </div>
          </div>
        </div>
        <div class="intercom-composer-container">
          <form action="" method="POST" id="intercom-composer" class="intercom-composer">
          <div class="intercom-composer-textarea-container">
            <div class="intercom-composer-textarea">
              <div id="step_one_function">
                <input type="text" name="conv_message" id="conv_message" placeholder="Type your Message Here…" style="margin-bottom: 8px; height: 90px;" />
                <br />
                <select class="form-control" name="country" id="country" style="height: 42px;">
                  <option>Please select country</option>
                  <option value="United States" <?php echo ($return_object != "" && $return_object->country == "United States") ? 'selected="selected"' : ''; ?>>United States</option>
                  <option value="United Kingdom" <?php echo ($return_object != "" && $return_object->country == "United Kingdom") ? 'selected="selected"' : ''; ?>>United Kingdom</option>
                  <option value="Canada" <?php echo ($return_object != "" && $return_object->country == "Canada") ? 'selected="selected"' : ''; ?>>Canada</option>
                  <option value="Australia" <?php echo ($return_object != "" && $return_object->country ==  "Australia") ? 'selected="selected"' : ''; ?>>Australia</option>
                  <option value="Singapore" <?php echo ($return_object != "" && $return_object->country ==  "Singapore") ? 'selected="selected"' : ''; ?>>Singapore</option>
                  <option value="Ireland" <?php echo ($return_object != "" && $return_object->country ==  "Ireland") ? 'selected="selected"' : ''; ?>>Ireland</option>
                  <option value="New Zealand" <?php echo ($return_object != "" && $return_object->country ==  "New Zealand") ? 'selected="selected"' : ''; ?>>New Zealand</option>
                  <option value="Philippines" <?php echo ($return_object != "" && $return_object->country ==  "Philippines") ? 'selected="selected"' : ''; ?>>Philippines</option>
                  <option value="Sweden" <?php echo ($return_object != "" && $return_object->country ==  "Sweden") ? 'selected="selected"' : ''; ?>>Sweden</option>
                  <option value="Finland" <?php echo ($return_object != "" && $return_object->country ==  "Finland") ? 'selected="selected"' : ''; ?>>Finland</option>
                  <option value="Afghanistan" <?php echo ($return_object != "" && $return_object->country ==  "Afghanistan") ? 'selected="selected"' : ''; ?>>Afghanistan</option>
                  <option value="Albania" <?php echo ($return_object != "" && $return_object->country ==  "Albania") ? 'selected="selected"' : ''; ?>>Albania</option>
                  <option value="Algeria" <?php echo ($return_object != "" && $return_object->country ==  "Algeria") ? 'selected="selected"' : ''; ?>>Algeria</option>
                  <option value="Andorra" <?php echo ($return_object != "" && $return_object->country ==  "Andorra") ? 'selected="selected"' : ''; ?>>Andorra</option>
                  <option value="Angola" <?php echo ($return_object != "" && $return_object->country ==  "Angola") ? 'selected="selected"' : ''; ?>>Angola</option>
                  <option value="Anguilla" <?php echo ($return_object != "" && $return_object->country ==  "Anguilla") ? 'selected="selected"' : ''; ?>>Anguilla</option>
                  <option value="Antigua and Barbuda" <?php echo ($return_object != "" && $return_object->country ==  "Antigua and Barbuda") ? 'selected="selected"' : ''; ?>>Antigua and Barbuda</option>
                  <option value="Argentina" <?php echo ($return_object != "" && $return_object->country ==  "Argentina") ? 'selected="selected"' : ''; ?>>Argentina</option>
                  <option value="Armenia" <?php echo ($return_object != "" && $return_object->country ==  "Armenia") ? 'selected="selected"' : ''; ?>>Armenia</option>
                  <option value="Aruba" <?php echo ($return_object != "" && $return_object->country ==  "Aruba") ? 'selected="selected"' : ''; ?>>Aruba</option>
                  <option value="Ashmore and Cartier Islands">Ashmore and Cartier Islands</option>
                  <option value="Austria" <?php echo ($return_object != "" && $return_object->country ==  "Austria") ? 'selected="selected"' : ''; ?>>Austria</option>
                  <option value="Azerbaijan" <?php echo ($return_object != "" && $return_object->country ==  "Azerbaijan") ? 'selected="selected"' : ''; ?>>Azerbaijan</option>
                  <option value="Bahamas" <?php echo ($return_object != "" && $return_object->country ==  "Bahamas") ? 'selected="selected"' : ''; ?>>Bahamas</option>
                  <option value="Bahrain" <?php echo ($return_object != "" && $return_object->country ==  "Bahrain") ? 'selected="selected"' : ''; ?>>Bahrain</option>
                  <option value="Bangladesh" <?php echo ($return_object != "" && $return_object->country ==  "Bangladesh") ? 'selected="selected"' : ''; ?>>Bangladesh</option>
                  <option value="Barbados" <?php echo ($return_object != "" && $return_object->country ==  "Barbados") ? 'selected="selected"' : ''; ?>>Barbados</option>
                  <option value="Belarus" <?php echo ($return_object != "" && $return_object->country ==  "Belarus") ? 'selected="selected"' : ''; ?>>Belarus</option>
                  <option value="Belgium" <?php echo ($return_object != "" && $return_object->country ==  "Belgium") ? 'selected="selected"' : ''; ?>>Belgium</option>
                  <option value="Belize" <?php echo ($return_object != "" && $return_object->country ==  "Belize") ? 'selected="selected"' : ''; ?>>Belize</option>
                  <option value="Benin" <?php echo ($return_object != "" && $return_object->country ==  "Benin") ? 'selected="selected"' : ''; ?>>Benin</option>
                  <option value="Bermuda" <?php echo ($return_object != "" && $return_object->country ==  "Bermuda") ? 'selected="selected"' : ''; ?>>Bermuda</option>
                  <option value="Bhutan" <?php echo ($return_object != "" && $return_object->country ==  "Bhutan") ? 'selected="selected"' : ''; ?>>Bhutan</option>
                  <option value="Bolivia" <?php echo ($return_object != "" && $return_object->country ==  "Bolivia") ? 'selected="selected"' : ''; ?>>Bolivia</option>
                  <option value="Bosnia and Herzegovina <?php echo ($return_object != "" && $return_object->country ==  "Bosnia and Herzegovina") ? 'selected="selected"' : ''; ?>">Bosnia and Herzegovina</option>
                  <option value="Botswana" <?php echo ($return_object != "" && $return_object->country ==  "Botswana") ? 'selected="selected"' : ''; ?>>Botswana</option>
                  <option value="Brazil" <?php echo ($return_object != "" && $return_object->country ==  "Brazil") ? 'selected="selected"' : ''; ?>>Brazil</option>
                  <option value="British Virgin Islands" <?php echo ($return_object != "" && $return_object->country ==  "British Virgin Islands") ? 'selected="selected"' : ''; ?>>British Virgin Islands</option>
                  <option value="Brunei" <?php echo ($return_object != "" && $return_object->country ==  "Brunei") ? 'selected="selected"' : ''; ?>>Brunei</option>
                  <option value="Bulgaria" <?php echo ($return_object != "" && $return_object->country ==  "Bulgaria") ? 'selected="selected"' : ''; ?>>Bulgaria</option>
                  <option value="Burkina Faso" <?php echo ($return_object != "" && $return_object->country ==  "Burkina Faso") ? 'selected="selected"' : ''; ?>>Burkina Faso</option>
                  <option value="Burma">Burma</option>
                  <option value="Burundi" <?php echo ($return_object != "" && $return_object->country ==  "Burundi") ? 'selected="selected"' : ''; ?>>Burundi</option>
                  <option value="Cambodia" <?php echo ($return_object != "" && $return_object->country ==  "Cambodia") ? 'selected="selected"' : ''; ?>>Cambodia</option>
                  <option value="Cameroon" <?php echo ($return_object != "" && $return_object->country ==  "Cameroon") ? 'selected="selected"' : ''; ?>>Cameroon</option>
                  <option value="Cape Verde" <?php echo ($return_object != "" && $return_object->country ==  "Cape Verde") ? 'selected="selected"' : ''; ?>>Cape Verde</option>
                  <option value="Cayman Islands" <?php echo ($return_object != "" && $return_object->country ==  "Cayman Islands") ? 'selected="selected"' : ''; ?>>Cayman Islands</option>
                  <option value="Central African Republic" <?php echo ($return_object != "" && $return_object->country ==  "Central African Republic") ? 'selected="selected"' : ''; ?>>Central African Republic</option>
                  <option value="Chad" <?php echo ($return_object != "" && $return_object->country ==  "Chad") ? 'selected="selected"' : ''; ?>>Chad</option>
                  <option value="Chile" <?php echo ($return_object != "" && $return_object->country ==  "Chile") ? 'selected="selected"' : ''; ?>>Chile</option>
                  <option value="China" <?php echo ($return_object != "" && $return_object->country ==  "China") ? 'selected="selected"' : ''; ?>>China</option>
                  <option value="Christmas Island" <?php echo ($return_object != "" && $return_object->country ==  "Christmas Island") ? 'selected="selected"' : ''; ?>>Christmas Island</option>
                  <option value="Colombia" <?php echo ($return_object != "" && $return_object->country ==  "Colombia") ? 'selected="selected"' : ''; ?>>Colombia</option>
                  <option value="Comoros" <?php echo ($return_object != "" && $return_object->country ==  "Comoros") ? 'selected="selected"' : ''; ?>>Comoros</option>
                  <option value="Congo" <?php echo ($return_object != "" && $return_object->country ==  "Congo") ? 'selected="selected"' : ''; ?>>Congo</option>
                  <option value="Cook Islands" <?php echo ($return_object != "" && $return_object->country ==  "Cook Islands") ? 'selected="selected"' : ''; ?>>Cook Islands</option>
                  <option value="Coral Sea Islands">Coral Sea Islands</option>
                  <option value="Costa Rica" <?php echo ($return_object != "" && $return_object->country ==  "Costa Rica") ? 'selected="selected"' : ''; ?>>Costa Rica</option>
                  <option value="Cote d'Ivoire" <?php echo ($return_object != "" && $return_object->country ==  "Cote d'Ivoire") ? 'selected="selected"' : ''; ?>>Cote d'Ivoire</option>
                  <option value="Croatia" <?php echo ($return_object != "" && $return_object->country ==  "Croatia") ? 'selected="selected"' : ''; ?>>Croatia</option>
                  <option value="Cuba" <?php echo ($return_object != "" && $return_object->country ==  "Cuba") ? 'selected="selected"' : ''; ?>>Cuba</option>
                  <option value="Cyprus" <?php echo ($return_object != "" && $return_object->country ==  "Cyprus") ? 'selected="selected"' : ''; ?>>Cyprus</option>
                  <option value="Czech Republic" <?php echo ($return_object != "" && $return_object->country ==  "Czech Republic") ? 'selected="selected"' : ''; ?>>Czech Republic</option>
                  <option value="Democratic Republic of the Congo" <?php echo ($return_object != "" && $return_object->country ==  "Democratic Republic of the Congo") ? 'selected="selected"' : ''; ?>>Democratic Republic of the Congo</option>
                  <option value="Denmark" <?php echo ($return_object != "" && $return_object->country ==  "Denmark") ? 'selected="selected"' : ''; ?>>Denmark</option>
                  <option value="Djibouti" <?php echo ($return_object != "" && $return_object->country ==  "Djibouti") ? 'selected="selected"' : ''; ?>>Djibouti</option>
                  <option value="Dominica" <?php echo ($return_object != "" && $return_object->country ==  "Dominica") ? 'selected="selected"' : ''; ?>>Dominica</option>
                  <option value="Dominican Republic" <?php echo ($return_object != "" && $return_object->country ==  "Dominican Republic") ? 'selected="selected"' : ''; ?>>Dominican Republic</option>
                  <option value="East Timor">East Timor</option>
                  <option value="Ecuador" <?php echo ($return_object != "" && $return_object->country ==  "Ecuador") ? 'selected="selected"' : ''; ?>>Ecuador</option>
                  <option value="Egypt" <?php echo ($return_object != "" && $return_object->country ==  "Egypt") ? 'selected="selected"' : ''; ?>>Egypt</option>
                  <option value="El Salvador" <?php echo ($return_object != "" && $return_object->country ==  "El Salvador") ? 'selected="selected"' : ''; ?>>El Salvador</option>
                  <option value="Equatorial Guinea" <?php echo ($return_object != "" && $return_object->country ==  "Equatorial Guinea") ? 'selected="selected"' : ''; ?>>Equatorial Guinea</option>
                  <option value="Eritrea" <?php echo ($return_object != "" && $return_object->country ==  "Eritrea") ? 'selected="selected"' : ''; ?>>Eritrea</option>
                  <option value="Estonia" <?php echo ($return_object != "" && $return_object->country ==  "Estonia") ? 'selected="selected"' : ''; ?>>Estonia</option>
                  <option value="Ethiopia" <?php echo ($return_object != "" && $return_object->country ==  "Ethiopia") ? 'selected="selected"' : ''; ?>>Ethiopia</option>
                  <option value="Europa Island">Europa Island</option>
                  <option value="Falkland Islands" <?php echo ($return_object != "" && $return_object->country ==  "Falkland Islands") ? 'selected="selected"' : ''; ?>>Falkland Islands</option>
                  <option value="Faroe Islands" <?php echo ($return_object != "" && $return_object->country ==  "Faroe Islands") ? 'selected="selected"' : ''; ?>>Faroe Islands</option>
                  <option value="Fiji" <?php echo ($return_object != "" && $return_object->country ==  "Fiji") ? 'selected="selected"' : ''; ?>>Fiji</option>
                  <option value="France" <?php echo ($return_object != "" && $return_object->country ==  "France") ? 'selected="selected"' : ''; ?>>France</option>
                  <option value="French Guiana" <?php echo ($return_object != "" && $return_object->country ==  "French Guiana") ? 'selected="selected"' : ''; ?>>French Guiana</option>
                  <option value="French Polynesia" <?php echo ($return_object != "" && $return_object->country ==  "French Polynesia") ? 'selected="selected"' : ''; ?>>French Polynesia</option>
                  <option value="French Southern and Antarctic Lands">French Southern and Antarctic Lands</option>
                  <option value="Gabon" <?php echo ($return_object != "" && $return_object->country ==  "Gabon") ? 'selected="selected"' : ''; ?>>Gabon</option>
                  <option value="Gambia" <?php echo ($return_object != "" && $return_object->country ==  "Gambia") ? 'selected="selected"' : ''; ?>>Gambia</option>
                  <option value="Gaza Strip">Gaza Strip</option>
                  <option value="Georgia" <?php echo ($return_object != "" && $return_object->country ==  "Georgia") ? 'selected="selected"' : ''; ?>>Georgia</option>
                  <option value="Germany" <?php echo ($return_object != "" && $return_object->country ==  "Germany") ? 'selected="selected"' : ''; ?>>Germany</option>
                  <option value="Ghana" <?php echo ($return_object != "" && $return_object->country ==  "Ghana") ? 'selected="selected"' : ''; ?>>Ghana</option>
                  <option value="Gibraltar" <?php echo ($return_object != "" && $return_object->country ==  "Gibraltar") ? 'selected="selected"' : ''; ?>>Gibraltar</option>
                  <option value="Glorioso Islands">Glorioso Islands</option>
                  <option value="Greece" <?php echo ($return_object != "" && $return_object->country ==  "Greece") ? 'selected="selected"' : ''; ?>>Greece</option>
                  <option value="Greenland" <?php echo ($return_object != "" && $return_object->country ==  "Greenland") ? 'selected="selected"' : ''; ?>>Greenland</option>
                  <option value="Grenada" <?php echo ($return_object != "" && $return_object->country ==  "Grenada") ? 'selected="selected"' : ''; ?>>Grenada</option>
                  <option value="Guadeloupe" <?php echo ($return_object != "" && $return_object->country ==  "Guadeloupe") ? 'selected="selected"' : ''; ?>>Guadeloupe</option>
                  <option value="Guatemala" <?php echo ($return_object != "" && $return_object->country ==  "Guatemala") ? 'selected="selected"' : ''; ?>>Guatemala</option>
                  <option value="Guernsey" <?php echo ($return_object != "" && $return_object->country ==  "Guernsey") ? 'selected="selected"' : ''; ?>>Guernsey</option>
                  <option value="Guinea" <?php echo ($return_object != "" && $return_object->country ==  "Guinea") ? 'selected="selected"' : ''; ?>>Guinea</option>
                  <option value="Guinea-Bissau" <?php echo ($return_object != "" && $return_object->country ==  "Guinea-Bissau") ? 'selected="selected"' : ''; ?>>Guinea-Bissau</option>
                  <option value="Guyana" <?php echo ($return_object != "" && $return_object->country ==  "Guyana") ? 'selected="selected"' : ''; ?>>Guyana</option>
                  <option value="Haiti" <?php echo ($return_object != "" && $return_object->country ==  "Haiti") ? 'selected="selected"' : ''; ?>>Haiti</option>
                  <option value="Heard Island and McDonald Islands" <?php echo ($return_object != "" && $return_object->country ==  "Heard Island and McDonald Islands") ? 'selected="selected"' : ''; ?>>Heard Island and McDonald Islands</option>
                  <option value="Honduras" <?php echo ($return_object != "" && $return_object->country ==  "Honduras") ? 'selected="selected"' : ''; ?>>Honduras</option>
                  <option value="Hong Kong" <?php echo ($return_object != "" && $return_object->country ==  "Hong Kong") ? 'selected="selected"' : ''; ?>>Hong Kong</option>
                  <option value="Hungary" <?php echo ($return_object != "" && $return_object->country ==  "Hungary") ? 'selected="selected"' : ''; ?>>Hungary</option>
                  <option value="Iceland" <?php echo ($return_object != "" && $return_object->country ==  "Iceland") ? 'selected="selected"' : ''; ?>>Iceland</option>
                  <option value="India" <?php echo ($return_object != "" && $return_object->country ==  "India") ? 'selected="selected"' : ''; ?>>India</option>
                  <option value="Indonesia" <?php echo ($return_object != "" && $return_object->country ==  "Indonesia") ? 'selected="selected"' : ''; ?>>Indonesia</option>
                  <option value="Iran" <?php echo ($return_object != "" && $return_object->country ==  "Iran") ? 'selected="selected"' : ''; ?>>Iran</option>
                  <option value="Iraq" <?php echo ($return_object != "" && $return_object->country ==  "Iraq") ? 'selected="selected"' : ''; ?>>Iraq</option>
                  <option value="Isle of Man" <?php echo ($return_object != "" && $return_object->country ==  "Isle of Man") ? 'selected="selected"' : ''; ?>>Isle of Man</option>
                  <option value="Israel" <?php echo ($return_object != "" && $return_object->country ==  "Israel") ? 'selected="selected"' : ''; ?>>Israel</option>
                  <option value="Italy" <?php echo ($return_object != "" && $return_object->country ==  "Italy") ? 'selected="selected"' : ''; ?>>Italy</option>
                  <option value="Jamaica" <?php echo ($return_object != "" && $return_object->country ==  "Jamaica") ? 'selected="selected"' : ''; ?>>Jamaica</option>
                  <option value="Jan Mayen" <?php echo ($return_object != "" && $return_object->country ==  "Jan Mayen") ? 'selected="selected"' : ''; ?>>Jan Mayen</option>
                  <option value="Japan" <?php echo ($return_object != "" && $return_object->country ==  "Japan") ? 'selected="selected"' : ''; ?>>Japan</option>
                  <option value="Jersey" <?php echo ($return_object != "" && $return_object->country ==  "Jersey") ? 'selected="selected"' : ''; ?>>Jersey</option>
                  <option value="Jordan" <?php echo ($return_object != "" && $return_object->country ==  "Jordan") ? 'selected="selected"' : ''; ?>>Jordan</option>
                  <option value="Juan de Nova Island">Juan de Nova Island</option>
                  <option value="Kazakhstan" <?php echo ($return_object != "" && $return_object->country ==  "Kazakhstan") ? 'selected="selected"' : ''; ?>>Kazakhstan</option>
                  <option value="Kenya" <?php echo ($return_object != "" && $return_object->country ==  "Kenya") ? 'selected="selected"' : ''; ?>>Kenya</option>
                  <option value="Kiribati" <?php echo ($return_object != "" && $return_object->country ==  "Kiribati") ? 'selected="selected"' : ''; ?>>Kiribati</option>
                  <option value="Kuwait" <?php echo ($return_object != "" && $return_object->country ==  "Kuwait") ? 'selected="selected"' : ''; ?>>Kuwait</option>
                  <option value="Kyrgyzstan" <?php echo ($return_object != "" && $return_object->country ==  "Kyrgyzstan") ? 'selected="selected"' : ''; ?>>Kyrgyzstan</option>
                  <option value="Laos" <?php echo ($return_object != "" && $return_object->country ==  "Laos") ? 'selected="selected"' : ''; ?>>Laos</option>
                  <option value="Latvia" <?php echo ($return_object != "" && $return_object->country ==  "Latvia") ? 'selected="selected"' : ''; ?>>Latvia</option>
                  <option value="Lebanon" <?php echo ($return_object != "" && $return_object->country ==  "Lebanon") ? 'selected="selected"' : ''; ?>>Lebanon</option>
                  <option value="Lesotho" <?php echo ($return_object != "" && $return_object->country ==  "Lesotho") ? 'selected="selected"' : ''; ?>>Lesotho</option>
                  <option value="Liberia" <?php echo ($return_object != "" && $return_object->country ==  "Liberia") ? 'selected="selected"' : ''; ?>>Liberia</option>
                  <option value="Libya" <?php echo ($return_object != "" && $return_object->country ==  "Libya") ? 'selected="selected"' : ''; ?>>Libya</option>
                  <option value="Liechtenstein" <?php echo ($return_object != "" && $return_object->country ==  "Liechtenstein") ? 'selected="selected"' : ''; ?>>Liechtenstein</option>
                  <option value="Lithuania" <?php echo ($return_object != "" && $return_object->country ==  "Lithuania") ? 'selected="selected"' : ''; ?>>Lithuania</option>
                  <option value="Luxembourg" <?php echo ($return_object != "" && $return_object->country ==  "Luxembourg") ? 'selected="selected"' : ''; ?>>Luxembourg</option>
                  <option value="Macau" <?php echo ($return_object != "" && $return_object->country ==  "Macau") ? 'selected="selected"' : ''; ?>>Macau</option>
                  <option value="Macedonia" <?php echo ($return_object != "" && $return_object->country ==  "Macedonia") ? 'selected="selected"' : ''; ?>>Macedonia</option>
                  <option value="Madagascar" <?php echo ($return_object != "" && $return_object->country ==  "Madagascar") ? 'selected="selected"' : ''; ?>>Madagascar</option>
                  <option value="Malawi" <?php echo ($return_object != "" && $return_object->country ==  "Malawi") ? 'selected="selected"' : ''; ?>>Malawi</option>
                  <option value="Malaysia" <?php echo ($return_object != "" && $return_object->country ==  "Malaysia") ? 'selected="selected"' : ''; ?>>Malaysia</option>
                  <option value="Maldives" <?php echo ($return_object != "" && $return_object->country ==  "Maldives") ? 'selected="selected"' : ''; ?>>Maldives</option>
                  <option value="Mali" <?php echo ($return_object != "" && $return_object->country ==  "Mali") ? 'selected="selected"' : ''; ?>>Mali</option>
                  <option value="Malta" <?php echo ($return_object != "" && $return_object->country ==  "Malta") ? 'selected="selected"' : ''; ?>>Malta</option>
                  <option value="Marshall Islands" <?php echo ($return_object != "" && $return_object->country ==  "Marshall Islands") ? 'selected="selected"' : ''; ?>>Marshall Islands</option>
                  <option value="Martinique" <?php echo ($return_object != "" && $return_object->country ==  "Martinique") ? 'selected="selected"' : ''; ?>>Martinique</option>
                  <option value="Mauritania" <?php echo ($return_object != "" && $return_object->country ==  "Mauritania") ? 'selected="selected"' : ''; ?>>Mauritania</option>
                  <option value="Mauritius" <?php echo ($return_object != "" && $return_object->country ==  "Mauritius") ? 'selected="selected"' : ''; ?>>Mauritius</option>
                  <option value="Mayotte" <?php echo ($return_object != "" && $return_object->country ==  "Mayotte") ? 'selected="selected"' : ''; ?>>Mayotte</option>
                  <option value="Mexico" <?php echo ($return_object != "" && $return_object->country ==  "Mexico") ? 'selected="selected"' : ''; ?>>Mexico</option>
                  <option value="Micronesia Federal States" <?php echo ($return_object != "" && $return_object->country ==  "Micronesia Federal States") ? 'selected="selected"' : ''; ?>>Micronesia Federal States</option>
                  <option value="Moldova" <?php echo ($return_object != "" && $return_object->country ==  "Moldova") ? 'selected="selected"' : ''; ?>>Moldova</option>
                  <option value="Monaco" <?php echo ($return_object != "" && $return_object->country ==  "Monaco") ? 'selected="selected"' : ''; ?>>Monaco</option>
                  <option value="Mongolia" <?php echo ($return_object != "" && $return_object->country ==  "Mongolia") ? 'selected="selected"' : ''; ?>>Mongolia</option>
                  <option value="Montenegro" <?php echo ($return_object != "" && $return_object->country ==  "Montenegro") ? 'selected="selected"' : ''; ?>>Montenegro</option>
                  <option value="Montserrat" <?php echo ($return_object != "" && $return_object->country ==  "Montserrat") ? 'selected="selected"' : ''; ?>>Montserrat</option>
                  <option value="Morocco" <?php echo ($return_object != "" && $return_object->country ==  "Morocco") ? 'selected="selected"' : ''; ?>>Morocco</option>
                  <option value="Mozambique" <?php echo ($return_object != "" && $return_object->country ==  "Mozambique") ? 'selected="selected"' : ''; ?>>Mozambique</option>
                  <option value="Namibia" <?php echo ($return_object != "" && $return_object->country ==  "Namibia") ? 'selected="selected"' : ''; ?>>Namibia</option>
                  <option value="Nauru" <?php echo ($return_object != "" && $return_object->country ==  "Nauru") ? 'selected="selected"' : ''; ?>>Nauru</option>
                  <option value="Nepal" <?php echo ($return_object != "" && $return_object->country ==  "Nepal") ? 'selected="selected"' : ''; ?>>Nepal</option>
                  <option value="Netherlands" <?php echo ($return_object != "" && $return_object->country ==  "Netherlands") ? 'selected="selected"' : ''; ?>>Netherlands</option>
                  <option value="Netherlands Antilles">Netherlands Antilles</option>
                  <option value="New Caledonia" <?php echo ($return_object != "" && $return_object->country ==  "New Caledonia") ? 'selected="selected"' : ''; ?>>New Caledonia</option>
                  <option value="Nicaragua" <?php echo ($return_object != "" && $return_object->country ==  "Nicaragua") ? 'selected="selected"' : ''; ?>>Nicaragua</option>
                  <option value="Niger" <?php echo ($return_object != "" && $return_object->country ==  "Niger") ? 'selected="selected"' : ''; ?>>Niger</option>
                  <option value="Nigeria" <?php echo ($return_object != "" && $return_object->country ==  "Nigeria") ? 'selected="selected"' : ''; ?>>Nigeria</option>
                  <option value="Niue" <?php echo ($return_object != "" && $return_object->country ==  "Niue") ? 'selected="selected"' : ''; ?>>Niue</option>
                  <option value="No Man's Land">No Man's Land</option>
                  <option value="Norfolk Island" <?php echo ($return_object != "" && $return_object->country ==  "Norfolk Island") ? 'selected="selected"' : ''; ?>>Norfolk Island</option>
                  <option value="North Korea">North Korea</option>
                  <option value="Norway" <?php echo ($return_object != "" && $return_object->country ==  "Norway") ? 'selected="selected"' : ''; ?>>Norway</option>
                  <option value="Oceans">Oceans</option>
                  <option value="Oman" <?php echo ($return_object != "" && $return_object->country ==  "Oman") ? 'selected="selected"' : ''; ?>>Oman</option>
                  <option value="Pakistan" <?php echo ($return_object != "" && $return_object->country ==  "Pakistan") ? 'selected="selected"' : ''; ?>>Pakistan</option>
                  <option value="Palau" <?php echo ($return_object != "" && $return_object->country ==  "Palau") ? 'selected="selected"' : ''; ?>>Palau</option>
                  <option value="Panama" <?php echo ($return_object != "" && $return_object->country ==  "Panama") ? 'selected="selected"' : ''; ?>>Panama</option>
                  <option value="Papua New Guinea" <?php echo ($return_object != "" && $return_object->country ==  "Papua New Guinea") ? 'selected="selected"' : ''; ?>>Papua New Guinea</option>
                  <option value="Paraguay" <?php echo ($return_object != "" && $return_object->country ==  "Paraguay") ? 'selected="selected"' : ''; ?>>Paraguay</option>
                  <option value="Peru" <?php echo ($return_object != "" && $return_object->country ==  "Peru") ? 'selected="selected"' : ''; ?>>Peru</option>
                  <option value="Pitcairn Islands">Pitcairn Islands</option>
                  <option value="Poland" <?php echo ($return_object != "" && $return_object->country ==  "Poland") ? 'selected="selected"' : ''; ?>>Poland</option>
                  <option value="Portugal" <?php echo ($return_object != "" && $return_object->country ==  "Portugal") ? 'selected="selected"' : ''; ?>>Portugal</option>
                  <option value="Qatar" <?php echo ($return_object != "" && $return_object->country ==  "Qatar") ? 'selected="selected"' : ''; ?>>Qatar</option>
                  <option value="Reunion" <?php echo ($return_object != "" && $return_object->country ==  "Reunion") ? 'selected="selected"' : ''; ?>>Reunion</option>
                  <option value="Romania" <?php echo ($return_object != "" && $return_object->country ==  "Romania") ? 'selected="selected"' : ''; ?>>Romania</option>
                  <option value="Russia" <?php echo ($return_object != "" && $return_object->country ==  "Russia") ? 'selected="selected"' : ''; ?>>Russia</option>
                  <option value="Rwanda" <?php echo ($return_object != "" && $return_object->country ==  "Rwanda") ? 'selected="selected"' : ''; ?>>Rwanda</option>
                  <option value="Saint Helena" <?php echo ($return_object != "" && $return_object->country ==  "Saint Helena") ? 'selected="selected"' : ''; ?>>Saint Helena</option>
                  <option value="Saint Kitts and Nevis" <?php echo ($return_object != "" && $return_object->country ==  "Saint Kitts and Nevis") ? 'selected="selected"' : ''; ?>>Saint Kitts and Nevis</option>
                  <option value="Saint Lucia" <?php echo ($return_object != "" && $return_object->country ==  "Saint Lucia") ? 'selected="selected"' : ''; ?>>Saint Lucia</option>
                  <option value="Saint Pierre and Miquelon" <?php echo ($return_object != "" && $return_object->country ==  "Saint Pierre and Miquelon") ? 'selected="selected"' : ''; ?>>Saint Pierre and Miquelon</option>
                  <option value="Saint Vincent and the Grenadines" <?php echo ($return_object != "" && $return_object->country ==  "Saint Vincent and the Grenadines") ? 'selected="selected"' : ''; ?>>Saint Vincent and the Grenadines</option>
                  <option value="Samoa" <?php echo ($return_object != "" && $return_object->country ==  "Samoa") ? 'selected="selected"' : ''; ?>>Samoa</option>
                  <option value="San Marino" <?php echo ($return_object != "" && $return_object->country ==  "San Marino") ? 'selected="selected"' : ''; ?>>San Marino</option>
                  <option value="Sao Tome and Principe" <?php echo ($return_object != "" && $return_object->country ==  "Sao Tome and Principe") ? 'selected="selected"' : ''; ?>>Sao Tome and Principe</option>
                  <option value="Saudi Arabia" <?php echo ($return_object != "" && $return_object->country ==  "Saudi Arabia") ? 'selected="selected"' : ''; ?>>Saudi Arabia</option>
                  <option value="Senegal" <?php echo ($return_object != "" && $return_object->country ==  "Senegal") ? 'selected="selected"' : ''; ?>>Senegal</option>
                  <option value="Serbia" <?php echo ($return_object != "" && $return_object->country ==  "Serbia") ? 'selected="selected"' : ''; ?>>Serbia</option>
                  <option value="Seychelles" <?php echo ($return_object != "" && $return_object->country ==  "Seychelles") ? 'selected="selected"' : ''; ?>>Seychelles</option>
                  <option value="Sierra Leone" <?php echo ($return_object != "" && $return_object->country ==  "Sierra Leone") ? 'selected="selected"' : ''; ?>>Sierra Leone</option>
                  <option value="Slovakia" <?php echo ($return_object != "" && $return_object->country ==  "Slovakia") ? 'selected="selected"' : ''; ?>>Slovakia</option>
                  <option value="Slovenia" <?php echo ($return_object != "" && $return_object->country ==  "Slovenia") ? 'selected="selected"' : ''; ?>>Slovenia</option>
                  <option value="Solomon Islands" <?php echo ($return_object != "" && $return_object->country ==  "Solomon Islands") ? 'selected="selected"' : ''; ?>>Solomon Islands</option>
                  <option value="Somalia" <?php echo ($return_object != "" && $return_object->country ==  "Somalia") ? 'selected="selected"' : ''; ?>>Somalia</option>
                  <option value="South Africa" <?php echo ($return_object != "" && $return_object->country ==  "South Africa") ? 'selected="selected"' : ''; ?>>South Africa</option>
                  <option value="South Georgia and the South Sandwich Islands" <?php echo ($return_object != "" && $return_object->country ==  "South Georgia and the South Sandwich Islands") ? 'selected="selected"' : ''; ?>>South Georgia and the South Sandwich Islands</option>
                  <option value="South Korea">South Korea</option>
                  <option value="Spain" <?php echo ($return_object != "" && $return_object->country ==  "Spain") ? 'selected="selected"' : ''; ?>>Spain</option>
                  <option value="Spratly Islands">Spratly Islands</option>
                  <option value="Sri Lanka" <?php echo ($return_object != "" && $return_object->country ==  "Sri Lanka") ? 'selected="selected"' : ''; ?>>Sri Lanka</option>
                  <option value="Sudan" <?php echo ($return_object != "" && $return_object->country ==  "Sudan") ? 'selected="selected"' : ''; ?>>Sudan</option>
                  <option value="Suriname" <?php echo ($return_object != "" && $return_object->country ==  "Suriname") ? 'selected="selected"' : ''; ?>>Suriname</option>
                  <option value="Svalbard" <?php echo ($return_object != "" && $return_object->country ==  "Svalbard") ? 'selected="selected"' : ''; ?>>Svalbard</option>
                  <option value="Swaziland" <?php echo ($return_object != "" && $return_object->country ==  "Swaziland") ? 'selected="selected"' : ''; ?>>Swaziland</option>
                  <option value="Switzerland" <?php echo ($return_object != "" && $return_object->country ==  "Switzerland") ? 'selected="selected"' : ''; ?>>Switzerland</option>
                  <option value="Syria" <?php echo ($return_object != "" && $return_object->country ==  "Syria") ? 'selected="selected"' : ''; ?>>Syria</option>
                  <option value="Taiwan" <?php echo ($return_object != "" && $return_object->country ==  "Taiwan") ? 'selected="selected"' : ''; ?>>Taiwan</option>
                  <option value="Tajikistan" <?php echo ($return_object != "" && $return_object->country ==  "Tajikistan") ? 'selected="selected"' : ''; ?>>Tajikistan</option>
                  <option value="Tanzania" <?php echo ($return_object != "" && $return_object->country ==  "Tanzania") ? 'selected="selected"' : ''; ?>>Tanzania</option>
                  <option value="Thailand" <?php echo ($return_object != "" && $return_object->country ==  "Thailand") ? 'selected="selected"' : ''; ?>>Thailand</option>
                  <option value="Togo" <?php echo ($return_object != "" && $return_object->country ==  "Togo") ? 'selected="selected"' : ''; ?>>Togo</option>
                  <option value="Tokelau" <?php echo ($return_object != "" && $return_object->country ==  "Tokelau") ? 'selected="selected"' : ''; ?>>Tokelau</option>
                  <option value="Tonga" <?php echo ($return_object != "" && $return_object->country ==  "Tonga") ? 'selected="selected"' : ''; ?>>Tonga</option>
                  <option value="Trinidad and Tobago" <?php echo ($return_object != "" && $return_object->country ==  "Trinidad and Tobago") ? 'selected="selected"' : ''; ?>>Trinidad and Tobago</option>
                  <option value="Tunisia" <?php echo ($return_object != "" && $return_object->country ==  "Tunisia") ? 'selected="selected"' : ''; ?>>Tunisia</option>
                  <option value="Turkey" <?php echo ($return_object != "" && $return_object->country ==  "Turkey") ? 'selected="selected"' : ''; ?>>Turkey</option>
                  <option value="Turkmenistan" <?php echo ($return_object != "" && $return_object->country ==  "Turkmenistan") ? 'selected="selected"' : ''; ?>>Turkmenistan</option>
                  <option value="Turks and Caicos Islands" <?php echo ($return_object != "" && $return_object->country ==  "Turks and Caicos Islands") ? 'selected="selected"' : ''; ?>>Turks and Caicos Islands</option>
                  <option value="Tuvalu" <?php echo ($return_object != "" && $return_object->country ==  "Tuvalu") ? 'selected="selected"' : ''; ?>>Tuvalu</option>
                  <option value="Uganda" <?php echo ($return_object != "" && $return_object->country ==  "Uganda") ? 'selected="selected"' : ''; ?>>Uganda</option>
                  <option value="Ukraine" <?php echo ($return_object != "" && $return_object->country ==  "Ukraine") ? 'selected="selected"' : ''; ?>>Ukraine</option>
                  <option value="United Arab Emirates" <?php echo ($return_object != "" && $return_object->country ==  "United Arab Emirates") ? 'selected="selected"' : ''; ?>>United Arab Emirates</option>
                  <option value="Uruguay" <?php echo ($return_object != "" && $return_object->country ==  "Uruguay") ? 'selected="selected"' : ''; ?>>Uruguay</option>
                  <option value="Uzbekistan" <?php echo ($return_object != "" && $return_object->country ==  "Uzbekistan") ? 'selected="selected"' : ''; ?>>Uzbekistan</option>
                  <option value="Vanuatu" <?php echo ($return_object != "" && $return_object->country ==  "Vanuatu") ? 'selected="selected"' : ''; ?>>Vanuatu</option>
                  <option value="Venezuela" <?php echo ($return_object != "" && $return_object->country ==  "Venezuela") ? 'selected="selected"' : ''; ?>>Venezuela</option>
                  <option value="Vietnam" <?php echo ($return_object != "" && $return_object->country ==  "Vietnam") ? 'selected="selected"' : ''; ?>>Vietnam</option>
                  <option value="Wallis and Futuna" <?php echo ($return_object != "" && $return_object->country ==  "Wallis and Futuna") ? 'selected="selected"' : ''; ?>>Wallis and Futuna</option>
                  <option value="West Bank">West Bank</option>
                  <option value="Western Sahara" <?php echo ($return_object != "" && $return_object->country ==  "Western Sahara") ? 'selected="selected"' : ''; ?>>Western Sahara</option>
                  <option value="Yemen" <?php echo ($return_object != "" && $return_object->country ==  "Yemen") ? 'selected="selected"' : ''; ?>>Yemen</option>
                  <option value="Zambia" <?php echo ($return_object != "" && $return_object->country ==  "Zambia") ? 'selected="selected"' : ''; ?>>Zambia</option>
                  <option value="Zimbabwe" <?php echo ($return_object != "" && $return_object->country ==  "Zimbabwe") ? 'selected="selected"' : ''; ?>>Zimbabwe</option>
                </select>
                <input type="text" name="country_code" id="country_code" placeholder="CC" style="width: 20%; display: inline-block; padding: 5px 0px 5px 0px; text-align: center;" readonly="" />
                <input type="text" name="conv_phone" id="conv_phone" placeholder="Your WhatsApp number…" style="width: 77%; padding: 5px 35px 5px 14px;" />
              </div>
              <div id="step_two_function" style="margin-bottom: 45px;">
                <input type="text" name="country_code_confirm" id="country_code_confirm" placeholder="CC" style="width: 20%; display: inline-block; padding: 5px 0px 5px 0px; text-align: center;" readonly="" />
                <input type="text" name="conv_phone_confirm" id="conv_phone_confirm" placeholder="Confirm your WhatsApp number…" style="width: 77%; padding: 5px 35px 5px 14px;" />
                <br />
                <input type="text" name="conv_account_name" id="conv_account_name" placeholder="Let Us Know Your Name" style="padding: 5px 35px 5px 14px;" />
              </div>
              <br />
              <input type="button" id="conv_next_btn" class="intercom-composer-send-button" value="Next" style="margin-top: 10px; background: #28AC32; border: none; text-align: center; width: 100%; max-width: 100%; padding: 10px; color: white; font-size: 16px;" />
              <input type="button" id="conv_send_btn" class="intercom-composer-send-button" value="Send" style="margin-top: 10px; background: #28AC32; border: none; text-align: center; width: 100%; max-width: 100%; padding: 10px; color: white; font-size: 16px;" />

              <?php
              $wsify_site_admin_id = get_option("wsify_site_admin_id");
              $wsify_site_admin_info = get_userdata($wsify_site_admin_id);
              $wsify_user_wsify_secret = $wsify_site_admin_info->user_wsify_account_secret;
              ?>
              <input type="hidden" name="wsify_user_wsify_secret" id="wsify_user_wsify_secret" value="<?php echo $wsify_user_wsify_secret; ?>" />
            </div>
          </div>
          <div class="intercom-composer-press-enter-to-send" style="height: auto; display: none;">Press Enter to send</div>
          </form>
        </div>
      </div>
    </div>
    <script type="text/javascript">
    jQuery(document).ready(function($) {
      initialise();

      $(".wsify-widget-chat").on("click", function(){
        $("#success_message").hide();
        $("#failure_message").hide();

        $("#conv_phone").css("border", "1px solid #dadee2");
        $("#conv_phone_confirm").css("border", "1px solid #dadee2");
        $("#conv_account_name").css("border", "1px solid #dadee2");
        $("#conv_message").css("border", "1px solid #dadee2");
        $("#country_code").css("border", "1px solid #dadee2");
        $("#country_code_confirm").css("border", "1px solid #dadee2");
        $("#country").css("border", "1px solid #dadee2");

        $("#step_one_function").show();
        $("#step_two_function").hide();

        $("#conv_send_btn").hide();
        $("#conv_next_btn").show();

        $("#intercom-container").css("visibility", "visible");
        $(".wsify-chat-sidebar").css("visibility", "visible");
      });

      $("#wsify-close-sidebar").on("click", function(){
        $("#intercom-container").css("visibility", "hidden");
        $(".wsify-chat-sidebar").css("visibility", "hidden");
      });

      $("#country").on("change", function(){
        $("#country_code").val(getCountryDialCode($(this).val()));
      });

      $("#conv_next_btn").on("click", function(){
        $("#conv_phone").css("border", "1px solid #dadee2");
        $("#conv_phone_confirm").css("border", "1px solid #dadee2");
        $("#conv_account_name").css("border", "1px solid #dadee2");
        $("#conv_message").css("border", "1px solid #dadee2");
        $("#country_code").css("border", "1px solid #dadee2");
        $("#country_code_confirm").css("border", "1px solid #dadee2");
        $("#country").css("border", "1px solid #dadee2");

        if($("#country_code").val() == "") {
          $("#country").css("border", "1px solid red");
          $("#country").focus();
        } else if($("#conv_phone").val() == "") {
          $("#conv_phone").css("border", "1px solid red");
          $("#conv_phone").focus();
        } else if($("#conv_message").val() == "") {
          $("#conv_message").css("border", "1px solid red");
          $("#conv_message").focus();
        } else {
          $("#conv_next_btn").hide();
          $("#conv_send_btn").show();

          $("#country_code_confirm").val($("#country_code").val());

          $("#step_one_function").hide();
          $("#step_two_function").show();
        }
      });

      $("#conv_send_btn").on("click", function(){
        $("#conv_phone").css("border", "1px solid #dadee2");
        $("#conv_phone_confirm").css("border", "1px solid #dadee2");
        $("#conv_account_name").css("border", "1px solid #dadee2");
        $("#conv_message").css("border", "1px solid #dadee2");
        $("#country_code").css("border", "1px solid #dadee2");
        $("#country_code_confirm").css("border", "1px solid #dadee2");
        $("#country").css("border", "1px solid #dadee2");

        if($("#conv_phone_confirm").val() == "") {
          $("#conv_phone_confirm").css("border", "1px solid red");
          $("#conv_phone_confirm").focus();
        } else if($("#conv_phone_confirm").val() != $("#conv_phone").val()) {
          $("#conv_phone_confirm").css("border", "1px solid red");
          $("#conv_phone_confirm").focus();
        } else {
          $("#conv_send_btn").attr("disabled", "disabled");
          $.post("https://www.wsify.com/wp-content/plugins/wsify-chat/ajax/wsify_widget_apis.php", {type: 'add_message', account_secret: $("#wsify_user_wsify_secret").val(), conv_phone: $("#country_code").val()+$("#conv_phone").val(), conv_message: $("#conv_message").val(), conv_account_name: $("#conv_account_name").val() }, function(data){
            var response = $.parseJSON(data);
            if(response.success){
              $("#success_message").show();
              $("#failure_message").hide();

              $("#step_one_function").hide();
              $("#step_two_function").hide();

              $("#conv_phone").val("");
              $("#conv_phone_confirm").val("");
              $("#conv_message").val("");
              $("#conv_send_btn").hide();
            } else{
              $("#success_message").hide();
              $("#failure_message").show();
              $("#conv_send_btn").hide();
            }
          });
        }
      });
    });

    var isoCountries = {
      "countries": [
        {
          "code": "+7 840",
          "name": "Abkhazia"
        },
        {
          "code": "+93",
          "name": "Afghanistan"
        },
        {
          "code": "+355",
          "name": "Albania"
        },
        {
          "code": "+213",
          "name": "Algeria"
        },
        {
          "code": "+1 684",
          "name": "American Samoa"
        },
        {
          "code": "+376",
          "name": "Andorra"
        },
        {
          "code": "+244",
          "name": "Angola"
        },
        {
          "code": "+1 264",
          "name": "Anguilla"
        },
        {
          "code": "+1 268",
          "name": "Antigua and Barbuda"
        },
        {
          "code": "+54",
          "name": "Argentina"
        },
        {
          "code": "+374",
          "name": "Armenia"
        },
        {
          "code": "+297",
          "name": "Aruba"
        },
        {
          "code": "+247",
          "name": "Ascension"
        },
        {
          "code": "+61",
          "name": "Australia"
        },
        {
          "code": "+672",
          "name": "Australian External Territories"
        },
        {
          "code": "+43",
          "name": "Austria"
        },
        {
          "code": "+994",
          "name": "Azerbaijan"
        },
        {
          "code": "+1 242",
          "name": "Bahamas"
        },
        {
          "code": "+973",
          "name": "Bahrain"
        },
        {
          "code": "+880",
          "name": "Bangladesh"
        },
        {
          "code": "+1 246",
          "name": "Barbados"
        },
        {
          "code": "+1 268",
          "name": "Barbuda"
        },
        {
          "code": "+375",
          "name": "Belarus"
        },
        {
          "code": "+32",
          "name": "Belgium"
        },
        {
          "code": "+501",
          "name": "Belize"
        },
        {
          "code": "+229",
          "name": "Benin"
        },
        {
          "code": "+1 441",
          "name": "Bermuda"
        },
        {
          "code": "+975",
          "name": "Bhutan"
        },
        {
          "code": "+591",
          "name": "Bolivia"
        },
        {
          "code": "+387",
          "name": "Bosnia and Herzegovina"
        },
        {
          "code": "+267",
          "name": "Botswana"
        },
        {
          "code": "+55",
          "name": "Brazil"
        },
        {
          "code": "+246",
          "name": "British Indian Ocean Territory"
        },
        {
          "code": "+1 284",
          "name": "British Virgin Islands"
        },
        {
          "code": "+673",
          "name": "Brunei"
        },
        {
          "code": "+359",
          "name": "Bulgaria"
        },
        {
          "code": "+226",
          "name": "Burkina Faso"
        },
        {
          "code": "+257",
          "name": "Burundi"
        },
        {
          "code": "+855",
          "name": "Cambodia"
        },
        {
          "code": "+237",
          "name": "Cameroon"
        },
        {
          "code": "+1",
          "name": "Canada"
        },
        {
          "code": "+238",
          "name": "Cape Verde"
        },
        {
          "code": "+ 345",
          "name": "Cayman Islands"
        },
        {
          "code": "+236",
          "name": "Central African Republic"
        },
        {
          "code": "+235",
          "name": "Chad"
        },
        {
          "code": "+56",
          "name": "Chile"
        },
        {
          "code": "+86",
          "name": "China"
        },
        {
          "code": "+61",
          "name": "Christmas Island"
        },
        {
          "code": "+61",
          "name": "Cocos-Keeling Islands"
        },
        {
          "code": "+57",
          "name": "Colombia"
        },
        {
          "code": "+269",
          "name": "Comoros"
        },
        {
          "code": "+242",
          "name": "Congo"
        },
        {
          "code": "+243",
          "name": "Congo, Dem. Rep. of (Zaire)"
        },
        {
          "code": "+682",
          "name": "Cook Islands"
        },
        {
          "code": "+506",
          "name": "Costa Rica"
        },
        {
          "code": "+385",
          "name": "Croatia"
        },
        {
          "code": "+53",
          "name": "Cuba"
        },
        {
          "code": "+599",
          "name": "Curacao"
        },
        {
          "code": "+537",
          "name": "Cyprus"
        },
        {
          "code": "+420",
          "name": "Czech Republic"
        },
        {
          "code": "+45",
          "name": "Denmark"
        },
        {
          "code": "+246",
          "name": "Diego Garcia"
        },
        {
          "code": "+253",
          "name": "Djibouti"
        },
        {
          "code": "+1 767",
          "name": "Dominica"
        },
        {
          "code": "+1 809",
          "name": "Dominican Republic"
        },
        {
          "code": "+670",
          "name": "East Timor"
        },
        {
          "code": "+56",
          "name": "Easter Island"
        },
        {
          "code": "+593",
          "name": "Ecuador"
        },
        {
          "code": "+20",
          "name": "Egypt"
        },
        {
          "code": "+503",
          "name": "El Salvador"
        },
        {
          "code": "+240",
          "name": "Equatorial Guinea"
        },
        {
          "code": "+291",
          "name": "Eritrea"
        },
        {
          "code": "+372",
          "name": "Estonia"
        },
        {
          "code": "+251",
          "name": "Ethiopia"
        },
        {
          "code": "+500",
          "name": "Falkland Islands"
        },
        {
          "code": "+298",
          "name": "Faroe Islands"
        },
        {
          "code": "+679",
          "name": "Fiji"
        },
        {
          "code": "+358",
          "name": "Finland"
        },
        {
          "code": "+33",
          "name": "France"
        },
        {
          "code": "+596",
          "name": "French Antilles"
        },
        {
          "code": "+594",
          "name": "French Guiana"
        },
        {
          "code": "+689",
          "name": "French Polynesia"
        },
        {
          "code": "+241",
          "name": "Gabon"
        },
        {
          "code": "+220",
          "name": "Gambia"
        },
        {
          "code": "+995",
          "name": "Georgia"
        },
        {
          "code": "+49",
          "name": "Germany"
        },
        {
          "code": "+233",
          "name": "Ghana"
        },
        {
          "code": "+350",
          "name": "Gibraltar"
        },
        {
          "code": "+30",
          "name": "Greece"
        },
        {
          "code": "+299",
          "name": "Greenland"
        },
        {
          "code": "+1 473",
          "name": "Grenada"
        },
        {
          "code": "+590",
          "name": "Guadeloupe"
        },
        {
          "code": "+1 671",
          "name": "Guam"
        },
        {
          "code": "+502",
          "name": "Guatemala"
        },
        {
          "code": "+224",
          "name": "Guinea"
        },
        {
          "code": "+245",
          "name": "Guinea-Bissau"
        },
        {
          "code": "+595",
          "name": "Guyana"
        },
        {
          "code": "+509",
          "name": "Haiti"
        },
        {
          "code": "+504",
          "name": "Honduras"
        },
        {
          "code": "+852",
          "name": "Hong Kong SAR China"
        },
        {
          "code": "+36",
          "name": "Hungary"
        },
        {
          "code": "+354",
          "name": "Iceland"
        },
        {
          "code": "+91",
          "name": "India"
        },
        {
          "code": "+62",
          "name": "Indonesia"
        },
        {
          "code": "+98",
          "name": "Iran"
        },
        {
          "code": "+964",
          "name": "Iraq"
        },
        {
          "code": "+353",
          "name": "Ireland"
        },
        {
          "code": "+972",
          "name": "Israel"
        },
        {
          "code": "+39",
          "name": "Italy"
        },
        {
          "code": "+225",
          "name": "Ivory Coast"
        },
        {
          "code": "+1 876",
          "name": "Jamaica"
        },
        {
          "code": "+81",
          "name": "Japan"
        },
        {
          "code": "+962",
          "name": "Jordan"
        },
        {
          "code": "+7 7",
          "name": "Kazakhstan"
        },
        {
          "code": "+254",
          "name": "Kenya"
        },
        {
          "code": "+686",
          "name": "Kiribati"
        },
        {
          "code": "+965",
          "name": "Kuwait"
        },
        {
          "code": "+996",
          "name": "Kyrgyzstan"
        },
        {
          "code": "+856",
          "name": "Laos"
        },
        {
          "code": "+371",
          "name": "Latvia"
        },
        {
          "code": "+961",
          "name": "Lebanon"
        },
        {
          "code": "+266",
          "name": "Lesotho"
        },
        {
          "code": "+231",
          "name": "Liberia"
        },
        {
          "code": "+218",
          "name": "Libya"
        },
        {
          "code": "+423",
          "name": "Liechtenstein"
        },
        {
          "code": "+370",
          "name": "Lithuania"
        },
        {
          "code": "+352",
          "name": "Luxembourg"
        },
        {
          "code": "+853",
          "name": "Macau SAR China"
        },
        {
          "code": "+389",
          "name": "Macedonia"
        },
        {
          "code": "+261",
          "name": "Madagascar"
        },
        {
          "code": "+265",
          "name": "Malawi"
        },
        {
          "code": "+60",
          "name": "Malaysia"
        },
        {
          "code": "+960",
          "name": "Maldives"
        },
        {
          "code": "+223",
          "name": "Mali"
        },
        {
          "code": "+356",
          "name": "Malta"
        },
        {
          "code": "+692",
          "name": "Marshall Islands"
        },
        {
          "code": "+596",
          "name": "Martinique"
        },
        {
          "code": "+222",
          "name": "Mauritania"
        },
        {
          "code": "+230",
          "name": "Mauritius"
        },
        {
          "code": "+262",
          "name": "Mayotte"
        },
        {
          "code": "+52",
          "name": "Mexico"
        },
        {
          "code": "+691",
          "name": "Micronesia"
        },
        {
          "code": "+1 808",
          "name": "Midway Island"
        },
        {
          "code": "+373",
          "name": "Moldova"
        },
        {
          "code": "+377",
          "name": "Monaco"
        },
        {
          "code": "+976",
          "name": "Mongolia"
        },
        {
          "code": "+382",
          "name": "Montenegro"
        },
        {
          "code": "+1664",
          "name": "Montserrat"
        },
        {
          "code": "+212",
          "name": "Morocco"
        },
        {
          "code": "+95",
          "name": "Myanmar"
        },
        {
          "code": "+264",
          "name": "Namibia"
        },
        {
          "code": "+674",
          "name": "Nauru"
        },
        {
          "code": "+977",
          "name": "Nepal"
        },
        {
          "code": "+31",
          "name": "Netherlands"
        },
        {
          "code": "+599",
          "name": "Netherlands Antilles"
        },
        {
          "code": "+1 869",
          "name": "Nevis"
        },
        {
          "code": "+687",
          "name": "New Caledonia"
        },
        {
          "code": "+64",
          "name": "New Zealand"
        },
        {
          "code": "+505",
          "name": "Nicaragua"
        },
        {
          "code": "+227",
          "name": "Niger"
        },
        {
          "code": "+234",
          "name": "Nigeria"
        },
        {
          "code": "+683",
          "name": "Niue"
        },
        {
          "code": "+672",
          "name": "Norfolk Island"
        },
        {
          "code": "+850",
          "name": "North Korea"
        },
        {
          "code": "+1 670",
          "name": "Northern Mariana Islands"
        },
        {
          "code": "+47",
          "name": "Norway"
        },
        {
          "code": "+968",
          "name": "Oman"
        },
        {
          "code": "+92",
          "name": "Pakistan"
        },
        {
          "code": "+680",
          "name": "Palau"
        },
        {
          "code": "+970",
          "name": "Palestinian Territory"
        },
        {
          "code": "+507",
          "name": "Panama"
        },
        {
          "code": "+675",
          "name": "Papua New Guinea"
        },
        {
          "code": "+595",
          "name": "Paraguay"
        },
        {
          "code": "+51",
          "name": "Peru"
        },
        {
          "code": "+63",
          "name": "Philippines"
        },
        {
          "code": "+48",
          "name": "Poland"
        },
        {
          "code": "+351",
          "name": "Portugal"
        },
        {
          "code": "+1 787",
          "name": "Puerto Rico"
        },
        {
          "code": "+974",
          "name": "Qatar"
        },
        {
          "code": "+262",
          "name": "Reunion"
        },
        {
          "code": "+40",
          "name": "Romania"
        },
        {
          "code": "+7",
          "name": "Russia"
        },
        {
          "code": "+250",
          "name": "Rwanda"
        },
        {
          "code": "+685",
          "name": "Samoa"
        },
        {
          "code": "+378",
          "name": "San Marino"
        },
        {
          "code": "+966",
          "name": "Saudi Arabia"
        },
        {
          "code": "+221",
          "name": "Senegal"
        },
        {
          "code": "+381",
          "name": "Serbia"
        },
        {
          "code": "+248",
          "name": "Seychelles"
        },
        {
          "code": "+232",
          "name": "Sierra Leone"
        },
        {
          "code": "+65",
          "name": "Singapore"
        },
        {
          "code": "+421",
          "name": "Slovakia"
        },
        {
          "code": "+386",
          "name": "Slovenia"
        },
        {
          "code": "+677",
          "name": "Solomon Islands"
        },
        {
          "code": "+27",
          "name": "South Africa"
        },
        {
          "code": "+500",
          "name": "South Georgia and the South Sandwich Islands"
        },
        {
          "code": "+82",
          "name": "South Korea"
        },
        {
          "code": "+34",
          "name": "Spain"
        },
        {
          "code": "+94",
          "name": "Sri Lanka"
        },
        {
          "code": "+249",
          "name": "Sudan"
        },
        {
          "code": "+597",
          "name": "Suriname"
        },
        {
          "code": "+268",
          "name": "Swaziland"
        },
        {
          "code": "+46",
          "name": "Sweden"
        },
        {
          "code": "+41",
          "name": "Switzerland"
        },
        {
          "code": "+963",
          "name": "Syria"
        },
        {
          "code": "+886",
          "name": "Taiwan"
        },
        {
          "code": "+992",
          "name": "Tajikistan"
        },
        {
          "code": "+255",
          "name": "Tanzania"
        },
        {
          "code": "+66",
          "name": "Thailand"
        },
        {
          "code": "+670",
          "name": "Timor Leste"
        },
        {
          "code": "+228",
          "name": "Togo"
        },
        {
          "code": "+690",
          "name": "Tokelau"
        },
        {
          "code": "+676",
          "name": "Tonga"
        },
        {
          "code": "+1 868",
          "name": "Trinidad and Tobago"
        },
        {
          "code": "+216",
          "name": "Tunisia"
        },
        {
          "code": "+90",
          "name": "Turkey"
        },
        {
          "code": "+993",
          "name": "Turkmenistan"
        },
        {
          "code": "+1 649",
          "name": "Turks and Caicos Islands"
        },
        {
          "code": "+688",
          "name": "Tuvalu"
        },
        {
          "code": "+1 340",
          "name": "U.S. Virgin Islands"
        },
        {
          "code": "+256",
          "name": "Uganda"
        },
        {
          "code": "+380",
          "name": "Ukraine"
        },
        {
          "code": "+971",
          "name": "United Arab Emirates"
        },
        {
          "code": "+44",
          "name": "United Kingdom"
        },
        {
          "code": "+1",
          "name": "United States"
        },
        {
          "code": "+598",
          "name": "Uruguay"
        },
        {
          "code": "+998",
          "name": "Uzbekistan"
        },
        {
          "code": "+678",
          "name": "Vanuatu"
        },
        {
          "code": "+58",
          "name": "Venezuela"
        },
        {
          "code": "+84",
          "name": "Vietnam"
        },
        {
          "code": "+1 808",
          "name": "Wake Island"
        },
        {
          "code": "+681",
          "name": "Wallis and Futuna"
        },
        {
          "code": "+967",
          "name": "Yemen"
        },
        {
          "code": "+260",
          "name": "Zambia"
        },
        {
          "code": "+255",
          "name": "Zanzibar"
        },
        {
          "code": "+263",
          "name": "Zimbabwe"
        }
      ]
    };

    function getCountryDialCode(countryName) {
        var result = jQuery.grep(isoCountries['countries'], function(e){ return e.name == countryName; });
        if (result.length == 0) {
          return "";
        } else if (result.length == 1) {
          return result[0].code;
        } else {
          return result[0].code;
        }
    }

    var isoCountriesNames = {
        'AF' : 'Afghanistan',
        'AX' : 'Aland Islands',
        'AL' : 'Albania',
        'DZ' : 'Algeria',
        'AS' : 'American Samoa',
        'AD' : 'Andorra',
        'AO' : 'Angola',
        'AI' : 'Anguilla',
        'AQ' : 'Antarctica',
        'AG' : 'Antigua And Barbuda',
        'AR' : 'Argentina',
        'AM' : 'Armenia',
        'AW' : 'Aruba',
        'AU' : 'Australia',
        'AT' : 'Austria',
        'AZ' : 'Azerbaijan',
        'BS' : 'Bahamas',
        'BH' : 'Bahrain',
        'BD' : 'Bangladesh',
        'BB' : 'Barbados',
        'BY' : 'Belarus',
        'BE' : 'Belgium',
        'BZ' : 'Belize',
        'BJ' : 'Benin',
        'BM' : 'Bermuda',
        'BT' : 'Bhutan',
        'BO' : 'Bolivia',
        'BA' : 'Bosnia And Herzegovina',
        'BW' : 'Botswana',
        'BV' : 'Bouvet Island',
        'BR' : 'Brazil',
        'IO' : 'British Indian Ocean Territory',
        'BN' : 'Brunei Darussalam',
        'BG' : 'Bulgaria',
        'BF' : 'Burkina Faso',
        'BI' : 'Burundi',
        'KH' : 'Cambodia',
        'CM' : 'Cameroon',
        'CA' : 'Canada',
        'CV' : 'Cape Verde',
        'KY' : 'Cayman Islands',
        'CF' : 'Central African Republic',
        'TD' : 'Chad',
        'CL' : 'Chile',
        'CN' : 'China',
        'CX' : 'Christmas Island',
        'CC' : 'Cocos (Keeling) Islands',
        'CO' : 'Colombia',
        'KM' : 'Comoros',
        'CG' : 'Congo',
        'CD' : 'Congo, Democratic Republic',
        'CK' : 'Cook Islands',
        'CR' : 'Costa Rica',
        'CI' : 'Cote D\'Ivoire',
        'HR' : 'Croatia',
        'CU' : 'Cuba',
        'CY' : 'Cyprus',
        'CZ' : 'Czech Republic',
        'DK' : 'Denmark',
        'DJ' : 'Djibouti',
        'DM' : 'Dominica',
        'DO' : 'Dominican Republic',
        'EC' : 'Ecuador',
        'EG' : 'Egypt',
        'SV' : 'El Salvador',
        'GQ' : 'Equatorial Guinea',
        'ER' : 'Eritrea',
        'EE' : 'Estonia',
        'ET' : 'Ethiopia',
        'FK' : 'Falkland Islands (Malvinas)',
        'FO' : 'Faroe Islands',
        'FJ' : 'Fiji',
        'FI' : 'Finland',
        'FR' : 'France',
        'GF' : 'French Guiana',
        'PF' : 'French Polynesia',
        'TF' : 'French Southern Territories',
        'GA' : 'Gabon',
        'GM' : 'Gambia',
        'GE' : 'Georgia',
        'DE' : 'Germany',
        'GH' : 'Ghana',
        'GI' : 'Gibraltar',
        'GR' : 'Greece',
        'GL' : 'Greenland',
        'GD' : 'Grenada',
        'GP' : 'Guadeloupe',
        'GU' : 'Guam',
        'GT' : 'Guatemala',
        'GG' : 'Guernsey',
        'GN' : 'Guinea',
        'GW' : 'Guinea-Bissau',
        'GY' : 'Guyana',
        'HT' : 'Haiti',
        'HM' : 'Heard Island & Mcdonald Islands',
        'VA' : 'Holy See (Vatican City State)',
        'HN' : 'Honduras',
        'HK' : 'Hong Kong',
        'HU' : 'Hungary',
        'IS' : 'Iceland',
        'IN' : 'India',
        'ID' : 'Indonesia',
        'IR' : 'Iran, Islamic Republic Of',
        'IQ' : 'Iraq',
        'IE' : 'Ireland',
        'IM' : 'Isle Of Man',
        'IL' : 'Israel',
        'IT' : 'Italy',
        'JM' : 'Jamaica',
        'JP' : 'Japan',
        'JE' : 'Jersey',
        'JO' : 'Jordan',
        'KZ' : 'Kazakhstan',
        'KE' : 'Kenya',
        'KI' : 'Kiribati',
        'KR' : 'Korea',
        'KW' : 'Kuwait',
        'KG' : 'Kyrgyzstan',
        'LA' : 'Lao People\'s Democratic Republic',
        'LV' : 'Latvia',
        'LB' : 'Lebanon',
        'LS' : 'Lesotho',
        'LR' : 'Liberia',
        'LY' : 'Libyan Arab Jamahiriya',
        'LI' : 'Liechtenstein',
        'LT' : 'Lithuania',
        'LU' : 'Luxembourg',
        'MO' : 'Macao',
        'MK' : 'Macedonia',
        'MG' : 'Madagascar',
        'MW' : 'Malawi',
        'MY' : 'Malaysia',
        'MV' : 'Maldives',
        'ML' : 'Mali',
        'MT' : 'Malta',
        'MH' : 'Marshall Islands',
        'MQ' : 'Martinique',
        'MR' : 'Mauritania',
        'MU' : 'Mauritius',
        'YT' : 'Mayotte',
        'MX' : 'Mexico',
        'FM' : 'Micronesia, Federated States Of',
        'MD' : 'Moldova',
        'MC' : 'Monaco',
        'MN' : 'Mongolia',
        'ME' : 'Montenegro',
        'MS' : 'Montserrat',
        'MA' : 'Morocco',
        'MZ' : 'Mozambique',
        'MM' : 'Myanmar',
        'NA' : 'Namibia',
        'NR' : 'Nauru',
        'NP' : 'Nepal',
        'NL' : 'Netherlands',
        'AN' : 'Netherlands Antilles',
        'NC' : 'New Caledonia',
        'NZ' : 'New Zealand',
        'NI' : 'Nicaragua',
        'NE' : 'Niger',
        'NG' : 'Nigeria',
        'NU' : 'Niue',
        'NF' : 'Norfolk Island',
        'MP' : 'Northern Mariana Islands',
        'NO' : 'Norway',
        'OM' : 'Oman',
        'PK' : 'Pakistan',
        'PW' : 'Palau',
        'PS' : 'Palestinian Territory, Occupied',
        'PA' : 'Panama',
        'PG' : 'Papua New Guinea',
        'PY' : 'Paraguay',
        'PE' : 'Peru',
        'PH' : 'Philippines',
        'PN' : 'Pitcairn',
        'PL' : 'Poland',
        'PT' : 'Portugal',
        'PR' : 'Puerto Rico',
        'QA' : 'Qatar',
        'RE' : 'Reunion',
        'RO' : 'Romania',
        'RU' : 'Russian Federation',
        'RW' : 'Rwanda',
        'BL' : 'Saint Barthelemy',
        'SH' : 'Saint Helena',
        'KN' : 'Saint Kitts And Nevis',
        'LC' : 'Saint Lucia',
        'MF' : 'Saint Martin',
        'PM' : 'Saint Pierre And Miquelon',
        'VC' : 'Saint Vincent And Grenadines',
        'WS' : 'Samoa',
        'SM' : 'San Marino',
        'ST' : 'Sao Tome And Principe',
        'SA' : 'Saudi Arabia',
        'SN' : 'Senegal',
        'RS' : 'Serbia',
        'SC' : 'Seychelles',
        'SL' : 'Sierra Leone',
        'SG' : 'Singapore',
        'SK' : 'Slovakia',
        'SI' : 'Slovenia',
        'SB' : 'Solomon Islands',
        'SO' : 'Somalia',
        'ZA' : 'South Africa',
        'GS' : 'South Georgia And Sandwich Isl.',
        'ES' : 'Spain',
        'LK' : 'Sri Lanka',
        'SD' : 'Sudan',
        'SR' : 'Suriname',
        'SJ' : 'Svalbard And Jan Mayen',
        'SZ' : 'Swaziland',
        'SE' : 'Sweden',
        'CH' : 'Switzerland',
        'SY' : 'Syrian Arab Republic',
        'TW' : 'Taiwan',
        'TJ' : 'Tajikistan',
        'TZ' : 'Tanzania',
        'TH' : 'Thailand',
        'TL' : 'Timor-Leste',
        'TG' : 'Togo',
        'TK' : 'Tokelau',
        'TO' : 'Tonga',
        'TT' : 'Trinidad And Tobago',
        'TN' : 'Tunisia',
        'TR' : 'Turkey',
        'TM' : 'Turkmenistan',
        'TC' : 'Turks And Caicos Islands',
        'TV' : 'Tuvalu',
        'UG' : 'Uganda',
        'UA' : 'Ukraine',
        'AE' : 'United Arab Emirates',
        'GB' : 'United Kingdom',
        'US' : 'United States',
        'UM' : 'United States Outlying Islands',
        'UY' : 'Uruguay',
        'UZ' : 'Uzbekistan',
        'VU' : 'Vanuatu',
        'VE' : 'Venezuela',
        'VN' : 'Viet Nam',
        'VG' : 'Virgin Islands, British',
        'VI' : 'Virgin Islands, U.S.',
        'WF' : 'Wallis And Futuna',
        'EH' : 'Western Sahara',
        'YE' : 'Yemen',
        'ZM' : 'Zambia',
        'ZW' : 'Zimbabwe'
    };

    function getCountryName (countryCode) {
        if (isoCountriesNames.hasOwnProperty(countryCode)) {
            return isoCountriesNames[countryCode];
        } else {
            return countryCode;
        }
    }
    </script>
    <?php
  }
}

add_action('wp_footer', 'show_wsify_widget');
