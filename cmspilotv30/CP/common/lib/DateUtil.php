<?
/**
 *
 */
require_once("adodb-time.inc.php");

define('CP_SECOND', 1);
define('CP_MINUTE', 60 * CP_SECOND);
define('CP_HOUR', 60 * CP_MINUTE);
define('CP_DAY', 24 * CP_HOUR);
define('CP_MONTH', 30 * CP_DAY);

/**
 *
 */
class CP_Common_Lib_DateUtil
{

    /**
     *
     * @param <type> $monthNumber
     * @return <type>
     */
    function getShortMonthName($monthNumber) {

       if (strlen($monthNumber) == 1){
          $monthNumber = "0" . $monthNumber;
       }

       $monthArray["01"] = "Jan";
       $monthArray["02"] = "Feb";
       $monthArray["03"] = "Mar";
       $monthArray["04"] = "Apr";
       $monthArray["05"] = "May";
       $monthArray["06"] = "Jun";
       $monthArray["07"] = "Jul";
       $monthArray["08"] = "Aug";
       $monthArray["09"] = "Sep";
       $monthArray["10"] = "Oct";
       $monthArray["11"] = "Nov";
       $monthArray["12"] = "Dec";

       if ($monthNumber != "" & $monthNumber != "00") {
          return $monthArray[$monthNumber];
       }
       else {
          return "";
       }

    }

    /**
     *
     * @param <type> $dayNumber
     * @return <type>
     */
    function getShortDayName($dayNumber) {
       $dayArray["1"] = "Mon";
       $dayArray["2"] = "Tue";
       $dayArray["3"] = "Wed";
       $dayArray["4"] = "Thu";
       $dayArray["5"] = "Fri";
       $dayArray["6"] = "Sat";
       $dayArray["7"] = "Sun";

       if ($dayNumber != "" & $dayNumber != "00") {
          return $dayArray[$dayNumber];
       }
       else {
          return "";
       }
    }

    /**
     *
     * @param <type> $monthNumber
     * @return <type>
     */
    function getLongMonthName($monthNumber) {
       $monthArray["01"] = "January";
       $monthArray["02"] = "February";
       $monthArray["03"] = "March";
       $monthArray["04"] = "April";
       $monthArray["05"] = "May";
       $monthArray["06"] = "June";
       $monthArray["07"] = "July";
       $monthArray["08"] = "August";
       $monthArray["09"] = "September";
       $monthArray["10"] = "October";
       $monthArray["11"] = "November";
       $monthArray["12"] = "December";

       if ($monthNumber != "" & $monthNumber != "00" ) {
          return $monthArray[$monthNumber];
       }
       else {
          return "";
       }
    }

    /**
     *
     * @param <type> $dateValue
     * @param <type> $formatStyle
     * @return <type>
     */
    function formatDate($dateValue, $formatStyle) {
       if ($dateValue === null || $dateValue === '') {
          return "";
       }

       $formattedDate = $formatStyle;
       $day      = substr($dateValue, 8, 2);
       $dayShort = (substr($day, 0, 1) == 0) ? substr($day, 1, 1) : $day;
       $month = substr($dateValue, 5, 2);
       $monthWithNoPrefix = (substr($month, 0, 1) == "0") ? substr($month, 1, 1) : $month;
       $year  = substr($dateValue, 0, 4);

       $hour  = substr($dateValue, 11, 2);
       $min   = substr($dateValue, 14, 2);
       $sec   = substr($dateValue, 17, 2);
       $hourForCalculation = (int) $hour;
       $minForCalculation = (int) $min;
       $secForCalculation = (int) $sec;

       if ($year == "0000")
          return "";

       $longMonthName  = $this->getLongMonthName($month);
       $shortMonthName = $this->getShortMonthName($month);
       $shortYear      = substr($year, 2, 2);

       $shortDayName  = adodb_date("D", adodb_mktime(0, 0, 0, $month, $day, $year));
       $longDayName   = adodb_date("l", adodb_mktime(0, 0, 0, $month, $day, $year));

       $hour24   = $hour;
       $hour12   = adodb_date("h", adodb_mktime($hourForCalculation, $minForCalculation, $secForCalculation, $month, $day, $year));
       $hourAMPM = adodb_date("A", adodb_mktime($hourForCalculation, $minForCalculation, $secForCalculation, $month, $day, $year));

       $formattedDate = str_replace("DDDD", $longDayName,          $formattedDate);
       $formattedDate = str_replace("DDD",  $shortDayName,         $formattedDate);
       $formattedDate = str_replace("DD",   $day,                  $formattedDate);
       $formattedDate = str_replace("D",    $dayShort,             $formattedDate);


       $formattedDate = str_replace("MMMM", $longMonthName,        $formattedDate);
       $formattedDate = str_replace("MMM",  $shortMonthName,       $formattedDate);
       $formattedDate = str_replace("MM",   $month,                $formattedDate);
       $formattedDate = str_replace("MS",   $monthWithNoPrefix,    $formattedDate);
       $formattedDate = str_replace("YYYY", $year,                 $formattedDate);
       $formattedDate = str_replace("YY",   $shortYear,            $formattedDate);

       $formattedDate = str_replace("HHH",  $hour24,               $formattedDate);
       $formattedDate = str_replace("HH" ,  $hour12,               $formattedDate);
       $formattedDate = str_replace("MIN",  $min,                  $formattedDate);
       $formattedDate = str_replace("SS" ,  $sec,                  $formattedDate);
       $formattedDate = str_replace("AM" ,  $hourAMPM ,            $formattedDate);

       if ($dateValue == ""){
          return "";
       } else {
          return $formattedDate;
       }
    }

