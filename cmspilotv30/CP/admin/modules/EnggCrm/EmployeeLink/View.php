<?
class CP_Admin_Modules_EnggCrm_EmployeeLink_View extends CP_Common_Lib_ModuleLinkViewAbstract
{
    function getList($dataArray, $linkRecType) {
        $listObj = Zend_Registry::get('listObj');
        $listLinkObj = Zend_Registry::get('listLinkObj');

        $rows       = '';
        $rowCounter = 0;

        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){

            if ($row['employee_work_type'] == 'Part time') {
                $rate = $row['add_hourly_rate'];
            } else if ($row['employee_work_type'] == 'Full Time') {
                $rate = $row['salary'];
            }

            $rows .= "
            {$listLinkObj->getListRowHeaderLink($row, $rowCounter)}
            {$listObj->getListDataCell($row['employee_name'])}
            {$listObj->getListDataCell($row['employee_work_type'])}
            {$listObj->getListDataCell($rate)}
            {$listLinkObj->getListRowEndLink($linkRecType, $row['employee_id'])}
            ";

            $rowCounter++ ;
        }

        $text = "
        {$listLinkObj->getListHeaderLink()}
        {$listLinkObj->getListHeaderCellLink($linkRecType,'Name', 'employee_name')}
        {$listLinkObj->getListHeaderCellLink($linkRecType,'Part Time / Full Time', 'employee_work_type')}
        {$listLinkObj->getListHeaderCellLink($linkRecType,'Basic Salary', 'employee_name')}
        {$listLinkObj->getListHeaderEndLink($linkRecType)}
        {$rows}
        {$listLinkObj->getListFooterLink()}
        ";
        
        return $text;
    }
}
