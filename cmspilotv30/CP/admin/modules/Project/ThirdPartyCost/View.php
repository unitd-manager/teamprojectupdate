<?
class CP_Admin_Modules_Project_ThirdPartyCost_View extends CP_Common_Lib_ModuleViewAbstract
{
    /**
     *
     */
    function getList($dataArray) {
        $listObj = Zend_Registry::get('listObj');

        $rows = '';

        $rowCounter = 0;

        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){
            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getListDataCell( $row['project_code'])}
            {$listObj->getListDataCell($row['project_title'])}
            {$listObj->getListDataCell($row['item_title'])}
            {$listObj->getListDataCell(number_format($row['budget_amount'],0), 'right')}
            {$listObj->getListDataCell(number_format($row['actual_amount'],0), 'right')}
            {$listObj->getListDataCell($row['third_party_cost_id'], 'center')}
            {$listObj->getListRowEnd($row['third_party_cost_id'])}
            ";
            $rowCounter++;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Project Code', 'b.project_code', 'w75')}
        {$listObj->getListHeaderCell('Project Title', 'b.project_title')}
        {$listObj->getListHeaderCell('Item Title', 'a.item_title')}
        {$listObj->getListHeaderCell('Budget Amount', 'a.budget_amount', 'w100 headerRight')}
        {$listObj->getListHeaderCell('Actual Amount', 'a.actual_amount', 'w100 headerRight')}
        {$listObj->getListHeaderCell('ID', 'a.third_party_cost_id', 'headerCenter')}
        {$listObj->getListHeaderEnd()}
        {$rows}
        {$listObj->getListFooter()}

        ";
        return $text;
    }

    /**
     *
     */
    function getQuickSearch() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $fn = Zend_Registry::get('fn');

        $item_title = $fn->getReqParam('item_title');

        $sqlCombo  = $fn->getValueListSQL('thirdPartyItem');

        $text = "
        <td>
            <select name='item_title'>
                <option value=''>Item Title</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $sqlCombo, $item_title)}
            </select>
        </td>
        ";
        
        return $text;
    }
}
