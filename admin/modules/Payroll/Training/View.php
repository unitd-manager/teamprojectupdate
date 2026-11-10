<?
class CPL_Admin_Modules_Payroll_Training_View extends CP_Admin_Modules_Payroll_Training_View
{
    /**
     *
     */
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $dateUtil = Zend_Registry::get('dateUtil');

        $count   = 0;
        $rows    = '';

        foreach ($dataArray as $row){

            $date = $dateUtil->formatDate($row['date'], 'DD-MM-YYYY');

            $rows .= "
            {$listObj->getListRowHeader($row, $count)}
            {$listObj->getListDataCell($row['title'])}
            {$listObj->getListDataCell($row['trainer'])}
            {$listObj->getListDataCell($date)}
            {$listObj->getListRowEnd($row['training_id'])}
            ";

            $count++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Title', 't.title')}
        {$listObj->getListHeaderCell('Trainer', 't.trainer')}
        {$listObj->getListHeaderCell('Date', 't.date')}
        {$listObj->getListHeaderEnd()}
        {$rows}
        {$listObj->getListFooter()}
        ";

        return $text;
    }

    /**
     *
     */
    function getNew(){
        $formObj = Zend_Registry::get('formObj');

        $fielset1 = "
        {$formObj->getTBRow('Title', 'title')}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fielset1)}
        ";

        return $text;
    }

    /**
     *
     */
    function getEdit($row){
        $formObj = Zend_Registry::get('formObj');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        $formObj->mode = $tv['action'];


        $text = "
        <div class='linkPortalWrapper'>
            <div expanded='1' class='header'>
                <div class='floatbox'>
                    <div class='float_left'>Step 1 (Main Details)</div>
                    <div class='toggle'></div>
                </div>
            </div>
            <div>
                <div class='linkPortalDataWrapper'>
                    <table class='thinlist'>
                        <tbody>
                            <tr>
                                <td>{$formObj->getTBRow('Title', 'title', $row['title'])}</td>
                                <td>{$formObj->getDateRow('From date', 'date', $row['date'])}</td>
                                <td>{$formObj->getDateRow('To date', 'to_date', $row['to_date'])}</td>
                                <td>{$formObj->getTBRow('Trainer', 'trainer', $row['trainer'])}</td>
                            </tr>
                            <tr>
                                <td>{$formObj->getTARow('Description', 'description', $row['description'])}</td>
                            </tr>

                            <tr>
                                <th colspan='4'>Training Company Details</th>
                            </tr>
                            <tr>
                                <td>{$formObj->getTBRow('Training Company name', 'training_company_name', $row['training_company_name'])}</td>
                                <td>{$formObj->getTARow('Training company address', 'training_company_address', $row['training_company_address'])}</td>
                                <td>{$formObj->getTBRow('Training company email', 'training_company_email', $row['training_company_email'])}</td>
                                <td>{$formObj->getTBRow('Training company phone', 'training_company_phone', $row['training_company_phone'])}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getPrintDetail($row){
        $db = Zend_Registry::get('db');
        return $this->getDetail($row);
    }

    /**
     *
     */
    function getSearch(){
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $sqlCategory = $fn->getValueListSQL('companyCategory');
        $sqlStatus   = $fn->getValueListSQL('companyStatus');
        $expVl = array('sqlType' => 'OneField');

        $spArray = array(
            "Flagged"
           ,"Not-Flagged"
        );

        $fielset = "
        {$formObj->getTBRow('Company Name', 'company_name')}
        {$formObj->getDDRowBySQL('Choose Category', 'category', $sqlCategory, 'Client', $expVl)}
        {$formObj->getDDRowBySQL('Status', 'status', $sqlStatus, 'Current', $expVl)}
        {$formObj->getDDRowByArr('Special Search', 'special_search', $spArray)}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Company Details', $fielset)}
        ";

        return $text;
    }

    /**
     *
     */
    function getRightPanel($row){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $comment = getCPPluginObj('common_comment');
        $db = Zend_Registry::get('db');
        $media = Zend_Registry::get('media');

        $record_id = $fn->getIssetParam($row, 'training_id');
        $training_id  = $fn->getReqParam('training_id');

        $text = "
        {$media->getRightPanelMediaDisplay('Attachments', 'payroll_training', 'attachment', $row)}
        ";

        $sqlTraining = "
        SELECT t.*
        FROM training t
        WHERE t.training_id = {$row['training_id']}
        ";

        $resultTraining = $db->sql_query($sqlTraining);
        $rowTraining = $db->sql_fetchrow($resultTraining);

        $printText ="";
        if ($rowTraining['training_id'] != '') {
            $printText .="
            <div id='employeeLinkPortal'>{$this->getAddTrainingEmplyoee($row['training_id'])}</div>
            ";
        }
        $text=$text.$printText;

        return $text;
    }

    /**
     *
     */
    function getAddTrainingEmplyoee($training_id=''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        if($training_id == ''){
            $training_id = $fn->getReqParam('training_id');
        }

        $TrainingEmplyoee = $this->getAddTrainingEmplyoeeDetail($training_id);

        $recCount = $fn->getRecordCount('training_staff', "training_id = '{$training_id}'");

        $header ="
        <thead>
            <tr>
                <th>Employee Name</th>
                <th>From Date</th>
                <th>To Date</th>
            </tr>
        </thead>
        ";

        if($recCount == 0){
            $header ="<thead></thead>";
        }

        $formActionTrainingEmplyoee = "index.php?module=payroll_training&_spAction=TrainingEmplyoee&training_id={$training_id}&showHTML=0";

        $add = "<div class='actBtns'>
                    <a id='AddTrainingEmplyoee' href='{$formActionTrainingEmplyoee}' training_id={$training_id}>Add</a>
                </div>";

        $addRecord = "
        <div class='button'>
            <a href='#' id='linkEmployeeToCourse' class='ml0' training_id={$training_id}>Link Employee</a>
        </div>
        ";

        $text = "
        <div class='linkPortalWrapper payroll_training__payroll_training_staffLink'>
            {$addRecord}
            <div class='header' expanded='1'>
                <div class='floatbox'>
                    <div class='float_left'>Employee Linked</div>
                    <div class='txtRight'>
                    <span class='count'>({$recCount})</span>
                    </div>
                </div>
            </div>
            <div class='linkPortalDataWrapper'>
                <form>
                    <table class='employeeLinkPortalTable'>
                        {$header}
                        <tbody id='AddTrainingEmplyoeePortal'>
                            {$TrainingEmplyoee}
                        </tbody>
                    </table>
                    <input type='hidden' name='training_id' value='{$training_id}' />
                </form>
            </div>
        </div>
        ";

        return $text;

    }
    /**
     *
     */
    function getAddTrainingEmplyoeeDetail($training_id = ''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        if($training_id == ''){
            $training_id = $fn->getReqParam('training_id');
        }

        $training_staff_id = $fn->getReqParam('training_staff_id');

        $rows  = "";

        $SQL="
        SELECT ts.*
               ,CONCAT_WS(' ', e.first_name, e.last_name) AS employee_name
               ,ji.designation
        FROM training_staff ts
        LEFT JOIN (employee e) ON (ts.staff_id = e.employee_id)
        LEFT JOIN (job_information ji) ON (e.employee_id = ji.employee_id)
        WHERE training_id = '{$training_id}'
        ORDER BY training_staff_id DESC
        ";
        $result   = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        $sqlstaff = "
        SELECT e.employee_id
              ,CONCAT_WS(' ', e.first_name, e.last_name) AS employee_name
        FROM employee e
        WHERE e.status = 'Current'
        ORDER BY employee_name ASC
        "; 

        $count = 1;
        while ($row = $db->sql_fetchrow($result)) {

            //$formActionDeleteMedicineTemplate = "index.php?module=hms_treatment&_spAction=DeleteMedicineTemplate&treatment_medicine_template_id={$row['treatment_medicine_template_id']}&treatment_id={$treatment_id}&showHTML=0";

            $deleteIcon ="
            <div class='float_right'>
                <a class='deleteTrainingEmplyoee' href='#'  training_staff_id='{$row['training_staff_id']}'>
                    <img src='/cmspilotv30/CP/admin/images/icons/btn_remove.png'>
                </a>
            </div>
            ";

            $training_staff_id = $row['training_staff_id'];

            $from_date = $formObj->getDateRow('', 'employee_from_date'.$training_staff_id, $row['from_date']);
            $to_date   = $formObj->getDateRow('', 'employee_to_date'.$training_staff_id, $row['to_date']);

            $rows .= "
            <tr rec_id='{$row['training_staff_id']}'>
                <td>{$formObj->getDDRowBySQL('', 'employee_staff_id', $sqlstaff, $row['staff_id'])}</td>
                <td>
                    <div class='employeeFromDate'>
                        {$from_date}
                    </div>
                </td>
                <td>
                    <div class='employeeToDate'>
                        {$to_date}
                    </div>
                </td>
                <td>
                    {$deleteIcon}
                </td>
            </tr>
            ";
            $count++;
        }


        if($numRows == 0){
            $rows .= "
                <tr>
                    <td class='noRenewal'>No Records Linked</td>
                </tr>
            ";

        }
        $text="{$rows}";

        return $text;
    }

    /**
     *
     */

    function getTrainingEmplyoee() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $row = '';
        $training_id  = $fn->getReqParam('training_id');

        $formAction = "index.php?_topRm=order&module=payroll_training&_spAction=TrainingEmplyoeeFormSubmit&showHTML=0";

        $sqlstaff = "
        SELECT e.employee_id
              ,CONCAT_WS(' ', e.first_name, e.last_name) AS employee_name
        FROM employee e
        WHERE e.status = 'Current'
        ORDER BY employee_name ASC
        ";

        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            {$formObj->getDDRowBySQL('Employee Name', 'staff_id', $sqlstaff,'')}
            <input type='hidden' name='training_id' value='{$training_id}' />
        </form>
        ";
        return $text;
    }

     /**
     *
     */
    function getQuickSearch() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        $employee_id = $fn->getReqParam('employee_id');

        $sqlEmployeeName = "
        SELECT e.employee_id
              ,CONCAT_WS(' ', e.first_name, e.last_name) AS employee_name
        FROM employee e
        ORDER BY employee_name
        ";

        $spArray = array(
            ""
           ,"Flagged"
           ,"Not-Flagged"
        );

        $text = "
        <td>
            <select name='employee_id' >
                <option value=''>Employee Name</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlEmployeeName, $employee_id)}
            </select>
        </td>
        <td>
            <select name='special_search'>
                <option value=''>Special Search</option
                {$cpUtil->getDropDown1($spArray, $tv['special_search'])}
           </select>
        </td>
        ";

        return $text;
    }
     /**
     *
     */
}