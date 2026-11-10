<?
class CP_Www_Widgets_Ecommerce_AddToCart_View extends CP_Common_Lib_WidgetViewAbstract
{
    var $jssKeys = array();

    //========================================================//
    function getWidget() {
        $tv = Zend_Registry::get('tv');
        $ln = Zend_Registry::get('ln');
        $cpCfg = Zend_Registry::get('cpCfg');
        $modulesArr = Zend_Registry::get('modulesArr');
        $c = &$this->controller;

        $row = $c->record;
        $keyField  = $modulesArr[$c->modName]['keyField'];
        $tableName = $modulesArr[$c->modName]['tableName'];

        //print_r($row);
        //print "<hr>";

        $basket = $cpCfg['cp.basketArray'][$c->modName];

        $text = '';
        if ($row[$basket['priceFld']] > 0){

            if ($row[$basket['stockFld']] > 0){
                $url = "{$c->url}&modName={$c->modName}&record_id={$row[$keyField]}";
                $url .= $c->childId  != '' ? "&child_id={$c->childId}" : '';
                $text = "
                <div class='{$c->btnStyle}'>
                    <a href='javascript:void(0);' url='{$url}' class='ic-cart'>
                        <span>{$ln->gd($c->btnLabel)}</span>
                    </a>
                </div>
                ";
            } else {
                $text = "
                <div class='{$c->btnStyleNoStock}'>
                    <span>{$ln->gd($c->btnLabelNoStock)}</span>
                </div>
                ";
            }
        }

        return $text;
    }

    //========================================================//
    function getRowsHTML() {
    }

    /**
     *
     */
    function getViewBasketText() {
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $cpUrl = Zend_Registry::get('cpUrl');

        $url = $cpUrl->getUrlBySecType('Basket');
        $text = "
        <div class='btnViewCart'>
            <a href='{$url}'>
                <span>{$ln->gd('w.ecommerce.addToCart.lbl.viewBasket')}</span>
            </a>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getLoginLink() {
        $cpCfg = Zend_Registry::get('cpCfg');

		/** if there is a hook in the current then use that **/
		$theme = getCPThemeObj($cpCfg['cp.theme']);

		if (method_exists($theme->fns, 'getLoginLink')){
			return $theme->fns->getLoginLink();
		}

        $ln = Zend_Registry::get('ln');
        $cpUrl = Zend_Registry::get('cpUrl');
        $loginUrl = $cpUrl->getUrlBySecType('Login');
        $text = "
        <a class='btnLogin' href='{$loginUrl}'>
            <span>{$ln->gd('w.member.loginForm.form.lbl.login')}</span>
        </a>
        ";
        return $text;
    }

}