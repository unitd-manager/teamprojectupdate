<?
class CP_Admin_Modules_Directory_City_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $actBtnsList = array('new', 'export');
        if($cpCfg['cp.showDeleteActionBtnInList']){
            $actBtnsList[] = 'deleteList';
        } 
        
        $modObj = $modules->getModuleObj('directory_city');
        $modules->registerModule($modObj, array(
        	'hasFlagInList' => 0
           ,'actBtnsList' => $actBtnsList
           ,'hasMultiLang' => 1
        ));
    }
}