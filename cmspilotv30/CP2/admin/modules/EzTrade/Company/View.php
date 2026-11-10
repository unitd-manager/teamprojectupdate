<?
class CP_Admin_Modules_EzTrade_Company_View extends CP_Common_Lib_ModuleViewAbstract
{
    /**
     *
     */
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');

        $count   = 0;
        $rows    = '';

        foreach ($dataArray as $row){
            $email   = $row['email'];
            $website = $row['website'];

            $email = $row['email'];
            $emailText = "<a href='mailto:{$email}'>{$email}</a></div></td>";
            $rows .= "
            {$listObj->getListRowHeader($row, $count)}
            {$listObj->getGoToDetailText($count, $row['company_code'])}
            {$listObj->getGoToDetailText($count, $row['company_name'])}
            {$listObj->getListDataCell($row['category'])}
            {$listObj->getListDataCell($row['party_type'])}
            {$listObj->getListDataCell($row['address_country'])}
            {$listObj->getListDataCell($row['region_name'])}
            {$listObj->getPhoneFieldValue($row['phone_country_code'], $row['phone_area_code'], $row['phone'])}
            {$listObj->getListDataCell($emailText)}
            {$listObj->getListDataCell($row['status'])}
            {$listObj->getListRowEnd($row['company_id'])}
            ";
            $count++;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Company Number', 'c.company_code')}
        {$listObj->getListHeaderCell('Company Name', 'c.company_name')}
        {$listObj->getListHeaderCell('Party', 'c.category')}
        {$listObj->getListHeaderCell('Party Type', 'c.party_type')}
        {$listObj->getListHeaderCell('Country', 'c.country_name')}
        {$listObj->getListHeaderCell('Region', 'r.region_name')}
        {$listObj->getListHeaderCell('Telephone', 'c.phone')}
        {$listObj->getListHeaderCell('General Email', 'c.email')}
        {$listObj->getListHeaderCell('Status', 'c.status')}
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
        $cpCfg = Zend_Registry::get('cpCfg');

        $modCategory = getCPModuleObj('webBasic_category');

        $fielset1 = "
        {$formObj->getTBRow('Company Name', 'company_name')}
        {$formObj->getDDRowByArr('Party', 'category', $cpCfg['m.trading.company.partyArr'])}
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
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');

        $supplierClientText = '';

        $expVl = array('sqlType' => 'OneField');
        $sqlPaymentTerms  = $fn->getValueListSQL('paymentTerms');
        $sqlDeliveryTerms = $fn->getValueListSQL('deliveryTerms');
        $sqlCurrency      = $fn->getValueListSQL('currency');
        $sqlPartyType     = $fn->getValueListSQL('partyType');

        $sqlRegion  = "
        SELECT r.region_id
              ,r.region_name
        FROM region r
        ORDER BY r.region_name
        ";

        $sqlPricingType  = "
        SELECT pt.pricing_type_id
              ,pt.pricing_type
        FROM pricing_type pt
        ORDER BY pt.pricing_type
        ";

        $sectionType    = 'Product';
        $modCategory    = getCPModuleObj('webBasic_category');
        $sqlCategory    = $modCategory->model->getCategorySQLByType($sectionType);
        $expCategory    = array('detailValue' => $row['category_title']);

        $modSubCategory = getCPModuleObj('webBasic_subCategory');
        $sqlSubCategory = $modSubCategory->model->getSubCategorySQLByCategory($row['category_id']);
        $expSubCategory = array('detailValue' => $row['sub_category_title']);

        $expNoEdit = array('isEditable' => 0);

        if ($row['category'] == 'Customer') {
            $supplierClientText .= "
            {$formObj->getDDRowBySQL('Sell Currency', 'sell_currency', $sqlCurrency, $row['sell_currency'], $expVl)}
            ";

        } else if ($row['category'] == 'Supplier') {
            $supplierClientText .= "
            {$formObj->getDDRowBySQL('Buy Currency', 'buy_currency', $sqlCurrency, $row['buy_currency'], $expVl)}
            ";
        }

        $sqlCountry = getCPModelObj('common_geoCountry')->getCountryDDSQL();
        $countryArr = $dbUtil->getArrayFromSQLForVL($sqlCountry);
        $expCountry = array('detailValue' => $row['address_country'], 'useKey' => 0);

        $fielset1 = "
        {$formObj->getTBRow('Company Code', 'comp_code', $row['company_code'], $expNoEdit)}
        {$formObj->getTBRow('Party', 'category', $row['category'], $expNoEdit)}
        {$formObj->getDDRowByArr('Status', 'status', $cpCfg['m.trading.company.statusArr'], $row['status'])}
        {$formObj->getDDRowBySQL('Party Type', 'party_type', $sqlPartyType, $row['party_type'], $expVl)}
        {$formObj->getDDRowBySQL('Type of Pricing', 'pricing_type_id', $sqlPricingType, $row['pricing_type_id'])}
        {$formObj->getTBRow('Company Name', 'company_name', $row['company_name'], $expNoEdit)}
        {$formObj->getTBRow('Company Name (Chinese)', 'chi_company_name', $row['chi_company_name'])}
        {$formObj->getTBRow('Website', 'website', $row['website'])}
        {$formObj->getPhoneNoRow2('Main Phone', 'phone_country_code', 'phone_area_code', 'phone',
                                  $row['phone_country_code'], $row['phone_area_code'], $row['phone'])}
        {$formObj->getPhoneNoRow2('Main Fax', 'fax_country_code', 'fax_area_code', 'fax',
                                  $row['fax_country_code'], $row['fax_area_code'], $row['fax'])}
        {$formObj->getTBRow('General Email', 'email', $row['email'])}
        {$formObj->getTBRow('Address Line 1', 'address_flat', $row['address_flat'])}
        {$formObj->getTBRow('Address Line 2', 'address_street', $row['address_street'])}
        {$formObj->getTBRow('Town/City', 'address_town', $row['address_town'])}
        {$formObj->getTBRow('State/County', 'address_state', $row['address_state'])}
        {$formObj->getTBRow('Post Code/Zip', 'address_po_code', $row['address_po_code'])}
        {$formObj->getDropDownRowByArray('Country', 'address_country', $countryArr, $row['address_country'], $expCountry)}
        {$formObj->getTBRow('Port', 'port', $row['port'])}
        {$formObj->getDDRowBySQL('Region', 'region_id', $sqlRegion, $row['region_id'])}
        {$supplierClientText}
        ";

        $fielset2 = "
        {$formObj->getTBRow('Consignee Contact Person','consignee_contact_person', $row['consignee_contact_person'])}
        {$formObj->getTBRow('Consignee Name','consignee_name', $row['consignee_name'])}
        {$formObj->getTBRow('Consignee Address','consignee_address', $row['consignee_address'])}
        {$formObj->getPhoneNoRow2('Consignee Phone', 'consignee_phone_country_code', 'consignee_phone_area_code', 'consignee_phone',
                                  $row['consignee_phone_country_code'], $row['consignee_phone_area_code'], $row['consignee_phone'])}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Company Details', $fielset1)}
        {$formObj->getFieldSetWrapped('Consignee', $fielset2)}
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
    function getRightPanel($row){
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $fn = Zend_Registry::get('fn');
        $comment = getCPPluginObj('common_comment');

        $links   = '';

        $record_id = $fn->getIssetParam($row, 'company_id');

        $links = "
        {$displayLinkData->getLinkPortalMain('ezTrade_company', 'ezTrade_contactLink', 'Contacts Linked', $row)}
        {$displayLinkData->getLinkPortalMain('ezTrade_company', 'ezTrade_deliveryAddressLink', 'Delivery Address', $row)}
        {$displayLinkData->getLinkPortalMain('ezTrade_company', 'ezTrade_deliveryTermsLink', 'Delivery Terms', $row)}
        {$displayLinkData->getLinkPortalMain('ezTrade_company', 'ezTrade_paymentTermsLink', 'Payment Terms', $row)}
        ";

        $text = "
        {$links}
        {$comment->getView(array(
             'roomName' => 'ezTrade_company'
            ,'recordId' => $record_id
        ))}
        ";

        return $text;
    }

    /**
     *
     */
    function getQuickSearch() {
        $cpUtil = Zend_Registry::get('cpUtil');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $status   = $fn->getReqParam('status');
        $category = $fn->getReqParam('category');
        $region_id   = $fn->getReqParam('region_id');

        $spArray = array(
            "Flagged"
           ,"Not-Flagged"
        );

        $sqlRegion = "
        SELECT region_id
              ,region_name
        FROM region
        ";

        $text = "
        <td>
            <select name='category'>
                <option value=''>Party</option>
                {$cpUtil->getDropDown1($cpCfg['m.trading.company.partyArr'], $category)}
            </select>
        </td>
        <td>
            <select name='region_id'>
                <option value=''>Region</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlRegion, $region_id)}
            </select>
        </td>
        <td>
            <select name='status' >
                <option value=''>Status</option>
                {$cpUtil->getDropDown1($cpCfg['m.trading.company.statusArr'], $status)}
            </select>
        </td>
        <td>
            <select class='w125' name='special_search'>
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
    function getExportData(){
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $result = Zend_Registry::get('result');
        $cpUtil = Zend_Registry::get('cpUtil');

        set_time_limit(50000);
        ini_set('memory_limit', '512M');


        require_once("PHPExcel.php");
        include "PHPExcel/IOFactory.php";

        $file_name = "Company_" . date("d-m-Y") . ".xls";

        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");;
        header("Content-Disposition: attachment;filename={$file_name}");
        header("Content-Transfer-Encoding: binary ");

        $objPHPExcel = new PHPExcel();

        //--------------------------------------------------//
        $rowc = 1;
        $colc = 0;

        $actSheet = &$objPHPExcel->getActiveSheet();

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Company Number');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Company Name');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Company Name (Chinese)');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Website');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Main Phone');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Main Fax');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'General Email');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Address Line 1');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Address Line 2');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'City');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'State');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Post Code/Zip');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Country');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Company Address (Chinese)');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Consignee Name');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Consignee Address');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Consignee Phone');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Consignee Contact Person');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Status');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Party');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Buy Currency');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Sell Currency');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Delivery Terms');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Payment Terms');

        /******************** FORMAT HEADER *******************/
        $headStyle = array(
            'font' => array( 'bold' => true)
        );

        $lastCol    = $actSheet->getHighestColumn();
        $lastColInd = PHPExcel_Cell::columnIndexFromString($lastCol);
        $actSheet->getStyle("A1:{$lastCol}1")->applyFromArray($headStyle);

        for ($i=0; $i < $lastColInd; $i++){
            $colAlphabet = PHPExcel_Cell::stringFromColumnIndex($i);
            $actSheet->getColumnDimension($colAlphabet)->setAutoSize(true);
        }


        //============================================================================= //
        $fnsModGrp = includeCPClass('ModGroup', 'EzTrade', 'Functions');

        while ($row = $db->sql_fetchrow($result)) {
            $colc = 0;
            $rowc++;

            $fax   = $row['fax_country_code']   != '' ? $row['fax_country_code']   . ' - ' . $row['fax']   : $row['fax'];

            $phone = $fnsModGrp->getFormattedPhoneField(
                        $row['phone_country_code'],
                        $row['phone_area_code'],
                        $row['phone']
                     );
            $fax = $fnsModGrp->getFormattedPhoneField(
                        $row['fax_country_code'],
                        $row['fax_area_code'],
                        $row['fax']
                   );
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['company_code']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['company_name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['chi_company_name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['website']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $phone);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $fax);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['email']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['address_flat']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['address_street']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['address_town']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['address_state']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['address_po_code']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['address_country']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['chi_company_address']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['consignee_name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['consignee_address']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['consignee_phone']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['consignee_contact_person']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['status']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['category']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['buy_currency']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['sell_currency']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['delivery_terms']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['payment_terms']);
        }

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }
}