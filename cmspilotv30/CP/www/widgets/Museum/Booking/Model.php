<?
class CP_Www_Widgets_Museum_Booking_Model extends CP_Common_Lib_WidgetModelAbstract
{

    /**
     *
     * @return <type>
     */
    function getBookingFormSubmit() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');
        $ln = Zend_Registry::get('ln');
        $cpCfg = Zend_Registry::get('cpCfg');
        $media = Zend_Registry::get('media');
        $cpUrl = Zend_Registry::get('cpUrl');

        //-------------------------------------------------------------------------------------//        
        if (!$this->getBookingFormSubmitValidate()){
            return $validate->getErrorMessageXML();
        }        

        $facility_availability_id = $fn->getReqParam('facility_availability_id');
        $SQL = "
        SELECT fa.*, f.title
        FROM facility_availability fa
        LEFT JOIN (facility f) ON (f.facility_id = fa.facility_id)
        WHERE fa.facility_availability_id = {$facility_availability_id}
          AND f.published = 1
        ";        
        $facilityRow = $fn->getRecordBySQL($SQL);          
 
        $attRec = $media->model->getFirstMediaRecord('museum_facility', 'attachment', $facilityRow['facility_id']);
        $url    = $cpUrl->getURLPrefix(). "/index.php?plugin=common_media&_spAction=saveMedia&showHTML=0&media_id=";
        $pdfUrl = isset($attRec['media_id']) ? $url . $attRec['media_id'] : "#";

        $fa = array();
        $fa['first_name']    = $fn->getPostParam('first_name');
        $fa['last_name']     = $fn->getPostParam('last_name');
        $fa['organisation']  = $fn->getPostParam('organisation');
        $fa['email']         = $fn->getPostParam('email');
        $fa['phone']         = $fn->getPostParam('phone');
        $fa['comments']      = $fn->getPostParam('comments');
        
        $fa['date']         = $fn->getReqParam('booking_date');
        $fa['facility_id']  = $fn->getIssetParam($facilityRow, 'facility_id');
        $fa['from_time']    = $fn->getIssetParam($facilityRow, 'from_time');
        $fa['to_time']      = $fn->getIssetParam($facilityRow,'to_time');
        $fa['availability'] = "pending";

        $SQL    = $dbUtil->getInsertSQLStringFromArray($fa, 'booking');
        $result = $db->sql_query($SQL);
        $id     = $db->sql_nextid();
        
        //------------------- ADMIN EMAIL ----------------------------------------------//
        $currentDate  = date('d-M-Y l h:i:s A');

        $message = $ln->gd('w.museum.booking.bookingForm.adminEmail.notifyBody');
        $message = str_replace('[[first_name]]', $fa['first_name'], $message);
        $message = str_replace('[[last_name]]', $fa['last_name'], $message);
        $message = str_replace('[[organisation]]', $fa['organisation'], $message);
        $message = str_replace('[[email]]', $fa['email'], $message);
        $message = str_replace('[[phone]]', $fa['phone'], $message);
        $message = str_replace('[[comments]]', $fa['comments'], $message);
        $message = str_replace('[[currentDate]]', $currentDate, $message);

        $subject   = $ln->gd('w.museum.booking.bookingForm.email.notifySubject');
        $fromName  = $fa['first_name'] . ' ' . $fa['last_name'];
        $fromEmail = $fa['email'];
        $toName    = $cpCfg['cp.companyName'];
        $toEmail   = $cpCfg['cp.bookingAdminEmail'];

        $args = array(
             'toName'    => $toName
            ,'toEmail'   => $toEmail
            ,'subject'   => $subject
            ,'message'   => $message
            ,'fromName'  => $fromName
            ,'fromEmail' => $fromEmail
        );

        $emailMsg = includeCPClass('Lib', 'EmailTemplate', 'EmailTemplate', true, array('args' => $args));
        $emailMsg->sendEmail();

        //------------------- USER EMAIL ----------------------------------------------//
        $message = $ln->gd('w.museum.booking.bookingForm.userEmail.notifyBody');
        $message = str_replace('[[first_name]]', $fa['first_name'], $message);
        $message = str_replace('[[last_name]]', $fa['last_name'], $message);
        $message = str_replace('[[pdf]]', $pdfUrl, $message);

