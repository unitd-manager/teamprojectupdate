<?
class CP_Www_Widgets_Ecommerce_PaymentMethods_View extends CP_Common_Lib_WidgetViewAbstract
{
    
    /**
     *
     */
    function getWidget() {
        $ln = Zend_Registry::get('ln');
        $c = &$this->controller;

        $text = "
        <h1>{$ln->gd($c->caption)}</h1>
        <table class='thinlist'>
            {$this->getRowsHTML()}
        </table>
        ";

        return $text;
    }

    /**
     *
     */
    function getRowsHTML() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $c = &$this->controller;

        $basketArray = $cpCfg['cp.basketArray'][$c->modName];
        
        $selected = (isset($_SESSION['shippingDetails']['payment_method'])) ? 
                  $_SESSION['shippingDetails']['payment_method'] : 
                  $basketArray['defaultPaymentMethod'];

        $rows = '';
        $index = 1;

        if ($c->mode == 'detail'){
            $infoArr = getCPPluginObj('paymentMethods_' . $selected)->model->getDataArray();
            $rows = "
            <tr>
                <td colspan='2'>
                    {$infoArr['title']}
                </td>
            </tr>
            ";
            
            return $rows;
        }

        foreach($this->model->dataArray AS $value) {
            $infoArr = getCPPluginObj('paymentMethods_' . $value)->model->getDataArray();
            $checked = ($value == $selected) ? " checked='checked'" : '';
            
            $rows .= "
            <tr>
                <td class='method'>
                    <input id='payment_method_{$index}' type='radio' value='{$value}' name='payment_method'{$checked}>
                    <label for='payment_method_{$index}'>{$infoArr['title']}</label>
                </td>
                <td>{$infoArr['title']}</td>
            </tr>
            ";
            
            $index++;
        }

        return $rows;
    }
}