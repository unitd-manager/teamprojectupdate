<?
class CP_Admin_Modules_LawNews_YearLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('lawNews_yearLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'correspondent_year'
           ,'keyField'  => 'correspondent_year_id'
        ));
    }
}
