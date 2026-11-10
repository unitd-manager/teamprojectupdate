<?
class CP_Www_Widgets_Social_FacebookLike_View extends CP_Common_Lib_WidgetViewAbstract
{
    var $jssKeys = array('facebookSDK');

    //========================================================//
    function getWidget() {
        $tv = Zend_Registry::get('tv');
        $c = &$this->controller;

        $embed = '';

        if (!@$tv['facebookInitiated']){
            $embed = "
            <div id='fb-root'></div>
            <script>(function(d, s, id) {
              var js, fjs = d.getElementsByTagName(s)[0];
              if (d.getElementById(id)) return;
              js = d.createElement(s); js.id = id;
              js.src = '//connect.facebook.net/en_GB/all.js#xfbml=1';
              fjs.parentNode.insertBefore(js, fjs);
            }(document, 'script', 'facebook-jssdk'));</script>
            ";
        }

        $tv['facebookInitiated'] = true;
        CP_Common_Lib_Registry::arrayMerge('tv', $tv);

        $text = "
        {$embed}
        {$this->getRowsHTML()}
        ";

        return $text;
    }

    //========================================================//
    function getRowsHTML() {
        $c = &$this->controller;

        $text = "
        <div class='fb-like'
            data-send='{$c->dataSend}'
            data-layout='{$c->layout}'
            data-width='{$c->width}'
            data-show-faces='{$c->showFaces}'>
        </div>
        ";

        return $text;
    }
}