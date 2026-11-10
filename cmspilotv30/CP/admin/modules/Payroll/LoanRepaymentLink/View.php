<?
class CP_Admin_Modules_Payroll_LoanRepaymentLink_View extends CP_Common_Lib_ModuleLinkViewAbstract
{
    function getList($dataArray, $linkRecType) {
        $listObj = Zend_Registry::get('listObj');
        $listLinkObj = Zend_Registry::get('listLinkObj');

        $rows       = '';
        $rowCounter = 0;

        foreach ($dataArray as $row){

            $rows .= "
            {$listLinkObj->getListRowHeaderLink($row, $rowCounter)}
            {$listObj->getListDataCell($row['generated_date'])}
            {$listObj->getListDataCell($row['loan_repayment_amount_per_month'])}
            {$listLinkObj->getListRowEndLink($linkRecType, $row['loan_id'])}
            ";
            $rowCounter++ ;
        }


        $text = "
        {$listLinkObj->getListHeaderLink()}
        {$listLinkObj->getListHeaderCellLink($linkRecType,'Generated Date', 'generated_date')}
        {$listLinkObj->getListHeaderCellLink($linkRecType,'Amount Paid', 'a.loan_repayment_amount_per_month')}
        {$listLinkObj->getListHeaderEndLink($linkRecType)}
        {$rows}
        {$listLinkObj->getListFooterLink()}
        ";

        return $text;
    }

}
