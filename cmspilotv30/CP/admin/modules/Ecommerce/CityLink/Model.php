<?
class CP_Admin_Modules_Ecommerce_CityLink_Model extends CP_Common_Lib_ModuleLinkModelAbstract
{
    /**
     *
     */
    function getFields(){
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'city_id'); 
        $fa = $fn->addToFieldsArray($fa, 'stock'); 
        $fa = $fn->addToFieldsArray($fa, 'stock_threshold'); 
        $fa = $fn->addToFieldsArray($fa, 'published'); 
                
        return $fa;
    }

    /**
     *
     */
    function getAddNewGridItem(){
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        $fa = $this->getFields();
        $fa['product_country_id'] = $tv['srcRoomId'];
        $id = $fn->addRecord($fa, 'product_city');
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