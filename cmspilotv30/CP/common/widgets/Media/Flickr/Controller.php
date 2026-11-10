<?
class CP_Common_Widgets_Media_Flickr_Controller extends CP_Common_Lib_WidgetControllerAbstract
{
    //phpFlickr Documentation: http://phpflickr.com/docs/
    //Flickr API Documentation: http://www.flickr.com/services/api/
    //To call a method, remove the "flickr." part of the name and replace any periods with underscores. 
    //For example, instead of flickr.photos.search, you would call $f->photos_search() 
    //instead of flickr.photos.licenses.getInfo, you would call $f->photos_licenses_getInfo() 
    //it is case sensitive
    
    var $api_key = ''; //This is the API key given to you by flickr.com.
    var $secret  = ''; // The "secret" is optional    
    var $enableCache     = true; //to store the filckr data in local table to make the loading faster
    var $refreshCacheSec = 3600; //refresh cache in seconds
    var $flickrReference  = ''; // Flickr photo id, photo set id etc. 
    
    var $handle    = 'flickr1';
    var $appendCls = '';
    
    var $phpFlickr = NULL;
    
    /**
     * 
     */
    function __construct() {
        require_once("phpFlickr/phpFlickr.php");       
    }    

}