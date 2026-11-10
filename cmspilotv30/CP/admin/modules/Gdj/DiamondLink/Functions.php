<?
class CP_Admin_Modules_Gdj_DiamondLink_Functions
{
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('gdj_diamondLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'product'
           ,'keyField'  => 'product_id'
        ));
    }
}
