<?
class CP_Admin_Modules_AceIms_Company_View extends CP_Common_Modules_AceIms_Company_View
{
    /**
     *
     */
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');

        $count   = 0;
        $rows    = '';

        foreach ($dataArray as $row) {
            $email     = $row['email'];
            $website   = $row['website'];

            $rows .= "
            {$listObj->getListRowHeader($row, $count)}
            {$listObj->getGoToDetailText($count, $row['title'])}
            {$listObj->getListDataCell($row['category'])}
            {$listObj->getListDataCell($row['status'])}
            {$listObj->getListDataCell($row['industry'])}
            {$listObj->getListDataCell($row['phone'])}
            {$listObj->getListRowEnd($row['company_id'])}
            ";

            $count++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Company Name', 'c.title')}
        {$listObj->getListHeaderCell('Category', 'c.category')}
        {$listObj->getListHeaderCell('Status', 'c.status')}
        {$listObj->getListHeaderCell('Industry', 'c.industry')}
        {$listObj->getListHeaderCell('Telephone', 'c.phone' )}
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
        {$formObj->getTBRow('Company Name', 'title')}
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

        $expVl = array('sqlType' => 'OneField');

        $sqlCountry = getCPModelObj('common_geoCountry')->getCountryDDSQL();
        $expCountry = array('detailValue' => $row['country_name']);

        $sqlCategory = $fn->getValueListSQL('companyCategory');
        $sqlStatus   = $fn->getValueListSQL('companyStatus');
        $sqlSupplier = $fn->getValueListSQL('supplierType');
        $sqlIndustry = $fn->getValueListSQL('companyIndustry');
        $sqlSize     = $fn->getValueListSQL('companySize');
        $sqlSource   = $fn->getValueListSQL('companySource');

        $fieldset1 = "
        {$formObj->getTBRow('Company Name', 'title', $row['title'])}
        {$formObj->getTBRow('Business Reg. No', 'reg_number', $row['reg_number'])}
        {$formObj->getDDRowBySQL('Type of Reg.', 'category', $sqlCategory, $row['category'], $expVl)}
        {$formObj->getTBRow('Phone', 'phone', $row['phone'])}
        {$formObj->getTBRow('Fax', 'fax', $row['fax'])}
        {$formObj->getTBRow('Email', 'email', $row['email'])}
        ";

		if ($row['address_country_code'] == ''){
			$country = 'SG';
		} else {
			$country = $row['address_country_code'];
		}

        $fieldset2 = "
        {$formObj->getTBRow('Address 1', 'address1', $row['address1'])}
        {$formObj->getTBRow('Address 2', 'address2', $row['address2'])}
        {$formObj->getTBRow('City / Town', 'address_city', $row['address_city'])}
        {$formObj->getDDRowBySQL('Country', 'address_country_code', $sqlCountry, $country)}
        {$formObj->getTBRow('Postal Code', 'address_po_code', $row['address_po_code'])}
        ";

        $fieldset3 = "
        {$formObj->getTBRow('Contact Person Name', 'contact_name', $row['contact_name'])}
        {$formObj->getTBRow('Phone', 'contact_phone', $row['contact_phone'])}
        {$formObj->getTBRow('Mobile', 'contact_mobile', $row['contact_mobile'])}
        {$formObj->getTBRow('Email', 'contact_email', $row['contact_email'])}
        {$formObj->getTBRow('Department & Designation', 'contact_position', $row['contact_position'])}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Company Details', $fieldset1)}
        {$formObj->getFieldSetWrapped('Address Details', $fieldset2)}
        {$formObj->getFieldSetWrapped('Authorized Representative', $fieldset3)}
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
                <option value=''>Special Search</option>
                {$cpUtil->getDropDown1($spArray, $tv['special_search'])}
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
        $_SESSION['selectedContactIds'] = '';

        $rows = "";
        $links= "";

        $rows .= $this->getCourseContactDisplay($row);
        $record_id = $fn->getIssetParam($row, 'company_id');

        $text = "
        {$displayLinkData->getLinkPortalMain('aceIms_company', 'aceIms_contactLink', 'Contacts Linked', $row)}
        {$rows}
        ";

        return $text;
    }

