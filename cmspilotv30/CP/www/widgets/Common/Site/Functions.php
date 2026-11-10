<?
class CP_Www_Widgets_Common_Site_Functions
{
    //==================================================================//
    function setWidgetArray($widgets){
        $widgetObj = $widgets->getWidgetObj('common_country');
    }

    /**
     *
     */
    function setSiteIdInSession(){
        $cpUrl = Zend_Registry::get('cpUrl');
        $fn = Zend_Registry::get('fn');
        
        $site_id = '';
        $site_id_req = $fn->getReqParam('cp_site_id');

        if ($site_id_req != ''){
            $site_id = $site_id_req;

        } else if (isset($_SESSION['cp_site_id'])){
            $site_id = $_SESSION['cp_site_id'];
        } else {
            $rec = $fn->getRecordByCondition('site', 'default_country=1');
            if(is_array($rec)){
                $site_id = $rec['site_id'];
            }
        }
        
        if ($site_id != ''){
            $rec = $fn->getRecordRowByID('site', 'site_id', $site_id);
            $_SESSION['cp_site_id']    = $site_id;
            $_SESSION['cpCountryCode']  = $rec['country_code'];
            $_SESSION['cpSiteTitle'] = $rec['title'];
        }
   }
}
