<?
class CP_Admin_Modules_Project_CostingLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('project_costingLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'costing'
           ,'keyField'  => 'costing_id'
        ));
    }
}
