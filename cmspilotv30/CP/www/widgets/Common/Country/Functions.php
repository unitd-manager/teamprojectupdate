<?
class CP_Www_Widgets_Common_Country_Functions
{
    //==================================================================//
    function setWidgetArray($widgets){
        $widgetObj = $widgets->getWidgetObj('common_country');
    }

    /**
     *
     */
    function setCountryIdInSession(){
        $cpUrl = Zend_Registry::get('cpUrl');
        $fn = Zend_Registry::get('fn');
        
        $country_id = '';
        $country_id_req = $fn->getReqParam('cp_country_id');
        $countryCodeReq = $fn->getReqParam('countryCodeReq');
        
        
        if ($countryCodeReq != ''){
            $rec = $fn->getRecordByCondition('country', "country_code='{$countryCodeReq}'");
            if(is_array($rec)){
                $country_id = $rec['country_id'];
            }
        } else if ($country_id_req != ''){
            $country_id = $country_id_req;

        } else if (isset($_SESSION['cp_country_id']) && $_SESSION['cp_country_id'] != ''){
            $country_id = $_SESSION['cp_country_id'];
        } else {
            $rec = $fn->getRecordByCondition('country', 'default_country=1');
            if(is_array($rec)){
                $country_id = $rec['country_id'];
            }
        }
        
        if ($country_id != ''){
            $rec = $fn->getRecordRowByID('country', 'country_id', $country_id);
            $_SESSION['cp_country_id']    = $country_id;
            $_SESSION['cpCountryCode']  = $rec['country_code'];
            $_SESSION['cpCountryTitle'] = $rec['title'];
            $_SESSION['cpCountryCurrency'] = isset($rec['currency']) ? $rec['currency'] : '';
        }
    }
}
