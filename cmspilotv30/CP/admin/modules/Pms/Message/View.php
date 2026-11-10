<?
class CP_Admin_Modules_Pms_Message_View extends CP_Common_Lib_ModuleViewAbstract
{
    /**
     *
     */
    function getList($dataArray) {
        $listObj = Zend_Registry::get('listObj');
        $dateUtil = Zend_Registry::get('dateUtil');

        $text = '';
        $rows = '';

        $rowCounter = 0;

        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){
            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $dateUtil->formatDate($row['creation_date'], 'DD MMM YYYY'))}
            {$listObj->getListDataCell($row['subject'])}
            {$listObj->getListDataCell($row['status'])}
            {$listObj->getListDataCell($row['message_id'], 'center')}
            {$listObj->getListRowEnd($row['message_id'])}
            ";

            $rowCounter++;
        }

        $text .= "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Message Date', 'm.creation_date')}
        {$listObj->getListHeaderCell('Subject', 'm.subject')}
        {$listObj->getListHeaderCell('Status', 'm.status')}
        {$listObj->getListHeaderCell('ID', 'm.message_id', 'headerCenter')}
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
        {$formObj->getTBRow('Subject', 'subject')}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fielset)}
        ";

        return $text;
    }

    /**
     *
     */
    function getEdit($row) {
        $formObj = Zend_Registry::get('formObj');
        $ln = Zend_Registry::get('ln');
        
        $ratingArr = array (1, 2, 3, 4, 5);

        $sqlstaff = "
        SELECT staff_id, first_name
        FROM   staff
        ORDER  BY staff_id
        ";

        $fieldset1 = "
        {$formObj->getTBRow('Subject', 'subject', $row['subject'])}
        {$formObj->getTBRow('Status', 'status', $row['status'])}
        {$formObj->getTARow('Description', 'description', $row['description'])}
        {$formObj->getCheckBoxArrRowBySQL('Staffs', 'staff_ids[]', $sqlstaff)}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Message Details', $fieldset1)}
        ";
        
        return $text;
    }

    /**
     *
     */
    function getRightPanel($row) {

        $text = "
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