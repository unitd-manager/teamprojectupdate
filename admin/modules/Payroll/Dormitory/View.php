<?  
class CPL_Admin_Modules_Payroll_Dormitory_View extends CP_Common_Lib_ModuleViewAbstract
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
            $rows .= "
            {$listObj->getListRowHeader($row, $count)}
            {$listObj->getListDataCell($row['name'])}
            {$listObj->getListDataCell($row['phone'])}
            {$listObj->getListDataCell($row['address1'])}
            {$listObj->getListDataCell($row['address2'])}
            {$listObj->getListDataCell($row['country'])}
            {$listObj->getListRowEnd($row['dormitory_id'])}
            ";

            $count++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Name', 'd.name')}
        {$listObj->getListHeaderCell('Phone', 'd.phone')}
        {$listObj->getListHeaderCell('Address 1', 'd.address1')}
        {$listObj->getListHeaderCell('Address 2', 'd.address2')}
        {$listObj->getListHeaderCell('Country', 'd.country')}
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

        $fieldset = "
        <div class='noteHighlight'>Please note * indicates mandatory fields</div>
        {$formObj->getTBRow('Name *', 'name')}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fieldset)}
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

        $expNoEdit  = array('isEditable' => 0);
        $expVl = array('sqlType' => 'OneField');
        $sqlCountry = getCPModelObj('common_geoCountry')->getCountryDDSQL();
        $expCountry = array('detailValue' => $row['country_name']);

        $text = "
        <div class='linkPortalWrapper'>
            <div expanded='1' class='header'>
                <div class='floatbox'>
                    <div class='float_left'>Main Details</div>
                    <div class='toggle'></div>
                </div>
            </div>
            <div>
                <div class='linkPortalDataWrapper'>
                    <table class='thinlist'>
                        <tbody>
                            <tr>
                                <td>{$formObj->getTBRow('Name', 'name', $row['name'])}</td>
                                <td>{$formObj->getTBRow('Phone', 'phone', $row['phone'])}</td>
                                <td>{$formObj->getTBRow('Fax', 'fax', $row['fax'])}</td>
                                <td>{$formObj->getTBRow('Address 1', 'address1', $row['address1'])}</td>
                            </tr>
                            <tr>
                                <td>{$formObj->getTBRow('Address 2', 'address2', $row['address2'])}</td>
                                <td>{$formObj->getDDRowBySQL('Country', 'country', $sqlCountry, $row['country'], $expCountry)}</td>
                                <td>{$formObj->getTBRow('Postal Code', 'postal_code', $row['postal_code'])}</td>
                                <td>{$formObj->getTBRow('Contact Name', 'contact_name', $row['contact_name'])}</td>
                            </tr>
                            <tr>
                                <td>{$formObj->getTBRow('Designation', 'designation', $row['designation'])}</td>
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
    function getRightPanel($row){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $media = Zend_Registry::get('media');
        $comment = getCPPluginObj('common_comment');

        $rows = "";

        $record_id = $fn->getIssetParam($row, 'dormitory_id');

        $text = "
        <div id='employeeLinkPortal'>{$this->getEmployeeLinkPortal($row['dormitory_id'])}</div>
        ";

        return $text;
    }

    /**
     *
     */
    function getEmployeeLinkPortal($dormitory_id=''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        if($dormitory_id == ''){
            $dormitory_id = $fn->getReqParam('dormitory_id');
        }

        $employeeLinked = $this->getEmployeeLinkDetail($dormitory_id);

        $recCount = $fn->getRecordCount('employee', "dormitory_id = '{$dormitory_id}'");

        $header ="
        <thead>
            <tr>
                <th>Employee Name</th>
            </tr>
        </thead>
        ";

        if($recCount == 0){
            $header = "<tr><td align='center'>No Records Linked<br/><br/></td></tr>";
        }

        $text = "
        <div class='linkPortalWrapper hms_dosage_agewiseLink'>
            <div class='header' expanded='1'>
                <div class='floatbox'>
                    <div class='float_left'>Employee Linked</div>
                    <div class='txtRight'>
                        <span class='count'>({$recCount})</span>
                    </div>
                </div>
            </div>
            <div class='linkPortalDataWrapper'>
                <form>
                    <table class='renewallist'>
                        {$header}
                        <tbody id='addEmployeeCategoryPortal'>
                            {$employeeLinked}
                        </tbody>
                    </table>
                    <input type='hidden' name='dormitory_id' value='{$dormitory_id}' />
                </form>
            </div>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getEmployeeLinkDetail($dormitory_id = ''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        if($dormitory_id == ''){
            $dormitory_id = $fn->getReqParam('dormitory_id');
        }

        $rows  = "";

        $SQL="
        SELECT ec.*
        FROM employee ec
        WHERE ec.dormitory_id = '{$dormitory_id}'
          AND ec.status = 'Current'
        ";
        $result   = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);
        
        $count = 1;
        while ($row = $db->sql_fetchrow($result)) {
            $rows .= "
                <tr>
                    <td>{$row['first_name']}</td>
                </tr>
            ";
            $count++;
        }

        $text = "{$rows}";

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
        
        $text = "
        ";

        return $text;
    }

}