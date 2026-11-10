<?
class CP_Www_Widgets_Media_Carousel_Controller extends CP_Common_Lib_WidgetControllerAbstract
{
    var $handle       = 'carousel1';
    var $appendCssClass = '';
    var $items        = ''; // how many items to show in the carousel
    var $showNav      = true;
    var $showPager    = true;
    var $speed        = 3;
    var $autoStart    = false;
    var $module       = 'webBasic_section';
    var $mediaRecType = 'banner';
    var $type         = 'picByContentType'; //picture, picByProductType, picRelatedPic
    var $contentType  = '';
    var $mediaFolderThumb = 'thumb';
    var $mediaFolderLarge = 'large';
    var $width       = '';
    var $height      = '';
    var $fx          = 'scroll'; //"none", "scroll", "directscroll", "fade", "crossfade", "cover" or "uncover"
    var $showCaption = false;
    var $thumbnailNav = false;
    var $url          = '';
    
    // these values are used for type = picRelatedPic
    var $recordType1 = 'picture';
    var $recordType2 = 'relatedPicture';
    var $record_id   = '';
    var $useRecType1Only = false; // if you want to show this plugin for just one record type
    var $colorId = ''; // pass this when you want to display images for a particular color (ref: qrkie)
    var $childId = ''; // pass this when you want to display images based on product items table (ref: qrkie)
}