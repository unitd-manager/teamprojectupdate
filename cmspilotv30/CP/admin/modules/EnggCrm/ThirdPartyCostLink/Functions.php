<?
class CP_Admin_Modules_EnggCrm_ThirdPartyCostLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('enggCrm_thirdPartyCostLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'third_party_cost'
           ,'keyField'  => 'third_party_cost_id'
        ));
    }
}
