<?
class CP_Admin_Modules_Common_SiteLink_Model extends CP_Common_Lib_ModuleLinkModelAbstract
{
    /**
     *
     */
    function getFields(){
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'record_id');
        $fa = $fn->addToFieldsArray($fa, 'site_id');
        $fa = $fn->addToFieldsArray($fa, 'module');
        $fa = $fn->addToFieldsArray($fa, 'published', 0);
        $fa = $fn->addToFieldsArray($fa, 'price');
        $fa = $fn->addToFieldsArray($fa, 'top_seller');
        $fa = $fn->addToFieldsArray($fa, 'special_price');
        return $fa;
    }

    //==================================================================//
    function getAddNewGridItem(){
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        $fa = $this->getFields();
        $fa['module']    = $tv['srcRoom'];
        $fa['record_id'] = $tv['srcRoomId'];
        $id = $fn->addRecord($fa, 'site_link');
    }

    //==================================================================//
    function getSaveGridItem(){
        $fn = Zend_Registry::get('fn');
        $fa = $this->getFields();
        $id = $fn->saveRecord($fa);
    }
}