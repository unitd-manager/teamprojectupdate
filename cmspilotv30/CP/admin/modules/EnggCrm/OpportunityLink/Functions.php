<?
class CP_Admin_Modules_EnggCrm_OpportunityLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('enggCrm_opportunityLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'opportunity'
           ,'keyField'  => 'opportunity_id'
        ));
    }
}
