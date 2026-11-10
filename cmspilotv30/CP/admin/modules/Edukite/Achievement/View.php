<?
class CP_Admin_Modules_Edukite_Achievement_View extends CP_Common_Lib_ModuleViewAbstract
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
            {$listObj->getListDataCell($row['category'])}
            {$listObj->getListDataCell($row['achievement_id'], 'center')}
            {$listObj->getListPublishedImage($row['published'], $row['achievement_id'])}
            {$listObj->getListRowEnd($row['achievement_id'])}
            ";
            $rowCounter++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Achievement Title', 'a.title')}
        {$listObj->getListHeaderCell('Category', 'a.category')}
        {$listObj->getListHeaderCell('ID', 'a.achievement_id' , 'headerCenter')}
        {$listObj->getListHeaderCell('Published', 'a.published', 'headerCenter')}
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
        {$formObj->getTBRow('Achievement Title', 'title')}
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
        $am = Zend_Registry::get('am');
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $formObj = Zend_Registry::get('formObj');

        $formObj->mode = $tv['action'];     

		$spArrayCat = array (
				'Heading'
		); 

        $fielset1 = "
        {$formObj->getTBRow('Achievement Title', 'title', $row['title'])}
        {$formObj->getDDRowByArr('Category', 'category', $spArrayCat, $row['category'])}
        {$formObj->getYesNoRRow('Published', 'published', $row['published'])}
		";

        $text = "
        {$formObj->getFieldSetWrapped('Main Details', $fielset1)}
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