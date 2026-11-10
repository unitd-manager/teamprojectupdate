<?
class CP_Www_Modules_Ecommerce_Basket_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    //==================================================================//
    function getController() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUrl = Zend_Registry::get('cpUrl');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');

        $hook = getCPModuleHook('ecommerce_basket', 'controller', $this);
        if($hook['status']){
            return $hook['html'];
        }

        if ($tv['secType'] == 'Basket'){
            $text = '';
            $modName = $fn->getReqParam('modName', $cpCfg['cp.defaultShoppingCartModule']);
            $basket = $cpCfg['cp.basketArray'][$modName];

            if ($tv['catType'] == 'Shipping Details' || ($tv['catType'] == '' && $basket['shipShowItemsList'])){
                if ($cpCfg['cp.basketArray'][$modName]['loginRequired']){
                    $_SESSION['cpReturnUrlAfterLogin'] = $_SERVER['REQUEST_URI'];
                    checkLoggedIn();
                }
                $wShip = getCPWidgetObj('ecommerce_shippingDetails');
                $text = $wShip->getWidget(array(
                     'modName' => $modName
                    ,'showConfirmEmail' => $cpCfg['cp.basketArray'][$modName]['shipShowConfirmEmail']
                    ,'hasAcceptTermsCbox' => $cpCfg['cp.basketArray'][$modName]['shipShowAcceptTermsCbox']
                ));

            } else if ($tv['catType'] == 'Confirm Order'){
                //confirm order page display
                if ($cpCfg['cp.basketArray'][$modName]['loginRequired']){
                    checkLoggedIn();
                }
                $wConfirm= getCPWidgetObj('ecommerce_confirmOrder');
                $text = $wConfirm->getWidget(array(
                    'modName' => $modName
                ));

            } else if ($tv['catType'] == 'Order Success'){
                $text = $this->view->getOrderSuccess();

            } else if ($tv['catType'] == 'Order Cancel'){
                $text = $this->view->getOrderFail();

            } else {
                $wBasket = getCPWidgetObj('ecommerce_basket');
                $text = $wBasket->getWidget(array(
                    'modName' => $modName
                ));
                // To show the all the details in basket (view cart) applied for dealon site
                if ($cpCfg['m.ecommerce.basket.showAllDetailsInBasket']= false){
                    if ($cpCfg['cp.basketArray'][$modName]['loginRequired']){
                        //checkLoggedIn();
                    }
                    if (isLoggedInWWW()){
                        $wShip = getCPWidgetObj('ecommerce_shippingDetails');
                        $text .= $wShip->getWidget(array(
                            'modName' => $modName
                        ));
                        /*
                        $text .="
                        <div class='mt10'>
                            <a href='/index.php?module=ecommerce_basket&_spAction=voucherpdf&showHTML=0'>CHECK THE PDF</a>
                        </div>
                        ";
                        */
                    }
                    else{
                        $wLogin = getCPWidgetObj('member_loginForm');
                        $baksetUrl = $cpUrl->getUrlBySecType('Basket');
                        $text .= $wLogin->getWidget(array(
                            'returnUrl' => $baksetUrl
                           ,'hasRegiserInfo' => false
                        ));

                        $wRegister = getCPWidgetObj('member_registerForm');
                        $text .= $wRegister->getWidget(array(
                            'returnUrl' => $baksetUrl
                        ));
                    }
                }
            }

            return $text;

        } else if ($tv['secType'] == 'Order Form' || $tv['catType'] == 'Order Form'){
            $wOrderForm = getCPWidgetObj('ecommerce_orderForm');
            return $wOrderForm->getWidget(array(
            ));

        } else {
            checkLoggedIn();

            $fnName = $fn->getFnNameByAction();
            $text = $this->$fnName();
            return $text;
        }
    }

    //==================================================================//
    function getConfirmOrder() {
        return $this->model->getConfirmOrder();
    }
    //==================================================================//
    function getVoucherPdf() {
        return $this->model->getVoucherPdf();
    }
}