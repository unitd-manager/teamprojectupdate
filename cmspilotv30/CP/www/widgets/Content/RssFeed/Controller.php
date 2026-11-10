<?
class CP_Www_Widgets_Content_RssFeed_Controller extends CP_Common_Lib_WidgetControllerAbstract
{
    var $feedUrl           = '';

    var $addUrlForTitle    = false;  
    
    var $showHeading       = true;
    var $heading           = '';
    var $headingTag        = 'h2';

    var $showShortDesc     = true;
    var $showDesc          = true;
    var $showDate          = false;

    var $specialFilter     = ''; // eg: Latest, Favourite, By Section Type, By Category Type etc..
    var $displayLimit      = 5;

    var $showReadMore      = false; // if you need to show a read more in each record
    var $readMoreLbl       = 'cp.lbl.readMore';
    var $showGroupReadMore = false; // if you need to show a read more at the end of the widget
    var $groupReadMoreUrl  = '';
    var $groupReadMoreLbl  = 'cp.lbl.readMore';
    
    var $useDivContainer   = false;
    
    var $truncateDesc        = false;
    var $truncateDescLength  = 200;
    var $truncateDescEnding  = '...';

    var $truncateShortDesc   = false;
    var $truncateShortDescLength  = 100;
    var $truncateShortDescEnding  = '...';    

}