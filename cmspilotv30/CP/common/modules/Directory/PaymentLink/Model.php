<?
class CP_Common_Modules_Directory_PaymentLink_Model extends CP_Common_Lib_ModuleLinkModelAbstract
{
    /**
     *
     */
    function getFields(){
        $fn = Zend_Registry::get('fn');

        $fa = array();
        $fa = $fn->addToFieldsArray($fa, 'payment_id');

        return $fa;
    }

    function getAddNewGridItem(){
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        $srcRoom = $fn->getReqParam('srcRoom');

        $fa = $this->getFields();

        $tableName = '';
        if ($tv['module'] == 'directory_business' || $srcRoom == 'directory_business') {
            $fa['business_id'] = $tv['srcRoomId'];
            $tableName = 'business_payment';

        } else if ($tv['module'] == 'directory_businessGroup' || $srcRoom == 'directory_businessGroup') {
            $fa['business_group_id'] = $tv['srcRoomId'];
            $tableName = 'bg_payment';
        }
        $id = $fn->addRecord($fa, $tableName);
    }


    function getSaveGridItem(){
        $fn = Zend_Registry::get('fn');

        $fa = $this->getFields();
        $id = $fn->saveRecord($fa);
    }
}