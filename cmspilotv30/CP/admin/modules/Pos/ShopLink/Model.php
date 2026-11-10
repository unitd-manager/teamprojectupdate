<?
class CP_Admin_Modules_Pos_ShopLink_Model extends CP_Common_Lib_ModuleLinkModelAbstract
{
    /**
     *
     */
    function getFields() {
        $fn = Zend_Registry::get('fn');
        $modulesArr = Zend_Registry::get('modulesArr');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'shop_id');
        $fa = $fn->addToFieldsArray($fa, 'list_price');
        $fa = $fn->addToFieldsArray($fa, 'currency');

        return $fa;
    }
    /**
     *
     */
    function getAddNewGridItem(){
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $record_id = $fn->getReqParam('product_shop_id');

        $fa = $this->getFields();
        $fa['product_id'] = $tv['srcRoomId'];

        $id = $fn->addRecord($fa, 'product_shop');
    }

    /**
     *
     */
    function getSaveGridItem(){
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $record_id = $fn->getReqParam('product_shop_id');
        
        $fa = $this->getFields();

        $shop = $fn->getRecordRowByID('shop', 'shop_id', $fa['shop_id']);
        $fa['currency'] = $shop['currency'];
        $id = $fn->saveRecord($fa);
    }

}
