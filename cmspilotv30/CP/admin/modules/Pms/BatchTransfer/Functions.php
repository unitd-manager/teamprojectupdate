<?
class CP_Admin_Modules_Pms_BatchTransfer_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('pms_batchTransfer');
        $modules->registerModule($modObj, array(
            'tableName'     => 'contact'
           ,'keyField'      => 'contact_id'
           ,'title'         => 'Batch Transfer'
           ,'actBtnsList'   => array()
        ));
    }
}