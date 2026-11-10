<?
class CP_Admin_Modules_Museum_FacilityAvailabilityLink_Functions
{
    //==================================================================//
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('museum_facilityAvailabilityLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'facility_availability'
           ,'keyField'  => 'facility_availability_id'
        ));
    }
}
