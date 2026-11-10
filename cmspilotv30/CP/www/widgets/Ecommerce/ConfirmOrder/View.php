<?
class CP_Www_Widgets_Ecommerce_ConfirmOrder_View extends CP_Common_Lib_WidgetViewAbstract
{
    /**
     *
     */
    function getWidget($exp = array()){
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUrl = Zend_Registry::get('cpUrl');
        $formObj  = Zend_Registry::get('formObj');
        $c = &$this->controller;
        
        $rowsHTML = $this->getRowsHTML();
        $formObj->mode = 'detail';
        
        $basketArray = $cpCfg['cp.basketArray'][$c->modName];

        $wShip = getCPWidgetObj('ecommerce_shippingDetails');
        $wBasket = getCPWidgetObj('ecommerce_basket');
        
        $basket = '';
        if ($c->showBasket){
            $basket = "
            {$wBasket->getWidget(array(
                 'mode' => 'detail'
                ,'modName' => $c->modName
            ))}
            ";
        }
        
        $text = "
        <h1>{$ln->gd($c->heading)}</h1>
        {$basket}
        {$wShip->getWidget(array(
             'modName' => $c->modName
            ,'mode' => 'detail'
            ,'showItemsList' => false
            ,'showCaptcha' => false
        ))}
        {$this->getButtons()}
        ";

        return $text;
    }

    /**
     *
     */
    function getButtons() {
        $hook = getCPWidgetHook('ecommerce_confirmOrder', 'buttons', $this->controller);
        if($hook['status']){
            return $hook['html'];
        }

        $ln = Zend_Registry::get('ln');
        $c = &$this->controller;
        $cpUrl = Zend_Registry::get('cpUrl');
        $cpCfg = Zend_Registry::get('cpCfg');

        $basketArray = $cpCfg['cp.basketArray'][$c->modName];
        $shopUrl = $cpUrl->getUrlBySecType($basketArray['sectionType']);
        $shipUrl = $cpUrl->getUrlByCatType('Shipping Details', $basketArray['basketSecType']);
        $confirmUrl = '/index.php?module=ecommerce_basket&_spAction=confirmOrder';

        $text = "
        <div class='floatbox shopBtns' modName='{$c->modName}'>
            <div class='float_left button btnContinueShopping'>
                <a href='{$shopUrl}'>
                    {$ln->gd($c->continueShopping)}
                </a>
            </div>
            <div class='float_right button btnConfirmOrder'>
                <a href='{$confirmUrl}'>
                    {$ln->gd($c->confirmOrder)}
                </a>
            </div>
        </div>
        ";

        return $text;
    }
}