<?
class CP_Admin_Modules_ManPower_CallRegistry_Functions
{
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('manPower_callRegistry');
        $modObj['tableName'] = 'call_registry';
        $modObj['keyField']  = 'call_registry_id';
        $modules->registerModule($modObj, array(
             'hasFlagInList' => 0
            ,'title' => 'Call Registry'
            ,'actBtnsList' => array('new')
            ,'actBtnsEdit'  => array('save', 'apply', 'delete')
        ));
    }
}