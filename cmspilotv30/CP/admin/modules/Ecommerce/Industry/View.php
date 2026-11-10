<?
class CP_Admin_Modules_Ecommerce_Industry_View extends CP_Common_Modules_Ecommerce_Industry_View
{
    /**
     *
     */
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');

        $rows  = "";
        $rowCounter = 0;

        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){

            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['title'])}
            {$listObj->getListSortOrderField($row, 'industry_id')}
            {$listObj->getListDataCell($row['division_title'])}
            {$listObj->getListDataCell($row['industry_id'], 'center')}
            {$listObj->getListPublishedImage($row['published'], $row['industry_id'])}
            {$listObj->getListRowEnd($row['industry_id'])}
            ";
            $rowCounter++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Title', 'i.title')}
        {$listObj->getListSortOrderImage()}
        {$listObj->getListHeaderCell('Admin Email', 'd.division_title')}
        {$listObj->getListHeaderCell('ID', 'i.division_id', 'headerCenter')}
        {$listObj->getListHeaderCell('Published', 'i.published', 'headerCenter')}
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
        $ln = Zend_Registry::get('ln');

        $fieldset = "
        {$formObj->getTextBoxRow('Title', 'title')}
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
        $cpCfg = Zend_Registry::get('cpCfg');
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');

        $modSec      = getCPModuleObj('ecommerce_division');
        $sqlDivision = $modSec->model->getSQL();
        $expDivision  = array('detailValue' => $row['division_title']);

        $fieldset1 = "
        {$formObj->getTextBoxRow('Title', 'title', $ln->gfv($row, 'title', '0'))}
        {$formObj->getDDRowBySQL('Division', 'division_id', $sqlDivision, $row['division_id'], $expDivision)}
        {$formObj->getYesNoRRow('Published', 'published', $row['published'])}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Distributor Details', $fieldset1)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    /**
     *
     */
    function getQuickSearch() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $fn = Zend_Registry::get('fn');

        $division_id = $fn->getReqParam('division_id');
        $sqlDivision = $fn->getDDSQL('ecommerce_division');

        $text = "
        <td>
            <select name='division_id'>
                <option value=''>Division</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlDivision, $division_id)}
            </select>
        </td>
        ";

        return $text;
    }
    
    /**
     *
     */
    function getRightPanel($row) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $ln = Zend_Registry::get('ln');
        $media = Zend_Registry::get('media');

        $text = "
        {$media->getRightPanelMediaDisplay('Picture', 'ecommerce_industry', 'picture', $row)}
        ";

        return $text;
    }    
}