    /**
     *
     * @param <type> $textdateValue
     * @param <type> $formatStyle
     * @return <type>
     */
    function formatTextdate($textdateValue, $formatStyle) {
       $formatStyle   = strtoupper($formatStyle);
       $formatteddate = $formatStyle;

       $day   = substr($textdateValue, 6, 2);
       $month = substr($textdateValue, 4, 2);
       $year  = substr($textdateValue, 0, 4);

       $longMonthName  = $this->getLongMonthName($month);
       $shortMonthName = $this->getShortMonthName($month);
       $shortYear      = substr($year, 2, 2);

       $formatteddate = str_replace("DD",   $day,            $formatteddate);
       $formatteddate = str_replace("MMMM", $longMonthName,  $formatteddate);
       $formatteddate = str_replace("MMM",  $shortMonthName, $formatteddate);
       $formatteddate = str_replace("MM",   $month,          $formatteddate);
       $formatteddate = str_replace("YYYY", $year,           $formatteddate);
       $formatteddate = str_replace("YY",   $shortYear,      $formatteddate);

       if (strlen( trim($textdateValue) ) == 8) {
          return $formatteddate;
       }
       else {
          return "";
       }
    }

    /**
     *
     * @param <type> $textDateValue
     * @param <type> $formatStyle
     * @return <type>
     */
    function getDateFromText($textDateValue, $formatStyle) {
       $formatStyle   = strtoupper($formatStyle);

       if (strlen( trim($textDateValue) ) < 8) {
          return;
       }

       if ($formatStyle == "DD-MM-YYYY"){
          $day   = substr($textDateValue, 0, 2);
          $month = substr($textDateValue, 3, 2);
          $year  = substr($textDateValue, 6, 4);

          $convertedDate = $year . "-" . $month . "-" . $day;

          return $convertedDate;
       }

    }

    /**
     *
     * @param <type> $textDateValue
     * @param <type> $formatStyle
     * @param <type> $inputFormat
     * @return <type>
     */
    function getDateFromTextRegExp($textDateValue, $formatStyle, $inputFormat = "MM-DD-YYYY") {
       $formatStyle   = strtoupper($formatStyle);
       $dateArr = array();

       $arr = preg_split('~[/.-]~', $textDateValue);
       if (count($arr) < 3) {
          return "";
       }

       if ($inputFormat == "MM-DD-YYYY" || $inputFormat == "MM/DD/YYYY") {
          list($dateArr['month'], $dateArr['day'], $dateArr['year']) = preg_split('~[/.-]~', $textDateValue);
       }
       else if ($inputFormat == "YYYY-MM-DD" || $inputFormat == "YYYY/MM/DD") {
          list($dateArr['year'], $dateArr['month'], $dateArr['day']) = preg_split('~[/.-]~', $textDateValue);
       }
       else if ($inputFormat == "DD-MM-YYYY" || $inputFormat == "DD/MM/YYYY") {
          list($dateArr['day'], $dateArr['month'], $dateArr['year']) = preg_split('~[/.-]~', $textDateValue);
       }

       $convertedDate = str_replace("DD", $dateArr['day'], $formatStyle);
       $convertedDate = str_replace("MM", $dateArr['month'], $convertedDate);
       $convertedDate = str_replace("YYYY", $dateArr['year'], $convertedDate);

       return $convertedDate;

    }

