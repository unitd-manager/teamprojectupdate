<?
class CP_Admin_Modules_EnterpriseIms_BatchLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('enterpriseIms_batchLink');
        $modules->registerModule($modObj, array(
            'tableName'     => 'batch'
           ,'keyField'      => 'batch_id'
        ));
    }
}
