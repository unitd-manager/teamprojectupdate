<?
class CP_Admin_Modules_AgileIms_Company_View extends CP_Common_Modules_AgileIms_Company_View
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

        $sqlCategory   = $fn->getValueListSQL('companyCategory');
        $sqlStatus     = $fn->getValueListSQL('companyStatus');
        $sqlSupplier   = $fn->getValueListSQL('supplierType');
        $sqlIndustry   = $fn->getValueListSQL('companyIndustry');
        $sqlSize       = $fn->getValueListSQL('companySize');
        $sqlSource     = $fn->getValueListSQL('companySource');
        $sqlSalutation = $fn->getValueListSQL('salutation');

        $fieldset1 = "
        {$formObj->getTBRow('Company Name', 'title', $row['title'])}
        {$formObj->getTBRow('Business Reg. No', 'reg_number', $row['reg_number'])}
        {$formObj->getDDRowBySQL('Type of Reg.', 'category', $sqlCategory, $row['category'], $expVl)}
        {$formObj->getDDRowBySQL('Status', 'status', $sqlStatus, $row['status'], $expVl)}
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
        {$formObj->getDDRowBySQL('Country', 'address_country_code', $sqlCountry, $country)}
        {$formObj->getTBRow('Postal Code', 'address_po_code', $row['address_po_code'])}
        ";

        $fieldset3 = "
        {$formObj->getDDRowBySQL('Salutation', 'salutation', $sqlSalutation, $row['salutation'], $expVl)}
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
        {$displayLinkData->getLinkPortalMain('agileIms_company', 'agileIms_contactLink', 'Contacts Linked', $row)}
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
        
        $rows = "";
        $links= "";

        $SQL = "
        SELECT DISTINCT cc.course_contact_id
              ,cc.discount
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
        LEFT JOIN (subsidy_discount sd) ON (cc.subsidy_discount_id = sd.subsidy_discount_id)
        LEFT JOIN (subsidy_discount sdis) ON (cc.subsidy_discount_id = sdis.subsidy_discount_id and sdis.category_type = 'Discount')
        WHERE cc.company_id = {$row['company_id']}
        ORDER BY o.order_date DESC, o.order_id
        ";
        $SQL = "
        SELECT DISTINCT cc.course_contact_id
              ,cc.discount
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
        ORDER BY o.order_date DESC, o.order_id
        ";
        $result   = $db->sql_query($SQL);  
        $order_id = '';
        $exp = array('isEditable' => 0);

        while ($rowCC = $db->sql_fetchrow($result)) {
            if ($order_id != $rowCC['order_id']){
                $printUrl    = "index.php?module=agileIms_company&_spAction=printVoucher&id={$row['company_id']}&order_id={$rowCC['order_id']}&showHTML=0";
                $orderUrl = "/admin/index.php?_topRm=finance&module=agileIms_order&_action=detail&order_id={$rowCC['order_id']}";
                $editurl = "index.php?_topRm=main&_spAction=courseTraineeSearch&module=agileIms_orderLink&srcRoom=agileIms_company&lnkRoom=agileIms_orderLink&company_id={$row['company_id']}&order_id={$rowCC['order_id']}&showHTML=0
                ";

                $editRow = '';
                $cancelRow = 'Enrollment Cancelled';
                $cancelledClass = 'highlightClass';
                if ($rowCC['course_status'] != 'Cancelled') {
                    $editRow = "
                    <a class='editCompanyEnrollment' id='editPortalRecord' h='350' w='650' recid={$rowCC['order_id']} dialogtitle='Edit OrderLink' link='{$editurl}' href='javascript:void(0);'>
                        <img border='0' title='Edit Record' src='/cmspilotv30/CP/admin/images/icons/btn_edit.png'>
                    </a>
                    ";

                    $cancelRow = "
                    <a class='cancelEnrollment' order_id={$rowCC['order_id']} href='javascript:void(0);'>
                        <u>Cancel Enrollment</u>
                    </a>
                    ";
                    $cancelledClass = '';
                }

                $printConfirmationurl = "index.php?module=agileIms_company&_spAction=printCourseConfirmation&id={$row['company_id']}&order_id={$rowCC['order_id']}&showHTML=0";
                $printConfirmation = "
                        <a id='printConfirmation' style='color:#000000;' href='{$printConfirmationurl}'><u>Print Course Confirmation</u></a>
                    ";

                $rows .= "
                <tr style='background-color: #DFDFDF; color:#000000;'>
                    <td colspan='2'>Enrollment Date: {$fn->getCPDate($rowCC['order_date'], 'd M Y')}</td>
                    <td>
                        <div class='w100'>
                            <a href= {$orderUrl} style='color:#000000;'><u>Go to Finance</u></a>
                        </div>
                    </td>
                    <td>{$printConfirmation}</td>
                    <td>{$editRow}</td>
                    <td class='{$cancelledClass}'>{$cancelRow}</td>
                </tr>
                ";
            }

            $rows .= "
            <tr>
                <td>{$rowCC['contact_name']} {$rowCC['contact_id_card_no']}</td>
                <td>{$rowCC['course_title']}</td>
                <td>{$rowCC['batch_title']}</td>
                <td>{$rowCC['subsidy_title']}</td>
                <td>{$rowCC['discount_title']}</td>
                <td>{$rowCC['evaluate_status']}</td>
            </tr>
            ";
            
            $order_id =  $rowCC['order_id'];
        }
        
        $text = "
        <div class='header txtCenter'>
            <h3 class='button'>
                <a id='bulkCompanyCourseLink' class='' dialogtitle='Bulk Company Trainee Link' href='index.php?_topRm=main&_spAction=courseTraineeSearch&module=agileIms_orderLink&srcRoom=agileIms_company&lnkRoom=agileIms_orderLink&company_id={$row['company_id']}&showHTML=0';'> 
                    Click for Registration/Enrollment
                </a>
            </h3>
        </div>
        <table class='thinlist enrollmentTable'>
            <tr class='header'>
                <th>Contact</th>
                <th>Course</th>
                <th>Batch</th>
                <th>Subsidy</th>
                <th>Discount</th>
                <th>Status</th>
            </tr>
            {$rows}
        </table>
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
        <div class='linkPortalWrapper agileIms_company__agileIms_orderLink' >
            <div expanded='1' class='header'>
                <div class='linkPortalDataWrapper'>
                    <div class='actBtns'>
                        <a id='bulkCompanyCourseLink' class='' dialogtitle='Bulk Company Trainee Link' href='index.php?_topRm=main&_spAction=courseTraineeSearch&module=agileIms_orderLink&srcRoom=agileIms_company&lnkRoom=agileIms_orderLink&srcRoomId={$row['company_id']}&showHTML=0';'> 
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
        <div class='linkPortalWrapper agileIms_company__agileIms_orderLink' id='agileIms_company#agileIms_orderLink'>
            <div expanded='1' class='header'>
                <div class='linkPortalDataWrapper'>
                    <div class='actBtns'>
                        <a class='newPortalRecord' h='525' w='1100' dialogtitle='Bulk Company Trainee Link' link='/admin/index.php?_spAction=courseTraineeSearch&srcRoom=agileIms_company&lnkRoom=agileIms_orderLink&srcRoomId={$row['company_id']}&showHTML=0' href='javascript:void(0);'> 
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

        $formAction ='index.php?module=agileIms_company&_spAction=companyAddSubmit&showHTML=0';
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
            {$formObj->getDDRowBySQL('Country', 'address_country_code', $sqlCountry)}
            {$formObj->getTBRow('Postal Code', 'address_po_code')}
            <input type='submit' name='x_submit' class='submithidden' />
        </form>
        ";
        
        return $text;
     }     

    /**
     *
     */
    function getCompanyDetailsForContactJson() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpUtil = Zend_Registry::get('cpUtil');

        $company_id = $fn->getReqParam('company_id');

        $SQL = "
        SELECT c.contact_name
              ,c.phone
              ,c.fax
              ,c.address1
              ,c.address2
              ,c.address_po_code
              ,gc.name
        FROM company c
        LEFT JOIN (geo_country gc) ON (c.address_country_code = gc.country_code)
        WHERE c.company_id = {$company_id}
        ";
        $result = $db->sql_query($SQL);
        $row    = $db->sql_fetchrow($result);

        $arr['contact_name']    = $row['contact_name'];
        $arr['phone']           = $row['phone'];
        $arr['fax']             = $row['fax'];
        $arr['address1']        = $row['address1'];
        $arr['address2']        = $row['address2'];
        $arr['address_po_code'] = $row['address_po_code'];
        $arr['address_country'] = $row['name'];

        return $cpUtil->getJsonFromArray($arr);
    }
}