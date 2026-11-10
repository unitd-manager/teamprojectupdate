<?
class CP_Admin_Modules_Trading_Catalog_View extends CP_Common_Lib_ModuleViewAbstract{

    function getList($dataArray){
        return getCPViewObj('trading_product')->getList($dataArray);
    }
    
    function getEdit($row){
        return getCPViewObj('trading_product')->getEdit($row);
    }
    
    function getRightPanel($row){
        includeCPClass('Module', 'trading_product', 'Product');
        
        $fn = Zend_Registry::get('fn');
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $media = Zend_Registry::get('media');
        $comment = getCPPluginObj('common_comment');
        $fn = Zend_Registry::get('fn');

        $record_id = $fn->getIssetParam($row, 'product_id');

        $rows = "";
        $rows = "
        {$displayLinkData->getLinkPortalMain('trading_catalog', 'trading_pricingTypeLink', 'Pricing', $row)}
        {$displayLinkData->getLinkPortalMain('trading_product', 'trading_inventoryLink', 'Inventory', $row)}
        ";

        $text = "
        {$media->getRightPanelMediaDisplay('Pictures', 'trading_product', 'picture', $row)}
        {$rows}
        {$comment->getView(array(
             'roomName' => 'trading_product'
            ,'recordId' => $record_id
        ))}
        ";

        return $text;        
    }
    
    function getQuickSearch() {
        return getCPViewObj('trading_product')->getQuickSearch();
    }
    
    
}
