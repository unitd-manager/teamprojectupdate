<?
class CP_Admin_Modules_Project_Contact_View extends CP_Common_Lib_ModuleViewAbstract
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
            $company = "<a href='index.php?_topRm=project&module=project_company&_action=edit&company_id={$row['company_id']}'>{$row['c_company_name']}</a>";
            $rows .= "
            {$listObj->getListRowHeader($row, $count)}
            {$listObj->getGoToDetailText($count, $row['first_name'])}
            {$listObj->getGoToDetailText($count, $row['last_name'])}
            {$listObj->getListDataCell("<a href='mailto:{$row['email']}'>{$row['email']}</a>")}
            {$listObj->getListDataCell($company)}
            {$listObj->getListDataCell($row['c_phone'])}
            {$listObj->getListDataCell($row['phone_direct'])}
            {$listObj->getListDataCell($row['status'])}
            {$listObj->getListDataCell($fn->getYesNo($row['subscribe']), "center")}
            {$listObj->getListRowEnd($row['contact_id'])}
            ";

            $count++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('First Name', 'a.first_name')}
        {$listObj->getListHeaderCell('Last Name', 'a.last_name')}
        {$listObj->getListHeaderCell('Email', 'a.email')}
        {$listObj->getListHeaderCell('Company Name', 'b.company_name')}
        {$listObj->getListHeaderCell('Phone (Main)', 'b.phone')}
        {$listObj->getListHeaderCell('Phone (Direct)', 'a.phone_direct')}
        {$listObj->getListHeaderCell('Status', 'a.status')}
        {$listObj->getListHeaderCell('Subscribed', 'a.subscribe', 'headerCenter')}
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

        $fielset = "
        {$formObj->getTBRow('First Name', 'first_name')}
        {$formObj->getTBRow('Last Name', 'last_name')}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fielset)}
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
        $sqlComp        = $fn->getDDSql('project_company');
        
        if ($cpCfg['m.project.contact.showChineseFields'] == 1){
            $chineseName = $formObj->getTBRow('Name (Chinese)', 'chi_name', $row['chi_name']);
            $chinesePos  = $formObj->getTBRow('Position (Chinese)', 'chi_position', $row['chi_position']);
            $chineseDept = $formObj->getTBRow('Department (Chinese)', 'chi_department', $row['chi_department']);
        }

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

        if ($cpCfg['m.project.contact.showDetail'] == 1){
            $sqlCombo = "
            SELECT staff_id
                  ,CONCAT_WS(' ', a.first_name, a.last_name) AS staff_name
            FROM staff a
            ORDER BY staff_name";

            $fieldset = "
            {$formObj->getDDRowBySQL("{$cpCfg['m.project.staffFieldLabel']}", "staff_id", $sqlCombo, $row['staff_id'])}
            ";

            $staffDetail = $formObj->getFieldSetWrapped($cpCfg['m.project.staffFieldLabel'], $fieldset);
        }

        if ($cpCfg['m.project.contact.showPersonalAddress'] == 1){
            $fieldset = "
            {$formObj->getTBRow('Flat / Building', 'address_flat', $row['address_flat'])}
            {$formObj->getTBRow('Street Address', 'address_street', $row['address_street'])}
            {$formObj->getTBRow('District/ Town', 'address_town', $row['address_town'])}
            {$formObj->getTBRow('State/ Zip', 'address_state', $row['address_state'])}
            {$formObj->getTBRow('Country', 'address_country', $row['address_country'])}
            ";

            $personalAdd = $formObj->getFieldSetWrapped('Personal Address', $fieldset);
        }

        $expVl = array('sqlType' => 'OneField');

        $fielset1 = "
        {$formObj->getDDRowBySQL('Choose Category', 'category', $sqlCategory, $row['category'], $expVl)}
        {$formObj->getDDRowBySQL('Salutation', 'salutation', $sqlTitle, $row['salutation'], $expVl)}
        {$formObj->getTBRow('First Name', 'first_name', $row['first_name'])}
        {$formObj->getTBRow('Last Name', 'last_name', $row['last_name'])}
        {$chineseName}
        {$formObj->getTBRow('Email', 'email', $row['email'])}
        ";
        
        if ($tv['action'] == 'detail'){
            if($cpCfg['m.project.hasMultipleCompanyAddress'] == 1){
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
            $compLink = "<a class='editLinkSingle' href='' link='{$fn->getOpenLinkUrl('project_contact', 'project_companyLink', 'fld_company_id')}'>Choose</a>";
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
        {$formObj->getTBRow('Department', 'department', $row['department'])}
        {$chineseDept}
        ";
        
        $subscribed = ($tv['newRecord'] == 1) ? 1 : $row['subscribe'];
        $sqlStatus  = $fn->getValueListSQL('companyStatus');
        $expVl = array('sqlType' => 'OneField');

        $fielset3 = "
        {$formObj->getYesNoRRow('Newsletter Subscribed', 'subscribe', $subscribed)}
        {$formObj->getDDRowBySQL('Status', 'status', $sqlStatus, $row['status'], $expVl)}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Contact Details', $fielset1)}
        {$formObj->getFieldSetWrapped('Company Details', $fielset2)}
        {$staffDetail}
        {$personalAdd}
        {$formObj->getFieldSetWrapped('Other Details', $fielset3)}
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

        $sqlCompany = $fn->getDDSql('project_company');
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
        {$formObj->getFieldSetWrapped('Contact Details', $fielset1)}
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

        if( $cpCfg['m.project.contact.showInterest'] == "1"){
            $rows .= $displayLinkData->getLinkPortalMain("project_contact", "common_interestLink", "Interests Linked", $row);
        }
        
        if( $cpCfg['m.project.contact.showEvent'] == "1"){
            $rows .= $displayLinkData->getLinkPortalMain("project_contact", "event_eventLink", "Events Linked", $row);
        }

        $record_id = $fn->getIssetParam($row, 'contact_id');

        $text = "
        {$media->getRightPanelMediaDisplay("Picture", "project_contact", "picture", $row)}
        {$rows}
        {$comment->getView(array(
             'roomName' => 'project_contact'
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
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        $interest_id    = $fn->getReqParam('interest_id');
        $subscribe      = $fn->getReqParam('subscribe');
        $special_search = $fn->getReqParam('special_search');
        $company_id     = $fn->getReqParam('company_id');
        $category       = $fn->getReqParam('category');
        $status         = $fn->getReqParam('status');
        
        if ($tv['searchDone'] == 0){
            $status = 'Current';
        }
        
        //==================================================================//
        $companyText  = "";
        $categoryText = "";
        $interestText = "";
        
        $sqlCompany     = $fn->getDDSql('project_company');
        $SQLStatus      = $fn->getValueListSQL('companyStatus');
        $SQLCategory    = $fn->getValueListSQL('contactCategory');
        $sqlInterest    = $fn->getDDSql('common_interest');

        $companyText = "
        <td>
            <select name='company_id' >
                <option value=''>Company</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlCompany, $company_id)}
            </select>
        </td>
        ";
        
        if ($cpCfg['m.project.contact.showCategory'] == 1) {
            $categoryText = "
            <td>
                <select name='category'>
                    <option value=''>Category</option>
                    {$dbUtil->getDropDownFromSQLCols1($db, $sqlCombo, $category)}
                </select>
            </td>    
            ";
        }

        //==================================================================//
        if ($cpCfg['m.project.contact.showInterest'] == 1) {
            $interestText = "
            <td>
                <select name='interest_id' >
                    <option value=''>Interest Group</option>
                    {$dbUtil->getDropDownFromSQLCols2($db, $sqlInterest, $interest_id)}
                </select>
            </td>
            ";
        }

        //==================================================================//
        $spArray = array(
              "Subscribed"
             ,"Not-Subscribed"
             ,"Flagged"
             ,"Not-Flagged"
        );

        $text = "
        {$companyText}
        {$categoryText}
        {$interestText}
        <td>
            <select name='status' >
                <option value=''>Status</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $SQLStatus, $status)}
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
}