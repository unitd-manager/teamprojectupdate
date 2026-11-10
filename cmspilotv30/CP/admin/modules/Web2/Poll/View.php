<?
class CP_Admin_Modules_Web2_Poll_View extends CP_Common_Lib_ModuleViewAbstract
{
    //==================================================================//
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        
        $rows  = "";
        $rowCounter = 0;

        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){
            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['title'])}                                  
            {$listObj->getListDataCell($row['poll_id'], 'center')}
            {$listObj->getListPublishedImage($row['published'], $row['poll_id'])}
            {$listObj->getListDataCell($fn->getYesNo($row['latest']), 'center')}
            {$listObj->getListRowEnd($row['poll_id'])}
            ";
            $rowCounter++;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Title', 'p.title')}
        {$listObj->getListHeaderCell('ID', 'p.poll_id', 'headerCenter')}
        {$listObj->getListHeaderCell('Published', 'p.published', 'headerCenter')}
        {$listObj->getListHeaderCell('Latest', 'p.latest', 'headerCenter')}
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
        {$formObj->getTBRow('Title', 'title')}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fieldset)}
        ";

        return $text;
    }

    //==================================================================//
    function getEdit($row) {
        $db = Zend_Registry::get('db');
        $ln = Zend_Registry::get('ln');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $am = Zend_Registry::get('am');
        $dateUtil = Zend_Registry::get('dateUtil');
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $ln = Zend_Registry::get('ln');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        $formObj->mode = $tv['action'];

        $published        = '';
        $descriptionshort = '';
 
        $formObj->mode = $tv['action'];

        $text = '';

        $exp = array('sqlType' => 'OneField');

        $fieldset1 = "
        {$formObj->getTBRow('Title', 'title', $row['title'])}
        {$formObj->getYesNoRRow('Published', 'published', $row['published'] )} 
        {$formObj->getYesNoRRow('Latest', 'latest', $row['latest'] )} 
        ";      
            
        $text = "
        {$formObj->getFieldSetWrapped('Poll Details', $fieldset1)}
     
        ";

        return $text;
    }

    //==================================================================//
    function getRightPanel($row) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $media = Zend_Registry::get('media');

        $text = "
        {$displayLinkData->getLinkPortalMain('web2_poll', 'web2_pollHistoryLink', 'Poll History', $row)}
        ";

        return $text;
    }


    /**
     *
     */
    function getQuickSearch() {
        $db = Zend_Registry::get('db');

        $text = "
        ";
        return $text;
    }
}