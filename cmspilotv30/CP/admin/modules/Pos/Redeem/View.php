<?
class CP_Admin_Modules_Pos_Redeem_View extends CP_Common_Lib_ModuleViewAbstract
{
    //==================================================================//
    function getList($dataArray) {
        $listObj = Zend_Registry::get('listObj');

        $text = '';
        $rows = '';
        $rowCounter = 0;

        foreach ($dataArray as $row){
            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['code'])}
            {$listObj->getGoToDetailText($rowCounter, $row['start_date'])}
            {$listObj->getListDataCell($row['end_date'])}
            {$listObj->getListDataCell($row['member_group'])}
            {$listObj->getListDataCell($row['redeem_id'], 'center')}
            {$listObj->getListRowEnd($row['redeem_id'])}
            ";
            $rowCounter++;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Redeem Code', 'r.code')}
        {$listObj->getListHeaderCell('StartDate', 'r.start_date')}
        {$listObj->getListHeaderCell('End Date', 'r.end_date')}
        {$listObj->getListHeaderCell('Member Group', 'r.member_group')}
        {$listObj->getListHeaderCell('ID', 'r.redeem_id', 'headerCenter')}
        {$listObj->getListHeaderEnd()}
        {$rows}
        {$listObj->getListFooter()}
        ";

        return $text;
    }

    //==================================================================//
    function getNew(){
        $formObj = Zend_Registry::get('formObj');

        $fieldset = "
        {$formObj->getTBRow('Redeem Code', 'code')}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fieldset)}
        ";

        return $text;
    }

    //==================================================================//
    function getEdit($row) {
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $formObj = Zend_Registry::get('formObj');
        
        $sqlRedeemGroup  = $fn->getValueListSQL('redeemMemberGroup');
        $expVl = array('sqlType' => 'OneField');
        $expInterest = array('detailValue' => $row['member_group']);

        $fieldset1 = "
        {$formObj->getTBRow('Redeem Code', 'code', $row['code'])}
        {$formObj->getDateRow('Start Date', 'start_date', $row['start_date'])}
        {$formObj->getDateRow('End Date', 'end_date', $row['end_date'])}
        {$formObj->getDDRowBySQL('Member Group', 'member_group_id', $fn->getDDSql('pos_interest'), $row['member_group_id'], $expInterest)}
        ";
        
        $text = "
        {$formObj->getFieldSetWrapped('Redeem Maintenance Details', $fieldset1)}
        ";

        return $text;
    }

    //==================================================================//
    function getRightPanel($row){
        $media = Zend_Registry::get('media');
        $displayLinkData = Zend_Registry::get('displayLinkData');
                
        $text ="
        {$displayLinkData->getLinkPortalMain("pos_redeem", "pos_productLink", "Products Linked", $row)}
        ";
        return $text;
    }

    /**
     *
     */
    function getQuickSearch() {
                
        $text = "
        ";

        
        return $text;
    }
}