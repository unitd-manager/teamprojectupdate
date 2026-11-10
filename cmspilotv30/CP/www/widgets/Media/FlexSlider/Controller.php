<?
class CP_Www_Widgets_Media_FlexSlider_Controller extends CP_Common_Lib_WidgetControllerAbstract
{
    var $autoPlay  = true;
    var $slideshowSpeed  = 5; //seconds
    var $animationSpeed  = 0.6; //seconds
    var $animationType   = "slide"; // slide / fade
    var $direction   = "horizontal"; // horizontal / vertical
    var $showNav   = true;
    var $showNavArrows = true;
    var $appendCls = '';
    var $handle    = 'slider1';

    var $type      = 'picture'; //picture (= banner), picByContentType
    var $module    = 'webBasic_section';
    var $recordType    = 'banner';
    var $record_id = '';
    var $contentType = '';
    
    var $showCaption = true;

    /** options: default, minimalist-round, minimalist-square, metallic, construction, cs-portfolio **/
    var $subColLeft   = 'c50l'; // this will be used when description is available for picture
    var $subColRight  = 'c50r'; // this will be used when description is available for picture
    var $showReadMore = true; // this will be used when desc is available with url (ext or int)
    var $readMoreLbl  = 'cp.lbl.readMore';
}