<?
class CP_Admin_Modules_Labsg_Contact_View extends CP_Common_Lib_ModuleViewAbstract
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
            $exp = array('displayText' => $row['c_company_name']);
            $company = $fn->getRecordDetailLink('trading_company', 'record_id', $row['company_id'], $exp);
            $rows .= "
            {$listObj->getListRowHeader($row, $count)}
            {$listObj->getListDataCell($row['salutation'])}
            {$listObj->getGoToDetailText($count, $row['first_name'])}
            {$listObj->getListDataCell("<a href='mailto:{$row['email']}'>{$row['email']}</a>")}
            {$listObj->getListDataCell($company)}
            {$listObj->getListDataCell($row['phone_full'])}
            {$listObj->getListDataCell($row['mobile_full'])}
            {$listObj->getListDataCell($row['position'])}
            {$listObj->getListRowEnd($row['contact_id'])}
            ";
            $count++;
        }
        $rows = $listObj->getDisplayListRows($rows);

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Title', 'salutation')}
        {$listObj->getListHeaderCell('Name', 'contact_name')}
        {$listObj->getListHeaderCell('Email', 'c.email')}
        {$listObj->getListHeaderCell('Company Name', 'com.company_name')}
        {$listObj->getListHeaderCell('Phone', 'c.phone')}
        {$listObj->getListHeaderCell('Mobile', 'mobile')}
        {$listObj->getListHeaderCell('Position', 'position')}
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
        {$formObj->getTBRow('Name', 'first_name')}
        {$formObj->getTBRow('Email', 'email')}
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
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $expComp = array('displayText' => $row['c_company_name']);
        $sqlComp = $fn->getDDSql('labsg_company');
        $companyText = $fn->getRecordDetailLink('labsg_company', 'record_id',
                            $row['company_id'], $expComp);
        $expCompDisp = array('detailValue' => $companyText,);

        $expVl = array('sqlType' => 'OneField');
        $sqlCategory = $fn->getValueListSQL('contactCategory');
        $sqlTitle    = $fn->getValueListSQL('contactTitle');

        $fieldset1 = "
        {$formObj->getDDRowBySQL('Title', 'salutation', $sqlTitle, $row['salutation'], $expVl)}
        {$formObj->getTextBoxRow('Name', 'first_name', $row['first_name'])}
        {$formObj->getTextBoxRow('Email', 'email', $row['email'])}
        {$formObj->getTextBoxRow('Phone (Direct)', 'phone', $row['phone'])}
        {$formObj->getTextBoxRow('Fax', 'fax', $row['fax'])}
        {$formObj->getTextBoxRow('Mobile', 'mobile', $row['mobile'])}
        {$formObj->getYesNoRRow('Published', 'published', $row['published'])}
        ";


        $fieldset2 = "
        {$formObj->getDDRowBySQL('Company Name', 'company_id', $sqlComp,
                                 $row['company_id'], $expCompDisp)}
        {$formObj->getTBRow('Position', 'position', $row['position'])}
        {$formObj->getTBRow('Department', 'department', $row['department'])}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Contact Details', $fieldset1)}
        {$formObj->getFieldSetWrapped('Company Details', $fieldset2)}
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

        $record_id = $fn->getIssetParam($row, 'contact_id');

        $text = "
        {$media->getRightPanelMediaDisplay('Attachments', 'labsg_contact', 'attachment', $row)}
        {$comment->getView(array(
             'roomName' => 'labsg_contact'
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
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $special_search = $fn->getReqParam('special_search');
        $company_id     = $fn->getReqParam('company_id');

        //==================================================================//
        $spArray = array(
              "Subscribed"
             ,"Not-Subscribed"
             ,"Flagged"
             ,"Not-Flagged"
        );

        $sqlCompany = $fn->getDDSql('trading_company');

        $text = "
        <td>
            <select name='company_id'>
                <option value=''>Company Name</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlCompany, $company_id)}
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