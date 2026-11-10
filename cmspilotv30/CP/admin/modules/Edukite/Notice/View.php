<?
class CP_Admin_Modules_Edukite_Notice_View extends CP_Common_Modules_Edukite_Notice_View
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
            {$listObj->getGoToDetailText($rowCounter, $row['title'])}
            {$listObj->getListDataCell($row['launch_date'])}
            {$listObj->getListPublishedImage($row['published'], $row['notice_id'])}
            {$listObj->getListDataCell($row['notice_id'], 'center')}
            {$listObj->getListRowEnd($row['notice_id'])}
            ";
            $rowCounter++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Title', 'n.title')}
        {$listObj->getListHeaderCell('Launch Date', 'n.launch_date')}
        {$listObj->getListHeaderCell('Published', 'n.published', 'headerCenter')}
        {$listObj->getListHeaderCell('ID', 'n.notice_id' , 'headerCenter')}
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
        {$formObj->getTBRow('Title', 'title')}
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
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $formObj = Zend_Registry::get('formObj');

        $formObj->mode = $tv['action'];
        
        $sqlStaff = "
        SELECT  a.staff_id
               ,CONCAT_WS(' ', a.first_name, a.last_name ) AS staff_name 
        FROM staff a 
        ORDER BY staff_name
        ";
        
        $fielset1 = "
        {$formObj->getTBRow('Notice Title', 'title', $row['title'])}
        {$formObj->getDateRow('Launch Date', 'launch_date', $row['launch_date'])}
        {$formObj->getTBRow('Links', 'links', $row['links'])}
        {$formObj->getYesNoRRow('Published', 'published', $row['published'])}
		";

        $fieldset2 = "
        {$formObj->getHTMLEditor('Description', 'description', $ln->gfv($row, 'description', '0'))}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Main Details', $fielset1)}
        {$formObj->getFieldSetWrapped('Description', $fieldset2)}
        ";

        return $text;
    }

    /**
     *
     */
    function getRightPanel($row){
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