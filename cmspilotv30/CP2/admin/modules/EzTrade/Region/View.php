<?
class CP_Admin_Modules_EzTrade_Region_View extends CP_Common_Lib_ModuleViewAbstract
{
    /**
     *
     */
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $fn = Zend_Registry::get('fn');

        $count   = 0;
        $rows    = '';

        foreach ($dataArray as $row){
            $rows .= "
            {$listObj->getListRowHeader($row, $count)}
            {$listObj->getGoToDetailText($count, $row['region_code'])}
            {$listObj->getListDataCell($row['region_name'])}
            {$listObj->getListDataCell($row['agent_name'])}
            {$listObj->getListDataCell($row['status'])}
            {$listObj->getListRowEnd($row['region_id'])}
            ";

            $count++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Region Code', 'r.region_code')}
        {$listObj->getListHeaderCell('Region Name', 'r.region_name')}
        {$listObj->getListHeaderCell('Agent Name', 'r.agent_name')}
        {$listObj->getListHeaderCell('Status', 'r.status')}
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
        {$formObj->getTBRow('Region Code', 'region_code')}
        {$formObj->getTBRow('Region Name', 'region_name')}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Region Details', $fieldset)}
        ";

        return $text;
    }

    /**
     *
     */
    function getEdit($row){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $fieldset = "
        {$formObj->getTBRow('Region Code', 'region_code', $row['region_code'])}
        {$formObj->getTBRow('Region Name', 'region_name', $row['region_name'])}
        {$formObj->getTBRow('Agent Name', 'agent_name', $row['agent_name'])}
        {$formObj->getTBRow('Status', 'status', $row['status'])}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Region Details', $fieldset)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    /**
     *
     */
    function getRightPanel($row){
        $fn = Zend_Registry::get('fn');
        $comment = getCPPluginObj('common_comment');
       
        $record_id = $fn->getIssetParam($row, 'region_id');

        $text = "
        {$comment->getView(array(
             'roomName' => 'ezTrade_currencyRate'
            ,'recordId' => $record_id
        ))}
        ";

        return $text;
    }

    /**
     *
     */
    function getQuickSearch() {
        $cpUtil = Zend_Registry::get('cpUtil');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $status       = $fn->getReqParam('status');

        $text = "
        <td>
            <select name='status'>
                <option value=''>Status</option>
                {$cpUtil->getDropDown1($cpCfg['m.trading.rfq.statusArr'], $status)}
            </select>
        </td>
        ";

        return $text;
    }
}