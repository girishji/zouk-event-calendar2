<?php
// following may be needed for ajax
//header('Access-Control-Allow-Origin: *');

// vendor dir exists on heroku server, you can download locally also for local execution.
require('../vendor/autoload.php');

$appID = '1263842110297634';
$appSecret = 'fbf6c8e32c3da1ace6f11805decb49e4';

$fb = new Facebook\Facebook([
    'app_id' => $appID,
    'app_secret' => $appSecret,
    'default_graph_version' => 'v2.2',
]);

if (! $_SESSION['facebook_access_token']) {
    // User has approved this app through fb login prior to coming here. Get access token from GET
    // request of ajax
    // caution: can't use getJavaScriptHelper since this is server side.
    $_SESSION['facebook_access_token'] = (string) $_GET["access_token"];
}

/**
 * Does the id already exist? If not, add to array.
 **/
function addEvent(&$events, $graphNode) {
    $found = false;
    foreach ($events as $event) {
        if ($event->getField('id') == $graphNode->getField('id')) {
            $found = true;
        }
    }
    if (! $found) {
        $events[] = $graphNode;
    }
}

/**
 * Populate events
 **/
function populate(&$fb, &$events, $searchStr) {
    // Set either through ajax or throuh signedRequest sent from facebook to server, and 
    //  retrieved through pagetabhelper
    $accessToken = $_SESSION['facebook_access_token'];

    try {
        // Returns a `Facebook\FacebookResponse` object
        // $response = $fb->get('/search?q=zouk&type=event&fields=id,name,cover,start_time&limit=100', $accessToken);
        // default you get back 25 entries
        $fullSearchStr = '/search?q=' . $searchStr . '&type=event&fields=id,name,start_time,place,attending_count';
        $response = $fb->get($fullSearchStr, $accessToken);
        //$response = $fb->get('/search?q=zouk&type=event', $accessToken);
    } catch(Facebook\Exceptions\FacebookResponseException $e) {
        echo 'ZEC: Facebook Graph returned an error: ' . $e->getMessage();
        exit;
    } catch(Facebook\Exceptions\FacebookSDKException $e) {
        echo 'ZEC: Facebook SDK returned an error: ' . $e->getMessage();
        exit;
    } catch (OAuthException $e) {
        echo 'ZEC: Facebook returned an error: ' . $e->getMessage();
        exit;
    }

    if ($response->isError()) {
        echo 'ZEC: Graph API returned error';
        exit;
    }

    $curtime = time();

    // Page 1 (turn nodes into edges for pagination)
    $feedEdge = $response->getGraphEdge();

    foreach ($feedEdge as $graphNode) {
        $timestamp = $graphNode->getField('start_time'); //DateTime object
        if ($timestamp && (get_class($timestamp) == "DateTime")) {
            $startTime = $timestamp->getTimestamp(); // unix time, seconds since 1970 00:00:00
            if(($startTime > $curtime) || (($curtime - $startTime) < 3 * 24 * 60 * 60)) {     // 3 days
                addEvent($events, $graphNode);
            } else {
                //echo 'discarding node: ' . $graphNode->getField('start_time')->format('Y-m-d H:i:s') . ' ## ' . $graphNode->getField('name'); 
            }
        } else {
            echo 'ZEC: Error: start_time is not a DateTime';
            exit;
        } 
    }

    // Page 2 (and all other pages)
    $nextFeed = $fb->next($feedEdge);
    while ($nextFeed) {
        //echo "paging  <br />";
        foreach ($nextFeed as $graphNode) {
            $timestamp = $graphNode->getField('start_time'); //DateTime object
            if ($timestamp && (get_class($timestamp) == "DateTime")) {
                $startTime = $timestamp->getTimestamp(); // unix time, seconds since 1970 00:00:00
                if(($startTime > $curtime) || (($curtime - $startTime) < 3 * 24 * 60 * 60)) {     // 3 days
                    addEvent($events, $graphNode);
                } else {
                    //echo 'discarding node: ' . $graphNode->getField('start_time')->format('Y-m-d H:i:s') . ' ## ' . $graphNode->getField('name'); 
                }
            } else {
                echo 'ZEC: Error: start_time is not a DateTime';
                exit;
            } 
        }
        $nextFeed = $fb->next($nextFeed);
    }
}

