<?
class CP_Admin_Modules_Project_QuoteCategory_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('project_quoteCategory');
        $modules->registerModule($modObj, array(
        ));
    }
}
