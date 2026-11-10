<?
class CP_Www_Widgets_Ecommerce_Orders_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){
        $SQL = "
        SELECT o.*
              ,(SELECT (SUM(oi.unit_price * oi.qty) + o.shipping_charge)
               FROM order_item oi
               WHERE oi.order_id = o.order_id
               ) AS order_amount        
        FROM `order` o
        ";
        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $searchVar = $this->searchVar;
        $fn = Zend_Registry::get('fn');
        $c = &$this->controller;
        $order_id = $fn->getReqParam('order_id');
        
        $rowContact = $fn->getRecordRowByID('contact', 'contact_id', $_SESSION['cpContactId']);
        $searchVar->sqlSearchVar[] = "o.organization_id = {$rowContact['organization_id']}";
        
        if($order_id != ''){
            $searchVar->sqlSearchVar[] = "o.order_id = {$order_id}";
        }

        $searchVar->sortOrder = $c->orderBy;
    }

    /**
     *
     */
    function getDataArray(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modulesArr = Zend_Registry::get('modulesArr');
        $c = &$this->controller;
        $basket = $cpCfg['cp.basketArray'][$c->modName];
        
        $c->currency        = ($c->currency        != '') ? $c->currency        : $basket['currency'];
        $c->decimals        = ($c->decimals        != '') ? $c->decimals        : $basket['decimals'];
        $c->keyFldName      = ($c->keyFldName      != '') ? $c->keyFldName      : $modulesArr[$c->modName]['keyField'];
        $c->currencyDisplay = ($c->currencyDisplay != '') ? $c->currencyDisplay : $basket['currencyDisplay'];

        $modelHelper = Zend_Registry::get('modelHelper');
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'ecommerce_productList');

        $this->dataArray = $dataArray ;
        return $dataArray;
    }

    /**
     *
     */
    function getUpdateAmountPaid(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        checkLoggedIn();
        
        $order_id = $fn->getReqParam('order_id', '', true);
        $amount_paid  = $fn->getReqParam('amount_paid', '', true);
        
        if ($order_id == ''){
            return;
        }
        
        $SQL = "
        UPDATE `order`
        SET amount_paid = '{$amount_paid}'
        WHERE order_id = {$order_id}
        ";
        
        $result = $db->sql_query($SQL);
    }
}