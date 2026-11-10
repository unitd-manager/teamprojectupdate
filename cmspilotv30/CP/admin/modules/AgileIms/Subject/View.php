<?
class CP_Admin_Modules_AgileIms_Subject_View extends CP_Common_Lib_ModuleViewAbstract
{
    /**
     *
     */
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');

        $rows  = "";
        $rowCounter = 0;

        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){
            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
			{$listObj->getGoToDetailText($rowCounter, $row['code'])}
            {$listObj->getListDataCell($row['title'])}
            {$listObj->getListDataCell($row['fees'], 'right')}
            {$listObj->getListDataCell($row['subject_id'], 'center')}
            {$listObj->getListRowEnd($row['subject_id'])}
            ";
            $rowCounter++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Subject Code', 'code')}
        {$listObj->getListHeaderCell('Subject Title', 'title')}
        {$listObj->getListHeaderCell('Fees', 'fees', 'headerRight')}
        {$listObj->getListHeaderCell('ID', 'subject_id' , 'headerCenter')}
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
        {$formObj->getTBRow('Subject Code', 'code')}
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
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');

        $fieldset1 = "
        {$formObj->getTBRow('Subject Code', 'code', $row['code'])}
        {$formObj->getTBRow('Title', 'title', $row['title'])}
        {$formObj->getTBRow('Fees', 'fees', $row['fees'])}
        {$formObj->getTARow('Outcome', 'outcome', $row['outcome'])}
        {$formObj->getTARow('Synopsys', 'synopsys', $row['synopsys'])}
    	";

        $text = "
        {$formObj->getFieldSetWrapped('Subject Details', $fieldset1)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }
  
    /**
     *
     */
    function getQuickSearch() {
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');

        $text = "";        
        
        return $text;
    }
}