<?
class CP_Admin_Modules_AceIms_BatchLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('aceIms_batchLink');
        $modules->registerModule($modObj, array(
            'tableName'     => 'batch'
           ,'keyField'      => 'batch_id'
        ));
    }
}
