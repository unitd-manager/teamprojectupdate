<?
class CP_Admin_Modules_Tradingsg_PurchaseOrderLink_Model extends CP_Common_Lib_ModuleLinkModelAbstract
{

    /**
     *
     */
    function getSQL() {

        $product = getCPModelObj('tradingsg_purchaseOrder');
        $SQL = $product->getSQL();

        return $SQL;
    }

    /**
     *
     */
    function getFields(){
        $ln = Zend_Registry::get('ln');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'po_code');
        $fa = $fn->addToFieldsArray($fa, 'supplier_id');
        $fa = $fn->addToFieldsArray($fa, 'status');

        return $fa;
    }

    /**
     *
     */
    function getAddNewGridItem(){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        $fa = $this->getFields();
        $fa['quote_id']    = $tv['srcRoomId'];
        $id = $fn->addRecord($fa);
    }

    /**
     *
     */
    function getSaveGridItem(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        $fa = $this->getFields();
        $id = $fn->saveRecord($fa);
    }
}
