<?
class CP_Admin_Modules_Crm_Ideas_View extends CP_Common_Lib_ModuleViewAbstract
{
    //==================================================================//
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $db = Zend_Registry::get('db');
        $pager = Zend_Registry::get('pager');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $modulesArr = Zend_Registry::get('modulesArr');
        $mediaArray = Zend_Registry::get('mediaArray');
        
        $rows  = "";
        $rowCounter = 0;

        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){
            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['title'])}
            {$listObj->getListDataCell($row['staff_name'])}
            {$listObj->getListDataCell($row['record_type'])}
            {$listObj->getListDataCell($row['status'])}
            {$listObj->getListDataCell($row['by_when'])}
            {$listObj->getListDateCell($row['creation_date'])}
            {$listObj->getListDataCell($row['created_by'])}
            {$listObj->getListRowEnd($row['ideas_id'])}
            ";
            $rowCounter++;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Title', 'i.title')}
        {$listObj->getListHeaderCell('Key Staff', 'staff_name')}
        {$listObj->getListHeaderCell('Type', 'i.record_type')}
        {$listObj->getListHeaderCell('Status', 'i.status')}
        {$listObj->getListHeaderCell('By When', 'i.by_when')}
        {$listObj->getListHeaderCell('Creation Date', 'i.creation_date')}
        {$listObj->getListHeaderCell('Created By', 'i.created_by')}
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

        $text = '';

        $expStaffName    = array('detailValue' => $row['staff_name']);
        $fnsModStaff     = includeCPClass('ModuleFns', 'core_staff');
        $sqlStaffName    = $fnsModStaff->getStaffByGroupSQL();

        $sqlStatus = $fn->getValueListSQL('ideasStatus');
        $sqlByWhen = $fn->getValueListSQL('ideasByWhen');
        $sqlType   = $fn->getValueListSQL('ideasType');
        $exp = array('sqlType' => 'OneField');

        $fieldset1 = "
        {$formObj->getTBRow('Title', 'title', $row['title'])}
        {$formObj->getDDRowBySQL('Key Staff', 'staff_id', $sqlStaffName, $row['staff_id'], $expStaffName)}
        {$formObj->getDDRowBySQL('Type', 'record_type', $sqlType, $row['record_type'], $exp)}
        {$formObj->getDDRowBySQL('Status', 'status', $sqlStatus, $row['status'], $exp)}
        {$formObj->getDDRowBySQL('By When', 'by_when', $sqlByWhen, $row['by_when'], $exp)}
        ";
        
        $fieldset2 = $formObj->getHTMLEditor('Description', 'description', $ln->gfv($row, 'description', '0'));

        $text = "
        {$formObj->getFieldSetWrapped('Content Details', $fieldset1)}
        {$formObj->getFieldSetWrapped('Description', $fieldset2)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    //========================================================//
    //==================================================================//
    //==================================================================//
    function getRightPanel($row){
        $media = Zend_Registry::get('media');
        $fn = Zend_Registry::get('fn');
        $comment = getCPPluginObj('common_comment');
        
        $record_id = $fn->getIssetParam($row, 'ideas_id');

        $text = "
        {$media->getRightPanelMediaDisplay('Attachments', 'crm_ideas', 'attachment', $row)}
        {$comment->getView(array(
             'roomName' => 'crm_ideas'
            ,'recordId' => $record_id
        ))}
        ";

        return $text;
    }

    //==================================================================//
    /**
     *
     */
    function getQuickSearch() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');

        $record_type = $fn->getReqParam('record_type');
        $status      = $fn->getReqParam('status');
        $by_when     = $fn->getReqParam('by_when');

        $sqlType   = $fn->getValueListSQL('ideasType');
        $sqlStatus = $fn->getValueListSQL('ideasStatus');
        $sqlByWhen = $fn->getValueListSQL('ideasByWhen');

        $text = "
        <td>
            <select name='record_type'>
                <option value=''>Type</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $sqlType, $record_type)}
            </select>
        </td>
        <td>
            <select name='status'>
                <option value=''>Status</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $sqlStatus, $status)}
            </select>
        </td>
        <td>
            <select name='by_when'>
                <option value=''>By When</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $sqlByWhen, $by_when)}
            </select>
        </td>
        ";

        
        return $text;
    }
}