<?
class CPL_Admin_Modules_EnggCrm_Employee_View extends CP_Admin_Modules_EnggCrm_Employee_View
{

    /**
     *
     */
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $fn = Zend_Registry::get('fn');

        $count   = 0;
        $rows    = '';

        foreach ($dataArray as $row){

            if ($row['employee_work_type'] == 'Part time') {
                $amount = $row['day_rate'];
            } else {
                $amount = $row['salary'];
            }

            $rows .= "
            {$listObj->getListRowHeader($row, $count)}
            {$listObj->getGoToDetailText($count, $row['employee_name'])}
            {$listObj->getListDateCell($row['date_of_expiry'])}
            {$listObj->getListDataCell($row['spass_no'])}
            {$listObj->getListDataCell($row['employee_work_type'])}
            {$listObj->getListDataCell($amount)}
            {$listObj->getListDataCell($row['status'])}
            {$listObj->getListRowEnd($row['employee_id'])}
            ";

            $count++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Name', 'a.employee_name')}
        {$listObj->getListHeaderCell('Date of Expiry', 'a.date_of_expiry')}
        {$listObj->getListHeaderCell('S Pass No', 'a.spass_no')}
        {$listObj->getListHeaderCell('Part Time / Full Time', 'a.employee_work_type')}
        {$listObj->getListHeaderCell('Hourly Rate / Salary', 'a.employee_name')}
        {$listObj->getListHeaderCell('Status', 'a.status')}
        {$listObj->getListHeaderEnd()}
        {$rows}
        {$listObj->getListFooter()}
        ";

        return $text;
    }

