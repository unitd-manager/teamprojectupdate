<?
class CP_Admin_Modules_Pos_SystemSettings_Functions
{
    function setModuleArray($modules){
        $fn = Zend_Registry::get('fn');

        $modObj = $modules->getModuleObj('pos_systemSettings');
        $modObj['tableName'] = 'setting';
        $modObj['keyField']  = 'setting_id';
        if ($fn->isDeveloper()){
            $modules->registerModule($modObj, array(
                 'title' => 'System Setting'
                ,'depModulesForJSS' => array('pos_globalSettings')
                ,'hasFlagInList' => false
               ,'tableName'     => 'setting'
               ,'keyField'      => 'setting_id'
            ));
        } else {
            $modules->registerModule($modObj, array(
                 'title' => 'System Setting'
                ,'actBtnsList' => array()
                ,'depModulesForJSS' => array('pos_globalSettings')
                ,'hasFlagInList' => false
               ,'tableName'     => 'setting'
               ,'keyField'      => 'setting_id'
            ));
        }
    }
}