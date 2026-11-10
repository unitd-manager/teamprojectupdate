<?
class CP_Admin_Modules_Pos_GlobalSettings_Functions
{
    function setModuleArray($modules){
        $fn = Zend_Registry::get('fn');
        $modObj = $modules->getModuleObj('pos_globalSettings');
        $modObj['tableName'] = 'setting';
        $modObj['keyField']  = 'setting_id';
        if ($fn->isDeveloper()){
            $modules->registerModule($modObj, array(
                'title'       => 'Global Setting'
               ,'actBtnsList' => array('new')
               ,'hasFlagInList' => false
               ,'tableName'     => 'setting'
               ,'keyField'      => 'setting_id'
            ));
        } else {
            $modules->registerModule($modObj, array(
                'title'       => 'Global Setting'
               ,'actBtnsList' => array()
               ,'hasFlagInList' => false
               ,'tableName'     => 'setting'
               ,'keyField'      => 'setting_id'
            ));
        }
    }

    /**
     *
     */
    function getSettingsValueTypeArray(){
        $arr =
        array(
             'Yes No'
            ,'Text Field'
            ,'Text Area'
            ,'Number Field'
        );

        return $arr;
    }

}