    /**
     *
     * @param <type> $formatStyle
     * @return <type>
     */
    function getCurrentTextdate($formatStyle) {
       $textdateValue = date("Y") . date("m") . date("d");
       $formatStyle   = strtoupper($formatStyle);
       $formatteddate = $this->formatTextdate($textdateValue, $formatStyle);

       if (strlen( trim($textdateValue) ) == 8) {
          return $formatteddate;
       }
       else {
          return "";
       }
    }

    /**
     *
     * @param <type> $textTimeValue
     * @param <type> $formatStyle
     * @return <type>
     */
    function formatTextTime($textTimeValue, $formatStyle) {
       $formatStyle   = strtoupper($formatStyle);
       $formattedTime = $formatStyle;

       $hour   = substr($textTimeValue, 0, 2);
       $min    = substr($textTimeValue, 2, 2);
       $sec    = substr($textTimeValue, 4, 2);


       $formattedTime = str_replace("HH",   $hour,     $formattedTime);
       $formattedTime = str_replace("MM",   $min,      $formattedTime);
       $formattedTime = str_replace("SS",   $sec,      $formattedTime);

       if (strlen( trim($textTimeValue) ) == 6) {
          return $formattedTime;
       }
       else {
          return "";
       }
    }

    /**
     *
     * @return <type>
     */
    function getMonthArrayLong() {
       $mnthArr[1]  = "January";
       $mnthArr[2]  = "February";
       $mnthArr[3]  = "March";
       $mnthArr[4]  = "April";
       $mnthArr[5]  = "May";
       $mnthArr[6]  = "June";
       $mnthArr[7]  = "July";
       $mnthArr[8]  = "August";
       $mnthArr[9]  = "September";
       $mnthArr[10] = "October";
       $mnthArr[11] = "November";
       $mnthArr[12] = "December";
       return $mnthArr;
    }

    /**
     *
     * @return <type>
     */
    function getMonthArrayShort() {
       $mnthArr[1]  = "Jan";
       $mnthArr[2]  = "Feb";
       $mnthArr[3]  = "Mar";
       $mnthArr[4]  = "Apr";
       $mnthArr[5]  = "May";
       $mnthArr[6]  = "Jun";
       $mnthArr[7]  = "Jul";
       $mnthArr[8]  = "Aug";
       $mnthArr[9]  = "Sep";
       $mnthArr[10] = "Oct";
       $mnthArr[11] = "Nov";
       $mnthArr[12] = "Dec";
       return $mnthArr;
    }

    /**
     *
     * @param <type> $monthName
     * @return <type>
     */
    function getMonthNumByLongMonth($monthName) {
       $mnthArr["January"  ] = "1";
       $mnthArr["February" ] = "2";
       $mnthArr["March"    ] = "3";
       $mnthArr["April"    ] = "4";
       $mnthArr["May"      ] = "5";
       $mnthArr["June"     ] = "6";
       $mnthArr["July"     ] = "7";
       $mnthArr["August"   ] = "8";
       $mnthArr["September"] = "9";
       $mnthArr["October"  ] = "10";
       $mnthArr["November" ] = "11";
       $mnthArr["December" ] = "12";

       foreach ($mnthArr as $key => $value) {
          if ($key == $monthName) {
            return $value;
          }
       }

       return "error";
    }

    /**
     *
     * @return <type>
     */
    function getCurrentDateStamp(){
       //get the current timestamp
       $currdate  = date("Ymd");
       $currday   = substr($currdate,6,2);
       $currmonth = substr($currdate,4,2);
       $curryear  = substr($currdate,0,4);
       $currDateStamp = ($curryear . "-" . $currmonth . "-" . $currday);
       return $currDateStamp;
    }

    /**
     *
     * @return <type>
     */
    function getCurrentTimeStamp(){

       //get the current timestamp
       $currdate  = date("YmdHis");
       $currday   = substr($currdate,6,2);
       $currmonth = substr($currdate,4,2);
       $curryear  = substr($currdate,0,4);
       $currhour  = substr($currdate,8,2);
       $currmin   = substr($currdate,10,2);
       $currsec   = substr($currdate,12,2);
       $currTimeStamp = ($curryear . "-" . $currmonth . "-" . $currday . " " . $currhour . ":" . $currmin . ":" . $currsec );
       return $currTimeStamp;

    }

