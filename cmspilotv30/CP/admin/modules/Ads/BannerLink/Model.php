<?
class CP_Admin_Modules_Ads_BannerLink_Model extends CP_Common_Lib_ModuleLinkModelAbstract
{
    /**
     *
     */
    function getFields(){
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'record_id'); 
        $fa = $fn->addToFieldsArray($fa, 'banner_id');
        $fa = $fn->addToFieldsArray($fa, 'module');
        $fa = $fn->addToFieldsArray($fa, 'banner_position');
        $fa = $fn->addToFieldsArray($fa, 'sort_order');
        $fa = $fn->addToFieldsArray($fa, 'published', 0);
        return $fa;
    }

    //==================================================================//
    function getAddNewGridItem(){
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        $fa = $this->getFields();
        $fa['module']    = $tv['srcRoom'];
        $fa['record_id'] = $tv['srcRoomId'];
        $id = $fn->addRecord($fa, 'banner_link');
    }

    //==================================================================//
    function getSaveGridItem(){
        $fn = Zend_Registry::get('fn');
        $fa = $this->getFields();
        $id = $fn->saveRecord($fa);
    }
}