<?
class CP_Admin_Modules_Event_Fixture_View extends CP_Common_Lib_ModuleViewAbstract
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
            {$listObj->getListDataCell($row['category_title'])}
            {$listObj->getListDataCell($row['status'])}
            {$listObj->getListDataCell($row['venue_title'])}
            {$listObj->getListDateCell($row['date'])}
            {$listObj->getListDataCell($row['category_id'], 'center')}
            {$listObj->getListPublishedImage($row['published'], $row['fixture_id'])}
            {$listObj->getListRowEnd($row['fixture_id'])}            
            ";
            $rowCounter++;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Title', 'f.title')}
        {$listObj->getListHeaderCell('Category', 'category_title')}
        {$listObj->getListHeaderCell('Status', 'f.status')}
        {$listObj->getListHeaderCell('Venue', 'venue_title')}
        {$listObj->getListHeaderCell('Date', 'f.date')}
        {$listObj->getListHeaderCell('ID', 'f.fixture_id', 'headerCenter')}        
        {$listObj->getListHeaderCell('Published', 'f.published', 'headerCenter')}
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
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');
        $ln = Zend_Registry::get('ln');

        $formObj->mode = $tv['action'];

        $expCat    = array('detailValue' => $row['category_title']);
        $expSubCat = array('detailValue' => $row['sub_category_title']);
        $expVenue  = array('detailValue' => $row['venue_title']);

        $sqlCategory = "
        SELECT c.category_id
              ,c.title 
        FROM category c
        JOIN section s ON (s.section_id = c.section_id)
        WHERE s.section_type = 'Fixture'
        ";

        $sqlVenue = "
        SELECT venue_id
               ,title
        FROM venue 
        ORDER BY title
        ";

        $fnModSubCat = includeCPClass('ModuleFns', 'webBasic_subCategory');
        $sqlSubCat = $fnModSubCat->getSubCategorySQL($row['category_id']);

        $exp = array('sqlType' => 'OneField');
        $sqlStatus = $fn->getValueListSQL('status');

        $fieldset1 = "
        {$formObj->getTBRow('Title', 'title', $row['title'])}
        {$formObj->getDDRowBySQL('Category', 'category_id', $sqlCategory, $row['category_id'], $expCat)}
        {$formObj->getDDRowBySQL('Sub Category', 'sub_category_id', $sqlSubCat, $row['sub_category_id'], $expSubCat)}
        {$formObj->getDDRowBySQL('Status', 'status', $sqlStatus, $row['status'], $exp)}
        {$formObj->getTBRow('Team', 'opposite_team', $row['opposite_team'], $exp)}
        {$formObj->getDDRowBySQL('Venue', 'venue_id', $sqlVenue, $row['venue_id'], $expVenue)}
        {$formObj->getDateRow('Date', 'date', $row['date'])}
        {$formObj->getTBRow('Time', 'time', $row['time'])}
        {$formObj->getTBRow('Result', 'result', $row['result'])}
        {$formObj->getYesNoRRow('Published', 'published', $row['published'] )} 
        {$formObj->getYesNoRRow('Latest', 'latest', $row['latest'] )} 
        ";      
          
        $fieldset2 = $formObj->getHTMLEditor('Description', 'description', $ln->gfv($row, 'description', '0'));   
        $fieldset3 = $formObj->getHTMLEditor('Match Report', 'match_report', $ln->gfv($row, 'match_report', '0'));   
        
        $text = "
        {$formObj->getFieldSetWrapped('Fixture Details', $fieldset1)}
        {$formObj->getFieldSetWrapped('Description', $fieldset2)}
        {$formObj->getFieldSetWrapped('Match Report', $fieldset3)}
        
        ";

        return $text;
    }

    //========================================================//
    //==================================================================//
    //==================================================================//
    function getRightPanel($row){
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $media = Zend_Registry::get('media');

        $links = "";

        if($cpCfg['showContactInFixture'] == 1){
            $links .= $displayLinkData->getLinkPortalMain("event_fixture", "event_fixtureContactLink", "Contacts Linked", $row);
        }
         
        $text = "
        {$media->getRightPanelMediaDisplay('Picture', 'event_fixture', 'picture', $row)}
        {$media->getRightPanelMediaDisplay('Related Pictures', 'event_fixture', 'relatedPicture', $row)}
        {$links}
        ";

        return $text;
    }

    //==================================================================//
    /**
     *
     */
    function getQuickSearch() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $am = Zend_Registry::get('am');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        $category_id    = $fn->getReqParam('category_id');
        $status         = $fn->getReqParam('status');
        $sqlStatus      = $fn->getValueListSQL('status');
        $interestText   = "";
        
        //==================================================================//
        $spArray = array(
              "Match Report"
             ,"To Play"
        );

        $sqlCombo = "
        SELECT category_id
              ,title 
        FROM category a              
        WHERE a.section_id = '3'           
        ORDER BY title            
        ";
        
        $staff = "
        <td>
            <select name='staff_id'>
                <option value=''>Staff</option>
            </select>
        </td>
        ";

        $text = "
        {$interestText}
        <td>
            <select name='status'>
                <option value=''>Status</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $sqlStatus, $status)}
            </select>
        </td>
        <td>
            <select name='category_id' >
                <option value=''>Category</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlCombo, $category_id)}
            </select>
        </td>
        ";

        
        return $text;
    }
}