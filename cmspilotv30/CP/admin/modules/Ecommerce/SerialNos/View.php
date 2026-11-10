<?
class CP_Admin_Modules_Ecommerce_SerialNos_View extends CP_Common_Lib_ModuleViewAbstract
{
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        $rows  = "";
        $rowCounter = 0;

        foreach ($dataArray as $row){
            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['product_title'])}
            {$listObj->getGoToDetailText($rowCounter, $row['product_code'])}
            {$listObj->getListDataCell($row['product_serial'])}
            {$listObj->getListDataCell($row['status'])}
            {$listObj->getListDataCell($fn->getYesNo($row['registered']), 'center')}
            {$listObj->getListRowEnd($row['serial_nos_id'])}
            ";
            $rowCounter++;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Product ID', 's.product_title')}
        {$listObj->getListHeaderCell('Product Code', 's.product_code')}
        {$listObj->getListHeaderCell('Product Serial', 's.product_serial')}
        {$listObj->getListHeaderCell('Status', 's.status')}
        {$listObj->getListHeaderCell('Subscribed', 's.registered', 'headerCenter')}
        {$listObj->getListHeaderEnd()}
        {$rows}
        {$listObj->getListFooter()}
        ";

        return $text;
    }

    /**
     *
     */
    function getPrint($result){
        return $this->getList($result);
    }

    /**
     *
     */
    function getNew(){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');

        $fieldset = "
        {$formObj->getDDRowBySQL('Product', 'product_id', $fn->getDdSql('ecommerce_product'))}
        {$formObj->getTBRow('Product Code', 'product_code')}
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
        $tv = Zend_Registry::get('tv');
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');

        $expProd = array('detailValue' => $row['product_title']);

        $expVl = array('sqlType' => 'OneField');

        $fieldset1 = "
        {$formObj->getDDRowBySQL('Product', 'product_id', $fn->getDdSql('ecommerce_product'), $row['product_id'], $expProd)}
        {$formObj->getTBRow('Product Code', 'product_code', $row['product_code'])}
        {$formObj->getTBRow('Product Serial', 'product_serial', $row['product_serial'])}
        {$formObj->getYesNoRRow('Registered', 'registered', $row['registered'])}
        {$formObj->getDDRowByVL('Status', 'status', 'serialNoStatus', $row['status'], $expVl)}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Serial Nos Details', $fieldset1)}
        {$formObj->getCreationModificationText2($row)}
        ";

        return $text;
    }

    /**
     *
     */
    function getRightPanel($row){
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        
        $text = '';

        if ($cpCfg['m.ecommerce.serialNos.showComments']) {
            $comment = getCPPluginObj('common_comment');
            $record_id = $fn->getIssetParam($row, 'serial_nos_id');
            $text .= "
            {$comment->getView(array(
                 'roomName' => 'ecommerce_serialNos'
                ,'recordId' => $record_id
            ))}
            ";
        }

        return $text;
    }
    
    /**
     *
     */
    function getQuickSearch() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');        
        $fn = Zend_Registry::get('fn');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        $special_search  = $fn->getReqParam('special_search');
        $product_id = $fn->getReqParam('product_id');
        $product_code = $fn->getReqParam('product_code');
        $sqlProduct = $fn->getDDSQL('ecommerce_product');
        $status = $fn->getReqParam('status');
        
        $sqlCode = '';
        if ($product_id != ''){
            $sqlCode = $fn->getSQL("
                SELECT DISTINCT product_code
                FROM serial_nos
                "
                ,array(
                    "product_id = '{$product_id}'"
                )
                ,array(
                     'orderBy' => 'product_code'
                )
            );
        }
        
        $text = "
        <td>
            <select name='product_id'>
                <option value=''>Product</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlProduct, $product_id)}
            </select>
        </td>
        <td>
            <select name='product_code'>
                <option value=''>Product Code (SKU)</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $sqlCode, $product_code)}
            </select>
        </td>
        <td>
            <select name='status'>
                <option value=''>Status</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $fn->getValueListSQL('serialNoStatus'), $status)}
            </select>
        </td>
        <td>
            <select name='special_search'>
                <option value=''>Special Search</option>
                {$cpUtil->getDropDown1($cpCfg['m.ecommerce.serialNos.specialSearchArr'], $special_search)}
            </select>
        </td>
        ";

        
        return $text;
    }
}