<?
class CP_Admin_Modules_Logistics_VehicleLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('logistics_vehicleLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'vehicle'
           ,'keyField'  => 'vehicle_id'
        ));
    }
}