    /**
     *
     */
    function getCourseContactDisplay($row){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $formObj = Zend_Registry::get('formObj');
        $dateUtil = Zend_Registry::get('dateUtil');

        $rows = "";
        $links= "";

        $SQL = "
        SELECT DISTINCT cc.course_contact_id
              ,cc.evaluate_status
              ,cc.course_status
              ,c.title AS course_title
              ,b.title AS batch_title
              ,sd.title AS subsidy_title
              ,sdis.title AS discount_title
              ,cont.first_name AS contact_name
              ,cont.id_card_no AS contact_id_card_no
              ,o.order_date
              ,o.order_id
        FROM course_contact cc
        LEFT JOIN course c ON (c.course_id = cc.course_id)
        LEFT JOIN contact cont ON (cont.contact_id = cc.contact_id)
        LEFT JOIN `order` o ON (o.order_id = cc.order_id)
        LEFT JOIN batch b ON (b.batch_id = cc.batch_id)
        LEFT JOIN (subsidy_discount sd) ON (cc.subsidy_discount_id = sd.subsidy_discount_id AND sd.category_type = 'Subsidy')
        LEFT JOIN (subsidy_discount sdis) ON (cc.subsidy_discount_id = sdis.subsidy_discount_id and sdis.category_type = 'Discount')
        WHERE cc.company_id = {$row['company_id']}
        ORDER BY o.order_date DESC, o.order_id DESC
        ";
        $result   = $db->sql_query($SQL);
        $order_id = '';
        $exp = array('isEditable' => 0);

        while ($rowCC = $db->sql_fetchrow($result)) {
            if ($order_id != $rowCC['order_id']){
                $order_date = $dateUtil->formatDate($rowCC['order_date'], 'DD-MM-YYYY');
                $printUrl    = "index.php?module=aceIms_company&_spAction=printVoucher&id={$row['company_id']}&order_id={$rowCC['order_id']}&showHTML=0";
                $orderUrl = "/admin/index.php?_topRm=finance&module=aceIms_order&_action=detail&order_id={$rowCC['order_id']}";
                $editurl = "index.php?_topRm=main&_spAction=courseTraineeSearch&module=aceIms_orderLink&srcRoom=aceIms_company&lnkRoom=aceIms_orderLink&company_id={$row['company_id']}&order_id={$rowCC['order_id']}&showHTML=0
                ";

                $SQLOI ="
                SELECT SUM(unit_price) AS fees
                FROM order_item
                WHERE order_id = {$rowCC['order_id']}
                  AND module = 'aceIms_subject'
                ";
                $resultOI = $db->sql_query($SQLOI);
                $rowOI = $db->sql_fetchrow($resultOI);

                $rows .= "
                <tr style='background-color: #F4F4F4; color:#000000;'>
                    <td style='background-color: #98C2E2;'>
                        <div class='w100 txtCenter'>
                            <a href= {$orderUrl} target='_blank' style='color:#000000;'>
                                 <u>Go to Finance <br/> {$order_date}</u>
                            </a>
                        </div>
                    </td>
                    <td colspan = '2'>Course Fees: {$rowOI['fees']}</td>
                    <td colspan = '2'></td>
                    <td class='portalActBtns'>
                        <div style='float:right'>
                            <a class='editPortalRecordCompany' h='350' w='650' recid={$rowCC['order_id']} dialogtitle='Edit OrderLink' link='{$editurl}' href='javascript:void(0);'>
                                <img border='0' title='Edit Record' src='/cmspilotv30/CP/admin/images/icons/btn_edit.png'>
                            </a>
                            <!--<a class='deletePortalRecord' srcroomid={$row['company_id']} link='/admin/index.php?_spAction=deletePortalRecordByID&srcRoom=aceIms_company&lnkRoom=aceIms_orderLink&id={$rowCC['order_id']}&showHTML=0' href='javascript:void(0);'>-->
                            <a class='deletePortalRecordCompany' order_id={$rowCC['order_id']} srcroomid={$row['company_id']} href='#'>
                                <img border='0'  style='margin: 0px 3px 3px 3px;' title='Delete Record' src='/cmspilotv30/CP/admin/images/icons/btn_remove.png'>
                            </a>
                        </div>
                    </td>
                </tr>
                ";
            }

            $SQLSubject = "
            SELECT s.title
            FROM subject s
            LEFT JOIN course_contact_subject_history ccsh ON (ccsh.course_contact_id = {$rowCC['course_contact_id']})
            WHERE s.subject_id = ccsh.subject_id
            ";
            $resultSubject   = $db->sql_query($SQLSubject);
            $subjectTitle = '';
            while ($rowSubject = $db->sql_fetchrow($resultSubject)) {
                $subjectTitle .= '- '.$rowSubject['title']."<br>";
            }
            $divider = '';

            if($rowCC['subsidy_title'] !='' && $rowCC['discount_title'] !=''){
                $divider = '/';
            }
            $rows .= "
            <tr>
                <td>{$rowCC['contact_name']} {$rowCC['contact_id_card_no']}</td>
                <td>{$rowCC['course_title']}</td>
                <td>{$rowCC['batch_title']}</td>
                <td class='subject'>{$subjectTitle}</td>
                <td>{$rowCC['subsidy_title']}{$divider}{$rowCC['discount_title']}</td>
                <td>{$rowCC['evaluate_status']}</td>
            </tr>
            ";
            $order_id =  $rowCC['order_id'];
        }

        $header ="
        <th>Contact</th>
        <th>Course</th>
        <th>Batch</th>
        <th>Subject</th>
        <th>Subsidy/Discount</th>
        <th>Status</th>
        ";

        $text = "
        <div class='linkPortalWrapper aceIms_company__aceIms_orderLink' id='aceIms_company#aceIms_orderLink'>
            <div expanded='1' class='header'>
                <div class='linkPortalDataWrapper'>
                    <div class='actBtns'>
                        <a id='bulkCompanyCourseLink' class='' dialogtitle='Bulk Company Trainee Link' href='index.php?_topRm=main&_spAction=courseTraineeSearch&module=aceIms_orderLink&srcRoom=aceIms_company&lnkRoom=aceIms_orderLink&company_id={$row['company_id']}&showHTML=0';'>
                            <u>Click here for Enrollment</u>
                        </a>
                    </div>
                    <table class='thinlist'>
                        {$header}
                        {$rows}
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
    function getCourseTraineePortal($row){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        $text = "
        <div class='linkPortalWrapper aceIms_company__aceIms_orderLink' >
            <div expanded='1' class='header'>
                <div class='linkPortalDataWrapper'>
                    <div class='actBtns'>
                        <a id='bulkCompanyCourseLink' class='' dialogtitle='Bulk Company Trainee Link' href='index.php?_topRm=main&_spAction=courseTraineeSearch&module=aceIms_orderLink&srcRoom=aceIms_company&lnkRoom=aceIms_orderLink&srcRoomId={$row['company_id']}&showHTML=0';'>
                            Add
                        </a>
                    </div>
                </div>
            </div>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getCourseTraineePortalOrgnl($row){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        $text = "
        <div class='linkPortalWrapper aceIms_company__aceIms_orderLink' id='aceIms_company#aceIms_orderLink'>
            <div expanded='1' class='header'>
                <div class='linkPortalDataWrapper'>
                    <div class='actBtns'>
                        <a class='newPortalRecord' h='525' w='1100' dialogtitle='Bulk Company Trainee Link' link='/admin/index.php?_spAction=courseTraineeSearch&srcRoom=aceIms_company&lnkRoom=aceIms_orderLink&srcRoomId={$row['company_id']}&showHTML=0' href='javascript:void(0);'>
                            Add
                        </a>
                    </div>
                </div>
            </div>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getCompanyNew(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');

        $expVl = array('sqlType' => 'OneField');
        $sqlCategory = $fn->getValueListSQL('companyCategory');
        $sqlCountry = getCPModelObj('common_geoCountry')->getCountryDDSQL();

        $formAction ='index.php?module=aceIms_company&_spAction=companyAddSubmit&showHTML=0';
        $text = "
        <form name='portalForm' id='companyAddForm' class='yform columnar'
              method='post' action='{$formAction}'>
            {$formObj->getTBRow('Company Name', 'title')}
            {$formObj->getTBRow('Business Reg. No', 'reg_number')}
            {$formObj->getDDRowBySQL('Type of Reg.', 'category', $sqlCategory, '', $expVl)}
            {$formObj->getTBRow('Phone', 'phone')}
            {$formObj->getTBRow('Fax', 'fax')}
            {$formObj->getTBRow('Email', 'email')}

            {$formObj->getTBRow('Address 1', 'address1')}
            {$formObj->getTBRow('Address 2', 'address2')}
            {$formObj->getTBRow('City / Town', 'address_city')}
            {$formObj->getDDRowBySQL('Country', 'address_country_code', $sqlCountry)}
            {$formObj->getTBRow('Postal Code', 'address_po_code')}
            <input type='submit' name='x_submit' class='submithidden' />
        </form>
        ";

        return $text;
     }
}