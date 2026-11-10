<?
class CP_Www_Widgets_Social_Twitter_View extends CP_Common_Lib_WidgetViewAbstract
{
    var $jssKeys = array('jTweetsAnywhere-1.3.1');
    
    //========================================================//
    function getWidget() {
        $tv = Zend_Registry::get('tv');
        $c = &$this->controller;

        $text = "
        <div id='{$c->handle}'>
        </div>
        ";

        CP_Common_Lib_Registry::arrayMerge('inlineScripts', array("
            exp = {
                 handle: '{$c->handle}'
                ,username: '{$c->username}'
                ,count: '{$c->count}'
                ,showProfileImages: '{$c->showProfileImages}'
                ,showUserScreenNames: '{$c->showUserScreenNames}'
                ,showUserFullNames: '{$c->showUserFullNames}'
                ,showActionReply: '{$c->showActionReply}'
                ,showActionFavorite: '{$c->showActionFavorite}'
            }
            cpw.social.twitter.run(exp);
        "));
        
        return $text;
    }
}