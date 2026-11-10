<?
class CP_Admin_Modules_ManPower_Company_View extends CP_Common_Lib_ModuleViewAbstract
{
    /**
     *
     */
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $db = Zend_Registry::get('db');

        $count   = 0;
        $rows    = '';

        foreach ($dataArray as $row){
            $email     = $row['email'];
            $website   = $row['website'];

            $rows .= "
            {$listObj->getListRowHeader($row, $count)}
            {$listObj->getListDataCell($row['client_code'])}
            {$listObj->getListDataCell(strtoupper($row['company_name']))}
            {$listObj->getListDataCell($row['company_type'])}
            {$listObj->getListDataCell($row['category'])}
            {$listObj->getListDataCell($row['status'])}
            {$listObj->getListDataCell("<a href='{$website}'>{$website}</a>")}
            {$listObj->getListDataCell($row['phone'])}
            {$listObj->getListDataCell($row['modified_by'].' '.$row['modification_date'])}
            {$listObj->getListRowEnd($row['company_id'])}
            ";

            $count++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Code', 'a.client_code')}
        {$listObj->getListHeaderCell('Name', 'a.company_name')}
        {$listObj->getListHeaderCell('Client Type', 'a.company_type')}
        {$listObj->getListHeaderCell('Category', 'a.category')}
        {$listObj->getListHeaderCell('Status', 'a.status')}
        {$listObj->getListHeaderCell('Website', 'a.website')}
        {$listObj->getListHeaderCell('Telephone', 'a.phone' )}
        {$listObj->getListHeaderCell('Updated By', '' )}
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
        $fn      = Zend_Registry::get('fn');

        $sqlCompany_type = $fn->getValueListSQL('companyType');
        $expVl = array('sqlType' => 'OneField');

        $fielset1 = "
        {$formObj->getTBRow('Company Name', 'company_name')}
        {$formObj->getDDRowBySQL('Company Type', 'company_type', $sqlCompany_type,'Client',$expVl)}
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
        $userGroupType = $fn->getSessionParam('userGroupType');

        $expNoEdit = array('isEditable' => 0);

        $address     = '';


        $sqlCountry = getCPModelObj('common_geoCountry')->getCountryDDSQL();
        $expCountry = array('detailValue' => $row['country_name']);

        $address = "
        {$formObj->getTBRow('Address 1', 'address_flat', $row['address_flat'])}
        {$formObj->getTBRow('Address 2', 'address_street', $row['address_street'])}
        {$formObj->getTBRow('City', 'address_town', $row['address_town'])}
        {$formObj->getDDRowByArr('State', 'address_state', $cpCfg['m.manPower.project.stateListArr'], $row['address_state'])}
        {$formObj->getTBRow('Zip Code', 'address_po_code', $row['address_po_code'])}
        {$formObj->getDDRowBySQL('Country', 'address_country_code', $sqlCountry, $row['address_country_code'], $expCountry)}
        ";

        $address = $formObj->getFieldSetWrapped('Address', $address);

        $sqlCategory  = $fn->getValueListSQL('companyCategory');
        $sqlStatus    = $fn->getValueListSQL('companyStatus');
        $sqlCompany   = $fn->getValueListSQL('companyType');
        $sqlIndustry  = $fn->getValueListSQL('companyIndustry');
        $sqlSize      = $fn->getValueListSQL('companySize');
        $sqlSource    = $fn->getValueListSQL('companySource');
        $sqlGroupName = $fn->getValueListSQL('companyGroupName');

        $expVl = array('sqlType' => 'OneField');

        $code = '';
        if ($cpCfg['m.manPower.company.showCode'] == 1){
            $code = $formObj->getTBRow('Code', 'code', $row['code']);
        }

        if ($cpCfg['m.manPower.company.groupNameDD'] == 1){
            $groupName = $formObj->getDDRowBySQL('Group Name', 'group_name', $sqlGroupName, $row['group_name'], $expVl);
        } else {
            $groupName = $formObj->getTBRow('Group Name', 'group_name', $row['group_name']);
        }

        /** REMOVED THE STAFF NAME FILTER IN THE EDIT MODE AND LIST (THAMIM)**/
        /*$appendStaffSql = '';
        if($cpCfg['cp.hasMultiUniqueSites'] == true) {
            $appendStaffSql = "WHERE s.site_id = '{$_SESSION['cp_site_id']}' AND staff_type = 'Staff'"; 
        }*/

        /*$sqlStaff = "
        SELECT s.staff_id
              ,CONCAT_WS(' ', s.first_name, s.last_name) AS staff_name
        FROM staff s
        {$appendStaffSql}
        ORDER BY staff_name
        ";
        $expStaff = array('detailValue' => $row['staff_name']);

        $staffName ='';
        if ($userGroupType == 'Super Administrator' || $userGroupType == 'Administrator'){
            $staffName = $formObj->getDDRowBySQL('Staff Name', 'staff_id', $sqlStaff, $row['staff_id'], $expStaff);
        }*/

        //{$staffName}


		$clientCode ='';
        if ($userGroupType == 'Super Administrator' || $userGroupType == 'Administrator'){
            $clientCode = $formObj->getTBRow('Code', 'client_code', $row['client_code'], $expNoEdit);
        }

        $commission_percent = '';
        if($row['company_type'] == 'Referral'){
            $commission_percent ="
            {$formObj->getTBRow('Commission Percentage (%)', 'commission_percentage', $row['commission_percentage'])}
            ";
        }

        $fielset1 = "
		{$clientCode}
        {$formObj->getTBRow('Name *', 'company_name', $row['company_name'])}
        {$formObj->getDDRowBySQL('Type *', 'company_type', $sqlCompany, $row['company_type'], $expVl)}
        {$commission_percent}
        {$formObj->getTBRow('EIN', 'uen', $row['uen'])}
        {$code}
        {$formObj->getDDRowBySQL('Choose Category', 'category', $sqlCategory, $row['category'], $expVl)}
        {$formObj->getTBRow('Website', 'website', $row['website'])}
        {$formObj->getTBRow('E-mail ID', 'email', $row['email'])}
        {$formObj->getTBRow('Main Phone', 'phone', $row['phone'])}
        {$formObj->getTBRow('Main Fax', 'fax', $row['fax'])}
        ";

        //{$formObj->getDDRowBySQL('Company Source', 'source', $sqlSource, $row['source'], $expVl)}

        $fielset2 = "
        {$formObj->getTBRow('Remarks', 'remarks', $row['remarks'])}
        {$formObj->getDDRowBySQL('Status', 'status', $sqlStatus, $row['status'], $expVl)}
	    {$formObj->getDDRowBySQL('Industry', 'industry', $sqlIndustry, $row['industry'], $expVl)}
        {$formObj->getDDRowBySQL('Company Size', 'company_size', $sqlSize, $row['company_size'], $expVl)}
        {$formObj->getTBRow('Annual Turn Over', 'annual_turn_over', $row['annual_turn_over'])}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Company Details', $fielset1)}
        {$address}
        {$formObj->getFieldSetWrapped('More Details', $fielset2)}
        {$formObj->getCreationModificationText($row)}
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
    function getCompanyDocument($company_id) {
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $db = Zend_Registry::get('db');
        $ln = Zend_Registry::get('ln');
                       
        $text= '';
        
        $SQL = "
        SELECT d.documents_id
              ,d.title
        FROM documents d
        WHERE d.module_name = 'Client'
        ";
        $result = $db->sql_query($SQL);

        $formAction = "index.php?_topRm=admin&module=manPower_company&_spAction=companyDocumentSubmit&showHTML=0";

        while ($row = $db->sql_fetchrow($result)) {
            $companyDocumentRec = $fn->getRecordByCondition('company_documents', 
                                                      "company_id = '{$company_id}' AND documents_id = {$row['documents_id']}");
            $checked = $companyDocumentRec['company_documents_id'] != '' ? "checked='checked'" : '';
            $text .= "
            <div class='documentChk'>
                <input type='checkbox' name='documents' value='1' {$checked}
                 company_id='{$company_id}' documents_id='{$row['documents_id']}' class='companyDocument_{$row['documents_id']}'>
                <label name='documents'>{$row['title']}</label>
            </div>
            <input type='hidden' name='company_id' value='{$company_id}' />
            ";
        }

        return $text;
    }

    /**
     *
     */
    function getRightPanel($row){
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $linksArray = Zend_Registry::get('linksArray');
        $comment = getCPPluginObj('common_comment');
        $media = Zend_Registry::get('media');
        
        
        $links   = "";
        $expProj = array();

        $record_id = $fn->getIssetParam($row, 'company_id');

		$companyDocumentLink ='';
        $attachment = '';

        if( $cpCfg['cp.hasMultiUniqueSites'] == 'true'){
            if ($_SESSION['userGroupType'] == 'Super Administrator') {
    						$companyDocumentLink = "
    				        <div class='header' expanded='1'>
    				            Client Document Link              
    				        </div>
    				        <div class='companyDocs'>
    				            {$this->getCompanyDocument($row['company_id'])}
    				        </div>
    						";
            }

            if ($cpCfg['m.manPower.company.showAttachment'] == 1){
                $links .= $media->getRightPanelMediaDisplay('Attachments', 'manPower_company', 'attachment', $row);
                $links .= $media->getRightPanelMediaDisplay('ACRA Profile', 'manPower_company', 'attachment1', $row);
                $links .= $media->getRightPanelMediaDisplay('Employer IC Copy (Front&Back)', 'manPower_company', 'attachment2', $row);
                $links .= $media->getRightPanelMediaDisplay('NEA Certificate (Optional-only for Restaurant)', 'manPower_company', 'attachment3', $row);
            }
        } else {
            $attachment ="
            {$media->getRightPanelMediaDisplay('Attachment', 'manPower_company', 'attachment', $row)}        
            ";
        }

        if($row['company_type'] == 'Client'){
            $agreement ="";
        }
        else{
            $agreement ="
            <div class='floatbox actionBtnsDetail'>
                <div class='orderbtnbackground floatbox'>
                    <div class='float_right button mb10'>
                        <a href='/admin/lib/template/Sub Contractor Agreement.doc' id='subContractorAgreement'>Agreement</a>
                    </div>
                </div>
            </div>
            ";
        }

        $text = "
        {$agreement}
        {$displayLinkData->getLinkPortalMain('manPower_company', 'manPower_contactLink', 'Contacts Linked', $row)}
        {$attachment}
        {$links}
		{$companyDocumentLink}
        ";
        /*
        {$comment->getView(array(
             'roomName' => 'manPower_company'
            ,'recordId' => $record_id
            ,'contactModule' => 'manPower_staff'
        ))}
        */
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

        $category       = $fn->getReqParam('category');
        $status         = $fn->getReqParam('status');
        $company_type   = $fn->getReqParam('company_type');

        $sqlCat         = $fn->getValueListSQL('companyCategory');
        $sqlStatus      = $fn->getValueListSQL('companyStatus');
        $sqlClientType  = $fn->getValueListSQL('companyType');

        $spArray = array(
            "Flagged"
           ,"Flagged"
           ,"Not-Flagged"
        );

        $text = "
        <td>
            <select name='company_type'>
                <option value=''>Client Type</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $sqlClientType, $company_type)}
            </select>
        </td>    
        <td>
            <select name='category'>
                <option value=''>Category</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $sqlCat, $category)}
            </select>
        </td>    
        <td>
            <select name='status' >
                <option value=''>Status</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $sqlStatus, $status)}
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

}