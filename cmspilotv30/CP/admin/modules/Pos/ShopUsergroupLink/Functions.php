<?
class CP_Admin_Modules_Pos_ShopUsergroupLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('pos_shopUsergroupLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'mod_acc_shop_user_group'
           ,'keyField'  => 'shop_user_group_id'
        ));
    }
}
