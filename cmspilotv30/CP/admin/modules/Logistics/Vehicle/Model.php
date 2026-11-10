<?
class CP_Admin_Modules_Logistics_Vehicle_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {

        $SQL = "
        SELECT v.*
        FROM vehicle v
        ";
        
        return $SQL;
    }

    /**
     */
    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');

        $vehicle_id = $fn->getReqParam('vehicle_id');
        $model = $fn->getReqParam('model');
        $status = $fn->getReqParam('status');

        if ($vehicle_id != "") {
            $searchVar->sqlSearchVar[] = "v.vehicle_id = '{$vehicle_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "v.vehicle_id = '{$tv['record_id']}'";
        } else {

            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'v.vehicle_id');

            if ($status != '' ) {
                $searchVar->sqlSearchVar[] = "v.status = '{$status}'";
            }

            if ($model != '' ) {
                $searchVar->sqlSearchVar[] = "v.model = '{$model}'";
            }

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                       v.vehicle_code LIKE '%{$tv['keyword']}%'
                    OR v.vehicle_name    LIKE '%{$tv['keyword']}%'
                    OR v.model    LIKE '%{$tv['keyword']}%'
                )";
            }
        }        
    }


    /**
     *
     */
    function getUpdateVehicleCode() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        /* Updation of Vehicle Code */
        $nextVehicleCode = $fn->getSettingsValueByKey("nextVehicleCode");

        if($nextVehicleCode < 10){
            $VehCode = $fn->getSettingsValueByKey('vehicleCodePrefix') . $nextVehicleCode;
        }
        else if($nextVehicleCode < 99){
            $VehCode = $fn->getSettingsValueByKey('vehicleCodePrefix') . $nextVehicleCode;
        }
        else if($nextVehicleCode > 99 || $nextOppCode < 999){
            $VehCode = $fn->getSettingsValueByKey('vehicleCodePrefix') . $nextVehicleCode;
        }
        else{
            $VehCode = $fn->getSettingsValueByKey('vehicleCodePrefix') . $nextVehicleCode;
        }
        
        $SQL    = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextVehicleCode'";
        $result = $db->sql_query($SQL);

        return $VehCode;
    }

    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('vehicle_no', 'Please enter the Vehicle No');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getImportData(){
        $phpExcel = includeCPClass('Lib', 'PhpExcelImportWrapper');

        $fa = array(
              'vehicle_no'          => $phpExcel->getImportFldObj('VEHICLE NO')
             ,'vehicle_model'          => $phpExcel->getImportFldObj('DESCRIPTION')
             ,'resource_id'          => $phpExcel->getImportFldObj('DRIVER NAME')
        );


        $config = array(
             'module'           => 'logistics_vehicle'
            ,'fldsArr'          => $fa
        );

        return $phpExcel->importData($config);
    }

    /**
     */
    function getAdd(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $validate = Zend_Registry::get('validate');

        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }

        $SQL = "SELECT max(vehicle_code) AS vehicle_code FROM vehicle";
        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);
        
        $fa = $this->getFields();
        $fa['vehicle_code'] = $this->getUpdateVehicleCode();
        $id = $fn->addRecord($fa);
        $fn->returnAfterNewSave($id);
    }

    /**
     */
    function getEditValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
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
     */
    function getFields() {
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'vehicle_id');
        $fa = $fn->addToFieldsArray($fa, 'vehicle_code');
        $fa = $fn->addToFieldsArray($fa, 'vehicle_model');
        $fa = $fn->addToFieldsArray($fa, 'notes');
        $fa = $fn->addToFieldsArray($fa, 'description');
        $fa = $fn->addToFieldsArray($fa, 'vehicle_no');
        $fa = $fn->addToFieldsArray($fa, 'bill_to_vehicle');
        $fa = $fn->addToFieldsArray($fa, 'status');
        $fa = $fn->addToFieldsArray($fa, 'contact_person');
        $fa = $fn->addToFieldsArray($fa, 'resource_id');
        $fa = $fn->addToFieldsArray($fa, 'make_year');
        $fa = $fn->addToFieldsArray($fa, 'staff');
        
        return $fa;
    }


}
