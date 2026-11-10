<?
class CP_Admin_Modules_Common_LoginHistory_View extends CP_Common_Lib_ModuleViewAbstract
{
    function getList($dataArray) {
        $listObj = Zend_Registry::get('listObj');
        $fn = Zend_Registry::get('fn');

        $rowCounter = 0;
        $rows       = "";

        foreach ($dataArray as $row){
            $expListRowHeader = array(
                     'hasCheckboxInList' => true
                    ,'hasEditInList' => false
            );
            $expListRowHeader = array();

            $rows .="
            {$listObj->getListRowHeader($row, $rowCounter, '', $expListRowHeader)}
            {$listObj->getGoToDetailText($rowCounter, $row['contact_name'])}
            {$listObj->getListDataCell($row['contact_email'])}
            {$listObj->getListDataCell($row['login_date'])}
            {$listObj->getListDataCell($row['login_time'])}
            {$listObj->getListDataCell($row['ip_number'])}
            {$listObj->getListDataCell($fn->getYesNo($row['active']), 'center')}
            {$listObj->getListRowEnd($row['login_history_id'])}
            ";
            $rowCounter++;
        }

        $text = "
    	{$listObj->getListHeader()}
    	{$listObj->getListHeaderCell('Contact Name', 'contact_name')}
    	{$listObj->getListHeaderCell('Contact Email', 'contact_email')}
    	{$listObj->getListHeaderCell('Login Date', 'login_date')}
    	{$listObj->getListHeaderCell('Login Time', 'login_time')}
    	{$listObj->getListHeaderCell('IP Address', 'ip_number')}
    	{$listObj->getListHeaderCell('Active', 'active', 'headerCenter')}
    	{$listObj->getListHeaderEnd()}
        {$rows}
        {$listObj->getListFooter()}
        ";

        return $text;
    }


    /**
     *
     */
    function getEdit($row) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $formObj = Zend_Registry::get('formObj');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');

        $formObj->mode = $tv['action'];

        $exp = array('isEditable' => false);

        $fieldset1 = "
        {$formObj->getTBRow('Contact Name', 'contact_name', $row['contact_name'], $exp)}
        {$formObj->getTBRow('Contact Email', 'contact_email', $row['contact_email'], $exp)}
        {$formObj->getTBRow('Login Date', 'login_date', $row['login_date'], $exp)}
        {$formObj->getTBRow('Login Time', 'login_time', $row['login_time'], $exp)}
        {$formObj->getTBRow('IP Address', 'ip_number', $row['ip_number'], $exp)}
        {$formObj->getYesNoRRow('Active', 'active', $row['active'])}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Details', $fieldset1)}
        ";

        return $text;
    }

    /**
     *
     */
    function getQuickSearch() {
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $cpUtil = Zend_Registry::get('cpUtil');
        $formObj = Zend_Registry::get('formObj');
        $cpCfg = Zend_Registry::get('cpCfg');

        $creation_date1 = $fn->getReqParam('creation_date1');
        $creation_date2 = $fn->getReqParam('creation_date2');
        $contact_id     = $fn->getReqParam('contact_id');

        $contactSQL = $fn->getDDSql('common_contact');

        $special_search = ($tv['searchDone'] == 0) ? 'Active' : $tv['special_search'];

        $text = "
        <td class='dateRange'>
            Creation Date:
            <input type='text' allowEdit='1' name='creation_date1' class='fld_date' id='fld_creation_date1' value='{$creation_date1}' />
            <input type='text' allowEdit='1' name='creation_date2' class='fld_date' id='fld_creation_date2' value='{$creation_date2}' />
        </td>
        <td>
            {$formObj->getDropDownBySQL('Contact', 'contact_id', $contactSQL, $contact_id)}
        </td>
        <td>
            <select name='special_search'>
                <option value=''>Special Search</option>
                {$cpUtil->getDropDown1($cpCfg['m.lawNews.loginHistory.specialSearchArr'], $special_search)}
            </select>
        </td>
        ";

        return $text;
    }
}
