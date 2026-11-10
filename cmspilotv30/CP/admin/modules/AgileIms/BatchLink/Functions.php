<?
class CP_Admin_Modules_AgileIms_BatchLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('agileIms_batchLink');
        $modules->registerModule($modObj, array(
            'tableName'     => 'batch'
           ,'keyField'      => 'batch_id'
        ));
    }
}
