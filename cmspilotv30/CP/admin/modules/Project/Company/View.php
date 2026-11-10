<?
class CP_Admin_Modules_Project_Company_View extends CP_Common_Lib_ModuleViewAbstract
{
    /**
     *
     */
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        
        $count   = 0;
        $rows    = '';

        foreach ($dataArray as $row){
            $email     = $row['email'];
            $website   = $row['website'];

            $rows .= "
            {$listObj->getListRowHeader($row, $count)}
            {$listObj->getGoToDetailText($count, $row['company_name'])}
            {$listObj->getListDataCell($row['category'])}
            {$listObj->getListDataCell($row['status'])}
            {$listObj->getListDataCell($row['company_size'])}
            {$listObj->getListDataCell($row['industry'])}
            {$listObj->getListDataCell($row['source'])}
            {$listObj->getListDataCell("<a href='{$website}'>{$website}</a>")}
            {$listObj->getListDataCell($row['phone'])}
            {$listObj->getListRowEnd($row['company_id'])}
            ";

            $count++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Company Name', 'a.company_name')}
        {$listObj->getListHeaderCell('Category', 'a.category')}
        {$listObj->getListHeaderCell('Status', 'a.status')}
        {$listObj->getListHeaderCell('Company Size', 'a.company_size')}
        {$listObj->getListHeaderCell('Industry', 'a.industry')}
        {$listObj->getListHeaderCell('Source', 'a.source')}
        {$listObj->getListHeaderCell('Website', 'a.website')}
        {$listObj->getListHeaderCell('Telephone', 'a.phone' )}
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
        {$formObj->getTBRow('Company Name', 'company_name')}
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

        $chineseName = '';
        $chineseAdd  = '';
        $address     = '';

        if ($cpCfg['m.project.company.showChineseFields'] == 1){
            $chineseName = $formObj->getTBRow('Company Name (Chinese)', 'chi_company_name', $row['chi_company_name']);
            $chineseAdd  = $formObj->getTARow('Company Address (Chinese)', 'chi_company_address', $row['chi_company_address']);
        }

        if ($cpCfg['m.project.hasMultipleCompanyAddress'] == 0){
            $address = "
            {$formObj->getTBRow('Office Address', 'address_flat', $row['address_flat'])}
            {$formObj->getTBRow('Street Address', 'address_street', $row['address_street'])}
            {$formObj->getTBRow('District/ Town', 'address_town', $row['address_town'])}
            {$formObj->getTBRow('State/ Zip', 'address_state', $row['address_state'])}
            {$formObj->getTBRow('Country', 'address_country', $row['address_country'])}
            ";

            $address = $formObj->getFieldSetWrapped('Address', $address);
        }

        $sqlCategory = $fn->getValueListSQL('companyCategory');
        $sqlStatus   = $fn->getValueListSQL('companyStatus');
        $sqlSupplier = $fn->getValueListSQL('supplierType');
        $sqlIndustry = $fn->getValueListSQL('companyIndustry');
        $sqlSize     = $fn->getValueListSQL('companySize');
        $sqlSource   = $fn->getValueListSQL('companySource');
        $sqlGroupName = $fn->getValueListSQL('companyGroupName');

        $expVl = array('sqlType' => 'OneField');

        $code = '';
        if ($cpCfg['m.project.company.showCode'] == 1){
            $code = $formObj->getTBRow('Code', 'code', $row['code']);
        }

        if ($cpCfg['m.project.company.groupNameDD'] == 1){
            $groupName = $formObj->getDDRowBySQL('Group Name', 'group_name', $sqlGroupName, $row['group_name'], $expVl);
        } else {
            $groupName = $formObj->getTBRow('Group Name', 'group_name', $row['group_name']);
        }

        $fielset1 = "
        {$formObj->getTBRow('Company Name', 'company_name', $row['company_name'])}
        {$code}
        {$formObj->getDDRowBySQL('Choose Category', 'category', $sqlCategory, $row['category'], $expVl)}
        {$groupName}
        {$chineseName}
        {$formObj->getTBRow('Website', 'website', $row['website'])}
        {$formObj->getTBRow('Main Phone', 'phone', $row['phone'])}
        {$formObj->getTBRow('Main Fax', 'fax', $row['fax'])}
        {$chineseAdd}
        ";

        $fielset2 = "
        {$formObj->getDDRowBySQL('Status', 'status', $sqlStatus, $row['status'], $expVl)}
  		{$formObj->getDDRowBySQL('Supplier Type', 'supplier_type', $sqlSupplier, $row['supplier_type'], $expVl)}
	    {$formObj->getDDRowBySQL('Industry', 'industry', $sqlIndustry, $row['industry'], $expVl)}
        {$formObj->getDDRowBySQL('Company Size', 'company_size', $sqlSize, $row['company_size'], $expVl)}
        {$formObj->getDDRowBySQL('Company Source', 'source', $sqlSource, $row['source'], $expVl)}
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

        if ($row['category'] != 'Supplier'){
            $links  = $displayLinkData->getLinkPortalMain('project_company', 'project_projectLink', 'Projects Linked', $row, $expProj);
            $links .= $displayLinkData->getLinkPortalMain('project_company', 'project_invoiceLink', 'Invoices Linked', $row);
            $links .= $displayLinkData->getLinkPortalMain('project_company', 'project_opportunityLink', 'Opportunities Linked', $row, $expProj);
        }
        
        if ($cpCfg['m.project.hasMultipleCompanyAddress'] == 1){
            $links .= $displayLinkData->getLinkPortalMain('project_company', 'project_companyAddressLink', 'Company Address', $row);
        }
        if ($cpCfg['m.project.company.showAttachment'] == 1){
            $links .= $media->getRightPanelMediaDisplay('Attachments', 'project_company', 'attachment', $row);
        }
		
		//Used in USSPromgmt//
        if ($cpCfg['m.project.company.showProductLink'] == 1){
            $links .= $displayLinkData->getLinkPortalMain('project_company', 'webBasic_productLink', 'Product Linked', $row);
        }

        $text = "
        <div id='renewalLinkPortal'>{$this->getRenewalDisplay($row['company_id'])}</div>
        {$displayLinkData->getLinkPortalMain('project_company', 'project_contactLink', 'Contacts Linked', $row)}
        {$links}
        {$comment->getView(array(
             'roomName' => 'project_company'
            ,'recordId' => $record_id
        ))}
        ";

        return $text;
    }

