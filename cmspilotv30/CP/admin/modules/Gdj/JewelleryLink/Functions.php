<?
class CP_Admin_Modules_Gdj_JewelleryLink_Functions
{
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('gdj_jewelleryLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'product'
           ,'keyField'  => 'product_id'
        ));
    }
}
