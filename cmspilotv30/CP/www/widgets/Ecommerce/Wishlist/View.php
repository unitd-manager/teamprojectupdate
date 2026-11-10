<?
class CP_Www_Widgets_Ecommerce_Wishlist_View extends CP_Common_Lib_WidgetViewAbstract
{
    var $jssKeys = array();

    //========================================================//
    function getWidget() {
        $tv = Zend_Registry::get('tv');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $modulesArr = Zend_Registry::get('modulesArr');
        $c = &$this->controller;

        $row = $c->record;
        $keyField  = $modulesArr[$c->modName]['keyField'];
        $tableName = $modulesArr[$c->modName]['tableName'];

        $addUrl = "{$c->url}&modName={$c->modName}&product_id={$row[$keyField]}";
        $addBtn = "
        <div class=''>
            <a href='javascript:void(0);' url='{$addUrl}' class='addToWishBtn'>
                <span>{$ln->gd($c->btnLabel)}</span>
            </a>
        </div>
        ";      
                
        $text = '';
        if(!isLoggedInWWW()){
            $text = $addBtn;
        } else {
            $product_id = $row[$keyField];
            $contact_id = $fn->getSessionParam('cpContactId');
            $rec = $fn->getRecordByCondition('wishlist', "product_id = {$product_id} AND contact_id = {$contact_id}");
            
            if (!is_array($rec)){
                $text = $addBtn;
            } else {
                $removeUrl = "{$c->removeUrl}&modName={$c->modName}&product_id={$row[$keyField]}";
                $removeBtn = "
                <div class=''>
                    <a href='javascript:void(0);' url='{$removeUrl}' class='removeFromWishBtn'>
                        <span>{$ln->gd($c->btnLabelRemove)}</span>
                    </a>
                </div>
                ";                  
                $text = $removeBtn;
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