        $subject   = $ln->gd('w.museum.booking.bookingForm.userEmail.notifySubject');
        $fromName  = $cpCfg['cp.companyName'];
        $fromEmail = $cpCfg['cp.bookingAdminEmail'];
        $toName    = $fa['first_name'] . ' ' . $fa['last_name'];
        $toEmail   = $fa['email'];

        $args1 = array(
             'toName'    => $toName
            ,'toEmail'   => $toEmail
            ,'subject'   => $subject
            ,'message'   => $message
            ,'fromName'  => $fromName
            ,'fromEmail' => $fromEmail
        );

        $emailMsg = includeCPClass('Lib', 'EmailTemplate', 'EmailTemplate', true, array('args' => $args1));
        $emailMsg->sendEmail();
        
        return $validate->getSuccessMessageXML();
    }

    /**
     *
     * @return <type>
     */
    function getBookingFormSubmitValidate() {
        $ln = Zend_Registry::get('ln');
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $validate->resetErrorArray();
        $validate->validateData('first_name', 'Please enter the firstname');
        $validate->validateData('last_name', 'Please enter the lastname');
        $validate->validateData('organisation', 'Please enter the organisation');
        $validate->validateData('email', 'Please enter the email');

            $captcha_code = $fn->getPostParam('captcha_code');
            require_once (CP_LIBRARY_PATH . 'lib_php/securimage/securimage.php');
            $img = new Securimage;
            if ($img->check($captcha_code) == false) {
                $validate->errorArray['captcha_code']['name'] = "captcha_code";
                $validate->errorArray['captcha_code']['msg']  = $ln->gd("cp.form.fld.captchaCode.err");
            }

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }

        return $text;
    }
    
    /**
     *
     * @return <type>
     */
    function getBookingJSON(){
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        
        $facility_id = $fn->getReqParam('facility_id');
        $startTimestamp = $fn->getReqParam('start'); //2013-03-17T00:00:00+08:00
        $endTimestamp = $fn->getReqParam('end');
        
        $start = date('c', $startTimestamp);
        $end = date('c', $endTimestamp);

        $start_date =  date ("Y-m-d", strtotime($start)); //2013-03-17
        $end_date   =  date ("Y-m-d", strtotime($end)); 
        $next_date  =  $start_date; 
        
        $format = "D, d M Y H:i:s O";
        $calArr = array();
        while(strtotime($next_date) < strtotime($end_date)){
            $today = date ("Y-m-d");
            $selectedDate = $next_date;
            //Add the events from facility_availability table
            $day_of_week = date('D', strtotime($selectedDate));
            $SQL = "
            SELECT fa.* 
            FROM facility_availability fa
            LEFT JOIN (facility f) ON (f.facility_id = fa.facility_id)
            WHERE fa.facility_id = {$facility_id}
              AND  fa.day != ''  
              AND (   fa.day = 'All' 
                   OR fa.day = '{$day_of_week}'
                   OR (fa.day = 'Date Range' AND '{$selectedDate}' BETWEEN fa.date_from AND fa.date_to)
                  )
              AND f.published = 1
            ORDER BY fa.day, fa.from_time
            ";        

            $resultsArr = $fn->getArrBySQL($SQL);    
            
            foreach($resultsArr AS $row){  
                $event_start = new DateTime($selectedDate." ".$row['from_time']);
                $event_end = new DateTime($selectedDate." ".$row['to_time']);
                
                if($row['day'] == 'All'){ //check everyday event is overridden by specific WeekDay
                    if($this->isEveryDaySlotOverriddenByWeekDaySlot($row, $day_of_week)){
                        continue;
                    }
                }
                
                //check everyday event is overridden by specific Date Range
                if($row['day'] != 'Date Range'){
                    if($this->isEveryDaySlotOverriddenByDateRangeSlot($row, $selectedDate)){
                        continue;
                    }
                }
                
                //check the slot is already booked or not available from bookings table
                if($this->isSlotOverriddenByBooking($row, $selectedDate)){
                    continue;
                }                
                

                $className = ($selectedDate < $today) ? 'notAvailable' : $row['availability'];
                $arr = array(
                    'id' => $row['facility_availability_id']
                    ,'title' => $ln->gd("m.museum.facility.availability.{$row['availability']}")
                    ,'start' => $event_start->format($format)
                    ,'end' => $event_end->format($format)
                    ,'allDay'=> false                
                    ,'className'=> $className              
                );
                
                if($className == 'open'){
                    $arr['url'] = "/index.php?widget=museum_booking&_spAction=bookingForm&showHTML=0&facility_availability_id={$row['facility_availability_id']}&booking_date={$next_date}";
                }
                
                $calArr[] = $arr;
            }
            
            //===================================================================
            //Add the events from bookings table
            $SQL2 = "
            SELECT b.* 
            FROM booking b
            LEFT JOIN (facility f) ON (f.facility_id = b.facility_id)            
            WHERE b.facility_id = {$row['facility_id']}
              AND b.date = '{$selectedDate}'   
              AND availability != 'cancelled'  
              AND f.published = 1
            ORDER BY b.from_time
            ";        

            $resultsArr2 = $fn->getArrBySQL($SQL2);    

            foreach($resultsArr2 AS $row2){  
                $event_start2 = new DateTime($selectedDate." ".$row2['from_time']);
                $event_end2 = new DateTime($selectedDate." ".$row2['to_time']);
                $className = ($selectedDate < $today) ? 'notAvailable' : $row2['availability'];
                $arr = array(
                    'id' => 'booking_'.$row2['booking_id']
                    ,'title' => $ln->gd("m.museum.facility.availability.{$row2['availability']}")
                    ,'start' => $event_start2->format($format)
                    ,'end' => $event_end2->format($format)
                    ,'allDay'=> false                
                    ,'className'=> $className               
                );
                
                $calArr[] = $arr;
            }            
            
            $next_date = date ("Y-m-d", strtotime("+1 day", strtotime($selectedDate)));
        }        
    
        return json_encode($calArr);
    }

    /**
     * 
     * @param type $row
     * @param type $day_of_week
     * @return boolean
     */
    function isEveryDaySlotOverriddenByWeekDaySlot($row, $day_of_week){
        $fn = Zend_Registry::get('fn');
        $SQL2 = "
        SELECT fa.* 
        FROM facility_availability fa
        WHERE fa.facility_id = {$row['facility_id']}
          AND fa.day = '{$day_of_week}'   
          AND (
            CAST('{$row['from_time']}' AS time) BETWEEN fa.from_time AND fa.to_time
            OR CAST('{$row['to_time']}' AS time) BETWEEN fa.from_time AND fa.to_time
            )
        ";

        $resultsArr2 = $fn->getArrBySQL($SQL2);    

        if(count($resultsArr2) > 0){
            return true;
        }   
        
        return false;
    }
    
    /**
     * 
     * @param type $row
     * @param type $date
     * @return boolean
     */    
    function isEveryDaySlotOverriddenByDateRangeSlot($row, $date){
        $fn = Zend_Registry::get('fn');
        $SQL2 = "
        SELECT fa.* 
        FROM facility_availability fa
        WHERE fa.facility_id = {$row['facility_id']}
          AND fa.day = 'Date Range'   
          AND '{$date}' BETWEEN fa.date_from AND fa.date_to
          AND (
            CAST('{$row['from_time']}' AS time) BETWEEN fa.from_time AND fa.to_time
            OR CAST('{$row['to_time']}' AS time) BETWEEN fa.from_time AND fa.to_time
            )
        ";

        $resultsArr2 = $fn->getArrBySQL($SQL2);    

        if(count($resultsArr2) > 0){
            return true;
        }   
        
        return false;
    }
    
    /**
     * 
     * @param type $row
     * @param type $day_of_week
     * @return boolean
     */
    function isSlotOverriddenByBooking($row, $date){
        $fn = Zend_Registry::get('fn');
        
        $SQLBooking = "
        SELECT b.* 
        FROM booking b
        WHERE b.facility_id = {$row['facility_id']}
          AND b.date = '{$date}'   
          AND (
            CAST('{$row['from_time']}' AS time) BETWEEN b.from_time AND b.to_time
            OR CAST('{$row['to_time']}' AS time) BETWEEN b.from_time AND b.to_time
            )
          AND availability != 'cancelled'
        ";                

        $resultsArr3 = $fn->getArrBySQL($SQLBooking);  
        
        if(count($resultsArr3) > 0){
            return true;
        }     
        
        return false;
    }

    /**
     *
     * @return <type>
     */
    function getSpExhibitBookingSubmit() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');
        $ln = Zend_Registry::get('ln');
        $cpCfg = Zend_Registry::get('cpCfg');
        $media = Zend_Registry::get('media');
        $cpUrl = Zend_Registry::get('cpUrl');

        //-------------------------------------------------------------------------------------//        
        if (!$this->getSpExhibitBookingSubmitValidate()){
            return $validate->getErrorMessageXML();
        }        

        $pdfUrl = '';

        // $facility_availability_id = $fn->getReqParam('facility_availability_id');
        // $SQL = "
        // SELECT fa.*, f.title
        // FROM facility_availability fa
        // LEFT JOIN (facility f) ON (f.facility_id = fa.facility_id)
        // WHERE fa.facility_availability_id = {$facility_availability_id}
        //   AND f.published = 1
        // ";        
        // $facilityRow = $fn->getRecordBySQL($SQL);          
 
        // $attRec = $media->model->getFirstMediaRecord('museum_facility', 'attachment', $facilityRow['facility_id']);
        // $url    = $cpUrl->getURLPrefix(). "/index.php?plugin=common_media&_spAction=saveMedia&showHTML=0&media_id=";
        // $pdfUrl = isset($attRec['media_id']) ? $url . $attRec['media_id'] : "#";

        $fa = array();
        $fa['facility_id']  = $fn->getReqParam('facility_id');

        $fa['date']         = $fn->getReqParam('booking_date');
        $fa['organisation']  = $fn->getPostParam('organisation');
        $fa['organisation_type']    = $fn->getPostParam('organisation_type');
        $fa['from_time']    = $fn->getPostParam('from_time');
        $fa['to_time']      = $fn->getPostParam('to_time');
        
        $fa['first_name']    = $fn->getPostParam('first_name');
        $fa['phone']         = $fn->getPostParam('phone');
        $fa['fax']           = $fn->getPostParam('fax');
        $fa['email']         = $fn->getPostParam('email');

        $fa['number_of_visitor']  = $fn->getPostParam('number_of_visitor');
        $fa['number_of_students'] = $fn->getPostParam('number_of_students');
        $fa['number_of_adults']   = $fn->getPostParam('number_of_adults');
        $fa['number_of_elderly']  = $fn->getPostParam('number_of_elderly');
        $fa['number_of_youth']    = $fn->getPostParam('number_of_youth');

        $fa['group_leader']          = $fn->getPostParam('group_leader');
        $fa['group_leader_mobile']   = $fn->getPostParam('group_leader_mobile');
        $fa['pre_visit']   = $fn->getPostParam('pre_visit', 0);
        $fa['date_pre_visit']   = $fn->getPostParam('date_pre_visit');
        $fa['disability_requirement']   = $fn->getPostParam('disability_requirement');
        $fa['visit_type']   = $fn->getPostParam('visit_type');

        $fa['availability'] = "pending";

        $fa['creation_date'] = date("Y-m-d H:i:s");

        $SQL    = $dbUtil->getInsertSQLStringFromArray($fa, 'booking');
        $result = $db->sql_query($SQL);
        $id     = $db->sql_nextid();

        $media->getAddMedia('museum_booking', 'attachment', $id, 'attFile');

        $attRec = $media->model->getFirstMediaRecord('museum_booking', 'attachment', $id);
        $url    = $cpUrl->getURLPrefix(). "/index.php?plugin=common_media&_spAction=saveMedia&showHTML=0&media_id=";
        $attUrl = isset($attRec['media_id']) ? $url . $attRec['media_id'] : "";        
        
        //------------------- ADMIN EMAIL ----------------------------------------------//
        $currentDate  = date('d-M-Y l h:i:s A');

        $message = $ln->gd('w.museum.booking.specialExhibitionBooking.adminEmail.notifyBody');
        $message = str_replace('[[application_date]]', $fa['creation_date'], $message);
        $message = str_replace('[[booking_date]]', $fa['date'], $message);
        $message = str_replace('[[organisation]]', $fa['organisation'], $message);
        $message = str_replace('[[organisation_type]]', $fa['organisation_type'], $message);
        $message = str_replace('[[from_time]]', $fa['from_time'], $message);
        $message = str_replace('[[to_time]]', $fa['to_time'], $message);
        $message = str_replace('[[first_name]]', $fa['first_name'], $message);
        $message = str_replace('[[phone]]', $fa['phone'], $message);
        $message = str_replace('[[fax]]', $fa['fax'], $message);
        $message = str_replace('[[email]]', $fa['email'], $message);

        $message = str_replace('[[number_of_visitor]]', $fa['number_of_visitor'], $message);
        $message = str_replace('[[number_of_students]]', $fa['number_of_students'], $message);
        $message = str_replace('[[number_of_adults]]', $fa['number_of_adults'], $message);
        $message = str_replace('[[number_of_elderly]]', $fa['number_of_elderly'], $message);
        $message = str_replace('[[number_of_youth]]', $fa['number_of_youth'], $message);

        $message = str_replace('[[group_leader]]', $fa['group_leader'], $message);
        $message = str_replace('[[group_leader_mobile]]', $fa['group_leader_mobile'], $message);
        $message = str_replace('[[pre_visit]]', $fa['pre_visit'], $message);
        $message = str_replace('[[date_pre_visit]]', $fa['date_pre_visit'], $message);
        $message = str_replace('[[disability_requirement]]', $fa['disability_requirement'], $message);
        $message = str_replace('[[visit_type]]', $fa['visit_type'], $message);

        if($attUrl != ''){
            $message .= "
            <p>
                Download the <a href='{$attUrl}'> attachment </a> here.
            </p>
            ";
        }
        
        $message = str_replace('[[currentDate]]', $currentDate, $message);

        $subject   = $ln->gd('w.museum.booking.specialExhibitionBooking.email.notifySubject');
        $fromName  = $fa['first_name'];
        $fromEmail = $fa['email'];
        $toName    = $cpCfg['cp.companyName'];
        $toEmail   = $cpCfg['cp.bookingAdminEmail'];

        $args = array(
             'toName'    => $toName
            ,'toEmail'   => $toEmail
            ,'subject'   => $subject
            ,'message'   => $message
            ,'fromName'  => $fromName
            ,'fromEmail' => $fromEmail
        );

        $emailMsg = includeCPClass('Lib', 'EmailTemplate', 'EmailTemplate', true, array('args' => $args));
        $emailMsg->sendEmail();
        
        return $validate->getSuccessMessageXML();
    }

    /**
     *
     * @return <type>
     */
    function getSpExhibitBookingSubmitValidate() {
        $ln = Zend_Registry::get('ln');
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $validate->resetErrorArray();

        $organisation_type = $fn->getPostParam('organisation_type');
        $pre_visit = $fn->getPostParam('pre_visit');

        $validate->validateData('facility_id', 'Invalid Facility');

        $validate->validateData('booking_date', $ln->gd("m.museum.facility.bookingForm.visitDate.err"));
        $validate->validateData('organisation', $ln->gd("m.museum.facility.bookingForm.organisation.err"));
        $validate->validateData('organisation_type', $ln->gd("m.museum.facility.bookingForm.organisationType.err"));
        $validate->validateData('from_time', $ln->gd("m.museum.facility.bookingForm.arrivalTime.err"));
        $validate->validateData('to_time', $ln->gd("m.museum.facility.bookingForm.departureTime.err"));
        $validate->validateData('first_name', $ln->gd("m.museum.facility.bookingForm.contactPerson.err"));
        $validate->validateData('phone', $ln->gd("cp.form.fld.phone.err"));
        $validate->validateData('email', $ln->gd("cp.form.fld.email.err"));
        $validate->validateData('visit_type', $ln->gd("m.museum.facility.bookingForm.visitType.err"));

        $validate->validateData('number_of_visitor', $ln->gd("m.museum.facility.bookingForm.numberOfVisitors.err"));
        $validate->validateData('number_of_adults', $ln->gd("m.museum.facility.bookingForm.numberOfAdults.err"));
        if ($organisation_type == 'Primary School' || $organisation_type == 'Secondary School'){
            $validate->validateData('number_of_students', $ln->gd("m.museum.facility.bookingForm.numberOfStudents.err"));
        } else if ($organisation_type == 'Community Group - Elderly'){
            $validate->validateData('number_of_elderly', $ln->gd("m.museum.facility.bookingForm.numberOfElderly.err"));
        } else if ($organisation_type == 'Community Group - Youth'){
            $validate->validateData('number_of_youth', $ln->gd("m.museum.facility.bookingForm.numberOfYouth.err"));            
        }

        if($pre_visit == 1){
            $validate->validateData('date_pre_visit', $ln->gd("m.museum.facility.bookingForm.preVisitDate.err"));
        }
        
        $captcha_code = $fn->getPostParam('captcha_code');
        require_once (CP_LIBRARY_PATH . 'lib_php/securimage/securimage.php');
        $img = new Securimage;
        if ($img->check($captcha_code) == false) {
            $validate->errorArray['captcha_code']['name'] = "captcha_code";
            $validate->errorArray['captcha_code']['msg']  = $ln->gd("cp.form.fld.captchaCode.err");
        }

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }

        return $text;
    }

   /**
     *
     * @return <type>
     */
    function getVenueHireSubmit() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');
        $ln = Zend_Registry::get('ln');
        $cpCfg = Zend_Registry::get('cpCfg');
        $media = Zend_Registry::get('media');
        $cpUrl = Zend_Registry::get('cpUrl');

        //-------------------------------------------------------------------------------------//        
        if (!$this->getVenueHireSubmitValidate()){
            return $validate->getErrorMessageXML();
        }        



        $fa = array();
        $fa['facility_id']  = $fn->getReqParam('facility_id');


        $fa['first_name']    = $fn->getPostParam('first_name');
        $fa['organisation']  = $fn->getPostParam('organisation');
        $fa['phone']         = $fn->getPostParam('phone');
        $fa['fax']           = $fn->getPostParam('fax');
        $fa['email']         = $fn->getPostParam('email');

        $fa['date']         = $fn->getReqParam('booking_date');
        $fa['from_time']    = $fn->getPostParam('from_time');
        $fa['to_time']      = $fn->getPostParam('to_time');

        $fa['event_name']      = $fn->getPostParam('event_name');
        $fa['event_nature']    = $fn->getPostParam('event_nature');
        $interested_venueArr = $fn->getPostParam('interested_venue', array(), false, true);

        $fa['interested_venue'] = implode(",", $interested_venueArr);

        $interested_venue_other = $fn->getPostParam('interested_venue_other');
        if($interested_venue_other != ''){
            $fa['interested_venue'] = $fa['interested_venue'] ." : " . $interested_venue_other;
        }

        $fa['hire_hours']  = $fn->getPostParam('hire_hours');
        $fa['number_of_visitor']  = $fn->getPostParam('number_of_visitor');

        $fa['availability'] = "pending";

        $fa['creation_date'] = date("Y-m-d H:i:s");

        $SQL    = $dbUtil->getInsertSQLStringFromArray($fa, 'booking');
        $result = $db->sql_query($SQL);
        $id     = $db->sql_nextid();

        $media->getAddMedia('museum_booking', 'attachment', $id, 'attFile');     
        
        //------------------- ADMIN EMAIL ----------------------------------------------//
        $currentDate  = date('d-M-Y l h:i:s A');

        $message = $ln->gd('w.museum.booking.venueHire.adminEmail.notifyBody');

        $message = str_replace('[[first_name]]', $fa['first_name'], $message);
        $message = str_replace('[[organisation]]', $fa['organisation'], $message);
        $message = str_replace('[[phone]]', $fa['phone'], $message);
        $message = str_replace('[[fax]]', $fa['fax'], $message);
        $message = str_replace('[[email]]', $fa['email'], $message);


        $message = str_replace('[[booking_date]]', $fa['date'], $message);
        $message = str_replace('[[from_time]]', $fa['from_time'], $message);
        $message = str_replace('[[to_time]]', $fa['to_time'], $message);

        $message = str_replace('[[event_name]]', $fa['event_name'], $message);
        $message = str_replace('[[event_nature]]', $fa['event_nature'], $message);
        $message = str_replace('[[interested_venue]]', $fa['interested_venue'], $message);
        $message = str_replace('[[hire_hours]]', $fa['hire_hours'], $message);
        $message = str_replace('[[number_of_visitor]]', $fa['number_of_visitor'], $message);
        
        $message = str_replace('[[currentDate]]', $currentDate, $message);

        $subject   = $ln->gd('w.museum.booking.venueHire.email.notifySubject');
        $fromName  = $fa['first_name'];
        $fromEmail = $fa['email'];
        $toName    = $cpCfg['cp.companyName'];
        $toEmail   = $cpCfg['cp.venueHireAdminEmail'];

        $args = array(
             'toName'    => $toName
            ,'toEmail'   => $toEmail
            ,'subject'   => $subject
            ,'message'   => $message
            ,'fromName'  => $fromName
            ,'fromEmail' => $fromEmail
        );

        $emailMsg = includeCPClass('Lib', 'EmailTemplate', 'EmailTemplate', true, array('args' => $args));
        $emailMsg->sendEmail();
        
        return $validate->getSuccessMessageXML();
    }

    /**
     *
     * @return <type>
     */
    function getVenueHireSubmitValidate() {
        $ln = Zend_Registry::get('ln');
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $validate->resetErrorArray();

        $organisation_type = $fn->getPostParam('organisation_type');
        $pre_visit = $fn->getPostParam('pre_visit');

        $validate->validateData('facility_id', 'Invalid Facility');

        $validate->validateData('first_name', $ln->gd("m.museum.facility.venueHireForm.contactPerson.err"));
        $validate->validateData('organisation', $ln->gd("m.museum.facility.venueHireForm.companyName.err"));
        $validate->validateData('phone', $ln->gd("cp.form.fld.phone.err"));
        $validate->validateData('email', $ln->gd("cp.form.fld.email.err"));

        $validate->validateData('booking_date', $ln->gd("m.museum.facility.venueHireForm.hireDate.err"));
        $validate->validateData('from_time', $ln->gd("m.museum.facility.venueHireForm.hireTime.err"));
        $validate->validateData('to_time', $ln->gd("m.museum.facility.venueHireForm.hireTime.err"));

        $validate->validateData('event_name', $ln->gd("m.museum.facility.venueHireForm.eventName.err"));
        $validate->validateData('event_nature', $ln->gd("m.museum.facility.venueHireForm.eventNature.err"));

        $validate->validateData('interested_venue', $ln->gd("m.museum.facility.venueHireForm.interestedVenue.err"));
        $validate->validateData('hire_hours', $ln->gd("m.museum.facility.venueHireForm.hireHours.err"));
        $validate->validateData('number_of_visitor', $ln->gd("m.museum.facility.venueHireForm.anticipatedNoOfParticipants.err"));


        
        $captcha_code = $fn->getPostParam('captcha_code');
        require_once (CP_LIBRARY_PATH . 'lib_php/securimage/securimage.php');
        $img = new Securimage;
        if ($img->check($captcha_code) == false) {
            $validate->errorArray['captcha_code']['name'] = "captcha_code";
            $validate->errorArray['captcha_code']['msg']  = $ln->gd("cp.form.fld.captchaCode.err");
        }

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }

        return $text;
    }


}