<?
class CP_Www_Widgets_Media_AnythingSlider_Controller extends CP_Common_Lib_WidgetControllerAbstract
{
    var $autoPlay  = true;
    var $speed     = 5;
    var $showNav   = true;
    var $showNavArrows = true;
    var $appendCls = '';
    var $handle    = 'slider1';
    var $width     = 990;
    var $height    = 200;
    var $type      = 'picture'; //picture (= banner), picByContentType
    var $showCaption = true;
    var $resizeContents = true;
    var $contentType = ''; // use this when type = picByContentType

    /** options: default, minimalist-round, minimalist-square, metallic, construction, cs-portfolio **/
    var $theme     = 'cs-portfolio';
    var $subColLeft   = 'c50l'; // this will be used when description is available for picture
    var $subColRight  = 'c50r'; // this will be used when description is available for picture
    var $showReadMore = true; // this will be used when desc is available with url (ext or int)
    var $readMoreLbl  = 'cp.lbl.readMore';
    var $captionPosition = 'bottom';
}