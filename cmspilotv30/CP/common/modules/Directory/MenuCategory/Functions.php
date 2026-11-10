<?
class CP_Common_Modules_Directory_MenuCategory_Functions extends CP_Common_Lib_ModuleFunctionsAbstract
{

    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('directory_menuCategory');
        $modules->registerModule($modObj, array(
            'tableName' => 'menu_category'
           ,'keyField'  => 'menu_category_id'
        ));
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('directory_menuCategory', 'picture', 'image');
        $mediaArr->registerMedia($mediaObj, array(
            'count' => 'single'
        ));
    }    
}