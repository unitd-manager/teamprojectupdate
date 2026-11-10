<?
class CP_Www_Modules_WebBasic_Home_Functions
{

    //==================================================================//
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('webBasic_home');
        $modules->registerModule($modObj, array(
        ));
    }

    //==================================================================//
    function getSQL() {
        $cpCfg = Zend_Registry::get('cpCfg');

        $fnMod = includeCPClass('ModuleFns', 'webBasic_content');
        return $fnMod->getSQL();
    }

}
