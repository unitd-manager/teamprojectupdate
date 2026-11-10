<?
class CP_Admin_Modules_Web2_TagsLink_Functions
{
    //==================================================================//
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('web2_tagsLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'tags'
           ,'keyField'  => 'tags_id'
           ,'hasFlagInList'   => 0
        ));
    }
}
