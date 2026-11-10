<?
class CP_Admin_Modules_Pms_BatchLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('pms_batchLink');
        $modules->registerModule($modObj, array(
            'tableName'     => 'batch'
           ,'keyField'      => 'batch_id'
        ));
    }
}