    /**
     *
     * @param <type> $week
     * @param <type> $year
     * @return <type>
     */
    function WeekToDate ($week, $year){
       $Jan1 = mktime (1, 1, 1, 1, 1, $year);
       $iYearFirstWeekNum = (int) strftime("%W",mktime (1, 1, 1, 1, 1, $year));

       if ($iYearFirstWeekNum == 1)
        {
          $week = $week - 1;
        }

       $weekdayJan1 = date ('w', $Jan1);
       $FirstMonday = strtotime(((4-$weekdayJan1)%7-3) . ' days', $Jan1);
       $CurrentMondayTS = strtotime(($week) . ' weeks', $FirstMonday);
       return ($CurrentMondayTS);
    }

    /**
     *
     * @param <type> $dateValue
     * @return <type>
     */
    function checkDate($dateValue) {
       $day   = substr($dateValue, 6, 2);
       $month = substr($dateValue, 4, 2);
       $year  = substr($dateValue, 0, 4);

       if(!checkdate($month,$day,$year)){
          return false;
       }else {
          return true;
       }
    }


    //=============================================================//
    /**
     *
     *  Gets the first weekday of that month and year
     *
     *  @param  int   The day of the week (0 = sunday, 1 = monday ... , 6 = saturday)
     *  @param  int   The month (if false use the current month)
     *  @param  int   The year (if false use the current year)
     *
     *  @return int   The timestamp of the first day of that month
     *
     **/
    function getFirstDayOfMonth($day_number=1, $month=false, $year=false){
      $month  = ($month === false) ? strftime("%m"): $month;
      $year   = ($year === false) ? strftime("%Y"): $year;

      $first_day = 1 + ((7+$day_number - strftime("%w", mktime(0,0,0,$month, 1, $year)))%7);

      return mktime(0,0,0,$month, $first_day, $year);
    }

    /**
     *
     * @param <type> $date
     * @return <type>
     */
    function getDateArrayFromISO($date) {
       $year   = substr($date, 0, 4);
       $month  = substr($date, 5, 2);
       $day    = substr($date, 8, 2);
       $hour   = "00";
       $minute = "00";
       $second = "00";
       if (strlen($date) > 10) {
          $hour   = substr($date, 11, 2);
          $minute = substr($date, 14, 2);
          $second = substr($date, 17, 2);
       }

       $retArr = array("year" => $year, "month" => $month, "day" => $day,
                       "hour" => $hour, "minute" => $minute, "second" => $second);
       return $retArr;
    }

    /**
     *
     * @param <type> $date
     * @return <type>
     */
    function getAdodbTimeFromISO($date) {
       if ($date == "" || $date == "0000-00-00" || $date == "0000-00-00 00:00:00") {
          return "";
       }
       $dateArr = $this->getDateArrayFromISO($date);
       $adodbTime = adodb_mktime($dateArr['hour'], $dateArr['minute'], $dateArr['second'], $dateArr['month'], $dateArr['day'], $dateArr['year']);
       return $adodbTime;
    }

    /*
    @params $firstdate, $lastdate
    @return array() of array(monday,sunday)
    @description returns all the mondays and sundays of the given date range
    */
    function get_week_intervals($fdate,$ldate){
        list($year,$month,$day) = explode('-',$fdate);
        $daynum = date('w',
                       mktime(date('H'),
                              date('i'),
                              date('s'),
                              $month,
                              $day,
                              $year)
                      );
        $daynum = $daynum==0? 7 : $daynum;
        $week=array();
        //get the dayname of the first day
        //if month = current month get the current date as the last day
        if($month==date('m'))
        {
            $lastday = date('d');
        }
        else
        {
            $lastday = date('t', strtotime($fdate));
        }
        if((date('l',strtotime($fdate))) == 'Sunday')
        {
            $monday = $fdate;
            $sunday = $fdate;
        }
        else
        {
            $monday = $fdate;
            $sunday = date('Y-m-d',(mktime(date('H'),
                           date('i'),date('s'),$month,
                           $day,$year))-($daynum-7)*86400);

        }
        $week[] = array('weekStart'=>$monday,'weekEnd'=>$sunday);

        $day = date('d',strtotime($sunday." +1 day"));

        while($sunday < $ldate)
        {
            $monday = date('Y-m-d',strtotime($sunday." +1 day"));

            list($year,$month,$day) = explode('-',$monday);
            $daynum = date('w',
                          mktime(date('H'),
                                 date('i'),
                                 date('s'),
                                 $month,
                                 $day,
                                 $year)
                           );
            $daynum = $daynum==0? 7 : $daynum;

            $sunday = date('Y-m-d',(mktime(date('H'),date('i'),
                           date('s'),$month,$day,$year))-($daynum-7)*86400);
            if($sunday > $ldate)
            {
                $sunday = $ldate;
            }

            $week[] = array('weekStart'=>$monday,'weekEnd'=>$sunday);
        }

        return $week;
    }

