<?
class CP_Admin_Modules_Project_TaskHistory_View extends CP_Common_Lib_ModuleViewAbstract
{

    //==================================================================//
    function getBulkAdd() {
        $ln = Zend_Registry::get('ln');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $am = Zend_Registry::get('am');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $tv = Zend_Registry::get('tv');

        $task_id = $fn->getReqParam('task_id');
        
        $formAction = "index.php?_spAction=bulkAddSubmit&module={$tv['module']}&showHTML=0";

        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            <fieldset>
                {$formObj->getTBRow('Prefix', 'prefix')}
                {$formObj->getTBRow('No of Records', 'no_of_records')}
            </fieldset>
            <input type='hidden' name='task_id' value='{$task_id}' />
        </form>
        ";

        return $text;
    }

    //==================================================================//
    function getBulkAddSubmit() {
        $ln = Zend_Registry::get('ln');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $am = Zend_Registry::get('am');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $tv = Zend_Registry::get('tv');
        $validate = Zend_Registry::get('validate');

        $task_id = $fn->getReqParam('task_id');
        $prefix = $fn->getReqParam('prefix');
        $no_of_records = $fn->getReqParam('no_of_records');
        
        for ($i = 1; $i <= $no_of_records ; $i++){
            $fa = array();
            $fa['task_id'] = $task_id;
            $fa['title']   = $prefix . ' ' . $i;
            $fa['status']  = 'To be Started';
            $fa['sort_order'] =  $fn->getNextSortOrder('task_history', "task_id={$task_id}");
            $id = $fn->addRecord($fa);
        }

        return $validate->getSuccessMessageXML();
    }
}
