<?
class CP_Admin_Modules_Event_Venue_View extends CP_Common_Lib_ModuleViewAbstract
{
    //==================================================================//
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $db = Zend_Registry::get('db');
        
        $rows  = "";
        $rowCounter = 0;

        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){
            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['title'])}                                  
            {$listObj->getListDataCell($row['place'])}
            {$listObj->getListPublishedImage($row['published'], $row['venue_id'])}
            
            ";
            $rowCounter++;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Title', 'v.title')}
        {$listObj->getListHeaderCell('Venue', 'v.place')}
        {$listObj->getListHeaderCell('Published', 'v.published', 'headerCenter')}
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

    //========================================================//
    //==================================================================//
    //==================================================================//


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
        {$formObj->getTBRow('Venue', 'place', $row['place'])}
        {$formObj->getYesNoRRow('Published', 'published', $row['published'] )} 
        ";      
          
        $fieldset2 = $formObj->getHTMLEditor('Description', 'description', $ln->gfv($row, 'description', '0'));   

        $fieldset3 = "
        {$formObj->getTARow('Map', 'map', $row['map'])}
        ";
        
        $text = "
        {$formObj->getFieldSetWrapped('Venue Details', $fieldset1)}
        {$formObj->getFieldSetWrapped('Description', $fieldset2)}
        {$formObj->getFieldSetWrapped('Map', $fieldset3)}
        
        ";

        return $text;
    }

    //========================================================//
    //==================================================================//
    //==================================================================//
    function getRightPanel($row){
        $media = Zend_Registry::get('media');
        $fn = Zend_Registry::get('fn');
        
        $record_id = $fn->getIssetParam($row, 'venue_id');

        $text = "
        {$media->getRightPanelMediaDisplay('picture', 'event_venue', 'picture', $row)}
        ";

        return $text;
    }

    //==================================================================//
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