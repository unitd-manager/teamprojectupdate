<?
class CP_Admin_Widgets_Pms_StudentParentReport_Model extends CP_Common_Lib_WidgetModelAbstract
{
    function getSQL(){
        $fn = Zend_Registry::get('fn');

        $enrollment_year = $fn->getReqParam('year');

        $SQL = "
        SELECT DISTINCT c.contact_id
              ,c.registration_no
              ,c.first_name AS student_name
              ,c.id_card_no
              ,c.email AS student_email
              ,c.date_of_birth
              ,c.age
              ,c.gender
              ,c.year_of_joining
              ,c.status
              ,c.continuing_to_next_year
              ,p.parent_id
              ,p.first_name AS parent_name
              ,p.id_card_no AS parent_id_card_no
              ,p.phone
              ,p.mobile
              ,p.email AS parent_email
              ,p.relationship_to_student
              ,p.mode_of_payment
              ,p.emergency_contact_name
              ,p.emergency_contact_mobile
              ,p.giro_applicant_name
              ,p.bank_name
              ,p.bank_code
              ,p.dda
              ,p.account_name
              ,p.branch
              ,p.account_no
              ,p.address_flat
              ,p.address_street
              ,p.address_po_code
              ,gc.name AS country_name
              ,s.title AS branch_name
              ,(SELECT cou.title 
                FROM course cou
                JOIN course_contact cc ON (cou.course_id = cc.course_id)
                WHERE cc.year_of_enrollment = '{$enrollment_year}'
                  AND cc.contact_id = c.contact_id) as student_registered_course
              ,(SELECT ba.title 
                FROM batch ba
                JOIN course_contact cc ON (ba.batch_id = cc.batch_id)
                WHERE cc.year_of_enrollment = '{$enrollment_year}'
                  AND cc.contact_id = c.contact_id) as student_registered_batch
        FROM contact c
        LEFT JOIN parent_contact pc ON (c.contact_id = pc.contact_id)
        LEFT JOIN parent p          ON (pc.parent_id = p.parent_id)
        LEFT JOIN site s            ON (c.site_id = s.site_id)
        JOIN course_contact cc      ON (c.contact_id = cc.contact_id)
        LEFT JOIN geo_country gc    ON (p.address_country = gc.country_code)
        ";

        return $SQL;
    }
    /**
     *
     */
    function setSearchVar() {
        $fn = Zend_Registry::get('fn');
        $searchVar = $this->searchVar;
        
        $status          = $fn->getReqParam('status');
        $site_id         = $fn->getReqParam('site_id');
        $enrollment_year = $fn->getReqParam('year');

        $searchVar->sqlSearchVar[] = "p.parent_id != ''";

        if ($enrollment_year == '') {
            $enrollment_year = date('Y');
        }

        if ($site_id) {
            if(is_numeric($site_id)) {
                $searchVar->sqlSearchVar[] = "c.site_id = '{$site_id}'";
            }
        }

        if ($status != '') {
            $searchVar->sqlSearchVar[] = "c.status = '{$status}'";
            
            if ($status == 'Graduated') {
                $searchVar->sqlSearchVar[] = "c.graduation_year = '{$enrollment_year}'";
            } else {
                $searchVar->sqlSearchVar[] = "cc.year_of_enrollment = '{$enrollment_year}'";
            }
        } else {
            $searchVar->sqlSearchVar[] = "c.status = 'Active'";
      	}

        $searchVar->sortOrder = 'c.site_id ASC, c.registration_no ASC';
    }

    /**
     *
     * @param <type> $SQL
     * @return <type>
     */
    function getDataArray() {
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');

        $modelHelper = Zend_Registry::get('modelHelper');
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'pms_studentParentReport');

        $this->dataArray = $dataArray ;
        return $dataArray;
    }
    
    function getExportToExcel($dataArray = ''){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        
        if (!is_array($dataArray)){
            $dataArray = $this->getDataArray();
        }

        $phpExcel = includeCPClass('Lib', 'PhpExcelExportWrapper', 'PhpExcelExportWrapper');
         
        $fa = array(
              'registration_no'         => $phpExcel->getFldObj('Reg No')
             ,'student_name'            => $phpExcel->getFldObj('Student Name')
             ,'id_card_no'              => $phpExcel->getFldObj('Student NRIC No')
             ,'student_email'           => $phpExcel->getFldObj('Student Email')
             ,'date_of_birth'           => $phpExcel->getFldObj('Date of Birth')
             ,'age'                     => $phpExcel->getFldObj('Age')
             ,'gender'                  => $phpExcel->getFldObj('Gender')
             ,'year_of_joining'         => $phpExcel->getFldObj('Year of Joining')
             ,'status'                  => $phpExcel->getFldObj('Status')
             ,'parent_name'             => $phpExcel->getFldObj('Parent Name')
             ,'parent_id_card_no'       => $phpExcel->getFldObj('Parent NRIC No')
             ,'phone'                   => $phpExcel->getFldObj('Phone')
             ,'mobile'                  => $phpExcel->getFldObj('Mobile')
             ,'parent_email'            => $phpExcel->getFldObj('Parent Email')
             ,'relationship_to_student' => $phpExcel->getFldObj('Relationship')             
             ,'mode_of_payment'         => $phpExcel->getFldObj('Payment Mode')
             ,'emergency_contact_name'  => $phpExcel->getFldObj('Emergency Contact Name')
             ,'emergency_contact_mobile'=> $phpExcel->getFldObj('Emergency Contact No')
             ,'giro_applicant_name'     => $phpExcel->getFldObj('Giro Applicant Name')
             ,'bank_name'               => $phpExcel->getFldObj('Bank Name')
             ,'bank_code'               => $phpExcel->getFldObj('Bank Code')
             ,'dda'                     => $phpExcel->getFldObj('DDA')
             ,'account_name'            => $phpExcel->getFldObj('Account Name')
             ,'branch'                  => $phpExcel->getFldObj('Branch Code')
             ,'account_no'              => $phpExcel->getFldObj('Account No')
             ,'address_flat'            => $phpExcel->getFldObj('Address 1')
             ,'address_street'          => $phpExcel->getFldObj('Address 2')
             ,'address_po_code'         => $phpExcel->getFldObj('Postal Code')
             ,'country_name'            => $phpExcel->getFldObj('Country')
             ,'branch_name'             => $phpExcel->getFldObj('Branch')
             ,'student_registered_course'=> $phpExcel->getFldObj('Class')
             ,'student_registered_batch' => $phpExcel->getFldObj('Session')
        );

        $file_name = "MasterList_" . date("d-m-Y") . ".xls";

        $config = array(
             'filename'  => $file_name
            ,'fldsArr'   => $fa
            ,'dataArray' => $dataArray
        );

        return $phpExcel->exportData($config);
    }

    /**
     *
     */
    function getSqlForCount() {
        $db = Zend_Registry::get('db');
        
        $serial_no = 0;
        foreach($this->dataArray as $row){           
            $serial_no += 1;
        }
        
        return $serial_no;
    }
}