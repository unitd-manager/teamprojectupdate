<?
class CPL_Admin_Modules_EnggCrm_Contact_View extends CP_Admin_Modules_EnggCrm_Contact_View
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
            $company = "<a href='index.php?_topRm=project&module=enggCrm_company&_action=edit&company_id={$row['company_id']}'>{$row['c_company_name']}</a>";
            $rows .= "
            {$listObj->getListRowHeader($row, $count)}
            {$listObj->getGoToDetailText($count, $row['first_name'])}
            {$listObj->getListDataCell("<a href='mailto:{$row['email']}'>{$row['email']}</a>")}
            {$listObj->getListDataCell($company)}
            {$listObj->getListDataCell($row['c_phone'])}
            {$listObj->getListDataCell($row['phone_direct'])}
            {$listObj->getListRowEnd($row['contact_id'])}
            ";

            $count++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Name', 'a.first_name')}
        {$listObj->getListHeaderCell('Email', 'a.email')}
        {$listObj->getListHeaderCell('Company Name', 'b.company_name')}
        {$listObj->getListHeaderCell('Phone (Main)', 'b.phone')}
        {$listObj->getListHeaderCell('Phone (Direct)', 'a.phone_direct')}
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

        $sqlCategory    = $fn->getValueListSQL('contactCategory');
        $sqlTitle       = $fn->getValueListSQL('contactTitle');
        $sqlComp        = $fn->getDDSql('enggCrm_company');
        
        if ($cpCfg['m.enggCrm.contact.showChineseFields'] == 1){
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

        if ($cpCfg['m.enggCrm.contact.showDetail'] == 1){
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

        $fielset1 = "
        {$formObj->getDDRowBySQL('Salutation', 'salutation', $sqlTitle, $row['salutation'], $expVl)}
        {$formObj->getTBRow('Name *', 'first_name', $row['first_name'])}
        {$chineseName}
        {$formObj->getTBRow('Email', 'email', $row['email'])}
        ";
        
        if ($tv['action'] == 'detail'){
            if($cpCfg['m.enggCrm.hasMultipleCompanyAddress'] == 1){
                $companyAddress = "
                {$formObj->getTBRow('Flat / Building', 'comp_mul_address_flat', $row['comp_mul_address_flat'])}
                {$formObj->getTBRow('Street Address', 'comp_mul_address_street', $row['comp_mul_address_street'])}
                {$formObj->getTBRow('District/ Town', 'comp_mul_address_town', $row['comp_mul_address_town'])}
                {$formObj->getTBRow('State/ Zip', 'comp_mul_address_state', $row['comp_mul_address_state'])}
                {$formObj->getTBRow('Country', 'comp_mul_address_country', $row['comp_mul_address_country'])}
                ";
            } else {
                $companyAddress = "
                {$formObj->getTBRow('Main Phone', 'c_phone', $row['c_phone'])}
                {$formObj->getTBRow('Main Fax', 'c_fax', $row['c_fax'])}
                {$formObj->getTBRow('Flat/Apartment/House', 'c_address_flat', $row['c_address_flat'])}
                {$formObj->getTBRow('Street Address', 'c_address_street', $row['c_address_street'])}
                {$formObj->getTBRow('Town/ Suburb', 'c_address_town', $row['c_address_town'])}
                {$formObj->getTBRow('State', 'c_address_state', $row['c_address_state'])}
                {$formObj->getTBRow('Country', 'c_address_country', $row['c_address_country'])}
                ";
            }
        }

        if ($formObj->mode == 'edit'){
            $compLink = "<a class='editLinkSingle' href='' link='{$fn->getOpenLinkUrl('enggCrm_contact', 'enggCrm_companyLink', 'fld_company_id')}'>Choose</a>";
        }
        $expComp  = array('notesRight' => $compLink, 'detailValue' => $row['c_company_name']);

        $fielset2 = "
        {$formObj->getDDRowBySQL('Company Name', 'company_id', $sqlComp, $row['company_id'], $expComp)}
        {$formObj->getTBRow('Position', 'position', $row['position'])}
        {$formObj->getTBRow('Phone', 'phone_direct', $row['phone_direct'])}
        {$formObj->getTBRow('Fax', 'fax', $row['fax'])}
        {$formObj->getTBRow('Mobile', 'mobile', $row['mobile'])}
        {$compAddressDD}
        {$companyAddress}
        {$chinesePos}
        {$chineseDept}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Contact Details', $fielset1)}
        {$formObj->getFieldSetWrapped('Company Details', $fielset2)}
        {$staffDetail}
        {$formObj->getCreationModificationText($row)}
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

        if( $cpCfg['m.enggCrm.contact.showEvent'] == "1"){
            $rows .= $displayLinkData->getLinkPortalMain("enggCrm_contact", "event_eventLink", "Events Linked", $row);
        }

        $record_id = $fn->getIssetParam($row, 'contact_id');

        $text = "
        {$rows}
        {$comment->getView(array(
             'roomName' => 'enggCrm_contact'
            ,'recordId' => $record_id
        ))}
        ";

        return $text;
    }
}