<?
class CP_Admin_Modules_Ecommerce_RatingLink_Model extends CP_Common_Lib_ModuleLinkModelAbstract
{
    /**
     *
     */
    function getFields(){
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'source');
        $fa = $fn->addToFieldsArray($fa, 'rating');

        return $fa;
    }

    /**
     *
     */
    function getAddNewGridItem(){
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        $fa = $this->getFields();
        $fa['product_id'] = $tv['srcRoomId'];
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