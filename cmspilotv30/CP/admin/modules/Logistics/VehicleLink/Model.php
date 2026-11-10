<?
class CP_Admin_Modules_Logistics_VehicleLink_Model extends CP_Common_Lib_ModuleLinkModelAbstract
{
    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

        $validate->validateData('vehicle_name', 'Please enter the vehicle name');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getEditValidate() {
        return $this->getNewValidate();
    }

    /**
     *
     */
    function getFields(){
        $fn = Zend_Registry::get('fn');

        $fa = array();
        $fa = $fn->addToFieldsArray($fa, 'vehicle_code');
        $fa = $fn->addToFieldsArray($fa, 'vehicle_name');
        $fa = $fn->addToFieldsArray($fa, 'vehicle_model');
        $fa = $fn->addToFieldsArray($fa, 'vehicle_date');
        
        return $fa;
    }

    function getAdd(){
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $validate = Zend_Registry::get('validate');

        $booking_id = $fn->getReqParam('booking_id');

        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
		$fa['booking_id'] = $booking_id;
        $id = $fn->addRecord($fa);
        return $validate->getSuccessMessageXML();
    }
}
