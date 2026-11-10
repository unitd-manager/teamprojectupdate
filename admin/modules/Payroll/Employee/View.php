<?
class CPL_Admin_Modules_Payroll_Employee_View extends CP_Admin_Modules_Payroll_Employee_View
{
    /**
     *
     */
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $fn = Zend_Registry::get('fn');
        $pager      = Zend_Registry::get('pager');
        $media = Zend_Registry::get('media');

        $count   = 0;
        $rows    = '';
        foreach ($dataArray as $row){

            if ($row['employee_work_type'] == 'Part time') {
                $amount = $row['day_rate'];
            } else {
                $amount = $row['salary'];
            }

            $emp_code = '';
                if($row['emp_code'] != ''){
                    $emp_code = 'EMP-'.$row['emp_code'];
                }
            $detailUrl = $pager->getGoToEditLink($count);

            $age = date_diff(date_create($row['date_of_birth']), date_create('today'))->y;            

            $exp = array('style' => 'employeePic','imgStyle'=>'img-responsive', 'folder'=>'large', 'url'=>$detailUrl);

            $pic = $media->getMediaPicture('payroll_employee', 'picture', $row['employee_id'], $exp);
            if($pic){
                $showPic = $pic;
            } else {
                $showPic = "<div class='employeePic'><img class='' src='/admin/images/no-images.png' alt='{$row['first_name']}'/></div>";
            }

            /*$rows .= "
            {$listObj->getListRowHeader($row, $count)}
            {$listObj->getListDataCell($emp_code)}
            {$listObj->getListDataCell($row['first_name'])}
            {$listObj->getListDataCell($row['mobile'])}
            {$listObj->getListDataCell($row['citizen'])}
            {$listObj->getListDataCell($row['nric_no'])}
            {$listObj->getListDataCell($row['fin_no'])}
            {$listObj->getListDateCell($row['date_of_expiry'])}
            {$listObj->getListDateCell($row['date_of_birth'])}
            {$listObj->getListDateCell($row['employee_id'], 'center')}
            {$listObj->getListRowEnd($row['employee_id'])}
            ";*/
            $rows .= "
            <div class='col-md-3 employeeList'>
                <div class='employeeListInner'>
                    <div class='col-md-5'>
                        {$showPic}
                    </div>
                    <div class='col-md-7'>
                        <div class='employeeListContent'>
                            <div class='employeeName'><a href='{$detailUrl}' rel='bookmark'>{$row['first_name']}</a></div>
                            <div class='designation'>{$row['project_designation']}</div>
                            <div class='age'>Age : {$age}</div>                    
                            <div class='age'>{$row['email']}</div>                    
                            <div class='age'>{$row['mobile']}</div>
                        </div>                    
                    </div>
                </div>
            </div>
            ";

            $count++ ;
        }

