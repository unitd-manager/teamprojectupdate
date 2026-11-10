<?
class CP_Admin_Modules_Pos_Lib_Functions
{
    //==================================================================//
    function setActionsArray($actArray){
        $cpCfg = Zend_Registry::get('cpCfg');
        $arrayMaster = Zend_Registry::get('arrayMaster');
        $tv = Zend_Registry::get('tv');
        $pager = Zend_Registry::get('pager');
        $cpUrl = Zend_Registry::get('cpUrl');

        $searchQueryString = $pager->searchQueryString;
        $searchQueryString = preg_replace('/&_action=[a-zA-Z0-9\. _,]+&?/', "&", $searchQueryString);
        if (substr($searchQueryString, -1) == "&") {
           $searchQueryString = substr($searchQueryString, 0, strlen($searchQueryString)-1);
        }
        $searchQueryString .= $cpUrl->getQnMarkForUrl($searchQueryString);
        //====================== BULK MOVE SUB CATEGORY ================================//
        $actObj = $actArray->getActionObj('bulkMoveSubCat');
        $actArray->registerAction($actObj, array(
             'title' => 'Bulk Move'
            ,'url' => "index.php?module=pos_subCategory&_spAction=bulkMoveToCategory&showHTML=0"
        ));

        //====================== DELIVERY ================================//
        $actObj = $actArray->getActionObj('delivery');
        $actArray->registerAction($actObj, array(
             'title' => 'Delivery'
            ,'url' => "#"
        ));

        //=============== DUPLICATE STAFF =================//
        $url = "index.php?module=pos_staff&_spAction=duplicateStaff&showHTML=0";
        $actObj = $actArray->getActionObj('duplicateStaff');
        $actArray->registerAction($actObj, array(
            'title' => 'Duplicate'
           ,'url' => $url
        ));
   }
}