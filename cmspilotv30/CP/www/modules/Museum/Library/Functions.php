<?
class CP_Www_Modules_Museum_Library_Functions extends CP_Common_Modules_Museum_Library_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('museum_library');
        $modules->registerModule($modObj, array(
             'tableName' => 'library'
            ,'keyField'  => 'library_id'
            ,'listLimit'  => 10
        ));
    }

//    /**
//     *
//     */
//    function setMediaArray($mediaArr) {
//        return getCPModuleObj('museum_library')->fns->setMediaArray($mediaArr);
//    }
}