        /*{$listObj->getListHeader()}
        {$listObj->getListHeaderCell('EMP Code', 'a.emp_code')}
        {$listObj->getListHeaderCell('Full Name', 'a.first_name')}
        {$listObj->getListHeaderCell('HP/Mobile No.', 'a.mobile')}
        {$listObj->getListHeaderCell('Pass Type', 'a.citizen')}
        {$listObj->getListHeaderCell('NRIC', 'a.position')}
        {$listObj->getListHeaderCell('Fin No', 'a.fin_no')}
        {$listObj->getListHeaderCell('Date of Expiry', 'a.date_of_expiry')}
        {$listObj->getListHeaderCell('Date of Birth', 'a.date_of_birth')}
        {$listObj->getListHeaderCell('ID', 'a.employee_id', 'txtCenter')}
        {$listObj->getListHeaderEnd()}
        {$listObj->getListFooter()}*/
        $text = "
        <div class='row'>
            {$rows}
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getNew(){
        $formObj = Zend_Registry::get('formObj');

        $citizenArray = array(
              "Citizen"
             ,"PR"
             ,"EP"
             ,"SP"
             ,"WP"
             ,"DP"
        );

        $status = array(
              "Current"
             ,"Archive"
             ,"Cancel"
        );

        $exp = array('hideFirstOption' => 1);

        //{$formObj->getYesNoRRow('Citizen / PR', 'is_citizen')}
        $fieldset = "
        <div class='noteHighlight'>Please note * indicates mandatory fields</div>
        {$formObj->getTBRow('Full Name *', 'first_name')}
        {$formObj->getDropDownRowByArray('Pass Type *', 'citizen', $citizenArray, '')}
        {$formObj->getTBRow('NRIC No *', 'nric_no')}
        {$formObj->getTBRow('Fin No *', 'fin_no')}
        {$formObj->getTBRow('Work Permit No', 'work_permit')}
        {$formObj->getDDRowByArr('Status *', 'status', $status,'Current',$exp)}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fieldset)}
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
        $ln = Zend_Registry::get('ln');
        $db = Zend_Registry::get('db');
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $media = Zend_Registry::get('media');
        $comment = getCPPluginObj('common_comment');

        $formObj->mode = $tv['action'];

        $expVL = array('sqlType' => 'OneField');
        $sprYear = array(
                       '1'
                      ,'2'
                      ,'3'
                      );

        $sqlGender              = $fn->getValueListSQL('gender', 'value');
        //$sqlNationality         = $fn->getValueListSQL('nationality', 'value');
        $sqlRelegion            = $fn->getValueListSQL('religion', 'value');
        $sqlRace                = $fn->getValueListSQL('race', 'value');
        $sqlMaritalStatus       = $fn->getValueListSQL('maritalStatus', 'value');
        $sqlEmployeeGroup       = $fn->getValueListSQL('employeeGroup', 'value');
        $sqlQualification       = $fn->getValueListSQL('candidateQualification', 'value');
        $sqlCategory            = $fn->getValueListSQL('employeeType', 'value');
        $sqlSalutation          = $fn->getValueListSQL('salutation', 'value');
        $sqlEmployeeWorkType    = $fn->getValueListSQL('employeeWorkType', 'value');
        $sqlPosition            = $fn->getValueListSQL('positionType', 'value');
        $sqlProDesignation      = $fn->getValueListSQL('projectDesignation', 'value');
        $sqlTeam                = $fn->getValueListSQL('team', 'value');

        $sqlNationality         = "SELECT title FROM nationality ORDER BY title ASC";

        $degree1     = $fn->getReqParam('degree1');

        /*$spArray = array(
              "M.A"
             ,"M.Sc"
             ,"M.B.A"
        );*/

        $citizenArray = array(
              "Citizen"
             ,"PR"
             ,"EP"
             ,"SP"
             ,"WP"
             ,"DP"
        );

        $emp_code = '';
            if($row['emp_code'] != ''){
                $emp_code = 'EMP-'.$row['emp_code'];
            }

        $expDisableNric = '';
        $expDisableFin  = '';
        if($row['is_citizen'] == 0){
            $expDisableNric = array('disabled' => 1);
        }elseif($row['is_citizen'] == 1){
            $expDisableFin = array('disabled' => 1);
        }

        $expNoEdit  = array('isEditable' => 0);
        $expVl = array('sqlType' => 'OneField');
        $sqlCountry = getCPModelObj('common_geoCountry')->getCountryDDSQL();
        $expCountry = array('detailValue' => $row['country_name'], 'isEditable' => 0);

        if ($row['address_country'] == ''){
            $country = 'SG';
        } else {
            $country = $row['address_country'];
        }

        $formAddGroup = "index.php?_topRm={$tv['topRm']}&module=payroll_employee&_spAction=addNewValuelistForm&valuelist_name=employeeGroup&employee_id={$row['employee_id']}&showHTML=0";
        $expGroup     = array('sqlType' => 'OneField'
                            ,'notesRight' => "<a href='{$formAddGroup}' class='mr20 addNewValue' valuelist_name='employeeGroup'>Add</a>");


        $classNric='';
        $classFin='';
        $classWp='';
        $classSpr = '';
        if($row['citizen'] == 'PR' || $row['citizen'] == 'Citizen'){
            $classNric = "displayNone passType";
        }
        if($row['citizen'] == 'EP' || $row['citizen'] == 'SP' || $row['citizen'] == 'DP'){
            $classFin = "displayNone passType";
        }
        if ($row['citizen'] == 'WP') {
            $classWp = "displayNone passType";
        }

        if($row['citizen'] != 'PR'){
            $classSpr = "displayNone passType";
        }

        $expHideFirst = array('hideFirstOption' => 1);

        $status = array(
              "Current"
             ,"Archive"
             ,"Cancel"
        );

        $employee_type = array(
              "In house"
             ,"Others"
        );

        $expDob = array('minDate' => date('1930-01-01'), 'maxDate' => date('Y-m-d'), 'yearStart' => '1930', 'yearEnd' => date('Y'));
        $expPassportExpiry = array('minDate' => date('Y-m-d'), 'yearStart' => date('Y'), 'yearEnd' => date('Y') + 20);

        $sqlDormitory = "
        SELECT dormitory_id
              ,name
        FROM dormitory
        ORDER BY name";

        $dormitoryFields = '';
        if($row['dormitory_id'] != ''){
            $dormitoryFields = $this->getShowDormitoryFields($row['dormitory_id'], $row['employee_id']);
        }
        $record_id = $fn->getIssetParam($row, 'employee_id');
        $employeeAttachment = $this->getEmployeeAttachmentDisplay($row['employee_id']);

        $sqlUG = "
        SELECT user_group_id
              ,title
        FROM {$cpCfg['cp.modAccessUserGroupTable']}
        WHERE title != 'Super Administrator'
        ";
        $expUG = array('hideFirstOption' => 1);

        $SQLJI = "
        SELECT COUNT(job_information_id) AS contract_count
        FROM job_information
        WHERE employee_id = {$row['employee_id']}
          AND status != 'Cancel'
        ";
        $resultJI   = $db->sql_query($SQLJI);
        $rowJI = $db->sql_fetchrow($resultJI);

        $SQLPM = "
        SELECT COUNT(payroll_management_id) AS payslip_count
        FROM payroll_management
        WHERE employee_id = {$row['employee_id']}
          AND status != 'Cancelled'
        ";
        $resultPM   = $db->sql_query($SQLPM);
        $rowPM = $db->sql_fetchrow($resultPM);

        $text = "
        <div class='floatbox'>
            <div class='topPanelCount'>
                <img class='' src='/admin/images/payslip.png'/> {$rowPM['payslip_count']} Payslips
            </div>
            <div class='topPanelCount'>
                <img class='' src='/admin/images/contract-icon.png'/> {$rowJI['contract_count']} Contracts
            </div>
        </div>
        <div class='linkPortalWrapper'>
            <div expanded='1' class='header'>
                <div class='floatbox'>
                    <div class='float_left'>Personal Information</div>
                    <div class='toggle'></div>
                </div>
            </div>
            <div class='linkPortalDataWrapper'>
                <table class='thinlist'>
                    <tbody>
                        <tr>
                            <td>{$formObj->getTBRow('Code', 'emp_code', $emp_code,  $expNoEdit)}</td>
                            <td>{$formObj->getTBRow('Full Name *', 'first_name', $row['first_name'])}</td>
                            <td>{$formObj->getDDRowBySQL('Salutation', 'salutation', $sqlSalutation, $row['salutation'], $expVL)}</td>
                            <td>{$formObj->getDDRowBySQL('Gender *', 'gender', $sqlGender, $row['gender'] , $expVL)}</td>
                        </tr>
                        <tr>
                            <td>{$formObj->getDDRowByArr('Status', 'status', $status, $row['status'], $expHideFirst)}</td>
                            <td>{$formObj->getDateRow('Date of Birth *', 'date_of_birth', $row['date_of_birth'], $expDob)}</td>
                            <td>{$formObj->getTBRow('Passport No', 'passport', $row['passport'])}</td>
                            <td>{$formObj->getDateRow('Passport Expiry', 'date_of_expiry', $row['date_of_expiry'], $expPassportExpiry)}</td>
                        </tr>
                        <tr>
                            <td>{$formObj->getDDRowBySQL('Marital Status', 'marital_status', $sqlMaritalStatus, $row['marital_status'], $expVL)}</td>
                            <td>{$formObj->getDDRowBySQL('Nationality *', 'nationality', $sqlNationality, $row['nationality'], $expVL)}</td>
                            <td>{$formObj->getDDRowBySQL('Race', 'race', $sqlRace, $row['race'], $expVL)}</td>
                            <td>{$formObj->getDDRowBySQL('Religion', 'religion', $sqlRelegion, $row['religion'], $expVL)}</td>
                        </tr>
                        <tr>
                            <td>{$formObj->getDDRowBySQL('Project Designation', 'project_designation', $sqlProDesignation, $row['project_designation'], $expVL)}</td>
                            <td>{$formObj->getDDRowBySQL('Team', 'team', $sqlTeam, $row['team'], $expVL)}</td>
                            <td>{$formObj->getYesNoRRow('Project manager', 'project_manager', $row['project_manager'])}</td>
                            <td>{$formObj->getYesNoRRow('Admin Staff', 'admin_staff', $row['admin_staff'])}</td>
                        </tr>
                        <tr>
                            <td>{$formObj->getDDRowByArr('Employee Type', 'employee_type', $employee_type, $row['employee_type'])}</td>
                        </tr>
                    </tbody>
                </table>
            </div>    
        </div>
        <input id='record_id' type='hidden' name='employee_id' value='{$row['employee_id']}'>        
        <div class='linkPortalWrapper tabView'>
            <div expanded='1' class='header'>
                <div class='floatbox'>
                    <div class='float_left'>
                        <h2 class='rightPanelHeading'>More Details</h2>
                    </div>
                </div>
            </div>
            <div>
                <div class='linkPortalDataWrapper'>
                    <div id='tabs' class='mb20'>
                        <ul>
                            <li class='first'>
                                <a href='#tabs-1' class='quoteLinked'>Login Details</a>
                            </li>
                            <li class='second'>
                                <a href='#tabs-2' class='materialsUsed'>Pass Type</a>
                            </li>
                            <li class='third'>
                                <a href='#tabs-3' class='materialsPurchased'>Educational Qualification</a>
                            </li>
                            <li class='fourth'>
                                <a href='#tabs-4' class='materialsTransferredFromOtherProjects'>Contact Information</a>
                            </li>
                            <li class='fifth'>
                                <a href='#tabs-5' class='subConWorkOrder'>Emergency Contact</a>
                            </li>
                            <li class='sixth'>
                                <a href='#tabs-6' class='subConWorkOrder'>Attachment Portals</a>
                            </li>
                            <li class='seventh'>
                                <a href='#tabs-7' class='subConWorkOrder'>Linked Portals</a>
                            </li>
                        </ul>

                        <div id='tabs-1'>
                          <div class='mb30 mt10' id='loginPortal'>
                            <table>
                                <tr>
                                    <td>{$formObj->getTBRow('Login Email', 'login_email', $row['login_email'])}</td>
                                    <td>{$formObj->getTBRow('Password', 'login_pass_word', $row['login_pass_word'])}</td>
                                    <td>{$formObj->getDDRowBySQL($ln->gd('m.core.staff.lbl.userGroup', 'User Group'), 'staff_user_group_id', $sqlUG, $row['staff_user_group_id'], $expUG)}</td>
                                    <td>{$formObj->getYesNoRRow($ln->gd('m.core.staff.lbl.published', 'Published'), 'staff_published', $row['staff_published'])}</td>
                                </tr>
                            </table>
                          </div>
                        </div>

                        <div id='tabs-2'>
                          <div class='floatbox'>
                            <div id='passTypePortal'>
                                <table>
                                    <tr>
                                        <td>{$formObj->getDropDownRowByArray('Pass Type *', 'citizen', $citizenArray, $row['citizen'])}</td>
                                        <td><div class='{$classNric}'>{$formObj->getTBRow('Fin No *', 'fin_no', $row['fin_no'])}</div>
                                        <div class='{$classFin} {$classWp}'>{$formObj->getTBRow('NRIC No *', 'nric_no', $row['nric_no'])}</div>
                                        <div class='{$classNric} {$classFin}'>{$formObj->getTBRow('Work Permit No', 'work_permit', $row['work_permit'])}</div></td>
                                        <td>
                                            <div class='{$classSpr}'>{$formObj->getDropDownRowByArray('SPR Year', 'spr_year', $sprYear, $row['spr_year'])}</div>
                                            <div class='{$classNric}'>{$formObj->getDateRow('Fin Expiry Date', 'fin_no_expiry_date', $row['fin_no_expiry_date'])}</div>
                                            <div class='{$classNric} {$classFin}'>{$formObj->getDateRow('Work Permit No Expiry Date', 'work_permit_expiry_date', $row['work_permit_expiry_date'])}</div>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                          </div>
                        </div>

                        <div id='tabs-3'>
                          <div class='floatbox'>
                            <div id='educationalQualificationPortal'>
                                <table>
                                    <tr>
                                        <td>{$formObj->getDDRowBySQL('Qualification 1', 'degree1', $sqlQualification, $row['degree1'], $expVl)}</td>
                                        <td>{$formObj->getTBRow('Degree', 'educational_qualitifcation1', $row['educational_qualitifcation1'])}</td>
                                        <td>{$formObj->getDateRow('Year of completion', 'year_of_completion1', $row['year_of_completion1'], array('yearStart' => 1950, 'yearEnd' => date('Y'), 'maxDate' => date('Y-m-d')))}</td>
                                    </tr>
                                    <tr>
                                        <td>{$formObj->getDDRowBySQL('Qualification 2', 'degree2', $sqlQualification, $row['degree2'], $expVl)}</td>
                                        <td>{$formObj->getTBRow('Degree', 'educational_qualitifcation2', $row['educational_qualitifcation2'])}</td>
                                        <td>{$formObj->getDateRow('Year of completion', 'year_of_completion2', $row['year_of_completion2'], array('yearStart' => 1950, 'yearEnd' => date('Y'), 'maxDate' => date('Y-m-d')))}</td>
                                    </tr>
                                    <tr>
                                        <td>{$formObj->getDDRowBySQL('Qualification 3', 'degree3', $sqlQualification, $row['degree3'], $expVl)}</td>
                                        <td>{$formObj->getTBRow('Degree', 'educational_qualitifcation3', $row['educational_qualitifcation3'])}</td>
                                        <td>{$formObj->getDateRow('Year of completion', 'year_of_completion3', $row['year_of_completion3'], array('yearStart' => 1950, 'yearEnd' => date('Y'), 'maxDate' => date('Y-m-d')))}</td>
                                    </tr>
                                </table>
                            </div>
                          </div>
                        </div>

                        <div id='tabs-4'>
                          <div class='floatbox'>
                            <div id='contactInformationPortal'>
                                <table>
                                    <tr>
                                        <th colspan='4'>Contact Information (For Citizen)</th>
                                    </tr>
                                    <tr>
                                        <td>{$formObj->getTBRow('Address 1', 'address_area', $row['address_area'])}</td>
                                        <td>{$formObj->getTBRow('Address 2', 'address_street', $row['address_street'])}</td>
                                        <td>{$formObj->getTBRow('Postal Code', 'address_po_code', $row['address_po_code'])}</td>
                                        <td>{$formObj->getTBRow('Country', 'address_country', 'Singapore',  $expNoEdit)}</td>
                                    </tr>
                                    <tr>
                                        <td>{$formObj->getTBRow('HP/Mobile No.', 'mobile', $row['mobile'])}</td>
                                        <td>{$formObj->getTBRow('Alternate Contact number', 'phone', $row['phone'])}</td>
                                        <td>{$formObj->getTBRow('Email', 'email', $row['email'])}</td>
                                    </tr>

                                    <tr>
                                        <th colspan='4'>Contact Information (Overseas, For Non-Citizen Or Permanent Resident)</th>
                                    </tr>
                                    <tr>
                                        <td>{$formObj->getTBRow('Address 1', 'foreign_addrs_area', $row['foreign_addrs_area'])}</td>
                                        <td>{$formObj->getTBRow('Address 2', 'foreign_addrs_street', $row['foreign_addrs_street'])}</td>
                                        <td>{$formObj->getDDRowBySQL('Country', 'foreign_addrs_country', $sqlCountry, $row['foreign_addrs_country'], $expCountry)}</td>
                                        <td>{$formObj->getTBRow('Postal Code', 'foreign_addrs_postal_code', $row['foreign_addrs_postal_code'])}</td>
                                    </tr>
                                    <tr>
                                        <td>{$formObj->getTBRow('HP/Mobile No.', 'foreign_mobile', $row['foreign_mobile'])}</td>
                                        <td>{$formObj->getTBRow('Alternate Contact number', 'phone_direct', $row['phone_direct'])}</td>
                                        <td>{$formObj->getTBRow('Email', 'foreign_email', $row['foreign_email'])}</td>
                                    </tr>
                                </table>
                            </div>
                          </div>
                        </div>

                        <div id='tabs-5'>
                          <div class='floatbox'>
                            <div id='emergencyContactPortal'>
                                <table>
                                    <tr>
                                        <td>{$formObj->getTBRow('Name', 'emergency_contact_name', $row['emergency_contact_name'])}</td>
                                        <td>{$formObj->getTBRow('Phone 1', 'emergency_contact_phone', $row['emergency_contact_phone'])}</td>
                                        <td>{$formObj->getTBRow('Phone 2', 'emergency_contact_phone2', $row['emergency_contact_phone2'])}</td>
                                        <td>{$formObj->getTBRow('Address', 'emergency_contact_address', $row['emergency_contact_address'])}</td>
                                    </tr>
                                </table>
                            </div>
                          </div>
                        </div>

                        <div id='tabs-6'>
                            <div class='floatbox row'>
                                <div class='col-md-12 col-sm-12 col-xs-12 noPadding'> 
                                    <div class='col-md-4 col-sm-6 col-xs-12'>
                                        {$media->getRightPanelMediaDisplay("Picture", "payroll_employee", "picture", $row)}
                                    </div>
                                    <div class='col-md-4 col-sm-6 col-xs-12'>
                                        {$media->getRightPanelMediaDisplay('Work Permit', 'payroll_employee', 'workPermit', $row)}
                                    </div>
                                    <div class='col-md-4 col-sm-6 col-xs-12'>
                                        {$media->getRightPanelMediaDisplay('WSQ', 'payroll_employee', 'wsq', $row)}
                                    </div>
                                    <div class='col-md-4 col-sm-6 col-xs-12'>
                                        {$media->getRightPanelMediaDisplay('Digital Sign', 'payroll_employee', 'digitalSign', $row)}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id='tabs-7'>
                            <div class='floatbox row'>
                                <div class='col-md-12 col-sm-12 col-xs-12 noPadding'> 
                                    <div class='col-md-6 col-sm-6 col-xs-12'>
                                        {$displayLinkData->getLinkPortalMain("payroll_employee", "payroll_jobInformationLink", "Job Information History", $row)}
                                    </div>
                                    <div class='col-md-6 col-sm-6 col-xs-12'>
                                        {$displayLinkData->getLinkPortalMain("payroll_employee", "payroll_trainingLink", "Training Linked", $row)}
                                    </div>
                                    <div class='col-md-6 col-sm-6 col-xs-12'>
                                        {$comment->getView(array(
                                        'roomName' => 'payroll_employee'
                                        ,'recordId' => $record_id
                                        ))}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        ";

        return $text;
    }
    /**
    */
    function getEditold($row){
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $formObj->mode  = $tv['action'];

       // $chineseName    = '';
        //$chinesePos     = '';
        //$chineseDept    = '';
        $compAddressDD  = '';
        $companyAddress = '';
        $staffDetail    = '';
        $personalAdd    = '';
        $compLink       = '';

        $expVL = array('sqlType' => 'OneField');

        $sqlGender              = $fn->getValueListSQL('gender');
        $sqlNationality         = $fn->getValueListSQL('nationality');
        $sqlRelegion            = $fn->getValueListSQL('relegion');
        $sqlRace                = $fn->getValueListSQL('race');
        $sqlMaritalStatus       = $fn->getValueListSQL('marital_status');
        $sqlCategory            = $fn->getValueListSQL('employeeType');
        $sqlSalutation          = $fn->getValueListSQL('Salutation');
        $sqlEmployeeWorkType    = $fn->getValueListSQL('employeeWorkType');
        $sqlPosition            = $fn->getValueListSQL('positionType','value');
        $sqlComp                = $fn->getDDSql('enggCrm_company');

       /* if ($cpCfg['m.enggCrm.employee.showChineseFields'] == 1){
            $chineseName = $formObj->getTBRow('Name (Chinese)', 'chi_name', $row['chi_name']);
            $chinesePos  = $formObj->getTBRow('Position (Chinese)', 'chi_position', $row['chi_position']);
            $chineseDept = $formObj->getTBRow('Department (Chinese)', 'chi_department', $row['chi_department']);
        }*/

        $degree1     = $fn->getReqParam('degree1');

        $spArray = array(
              "M.A"
             ,"M.Sc"
             ,"M.B.A"
        );

        if ($tv['action'] == 'edit'){
            if($cpCfg['m.project.hasMultipleCompanyAddress'] == 1){
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

        if ($cpCfg['m.project.employee.showDetail'] == 1){
            $sqlCombo = "
            SELECT staff_id
                  ,CONCAT_WS(' ', a.first_name, a.last_name) AS staff_name
            FROM staff a
            ORDER BY staff_name";

            /*$fieldset = "
            {$formObj->getDDRowBySQL("{$cpCfg['m.enggCrm.staffFieldLabel']}", "staff_id", $sqlCombo, $row['staff_id'])}
            ";

            $staffDetail = $formObj->getFieldSetWrapped($cpCfg['m.enggCrm.staffFieldLabel'], $fieldset);*/
        }


        $expNoEdit  = array('isEditable' => 0);
        $expVl = array('sqlType' => 'OneField');

        $sqlCountry = getCPModelObj('common_geoCountry')->getCountryDDSQL();
        $expCountry = array('detailValue' => $row['country_name']);

        if ($row['foreign_addrs_country'] == ''){
            $country = 'SG';
        } else {
            $country = $row['foreign_addrs_country'];
        }

        //$formAddPosition = "index.php?_topRm={$tv['topRm']}&module=payroll_employee&_spAction=addNewValuelistForm&valuelist_name=positionType&employee_id={$row['employee_id']}&showHTML=0";
        /*$expPosition     = array('sqlType' => 'OneField'
                            ,'notesRight' => "<a href='{$formAddPosition}' class='mr20 addNewValue' valuelist_name='positionType'>Add</a>");*/

        $fielset1 = "
        {$formObj->getTBRow('Code', 'emp_code', $row['emp_code'],  $expNoEdit)}
        {$formObj->getTBRow('Name', 'employee_name', $row['employee_name'])}
        {$formObj->getDDRowBySQL('Salutation', 'salutation', $sqlSalutation, $row['salutation'], $expVL)}
        {$formObj->getDDRowBySQL('Gender', 'gender', $sqlGender, $row['gender'] , $expVL)}
        {$formObj->getDateRow('Date of Birth', 'date_of_birth', $row['date_of_birth'])}
        {$formObj->getDDRowBySQL('Marital Status', 'marital_status', $sqlMaritalStatus, $row['marital_status'], $expVL)}
        {$formObj->getYesNoRRow('Citizen / PR', 'is_citizen', $row['is_citizen'])}
        {$formObj->getTBRow('Fin/Work Permit No', 'fin_no', $row['fin_no'])}
        {$formObj->getTBRow('NRIC No', 'nric_no', $row['nric_no'])}
        {$formObj->getTBRow('Passport No', 'passport', $row['passport'])}
        {$formObj->getDDRowBySQL('Nationality', 'nationality', $sqlNationality, $row['nationality'], $expVL)}
        {$formObj->getDDRowBySQL('Relegion', 'religion', $sqlRelegion, $row['religion'], $expVL)}
        {$formObj->getDDRowBySQL('Race', 'race', $sqlRace, $row['race'], $expVL)}
        ";

        /*if ($formObj->mode == 'edit'){
            $compLink = "<a class='editLinkSingle' href='' link='{$fn->getOpenLinkUrl('payroll_employee', 'payroll_companyLink', 'fld_company_id')}'>Choose</a>";
        }
        $expComp  = array('notesRight' => $compLink, 'detailValue' => $row['c_company_name']);*/

        $fielset2 = "
        {$formObj->getDDRowByArr('Qualification 1', 'degree1', $spArray, $row['degree1'])}
        {$formObj->getTBRow('Degree', 'educational_qualitifcation1', $row['educational_qualitifcation1'])}
        {$formObj->getDateRow('Year of completion', 'year_of_completion1', $row['year_of_completion1'])}
        {$formObj->getDDRowByArr('Qualification 2', 'degree2', $spArray, $row['degree2'])}
        {$formObj->getTBRow('Degree', 'educational_qualitifcation2', $row['educational_qualitifcation2'])}
        {$formObj->getDateRow('Year of completion', 'year_of_completion2', $row['year_of_completion2'])}
        {$formObj->getDDRowByArr('Qualification 3', 'degree3', $spArray, $row['degree3'])}
        {$formObj->getTBRow('Degree', 'educational_qualitifcation3', $row['educational_qualitifcation3'])}
        {$formObj->getDateRow('Year of completion', 'year_of_completion3', $row['year_of_completion3'])}
        ";

        $fielset3 = "
        {$formObj->getTBRow('Address 1', 'foreign_addrs_area', $row['foreign_addrs_area'])}
        {$formObj->getTBRow('Address 2', 'foreign_addrs_street', $row['foreign_addrs_street'])}
        {$formObj->getDDRowBySQL('Country', 'foreign_addrs_country', $sqlCountry, $country, $expCountry)}
        {$formObj->getTBRow('Postal Code', 'address_po_code', $row['address_po_code'])}
        {$formObj->getTBRow('HP/Mobile No.', 'mobile', $row['mobile'])}
        {$formObj->getTBRow('Alternate Contact number', 'phone', $row['phone'])}
        {$formObj->getTBRow('Email', 'email', $row['email'])}
       ";

        $fielset4 = "
        {$formObj->getTBRow('Address 1', 'address_flat', $row['address_flat'])}
        {$formObj->getTBRow('Address 2', 'address_street', $row['address_street'])}
        {$formObj->getDDRowBySQL('Country', 'address_country', $sqlCountry, $country, $expCountry)}
        {$formObj->getTBRow('Postal Code', 'address_po_code', $row['address_po_code'])}
        {$formObj->getTBRow('HP/Mobile No.', 'mobile', $row['mobile'])}
        {$formObj->getTBRow('Alternate Contact number', 'phone', $row['phone'])}
        {$formObj->getTBRow('Email', 'email', $row['email'])}
       ";

        $fielset5 = "
        {$formObj->getTBRow('Name', 'emergency_contact_name', $row['emergency_contact_name'])}
        {$formObj->getTBRow('Phone 1', 'emergency_contact_phone', $row['emergency_contact_phone'])}
        {$formObj->getTBRow('Phone 2', 'emergency_contact_phone2', $row['emergency_contact_phone2'])}
        {$formObj->getTBRow('Address', 'emergency_contact_address', $row['emergency_contact_address'])}
       ";

        $text = "
        {$formObj->getFieldSetWrapped('Personal Information', $fielset1)}
        {$formObj->getFieldSetWrapped('Educational Qualification', $fielset2)}
        {$formObj->getFieldSetWrapped('Contact Information (In Citizen)', $fielset3)}
        {$formObj->getFieldSetWrapped('Contact Information (Overseas, For Non-Citizen Or Permanent Resident)', $fielset4)}
        {$formObj->getFieldSetWrapped('Emergency Contact:', $fielset5)}
        {$staffDetail}
        {$personalAdd}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    /**
     *
     */
    function getSearch(){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');

        $textPublished  = "";

        $sqlCompany = $fn->getDDSql('enggCrm_company');
        $sqlInterest = $fn->getDDSql('common_interest');

        $spArray = array(
            "Flagged"
           ,"Not-Flagged"
        );

        $fielset1 = "
        {$formObj->getTBRow('First Name', 'first_name')}
        {$formObj->getTBRow('Last Name', 'last_name')}
        {$formObj->getTBRow('Email', 'email' )}
        ";

        $fielset2 = "
        {$formObj->getDDRowBySQL('Company Name', 'company_id', $sqlCompany)}
        {$formObj->getTBRow('Position', 'position')}
        ";

        $fielset3 = "
        {$formObj->getYesNoDropDownRow('Subscribed', 'subscribe')}
        {$formObj->getDDRowBySQL('Interst Group', 'interest_id', $sqlInterest)}
        {$formObj->getDDRowByArr('Special Search', 'special_search', $spArray)}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Employee Details', $fielset1)}
        {$formObj->getFieldSetWrapped('Company Details', $fielset2)}
        {$formObj->getFieldSetWrapped('Other Details', $fielset3)}
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

        /*
        if( $cpCfg['m.project.employee.showEvent'] == "1"){
            $rows .= $displayLinkData->getLinkPortalMain("payroll_employee", "event_eventLink", "Events Linked", $row);
        }
        */

        $record_id = $fn->getIssetParam($row, 'employee_id');
        $employeeAttachment = $this->getEmployeeAttachmentDisplay($row['employee_id']);

        $text = "
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
                <td width="100%" align="left" style="font-size:14px;">'.strtoupper($empRec['first_name']).'</td>
            </tr>
        </table>
        ';

        $pdf->writeHTML($tbl3, true, false, false, false, '');

        $SQLMedia = "
        SELECT file_name, record_type
        FROM media
        WHERE record_id = '{$employee_id}'
        AND room_name   = 'payroll_employee'
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

        $special_search      = $fn->getReqParam('special_search');
        $employee_work_type  = $fn->getReqParam('employee_work_type');
        $employee_status     = $fn->getReqParam('employee_status');
        $pass_type           = $fn->getReqParam('pass_type');
        
        //$sqlEmployeeWorkType = $fn->getValueListSQL('employeeWorkType');
        $empTypeArray = array(
              "Full Time"
             ,"Part Time"
             ,"Contract"
        );
        $spArray = array(
              "Subscribed"
             ,"Not-Subscribed"
             ,"Flagged"
             ,"Not-Flagged"
        );
        $status = array(
              "Current"
             ,"Archive"
             ,"Cancel"
        );
        $citizenArray = array(
              "Citizen"
             ,"PR"
             ,"EP"
             ,"SP"
             ,"WP"
             ,"DP"
        );

        if($employee_status == ''){
            $employee_status = 'Current';
        }

        $text = "
        <td>
            <select name='pass_type'>
                <option value=''>Pass Type</option>
                {$cpUtil->getDropDown1($citizenArray, $pass_type)}
            </select>
        </td>
        <td>
            <select name='employee_work_type' >
                <option value=''>Employee Work Type</option>
                {$cpUtil->getDropDown1($empTypeArray, $employee_work_type)}
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
    function getAddNewValuelistForm() {
        $tv      = Zend_Registry::get('tv');
        $fn      = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $valuelist_name = $fn->getReqParam('valuelist_name');
        $employee_id    = $fn->getReqParam('employee_id');

        $formAction = "index.php?_topRm={$tv['topRm']}&module=payroll_employee&_spAction=addNewValuelistFormSubmit&showHTML=0";

        $text = "
        <form id='portalForm' class='yform columnar addNewDropdownValueForm' method='post' action='{$formAction}'>
            {$formObj->getTBRow('Value', 'valuelist_value')}
            <input type='hidden' name='valuelist_name' value='{$valuelist_name}' />
            <input type='hidden' name='employee_id' value='{$employee_id}' />
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getShowDormitoryFields($dormitory_id='', $employee_id='') {
        $tv      = Zend_Registry::get('tv');
        $fn      = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        if($dormitory_id == ''){
            $dormitory_id = $fn->getReqParam('dormitory_id');
        }
        if($employee_id == ''){
            $employee_id    = $fn->getReqParam('employee_id');
        }

        $dorRec = $fn->getRecordRowByID('dormitory', 'dormitory_id', $dormitory_id);
        $empRec = $fn->getRecordRowByID('employee', 'employee_id', $employee_id);
        $expNoEdit  = array('isEditable' => 0);

        $text = "
        <tr class='dormitoryFieldsAppended'>
            <td>{$formObj->getTBRow('Address 1', 'address1', $dorRec['address1'], $expNoEdit)}</td>
            <td>{$formObj->getTBRow('Address 2', 'address2', $dorRec['address2'], $expNoEdit)}</td>
            <td>{$formObj->getTBRow('Postal Code', 'postal_code', $dorRec['postal_code'], $expNoEdit)}</td>
            <td>{$formObj->getTBRow('Room Number', 'room_no', $empRec['room_no'])}</td>
        </tr>
        ";

        return $text;
    }

}