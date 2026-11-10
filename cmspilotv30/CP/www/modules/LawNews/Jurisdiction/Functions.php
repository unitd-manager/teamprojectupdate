<?
class CP_Www_Modules_LawNews_Jurisdiction_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('lawNews_jurisdiction');
        $modules->registerModule($modObj, array(
             'tableName' => 'jurisdiction'
            ,'keyField'  => 'jurisdiction_id'
            ,'listLimit'  => 200            
        ));
    }
}
