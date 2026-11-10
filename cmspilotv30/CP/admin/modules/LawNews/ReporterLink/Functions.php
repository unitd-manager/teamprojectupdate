<?
class CP_Admin_Modules_LawNews_ReporterLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('lawNews_reporterLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'reporter'
           ,'keyField'  => 'reporter_id'
        ));
    }
     /**
     *
     */
}
