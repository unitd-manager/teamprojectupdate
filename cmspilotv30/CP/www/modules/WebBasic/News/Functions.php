<?
class CP_Www_Modules_WebBasic_News_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('webBasic_news');
        $modules->registerModule($modObj, array(
             'tableName' => 'content'
            ,'keyField'  => 'content_id'
        ));
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {
        return getCPModuleObj('webBasic_content')->fns->setMediaArray($mediaArr);
    }
}