    /**
     *
     */
    function getEdit($row){
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $formObj->mode  = $tv['action'];

        $chineseName    = '';
        $chinesePos     = '';
        $chineseDept    = '';
        $compAddressDD  = '';
        $companyAddress = '';
        $staffDetail    = '';
        $personalAdd    = '';
        $compLink       = '';

        $sqlCategory            = $fn->getValueListSQL('employeeCategory');
        $sqlTitle               = $fn->getValueListSQL('contactTitle');
        $sqlEmployeeWorkType    = $fn->getValueListSQL('employeeWorkType');
        $sqlPosition            = $fn->getValueListSQL('positionType','value');
        $sqlComp                = $fn->getDDSql('enggCrm_company');
        
        if ($cpCfg['m.enggCrm.employee.showChineseFields'] == 1){
            $chineseName = $formObj->getTBRow('Name (Chinese)', 'chi_name', $row['chi_name']);
            $chinesePos  = $formObj->getTBRow('Position (Chinese)', 'chi_position', $row['chi_position']);
            $chineseDept = $formObj->getTBRow('Department (Chinese)', 'chi_department', $row['chi_department']);
        }

        if ($tv['action'] == 'edit'){
            if($cpCfg['m.enggCrm.hasMultipleCompanyAddress'] == 1){
                $sqlCombo = "
                SELECT company_address_id
                      ,CONCAT_WS(', ', address_flat, address_street, address_town, address_country) AS address
                FROM  company_address a
                WHERE company_id = '{$row['company_id']}'
                ORDER BY company_address_id
                ";
                $compAddressDD = "
                {$formObj->getDDRowBySQL('Company Address', 'company_address_id', $sqlCombo, $row['company_address_id'])}
                ";
            }
        }

        if ($cpCfg['m.enggCrm.employee.showDetail'] == 1){
            $sqlCombo = "
            SELECT staff_id
                  ,CONCAT_WS(' ', a.first_name, a.last_name) AS staff_name
            FROM staff a
            ORDER BY staff_name";

            $fieldset = "
            {$formObj->getDDRowBySQL("{$cpCfg['m.enggCrm.staffFieldLabel']}", "staff_id", $sqlCombo, $row['staff_id'])}
            ";

            $staffDetail = $formObj->getFieldSetWrapped($cpCfg['m.enggCrm.staffFieldLabel'], $fieldset);
        }

        $expVl = array('sqlType' => 'OneField');
        $sqlCountry = getCPModelObj('common_geoCountry')->getCountryDDSQL();
        $expCountry = array('detailValue' => $row['country_name']);

        $formAddPosition = "index.php?_topRm={$tv['topRm']}&module=enggCrm_employee&_spAction=addNewValuelistForm&valuelist_name=positionType&employee_id={$row['employee_id']}&showHTML=0";
        $expPosition     = array('sqlType' => 'OneField'
                            ,'notesRight' => "<a href='{$formAddPosition}' class='mr20 addNewValue' valuelist_name='positionType'>Add</a>");

        //{$formObj->getDDRowBySQL('Choose Category', 'category', $sqlCategory, $row['category'], $expVl)}
        $fielset1 = "
        {$formObj->getDDRowBySQL('Salutation', 'salutation', $sqlTitle, $row['salutation'], $expVl)}
        {$formObj->getTBRow('Name', 'employee_name', $row['employee_name'])}
        {$chineseName}
        {$formObj->getTBRow('Email', 'email', $row['email'])}
        {$formObj->getTBRow('Passport No', 'passport', $row['passport'])}
        {$formObj->getTBRow('FIN No *', 'nric_no', $row['nric_no'])}
        {$formObj->getTBRow('S Pass No*', 'spass_no', $row['spass_no'])}
        {$formObj->getDateRow('Date of Birth', 'date_of_birth', $row['date_of_birth'])}
        {$formObj->getDateRow('Date of Expiry', 'date_of_expiry', $row['date_of_expiry'])}
        {$formObj->getDDRowByVL('Status', 'status', 'employeeStatus', $row['status'], $expVl)}
        {$formObj->getDDRowBySQL('Full Time & Part Time *', 'employee_work_type', $sqlEmployeeWorkType, $row['employee_work_type'], $expVl)}
        <div class='addHourlyRate'>{$formObj->getTBRow('Day Rate', 'day_rate', $row['day_rate'])}</div>
        <div class='salaryForFullTime'>{$formObj->getTBRow('Salary', 'salary', $row['salary'])}</div>
        {$formObj->getTBRow('Hourly Rate', 'add_hourly_rate', $row['add_hourly_rate'])}
        {$formObj->getTBRow('OT Rate', 'overtime_rate', $row['overtime_rate'])}
        {$formObj->getDDRowBySQL('Position', 'position', $sqlPosition, $row['position'], $expPosition)}
        ";     

        if ($formObj->mode == 'edit'){
            $compLink = "<a class='editLinkSingle' href='' link='{$fn->getOpenLinkUrl('enggCrm_employee', 'enggCrm_companyLink', 'fld_company_id')}'>Choose</a>";
        }
        $expComp  = array('notesRight' => $compLink, 'detailValue' => $row['c_company_name']);

        $fielset2 = "
        {$formObj->getTBRow('Flat / Building', 'address_flat', $row['address_flat'])}
        {$formObj->getTBRow('Street Address', 'address_street', $row['address_street'])}
        {$formObj->getTBRow('Phone', 'phone_direct', $row['phone_direct'])}
        {$formObj->getTBRow('Fax', 'fax', $row['fax'])}
        {$formObj->getTBRow('Mobile', 'mobile', $row['mobile'])}
        {$formObj->getTBRow('District/ Town', 'address_town', $row['address_town'])}
        {$formObj->getTBRow('State/ Zip', 'address_state', $row['address_state'])}
        {$formObj->getDDRowBySQL('Country', 'address_country', $sqlCountry, $row['address_country'], $expCountry)}
        ";
        
        $text = "
        {$formObj->getFieldSetWrapped('Employee Details', $fielset1)}
        {$formObj->getFieldSetWrapped('Employee Address Details', $fielset2)}
        {$staffDetail}
        {$personalAdd}
        {$formObj->getCreationModificationText($row)}
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
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
   
        $special_search     = $fn->getReqParam('special_search');
        $employee_work_type = $fn->getReqParam('employee_work_type');
        $employee_status    = $fn->getReqParam('employee_status');
        
        $emp_typeArray = array(
              "Full Time"
             ,"Part Time"
             ,"Contract"
        );

        $spArray = array(
              "Flagged"
             ,"Not-Flagged"
        );

        $status = array(
              "Current"
             ,"Archive"
             ,"Cancel"
        );

        $text = "
        <td>
            <select name='employee_work_type'>
                <option value=''>Employee Work Type</option>
                {$cpUtil->getDropDown1($emp_typeArray, $employee_work_type)}
            </select>
        </td>
        <td>
            <select name='special_search'>
                <option value=''>Special Search</option>
                {$cpUtil->getDropDown1($spArray, $tv['special_search'])}
            </select>
        </td>
        <td>
            <select name='employee_status'>
                <option value=''>Employee Status</option>
                {$cpUtil->getDropDown1($status, $employee_status)}
            </select>
        </td>
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
        $media = Zend_Registry::get('media');
        $comment = getCPPluginObj('common_comment');
        
        $rows = "";

        if( $cpCfg['m.enggCrm.employee.showEvent'] == "1"){
            $rows .= $displayLinkData->getLinkPortalMain("enggCrm_employee", "event_eventLink", "Events Linked", $row);
        }

        $record_id = $fn->getIssetParam($row, 'employee_id');
        $employeeAttachment = $this->getEmployeeAttachmentDisplay($row['employee_id']);

        $text = "
        {$media->getRightPanelMediaDisplay("Picture", "enggCrm_employee", "picture", $row)}
        {$media->getRightPanelMediaDisplay('Work Permit', 'enggCrm_employee', 'workPermit', $row)}
        {$media->getRightPanelMediaDisplay('WSQ', 'enggCrm_employee', 'wsq', $row)}
        {$rows}
        <div id='EmployeeAttachmentLinkPortal'>{$employeeAttachment}</div>
        <div id='employeeCategoryPortal'>{$this->getEmployeeCategoryPortal($row['employee_id'])}</div>
        {$comment->getView(array(
             'roomName' => 'enggCrm_employee'
            ,'recordId' => $record_id
        ))}
        ";

        return $text;
    }

