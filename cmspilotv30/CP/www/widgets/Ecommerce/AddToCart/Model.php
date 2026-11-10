<?
class CP_Www_Widgets_Ecommerce_AddToCart_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getAdd() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUrl = Zend_Registry::get('cpUrl');
        $cpCfg = Zend_Registry::get('cpCfg');

        $session_id = session_id();

        $basket_id = $fn->getReqParam('basket_id', '', true);
        $notes     = $fn->getReqParam('notes', '', true);
        $record_id = $fn->getReqParam('record_id', '', true);
        $module    = $fn->getReqParam('modName', '', true);
        $notes     = $fn->getReqParam('notes', '', true);
        $child_id = $fn->getReqParam('child_id');

        $appendSQL = ($child_id != '') ? "AND child_id = {$child_id}" : '';

        if ($basket_id != ''){
            $rec = $fn->getRecordByCondition('basket', "basket_id = {$basket_id} AND session_id = '{$session_id}'");

            if (!is_array($rec)){
                exit('invalid access');
            } else {
                $module    = $rec['module'];
                $record_id = $rec['record_id'];
            }

        } else {
            $rec = $fn->getRecordByCondition('basket', "module = '{$module}' {$appendSQL} AND session_id = '{$session_id}' AND record_id = '{$record_id}'");
        }

       if (is_array($rec)){
            $qty = $fn->getReqParam('qty', 1, true);
            $date = date("Y-m-d H:i:s");
            if ($qty == 0){
                $SQL = "
                DELETE FROM basket
                WHERE session_id = '{$session_id}'
                AND basket_id = '{$basket_id}'
                ";
                $result = $db->sql_query($SQL);
            } else {
                $fa = array();
                $fa['qty'] = $qty;

                if ($notes != '') {
                    $fa['notes'] = $notes;
                }

                $fn->saveRecord($fa, 'basket', '', '', array(
                    'customWhereCondn' => "session_id = '{$session_id}' AND basket_id = '{$basket_id}'"
                ));
            }

        } else {
            $fa = $this->getFields();
            $fa = $fn->addCreationDetailsToFieldsArray($fa, 'basket');
            $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'basket');
            $result = $db->sql_query($SQL);
        }

        $basketArray = $cpCfg['cp.basketArray'][$module];
        $url = $cpUrl->getUrlBySecType($basketArray['basketSecType']);

        $arr = array('basketUrl' => $url);
        return json_encode($arr);
    }

    /**
     *
     */
    function getFields(){
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $modulesArr = Zend_Registry::get('modulesArr');

        $c = &$this->controller;
        $module = $fn->getReqParam('modName', '', true);
        $basket = $cpCfg['cp.basketArray'][$module];

        $fa = array();
        $fa['module']     = $fn->getReqParam('modName');
        $fa['record_id']  = $fn->getReqParam('record_id');
        $fa['session_id'] = session_id();
        $fa['qty']        = $fn->getReqParam('qty', 1);

        $notes = $fn->getReqParam('notes');
        if ($notes != ''){
            $fa['notes'] = $notes;
        }

        $child_id = $fn->getReqParam('child_id');
        if ($child_id != ''){
            $fa['child_id'] = $child_id;
        }

        $keyField  = $modulesArr[$fa['module']]['keyField'];
        $tableName = $modulesArr[$fa['module']]['tableName'];
        $rec = $fn->getRecordRowByID($tableName, $keyField, $fa['record_id']);

        $fa['unit_price'] = $rec[$basket['priceFld']];

        return $fa;
    }
}