    /**
     *
     * @param <type> $start_date
     * @param <type> $end_date
     * @return <type>
     */
    function getMonthArrayBetweenTwoDates($start_date,$end_date){
       $start_month = $this->formatDate($start_date, "MM");
       $start_year  = $this->formatDate($start_date, "YYYY");
       $end_month   = $this->formatDate($end_date, "MM");
       $end_year    = $this->formatDate($end_date, "YYYY");

       $num_months = $this->getNumMonth($start_year . $start_month, $end_year . $end_month);

       $counter = 0;

       $rangeArray = array();

       while ($counter < $num_months){

          $startTS        = mktime(0, 0, 0, $start_month + $counter , 1, $start_year);
          $start          = date ("Y-m-d", $startTS);

          $monthInLoop    = date ("m", $startTS);
          $yearInLoop     = date ("Y", $startTS);
          $numDaysInMonth = cal_days_in_month(CAL_GREGORIAN, $monthInLoop, $yearInLoop);

          $endTS          = mktime(0, 0, 0, $start_month + $counter , $numDaysInMonth, $start_year);
          $end            = date ("Y-m-d", $endTS);
          $rangeArray[]   = array($start, $end);

          $counter++;
       }

       return $rangeArray;
    }

    /**
     *
     * @param <type> $start_date
     * @param <type> $end_date
     * @return <type>
     */
    function getQuarterArrayBetweenTwoDates($start_date, $end_date){
       $start_month = $this->formatDate($start_date, "MM");
       $start_year  = $this->formatDate($start_date, "YYYY");
       $end_month   = $this->formatDate($end_date, "MM");
       $end_year    = $this->formatDate($end_date, "YYYY");

       $rangeArray = array();

       $numMonths  = $this->getNumMonth($start_year . $start_month, $end_year . $end_month);
       $monthArray = $this->getMonthArrayBetweenTwoDates($start_date, $end_date);

       //print UtilCommon::getPrintR($monthArray);

       $numQuarters = ceil($numMonths / 3);

       $counter = 0;
       $monthIndexStart = 0;
       while ($counter < $numQuarters) {
          $monthIndexEnd = $monthIndexStart + 2;
          if ($monthIndexEnd > $numMonths - 1) {
             $monthIndexEnd = $numMonths - 1;
          }

          $start = $monthArray[$monthIndexStart][0];
          $end   = $monthArray[$monthIndexEnd][1];
          $rangeArray[] = array($start, $end);

          $monthIndexStart += 3;
          $counter++;
       }

       return $rangeArray;

    }

    /**
     *
     * @param <type> $start_date
     * @param <type> $end_date
     * @return <type>
     */
    function getYearArrayBetweenTwoDates($start_date,$end_date){

       $start_year  = $this->formatDate($start_date, "YYYY");
       $end_year    = $this->formatDate($end_date, "YYYY");

       $num_years = $end_year - $start_year + 1;

       $counter = 0;

       $rangeArray = array();

       while ($counter < $num_years){

          $startTS        = mktime(0, 0, 0, 1, 1, $start_year + $counter);
          $start          = date ("Y-m-d", $startTS);
          $endTS          = mktime(0, 0, 0, 12, 31, $start_year + $counter);
          $end            = date ("Y-m-d", $endTS);
          $rangeArray[]   = array($start, $end);

          $counter++;
       }

       return $rangeArray;
    }

