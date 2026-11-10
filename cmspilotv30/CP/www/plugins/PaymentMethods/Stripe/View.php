<?
class CP_Www_Plugins_PaymentMethods_Stripe_View extends CP_Common_Lib_PluginViewAbstract
{
    /**
     *
     */
    function getView($order_id, $contact_id) {
        $formObj = Zend_Registry::get('formObj');
        $ln      = Zend_Registry::get('ln');
        $fn      = Zend_Registry::get('fn');
        $cpCfg   = Zend_Registry::get('cpCfg');
        $c = &$this->controller;

        $theme = getCPThemeObj($cpCfg['cp.theme']);
        $headerPanel = $theme->view->getHeaderPanel();
        $footerPanel = $theme->view->getFooterPanel();
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
        <div class='container'>
            <h1>{$ln->gd($c->heading)}</h1>
        </div>
        
        <div class='mb20'>
          {$basket}
        </div>

        {$wShip->getWidget(array(
             'modName' => $c->modName
            ,'mode' => 'detail'
            ,'showItemsList' => false
            ,'showCaptcha' => false
        ))}

        <div class='container'>
          <div class='col-md-12 noPadding'>
            <!-- Stripe Payment Form Starts Here -->
            <div class='col-md-5 col-sm-6 col-xs-12 stripePaymentForm'>
              <img src='/www/images/stripe-badge-transparent.png'>
              <form action='/index.php?plugin=paymentMethods_stripe&_spAction=stripeFormSubmit&showHTML=0' method='post' id='payment-form'>
                <div class='form-row'>
                  <label for='card-element'>
                    Credit or debit card
                  </label>
                  <div id='card-element'>
                  </div>

                  <div id='card-errors' role='alert'></div>
                </div>

                <button>Submit Payment</button>
              </form>
            </div>
            <input type='hidden' name='stripePublishableKey' value='{$cpCfg['cp.stripePaymentPublishableKey']}'/>
            <input type='hidden' name='order_id' value='{$order_id}'/>
            <input type='hidden' name='contact_id' value='{$contact_id}'/>
            <!-- Stripe Payment Form Ends Here -->
          </div>
        </div>  
        ";

        return $text;
    }
}