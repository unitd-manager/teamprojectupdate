<?
class CP_Www_Widgets_Ecommerce_ConfirmOrder_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){
        $modulesArr = Zend_Registry::get('modulesArr');
        $c = &$this->controller;
        $tableName = $modulesArr[$c->modName]['tableName'];
        $keyFld = $modulesArr[$c->modName]['keyField'];
        $titleFld = $modulesArr[$c->modName]['titleField'];

        $SQL = "
        SELECT b.*
              ,(b.qty*unit_price) AS total_price
              ,p.{$titleFld} AS title
        FROM basket b
        LEFT JOIN {$tableName} p ON (p.{$keyFld} = b.record_id)
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

        $searchVar->mainTableAlias = 'b';
        $session_id = session_id();

        $searchVar->sqlSearchVar[] = "b.module = '{$c->modName}'";
        $searchVar->sqlSearchVar[] = "b.session_id = '{$session_id}'";

        $searchVar->sortOrder = "p.title";
    }

    /**
     *
     */
    function getDataArray(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modelHelper = Zend_Registry::get('modelHelper');
        $c = &$this->controller;
        $basket = $cpCfg['cp.basketArray'][$c->modName];
        $c->showBasket         = ($c->showBasket != '')          ? $c->showBasket         : $basket['showBasketInConfirmOrder'];
        $c->showPaymentMethods = ($c->showPaymentMethods !== '') ? $c->showPaymentMethods : $basket['showPaymentMethods'];
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'ecommerce_basket');

        $this->dataArray = $dataArray ;
        return $dataArray;
    }

    /**
     *
     */
    function getEmptyBasket(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $modName = $fn->getReqParam('modName', '', true);
        if ($modName == ''){
            return;
        }

        $session_id = session_id();
        $SQL = "
        DELETE FROM basket
        WHERE session_id = '{$session_id}'
          AND module = '{$modName}'
        ";

        $result = $db->sql_query($SQL);
    }

}