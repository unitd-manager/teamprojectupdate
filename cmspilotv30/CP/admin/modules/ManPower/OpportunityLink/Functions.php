<?
class CP_Admin_Modules_ManPower_OpportunityLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('manPower_opportunityLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'opportunity'
           ,'keyField'  => 'opportunity_id'
           ,'mainModuleName'  => 'manPower_opportunity'
        ));
    }
}
