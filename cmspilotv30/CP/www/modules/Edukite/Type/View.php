<?
class CP_Www_Modules_Edukite_Type_View extends CP_Common_Modules_Edukite_Type_View
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
            {$listObj->getGoToDetailText($rowCounter, $row['title'], '', '', $row)}
            {$listObj->getListRowEnd($row['notice_type_id'])}
            ";
            $rowCounter++ ;
        }

        $text = "
        <div class='typeList'>
            {$listObj->getListHeader()}
            {$listObj->getListHeaderCell('Type', 's.title')}
            {$listObj->getListHeaderEnd()}
            {$rows}
            {$listObj->getListFooter()}
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getNew(){
        $formObj = Zend_Registry::get('formObj');

        $fieldset = "
        {$formObj->getTBRow('Notice Type', 'title')}
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
                
        $fieldset1 = "
        {$formObj->getTBRow('Notice Type', 'title', $row['title'])}
		";
		
        $text = "
        {$formObj->getFieldSetWrapped('Notice Type Details', $fieldset1)}
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