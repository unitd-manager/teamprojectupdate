<?
class CPL_Admin_Modules_Payroll_IncomeHead_View extends CP_Common_Lib_ModuleViewAbstract
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
            {$listObj->getListRowEnd($row['income_group_id'])}
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
                    <div class='float_left'>Income Head Details</div>
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

        $record_id = $fn->getIssetParam($row, 'income_group_id');

        $text .="
        <div id='IncomeSubHeadLinkPortal'>
            {$this->getIncomeSubHeadDetail($row['income_group_id'])}
        </div>
        ";

        return $text;
    }
    /**
     *
     */
    function getIncomeSubHeadDetail($income_group_id=''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        if($income_group_id == ''){
            $income_group_id = $fn->getReqParam('income_group_id');
        }

        $IncomeSubHead = $this->getIncomeSubHeadDetailList($income_group_id);

        $recCount = $fn->getRecordCount('income_sub_group', "income_group_id = '{$income_group_id}'");

            $header ="
            <thead>
                <tr>
                    <th>Title</th>
                    <th class='txtRight'>Edit</th>
                    <th class='txtRight'>Delete</th>
                </tr>
            </thead>
            ";


        $formActionIncomeSubHead = "index.php?module=payroll_incomeHead&_spAction=AddIncomeSubHead&income_group_id={$income_group_id}&showHTML=0";

        $add = "<div class='actBtns'>
                    <a id='AddIncomeSubHead' href='{$formActionIncomeSubHead}' income_group_id={$income_group_id}>Add</a>
                </div>";

        $text = "
        <div class='linkPortalWrapper payroll_incomeHead_incomeSubHeadLink'>
            <div class='header' expanded='1'>
                <div class='floatbox'>
                    <div class='float_left'>Income Sub Head Linked</div>
                    <div class='txtRight'>
                    <span class='count'>({$recCount})</span>
                    </div>
                </div>
            </div>
            <div class='linkPortalDataWrapper'>
                <form>
                    <table class='IncomeSubHeadlist'>
                        {$header}
                        <tbody id='AddIncomeSubHeadPortal'>
                            {$IncomeSubHead}
                        </tbody>
                    </table>
                    <input type='hidden' name='income_group_id' value='{$income_group_id}' />
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
    function getIncomeSubHeadDetailList($income_group_id = ''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        if($income_group_id == ''){
            $income_group_id = $fn->getReqParam('income_group_id');
        }

        $income_sub_group_id = $fn->getReqParam('income_sub_group_id');

        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        $appendSql = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSql = "AND site_id = {$cpSiteIdSession}";
        }

        $rows  = "";

        $SQL="
        SELECT *
        FROM income_sub_group
        WHERE income_group_id = '{$income_group_id}'
        {$appendSql}
        ";
        $result   = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        $count = 1;
        while ($row = $db->sql_fetchrow($result)) {

            $formActionEditIncomeSubHead   = "index.php?module=payroll_incomeHead&_spAction=EditIncomeSubHead&income_sub_group_id={$row['income_sub_group_id']}&income_group_id={$income_group_id}&showHTML=0";

            $sqlIncomeRec ="
            SELECT e.expense_id  FROM expense e
            WHERE e.type = 'Income' AND e.group = {$income_group_id} AND e.sub_group = {$row['income_sub_group_id']}
            ";
            $resultIncomeRec   = $db->sql_query($sqlIncomeRec);
            $numRowsIncomeRec = $db->sql_numrows($resultIncomeRec);

            if ($numRowsIncomeRec > 0) {
                $deleteIcon = "<div class='float_right'>Cannot Delete</div>";
                $editIcon = "<div class='float_right'>Cannot Edit</div>";
            } else {
                $deleteIcon ="
                <div class='float_right'>
                    <a class='deleteIncomeSubHead' href='#'  income_sub_group_id='{$row['income_sub_group_id']}' income_group_id='{$row['income_group_id']}'>
                        <img src='/cmspilotv30/CP/admin/images/icons/btn_remove.png'>
                    </a>
                </div>
                ";
                $editIcon ="
                <div class='float_right'>
                    <a class='EditIncomeSubHead' href='{$formActionEditIncomeSubHead}' income_sub_group_id='{$row['income_sub_group_id']}'  income_group_id='{$row['income_group_id']}'>
                        <img src='/cmspilotv30/CP/admin/images/icons/btn_edit.png'>
                    </a>
                </div>
                ";
            }

            $rows .= "
            <tr>
                <td>{$row['title']}</td>
                <td>
                    {$editIcon}
                </td>
                <td>
                    {$deleteIcon}
                </td>
            </tr>
            ";
            $count++;
        }

        if ($numRows == 0) {
            $rows .= "<tr><td class='noRenewal'>No Records Linked</td></tr>";
        }

        $text = "{$rows}";

        return $text;
    }

    /**
     *
     */
    function getAddIncomeSubHead() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $expVl = array('sqlType' => 'OneField');

        $income_group_id  = $fn->getReqParam('income_group_id');

        $formAction = "index.php?_topRm=main&module=payroll_incomeHead&_spAction=incomeSubHeadFormSubmit&showHTML=0";

        $text = "
        <form id='incomeSubHeadPortalForm' class='yform columnar' method='post' action='{$formAction}'>
            {$formObj->getTBRow('Title', 'title')}
            <input type='hidden' name='income_group_id' value='{$income_group_id}' />
        </form>
        ";
        return $text;
    }

     /**
     *
     */
    function getEditIncomeSubHead() {
        $db      = Zend_Registry::get('db');
        $dbUtil  = Zend_Registry::get('dbUtil');
        $cpUtil  = Zend_Registry::get('cpUtil');
        $tv      = Zend_Registry::get('tv');
        $fn      = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $cpCfg   = Zend_Registry::get('cpCfg');

        $income_group_id  = $fn->getReqParam('income_group_id');
        $income_sub_group_id  = $fn->getReqParam('income_sub_group_id');

        if($income_sub_group_id == ''){
            $income_sub_group_id  = $fn->getReqParam('income_sub_group_id');
        }

        $rows  = "";

        $formAction = "index.php?module=payroll_incomeHead&_spAction=EditIncomeSubHeadFormSubmit&showHTML=0&income_sub_group_id={$income_sub_group_id}";

        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        $appendSql = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSql = "AND site_id = {$cpSiteIdSession}";
        }

        $SQLIncomeSubHead="
        SELECT *
        FROM  income_sub_group
        WHERE income_sub_group_id = '{$income_sub_group_id}'
        {$appendSql}
        ";
        $resultIncomeSubHead   = $db->sql_query($SQLIncomeSubHead);
        $rowIncomeSubHead = $db->sql_fetchrow($resultIncomeSubHead);

        $rows .= "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            {$formObj->getTBRow('Title', 'title', $rowIncomeSubHead['title'])}
            <input type='hidden' name='income_sub_group_id' value='{$income_sub_group_id}' />
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