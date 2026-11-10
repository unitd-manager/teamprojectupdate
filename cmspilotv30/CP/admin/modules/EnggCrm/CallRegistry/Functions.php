<?
class CP_Admin_Modules_EnggCrm_CallRegistry_Functions
{
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('enggCrm_callRegistry');
        $modObj['tableName'] = 'call_registry';
        $modObj['keyField']  = 'call_registry_id';
        $modules->registerModule($modObj, array(
             'hasFlagInList' => 0
            ,'title' => 'Lead'
            ,'actBtnsList' => array('new')
            ,'actBtnsEdit'  => array('save', 'apply', 'cancel', 'delete')
        ));
    }
}