<?
class CP_Admin_Modules_Ecommerce_OrderItemLink_Model extends CP_Common_Lib_ModuleLinkModelAbstract
{
    /**
     *
     */
    function getFields(){
        $fn = Zend_Registry::get('fn');
        $modulesArr = Zend_Registry::get('modulesArr');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'record_id'); 
        $fa = $fn->addToFieldsArray($fa, 'qty');
        $fa = $fn->addToFieldsArray($fa, 'unit_price');
        $fa = $fn->addToFieldsArray($fa, 'module', 'ecommerce_product');

        $keyField   = $modulesArr[$fa['module']]['keyField'];
        $tableName  = $modulesArr[$fa['module']]['tableName'];
        $titleField = $modulesArr[$fa['module']]['titleField'];

        $rec = $fn->getRecordRowByID($tableName, $keyField, $fa['record_id']);
        $fa['item_title'] = $rec[$titleField];
                
        return $fa;
    }

    /**
     *
     */
    function getAddNewGridItem(){
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        $fa = $this->getFields();
        $fa['order_id'] = $tv['srcRoomId'];
        $id = $fn->addRecord($fa);
    }

    /**
     *
     */
    function getSaveGridItem(){
        $fn = Zend_Registry::get('fn');
        
        $fa = $this->getFields();
        $id = $fn->saveRecord($fa);
    }
}