<?
class CP_Admin_Modules_Project_CostingLink_Model extends CP_Common_Lib_ModuleLinkModelAbstract
{
    /**
     *
     */
    function getFields(){
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'section');
        $fa = $fn->addToFieldsArray($fa, 'category');
        $fa = $fn->addToFieldsArray($fa, 'title');
        $fa = $fn->addToFieldsArray($fa, 'sort_order');
        $fa = $fn->addToFieldsArray($fa, 'comments');
        $fa = $fn->addToFieldsArray($fa, 'hours');
        $fa = $fn->addToFieldsArray($fa, 'amount');
        
        return $fa;
    }

    /**
     *
     */
    function getAddNewGridItem(){
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        
        $idFld = ($tv['module'] == 'project') ? 'project_id' : 'opportunity_id';

        $fa = $this->getFields();
        $fa['project_id'] = $tv['srcRoomId'];
        $fa['sort_order'] =  $fn->getNextSortOrder('costing', "{$idFld}={$tv['srcRoomId']}");
        $id = $fn->addRecord($fa);
    }

    /**
     *
     */
    function getSaveGridItem(){
        $fn = Zend_Registry::get('fn');
        
        $fa = $this->getFields();
        $id = $fn->saveRecord($fa);
    }
}
