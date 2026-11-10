<?
class CP_Admin_Modules_Project_TaskHistoryLink_Model extends CP_Common_Lib_ModuleLinkModelAbstract
{

    /**
     *
     */
    function getFields(){
        $ln = Zend_Registry::get('ln');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'task_id');
        $fa = $fn->addToFieldsArray($fa, 'title');
        $fa = $fn->addToFieldsArray($fa, 'percentage');
        $fa = $fn->addToFieldsArray($fa, 'status');
        $fa = $fn->addToFieldsArray($fa, 'sort_order');
        $fa = $fn->addToFieldsArray($fa, 'comments');
        
        return $fa;
    }

    //==================================================================//
    function getAddNewGridItem(){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        $fa = $this->getFields();
        $fa['task_id']    = $tv['srcRoomId'];
        $fa['sort_order'] =  $fn->getNextSortOrder('task_history', "task_id={$tv['srcRoomId']}");
        $id = $fn->addRecord($fa);
    }

    //==================================================================//
    function getSaveGridItem(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        
        $fa = $this->getFields();
        $id = $fn->saveRecord($fa);
    }

    //==================================================================//
}
