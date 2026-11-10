<?
class CP_Admin_Modules_EnterpriseIms_BatchTransfer_Functions
{
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('enterpriseIms_batchTransfer');
        $modules->registerModule($modObj, array(
            'tableName'     => 'contact'
           ,'keyField'      => 'contact_id'
           ,'title'         => 'Batch Transfer'
           ,'actBtnsList'   => array()
        ));
    }
}