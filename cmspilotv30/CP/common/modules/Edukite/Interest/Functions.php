<?
class CP_Common_Modules_Edukite_Interest_Functions
{
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        
        $modObj = $modules->getModuleObj('edukite_interest');
        $modules->registerModule($modObj, array(
        ));
    }

    /**
     *
     */
    function setLinksArray($inst) {
    }
}