<?
class CP_Admin_Modules_ManPower_Staff_View extends CP_Common_Lib_ModuleViewAbstract
{
    function getList($dataArray) {
        $listObj = Zend_Registry::get('listObj');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');

        $rowCounter = 0;
        $rows       = '';
        $staff_type = '';
        $country    = '';
        $userGrp    = '';

        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){
            $extraCols = '';

            $userGrp = '';

            if ($cpCfg['cp.hasProjectMg'] == 1) {
                $extraCols .= $listObj->getListDataCell($row['staff_type']);
                if ($cpCfg['m.manPower.hasStaffGroup'] == 1) {
                    $extraCols .= $listObj->getListDataCell($row['staff_group_names']);
                }
                $extraCols .= $listObj->getListDataCell($row['status']);
            }

            if ($cpCfg['m.manPower.staff.showUserGroup'] == 1 || $cpCfg['cp.hasAccessModule']){
                $userGrp = $listObj->getListDataCell($row['user_group_title']);
            }

            $publishedRow = '';
            if ($_SESSION['userGroupType'] == 'Super Administrator') {
                $publishedRow = $listObj->getListPublishedImage($row['published'], $row[$cpCfg['cp.modAccessStaffIdLabel']]);
            }
            $rows .="
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, strtoupper($row['first_name']))}
            {$listObj->getListDataCell($row['email'])}
            {$userGrp}
            {$extraCols}
            {$fn->getSiteFldForList($row)}
            {$listObj->getListDataCell($row[$cpCfg['cp.modAccessStaffIdLabel']], "center")}
            {$publishedRow}
            {$listObj->getListRowEnd($row[$cpCfg['cp.modAccessStaffIdLabel']])}
            ";

            $rowCounter++;
        }

        $extraCols = "";
        $shortCode = '';

       	if ($cpCfg['cp.hasProjectMg'] == 1) {
           $extraCols .= $listObj->getListHeaderCell('Staff Type', 'a.staff_type');
           $extraCols .= $listObj->getListHeaderCell('Status', 'a.status');

           if ($cpCfg['m.manPower.hasStaffGroup'] == 1) {
               $extraCols .= $listObj->getListHeaderCell('Staff Group', 'staff_group_names');
           }
       	}

        if ($cpCfg['m.manPower.staff.showUserGroup'] == 1 || $cpCfg['cp.hasAccessModule']){
            $userGrp = $listObj->getListHeaderCell($ln->gd('m.manPower.staff.lbl.group', 'Group'), 'b.title');
        }

        $published = '';
        if ($_SESSION['userGroupType'] == 'Super Administrator') {
            $published = $listObj->getListHeaderCell($ln->gd('m.manPower.staff.lbl.published', 'Published'), 'a.published', 'headerCenter');
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell($ln->gd('m.manPower.staff.lbl.firstName', 'Name'), 'a.first_name')}
        {$listObj->getListHeaderCell($ln->gd('m.manPower.staff.lbl.email', 'Email'), 'a.email')}
        {$userGrp}
        {$extraCols}
        {$fn->getSiteLabelForList()}
        {$listObj->getListHeaderCell($ln->gd('m.manPower.staff.lbl.id', 'ID'), 'a.staff_id', 'headerCenter')}
        {$published}
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
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');

        $fnModCountry = includeCPClass('ModuleFns', 'common_country');

        $fielset = "
        {$formObj->getTBRow($ln->gd('m.manPower.staff.lbl.firstName', 'Name'), 'first_name')}
        {$formObj->getTBRow($ln->gd('m.manPower.staff.lbl.email', 'Email'), 'email')}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fielset)}
        ";

        return $text;
    }

    /**
     *
     */
    function getEdit($row) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $ln = Zend_Registry::get('ln');
        $am = Zend_Registry::get('am');

        $formObj->mode = $tv['action'];

        $staffType        = "";
        $sectionName      = "";
        $description      = "";
        $userGroup        = "";

        $expVl = array('sqlType' => 'OneField');
        $expNoEdit  = array('isEditable' => 0);

        if ($cpCfg['cp.hasProjectMg'] == 1) {
            $sqlType = $fn->getValueListSQL('staffType');

            if ($_SESSION['userGroupType'] != 'Super Administrator') {
                $staffType = $formObj->getTBRow('Staff Type', 'staff_type', $row['staff_type'], $expNoEdit);                
            } else {
                $staffType = $formObj->getDDRowBySQL('Staff Type', 'staff_type', $sqlType, $row['staff_type'], $expVl);
            }
        }

        if ($cpCfg['cp.hasFirstRoomValueInStaff'] == 1) {
            $sectionName = $formObj->getDDRowByArr("Login Section Default", "section_name", $am->getSectionNameArray(), $row['section_name']);
        }

        $fnMod = includeCPClass('ModuleFns', 'manPower_staff');

        $userGrp = '';

        if ($cpCfg['m.manPower.staff.showUserGroup'] == 1 || $cpCfg['cp.hasAccessModule']){
            $exp = array('hideFirstOption' => 1, 'detailValue' => $row['user_group_title']);

            $sqlUG = "
            SELECT user_group_id
                  ,title
            FROM {$cpCfg['cp.modAccessUserGroupTable']}
            ";

            $sqlUG = $fn->getSQL($sqlUG);

            if ($_SESSION['userGroupType'] != 'Super Administrator') {
                $userGrp = $formObj->getTBRow($ln->gd('m.manPower.staff.lbl.userGroup', 'User Group'), 'user_group_id', $row['user_group_title'], $expNoEdit);                
            } else {
                $userGrp = $formObj->getDDRowBySQL($ln->gd('m.manPower.staff.lbl.userGroup', 'User Group'), 'user_group_id', $sqlUG, $row['user_group_id'], $exp);
            }
        }

        $fnModCountry = includeCPClass('ModuleFns', 'common_country');

        $passwordRow = '';
        $emailRow = '';
        if ($cpCfg['m.manPower.staff.hasPasswordSalt']) {
            $has_pwd = '';
            $lblPassword = $ln->gd('m.manPower.staff.lbl.password', 'Password');
            if ($row['pass_word'] != '') {
                $has_pwd = 1;
                $lblPassword = $ln->gd('m.manPower.staff.lbl.changePassword', 'Change Password');
            }
            $passwordRow = "
            {$formObj->getTBRow($lblPassword, 'pass_word')}
            <input type='hidden' name='has_pwd' value='{$has_pwd}' />
            ";

            $exp = array('isEditable' => 0);
            $emailRow = "
            {$formObj->getTBRow($ln->gd('m.manPower.staff.lbl.email', 'Email'), 'email', $row['email'], $exp)}
            <input type='hidden' name='email' value='{$row['email']}' />
            ";

        } else {
            $passwordRow = "{$formObj->getTBRow($ln->gd('m.manPower.staff.lbl.password', 'Password*'), 'pass_word', $row['pass_word'])}";
            if ($_SESSION['userGroupType'] != 'Super Administrator') {
                $emailRow = $formObj->getTBRow('Email*', 'email', $row['email'], $expNoEdit);                
            } else {
                $emailRow = $formObj->getTBRow('Email*', 'email', $row['email']);                
            }
        }
        
        $chngPwdNxtLogin = '';
        if($cpCfg['m.manPower.staff.hasChangePasswordNextLogin']){
            $chngPwdNxtLogin = $formObj->getYesNoRRow($ln->gd('m.manPower.staff.lbl.changePwdOnNext', 'Change password on next login'), 'change_password_next_login', $row['change_password_next_login']);
        }
        
        $commissionDetails = '';
        if($cpCfg['m.manPower.staff.hasCommissionDetails'] && $_SESSION['userGroupName'] == "Super Administrator"){

            $sqlCommType = $fn->getValueListSQL('commissionType');

            $commissionDetails = "
            {$formObj->getDDRowBySQL('Commission Type', 'commission_type', $sqlCommType, $row['commission_type'], $expVl)}
            {$formObj->getTBRow('Commission Rate', 'staff_commission_rate', $row['staff_commission_rate'])}
            ";
        }
        
        if ($_SESSION['userGroupType'] != 'Super Administrator') {
            $statusRow = $formObj->getTBRow($ln->gd('m.manPower.staff.lbl.status', 'Status'), 'status', $row['status'], $expNoEdit);                
            if ($row['published'] == 1){
                $publishedRow = 'Yes';
            } else {
                $publishedRow = 'No';
            }
            $published = $formObj->getTBRow('Published', 'published', $publishedRow, $expNoEdit);
        } else {
            $statusRow = $formObj->getDDRowByArr($ln->gd('m.manPower.staff.lbl.status', 'Status'), 'status', $fnMod->getStaffStatusArray(), $row['status']);                
            $published = $formObj->getYesNoRRow($ln->gd('m.manPower.staff.lbl.published', 'Published'), 'published', $row['published']);
        }

        if ($_SESSION['userGroupType'] != 'Super Administrator') {
						$contract_date = $formObj->getDateRow('Contract Date', 'contract_date', $row['contract_date'], $expNoEdit);
        } else {
						$contract_date = $formObj->getDateRow('Contract Date', 'contract_date', $row['contract_date']);
        }

				
        if ($_SESSION['userGroupType'] != 'Super Administrator') {
						$salary = '';
        } else {
						$salary = $formObj->getTBRow('Salary', 'staff_salary', $row['staff_salary']);
        }
						
        $fieldset1 = "
        {$formObj->getTBRow($ln->gd('m.manPower.staff.lbl.firstName', 'Name *'), 'first_name', $row['first_name'])}
        {$emailRow}
        {$passwordRow}
        {$userGrp}
        {$published}
        {$fnModCountry->getCountryDropDown($formObj->mode, $row)}
        {$formObj->getTBRow('Designation', 'designation', $row['designation'])}
        {$formObj->getTBRow('Department', 'department', $row['department'])}
        {$formObj->getTBRow('FIN.No', 'fin_no', $row['fin_no'])}
        {$formObj->getTBRow('Phone Number', 'phone', $row['phone'])}
        {$formObj->getDateRow('Date Of Birth', 'date_of_birth', $row['date_of_birth'], array('yearStart' => 1940, 'yearEnd' => 2040))}
        {$statusRow}
        {$staffType}
        {$sectionName}
				{$contract_date}
        {$fn->getSiteDropDown($formObj->mode, $row)}
        {$chngPwdNxtLogin}
        {$commissionDetails} 
        {$salary} 
        ";

        $sqlCountry = getCPModelObj('common_geoCountry')->getCountryDDSQL();
        $expCountry = array('detailValue' => $row['country_title']);

        $fieldset2 = "
        {$formObj->getTBRow($ln->gd('m.manPower.staff.lbl.streetAddress', 'Street Address'), 'address_street', $row['address_street'])}
        {$formObj->getTBRow($ln->gd('m.manPower.staff.lbl.town', 'Town / Suburb'), 'address_town', $row['address_town'])}
        {$formObj->getTBRow($ln->gd('m.manPower.staff.lbl.state', 'State'), 'address_state', $row['address_state'])}
        {$formObj->getDDRowBySQL($ln->gd('m.manPower.staff.lbl.country', 'Country'), 'address_country', $sqlCountry, $row['address_country'], $expCountry)}
        ";

        $fieldset3 = "
        {$formObj->getTBRow($ln->gd('m.manPower.staff.lbl.passport_no', 'Indian Passport No'), 'passport_no', $row['passport_no'])}
        {$formObj->getDateRow('Date Of Expiry', 'date_of_expiry', $row['date_of_expiry'], array('yearStart' => 1940, 'yearEnd' => 2040))}
        {$formObj->getTBRow($ln->gd('m.manPower.staff.lbl.streetAddress', 'Street Address'), 'native_address_street', $row['native_address_street'])}
        {$formObj->getTBRow($ln->gd('m.manPower.staff.lbl.town', 'Town / Suburb'), 'native_address_town', $row['native_address_town'])}
        {$formObj->getTBRow($ln->gd('m.manPower.staff.lbl.state', 'State'), 'native_address_state', $row['native_address_state'])}
        {$formObj->getTBRow($ln->gd('m.manPower.staff.lbl.zipcode', 'Zip Code'), 'zip_code', $row['zip_code'])}
        {$formObj->getDDRowBySQL($ln->gd('m.manPower.staff.lbl.country', 'Country'), 'native_address_country', $sqlCountry, $row['native_address_country'], $expCountry)}
        ";

        $text = "
        {$formObj->getFieldSetWrapped($ln->gd('m.manPower.staff.lbl.staffDetails', 'Staff Details'), $fieldset1)}
        {$formObj->getFieldSetWrapped($ln->gd('m.manPower.staff.lbl.address', 'Address'), $fieldset2)}
        {$formObj->getFieldSetWrapped($ln->gd('m.manPower.staff.lbl.nativeAddress', 'Native Address'), $fieldset3)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    /**
     *
     */
    function getRightPanel($row) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $media = Zend_Registry::get('media');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        
        $staff_id       = $fn->getReqParam('staff_id');

        $staffGroup = "";
        $signature  = "";
        $staffCommission  = "";
        $attachment = '';
        
        if ($cpCfg['m.manPower.hasStaffGroup'] == 1) {
            $staffGroup = $displayLinkData->getLinkPortalMain("manPower_staff", "project_staffGroupLink", $ln->gd('m.manPower.staff.link.staffGroupLinked', 'Staff Group Linked'), $row);
        }

		$contractButton ='';
		$staffDocumentLink ='';
        $attachments = '';
		$declarationButton ='';
		$noDueWordButton ='';
		$cancelButton ='';
		$resignationButton ='';
        
        if( $cpCfg['cp.hasMultiUniqueSites'] == 'true'){

            if($_SESSION['userGroupName'] == "Super Administrator"){
                if ($cpCfg['m.manPower.staff.hasStaffCommission']) {
                    $staffCommission = $displayLinkData->getLinkPortalMain("manPower_staff", "manPower_staffCommissionLink", "Staff Commission", $row);
                }
            }

            if ($cpCfg['cp.hasProjectMg'] == 1) {
                $signature = $media->getRightPanelMediaDisplay('Signature', 'manPower_staff', 'signature', $row);
            }

            if ($_SESSION['userGroupType'] == 'Super Administrator') {
				if($_SESSION['cp_site_id'] == 1){
					$urlStaffContract = "index.php?module=manPower_staff&_spAction=printStaffContract&staff_id={$row['staff_id']}&showHTML=0";
					$contractButton = "
			        <div class='floatbox  btnbackground'>
			            <div class='button mb5'>
			                <a href='{$urlStaffContract}' id='staffContract'>Print Employment Contract</a>
			            </div> 
			        </div>        
					";
				} else {
					$urlStaffContract = "index.php?module=manPower_staff&_spAction=printStaffContractAbuDhabi&staff_id={$row['staff_id']}&showHTML=0";
					$contractButton = "
			        <div class='floatbox  btnbackground'>
			            <div class='button mb5'>
			                <a href='{$urlStaffContract}' id='staffContract'>Print Employment Contract</a>
			            </div> 
			        </div>        
					";

				}
            }
    
            if ($_SESSION['userGroupType'] == 'Super Administrator') {
				$staffDocumentLink = "
		        <div class='header' expanded='1'>
		            Staff Document Link              
		        </div>
		        <div class='staffDocs'>
		            {$this->getStaffDocument($row['staff_id'])}
		        </div>
				";
            }

		    $urlDeclarationWord = "index.php?module=manPower_staff&_spAction=printDeclarationWord&staff_id={$row['staff_id']}&showHTML=0";
		    $urlNoDueWord = "index.php?module=manPower_staff&_spAction=printNoDueWord&staff_id={$row['staff_id']}&showHTML=0";
		    $urlCancelWord = "index.php?module=manPower_staff&_spAction=printCancelWord&staff_id={$row['staff_id']}&showHTML=0";
		    $urlResignationWord = "index.php?module=manPower_staff&_spAction=printResignationWord&staff_id={$row['staff_id']}&showHTML=0";
            
            if ($_SESSION['userGroupType'] != 'Agent') {
    			$declarationButton = "
    	        <div class='floatbox  btnbackground'>
    	            <div class='button mb5'>
    	                <a href='{$urlDeclarationWord}' id='staffResume' target='_blank'>Declaration Draft</a>
    	            </div> 
    	        </div>        
    			";

    			$noDueWordButton = "
    	        <div class='floatbox  btnbackground'>
    	            <div class='button mb5'>
    	                <a href='{$urlNoDueWord}' id='staffResume' target='_blank'>No Due Draft</a>
    	            </div> 
    	        </div>        
    			";

    			/*$cancelButton = "
    	        <div class='floatbox  btnbackground'>
    	            <div class='button mb5'>
    	                <a href='{$urlCancelWord}' id='staffResume' target='_blank'>Cancel</a>
    	            </div> 
    	        </div>        
    			";*/

    			$resignationButton = "
    	        <div class='floatbox  btnbackground'>
    	            <div class='button mb5'>
    	                <a href='{$urlResignationWord}' id='staffResume' target='_blank'>Resignation</a>
    	            </div> 
    	        </div>        
    			";
    		}
            
            $attachments ="
            {$media->getRightPanelMediaDisplay($ln->gd('m.manPower.staff.link.picture', 'Picture'), "manPower_staff", "picture", $row)}
            {$media->getRightPanelMediaDisplay('Passport Copy – Front & Back in Colour', 'manPower_staff', 'attachment', $row)}        
            {$media->getRightPanelMediaDisplay('UG Convocation Degree Certificate, Mark Statement in Colour', 'manPower_staff', 'attachment1', $row)}        
            {$media->getRightPanelMediaDisplay('PG Convocation Degree Certificate, Mark Statement in Colour', 'manPower_staff', 'attachment2', $row)}        
            {$media->getRightPanelMediaDisplay('Other Trade Certificates in Colour', 'manPower_staff', 'attachment3', $row)}        
            {$media->getRightPanelMediaDisplay('Transfer Certificate in Colour', 'manPower_staff', 'attachment4', $row)}        
            {$media->getRightPanelMediaDisplay('Application Copy', 'manPower_staff', 'attachment5', $row)}        
            {$media->getRightPanelMediaDisplay('Approval Copy', 'manPower_staff', 'attachment6', $row)}        
            {$media->getRightPanelMediaDisplay('Renewal Application Copy (Optional)', 'manPower_staff', 'attachment7', $row)}        
            {$media->getRightPanelMediaDisplay('Renewal Approval Copy (Optional)', 'manPower_staff', 'attachment8', $row)}        
            {$media->getRightPanelMediaDisplay('Upgrade Application Copy (Optional)', 'manPower_staff', 'attachment9', $row)}        
            {$media->getRightPanelMediaDisplay('Upgrade Approval Copy (Optional)', 'manPower_staff', 'attachment10', $row)}        
            {$media->getRightPanelMediaDisplay('Passport Size Photo in Colour', 'manPower_staff', 'attachment11', $row)}        
            {$media->getRightPanelMediaDisplay('Staff Letter Head', 'manPower_staff', 'attachment13', $row)}        
            {$media->getRightPanelMediaDisplay('No due Letter', 'manPower_staff', 'attachment14', $row)}        
            {$media->getRightPanelMediaDisplay('Cancel Letter', 'manPower_staff', 'attachment15', $row)}        
            {$media->getRightPanelMediaDisplay('Declaration Letter', 'manPower_staff', 'attachment16', $row)}        
            {$media->getRightPanelMediaDisplay('Digital Sign Agreement', 'manPower_staff', 'attachment17', $row)}        
            {$media->getRightPanelMediaDisplay('Resignation Letter', 'manPower_staff', 'attachment18', $row)}        
            {$media->getRightPanelMediaDisplay('Extra Attached', 'manPower_staff', 'attachment19', $row)}        
            ";
            
        } else {
            $attachment ="
            {$media->getRightPanelMediaDisplay('Attachment', 'manPower_staff', 'attachment', $row)}        
            ";
        }

        $text = "
		{$declarationButton}
		{$noDueWordButton}
		{$cancelButton}
		{$resignationButton}
        {$attachment}
        {$contractButton}
        {$attachments}
		{$staffDocumentLink}
        {$signature}
        {$staffGroup}
        {$staffCommission}
        ";
        return $text;
    }

    /**
     *
     */
    function getStaffXML() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $text = "";
        $project_id       = $fn->getReqParam('project_id');
        $opportunity_id   = $fn->getReqParam('opportunity_id');

        $text = "";

        $text .= $fn->getAjaxXMLHeader();
        $text .= "<data>";

        if ($opportunity_id != "") {
            $SQL    = "
            SELECT a.staff_id
                  ,CONCAT_WS(' ', a.first_name, a.last_name ) AS staff_name
            FROM staff a, opportunity_staff b
            WHERE a.staff_id = b.staff_id
              AND b.opportunity_id = {$opportunity_id}
            ORDER BY staff_name
            ";
        } else {
            $SQL    = "
            SELECT a.staff_id
                  ,CONCAT_WS(' ', a.first_name, a.last_name ) AS staff_name
            FROM staff a, project_staff b
            WHERE a.staff_id = b.staff_id
              AND b.project_id = {$project_id}
            ORDER BY staff_name
            ";
        }
        $result = $db->sql_query($SQL);

        while ($row = $db->sql_fetchrow($result)) {
            $text .= "<row>";
            $text .= "<record_id>"      . $row[$cpCfg['cp.modAccessStaffIdLabel']]     . "</record_id>";
            $text .= "<title><![CDATA[" . $row['staff_name']   . "]]></title>";
            $text .= "</row>";
        }
        $text .= "</data>";

        return $text;
    }

    /**
     *
     */
    function getQuickSearch() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $cpUtil = Zend_Registry::get('cpUtil');

        $stfGrp     = '';
        $statusTxt  = '';
        $countryTxt = '';
        $userGrp    = '';

        $user_group_id  = $fn->getReqParam('user_group_id');
        $staff_group_id = $fn->getReqParam('staff_group_id');
        $status         = $fn->getReqParam('status');

        if ($cpCfg['m.manPower.staff.showUserGroup'] == 1 || $cpCfg['cp.hasAccessModule']){
            
            $appendSql = "";
            /*if ($_SESSION['cp_site_id']) {
                $appendSql = "WHERE site_id = {$_SESSION['cp_site_id']}";
            }*/
            $sqlUG = "
            SELECT user_group_id
                  ,title
            FROM {$cpCfg['cp.modAccessUserGroupTable']}
            {$appendSql}
            ORDER BY title
            ";

            $userGrp = "
            <td>
                <select name='user_group_id' >
                    <option value=''>{$ln->gd('m.manPower.staff.lbl.group', 'Group')}</option>
                    {$dbUtil->getDropDownFromSQLCols2($db, $sqlUG, $user_group_id)}
                </select>
            </td>
            ";
        }

        if ($cpCfg['m.manPower.hasStaffGroup'] == 1) {
            $appendSql = "";
            if ($_SESSION['cp_site_id']) {
                $appendSql = "WHERE site_id = {$_SESSION['cp_site_id']}";
            }

            $sqlCombo = "
            SELECT staff_group_id
                  ,title
            FROM staff_group
            {$appendSql}
            ORDER BY title
            ";

            $stfGrp = "
            <td>
                <select name='staff_group_id'>
                    <option value=''>{$ln->gd('m.manPower.staff.lbl.staffGroup', 'Staff Group')}</option>
                    {$dbUtil->getDropDownFromSQLCols2($db, $sqlCombo, $staff_group_id)}
                </select>
            </td>
            ";
        }

        if ($cpCfg['cp.hasProjectMg'] == 1) {
            $fnMod = includeCPClass('ModuleFns', 'manPower_staff');
            $sqlCombo = $fnMod->getStaffStatusArray();
            
            $statusTxt = "
            <td>
                <select name='status'>
                    <option value=''>{$ln->gd('m.manPower.staff.lbl.status', 'Status')}</option>
                    {$cpUtil->getDropDown1($sqlCombo, $status)}
                </select>
            </td>
            ";
        }

        $fnModCountry = includeCPClass('ModuleFns', 'common_country');

        $text = "
        {$userGrp}
        {$stfGrp}
        {$statusTxt}
        {$fnModCountry->getCountryDropDown('search')}
        ";

        return $text;
    }

    /**
     *
     */
    function getStaffDocument($staff_id) {
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $db = Zend_Registry::get('db');
        $ln = Zend_Registry::get('ln');
                       
        $text= '';
        
        $SQL = "
        SELECT d.documents_id
              ,d.title
        FROM documents d
        WHERE d.module_name = 'Staff'
        ";
        $result = $db->sql_query($SQL);

        $formAction = "index.php?_topRm=admin&module=manPower_staff&_spAction=staffDocumentSubmit&showHTML=0";

        while ($row = $db->sql_fetchrow($result)) {
            $staffDocumentRec = $fn->getRecordByCondition('staff_documents', 
                                                      "staff_id = '{$staff_id}' AND documents_id = {$row['documents_id']}");
            $checked = $staffDocumentRec['staff_documents_id'] != '' ? "checked='checked'" : '';
            $text .= "
            <div class='documentChk'>
                <input type='checkbox' name='documents' value='1' {$checked}
                 staff_id='{$staff_id}' documents_id='{$row['documents_id']}' class='staffDocument_{$row['documents_id']}'>
                <label name='documents'>{$row['title']}</label>
            </div>
            <input type='hidden' name='staff_id' value='{$staff_id}' />
            ";
        }

        return $text;
    }

}