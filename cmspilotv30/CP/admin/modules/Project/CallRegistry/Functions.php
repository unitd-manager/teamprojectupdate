<?
class CP_Admin_Modules_Project_CallRegistry_Functions
{
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('project_callRegistry');
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