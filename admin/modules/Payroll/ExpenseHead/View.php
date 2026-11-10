<?
class CPL_Admin_Modules_Payroll_ExpenseHead_View extends CP_Common_Lib_ModuleViewAbstract
{
   function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $text = '';
        $rows = '';
        $rowCounter = 0;

        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){

            $creation_date     = $fn->getCPDate($row['creation_date'], 'd-m-Y H:i:s');
            $modification_date = $fn->getCPDate($row['modification_date'], 'd-m-Y H:i:s');

            if($row['modified_by'] != "" && $row['modification_date'] != ""){
                $createdModifiedBy = "<i>{$row['modified_by']} {$modification_date}</i>";
            }else{
                $createdModifiedBy = "<i>{$row['created_by']} {$creation_date}</i>";
            }

            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getListDataCell($row['title'])}
            {$listObj->getListDataCell($createdModifiedBy)}
            {$listObj->getListRowEnd($row['expense_group_id'])}
            ";
            $rowCounter++;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Title', 'title')}
        {$listObj->getListHeaderCell('Updated By')}
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
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $fieldset = "
        {$formObj->getTBRow('Title', 'title')}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('New', $fieldset)}
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

        $creation_date     = $fn->getCPDate($row['creation_date'], 'd-m-Y H:i:s');
        $modification_date = $fn->getCPDate($row['modification_date'], 'd-m-Y H:i:s');

        $text = "
        <div class='linkPortalWrapper'>
            <div expanded='1' class='header'>
                <div class='floatbox'>
                    <div class='float_left'>Expense Head Details</div>
                    <div class='toggle'></div>
                    <div class='float_right'>Creation : {$row['created_by']} on {$creation_date} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Modified : {$row['modified_by']} {$modification_date}</div>
                </div>
            </div>
            <div>
                <div class='linkPortalDataWrapper'>
                    <table class='thinlist'>
                        <tbody>
                            <tr>
                                <td>{$formObj->getTBRow('Title', 'title', $row['title'])}</td>
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
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $db = Zend_Registry::get('db');
        $comment = getCPPluginObj('common_comment');
        $media = Zend_Registry::get('media');
        $text = '';

        $record_id = $fn->getIssetParam($row, 'expense_group_id');

        $text .="
        <div id='ExpenseSubHeadLinkPortal'>
            {$this->getExpenseSubHeadDetail($row['expense_group_id'])}
        </div>
        ";

        return $text;
    }
    /**
     *
     */
    function getExpenseSubHeadDetail($expense_group_id=''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        if($expense_group_id == ''){
            $expense_group_id = $fn->getReqParam('expense_group_id');
        }

        $ExpenseSubHead = $this->getExpenseSubHeadDetailList($expense_group_id);

        $recCount = $fn->getRecordCount('expense_sub_group', "expense_group_id = '{$expense_group_id}'");

            $header ="
            <thead>
                <tr>
                    <th>Title</th>
                    <th class='txtRight'>Edit</th>
                    <th class='txtRight'>Delete</th>
                </tr>
            </thead>
            ";


        $formActionExpenseSubHead = "index.php?module=payroll_expenseHead&_spAction=AddExpenseSubHead&expense_group_id={$expense_group_id}&showHTML=0";

        $add = "<div class='actBtns'>
                    <a id='AddExpenseSubHead' href='{$formActionExpenseSubHead}' expense_group_id={$expense_group_id}>Add</a>
                </div>";

        $text = "
        <div class='linkPortalWrapper payroll_expenseHead_expenseSubHeadLink'>
            <div class='header' expanded='1'>
                <div class='floatbox'>
                    <div class='float_left'>Expense Sub Head Linked</div>
                    <div class='txtRight'>
                    <span class='count'>({$recCount})</span>
                    </div>
                </div>
            </div>
            <div class='linkPortalDataWrapper'>
                <form>
                    <table class='ExpenseSubHeadlist'>
                        {$header}
                        <tbody id='AddExpenseSubHeadPortal'>
                            {$ExpenseSubHead}
                        </tbody>
                    </table>
                    <input type='hidden' name='expense_group_id' value='{$expense_group_id}' />
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
    function getExpenseSubHeadDetailList($expense_group_id = ''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        if($expense_group_id == ''){
            $expense_group_id = $fn->getReqParam('expense_group_id');
        }

        $expense_sub_group_id = $fn->getReqParam('expense_sub_group_id');

        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        $appendSql = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSql = "AND site_id = {$cpSiteIdSession}";
        }

        $rows  = "";

        $SQL="
        SELECT *
        FROM expense_sub_group
        WHERE expense_group_id = '{$expense_group_id}'
        {$appendSql}
        ";
        $result   = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        $count = 1;
        while ($row = $db->sql_fetchrow($result)) {

            $formActionEditExpenseSubHead   = "index.php?module=payroll_expenseHead&_spAction=EditExpenseSubHead&expense_sub_group_id={$row['expense_sub_group_id']}&expense_group_id={$expense_group_id}&showHTML=0";

            $sqlExpenseRec ="
            SELECT e.expense_id FROM expense e
            WHERE e.type = 'Expense' AND e.group = {$expense_group_id} AND e.sub_group = {$row['expense_sub_group_id']}
            ";
            $resultExpenseRec   = $db->sql_query($sqlExpenseRec);
            $numRowsExpenseRec = $db->sql_numrows($resultExpenseRec);

            if ($numRowsExpenseRec > 0) {
                $deleteIcon = "<div class='float_right'>Cannot Delete</div>";
                $editIcon = "<div class='float_right'>Cannot Edit</div>";
            } else {
                $deleteIcon ="
                <div class='float_right'>
                    <a class='deleteExpenseSubHead' href='#'  expense_sub_group_id='{$row['expense_sub_group_id']}' expense_group_id='{$row['expense_group_id']}'>
                        <img src='/cmspilotv30/CP/admin/images/icons/btn_remove.png'>
                    </a>
                </div>
                ";
                $editIcon ="
                <div class='float_right'>
                    <a class='EditExpenseSubHead' href='{$formActionEditExpenseSubHead}' expense_sub_group_id='{$row['expense_sub_group_id']}'  expense_group_id='{$row['expense_group_id']}'>
                        <img src='/cmspilotv30/CP/admin/images/icons/btn_edit.png'>
                    </a>
                </div>
                ";
            }


            $rows .= "
            <tr>
                <td>{$row['title']}</td>
                <td>{$editIcon}</td>
                <td>{$deleteIcon}</td>
            </tr>
            ";
            $count++;
        }

        if($numRows == 0){
            $rows .= "
                <tr>
                    <td class='noRenewal'>No Records Linked</td>
                </tr>
            ";

        }
        $text="{$rows}";

        return $text;
    }
    /**
     *
     */
    function getAddExpenseSubHead() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $expVl = array('sqlType' => 'OneField');

        $expense_group_id  = $fn->getReqParam('expense_group_id');

        $formAction = "index.php?_topRm=main&module=payroll_expenseHead&_spAction=expenseSubHeadFormSubmit&showHTML=0";

        $text = "
        <form id='expenseSubHeadPortalForm' class='yform columnar' method='post' action='{$formAction}'>
            {$formObj->getTBRow('Title', 'title')}
            <input type='hidden' name='expense_group_id' value='{$expense_group_id}' />
        </form>
        ";
        return $text;
    }

     /**
     *
     */
    function getEditExpenseSubHead() {
        $db      = Zend_Registry::get('db');
        $dbUtil  = Zend_Registry::get('dbUtil');
        $cpUtil  = Zend_Registry::get('cpUtil');
        $tv      = Zend_Registry::get('tv');
        $fn      = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $cpCfg   = Zend_Registry::get('cpCfg');

        $expense_group_id  = $fn->getReqParam('expense_group_id');
        $expense_sub_group_id  = $fn->getReqParam('expense_sub_group_id');

        if($expense_sub_group_id == ''){
            $expense_sub_group_id  = $fn->getReqParam('expense_sub_group_id');
        }

        $rows  = "";

        $formAction = "index.php?module=payroll_expenseHead&_spAction=EditExpenseSubHeadFormSubmit&showHTML=0&expense_sub_group_id={$expense_sub_group_id}";

        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        $appendSql = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSql = "AND site_id = {$cpSiteIdSession}";
        }

        $SQLExpenseSubHead="
        SELECT *
        FROM  expense_sub_group
        WHERE expense_sub_group_id = '{$expense_sub_group_id}'
        {$appendSql}
        ";
        $resultExpenseSubHead   = $db->sql_query($SQLExpenseSubHead);
        $rowExpenseSubHead = $db->sql_fetchrow($resultExpenseSubHead);

        $rows .= "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            {$formObj->getTBRow('Title', 'title', $rowExpenseSubHead['title'])}
            <input type='hidden' name='expense_sub_group_id' value='{$expense_sub_group_id}' />
        </form>
        ";        

        $text="{$rows}";

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
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $ln = Zend_Registry::get('ln');

        $text = "
        ";

        return $text;
    }

    /**
     *
     */

}