<?
class CP_Common_Modules_Directory_CardsLink_Model extends CP_Common_Lib_ModuleLinkModelAbstract
{
    /**
     *
     */
    function getFields(){
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'card_id');
        
        return $fa;
    }

    //==================================================================//
    function getAddNewGridItem(){
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        $fa = $this->getFields();
        
        if ($tv['srcRoom'] == 'directory_promotion'){
            $fa['promotion_id'] = $tv['srcRoomId'];
        } else {
            $fa['contact_id'] = $tv['srcRoomId'];
            $id = $fn->addRecord($fa, 'contact_card');
        }
    }

    //==================================================================//
    function getSaveGridItem(){
        $fn = Zend_Registry::get('fn');
        $fa = $this->getFields();
        $id = $fn->saveRecord($fa);
    }
}