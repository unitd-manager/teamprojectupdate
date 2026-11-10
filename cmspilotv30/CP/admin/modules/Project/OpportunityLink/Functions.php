<?
class CP_Admin_Modules_Project_OpportunityLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('project_opportunityLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'opportunity'
           ,'keyField'  => 'opportunity_id'
        ));
    }
}
