<?
class CP_Admin_Modules_Ecommerce_SerialNos_Functions
{
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('ecommerce_serialNos');
        $modObj['tableName'] = 'serial_nos';
        $modObj['keyField']  = 'serial_nos_id';
        $modules->registerModule($modObj, array(
            'title'         => 'Serial Nos'
           ,'actBtnsList'   => array('new', 'import')
        ));
    }
}