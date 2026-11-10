<?
class CP_Common_Modules_Directory_BusinessHoursLink_View extends CP_Common_Lib_ModuleLinkViewAbstract
{
    function getBusinessHoursUserFriendlyDisplay($busRow) {
        $db = Zend_Registry::get('db');
        $modelHelper = Zend_Registry::get('modelHelper');
        $cpUtil = Zend_Registry::get('cpUtil');

        $dataArray = $modelHelper->getLinkDataArrayByModule('directory_business', 'directory_businessHoursLink', $busRow['business_id']);

        $rows = '';
        $daysArr = $cpUtil->getWeekDaysArr();

        foreach($dataArray AS $row){
            $time = '';
            $start_time  = date('h:i a', strtotime($row['start_time']));
            $end_time    = date('h:i a', strtotime($row['end_time']));
            $start_time2 = date('h:i a', strtotime($row['start_time2']));
            $end_time2   = date('h:i a', strtotime($row['end_time2']));

            if ($row['start_time2'] != '' && $row['start_time2'] != '00:00:00'){
                $time = "{$start_time} to {$end_time},&nbsp;&nbsp;{$start_time2} to {$end_time2}";
            } else if ($row['start_time'] != '' && $row['start_time'] != '00:00:00'){
                $time = "{$start_time} to {$end_time2}";
            }

            $day = $daysArr[$row['week_day']];
            $rows .= "
            <tr>
                <td>{$day}</td>
                <td>{$time}</td>
            </tr>
            ";
        }

        $text = '';
        if ($rows != ''){
            $text = "
            <table class='thinlist table table-bordered table-condensed'>
                <tbody>
                    {$rows}
                </tbody>
            </table>
            ";
        }

        return $text;
    }
}