    /**
     */
    function getRenewalDisplay($company_id=''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        if($company_id == ''){
            $company_id = $fn->getReqParam('company_id');
        }

        $Renewals = $this->getRenewalDisplayDetail($company_id);

        $recCount = $fn->getRecordCount('renewals', "company_id = '{$company_id}'");

        $header ="
        <thead>
            <tr>
            <th>#</th>
            <th>Renewal type</th>
            <th>Domain Name</th>
            <th class='txtRight'>Total Amount</th>
            <th>Start Date</th>
            <th>End Date</th>
            <th>Notes</th>
            <th>Project Code</th>
            <th class='portalActBtns'></th>
            <th class='portalActBtns'></th>
            </tr>
        </thead>
        ";

        if($recCount == 0){
            $header ="<thead></thead>";
        }

        $formActionAddRenewal    = "index.php?module=project_company&_spAction=addRenewal&company_id={$company_id}&showHTML=0";

        $add = "<div class='actBtns'>
                    <a id='addRenewalNew' href='{$formActionAddRenewal}' company_id={$company_id}>Add</a>
                </div>";

        $text = "
        <div class='linkPortalWrapper project_company__project_companyRenewalLink'>
            <div class='header' expanded='1'>
                <div class='floatbox'>
                    <div class='float_left'>Renewal Linked</div>
                    <div class='txtRight'>
                    <span class='count'>({$recCount})</span>
                    </div>
                </div>
            </div>
            <div class='linkPortalDataWrapper'>
                <form>
                    <table class='renewallist'>
                        {$header}
                        <tbody id='renewalDisplayPortal'>
                            {$Renewals}
                        </tbody>
                    </table>
                    <input type='hidden' name='company_id' value='{$company_id}' />
                </form>
            </div>
            {$add}
        </div>
        ";

        return $text;

    }

