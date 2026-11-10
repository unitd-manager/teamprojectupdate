<?
class CP_Admin_Modules_Labsg_Company_View extends CP_Common_Lib_ModuleViewAbstract
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
            {$listObj->getListDataCell("<a href='{$website}'>{$website}</a>")}
            {$listObj->getListDataCell($row['phone'])}
            {$listObj->getListRowEnd($row['company_id'])}
            ";

            $count++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Name', 'c.company_name')}
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
        {$formObj->getTBRow('ClientName', 'company_name')}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fielset1)}
        ";

        return $text;
    }

    /**
     *
     */
    function getEditOld($row){
        $formObj = Zend_Registry::get('formObj');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        $formObj->mode = $tv['action'];

        $discountPercent = '';
        $cstNo = '';
        $tinNo = '';

        $sqlStatus   = $fn->getValueListSQL('companyStatus');
        $sqlSupplier = $fn->getValueListSQL('supplierType');
        $sqlIndustry = $fn->getValueListSQL('companyIndustry');
        $sqlSize     = $fn->getValueListSQL('companySize');
        $sqlSource   = $fn->getValueListSQL('companySource');

        $sqlCountry = getCPModelObj('common_geoCountry')->getCountryDDSQL();
        $expCountry = array('detailValue' => $row['country_name']);

        $expVl = array('sqlType' => 'OneField');

        $fieldset1 = "
        {$formObj->getTBRow('Name', 'company_name', $row['company_name'])}
        {$formObj->getTBRow('Website', 'website', $row['website'])}
        {$formObj->getTBRow('Main Phone', 'phone', $row['phone'])}
        {$formObj->getTBRow('Main Fax', 'fax', $row['fax'])}
        ";

        $fieldset2 = "
        {$formObj->getTBRow('Address1', 'address_flat', $row['address_flat'])}
        {$formObj->getTBRow('Address2', 'address_street', $row['address_street'])}
        {$formObj->getTBRow('District/ Town', 'address_town', $row['address_town'])}
        {$formObj->getTBRow('State/ Zip', 'address_state', $row['address_state'])}
        {$formObj->getDDRowBySQL('Country', 'address_country', $sqlCountry, $row['address_country'], $expCountry)}
        ";

		$fieldset3 = "
        {$formObj->getTBRow('Address1', 'billing_address_flat', $row['billing_address_flat'])}
        {$formObj->getTBRow('Address2', 'billing_address_street', $row['billing_address_street'])}
        {$formObj->getTBRow('District/ Town', 'billing_address_town', $row['billing_address_town'])}
        {$formObj->getTBRow('State/ Zip', 'billing_address_state', $row['billing_address_state'])}
        {$formObj->getDDRowBySQL('Country', 'billing_address_country', $sqlCountry, $row['billing_address_country'], $expCountry)}
		";


        $text = "
        {$formObj->getFieldSetWrapped('Company Details', $fieldset1)}
        {$formObj->getFieldSetWrapped('Client Delivery Address', $fieldset2)}
        {$formObj->getFieldSetWrapped('Client Billing Address', $fieldset3)}
        {$formObj->getCreationModificationText($row)}
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

        $discountPercent = '';
        $cstNo = '';
        $tinNo = '';

        $sqlStatus   = $fn->getValueListSQL('companyStatus');
        $sqlSupplier = $fn->getValueListSQL('supplierType');
        $sqlIndustry = $fn->getValueListSQL('companyIndustry');
        $sqlSize     = $fn->getValueListSQL('companySize');
        $sqlSource   = $fn->getValueListSQL('companySource');

        $sqlCountry = getCPModelObj('common_geoCountry')->getCountryDDSQL();
        $expCountry = array('detailValue' => $row['country_name']);

        $expVl = array('sqlType' => 'OneField');

        $text = "
        <div class='linkPortalWrapper'>
            <div expanded='1' class='header'>
                <div class='floatbox'>
                    <div class='float_left'>Company Details</div>
                    <div class='toggle'></div>
                </div>
            </div>
            <div>
                <div class='linkPortalDataWrapper'>
                    <table class='thinlist'>
                        <tbody>
                            <tr>
                                <td>{$formObj->getTBRow('Name', 'company_name', $row['company_name'])}</td>
                                <td>{$formObj->getTBRow('Website', 'website', $row['website'])}</td>
                                <td>{$formObj->getTBRow('Main Phone', 'phone', $row['phone'])}</td>
                                <td>{$formObj->getTBRow('Main Fax', 'fax', $row['fax'])}</td>
                                <td>{$formObj->getTARow('Remark', 'notes', $row['notes'])}</td>
                            </tr>

                            <tr>
                                <th colspan='5'>Client Delivery Address</th>
                            </tr>

                            <tr>
                                <td>{$formObj->getTBRow('Address1', 'address_flat', $row['address_flat'])}</td>
                                <td>{$formObj->getTBRow('Address2', 'address_street', $row['address_street'])}</td>
                                <td>{$formObj->getTBRow('District/ Town', 'address_town', $row['address_town'])}</td>
                                <td>{$formObj->getTBRow('State/ Zip', 'address_state', $row['address_state'])}</td>
                                <td>{$formObj->getDDRowBySQL('Country', 'address_country', $sqlCountry, $row['address_country'], $expCountry)}</td>
                            </tr>

                            <tr>
                                <th colspan='5'>Client Billing Address</th>
                            </tr>

                            <tr>
                                <td>{$formObj->getTBRow('Address 1', 'billing_address_flat', $row['billing_address_flat'])}</td>
                                <td>{$formObj->getTBRow('Address 2', 'billing_address_street', $row['billing_address_street'])}</td>
                                <td>{$formObj->getTBRow('District/ Town', 'billing_address_town', $row['billing_address_town'])}</td>
                                <td>{$formObj->getTBRow('State/ Zip', 'billing_address_state', $row['billing_address_state'])}</td>
                                <td>{$formObj->getDDRowBySQL('Country', 'billing_address_country', $sqlCountry, $row['billing_address_country'], $expCountry)}</td>
                            </tr>

                            <tr>
                                <td colspan='5' class='creModdate'>{$formObj->getCreationModificationText($row)}</td>
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
        $db = Zend_Registry::get('db');
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $media = Zend_Registry::get('media');

        $record_id = $fn->getIssetParam($row, 'company_id');

        $text = "
        <div id='treatmentLinkPortal'>{$this->getAddTreatment($record_id)}</div>
        {$displayLinkData->getLinkPortalMain('labsg_company', 'labsg_contactLink', 'Company Contacts Linked', $row)}
        {$media->getRightPanelMediaDisplay('Attachments', 'labsg_company', 'attachment', $row)}
        ";

        return $text;
    }
    /**
     *
     */
    function getAddTreatment($company_id = ''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        if($company_id == ''){
            $company_id = $fn->getReqParam('company_id');
        }

        $Treatment = $this->getAddTreatmentDetail($company_id);

        $sqlCompany = "
        SELECT COUNT(*) AS company_treatment_count
        FROM company_treatment
        WHERE company_id = '{$company_id}'
        ";
        $resultCompany = $db->sql_query($sqlCompany);
        $rowCompany    = $db->sql_fetchrow($resultCompany);
        $recCount      = $rowCompany['company_treatment_count'];

        $header ="
        <thead>
            <tr>
                <th>Treatment</th>
                <th class='txtRight'>Treatment Amount</th>
                <th class='txtRight'>Edit</th>
                <th class='txtRight'>Delete</th>
            </tr>
        </thead>
        ";

        if($recCount == 0){
            $header ="<thead></thead>";
        }

        $formActionTreatment = "index.php?module=labsg_company&_spAction=Treatment&company_id={$company_id}&showHTML=0";

        $add = "<div class='actBtns'>
                    <a id='AddTreatment' href='{$formActionTreatment}' company_id={$company_id}>Add</a>
                </div>";

        $text = "
        <div class='linkPortalWrapper labsg_company__labsg_company_treatmentLink'>
            <div class='header' expanded='1'>
                <div class='floatbox'>
                    <div class='float_left'>Treatment Linked</div>
                    <div class='txtRight'>
                    <span class='count'>({$recCount})</span>
                    </div>
                </div>
            </div>
            <div class='linkPortalDataWrapper'>
                <form>
                    <table class='renewallist'>
                        {$header}
                        <tbody id='AddTreatmentPortal'>
                            {$Treatment}
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
    /**
     *
     */
    function getAddTreatmentDetail($company_id = ''){
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
        SELECT ct.*
              ,t.title AS Treatment_Name
        FROM company_treatment ct
        LEFT JOIN (treatment t) ON (t.treatment_id = ct.treatment_id)
        WHERE ct.company_id = '{$company_id}'
        ";
        $result   = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        $count = 1;
        while ($row = $db->sql_fetchrow($result)) {

            $formActionEditTreatment   = "index.php?module=labsg_company&_spAction=EditTreatment&company_treatment_id={$row['company_treatment_id']}&showHTML=0";

            $deleteIcon ="
                <div class='float_right'>
                    <a class='deleteTreatment' href='#' company_id='{$company_id}' treatment_id='{$row['treatment_id']}' company_treatment_id='{$row['company_treatment_id']}'>
                        <img src='/cmspilotv30/CP/admin/images/icons/btn_remove.png'>
                    </a>
                </div>
                ";

            $editIcon ="
                <div class='float_right'>
                    <a class='EditTreatment' href='{$formActionEditTreatment}' company_id='{$company_id}' company_treatment_id='{$row['company_treatment_id']}'>
                        <img src='/cmspilotv30/CP/admin/images/icons/btn_edit.png'>
                    </a>
                </div>
                ";


            $rows .= "
                <tr>
                    <td>{$row['Treatment_Name']}</td>
                    <td class='txtRight'>{$row['amount']}</td>
                    <td>{$editIcon}</td>
                    <td>{$deleteIcon}</td>
                </tr>
            ";
            $count++;
        }

        if ($numRows == 0) {
            $rows .= "
            <tr>
                <td class='noRenewal'>No Records Linked</td>
            </tr>
            ";
        }

        $text = "{$rows}";

        return $text;
    }
    /**
     *
     */
    function getTreatment() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $company_id  = $fn->getReqParam('company_id');
        $formAction = "index.php?_topRm=order&module=labsg_company&_spAction=TreatmentFormSubmit&showHTML=0";

        $sqltreatmenttitle = "
        SELECT treatment_id
              ,title AS  Treatment_Name
        FROM treatment
        ";


        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            {$formObj->getDDRowBySQL('Treatment', 'treatment_id', $sqltreatmenttitle,'')}
            {$formObj->getTBRow('Amount', 'amount')}
            <input type='hidden' name='company_id' value='{$company_id}' />
        </form>
        ";
        return $text;
    }
     /**
     *
     */
    function getEditTreatment() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $company_treatment_id  = $fn->getReqParam('company_treatment_id');
        $row = $fn->getRecordRowByID('company_treatment', 'company_treatment_id', $company_treatment_id);
        $expNoEdit = array('isEditable' => 0);
        
        $formAction = "index.php?module=labsg_company&_spAction=EditTreatmentFormSubmit&showHTML=0&company_treatment_id={$company_treatment_id}";

        $sqltreatmenttitle = "
        SELECT treatment_id
              ,title AS  Treatment_Name
        FROM treatment
        ";

        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            {$formObj->getTBRow('Amount', 'amount', $row['amount'])}
            <input type='hidden' name='company_treatment_id' value='{$company_treatment_id}' />
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

        $status   = $fn->getReqParam('status');

        $sqlStatus = $fn->getValueListSQL('companyStatus');

        $spArray = array(
            "Flagged"
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