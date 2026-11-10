<?
class CP_Admin_Modules_EnggCrm_Company_View extends CP_Common_Lib_ModuleViewAbstract
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
            <td><div align='left'><a href='mailto:{$email}'>{$email}</a></div></td>
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
        {$listObj->getListHeaderCell('Email', 'a.email')}
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
        {$formObj->getTBRow('Company Name *', 'company_name')}
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
        $deliveryAddress = '';

        if ($cpCfg['m.enggCrm.company.showChineseFields'] == 1){
            $chineseName = $formObj->getTBRow('Company Name (Chinese)', 'chi_company_name', $row['chi_company_name']);
            $chineseAdd  = $formObj->getTARow('Company Address (Chinese)', 'chi_company_address', $row['chi_company_address']);
        }

        if ($cpCfg['m.enggCrm.hasMultipleCompanyAddress'] == 0){
            $address = "
            {$formObj->getTBRow('Address 1', 'billing_address_flat', $row['billing_address_flat'])}
            {$formObj->getTBRow('Address 2', 'billing_address_street', $row['billing_address_street'])}
            {$formObj->getTBRow('Country', 'billing_address_country', $row['billing_address_country'])}
            {$formObj->getTBRow('Postal Code', 'billing_address_po_code', $row['billing_address_po_code'])}
            ";
            $address = $formObj->getFieldSetWrapped('Billing Address', $address);

            $deliveryAddress = "
            {$formObj->getTBRow('Address 1', 'address_flat', $row['address_flat'])}
            {$formObj->getTBRow('Address 2', 'address_street', $row['address_street'])}
            {$formObj->getTBRow('Country', 'address_country', $row['address_country'])}
            {$formObj->getTBRow('Postal Code', 'address_po_code', $row['address_po_code'])}
            ";
            $deliveryAddress = $formObj->getFieldSetWrapped('Delivery Address', $deliveryAddress);
        }

        $sqlStatus   = $fn->getValueListSQL('companyStatus');
        $sqlSupplier = $fn->getValueListSQL('supplierType');
        $sqlIndustry = $fn->getValueListSQL('companyIndustry');
        $sqlSize     = $fn->getValueListSQL('companySize');
        $sqlSource   = $fn->getValueListSQL('companySource');
        $sqlGroupName = $fn->getValueListSQL('companyGroupName');

        $expVl = array('sqlType' => 'OneField');

        $code = '';
        if ($cpCfg['m.enggCrm.company.showCode'] == 1){
            $code = $formObj->getTBRow('Code', 'code', $row['code']);
        }

        if ($cpCfg['m.enggCrm.company.groupNameDD'] == 1){
            $groupName = $formObj->getDDRowBySQL('Group Name', 'group_name', $sqlGroupName, $row['group_name'], $expVl);
        } else {
            $groupName = $formObj->getTBRow('Group Name', 'group_name', $row['group_name']);
        }

        $fielset1 = "
        {$formObj->getTBRow('Company Name *', 'company_name', $row['company_name'])}
        {$code}
        {$groupName}
        {$chineseName}
        {$formObj->getTBRow('Website', 'website', $row['website'])}
        {$formObj->getTBRow('Main Phone', 'phone', $row['phone'])}
        {$formObj->getTBRow('Main Fax', 'fax', $row['fax'])}
        {$formObj->getTBRow('Email', 'email', $row['email'])}
        {$formObj->getTBRow('Client Code', 'client_code', $row['client_code'])}
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
        {$deliveryAddress}
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
            $links  = $displayLinkData->getLinkPortalMain('enggCrm_company', 'enggCrm_projectLink', 'Projects Linked', $row, $expProj);
            $links .= $displayLinkData->getLinkPortalMain('enggCrm_company', 'enggCrm_invoiceLink', 'Invoices Linked', $row);
            $links .= $displayLinkData->getLinkPortalMain('enggCrm_company', 'enggCrm_opportunityLink', 'Opportunities Linked', $row, $expProj);
        }
        
        if ($cpCfg['m.enggCrm.hasMultipleCompanyAddress'] == 1){
            $links .= $displayLinkData->getLinkPortalMain('enggCrm_company', 'enggCrm_companyAddressLink', 'Company Address', $row);
        }
        if ($cpCfg['m.enggCrm.company.showAttachment'] == 1){
            $links .= $media->getRightPanelMediaDisplay('Attachments', 'enggCrm_company', 'attachment', $row);
        }
        
		
		//Used in USSPromgmt//
        if ($cpCfg['m.enggCrm.company.showProductLink'] == 1){
            $links .= $displayLinkData->getLinkPortalMain('enggCrm_company', 'webBasic_productLink', 'Product Linked', $row);
        }

        $text = "
        {$displayLinkData->getLinkPortalMain('enggCrm_company', 'enggCrm_contactLink', 'Contacts Linked', $row)}
        {$links}
        {$comment->getView(array(
             'roomName' => 'enggCrm_company'
            ,'recordId' => $record_id
        ))}
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

        $category = $fn->getReqParam('category');
        $status   = $fn->getReqParam('status');

        $sqlStatus = $fn->getValueListSQL('companyStatus');

        $spArray = array(
            "Flagged"
           ,"Flagged"
           ,"Not-Flagged"
        );

        $text = "
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