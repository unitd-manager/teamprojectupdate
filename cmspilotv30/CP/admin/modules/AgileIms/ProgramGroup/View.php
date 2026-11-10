<?
class CP_Admin_Modules_AgileIms_ProgramGroup_View extends CP_Common_Lib_ModuleViewAbstract
{
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        
        $count   = 0;
        $rows    = '';

        foreach ($dataArray as $row){
            $rows .= "
            {$listObj->getListRowHeader($row, $count)}
            {$listObj->getGoToDetailText($count, $row['title'])}
            {$listObj->getListRowEnd($row['program_group_id'])}
            ";

            $count++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Title', 'pg.title')}
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
        $cpCfg = Zend_Registry::get('cpCfg');
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');

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
    function getEdit($row){
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $formObj = Zend_Registry::get('formObj');
        $ln = Zend_Registry::get('ln');

        $formObj->mode = $tv['action'];

        $fielset1  = "
        {$formObj->getTARow('Title', 'title', $ln->gfv($row, 'title', '0'))}
		";

        $text = "
        {$formObj->getFieldSetWrapped('Program Group Details', $fielset1)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    /**
     *
     */
    function getRightPanel($row){
        $displayLinkData = Zend_Registry::get('displayLinkData');

        $text = "
        {$displayLinkData->getLinkPortalMain('agileIms_programGroup', 'agileIms_subsidyDiscountLink', 'Subsidy Discount Link', $row)}
        ";
        
        return $text;
    }
    
    /**
     *
     */
    function getQuickSearch() {

        $text = "";        
        
        return $text;
    }
}