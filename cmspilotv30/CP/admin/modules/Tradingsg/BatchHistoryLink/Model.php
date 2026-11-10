<?
class CP_Admin_Modules_Tradingsg_BatchHistoryLink_Model extends CP_Common_Lib_ModuleLinkModelAbstract
{
    /**
     *
     */
    function getFields(){
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'record_id');
        $fa = $fn->addToFieldsArray($fa, 'product_id');
        $fa = $fn->addToFieldsArray($fa, 'qty');
        $fa = $fn->addToFieldsArray($fa, 'price');
        $fa = $fn->addToFieldsArray($fa, 'company_id');
        return $fa;
    }

    //==================================================================//
    function getAddNewGridItem(){
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');
        $cpUtil = Zend_Registry::get('cpUtil');
        $tv = Zend_Registry::get('tv');

        $fa = $this->getFields();

        $fa['module']    = $tv['srcRoom'];
        $fa['batch_import_id'] = $tv['srcRoomId'];
        $id = $fn->addRecord($fa, 'batch_history');
    }

    //==================================================================//
    function getSaveGridItem(){
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');
        $cpUtil = Zend_Registry::get('cpUtil');
        $tv = Zend_Registry::get('tv');

        $fa = $this->getFields();
        $id = $fn->saveRecord($fa);
    }
}