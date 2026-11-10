<?
class CP_Www_Widgets_Common_City_Functions
{
    //==================================================================//
    function setWidgetArray($widgets){
        $widgetObj = $widgets->getWidgetObj('common_city');
    }

    /**
     *
     */
    function setCityIdInSession(){
        $cpUrl = Zend_Registry::get('cpUrl');
        $fn = Zend_Registry::get('fn');

        $city_id = '';
        $city_id_req = $fn->getReqParam('cpCityId');

        if ($city_id_req != ''){
            $city_id = $city_id_req;

        } else if (isset($_SESSION['cpCityId'])){
            $city_id = $_SESSION['cpCityId'];
        } else {
            $rec = $fn->getRecordByCondition('city', 'default_city=1');
            if(is_array($rec)){
                $city_id = $rec['city_id'];
            }
        }

        if ($city_id != ''){
            $rec = $fn->getRecordRowByID('city', 'city_id', $city_id);
            $_SESSION['cpCityId']    = $city_id;
            $_SESSION['cpCityCode']  = $rec['city_code'];
            $_SESSION['cpCityTitle'] = $rec['title'];
        }
    }
}
