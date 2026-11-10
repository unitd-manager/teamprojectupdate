<?
class CP_Www_Modules_Museum_Facility_Controller extends CP_Common_Modules_Museum_Facility_Controller
{
    /**
     *
     */
    function getController() {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        $text = '';
		if ($tv['secType'] == 'Booking:Special Exhibition' 
			|| $tv['catType'] == 'Booking:Special Exhibition' 
			|| $tv['subCatType'] == 'Booking:Special Exhibition') {
	        $text = $this->getDetail('SpecialExhibitionBooking');

        } else if ($tv['catType'] == 'Venue Hire Form' || $tv['subCatType'] == 'Venue Hire Form') {
            $text = $this->getDetail('venueHireForm');

        } else if ($tv['secType'] == 'Site Map' || $tv['catType'] == 'Site Map') {
            $text = $this->getList('siteMap');

        } else {
            $fnName = $fn->getFnNameByAction();
            $text = $this->$fnName();
        }

        return $text;
    }	
}