    function getRenewalDisplayDetail($company_id = ''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        if($company_id == ''){
            $company_id = $fn->getReqParam('company_id');
        }

        $rows  = "";

        $SQL="
        SELECT *
        FROM renewals
        WHERE company_id = '{$company_id}'
        ORDER BY renewal_id ASC
        ";
        $result   = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        $count = 1;
        while ($rowRenew = $db->sql_fetchrow($result)) {

            //$formDeleteRenewalRecord = "index.php?module=project_company&_spAction=deleteRenewalRecord&renewal_id={$rowRenew['renewal_id']}&company_id={$company_id}&showHTML=0";
            $formActionEditRenewal   = "index.php?module=project_company&_spAction=editRenewal&id={$rowRenew['renewal_id']}&company_id={$company_id}&showHTML=0";

            $deleteIcon ="
                <div class='float_right'>
                    <a class='deleteRenewalRecord' href='#'  company_id='{$company_id}' renewal_id='{$rowRenew['renewal_id']}'>
                        <img src='/cmspilotv30/CP/admin/images/icons/btn_remove.png'>
                    </a>
                </div>
                ";

            $editIcon ="
                <div class='float_right'>
                    <a class='editRenewal' href='{$formActionEditRenewal}' company_id='{$company_id}'>
                        <img src='/cmspilotv30/CP/admin/images/icons/btn_edit.png'>
                    </a>
                </div>
                ";

            $sqlProject = "
            SELECT project_code
                   ,project_id
            FROM project
            WHERE renewal_id = {$rowRenew['renewal_id']}
            ";
            $resultProject  = $db->sql_query($sqlProject);
            $rowProject     = $db->sql_fetchrow($resultProject);

            $projectLink = "<a href='index.php?_topRm=project&module=project_project&project_id={$rowProject['project_id']}&_action=detail'><u>{$rowProject['project_code']}</u></a>";

            //<td class='txtCenter'>{$fn->getYesNo($rowRenew['chargeable'])}</td>

            $start_date = $fn->getCPDate($rowRenew['start_date'],"d-m-Y");
            $end_date   = $fn->getCPDate($rowRenew['end_date'],"d-m-Y");

            $formActionExtendRenewal = "index.php?module=project_company&_spAction=extendRenewalForm&company_id={$company_id}&renewal_id={$rowRenew['renewal_id']}&showHTML=0";
            $Renew = "<div class='float_left'>
                        <a class='extendRenewal' href='{$formActionExtendRenewal}' company_id={$company_id}><u>Renew</u></a>
                      </div>";

            $renewalAmount = number_format($rowRenew['amount'],2);

            $rows .= "
                <tr>
                    <td>{$count}</td>
                    <td>{$rowRenew['renewal_type']}</td>
                    <td>{$rowRenew['domain']}</td>
                    <td class='txtRight'>{$renewalAmount}</td>
                    <td>{$start_date}</td>
                    <td>{$end_date}</td>
                    <td>{$rowRenew['notes']}</td>
                    <td>{$projectLink}</td>
                    <td>
                        {$Renew}
                    </td>
                    <td>
                        {$editIcon}
                    </td>
                </tr>
            ";
            $count++;
        }

        //{$deleteIcon}

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
    function getQuickSearch() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        $category = $fn->getReqParam('category');
        $status   = $fn->getReqParam('status');

        $sqlCat = $fn->getValueListSQL('companyCategory');
        $sqlStatus = $fn->getValueListSQL('companyStatus');

        $spArray = array(
            "Flagged"
           ,"Not-Flagged"
        );

        $text = "
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