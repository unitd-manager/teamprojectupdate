<?
class CP_Admin_Modules_Ecommerce_WishList_View extends CP_Common_Lib_ModuleViewAbstract
{
    //==================================================================//
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $db = Zend_Registry::get('db');
        
        $rows    = '';
        $count   = 0;

        foreach ($dataArray as $row){
            $rows .= "
            {$listObj->getListRowHeader($row, $count)}
            {$listObj->getGoToDetailText($count, $row['wish_list_name'])}           
            {$listObj->getListDataCell($row['contact_name'])}
            {$listObj->getListDataCell($row['product_name'])}
            {$listObj->getListDateCell($row['creation_date'])}
            {$listObj->getListDataCell($row['wish_list_id'])}
            {$listObj->getListRowEnd($row['wish_list_id'])}
            ";
            $count ++;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Name', 'w.wish_list_name')}
        {$listObj->getListHeaderCell('Contact', 'w.contact_id')}
        {$listObj->getListHeaderCell('Product', 'w.product_id')}
        {$listObj->getListHeaderCell('Wishlist Date', 'w.creation_date')}
        {$listObj->getListHeaderCell('ID', 'w.wish_list_id', 'headerCenter')}        
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
        {$formObj->getTBRow('Name', 'wish_list_name')}
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
        $tv = Zend_Registry::get('tv');
        $formObj = Zend_Registry::get('formObj');
        
        $formObj->mode = $tv['action'];

        $fnModProduct = includeCPClass('ModuleFns', 'ecommerce_product');

        $expContact = array('detailValue' => $row['contact_name']);
        $modContact = getCPModuleObj('common_contact');
        $sqlContact = $modContact->model->getContactSQL();

        $expProduct = array('detailValue' => $row['product_name']);
        $modContact = getCPModuleObj('ecommerce_product');
        $sqlProduct = $modContact->model->getProductSQL();
                    
        $fieldset1 = "
        {$formObj->getTBRow('Name', 'wish_list_name', $row['wish_list_name'])}
        {$formObj->getDDRowBySQL('Contact', 'contact_id', $sqlContact, $row['contact_id'], $expContact)}
        {$formObj->getDDRowBySQL('Product', 'product_id', $sqlProduct, $row['product_id'], $expProduct)}
        ";      
      
        $text = "
        {$formObj->getFieldSetWrapped('Wishlist Details', $fieldset1)}
        {$formObj->getCreationModificationText($row)}
        ";
        return $text;
    }

    //========================================================//
    //==================================================================//
    //==================================================================//
    function getRightPanel($row){

    }

    //==================================================================//
    }