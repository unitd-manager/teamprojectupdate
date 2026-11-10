<?
class CP_Www_Themes_Herbal2_View extends CP_Www_Lib_ThemeViewAbstract
{
    var $jssKeys = array('cufon', 'cufon-diavlo', 'jqNivoSlider-2.5.1');
    
    /**
     *
     */
    function getHeaderPanel(){
        $tv = Zend_Registry::get('tv');
        $ln = Zend_Registry::get('ln');
        $cpUrl = Zend_Registry::get('cpUrl');

        /** create an instance of the widget **/
        $pSiteSearch = getCPPluginObj('common_siteSearch');
        $pLogin = getCPPluginObj('member_login');
        $wBasket = getCPWidgetObj('ecommerce_addToCart');
        $stockistUrl = $cpUrl->getUrlByCatType('Stockist');

        $text = "
        {$pLogin->view->getLoginInfoText()}
        <div class='btnStockist'>
            <a href='{$stockistUrl}'>
                <span>{$ln->gd('m.ecommerce.stockist.form.new.heading')}</span>
            </a>
        </div>
        {$wBasket->view->getViewBasketText()}
        ";

        return $text;
    }

    /**
     *
     */
    function getNavPanel(){
        $cpCfg = Zend_Registry::get('cpCfg');

		$text = '';
		if ($cpCfg['cp.showMainNavPanelAtTop']){
		    $mainNav = Zend_Registry::get('mainNav');
            $widget = "{$mainNav->getWidget(array(
                 'btnPos' => 'Top'
            ))}
            ";

			$text = "
	        <div id='nav'>
	            <a id='navigation' name='navigation'></a>
	            {$widget}
	        </div>
			";
		}
        return $text;
    }
}