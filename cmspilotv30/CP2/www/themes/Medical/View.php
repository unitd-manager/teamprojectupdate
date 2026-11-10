<?
class CP_Www_Themes_Medical_View extends CP_Www_Lib_ThemeViewAbstract
{
    var $jssKeys = array('cufon', 'cufon-diavlo', 'jqNivoSlider-2.5.1');

    /**
     *
     */
    function getHeaderPanel(){
    }


    /**
     *
     */
    function getBodyPanel() {
        $tv = Zend_Registry::get('tv');
        $clsInst = Zend_Registry::get('currentModule');

        $actionName = ($tv['action']) != '' ? ucfirst($tv['action']) : 'List';
        $actionTemp  = "get{$actionName}";  //eg: getList

        if (!method_exists($clsInst, $actionTemp)) {
            $clsName = ucfirst($tv['module']);
            print "<h3>{$clsName}->{$actionTemp} does not exist";
            exit();
        }
        
        $extra = '';
        if ($tv['secType'] != 'Home'){
            $pLogin = getCPPluginObj('member_login');
            $wBasket = getCPWidgetObj('ecommerce_addToCart');

            $extra = "
            {$pLogin->view->getLoginInfoText()}
            {$wBasket->view->getViewBasketText()}
            ";
        }
        
        $text = "
        <div class='bodyPanel'>
            {$clsInst->getController()}
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getFooterPanel(){
        $ln = Zend_Registry::get('ln');
        
        $text = "
        <div class='floatbox'>
            <div class='float_left'>
                {$ln->gd('cp.footer.leftText')}
            </div>
            <div class='float_right'>
                {$ln->gd('cp.footer.rightText')}
            </div>
        </div>
        
        ";

        return $text;
    }
}