$events = array();
// $str = 'zouk|zouk+carnival|zouk+time|zouk+night|f.i.e.l+official|lambada|lambazouk|zouk+lambada|' .
//      'brazilian+zouk|zouk+festival';
// populate($fb, $events, $str);

populate($fb, $events, "zouk");
populate($fb, $events, "zouk+carnival");
populate($fb, $events, "zouk+time");
populate($fb, $events, "zouk+night");
populate($fb, $events, "f.i.e.l+official");
populate($fb, $events, "lambazouk");
populate($fb, $events, "zouk+lambada");
populate($fb, $events, "brazilian+zouk");
populate($fb, $events, "zouk+festival");
populate($fb, $events, "zouk+marathon");
populate($fb, $events, "zouk+family");
populate($fb, $events, "zouk+fest");
populate($fb, $events, "zouk+congress");
populate($fb, $events, "zouk+weekend");
// // populate($fb, $events, "international+zouk");
populate($fb, $events, "zouk+salsa");
populate($fb, $events, "zouk+samba");
populate($fb, $events, "zouk+beach");
populate($fb, $events, "zouk+holiday");
populate($fb, $events, "bachaturo");
populate($fb, $events, "zouk+kizomba");
// // populate($fb, $events, "zouk+bachata");
populate($fb, $events, "carioca+zouk");
 
// // populate($fb, $events, "zouk+fever");
// // populate($fb, $events, "brasileiro+zouk");
// // populate($fb, $events, "zouk+fusion");
// // populate($fb, $events, "zouk+flow");
// // populate($fb, $events, "zouk+day");
// // populate($fb, $events, "zouk+jam");
// // populate($fb, $events, "zouk+danse");
populate($fb, $events, "zouk+dance");
populate($fb, $events, "zouk+sea");
populate($fb, $events, "zoukdevils");
populate($fb, $events, "fall+zouk");
populate($fb, $events, "berg+zouk");
populate($fb, $events, "brazouka");

// sort by start_time
function cmp($a, $b)
{
    $dt_a = $a->getField('start_time'); //DateTime object
    $ts_a = $dt_a->getTimestamp(); // unix time, seconds since 1970 00:00:00
    $dt_b = $b->getField('start_time'); //DateTime object
    $ts_b = $dt_b->getTimestamp(); // unix time, seconds since 1970 00:00:00

    if ($ts_a == $ts_b) {
        return 0;
    }
    return ($ts_a < $ts_b) ? -1 : 1;
}

usort($events, "cmp");

echo 'total ' . count($events) . '<br />';
foreach ($events as $event) {
    echo 'event node: ' . $event->getField('id') . " == " . $event->getField('start_time')->format('Y-m-d H:i:s') . ' ## ' . $event->getField('name'); 
    //var_dump($event->asArray());
    echo "<br />";
}

// Notes:
// - Session variables are stores in server memory and are deleted when the php session ends, normally after about 
// 20 minutes of no user activity. 
// - Cookies without an expire date are stored in the users browser and are deleted when the browser 
// session ends (i.e. the browser is closed).
// - Cookies with an expire date are stored in the browser until they expire although the user 
// can choose to remove them sooner.
// - There is no difference between cookies stored through js and ones stored through php, they are the same.
//
// - all php is executed server side. session is default 30 seconds and it times out. View frame source in chrome,
// you'll never get php back, only html
//
// http://stackoverflow.com/questions/11782809/facebook-access-token-and-ajax-calls
// don't send auth token in ajax request, use signedRequest
// On the browser (client) side if the user already authorized your app and is logged in Facevook, you
// can retrieve the signedRequest from the cookie $_COOKIE[('fbsr_' . YOUR_APP_ID)]

// 
// $request = new FacebookRequest(
//     $session,
//     'GET',
//     '/search',
//     array(
//         'pretty' => '0',
//         'fields' => 'name',
//         'q' => 'zouk',
//         'type' => 'event',
//         'limit' => '25',
//     'after' => 'MzQ3'
//     )
// );
// 
// $response = $request->execute();
// $graphObject = $response->getGraphObject();
// /* handle the result */
// 
?>