<?
class CP_Admin_Modules_Ecommerce_ProductVoucherLink_Model extends CP_Common_Lib_ModuleLinkModelAbstract
{

    /**
     *
     */
    function getFields(){
        $ln = Zend_Registry::get('ln');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'product_id');
        $fa = $fn->addToFieldsArray($fa, 'voucher_no');
        
        return $fa;
    }

    //==================================================================//
    function getAddNewGridItem(){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        $fa = $this->getFields();
        $rand_no = mt_rand();
        $fa['product_id']    = $tv['srcRoomId'];
        $fa['voucher_no']    = $rand_no;
        $id = $fn->addRecord($fa);
    }

    //==================================================================//
    function getSaveGridItem(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        
        $fa = $this->getFields();
        $id = $fn->saveRecord($fa);
    }

    //==================================================================//
}