    /**
     *
     */
    function getEmployeeCategoryPortal($employee_id=''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        if($employee_id == ''){
            $employee_id = $fn->getReqParam('employee_id');
        }

        $employeeCategory = $this->getEmployeeCategoryDetail($employee_id);

        $recCount = $fn->getRecordCount('employee_category', "employee_id = '{$employee_id}'");

        $header ="
        <thead>
            <tr>
                <th>Category</th>
                <th class='portalActBtns'></th>
            </tr>
        </thead>
        ";

        if($recCount == 0){
            $header = "<tr><td align='center'>No Records Linked<br/><br/></td></tr>";
        }

        $formActionEmployeeCategory = "index.php?module=enggCrm_employee&_spAction=addEmployeeCategory&employee_id={$employee_id}&showHTML=0";

        $add = "<div class='actBtns'>
                    <a id='addEmployeeCategory' href='{$formActionEmployeeCategory}' employee_id='{$employee_id}'>
                        Add
                    </a>
                </div>
                ";

        $text = "
        <div class='linkPortalWrapper hms_dosage_agewiseLink'>
            <div class='header' expanded='1'>
                <div class='floatbox'>
                    <div class='float_left'>Employee Category</div>
                    <div class='txtRight'>
                        <span class='count'>({$recCount})</span>
                    </div>
                </div>
            </div>
            <div class='linkPortalDataWrapper'>
                <form>
                    <table class='renewallist'>
                        {$header}
                        <tbody id='addEmployeeCategoryPortal'>
                            {$employeeCategory}
                        </tbody>
                    </table>
                    <input type='hidden' name='employee_id' value='{$employee_id}' />
                </form>
            </div>
            {$add}
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getEmployeeCategoryDetail($employee_id = ''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        if($employee_id == ''){
            $employee_id = $fn->getReqParam('employee_id');
        }

        $rows  = "";

        $SQL="
        SELECT ec.*
        FROM employee_category ec
        WHERE ec.employee_id = '{$employee_id}'
        ";
        $result   = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);
        
        $count = 1;
        while ($row = $db->sql_fetchrow($result)) {
            $deleteIcon ="
            <div class='float_right'>
                <a class='deleteEmployeeCategory' href='#'  employee_category_id='{$row['employee_category_id']}' employee_id='{$row['employee_id']}'>
                    <img src='/cmspilotv30/CP/admin/images/icons/btn_remove.png'>
                </a>
            </div>
            ";

            $rows .= "
                <tr>
                    <td>{$row['category']}</td>
                    <td>
                        {$deleteIcon}
                    </td>
                </tr>
            ";
            $count++;
        }

        $text = "{$rows}";

        return $text;
    }
    /**
     *
     */
    function getAddEmployeeCategory() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $expVl = array('sqlType' => 'OneField');

        $employee_id  = $fn->getReqParam('employee_id');
        $SQLcategory      = $fn->getValueListSQL('employeeCategory');

        $formAction = "index.php?_topRm=main&module=enggCrm_employee&_spAction=employeeCategorySubmit&showHTML=0";

