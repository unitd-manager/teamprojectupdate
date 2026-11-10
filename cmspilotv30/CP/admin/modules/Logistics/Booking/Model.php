<?
class CP_Admin_Modules_Logistics_Booking_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {    

		$SQL ="
		SELECT b.*
			  ,b.title AS company_name	
		FROM booking b
		LEFT JOIN (company c)ON (b.company_id = c.company_id)
		";

        return $SQL;

    }

    /**
     *
     */
    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');
        $searchVar->mainTableAlias = 'r';

        $booking_id   = $fn->getReqParam('booking_id');
        $booking_type = $fn->getReqParam('booking_type');
        $bookingDate1 = $fn->getReqParam('bookingDate1');
        $bookingDate2 = $fn->getReqParam('bookingDate2');

        if ($booking_id != "") {
            $searchVar->sqlSearchVar[] = "b.booking_id = '{$booking_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "b.booking_id = '{$tv['record_id']}'";
        } else {

            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'b.booking_id');

            if ($booking_type != '' ) {
                $searchVar->sqlSearchVar[] = "b.booking_type = '{$booking_type}'";
            }

            if ($bookingDate1 != "" && $bookingDate1 != "From"
            && $bookingDate2 != "" && $bookingDate2 != "To" ) {
                $searchVar->sqlSearchVar[] = "(b.booking_date BETWEEN '{$bookingDate1}' AND '{$bookingDate2}')";
            }

            if ($bookingDate1 != "" && $bookingDate1 != "From" && $bookingDate2 == "To") {
                $searchVar->sqlSearchVar[] = "(b.booking_date >= '{$bookingDate1}')";
            }


            if ($bookingDate2 != "" && ($bookingDate1 == "From" 
            || $bookingDate1 == "") && $bookingDate2 != "To") {
                $searchVar->sqlSearchVar[] = "(b.booking_date <= '{$bookingDate2}')";
            }

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                                        b.booking_code   LIKE '%{$tv['keyword']}%'
                                       )";
            }
        }        
    }

    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getAdd(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $fa['booking_code'] = $this->getUpdateBookingCode();
        $fa['priority'] = 'Low';
        $fa['status']   = 'Enquiry';
        $id = $fn->addRecord($fa);
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getEditValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('booking_date', 'Please select booking date');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getSave($tableName = '', $keyField = '', $keyFieldVal = ''){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        if (!$this->getEditValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $id = $fn->saveRecord($fa, $tableName, $keyField, $keyFieldVal);
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getFields() {
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'booking_code');
        $fa = $fn->addToFieldsArray($fa, 'title');
        $fa = $fn->addToFieldsArray($fa, 'status');
        $fa = $fn->addToFieldsArray($fa, 'priority');
        $fa = $fn->addToFieldsArray($fa, 'booking_date');
        $fa = $fn->addToFieldsArray($fa, 'description');
        $fa = $fn->addToFieldsArray($fa, 'company_id');
        $fa = $fn->addToFieldsArray($fa, 'contact_id');
        $fa = $fn->addToFieldsArray($fa, 'refund_id');
        $fa = $fn->addToFieldsArray($fa, 'booking_type');
        
        return $fa;
    }

    /**
     *
     */
    function getLogisticsBookingLogisticsResourceLinkSQL($id) {

        return "
        SELECT r.resource_id
              ,r.resource_name
              ,r.role
              ,r.email
              ,r.phone
        FROM resource r, booking b
        WHERE b.booking_id = r.booking_id 
          AND b.booking_id = {$id}
        ";
    }

    /**
     *
     */
    function getLogisticsBookingLogisticsVehicleLinkSQL($id) {

        return "
        SELECT v.vehicle_id
              ,v.vehicle_code
              ,v.vehicle_name
              ,v.vehicle_model
              ,v.vehicle_date
        FROM vehicle v, booking b
        WHERE b.booking_id = v.booking_id 
          AND b.booking_id = {$id}
        ";
    }

    /**
     *
     */
    function getUpdateBookingCode() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        /* Updation of Quote Code */
        $nextBookingCode = $fn->getSettingsValueByKey("nextBookingCode");

        if($nextBookingCode < 10){
            $bookingCode = $fn->getSettingsValueByKey('bookingCodePrefix') . $nextBookingCode;
        }
        else if($nextBookingCode < 99){
            $bookingCode = $fn->getSettingsValueByKey('bookingCodePrefix') . $nextBookingCode;
        }
        else if($nextBookingCode < 999){
            $bookingCode = $fn->getSettingsValueByKey('bookingCodePrefix') . $nextBookingCode;
        }
        else{
            $bookingCode = $fn->getSettingsValueByKey('bookingCodePrefix') . $nextBookingCode;
        }
        
        $SQL    = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextBookingCode'";
        $result = $db->sql_query($SQL);

        return $bookingCode;
    }
}
