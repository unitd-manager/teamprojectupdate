<?
class CP_Www_Modules_WebBasic_ContactUs_Functions
{

    /**
     *
     */
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('webBasic_contactUs');
        $modules->registerModule($modObj, array(
             'tableName' => 'content'
            ,'keyField'  => 'content_id'
        ));
    }
}
