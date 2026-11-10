<?
class CP_Admin_Modules_Museum_Booking_Model extends CP_Common_Modules_Museum_Booking_Model
{
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');
        
        $validate->resetErrorArray();
        $validate->validateData('facility_id', 'Please select the facility');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }
    
    function getFields() {
        $fn = Zend_Registry::get('fn');

        $fa = array();
        $fa = $fn->addToFieldsArray($fa, 'facility_id');
        $fa = $fn->addToFieldsArray($fa, 'date');
        $fa = $fn->addToFieldsArray($fa, 'from_time');
        $fa = $fn->addToFieldsArray($fa, 'to_time');
        $fa = $fn->addToFieldsArray($fa, 'availability');
        $fa = $fn->addToFieldsArray($fa, 'first_name');
        $fa = $fn->addToFieldsArray($fa, 'last_name');
        $fa = $fn->addToFieldsArray($fa, 'organisation');
        $fa = $fn->addToFieldsArray($fa, 'organisation_type');
        $fa = $fn->addToFieldsArray($fa, 'email');
        $fa = $fn->addToFieldsArray($fa, 'phone');
        $fa = $fn->addToFieldsArray($fa, 'fax');
        $fa = $fn->addToFieldsArray($fa, 'comments');

        $fa = $fn->addToFieldsArray($fa, 'number_of_visitor');
        $fa = $fn->addToFieldsArray($fa, 'number_of_students');
        $fa = $fn->addToFieldsArray($fa, 'number_of_adults');
        $fa = $fn->addToFieldsArray($fa, 'number_of_elderly');
        $fa = $fn->addToFieldsArray($fa, 'number_of_youth');

        $fa = $fn->addToFieldsArray($fa, 'group_leader');
        $fa = $fn->addToFieldsArray($fa, 'group_leader_mobile');

        $fa = $fn->addToFieldsArray($fa, 'pre_visit');
        $fa = $fn->addToFieldsArray($fa, 'date_pre_visit');
        $fa = $fn->addToFieldsArray($fa, 'disability_requirement');
        $fa = $fn->addToFieldsArray($fa, 'visit_type');
        
        return $fa;
    }
   //==================================================================//
    function getExportData($dataArray){
        $phpExcel = includeCPClass('Lib', 'PhpExcelExportWrapper', 'PhpExcelExportWrapper');

        $fa = array(
              'facility_title'  => $phpExcel->getFldObj('Facility')
             ,'date'            => $phpExcel->getFldObj('Date')
             ,'from_time'       => $phpExcel->getFldObj('From time')
             ,'to_time'         => $phpExcel->getFldObj('To time')
             ,'availability'    => $phpExcel->getFldObj('Availability')

             ,'first_name'      => $phpExcel->getFldObj('First Name')
             ,'last_name'       => $phpExcel->getFldObj('Last Name')
             ,'organisation'    => $phpExcel->getFldObj('Organisation')
             ,'organisation_type'       => $phpExcel->getFldObj('Organisation type')
             ,'email'           => $phpExcel->getFldObj('Email')
             ,'phone'           => $phpExcel->getFldObj('Phone')
             ,'fax'             => $phpExcel->getFldObj('Fax')

             ,'number_of_visitor'   => $phpExcel->getFldObj('Total number of visitors')
             ,'number_of_students'  => $phpExcel->getFldObj('Number of students')
             ,'number_of_adults'    => $phpExcel->getFldObj('Number of adults')
             ,'number_of_elderly'   => $phpExcel->getFldObj('Number of elderly')
             ,'number_of_youth' => $phpExcel->getFldObj('Number of youth')

             ,'group_leader'         => $phpExcel->getFldObj('Group leader')
             ,'group_leader_mobile'  => $phpExcel->getFldObj('Group leader mobile')

             ,'pre_visit'       => $phpExcel->getFldObj('Would you like to prearrange a visit?')
             ,'date_pre_visit'  => $phpExcel->getFldObj('Pre visit date')
             ,'disability_requirement' => $phpExcel->getFldObj('Special disability requirements?')
             ,'visit_type'  => $phpExcel->getFldObj('Type of Visit')
             ,'comments'    => $phpExcel->getFldObj('Comments')
        );

        $file_name = "Booking_" . date("d-m-Y") . ".xls";

        $config = array(
             'filename'  => $file_name
            ,'fldsArr'   => $fa
            ,'dataArray' => $dataArray
        );

        return $phpExcel->exportData($config);
    }    
}
