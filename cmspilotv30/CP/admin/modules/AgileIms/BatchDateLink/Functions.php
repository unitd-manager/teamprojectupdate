<?
class CP_Admin_Modules_AgileIms_BatchDateLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('agileIms_batchDateLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'batch_date'
           ,'keyField'  => 'batch_date_id'
        ));
    }
}
