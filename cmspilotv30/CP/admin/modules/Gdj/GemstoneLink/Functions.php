<?
class CP_Admin_Modules_Gdj_GemstoneLink_Functions
{
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('gdj_gemstoneLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'product'
           ,'keyField'  => 'product_id'
        ));
    }
}
