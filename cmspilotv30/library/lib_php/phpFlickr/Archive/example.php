<?php
/* Last updated with phpFlickr 1.3.2
 *
 * This example file shows you how to call the 100 most recent public
 * photos.  It parses through them and prints out a link to each of them
 * along with the owner's name.
 *
 * Most of the processing time in this file comes from the 100 calls to
 * flickr.people.getInfo.  Enabling caching will help a whole lot with
 * this as there are many people who post multiple photos at once.
 *
 * Obviously, you'll want to replace the "<api key>" with one provided
 * by Flickr: http://www.flickr.com/services/api/key.gne
 */

require_once("phpFlickr.php");
$f = new phpFlickr("7a8b8650dc074295ddac50c6131cd2ef");
$refreshCacheSeconds = "3600"; //1 hour
$f->enableCache("db", "mysql://root:{$_SERVER['dbPassword']}@localhost/hkmm", $refreshCacheSeconds);

foreach ($photoset['photoset']['photo'] as $photo) {

    $photoDetails = $f->photos_getInfo($photo['id']);
    $title = $photoDetails['photo']['title'];
    $description = $photoDetails['photo']['description'];

    $sizes = $f->photos_getSizes($photo['id']);
    $square    = $sizes[0]['source'];
    $thumbnail = $sizes[1]['source'];
    $small     = $sizes[2]['source'];
    $medium    = $sizes[3]['source'];

    print "<img src='{$square}' />";
}
?>
