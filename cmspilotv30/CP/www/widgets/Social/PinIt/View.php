<?
class CP_Www_Widgets_Social_PinIt_View extends CP_Common_Lib_WidgetViewAbstract
{
    var $jssKeys = array('pinIt');
    
    //========================================================//
    function getWidget() {
        $c = &$this->controller;
        
        $url = urlencode($c->url);
        $pinItMedia = urlencode($c->image);
        
        $text = "
        <a href='http://pinterest.com/pin/create/button/?url={$url}&media={$pinItMedia}' 
        class='pin-it-button' count-layout='horizontal'>
        <img border='0' src='//assets.pinterest.com/images/PinExt.png' title='Pin It' /></a>
        ";

        return $text;
    }

    //========================================================//
    function getRowsHTML() {
        $c = &$this->controller;

        $text = "
        ";
        
        return $text;
    }
}