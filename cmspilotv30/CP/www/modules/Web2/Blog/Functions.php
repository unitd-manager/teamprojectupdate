<?
class CP_Www_Modules_Web2_Blog_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('web2_blog');
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
