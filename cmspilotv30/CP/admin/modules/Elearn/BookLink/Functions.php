<?
class CP_Admin_Modules_Elearn_BookLink_Functions
{
    //==================================================================//
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('elearn_bookLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'book'
           ,'keyField'  => 'book_id'
        ));
    }
}
