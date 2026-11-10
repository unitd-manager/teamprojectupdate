<?
class CP_Admin_Modules_Edukloud_Attendance_View extends CP_Common_Lib_ModuleViewAbstract
{
    /**
     *
     */
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');

        $rows  = "";
        $email = "";
        $rowCounter = 0;

        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){

            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['contact_name'])}
            {$listObj->getListDataCell($row['batch_title'])}
            {$listObj->getListDataCell($row['date'])}
            {$listObj->getListDataCell($row['status'])}
            {$listObj->getListDataCell($row['attendance_id'], 'center')}
            {$listObj->getListRowEnd($row['attendance_id'])}
            ";
            $rowCounter++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Name', 'contact_name')}
        {$listObj->getListHeaderCell('Batch', 'batch_title')}
        {$listObj->getListHeaderCell('Date', 'a.date')}
        {$listObj->getListHeaderCell('Status', 'a.status')}
        {$listObj->getListHeaderCell('ID', 'a.attendance_id' , 'headerCenter')}
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
        {$formObj->getDateRow('Date', 'date')}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fieldset)}
        ";

        return $text;
    }

    /**
     *
     */
    function getEdit($row) {
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $statusArr = array(1 => 'Present', 0 => 'Absent');
        
        $sqlCont = $fn->getDDSql('edukloud_contact');
        $expCont = array('detailValue' => $row['contact_name']);

        $sqlBatch = $fn->getDDSql('edukloud_batch');
        $expBatch = array('detailValue' => $row['batch_title']);

        $fielset1 = "
        {$formObj->getDDRowBySQL('Name', 'contact_id', $sqlCont, $row['contact_id'], $expCont)}
        {$formObj->getDDRowBySQL('Batch', 'batch_id', $sqlBatch, $row['batch_id'], $expBatch)}
        {$formObj->getDateRow('Date', 'date', $row['date'])}
        {$formObj->getRadioArrRow('Status', 'status', $row['status'], $statusArr, '')}
        ";
		
        $text = "
        {$formObj->getFieldSetWrapped('Attendance Details', $fielset1)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    /**
     *
     */
    function getRightPanel($row){
        $media = Zend_Registry::get('media');
        $displayLinkData = Zend_Registry::get('displayLinkData');

        $text ="
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