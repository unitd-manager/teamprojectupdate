<?
class CP_Admin_Modules_Directory_ShopCenter_Functions extends CP_Common_Modules_Directory_ShopCenter_Functions
{
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('directory_shopCenter');
        $modules->registerModule($modObj, array(
            'tableName' => 'shop_center'
           ,'keyField'  => 'shop_center_id'
           ,'hasFlagInList' => 0
           ,'title'  => 'Shopping Mall'
           ,'actBtnsList' => array('new', 'export')
           ,'hasMultiLang' => 1
        ));
    }
}