    /**
     *
     * @param <type> $begindate
     * @param <type> $enddate
     * @return <type>
     */
    function countQuarters($begindate, $enddate){
        if (!isset($begindate) || empty($begindate) || !isset($enddate) || empty($enddate))
            return -1;

        $countyears = date("Y", strtotime($enddate)) - date("Y", strtotime($begindate));
        $quarters = 0;

        if (date("Y", strtotime($enddate)) == date("Y", strtotime($begindate)))
        {
            if (date("m", strtotime($enddate)) != date("m", strtotime($begindate)))
            {
                if (date("m", strtotime($enddate)) > date("m", strtotime($begindate)))
                {
                    $difference = date("m", strtotime($enddate)) - date("m", strtotime($begindate));

                    $quarters += ceil((int) $difference / 4);
                }
                else
                {
                    return -1;
                }
            }
        }
        else
        {
            $quarters = (int) $countyears * 4;
            if (date("m", strtotime($enddate)) != date("m", strtotime($begindate)))
            {
                if (date("m", strtotime($enddate)) > date("m", strtotime($begindate)))
                {
                    $difference = date("m", strtotime($enddate)) - date("m", strtotime($begindate));

                    $quarters += ceil((int) $difference / 4);
                }
                else
                {
                    $afterbegin = 12 - (int) date("m", strtotime($begindate));
                    $untilend = date("m", strtotime($enddate));

                    $quarters = ($quarters - 4) + ceil(($afterbegin + $untilend) / 4);
                }
            }
        }

        return $quarters;
    }

    /**
     *
     * @param <type> $start YYYYMM
     * @param <type> $stop YYYYMM
     * @return <type>
     */
    function getNumMonth($start,$stop) {

            $aSta = substr($start,0,4) ;
            $aSto = substr($stop,0,4) ;

            $mSta = substr($start,4,2) ;
            $mSto = substr($stop,4,2) ;

            if($aSta == $aSto) {
                return $mSto-$mSta+1 ;
            } else {
                if(($aSto-$aSta) == 1) {
                    return 12-$mSta+$mSto+1 ;
                } else {
                    return (12-$mSta+$mSto+1)+($aSto-$aSta-1)*12;
                }
            }
    }

    /**
     *
     * @param <type> $Y
     * @param <type> $m
     * @return <type>
     */
    function getWeeksAndDaysInMonth($Y, $m) {
       // constants used here for legibility, use $vars for dynamicon...
       define('MONTH_DAYS',date('t', strtotime(date($m . '/01/' . $Y))));
       // w:0->6 = Sun->Sat
       define('MONTH_FIRST_DAY',date('w', strtotime(date($m . '/01/' . $Y))));

       for($i = 1, $j = MONTH_FIRST_DAY, $w = 1;$i <= MONTH_DAYS;$i++) {
         $week[$w][$j] = $i;
         $j++;
         if($j == 7) {
           $j = 0;
           $w++;
         }
       }

       return $week;
    }

    /**
     *
     * @param <type> $yearMon yyyy-mm
     * @return <type>
     */
    function getMonthDetailsArr($yearMon) {
        $arr = array();

        if ($yearMon != ""){
            $qArr = explode("-", $yearMon);
            $curYr      = $qArr[0];
            $curMon     = $qArr[1];

            $curMonName = date('F Y', mktime(0,0,0, $curMon,   1, $curYr ));
            $prvMonName = date('F Y', mktime(0,0,0, $curMon-1, 1, $curYr ));
            $nxtMonName = date('F Y', mktime(0,0,0, $curMon+1, 1, $curYr ));

            $prvMon     = date('m'  , mktime(0,0,0, $curMon-1, 1, $curYr ));
            $prvYr      = date('Y'  , mktime(0,0,0, $curMon-1, 1, $curYr ));

            $nxtMon     = date('m'  , mktime(0,0,0, $curMon+1, 1, $curYr ));
            $nxtYr      = date('Y'  , mktime(0,0,0, $curMon+1, 1, $curYr ));

            $arr = array(
                     "curYr" => $curYr, "curMon" => $curMon, "curMonName" => $curMonName, "curMonYr" => $curYr . "-" . $curMon,
                     "prvYr" => $prvYr, "prvMon" => $prvMon, "prvMonName" => $prvMonName, "prvMonYr" => $prvYr . "-" . $prvMon,
                     "nxtYr" => $nxtYr, "nxtMon" => $nxtMon, "nxtMonName" => $nxtMonName, "nxtMonYr" => $nxtYr . "-" . $nxtMon
                   );
        }

        return $arr;

    }

