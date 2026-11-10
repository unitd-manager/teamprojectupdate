<?
class CP_Admin_Modules_Ecommerce_ProductItemLink_Model extends CP_Common_Lib_ModuleLinkModelAbstract
{
    /**
     *
     */
    function getAddNewGridItem(){
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        $fa = $this->getFields();
        $fa['product_id'] = $tv['srcRoomId'];

        $arr = $fn->getArrBySQL("
            SELECT MAX(sort_order) AS max_sort
            FROM product_item
            "
            ,array(
                "product_id = '{$fa['product_id']}'"
            )
        );
        
        $fa['sort_order'] = $arr[0]['max_sort'] + 1;
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

    /**
     *
     */
    function getFields(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'sku_no');
        $fa = $fn->addToFieldsArray($fa, 'color_id');
        $fa = $fn->addToFieldsArray($fa, 'size');
        $fa = $fn->addToFieldsArray($fa, 'stock');

        return $fa;
    }
}
