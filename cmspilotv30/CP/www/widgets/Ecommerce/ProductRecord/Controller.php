<?
class CP_Www_Widgets_Ecommerce_ProductRecord_Controller extends CP_Common_Lib_WidgetControllerAbstract
{
    var $ulClass       = '';
    var $showTitle     = true;
    var $showShortDesc = false;
    var $showDesc      = false;
    var $showPic       = true;
    var $mediaExp      = array('folder' => 'thumb');
    var $specialFilter = 'Latest'; // eg: latest, slideshow, favourite, relatedProducts hot etc..
    var $displayLimit  = 50;
    var $heading       = '';
    var $addUrlForTitle = true;
    var $addUrlForImage = true;
    var $showGroupReadMore = false; // if you need to show a read more at the end of the widget
    var $groupReadMoreUrl  = '';
    var $groupReadMoreLbl  = 'cp.lbl.readMore';
    var $prodSecType       = 'Product';
    var $product_id = ''; //used for $specialFilter = relatedProducts
    
    var $includeMediaRec = false;
}