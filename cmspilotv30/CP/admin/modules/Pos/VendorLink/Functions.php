<?
class CP_Admin_Modules_Pos_VendorLink_Functions
{
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('pos_vendorLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'vendor'
           ,'keyField'  => 'vendor_id'
           ,'hasFlagInList' => 0
        ));
    }
}