        $text = "
        <form id='employeeCategoryPortalForm' class='yform columnar' method='post' action='{$formAction}'>
            {$formObj->getDDRowBySQL('Category', 'category', $SQLcategory, '', $expVl)}
            <input type='hidden' name='employee_id' value='{$employee_id}' />
        </form>
        ";
        return $text;
    }    

    /**
     */
    function getEmployeeAttachmentDisplay($employee_id){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        $valArray = array(
              "workPermit" => "Work Permit"
             ,"wsq" => "WSQ"
        );

        $urlPrintEmployeeAttachmentPdf  = "index.php?_topRm=project&module=enggCrm_employee&_spAction=printEmployeeAttachmentPdf&employee_id={$employee_id}&showHTML=0";
        $exp = array('useKey' => 1);

        $text = "
        <div class='linkPortalWrapper enggCrm_employee__enggCrm_employeeLink'>
            <div class='header' expanded='1'>
                <div class='floatbox'>
                    <div class='float_left'>Employee attachment display</div>
                    <div class='float_right'>
                        <a href='#' class='printAttachmentLink' employee_id='{$employee_id}'><u>Print attachment pdf</u></a>
                    </div>
                </div>
            </div>
            <div class='linkPortalDataWrapper'>
                <form>
                    <table class='employeeAttachmentlist'>
                        <tr>
                            <td class='employeeAttachmentCheckBox'>{$formObj->getCheckBoxArrRowByArr('', 'attach_type', $valArray, '', $exp)}</td>
                            <input id='employee_id' type='hidden' name='employee_id' value='{$employee_id}' />
                        </tr>
                    </table>
                </form>
            </div>
        </div>
        ";

        return $text;

    }   

    /**
     *
     */
    function getPrintEmployeeAttachmentPdf() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        ini_set('memory_limit', '512M');
        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/tcpdf/tcpdf.php');
        include_once(CP_LOCAL_PATH.'lib/headfootNoHeaderFooter.php');

        $pdf = new MYPDF_Local(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('USS');
        $pdf->SetSubject('Employee Attachment Report');
        $pdf->SetTitle('Employee Attachment Report');

        // set default header data
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 04', PDF_HEADER_STRING);
        // set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER,10);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        //$pdf->setPrintFooter(false);

        // set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf->setLanguageArray($l);
        }

        /*HEADER PART AND FOOTER PART FUNCTIONS HAS BEEN ADDED IN (headfoot.php) PATH INCLUDE: (admin/lib/headfoot.php)*/
        $pdf->AddPage();

        $employee_id = $fn->getReqParam('employee_id');
        $attachType = $fn->getReqParam('attachType');
        $empRec = $fn->getRecordRowByID('employee', 'employee_id', $employee_id);
        $pdf->SetFont('', '', 9);

        $today = date("d-m-Y");
        $currentDate   = date("d-m-Y");

        $tbl3 = '
        <table border="0" width="100%" cellpadding="4">
            <tr>
                <td width="100%" align="left" style="font-size:14px;">'.strtoupper($empRec['employee_name']).'</td>
            </tr>
        </table>
        ';

        $pdf->writeHTML($tbl3, true, false, false, false, '');

        $SQLMedia = "
        SELECT file_name, record_type
        FROM media
        WHERE record_id = '{$employee_id}'
        AND room_name   = 'enggCrm_employee'
        AND record_type IN ({$attachType})
        ORDER BY record_type, sort_order ASC
        ";
        $resultMedia  = $db->sql_query($SQLMedia);
        $numRowsMedia = $db->sql_numrows($resultMedia);

        if($numRowsMedia > 0) {
            $count = 1;
            $record_type = '';
            while($rowMedia = $db->sql_fetchrow($resultMedia)) {
                $tbl4 = '<table cellpadding="4" border="0">'; 
                $imageAttached = realpath($cpCfg['cp.mediaFolder']).'/normal/'.$rowMedia['file_name'];
                $tbl4 = $tbl4.'<tr>';
                $tbl4 = $tbl4.'<td><img src="'.$imageAttached.'"></td>';
                $tbl4 = $tbl4.'</tr>';
                $tbl4 = $tbl4.'</table>';

                if($count > 1 && $record_type != $rowMedia['record_type']){
                    $pdf->AddPage();
                }
                $pdf->writeHTML($tbl4, true, false, false, false, '');
                $count++;
                $record_type = $rowMedia['record_type'];
            }
        }

        $download_title = 'employee_attachment_Report.pdf';
        $pdf->Output($download_title, 'I');
    }
}