    /**
     *
     */
    function getWeekDetailsArr($weekNo) {

        //incomplete ... need to fill in

        $arr = array();

        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');

        //sunday = start of week
        $sat = 6; //saturday = end of week
        $current_date = $fn->getTZDateTimeForDisplay($rowContact['timezone'], date('Y-m-d'));
        $current_day = date('w', $current_date);
        $days_remaining_until_sat = $sat - $current_day;

        $start_date = strtotime("-{$current_day} days");
        $end_date   = strtotime("+{$days_remaining_until_sat} days");

        $titleDate = $ln->gd('weekOf') . ' ' . date('d', $start_date) . ' - ' . date('d, Y', $end_date);

        return array($start_date, $end_date, $titleDate);


        if ($yearMon != ""){
            $qArr = explode("-", $yearMon);
            $curYr      = $qArr[0];
            $curMon     = $qArr[1];

            $curMonName = date('F Y', mktime(0,0,0, $curMon,   1, $curYr ));
            $prvMonName = date('F Y', mktime(0,0,0, $curMon-1, 1, $curYr ));
            $nxtMonName = date('F Y', mktime(0,0,0, $curMon+1, 1, $curYr ));

            $prvMon     = date('m'  , mktime(0,0,0, $curMon-1, 1, $curYr ));
            $prvYr      = date('Y'  , mktime(0,0,0, $curMon-1, 1, $curYr ));

            $nxtMon     = date('m'  , mktime(0,0,0, $curMon+1, 1, $curYr ));
            $nxtYr      = date('Y'  , mktime(0,0,0, $curMon+1, 1, $curYr ));

            $arr = array(
                     "curYr" => $curYr, "curMon" => $curMon, "curMonName" => $curMonName, "curMonYr" => $curYr . "-" . $curMon,
                     "prvYr" => $prvYr, "prvMon" => $prvMon, "prvMonName" => $prvMonName, "prvMonYr" => $prvYr . "-" . $prvMon,
                     "nxtYr" => $nxtYr, "nxtMon" => $nxtMon, "nxtMonName" => $nxtMonName, "nxtMonYr" => $nxtYr . "-" . $nxtMon
                   );
        }

        return $arr;

    }

    function getDaysInMonth($month, $year) {
        return date('t', mktime(0, 0, 0, $month+1, 0, $year));
    }

    function getWeekRange($date) {
        $ts = strtotime($date);
        $start = (date('w', $ts) == 0) ? $ts : strtotime('last sunday', $ts);
        return array(date('Y-m-d', $start),
                     date('Y-m-d', strtotime('next saturday', $start)));
    }

    function getDateArrBetween2Dates($date1, $date2)
    {
        if ($date1<$date2)
        {
            $dates_range[]=$date1;
            $date1=strtotime($date1);
            $date2=strtotime($date2);
            while ($date1!=$date2)
            {
                $date1=mktime(0, 0, 0, date("m", $date1), date("d", $date1)+1, date("Y", $date1));
                $dates_range[]=date('Y-m-d', $date1);
            }
        }
        return $dates_range;
    }

    function getRelativeTime($date) {
        $cpUtil = Zend_Registry::get('cpUtil');

        $diff = time() - strtotime($date);
        if ($diff>0) {
            if ($diff<60)
            return $diff . " second" . $cpUtil->getPlural($diff) . " ago";
            $diff = round($diff/60);
            if ($diff<60)
            return $diff . " minute" . $cpUtil->getPlural($diff) . " ago";
            $diff = round($diff/60);
            if ($diff<24)
            return $diff . " hour" . $cpUtil->getPlural($diff) . " ago";
            $diff = round($diff/24);
            if ($diff<7)
            return $diff . " day" . $cpUtil->getPlural($diff) . " ago";
            $diff = round($diff/7);
            if ($diff<4)
            return $diff . " week" . $cpUtil->getPlural($diff) . " ago";
            return "on " . date("F j, Y", strtotime($date));
        } else {
            if ($diff>-60)
            return "in " . -$diff . " second" . $cpUtil->getPlural($diff);
            $diff = round($diff/60);
            if ($diff>-60)
            return "in " . -$diff . " minute" . $cpUtil->getPlural($diff);
            $diff = round($diff/60);
            if ($diff>-24)
            return "in " . -$diff . " hour" . $cpUtil->getPlural($diff);
            $diff = round($diff/24);
            if ($diff>-7)
            return "in " . -$diff . " day" . $cpUtil->getPlural($diff);
            $diff = round($diff/7);
            if ($diff>-4)
            return "in " . -$diff . " week" . $cpUtil->getPlural($diff);
            return "on " . date("F j, Y", strtotime($date));
        }
    }
}
