<?
class CP_Admin_Modules_LawNews_JurisdictionLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('lawNews_jurisdictionLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'jurisdiction'
           ,'keyField'  => 'jurisdiction_id'
        ));
    }
     /**
     *
     */
}
