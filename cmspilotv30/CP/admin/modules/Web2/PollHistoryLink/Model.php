<?
class CP_Admin_Modules_Web2_PollHistoryLink_Model extends CP_Common_Lib_ModuleModelAbstract
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

        $fa = $fn->addToFieldsArray($fa, 'title', '', true);
        $fa = $fn->addToFieldsArray($fa, 'answer_count');
        $fa = $fn->addToFieldsArray($fa, 'sort_order');
        
        return $fa;
    }

    //==================================================================//
    function getAddNewGridItem(){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        $fa = $this->getFields();
        $fa['poll_id']    = $tv['srcRoomId'];
        $fa['sort_order'] =  $fn->getNextSortOrder('poll_history', "poll_id={$tv['srcRoomId']}");
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
