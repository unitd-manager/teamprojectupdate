<?
class CP_Www_Widgets_Museum_Booking_View extends CP_Common_Lib_WidgetViewAbstract
{
    var $jssKeys = array('jqForm-3.15');

    //========================================================//
    function getWidget1() {

        $text = "
        {$this->getRowsHTML()}
        ";

        return $text;
    }

    //========================================================//
    function getBookingForm() {
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $cpCfg = Zend_Registry::get('cpCfg');

        $facility_availability_id = $fn->getReqParam('facility_availability_id');
        $booking_date = $fn->getReqParam('booking_date');
        
        $captchaText = '';
        if ($cpCfg['w.facility.booking.hideCaptcha']) {
            $captchaText = "
            {$formObj->getCaptchaImage($ln->gd('cp.form.fld.antiSpamCode.lbl'), 'captcha_code')}
            ";
        }

        $c = &$this->controller;

        $formAction = $c->formAction;

        $SQL = "
        SELECT fa.*, f.title, f.chi_title
        FROM facility_availability fa
        LEFT JOIN (facility f) ON (f.facility_id = fa.facility_id)
        WHERE fa.facility_availability_id = {$facility_availability_id}
          AND f.published = 1
        ";        

        $facilityRow = $fn->getRecordBySQL($SQL);     
        
        if(!is_array($facilityRow)){
            return 'Invalid Access!';
        }

        $text = "
        <form name='bookingForm' id='bookingForm' class='yform columnar' method='post' action='{$c->formAction}'>
            <h3>{$ln->gfv($facilityRow, 'title')}</h3>
            <h4>{$ln->gd('m.museum.facility.bookingForm.bookingDate')}: {$booking_date}</h4>
            <h4>{$ln->gd('m.museum.facility.bookingForm.timeSlot')}:{$facilityRow['from_time']} - {$facilityRow['to_time']}<h4>
            <fieldset>
                {$formObj->getTBRow($ln->gd('cp.form.fld.firstName.lbl'), 'first_name')}
                {$formObj->getTBRow($ln->gd('cp.form.fld.lastName.lbl'), 'last_name')}
                {$formObj->getTBRow($ln->gd('cp.form.fld.organisation.lbl'), 'organisation')}
                {$formObj->getTBRow($ln->gd('cp.form.fld.email.lbl'), 'email')}
                {$formObj->getTBRow($ln->gd('cp.form.fld.phone.lbl'), 'phone')}
                {$formObj->getTARow($ln->gd('cp.form.fld.comments.lbl'), 'comments')}
      	    	{$captchaText}
                <input type='hidden' name='facility_availability_id' value='{$facility_availability_id}' />    
                <input type='hidden' name='booking_date' value='{$booking_date}' />    
                <input type='submit' name='x_submit' class='submithidden' />
            </fieldset>
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getSpExhibitBookingForm($facility_id) {
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $tv = Zend_Registry::get('tv');
        $formObj = Zend_Registry::get('formObj');
        $cpCfg = Zend_Registry::get('cpCfg');
        $formObj->mode = 'editable';

        $formAction = "/index.php?widget=museum_booking&_spAction=spExhibitBookingSubmit&showHTML=0";

        $infoText = '';

        $captchaText = '';
        if (!$cpCfg['m.webBasic.contactUs.hideCaptcha']) {
            $captchaText = "
            {$formObj->getCaptchaImage($ln->gd('cp.form.fld.antiSpamCode.lbl'), 'captcha_code')}
            ";
        }

        $cancelButton = '';
        if (!$cpCfg['m.webBasic.contactUs.hideCancelButton']) {
            $cancelButton = "
            <input type='reset'value='{$ln->gd('cp.form.btn.cancel')}' onclick='history.back()'/>
            ";
        }

        $modVl = getCPModuleObj('core_valuelist');
        $expVl = array('sqlType' => 'TwoFields', 'required' => 1);

        $sqlOrganisationType = $modVl->model->getValuelistSQL('organisationType', array('useEngValueAsKey' => 1 ));
        $organisation_type  = $fn->getReqParam('organisation_type');

        $organisationType = "
        {$formObj->getDDRowBySQL($ln->gd('m.museum.facility.bookingForm.organisationType'), 'organisation_type',
                                 $sqlOrganisationType, $organisation_type, $expVl)}
        ";

        $sqlVisitType = $modVl->model->getValuelistSQL('visitType', array('useEngValueAsKey' => 1 ));
        $visit_type  = $fn->getReqParam('visit_type');

        $visitType = "
        {$formObj->getDDRowBySQL($ln->gd('m.museum.facility.bookingForm.visitType'), 'visit_type',
                                 $sqlVisitType, $visit_type, $expVl)}
        ";

       
        $expVisitDate['dateFormat'] = 'yy-mm-dd';
        $expVisitDate['minDate'] = date('Y-m-d');
        $expVisitDate['required'] = 1;

        $expPreVisitDate = $expVisitDate;
        $expPreVisitDate['required'] = 0;

        $expApplnDate = $expPreVisitDate;
        $expApplnDate['isEditable'] = 0;

        $expYesNo['yesText'] = $ln->gd('cp.form.fld.yes.lbl');
        $expYesNo['noText'] = $ln->gd('cp.form.fld.no.lbl');

        $expSmallField['fieldCls'] = 'smallField';
        $expSmallField['required'] = 0;
        $expReq['required'] = 1;        

        $uploadify = $formObj->getUploadifyObj('museum_booking', 'attachment', 0);

        $text = "
        <form id='spExBookingForm' class='yform ym-form columnar ym-columnar cpJqForm' method='post' action='{$formAction}' enctype='multipart/form-data'>
            <input type='hidden' name='successMsg' value='{$ln->gd('m.museum.facility.form.spExhibitBooking.message.success')}' />
            <fieldset>
                {$formObj->getDateRow($ln->gd('m.museum.facility.bookingForm.applicationDate'), 'temp_application_date',  date('Y-m-d'), $expApplnDate)}
                {$formObj->getDateRow($ln->gd('m.museum.facility.bookingForm.visitDate'), 'booking_date', '', $expVisitDate )}
                {$formObj->getTBRow($ln->gd('m.museum.facility.bookingForm.organisation'), 'organisation', '', $expReq)}
                {$organisationType}
                
                {$formObj->getUploadRow($ln->gd('m.museum.facility.bookingForm.proofOfRegisteredCharity'), 'attFile')}

                {$formObj->getTimeRow($ln->gd('m.museum.facility.bookingForm.arrivalTime'), 'from_time', '', $expReq)}
                {$formObj->getTimeRow($ln->gd('m.museum.facility.bookingForm.departureTime'), 'to_time', '', $expReq)}
                {$formObj->getTBRow($ln->gd('m.museum.facility.bookingForm.contactPerson'), 'first_name', '', $expReq)}
                {$formObj->getTBRow($ln->gd('cp.form.fld.phone.lbl'), 'phone', '', $expReq)}
                {$formObj->getTBRow($ln->gd('cp.form.fld.fax.lbl'), 'fax')}
                {$formObj->getTBRow($ln->gd('cp.form.fld.email.lbl'), 'email', '', $expReq)}

                {$formObj->getTBRow($ln->gd('m.museum.facility.bookingForm.numberOfVisitors') . '*', 'number_of_visitor', '', $expSmallField)}
                {$formObj->getTBRow($ln->gd('m.museum.facility.bookingForm.numberOfStudents'). '*', 'number_of_students', '', $expSmallField)}
                {$formObj->getTBRow($ln->gd('m.museum.facility.bookingForm.numberOfAdults'). '*', 'number_of_adults', '', $expSmallField)}
                {$formObj->getTBRow($ln->gd('m.museum.facility.bookingForm.numberOfElderly'). '*', 'number_of_elderly', '', $expSmallField)}
                {$formObj->getTBRow($ln->gd('m.museum.facility.bookingForm.numberOfYouth'). '*', 'number_of_youth', '', $expSmallField)}

                {$formObj->getTBRow($ln->gd('m.museum.facility.bookingForm.groupLeader'), 'group_leader')}
                {$formObj->getTBRow($ln->gd('m.museum.facility.bookingForm.groupLeaderMobile'), 'group_leader_mobile')}
                {$formObj->getYesNoRRow($ln->gd('m.museum.facility.bookingForm.likeToPreArrangeVisit'), 'pre_visit', 0, $expYesNo)}
                {$formObj->getDateRow('&nbsp; *', 'date_pre_visit', '', $expPreVisitDate )}
                {$formObj->getTBRow($ln->gd('m.museum.facility.bookingForm.disabilityRequirement'), 'disability_requirement')}
                {$visitType}

                {$captchaText}
                <p>{$ln->gd('cp.form.mandatoryInfo')}</p>
                <div class='type-button'>
                    <div class='floatbox'>
                        <div class='float_left btnSubmit'>
                            <input type='submit' value='{$ln->gd('cp.form.btn.submit')}'/>
                        </div>
                        <div class='float_left btnReset'>
                            {$cancelButton}
                        </div>
                    </div>
                </div>
                <input type='hidden' name='facility_id' value='{$facility_id}' />
                <input type='submit' name='x_submit' class='submithidden' />
            </fieldset>
        </form>
        <script>
            $(function(){
                cpw.museum.booking.init();
            });
        </script>          
        ";

        return $text;
    }

    /**
     *
     */
    function getVenueHireForm($facility_id) {
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $tv = Zend_Registry::get('tv');
        $formObj = Zend_Registry::get('formObj');
        $cpCfg = Zend_Registry::get('cpCfg');
        $formObj->mode = 'editable';

        $formAction = "/index.php?widget=museum_booking&_spAction=venueHireSubmit&showHTML=0";

        $infoText = '';

        $captchaText = '';
        if (!$cpCfg['m.webBasic.contactUs.hideCaptcha']) {
            $captchaText = "
            {$formObj->getCaptchaImage($ln->gd('cp.form.fld.antiSpamCode.lbl'), 'captcha_code')}
            ";
        }

        $cancelButton = '';
        if (!$cpCfg['m.webBasic.contactUs.hideCancelButton']) {
            $cancelButton = "
            <input type='reset'value='{$ln->gd('cp.form.btn.cancel')}' onclick='history.back()'/>
            ";
        }

        $modVl = getCPModuleObj('core_valuelist');
        $expVl = array('sqlType' => 'TwoFields', 'required' => 1);

        $sqlInterestedVenue = $modVl->model->getValuelistSQL('interestedVenue', array('useEngValueAsKey' => 1 ));
        $visit_type  = $fn->getReqParam('visit_type');

        $interestedVenue = "
        {$formObj->getCheckBoxArrRowBySQL($ln->gd('m.museum.facility.venueHireForm.interestedVenue')  . '<span class=required ym-required>*</span>', 'interested_venue[]',
                                 $sqlInterestedVenue, '', $expVl)}
        ";

       
        $expVisitDate['dateFormat'] = 'yy-mm-dd';
        $expVisitDate['minDate'] = date('Y-m-d');
        $expVisitDate['required'] = 1;

        $expYesNo['yesText'] = $ln->gd('cp.form.fld.yes.lbl');
        $expYesNo['noText'] = $ln->gd('cp.form.fld.no.lbl');

        $expSmallField['fieldCls'] = 'smallField';
        $expSmallField['required'] = 1;
        $expReq['required'] = 1;        


        CP_Common_Lib_Registry::arrayMerge('jssKeys', array('jqUITimePickerAddon-0.9.3'));
        $text = "
        <form id='venueHireForm' class='yform ym-form columnar ym-columnar cpJqForm' method='post' action='{$formAction}'>
            <input type='hidden' name='successMsg' value='{$ln->gd('m.museum.facility.form.spExhibitBooking.message.success')}' />
            <fieldset>
                {$formObj->getTBRow($ln->gd('m.museum.facility.venueHireForm.contactPerson'), 'first_name', '', $expReq)}
                {$formObj->getTBRow($ln->gd('m.museum.facility.venueHireForm.companyName'), 'organisation', '', $expReq)}
                {$formObj->getTBRow($ln->gd('cp.form.fld.phone.lbl'), 'phone', '', $expReq)}
                {$formObj->getTBRow($ln->gd('cp.form.fld.email.lbl'), 'email', '', $expReq)}
                {$formObj->getDateRow($ln->gd('m.museum.facility.venueHireForm.hireDate'), 'booking_date', '', $expVisitDate )}

                <div class='type-text ym-fbox-text row_from_time editable'>
                    <label for='fld_from_time'>{$ln->gd('m.museum.facility.venueHireForm.hireTime')} <span class='required ym-required'>*</span></label>
                    <input type='text' aria-required='true' required='require' value='' id='fld_from_time' name='from_time' class='fld_time'>      
                     - <input type='text' aria-required='true' required='require' value='' id='fld_to_time' name='to_time' class='fld_time'>      
                </div>

                {$formObj->getTBRow($ln->gd('m.museum.facility.venueHireForm.eventName'), 'event_name', '', $expReq)}
                {$formObj->getTBRow($ln->gd('m.museum.facility.venueHireForm.eventNature'), 'event_nature', '', $expReq)}
                {$interestedVenue}
                {$formObj->getTBRow('&nbsp;', 'interested_venue_other', '')}
                {$formObj->getTBRow($ln->gd('m.museum.facility.venueHireForm.hireHours'), 'hire_hours', '', $expSmallField)}
                {$formObj->getTBRow($ln->gd('m.museum.facility.venueHireForm.anticipatedNoOfParticipants') . '<span class=required ym-required>*</span>', 'number_of_visitor', '', $expSmallField)}

                {$captchaText}
                <p>{$ln->gd('cp.form.mandatoryInfo')}</p>
                <div class='type-button'>
                    <div class='floatbox'>
                        <div class='float_left btnSubmit'>
                            <input type='submit' value='{$ln->gd('cp.form.btn.submit')}'/>
                        </div>
                        <div class='float_left btnReset'>
                            {$cancelButton}
                        </div>
                    </div>
                </div>
                <input type='hidden' name='facility_id' value='{$facility_id}' />
                <input type='submit' name='x_submit' class='submithidden' />
            </fieldset>
        </form>
        <script>
            $(function(){
                cpw.museum.booking.init();
            });
        </script>          
        ";

        return $text;
    }    

}