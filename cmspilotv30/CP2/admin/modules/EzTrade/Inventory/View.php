<?
class CP_Admin_Modules_EzTrade_Inventory_View extends CP_Common_Lib_ModuleViewAbstract
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
            {$listObj->getGoToDetailText($count, $row['product_code'])}
            {$listObj->getListDataCell($row['region_name'])}
            {$listObj->getListDataCell($row['agent_name'])}
            {$listObj->getListDataCell($row['product_name'])}
            {$listObj->getListDataCell($row['agent_name'])}
            {$listObj->getListDataCell($row['so_code'])}
            {$listObj->getListDataCell($row['po_code'])}
            {$listObj->getListDataCell($row['agent_name'])}
            {$listObj->getListDataCell($row['agent_name'])}
            {$listObj->getListDataCell($row['status'])}
            {$listObj->getListRowEnd($row['inventory_id'])}
            ";

            $count++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Product Code', 'i.product_code')}
        {$listObj->getListHeaderCell('Serial', 'i.product_name')}
        {$listObj->getListHeaderCell('Collection', 'i.product_name')}
        {$listObj->getListHeaderCell('Product Name', 'i.product_name')}
        {$listObj->getListHeaderCell('Location', 'i.product_name')}
        {$listObj->getListHeaderCell('Sales Order #', 'i.product_name')}
        {$listObj->getListHeaderCell('Purchase Order #', 'i.product_name')}
        {$listObj->getListHeaderCell('Supplier', 'i.agent_name')}
        {$listObj->getListHeaderCell('Client', 'i.agent_name')}
        {$listObj->getListHeaderCell('Status', 'i.status')}
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
        {$formObj->getTBRow('Product Code', 'product_code')}
        {$formObj->getTBRow('Product Name', 'product_name')}
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
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Inventory Details', $fieldset)}
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
       
        $record_id = $fn->getIssetParam($row, 'inventory_id');

        $text = "
        {$comment->getView(array(
             'roomName' => 'ezTrade_inventory'
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