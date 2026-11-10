<?
class CP_Admin_Modules_LawNews_Reporter_View extends CP_Common_Lib_ModuleViewAbstract
{
    /**
     *
     */
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
            {$listObj->getListDataCell($row['correspondent_title'])}
            {$listObj->getListSortOrderField($row, 'reporter_id')}
            {$listObj->getListDataCell($row['reporter_id'], 'center')}
            {$listObj->getListPublishedImage($row['published'], $row['reporter_id'])}
            {$listObj->getListRowEnd($row['reporter_id'])}            
            ";
            $rowCounter++;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Title', 'r.title')}
        {$listObj->getListHeaderCell('Correspondent', 'r.correspondent_id')}
        {$listObj->getListSortOrderImage('r')}
        {$listObj->getListHeaderCell('ID', 'r.reporter_id', 'headerCenter')}        
        {$listObj->getListHeaderCell('Published', 'r.published', 'headerCenter')}
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
        $formObj = Zend_Registry::get('formObj');
        $ln = Zend_Registry::get('ln');
        
        $sqlCorrespondent = $fn->getDDSql('lawNews_correspondent');
        $expCorres     = array('detailValue' => $row['correspondent_title']);
        
        $fieldset1 = "
        {$formObj->getTBRow('Title', 'title', $ln->gfv($row, 'title', '0'))}
        {$formObj->getDDRowBySQL('Correspondent', 'correspondent_id', $sqlCorrespondent, $row['correspondent_id'], $expCorres)}
        {$formObj->getYesNoRRow('Published', 'published', $row['published'] )} 
        ";      
          
        $fieldset2 = " 
        {$formObj->getHTMLEditor('Description', 'description', $ln->gfv($row, 'description', '0'))}  
        ";
        
        $text = "
        {$formObj->getFieldSetWrapped('Main Details', $fieldset1)}
        {$formObj->getFieldSetWrapped('Description', $fieldset2)}
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
        
        $links = " 
        {$displayLinkData->getLinkPortalMain('lawNews_reporter', 'webBasic_contentLink', 'Content Linked', $row)}
        ";

        $text = "
        {$media->getRightPanelMediaDisplay('Picture', 'lawNews_reporter', 'picture', $row)}
        {$links}
        ";

        return $text;
    }

    /**
     *
     */
    function getQuickSearch() {
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');        
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $special_search  = $fn->getReqParam('special_search');
        $correspondent_id = $fn->getReqParam('correspondent_id');

        $modCorres = getCPModuleObj('lawNews_correspondent');
        $sqlCorres = $modCorres->model->getCorrespondentSQL();

        $text = "
        <td class='fieldValue'>
            <select name='special_search'>
                <option value=''>Special Search</option>
                {$cpUtil->getDropDown1($cpCfg['m.webBasic.content.specialSearchArr'], $special_search)}
            </select>
        </td>
        <td class='fieldValue'>
            <select name='correspondent_id'>
                <option value=''>Correspondent</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlCorres, $correspondent_id)}
            </select>
        </td>
        ";
       
        return $text;
    }
}