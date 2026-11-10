<?
class CP_Www_Modules_LawNews_NewsArchive_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('lawNews_newsArchive');
        $modules->registerModule($modObj, array(
             'tableName' => 'content'
            ,'keyField'  => 'content_id'
            ,'listLimit'  => 10
        ));
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {
        return getCPModuleObj('webBasic_content')->fns->setMediaArray($mediaArr);
    }
}
