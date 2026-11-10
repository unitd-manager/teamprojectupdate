<?
class CP_Common_Modules_Directory_PromotionLink_Functions
{
    //==================================================================//
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('directory_promotionLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'promotion'
           ,'keyField' => 'promotion_id'
        ));
    }

}
