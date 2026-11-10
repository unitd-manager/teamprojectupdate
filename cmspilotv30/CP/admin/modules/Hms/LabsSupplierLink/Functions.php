<?
class CP_Admin_Modules_Hms_LabsSupplierLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        
        $modObj = $modules->getModuleObj('hms_labsSupplierLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'labs_suppliercategorylink'
           ,'keyField'  => 'labs_suppliercategorylink_id'
        ));        
    }
}
