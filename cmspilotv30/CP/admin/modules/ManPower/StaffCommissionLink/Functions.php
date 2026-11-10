<?
class CP_Admin_Modules_ManPower_StaffCommissionLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('manPower_staffCommissionLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'staff_commission'
           ,'keyField'  => 'staff_commission_id'
           ,'mainModuleName'  => 'manPower_opportunity'
        ));
    }
}
