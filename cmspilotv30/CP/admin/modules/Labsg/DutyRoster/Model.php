<?
class CP_Admin_Modules_Labsg_DutyRoster_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {

        $SQL = "
        SELECT dr.*
              ,e.employee_name
        FROM duty_roster dr
        LEFT JOIN (employee e) ON (e.employee_id = dr.employment_id)
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar1($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');
        $searchVar->mainTableAlias = 'dr';

        $status       = $fn->getReqParam('status');
        $duty_roster_id   = $fn->getReqParam('duty_roster_id');
       // $company_name = $fn->getReqParam('company_name');

        if ($duty_roster_id != "") {
            $searchVar->sqlSearchVar[] = "dr.duty_roster_id = '{$duty_roster_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "dr.duty_roster_id = '{$tv['record_id']}'";
        } else {
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'dr.duty_roster_id');


            if ($status != "") {
                $searchVar->sqlSearchVar[] = "c.status = '{$status}'";
            }

           /* if ($company_name != "") {
                $searchVar->sqlSearchVar[] = "c.company_name LIKE '%{$company_name}%'";
            }

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                    c.company_name  LIKE '%{$tv['keyword']}%'
                    OR c.group_name LIKE '%{$tv['keyword']}%'
                    OR c.email      LIKE '%{$tv['keyword']}%'
                )";
            }*/

            //------------------------------------------------------------------------//
            if ($tv['special_search'] == "Flagged") {
                $searchVar->sqlSearchVar[] = "dr.flag = 1";
            }

            if ($tv['special_search'] == "Not-Flagged") {
                $searchVar->sqlSearchVar[] = "(dr.flag != 1 OR dr.flag IS null)";
            }

            //$searchVar->sortOrder = "c.company_name";
        }
    }

    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('employee_name', 'Please enter the employee_name');

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
        $cpCfg = Zend_Registry::get('cpCfg');

        if (!$this->getEditValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $id = $fn->saveRecord($fa, $tableName, $keyField, $keyFieldVal);
        $fn->returnAfterNewSave($id, $cpCfg['cp.pagetoReturnAfterSave']);
    }

    /**
     *
     */
    function getSaveList(){
        $fn = Zend_Registry::get('fn');
        $fn->getSaveList();
    }

    /**
     *
     */
    function getFields(){
        $fn = Zend_Registry::get('fn');

        $fa = array();
        $fa = $fn->addToFieldsArray($fa, 'work_date');
        $fa = $fn->addToFieldsArray($fa, 'work_from_time');
        $fa = $fn->addToFieldsArray($fa, 'work_to_time');

        return $fa;
    }

    /**
     *
     */
    function getExportData($dataArray){
        $phpExcel = includeCPClass('Lib', 'PhpExcelExportWrapper', 'PhpExcelExportWrapper');

        $fa = array(
              'company_id'      => $phpExcel->getFldObj('Company ID')
             ,'company_name'    => $phpExcel->getFldObj('Company Name')
             ,'category'        => $phpExcel->getFldObj('Category')
             ,'company_size'    => $phpExcel->getFldObj('Company Size')
             ,'industry'        => $phpExcel->getFldObj('Industry')
             ,'source'          => $phpExcel->getFldObj('Source')
             ,'website'         => $phpExcel->getFldObj('Website')
             ,'phone'           => $phpExcel->getFldObj('Phone')
             ,'fax'             => $phpExcel->getFldObj('Fax')

             ,'address_flat'    => $phpExcel->getFldObj('Address Flat')
             ,'address_street'  => $phpExcel->getFldObj('Address Street')
             ,'address_town'    => $phpExcel->getFldObj('Address Town')
             ,'address_state'   => $phpExcel->getFldObj('Address State')
             ,'address_country' => $phpExcel->getFldObj('Address Country')

             ,'status'          => $phpExcel->getFldObj('Status')
             ,'comment_by'      => $phpExcel->getFldObj('Comment By')
             ,'notes'           => $phpExcel->getFldObj('Notes')
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
    function getImportData(){
        $phpExcel = includeCPClass('Lib', 'PhpExcelImportWrapper');

        $fa = array(
              'product_code'      => $phpExcel->getImportFldObj('Product Code')
             ,'title'             => $phpExcel->getImportFldObj('Title')
             ,'description_short' => $phpExcel->getImportFldObj('Short Description')
             ,'description'       => $phpExcel->getImportFldObj('Description')
             ,'picture'           => $phpExcel->getImportFldObj('Picture Ref')
             ,'published'         => $phpExcel->getImportFldObj('Published')
             ,'category_id'       => $phpExcel->getImportFldObj('Category')
             ,'sub_category_id'   => $phpExcel->getImportFldObj('Sub Category')
        );

        $fa['published']['defaultValue'] = 1;
        $fa['picture']['refOnly'] = true;

        $fa['category_id']['specialType'] = 'category';
        $fa['category_id']['exp'] = array('sectionType' => 'Product');

        $fa['sub_category_id']['specialType'] = 'subCategory';
        $fa['sub_category_id']['exp'] = array(
             'categoryFldKeyInArr' => 'category_id'
        );

        /****************************************/
        $config = array(
             'module'              => 'labsg_company'
            ,'matchFieldArr'       => array('product_code')
            ,'mandatoryFldsArr'    => array('product_code')
            ,'fldsArr'             => $fa
            ,'callbackAfterInsert' => 'callbackAfterImportInsert'
        );

        return $phpExcel->importData($config);
    }

    /**
     *
     */
    function callbackAfterImportInsert($product_id, $fa) {
        $media = Zend_Registry::get('media');

        if ($fa['picture'] != ''){
            $sourceFilePath = realpath('../media_import') . "/{$picture}";
            $exp = array(
                 'srcFile' => $sourceFilePath
                ,'actualFileName' => $picture
            );
            $media->model->createMedia('ecommerce_product', 'picture', $product_id, $exp);
        }
    }
    /**
     *
     */
    function getLabsgCompanyLabsgContactLinkSQL($id) {

        return "
        SELECT a.contact_id
              ,a.first_name
              ,a.email
              ,a.phone_direct
              ,a.mobile
              ,a.position
              ,a.department
        FROM company b, contact a
        WHERE a.company_id = b.company_id
          AND b.company_id = {$id}
        ";
    }
    /**
     *
     */
    function getLabsgCompanyLabsgDiscountLinkSQL($id) {

        return "
        SELECT d.discount_id
              ,pg.title
              ,c.title AS category_title
              ,d.margin
              ,d.discount_percent
        FROM discount d
        LEFT JOIN (product_group pg) ON (d.product_group_id = pg.product_group_id)
        LEFT JOIN (category c) ON (d.category_id = c.category_id)
        WHERE d.company_id = {$id}
        ORDER BY pg.sort_order
        ";
    }

    /**
     *
     */
    function getLabsgCompanyLabsgCompanyGroupLinkSQL1($id) {

        return "
        SELECT a.company_id
              ,a.company_name
              ,a.status
        FROM company_group b, company a
        WHERE a.company_id = b.company_id
          AND b.company_id = {$id}
        ";
    }

    /**
     *
     * @param <type> $SQL
     * @return <type>
     */
    function getEventDetails(){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $doctor_id = $fn->getReqParam('doctor_id');
        $cpCfg   = Zend_Registry::get('cpCfg');

        $jsonArray = array();

        $appendSql = "";
        if($doctor_id != ''){
           $appendSql ="WHERE employment_id = {$doctor_id}";
        }

        $appendSqlDr = '';
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        if ($cpCfg['cp.hasMultiUniqueSites'] && $doctor_id != '') {
            $appendSqlDr = "AND r.site_id = {$cpSiteIdSession}";
        }else if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSqlDr = "WHERE r.site_id = {$cpSiteIdSession}";
        }

        $SQL = "
        SELECT r.employment_id
              ,r.work_from_time
              ,r.work_to_time
              ,r.work_from_time2
              ,r.work_to_time2
              ,r.work_from_time3
              ,r.work_to_time3
              ,r.work_date
              ,r.duty_roster_id
              ,e.employee_name
              ,e.color
              ,r.duty_roster_id
        FROM duty_roster r
        LEFT JOIN (employee e) ON (e.employee_id = r.employment_id)
        {$appendSql}
        {$appendSqlDr}
       ";

        $result  = $db->sql_query($SQL);

        $title = '';
        while ($row = $db->sql_fetchrow($result)) {
             $eventStartdate    = $row['work_date'] .' ' . $row['work_from_time'];
             $eventEnddate      = $row['work_date'] .' ' . $row['work_to_time'];
             $dutyRosterLink    = "index.php?module=labsg_dutyRoster&_spAction=dutyRosterEdit&duty_roster_id={$row['duty_roster_id']}&showHTML=0";
             $doctor_name       = 'Doctor Name: '.$row['employee_name'];
             $cancelAppointment = "<a class='cancelDutyRoster cancelDutyRosterOnEvent' duty_roster_id={$row['duty_roster_id']}>Cancel</a>";

             $backgroundColor = $row['color'];


             $buildjson = array(
              'title'             => $title
             ,'doctor_name'       => $doctor_name
             ,'start'             => $eventStartdate
             ,'end'               => $eventEnddate
             ,'allDay'            => false
             ,'url'               => $dutyRosterLink
             ,'backgroundColor'   => $backgroundColor
             ,'borderColor'       => $backgroundColor
             ,'employee_id'       => $row['employment_id']
             ,'duty_roster_id'    => $row['duty_roster_id']
             );

             array_push($jsonArray, $buildjson);
        }

        echo json_encode($jsonArray);
    }

    /**
     *
     */
    function getAddDutyRosterDetailsValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $roster_type     = $fn->getPostParam('roster_type');
        $work_from_time  = $fn->getPostParam('work_from_time');
        $work_to_time    = $fn->getPostParam('work_to_time');
        $work_from_time2 = $fn->getPostParam('work_from_time2');
        $work_to_time2   = $fn->getPostParam('work_to_time2');
        $work_from_time3 = $fn->getPostParam('work_from_time3');
        $work_to_time3   = $fn->getPostParam('work_to_time3');
        $work_time2      = $fn->getPostParam('work_time2');
        $work_time3      = $fn->getPostParam('work_time3');

        $validate->resetErrorArray();
        $validate->validateData('employee_id', 'Please Select Doctor/Nurse');

        $work_from_time  = strtotime($work_from_time);
        $work_to_time    = strtotime($work_to_time);
        $work_from_time2 = strtotime($work_from_time2);
        $work_to_time2   = strtotime($work_to_time2);
        $work_from_time3 = strtotime($work_from_time3);
        $work_to_time3   = strtotime($work_to_time3);

        if ($roster_type == 'Weekly') {
            $validate->validateData('weekdays_select', 'Please Select Days');
        }

        if($work_to_time == ''){
            $validate->validateData('work_to_time', 'Please Select time out');
        }

        if($work_to_time != ''){
            if($work_to_time <= $work_from_time){
                $validate->errorArray['start_date']['name'] = 'work_to_time';
                $validate->errorArray['start_date']['msg']  = 'Time Out should not be less/equal to Time In';
            }
        }

        if($work_time2 != ''){
            $validate->validateData('work_from_time2', 'Please Select time in');

            if($work_to_time2 == ''){
                $validate->validateData('work_to_time2', 'Please Select time out');
            }

            if($work_to_time2 != ''){
                if($work_to_time <= $work_from_time){
                    $validate->errorArray['start_date']['name'] = 'work_to_time2';
                    $validate->errorArray['start_date']['msg']  = 'Time Out should not be less/equal to Time In';
                }
            }
        }

        if($work_time3 != ''){
            $validate->validateData('work_from_time3', 'Please Select time in');

            if($work_to_time3 == ''){
                $validate->validateData('work_to_time3', 'Please Select time out');
            }

            if($work_to_time3 != ''){
                if($work_to_time <= $work_from_time){
                    $validate->errorArray['start_date']['name'] = 'work_to_time3';
                    $validate->errorArray['start_date']['msg']  = 'Time Out should not be less/equal to Time In';
                }
            }
        }

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    function getAddDutyRosterDetailsSubmit(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        if (!$this->getAddDutyRosterDetailsValidate()){
            return $validate->getErrorMessageXML();
        }

        $employee_id     = $fn->getPostParam('employee_id');
        $work_from_time  = $fn->getPostParam('work_from_time');
        $work_to_time    = $fn->getPostParam('work_to_time');
        $work_from_time2 = $fn->getPostParam('work_from_time2');
        $work_to_time2   = $fn->getPostParam('work_to_time2');
        $work_from_time3 = $fn->getPostParam('work_from_time3');
        $work_to_time3   = $fn->getPostParam('work_to_time3');
        $work_date       = $fn->getPostParam('work_date');
        $create_month    = $fn->getPostParam('create_month');
        $daily_type      = $fn->getPostParam('daily_type', array());
        $roster_type     = $fn->getPostParam('roster_type');
        $weekdays_select = $fn->getPostParam('weekdays_select', array());
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');

        $workdays = array();
        $type  = CAL_GREGORIAN;
        $month = $fn->getCPDate($work_date, 'n');

        $month2 = 1;
        if($create_month == '3 Months'){
            $month2 = 3;
        }
        else if($create_month == '6 Months'){
            $month2 = 6;
        }

        for ($m = 0; $m< $month2; $m++) {
            $work_date_converted = $fn->getCPDate($work_date, 'Y-m');
            $convertedDate  = date("Y-m", strtotime($work_date_converted. " + " . $m . "Months"));
            $convertedMonth = $fn->getCPDate($convertedDate, 'n');
            $convertedYear  = $fn->getCPDate($convertedDate, 'Y');
            $day_count = cal_days_in_month($type, $convertedMonth, $convertedYear);

            $day = 1;
            if($convertedMonth == $month){
                $day   = $fn->getCPDate($work_date, 'd');
            }

            for ($i = $day; $i <= $day_count; $i++) {

                $date = $convertedYear.'-'.$convertedMonth.'-'.$i;
                $get_name = date('l', strtotime($date));
                $day_name = substr($get_name, 0, 3);

                if($roster_type == 'Daily'){
                    if(in_array('Sunday', $daily_type) && in_array('Saturday & Sunday', $daily_type)){
                        if($day_name != 'Sun' && $day_name != 'Sat'){
                            $fa = array();
                            $fa['employment_id']  = $employee_id;
                            $fa['work_from_time'] = $work_from_time;
                            $fa['work_to_time']   = $work_to_time;

                            if ($cpCfg['cp.hasMultiUniqueSites']) {
                                $fa['site_id'] = $cpSiteIdSession;
                            }

                            $fa['work_date']      = $date;
                            $fa['creation_date']  = date("Y-m-d H:i:s");
                            $fa['created_by']     = $fn->getSessionParam('userName');
                            $insertAppointmentSQL = $dbUtil->getInsertSQLStringFromArray($fa, 'duty_roster');
                            $resultAppointmentSQL = $db->sql_query($insertAppointmentSQL);

                            if($work_from_time2 != '' && $work_to_time2 != ''){
                                $fa1 = array();
                                $fa1['employment_id']  = $employee_id;
                                $fa1['work_from_time'] = $work_from_time2;
                                $fa1['work_to_time']   = $work_to_time2;

                                if ($cpCfg['cp.hasMultiUniqueSites']) {
                                    $fa1['site_id'] = $cpSiteIdSession;
                                }

                                $fa1['work_date']      = $date;
                                $fa1['creation_date']  = date("Y-m-d H:i:s");
                                $fa1['created_by']     = $fn->getSessionParam('userName');
                                $insertAppointmentSQL = $dbUtil->getInsertSQLStringFromArray($fa1, 'duty_roster');
                                $resultAppointmentSQL = $db->sql_query($insertAppointmentSQL);
                            }

                            if($work_from_time3 != '' && $work_to_time3 != ''){
                                $fa2 = array();
                                $fa2['employment_id']  = $employee_id;
                                $fa2['work_from_time'] = $work_from_time3;
                                $fa2['work_to_time']   = $work_to_time3;

                                if ($cpCfg['cp.hasMultiUniqueSites']) {
                                    $fa2['site_id'] = $cpSiteIdSession;
                                }

                                $fa2['work_date']      = $date;
                                $fa2['creation_date']  = date("Y-m-d H:i:s");
                                $fa2['created_by']     = $fn->getSessionParam('userName');
                                $insertAppointmentSQL = $dbUtil->getInsertSQLStringFromArray($fa2, 'duty_roster');
                                $resultAppointmentSQL = $db->sql_query($insertAppointmentSQL);
                            }
                        }
                    }

                    else if(in_array('Sunday', $daily_type)){
                        if($day_name != 'Sun'){
                            $fa = array();
                            $fa['employment_id']  = $employee_id;
                            $fa['work_from_time'] = $work_from_time;
                            $fa['work_to_time']   = $work_to_time;

                            if ($cpCfg['cp.hasMultiUniqueSites']) {
                                $fa['site_id'] = $cpSiteIdSession;
                            }

                            $fa['work_date']      = $date;
                            $fa['creation_date']  = date("Y-m-d H:i:s");
                            $fa['created_by']     = $fn->getSessionParam('userName');
                            $insertAppointmentSQL = $dbUtil->getInsertSQLStringFromArray($fa, 'duty_roster');
                            $resultAppointmentSQL = $db->sql_query($insertAppointmentSQL);

                            if($work_from_time2 != '' && $work_to_time2 != ''){
                                $fa1 = array();
                                $fa1['employment_id']  = $employee_id;
                                $fa1['work_from_time'] = $work_from_time2;
                                $fa1['work_to_time']   = $work_to_time2;

                                if ($cpCfg['cp.hasMultiUniqueSites']) {
                                    $fa1['site_id'] = $cpSiteIdSession;
                                }

                                $fa1['work_date']      = $date;
                                $fa1['creation_date']  = date("Y-m-d H:i:s");
                                $fa1['created_by']     = $fn->getSessionParam('userName');
                                $insertAppointmentSQL = $dbUtil->getInsertSQLStringFromArray($fa1, 'duty_roster');
                                $resultAppointmentSQL = $db->sql_query($insertAppointmentSQL);
                            }

                            if($work_from_time3 != '' && $work_to_time3 != ''){
                                $fa2 = array();
                                $fa2['employment_id']  = $employee_id;
                                $fa2['work_from_time'] = $work_from_time3;
                                $fa2['work_to_time']   = $work_to_time3;

                                if ($cpCfg['cp.hasMultiUniqueSites']) {
                                    $fa2['site_id'] = $cpSiteIdSession;
                                }

                                $fa2['work_date']      = $date;
                                $fa2['creation_date']  = date("Y-m-d H:i:s");
                                $fa2['created_by']     = $fn->getSessionParam('userName');
                                $insertAppointmentSQL = $dbUtil->getInsertSQLStringFromArray($fa2, 'duty_roster');
                                $resultAppointmentSQL = $db->sql_query($insertAppointmentSQL);
                            }
                        }
                    }

                    else if(in_array('Saturday & Sunday', $daily_type)){
                        if($day_name != 'Sun' && $day_name != 'Sat'){
                            $fa = array();
                            $fa['employment_id']  = $employee_id;
                            $fa['work_from_time'] = $work_from_time;
                            $fa['work_to_time']   = $work_to_time;

                            if ($cpCfg['cp.hasMultiUniqueSites']) {
                                $fa['site_id'] = $cpSiteIdSession;
                            }

                            $fa['work_date']      = $date;
                            $fa['creation_date']  = date("Y-m-d H:i:s");
                            $fa['created_by']     = $fn->getSessionParam('userName');
                            $insertAppointmentSQL = $dbUtil->getInsertSQLStringFromArray($fa, 'duty_roster');
                            $resultAppointmentSQL = $db->sql_query($insertAppointmentSQL);

                            if($work_from_time2 != '' && $work_to_time2 != ''){
                                $fa1 = array();
                                $fa1['employment_id']  = $employee_id;
                                $fa1['work_from_time'] = $work_from_time2;
                                $fa1['work_to_time']   = $work_to_time2;

                                if ($cpCfg['cp.hasMultiUniqueSites']) {
                                    $fa1['site_id'] = $cpSiteIdSession;
                                }

                                $fa1['work_date']      = $date;
                                $fa1['creation_date']  = date("Y-m-d H:i:s");
                                $fa1['created_by']     = $fn->getSessionParam('userName');
                                $insertAppointmentSQL = $dbUtil->getInsertSQLStringFromArray($fa1, 'duty_roster');
                                $resultAppointmentSQL = $db->sql_query($insertAppointmentSQL);
                            }

                            if($work_from_time3 != '' && $work_to_time3 != ''){
                                $fa2 = array();
                                $fa2['employment_id']  = $employee_id;
                                $fa2['work_from_time'] = $work_from_time3;
                                $fa2['work_to_time']   = $work_to_time3;

                                if ($cpCfg['cp.hasMultiUniqueSites']) {
                                    $fa2['site_id'] = $cpSiteIdSession;
                                }

                                $fa2['work_date']      = $date;
                                $fa2['creation_date']  = date("Y-m-d H:i:s");
                                $fa2['created_by']     = $fn->getSessionParam('userName');
                                $insertAppointmentSQL = $dbUtil->getInsertSQLStringFromArray($fa2, 'duty_roster');
                                $resultAppointmentSQL = $db->sql_query($insertAppointmentSQL);
                            }
                        }
                    }

                    else{

                        $fa = array();
                        $fa['employment_id']  = $employee_id;
                        $fa['work_from_time'] = $work_from_time;
                        $fa['work_to_time']   = $work_to_time;

                        if ($cpCfg['cp.hasMultiUniqueSites']) {
                            $fa['site_id'] = $cpSiteIdSession;
                        }

                        $fa['work_date']      = $date;
                        $fa['creation_date']  = date("Y-m-d H:i:s");
                        $fa['created_by']     = $fn->getSessionParam('userName');
                        $insertAppointmentSQL = $dbUtil->getInsertSQLStringFromArray($fa, 'duty_roster');
                        $resultAppointmentSQL = $db->sql_query($insertAppointmentSQL);

                        if($work_from_time2 != '' && $work_to_time2 != ''){
                            $fa1 = array();
                            $fa1['employment_id']  = $employee_id;
                            $fa1['work_from_time'] = $work_from_time2;
                            $fa1['work_to_time']   = $work_to_time2;

                            if ($cpCfg['cp.hasMultiUniqueSites']) {
                                $fa1['site_id'] = $cpSiteIdSession;
                            }

                            $fa1['work_date']      = $date;
                            $fa1['creation_date']  = date("Y-m-d H:i:s");
                            $fa1['created_by']     = $fn->getSessionParam('userName');
                            $insertAppointmentSQL = $dbUtil->getInsertSQLStringFromArray($fa1, 'duty_roster');
                            $resultAppointmentSQL = $db->sql_query($insertAppointmentSQL);
                        }

                        if($work_from_time3 != '' && $work_to_time3 != ''){
                            $fa2 = array();
                            $fa2['employment_id']  = $employee_id;
                            $fa2['work_from_time'] = $work_from_time3;
                            $fa2['work_to_time']   = $work_to_time3;

                            if ($cpCfg['cp.hasMultiUniqueSites']) {
                                $fa2['site_id'] = $cpSiteIdSession;
                            }

                            $fa2['work_date']      = $date;
                            $fa2['creation_date']  = date("Y-m-d H:i:s");
                            $fa2['created_by']     = $fn->getSessionParam('userName');
                            $insertAppointmentSQL = $dbUtil->getInsertSQLStringFromArray($fa2, 'duty_roster');
                            $resultAppointmentSQL = $db->sql_query($insertAppointmentSQL);
                        }
                    }
                }elseif ($roster_type == 'Weekly') {
                    if(in_array($day_name, $weekdays_select)){
                        $fa = array();
                        $fa['employment_id']  = $employee_id;
                        $fa['work_from_time'] = $work_from_time;
                        $fa['work_to_time']   = $work_to_time;

                        if ($cpCfg['cp.hasMultiUniqueSites']) {
                            $fa['site_id'] = $cpSiteIdSession;
                        }

                        $fa['work_date']      = $date;
                        $fa['creation_date']  = date("Y-m-d H:i:s");
                        $fa['created_by']     = $fn->getSessionParam('userName');
                        $insertAppointmentSQL = $dbUtil->getInsertSQLStringFromArray($fa, 'duty_roster');
                        $resultAppointmentSQL = $db->sql_query($insertAppointmentSQL);

                        if($work_from_time2 != '' && $work_to_time2 != ''){
                            $fa1 = array();
                            $fa1['employment_id']  = $employee_id;
                            $fa1['work_from_time'] = $work_from_time2;
                            $fa1['work_to_time']   = $work_to_time2;

                            if ($cpCfg['cp.hasMultiUniqueSites']) {
                                $fa1['site_id'] = $cpSiteIdSession;
                            }

                            $fa1['work_date']      = $date;
                            $fa1['creation_date']  = date("Y-m-d H:i:s");
                            $fa1['created_by']     = $fn->getSessionParam('userName');
                            $insertAppointmentSQL = $dbUtil->getInsertSQLStringFromArray($fa1, 'duty_roster');
                            $resultAppointmentSQL = $db->sql_query($insertAppointmentSQL);
                        }

                        if($work_from_time3 != '' && $work_to_time3 != ''){
                            $fa2 = array();
                            $fa2['employment_id']  = $employee_id;
                            $fa2['work_from_time'] = $work_from_time3;
                            $fa2['work_to_time']   = $work_to_time3;

                            if ($cpCfg['cp.hasMultiUniqueSites']) {
                                $fa2['site_id'] = $cpSiteIdSession;
                            }

                            $fa2['work_date']      = $date;
                            $fa2['creation_date']  = date("Y-m-d H:i:s");
                            $fa2['created_by']     = $fn->getSessionParam('userName');
                            $insertAppointmentSQL = $dbUtil->getInsertSQLStringFromArray($fa2, 'duty_roster');
                            $resultAppointmentSQL = $db->sql_query($insertAppointmentSQL);
                        }
                    }
                }
                else{

                    $fa = array();
                    $fa['employment_id']  = $employee_id;
                    $fa['work_from_time'] = $work_from_time;
                    $fa['work_to_time']   = $work_to_time;
                    $fa['work_date']      = $date;

                    if ($cpCfg['cp.hasMultiUniqueSites']) {
                        $fa['site_id'] = $cpSiteIdSession;
                    }

                    $fa['creation_date']  = date("Y-m-d H:i:s");
                    $fa['created_by']     = $fn->getSessionParam('userName');
                    $insertAppointmentSQL = $dbUtil->getInsertSQLStringFromArray($fa, 'duty_roster');
                    $resultAppointmentSQL = $db->sql_query($insertAppointmentSQL);

                    if($work_from_time2 != '' && $work_to_time2 != ''){
                        $fa1 = array();
                        $fa1['employment_id']  = $employee_id;
                        $fa1['work_from_time'] = $work_from_time2;
                        $fa1['work_to_time']   = $work_to_time2;

                        if ($cpCfg['cp.hasMultiUniqueSites']) {
                            $fa1['site_id'] = $cpSiteIdSession;
                        }

                        $fa1['work_date']      = $date;
                        $fa1['creation_date']  = date("Y-m-d H:i:s");
                        $fa1['created_by']     = $fn->getSessionParam('userName');
                        $insertAppointmentSQL = $dbUtil->getInsertSQLStringFromArray($fa1, 'duty_roster');
                        $resultAppointmentSQL = $db->sql_query($insertAppointmentSQL);
                    }

                    if($work_from_time3 != '' && $work_to_time3 != ''){
                        $fa2 = array();
                        $fa2['employment_id']  = $employee_id;
                        $fa2['work_from_time'] = $work_from_time3;
                        $fa2['work_to_time']   = $work_to_time3;

                        if ($cpCfg['cp.hasMultiUniqueSites']) {
                            $fa2['site_id'] = $cpSiteIdSession;
                        }

                        $fa2['work_date']      = $date;
                        $fa2['creation_date']  = date("Y-m-d H:i:s");
                        $fa2['created_by']     = $fn->getSessionParam('userName');
                        $insertAppointmentSQL = $dbUtil->getInsertSQLStringFromArray($fa2, 'duty_roster');
                        $resultAppointmentSQL = $db->sql_query($insertAppointmentSQL);
                    }
                }

            }
        }

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getDutyRosterEditFormValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $work_from_time  = $fn->getPostParam('work_from_time');
        $work_to_time    = $fn->getPostParam('work_to_time');

        $validate->resetErrorArray();
        $validate->validateData('employee_id', 'Please Select Doctor/Nurse');

        $work_from_time  = strtotime($work_from_time);
        $work_to_time    = strtotime($work_to_time);

        if($work_to_time == ''){
            $validate->validateData('work_to_time', 'Please Select time out');
        }

        if($work_to_time != ''){
            if($work_to_time <= $work_from_time){
                $validate->errorArray['start_date']['name'] = 'work_to_time';
                $validate->errorArray['start_date']['msg']  = 'Time Out should not be less/equal to Time In';
            }
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
    function getDutyRosterEditFormSubmit(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        if (!$this->getDutyRosterEditFormValidate()){
            return $validate->getErrorMessageXML();
        }

        $update_type            = $fn->getPostParam('update_type');
        $work_from_time         = $fn->getPostParam('work_from_time');
        $work_to_time           = $fn->getPostParam('work_to_time');
        $employee_id            = $fn->getPostParam('employee_id');
        $duty_roster_id         = $fn->getPostParam('duty_roster_id');
        $employee_id_current    = $fn->getPostParam('employee_id_current');
        $work_from_time_current = $fn->getPostParam('work_from_time_current');
        $work_to_time_current   = $fn->getPostParam('work_to_time_current');
        $work_date              = $fn->getPostParam('work_date');

        $fa = array();
        $fa['employment_id']      = $employee_id;
        $fa['work_from_time']     = $work_from_time;
        $fa['work_to_time']       = $work_to_time;
        $fa['modification_date']  = date("Y-m-d H:i:s");
        $fa['modified_by']        = $fn->getSessionParam('userName');

        if($update_type == 'Edit this Occurence'){
            $whereCondition = "WHERE duty_roster_id = {$duty_roster_id}";
            $updateSQL      = $dbUtil->getUpdateSQLStringFromArray($fa, 'duty_roster', $whereCondition);
            $resultSQL      = $db->sql_query($updateSQL);
        }
        else{
            $whereCondition = "WHERE employment_id = {$employee_id_current}  AND work_from_time = '{$work_from_time_current}' AND work_to_time = '{$work_to_time_current}' AND work_date >= '{$work_date}'";
            $updateSQL      = $dbUtil->getUpdateSQLStringFromArray($fa, 'duty_roster', $whereCondition);
            $resultSQL      = $db->sql_query($updateSQL);
        }

        return $validate->getSuccessMessageXML();
    }


    /**
     *
     */
    function getPrintDutyRosterFormValidate(){
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $validate->resetErrorArray();
        $validate->validateData('duty_year', 'Please Select Year');
        $validate->validateData('duty_Month', 'Please Select Month');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getPrintDutyRosterFormSubmit(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        if (!$this->getPrintDutyRosterFormValidate()){
            return $validate->getErrorMessageXML();
        }

        return $validate->getSuccessMessageXML();
    }


}


