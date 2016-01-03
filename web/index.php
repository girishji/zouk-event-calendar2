<!DOCTYPE html>
<html>
<head>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
<link rel="stylesheet" type="text/css" href="/stylesheets/main.css" />
</head>

<body>
<div id="fb-root"></div>
<script>
      $(document).ready(function() {
          $.ajaxSetup({ cache: true });

          // Setup FB Javascript SDK so you can resize canvas. Following code should be after 'body' html tag.
          // layout file in this dir has body and other tags. This file is what is inside the body.
          // https://developers.facebook.com/quickstarts/1261883747160137/?platform=canvas
          // http://stackoverflow.com/questions/3982789/facebook-canvas-app-iframed-auto-height-resize
          // http://stackoverflow.com/questions/7037124/how-to-resize-the-facebook-iframe-application-window
          // login: https://developers.facebook.com/docs/facebook-login/web

          // http://learn.jquery.com/ajax/
          // Ajax is a mechanism for updating a web page without having to reload it. With Ajax, the page only loads once, at the start. 
          // http://facebookanswers.co.uk/using-ajax-in-a-facebook-php-application-part-1/

          window.fbAsyncInit = function() {
              FB.init({
                      //appId      : '1261883747160137',
                      //appId      : "<?php echo getenv('FACEBOOK_APP_ID') ?>",
                      appId      : '1263842110297634', //test
                xfbml      : true,
                cookie     : true,
                version    : 'v2.5'
                });
           
              // ADD ADDITIONAL FACEBOOK CODE HERE
              // setAutoGrow works but slowly and consumes cycles
              FB.Canvas.setAutoGrow();
              // manually set size (also slow)
              // FB.Canvas.setSize({ width: 640, height: 4000 });
          };

          // load the facebook SDK async
          (function(d, s, id){
              var js, fjs = d.getElementsByTagName(s)[0];
              if (d.getElementById(id)) {return;}
              js = d.createElement(s); js.id = id;
              js.src = "//connect.facebook.net/en_US/sdk.js";
              fjs.parentNode.insertBefore(js, fjs);
          }(document, 'script', 'facebook-jssdk'));

      });

    // fb login setup
    // This is called with the results from from FB.getLoginStatus().
    function statusChangeCallback(response) {
        console.log('statusChangeCallback');
        console.log(response);
        // The response object is returned with a status field that lets the
        // app know the current login status of the person.
        // Full docs on the response object can be found in the documentation
        // for FB.getLoginStatus().
        if (response.status === 'connected') {
            // Logged into your app and Facebook.
            $("#fb_login_button").hide();
            //console.log("girish token " + response.authResponse.accessToken);

            // send accessToken to server through ajax and let server use it to
            // execute php graph api
            $.ajax({
                    // The URL for the request
                    url: "content.php",
                    // The data to send (will be converted to a query string)
                    data: {
                               access_token: response.authResponse.accessToken
                    },
                    // Whether this is a POST or GET request
                    type: "GET",
                    // The type of data we expect back
                    dataType : "html",
                    //timeout: 60000, // millisec
                     // Code to run if the request succeeds;
                    // the response is passed to the function
                    success: function( data ) {
                        //$( "<h1>" ).text( json.title ).appendTo( "body" );
                        //$( "<div class=\"content\">").html( json.html ).appendTo( "body" );
                        $("#zouk_content").html(data);
                    },
                    // Code to run if the request fails; the raw request and
                    // status codes are passed to the function
                    error: function( xhr, status, errorThrown ) {
                        alert( "Sorry, there was a problem!" );
                        console.log( "Error: " + errorThrown );
                        console.log( "Status: " + status );
                        console.dir( xhr );
                    }
             });
            
             // $("#zouk_content").load("/content.php"); // cannot send token through this method
        } else if (response.status === 'not_authorized') {
            // The person is logged into Facebook, but not your app.
            document.getElementById('my_status').innerHTML = 'Please log ' +
                    'into this app.';
        } else {
            // The person is not logged into Facebook, so we're not sure if
            // they are logged into this app or not.
            // girish: should not come here
            document.getElementById('my_status').innerHTML = 'Please log ' +
                    'into Facebook.';
        }
    }

    // This function is called when someone finishes with the Login
    // Button.  See the onlogin handler attached to it in the sample
    // code below.
    function checkLoginState() {
        FB.getLoginStatus(function(response) {
            statusChangeCallback(response);
        });
    }

</script>


<?php

    // You still need to login the user from inside your app and ask for permissions (not desirable to log them out).

    // To see the format of the data you are retrieving, use the "Graph API
    // Explorer" which is at https://developers.facebook.com/tools/explorer/

    // vendor dir exists on heroku server, you can download locally also for local execution.
    require('../vendor/autoload.php');

    //$appID = getenv('FACEBOOK_APP_ID');
    //$appSecret = getenv('FACEBOOK_SECRET');

    $appID = '1263842110297634';
    $appSecret = 'fbf6c8e32c3da1ace6f11805decb49e4';

    $fb = new Facebook\Facebook([
        'app_id' => $appID,
        'app_secret' => $appSecret,
        'default_graph_version' => 'v2.2',
    ]);

    // Get access token
    // https://www.sammyk.me/upgrading-the-facebook-php-sdk-from-v4-to-v5
    // https://developers.facebook.com/docs/php/howto/example_access_token_from_page_tab

    $helper = $fb->getPageTabHelper();

    $signedRequest = $helper->getSignedRequest();

    if ($signedRequest) {
        // $payload = $signedRequest->getPayload();
        // var_dump($payload);
        if (! $signedRequest->getUserId()) {
            // User has not logged in to this app and granted permissions (they could be logged into fb)

            // Below we include the Login Button social plugin. This button uses
            // the JavaScript SDK to present a graphical Login button that triggers
            //           the FB.login() function when clicked.
            echo '<div id="zouk_content">';
        echo 'Facebook requires that you grant permission for this app to access Facebook graph. In order to do that you have to grant this app permission to view your basic public profile. This app does *not* ask for permission to view private or sensitive data (you will verify it in the next step). Please click the login button and grant permission. <br /><br />';
        echo '</div>';

        // This button uses the JavaScript SDK to present a graphical Login button that triggers
        // the FB.login() function when clicked.
        echo <<<EOD
        <div id="fb_login_button">
        <fb:login-button scope="public_profile" onlogin="checkLoginState();">
        </fb:login-button>
        <div id="my_status">
        </div>
        </div>
EOD;
        return;
    }
} else {
    echo 'Signed Request not found';
    exit;
}

try {
    $accessToken = $helper->getAccessToken();
} catch(Facebook\Exceptions\FacebookResponseException $e) {
    // When Graph returns an error
    echo 'ZEC: Graph returned an error: ' . $e->getMessage();
    exit;
} catch(Facebook\Exceptions\FacebookSDKException $e) {
    // When validation fails or other local issues
    echo 'ZEC: Facebook SDK returned an error: ' . $e->getMessage();
    exit;
}


if (! isset($accessToken)) {
    echo 'ZEC: No OAuth data could be obtained from the signed request. User has not authorized your app yet.';
    exit;
} else {
    // Logged in.
    $_SESSION['facebook_access_token'] = (string) $accessToken;
    require 'content.php';
}


?>
</body>
</html>

