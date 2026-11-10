<?
class CPL_Admin_Modules_EnggCrm_Vehicle_Model extends CP_Common_Lib_ModuleModelAbstract
{
   function getSQL() {
$SQL = "
        SELECT v.* 
	
       
				  FROM vehicle v
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
        $searchVar->mainTableAlias = 'c';

        $vehicle_id = $fn->getReqParam('vehicle_id');
       

        if ($vehicle_id != "") {
            $searchVar->sqlSearchVar[] = "v.vehicle_id = '{$vehicle_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "v.vehicle_id = '{$tv['record_id']}'";
        }  
    }

    /**
     *
     */
     function getNewValidate() {
       $db = Zend_Registry::get('db');
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        $validate->resetErrorArray();

        $validate->validateData('vehicle_no', 'Please enter the vehicle_no');
      
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
        
        $id = $fn->addRecord($fa);
        $fn->returnAfterNewSave($id);
    }
    /**
     *
     */
    function getEditValidate() {
		 $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

       // $validate->validateData('project_id', 'Please enter the title');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
      
    }

     //function getEditPortalValidate() {
       // return $this->getNewValidate();
   // }

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
    function getFields(){
        $fn = Zend_Registry::get('fn');

        $fa = array();

       
        $fa = $fn->addToFieldsArray($fa, 'vehicle_id');
        $fa = $fn->addToFieldsArray($fa, 'vehicle_no');
       
        $fa = $fn->addToFieldsArray($fa, 'year_of_purchase');
        $fa = $fn->addToFieldsArray($fa, 'model');
      
       
       

        return $fa;
    }

   /**
     *
     */
    function getActualChargeSubmit() {
        $fn       = Zend_Registry::get('fn');
        $ln       = Zend_Registry::get('ln');
        $db       = Zend_Registry::get('db');
        $dbUtil   = Zend_Registry::get('dbUtil');
        $validate = Zend_Registry::get('validate');
        $cpUtil   = Zend_Registry::get('cpUtil');
        
        $vehicle_id = $fn->getPostParam('vehicle_id');
       $vehicle_fuel_id = $fn->getPostParam('vehicle_fuel_id');
        $date = $fn->getPostParam('date');
        $amount = $fn->getPostParam('amount');
        $liters = $fn->getPostParam('liters');

        if (!$this->getActualChargeValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = array();
        $fa['vehicle_fuel_id']    = $vehicle_fuel_id;
        $fa['vehicle_id']    = $vehicle_id;
        $fa['date']    = $date;
        $fa['amount']    = $amount;
        $fa['liters']    = $liters;
        $fa = $fn->addCreationDetailsToFieldsArray($fa, 'vehicle_fuel');

        $fn->addRecord($fa, 'vehicle_fuel');           

        return $validate->getSuccessMessageXML();
    }
    /**
     *
     */
    function getActualChargeValidate() {
        $validate = Zend_Registry::get('validate');
        $fn       = Zend_Registry::get('fn');
        $db       = Zend_Registry::get('db');

        $amount = $fn->getPostParam('amount');

        $validate->resetErrorArray();

        if ($amount == 0 || $amount == ''){
            $validate->errorArray['error_box']['name'] = "error_box1";
            $validate->errorArray['error_box']['msg']  = "Please Enter Amount";
        }

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

     /**
     *
     */
    function getRenewalDateSubmit() {
        $fn       = Zend_Registry::get('fn');
        $ln       = Zend_Registry::get('ln');
        $db       = Zend_Registry::get('db');
        $dbUtil   = Zend_Registry::get('dbUtil');
        $validate = Zend_Registry::get('validate');
        $cpUtil   = Zend_Registry::get('cpUtil');
        
        $vehicle_id = $fn->getPostParam('vehicle_id');
       $vehicle_insurance_id = $fn->getPostParam('vehicle_insurance_id');
        $insurance_date = $fn->getPostParam('insurance_date');
        $insurance_amount = $fn->getPostParam('insurance_amount');
        $renewal_date = $fn->getPostParam('renewal_date');

        if (!$this->getRenewalDateValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = array();
        $fa['vehicle_insurance_id']    = $vehicle_insurance_id;
        $fa['vehicle_id']    = $vehicle_id;
        $fa['insurance_date']    = $insurance_date;
        $fa['insurance_amount']    = $insurance_amount;
        $fa['renewal_date']    = $renewal_date;
        $fa = $fn->addCreationDetailsToFieldsArray($fa, 'vehicle_insurance');

        $fn->addRecord($fa, 'vehicle_insurance');           

        return $validate->getSuccessMessageXML();
    }
    /**
     *
     */
    function getRenewalDateValidate() {
        $validate = Zend_Registry::get('validate');
        $fn       = Zend_Registry::get('fn');
        $db       = Zend_Registry::get('db');

         $insurance_amount = $fn->getPostParam('insurance_amount');

        $validate->resetErrorArray();

        if ($insurance_amount == 0 || $insurance_amount == ''){
            $validate->errorArray['error_box']['name'] = "error_box1";
            $validate->errorArray['error_box']['msg']  = "Please Enter Insurance Amount";
        }

        
        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
      
    }


   /**
     *
     */
    function getServiceSubmit() {
        $fn       = Zend_Registry::get('fn');
        $ln       = Zend_Registry::get('ln');
        $db       = Zend_Registry::get('db');
        $dbUtil   = Zend_Registry::get('dbUtil');
        $validate = Zend_Registry::get('validate');
        $cpUtil   = Zend_Registry::get('cpUtil');
        
        $vehicle_id = $fn->getPostParam('vehicle_id');
       $vehicle_service_id = $fn->getPostParam('vehicle_service_id');
        $date = $fn->getPostParam('date');
        $amount = $fn->getPostParam('amount');
        $description = $fn->getPostParam('description');

        if (!$this->getServiceValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = array();
        $fa['vehicle_service_id']    = $vehicle_service_id;
        $fa['vehicle_id']    = $vehicle_id;
        $fa['date']    = $date;
        $fa['amount']    = $amount;
        $fa['description']    = $description;
        $fa = $fn->addCreationDetailsToFieldsArray($fa, 'vehicle_service');

        $fn->addRecord($fa, 'vehicle_service');           

        return $validate->getSuccessMessageXML();
    }
    /**
     *
     */
    function getServiceValidate() {
        $validate = Zend_Registry::get('validate');
        $fn       = Zend_Registry::get('fn');
        $db       = Zend_Registry::get('db');

        $amount = $fn->getPostParam('amount');

        $validate->resetErrorArray();

        if ($amount == 0 || $amount == ''){
            $validate->errorArray['error_box']['name'] = "error_box1";
            $validate->errorArray['error_box']['msg']  = "Please Enter Amount";
        }

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }
   /* function getprojectJsonByComId() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $project_id = $fn->getReqParam('project_id', '', true);

        $json  = array();

        if ($project_id == ''){
            $json[] = array('value' => '', 'caption' => 'Please Select');
            return json_encode($json);
        }

        $SQL = $this->getContactsByCompanySQL($project_id);
        $result = $db->sql_query($SQL);

        $json[] = array('value' => '', 'caption' => 'Please Select');
        while ($row = $db->sql_fetchrow($result)) {
            $json[] = array("value" => $row['project_id'], "caption" => $row['title']);
        }

        return json_encode($json);
    }*/

	

}
