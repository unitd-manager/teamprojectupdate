<?
class CP_Www_Themes_Edukloud_View extends CP_Www_Lib_ThemeViewAbstract
{
    
    /**
     *
     */
    function getHeaderPanel(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        /** create an instance of the widget **/
        $pSiteSearch = getCPPluginObj('common_siteSearch');

        $siteSearch = '';
        $logoText = '';
        $socialIcons = '';
        $cartText = '';
        $loginText = '';
        
        if ($cpCfg['cp.showLoginInfoAtTheTop']){
            $pLogin = getCPPluginObj('member_login');
            $loginText = "
            {$pLogin->view->getLoginInfoText()}
            ";
        }

        if ($cpCfg['cp.showSiteSearchAtTheTop']){
            $siteSearch = "
            {$pSiteSearch->view->getSearchBox()}
            ";
        }

        if ($cpCfg['cp.showLogoText']){
            $logoText = "<div class='logoText'>{$ln->gd('cp.logoText')}</div>";
        }

        if ($cpCfg['cp.showViewCartAtTheTop']){
            $wBasket = getCPWidgetObj('ecommerce_addToCart');
            $cartText = "
            {$wBasket->view->getViewBasketText()}
            ";
        }

        $text = "        
        <div class='headerInfo'>
        {$loginText}
        {$cartText}
        {$siteSearch}
        {$logoText}
        </div>
        ";

        return $text;
    }
    /*
     *
     */
    function getExtendedPanel() {
    }
        
}