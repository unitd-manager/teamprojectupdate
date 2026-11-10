<?
class CP_Www_Themes_MuseumMobile_Hooks_ModuleWebBasicHome
{
    /*
     *
     */
    function getList() {
        $wRecord = getCPWidgetObj('content_record');
        $wMobileHome = $wRecord->getWidget(array(
            'contentType'    => 'Mobile: Home'
            ,'showShortDesc' => false
            ,'mediaExp'      => array('limit' => 1, 'folder' => 'normal')
            ,'displayLimit'  => 1
        ));
        
        $mainNav = Zend_Registry::get('mainNav');
        $rows = $mainNav->view->getMenuDataRowsHTML('Top', array('noOfSubCatToDisplay' => 0));
        $text = "
        {$wMobileHome}
        <div class='homeMenu'>
            <ul class='noDefault'>
            {$rows}
            </ul>
        </div>
        ";
        return $text;
    }

    /*
     *
     */
    function getContent($dataArray) {

    }
}