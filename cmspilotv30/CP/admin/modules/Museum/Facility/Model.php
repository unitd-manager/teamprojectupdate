<?
class CP_Admin_Modules_Museum_Facility_Model extends CP_Common_Modules_Museum_Facility_Model
{
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');
        
        $validate->resetErrorArray();
        $validate->validateData('title', 'Please enter the facility');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }
    
    function getFields() {
        $fn = Zend_Registry::get('fn');

        $fa = array();
        $fa = $fn->addToFieldsArray($fa, 'title', '', true);
        $fa = $fn->addToFieldsArray($fa, 'description', '', true);
        $fa = $fn->addToFieldsArray($fa, 'description_short', '', true);
        $fa = $fn->addToFieldsArray($fa, 'date');
        $fa = $fn->addToFieldsArray($fa, 'published');

        $fa = $fn->addToFieldsArray($fa, 'section_id');
        $fa = $fn->addToFieldsArray($fa, 'category_id');
        $fa = $fn->addToFieldsArray($fa, 'sub_category_id');
        
        return $fa;
    }
    
    function getExportData($dataArray){
        $phpExcel = includeCPClass('Lib', 'PhpExcelExportWrapper', 'PhpExcelExportWrapper');

        $fa = array(
              'title' => $phpExcel->getFldObj('Facility')
        );

        $config = array(
             'fldsArr'   => $fa
            ,'dataArray' => $dataArray
        );

        return $phpExcel->exportData($config);
    }  
    
    /**
     *
     */
    function getMuseumFacilityMuseumFacilityAvailabilityLinkSQL($id) {
        $SQL = "
        SELECT fa.facility_availability_id
              ,fa.day
              ,fa.date_from
              ,fa.date_to
              ,fa.from_time
              ,fa.to_time
              ,fa.availability
        FROM facility_availability fa
        WHERE fa.facility_id = {$id}
        ";
        return $SQL;
    }    
    
       
}
