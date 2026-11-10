<?
class CP_Admin_Modules_Payroll_CPFCalculator_View extends CP_Common_Lib_ModuleViewAbstract
{
    /**
     *
     */
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');

        $count   = 0;
        $rows    = '';

        foreach ($dataArray as $row){

            $rows .= "
            {$listObj->getListRowHeader($row, $count)}
            {$listObj->getListDataCell($row['year'])}
            {$listObj->getListDataCell($row['from_age'])}
            {$listObj->getListDataCell($row['to_age'])}
            {$listObj->getListDataCell($row['by_employer'])}
            {$listObj->getListDataCell($row['by_employee'])}
            {$listObj->getListDataCell($row['spr_year'])}
            {$listObj->getListRowEnd($row['cpf_calculator_id'])}
            ";

            $count++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Year', 'cpf.year')}
        {$listObj->getListHeaderCell('From Age', 'cpf.from_age')}
        {$listObj->getListHeaderCell('To Age', 'cpf.to_age')}
        {$listObj->getListHeaderCell('CPF(Employer)', 'cpf.by_employer')}
        {$listObj->getListHeaderCell('CPF(Employee)', 'cpf.by_employee')}
        {$listObj->getListHeaderCell('SPR Year', 'cpf.spr_year')}
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
        {$formObj->getTBRow('FROM Age', 'from_age')}
        {$formObj->getTBRow('To Age', 'to_age')}
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

        $expVl = array('sqlType' => 'OneField');

        $expNoEdit  = array('isEditable' => 0);

        $sprYear = array(
                       '1'
                      ,'2'
                      ,'3'
                      );

        $text = "
        <div class='linkPortalWrapper'>
            <div expanded='1' class='header'>
                <div class='floatbox'>
                    <div class='float_left'>Step 1 (Main Details)</div>
                    <div class='toggle'></div>
                </div>
            </div>
            <div>
                <div class='linkPortalDataWrapper'>
                    <table class='thinlist'>
                        <tbody>
                            <tr>
                                <td>{$formObj->getTBRow('FROM Age', 'from_age', $row['from_age'])}</td>
                                <td>{$formObj->getTBRow('To Age', 'to_age', $row['to_age'])}</td>
                                <td>{$formObj->getTBRow('By employer(% of wage)', 'by_employer', $row['by_employer'])}</td>
                                <td>{$formObj->getTBRow('By employee(% of wage)', 'by_employee', $row['by_employee'])}</td>
                            </tr>
                            <tr>
                                <td>{$formObj->getTBRow('Total(% of wage)','Total', $row['Total'],  $expNoEdit)}</td>
                                <td>{$formObj->getTBRow('Year', 'year', $row['year'])}</td>
                                <td>{$formObj->getTBRow('From Salary', 'from_salary', $row['from_salary'])}</td>
                                <td>{$formObj->getTBRow('To Salary', 'to_salary', $row['to_salary'])}</td>
                            </tr>
                            <tr>
                                <td>{$formObj->getTBRow('Cap Amount Employer', 'cap_amount_employer', $row['cap_amount_employer'])}</td>
                                <td>{$formObj->getTBRow('Cap Amount Employee', 'cap_amount_employee', $row['cap_amount_employee'])}</td>
                                <td>{$formObj->getTBRow('Total Cap Amount', 'total_cap_amount', $row['total_cap_amount'],  $expNoEdit)}</td>
                                <td>{$formObj->getDropDownRowByArray('SPR Year', 'spr_year', $sprYear, $row['spr_year'])}</td>
                            </tr>

                            <tr>
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
        $fn = Zend_Registry::get('fn');
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $comment = getCPPluginObj('common_comment');
        $media = Zend_Registry::get('media');

        $record_id = $fn->getIssetParam($row, 'cpf_calculator_id');

        $text = "
        {$media->getRightPanelMediaDisplay('Attachments', 'payroll_cPFCalculator', 'attachment', $row)}
        {$this->getCpfCalculatorHistory($row)}
        ";

        return $text;
    }

    /**
     *
     */
    function getCpfCalculatorHistory($row){
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $rows ='';
        $text = '';

        $SQL = "
        SELECT *
        FROM cpf_calculator_history
        WHERE cpf_calculator_id = {$row['cpf_calculator_id']}
        ";
        $result = $db->sql_query($SQL);
        $numRows  = $db->sql_numrows($result);
        while ($rowCPF = $db->sql_fetchrow($result)) {

            $creation_date = $fn->getCPDate($rowCPF['creation_date'],"d-m-Y");

            $rows .="
            <tr>
                <td>{$rowCPF['from_age']}</td>
                <td>{$rowCPF['to_age']}</td>
                <td>{$rowCPF['by_employer']}</td>
                <td>{$rowCPF['by_employee']}</td>
                <td>{$rowCPF['year']}</td>
                <td>{$rowCPF['created_by']}</td>
                <td>{$creation_date}</td>
            </tr>
            ";
        }

        if ($numRows > 0){
        $text = "
        <div class='linkPortalWrapper'>
            <div expanded='1' class='header'>
                <div class='floatbox'>
                    <div class='float_left'>CPF History</div>
                    <div class='toggle'></div>
                </div>
            </div>
            <div>
                <div class='linkPortalDataWrapper'>
                    <table class='thinlist'>
                        <tbody>
                            <tr>
                                <th>FROM Age</th>
                                <th>TO Age</th>
                                <th>By employer(% of wage)</th>
                                <th>By employee(% of wage)</th>
                                <th>Year</th>
                                <th>Created By</th>
                                <th>Date</th>
                            </tr>
                            {$rows}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        ";
        }

        return $text;
    }

    /**
     *
     */
    function getQuickSearch() {
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');

        $year = $fn->getReqParam('year');

        $spArray = array(
            ""
           ,"Flagged"
           ,"Not-Flagged"
        );

        if ($year == '') {
            if (date('m') == 12) {
                $year  = date('Y') - 1;
            } else {
                $year = date('Y');
            }
        }

        $text = "
        <td>
            <select name='special_search'>
                <option value=''>Special Search</option
                {$cpUtil->getDropDown1($spArray, $tv['special_search'])}
           </select>
        </td>
        <td>Year
            <select name='year' class='yearFilter'>
                {$cpUtil->getDropDownFromArr($cpCfg['m.payroll.yearArr'], $year)}
            </select>
        </td>
        ";

        return $text;
    }
}