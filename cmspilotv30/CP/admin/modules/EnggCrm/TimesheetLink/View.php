<?
class CP_Admin_Modules_EnggCrm_TimesheetLink_View extends CP_Common_Lib_ModuleLinkViewAbstract
{
    /**
     *
     */
    function getNew(){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        $formAction = "index.php?_spAction=add&lnkRoom={$tv['lnkRoom']}&showHTML=0";
        $sqlStaff = $fn->getDDSql('core_staff', array('condn' => "status = 'Current'"));
        $extFlds = '';

        if ($tv['module'] == 'task'){
            $taskRec = $fn->getRecordRowByID('task', 'task_id', $tv['srcRoomId']);
            $extFlds = "
            <input type='hidden' name='opportunity_id' value='{$taskRec['opportunity_id']}' />
            <input type='hidden' name='project_id' value='{$taskRec['project_id']}' />
            ";
        }
        
        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            <fieldset>
                {$formObj->getDDRowBySQL("{$cpCfg['m.enggCrm.staffFieldLabel']} Name", 'staff_id', $sqlStaff, $_SESSION['staff_id'])}
                {$formObj->getDateRow('Date', 'entry_date', date('Y-m-d'))}
                {$formObj->getTBRow('Hours', 'hours')}
                {$formObj->getTARow('Description (if any)', 'description')}
            </fieldset>
            <input type='hidden' name='{$fn->getSrcRoomKeyFldName()}' value='{$tv['srcRoomId']}' />
            {$extFlds}
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getEdit(){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        $id = $fn->getReqParam('id');
        $row = $fn->getRecordRowByID('timesheet', 'timesheet_id', $id);
        $sqlStaff = $fn->getDDSql('core_staff', array('condn' => "status = 'Current'"));

        $formAction = "index.php?_spAction=save&lnkRoom={$tv['lnkRoom']}&showHTML=0";

        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            <fieldset>
                {$formObj->getDDRowBySQL("{$cpCfg['m.enggCrm.staffFieldLabel']} Name", 'staff_id', $sqlStaff, $row['staff_id'])}
                {$formObj->getDateRow('Date', 'entry_date', $row['entry_date'])}
                {$formObj->getTBRow('Hours', 'hours', $row['hours'])}
                {$formObj->getTARow('Description (if any)', 'description', $row['description'])}
            </fieldset>
            <input type='hidden' name='timesheet_id' value='{$id}' />
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getNewRecordFromList() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $text = "";

        $task_id = $fn->getReqParam('task_id');

        $SQL    = "SELECT * FROM task WHERE task_id = {$task_id}";
        $result = $db->sql_query($SQL);
        $row    = $db->sql_fetchrow($result);

        $project_id     = $row['project_id'];
        $opportunity_id = $row['opportunity_id'];
        
        if ($opportunity_id > 0){
            /*
            $oppRec = $fn->getRecordRowByID('opportunity', 'opportunity_id', $opportunity_id);
            
            if($oppRec['project_id'] > 0){
                return "
                <h3>This opportunity is already converted to project and no further editing allowed</h3>
                ";
            }
            */
        }
        
        $sqlStaff = $fn->getDDSql('core_staff', array('condn' => "status = 'Current'"));
        
        $text .= "
        <form name='edit' action='index.php?module=timesheet&_spAction=addRecordFromList' method='post'>
            <table class='tblDetail'>
                {$dh->getDDRowBySQL($sqlStaff, 'Staff Name', 'staff_id', $_SESSION['staff_id'])}
                {$dh->getDateRow('Date', 'entry_date', date('Y-m-d'))}
                {$dh->getTBRow('Hours', 'hours')}
                {$dh->getTARow('Description (if any)', 'description')}
                {$dh->getSubmitRow()}
            </table>
            <input type='hidden' name='task_id' value='{$task_id}' />
            <input type='hidden' name='project_id' value='{$project_id}' />
            <input type='hidden' name='opportunity_id' value='{$opportunity_id}' />
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getReportsMenu() {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        
        $entry_date_1 = $fn->getReqParam('entry_date_1');
        $entry_date_2 = $fn->getReqParam('entry_date_2');

        if ($tv['action'] == "detail") {

        } else {
            $qstr = $fn->getQueryStringForJasper();
            $printJasperUrl  = "index.php?_spAction=printReport&showHTML=0&{$qstr}&roomName={$tv['module']}&report=";
            $printMonthlySummaryUrl = "index.php?_spAction=timesheetSummaryByMonth&showHTML=0&module={$tv['module']}" .
                                      "&entry_date_1={$entry_date_1}&entry_date_2={$entry_date_2}";

            $text = "
            <h2>Reports:</h2>
            <ul class='printOptions'>
                <li><a href='{$printJasperUrl}timesheetSummaryList'>Timesheet Summary List</a></li>
                <li><a href='{$printJasperUrl}timesheetSummaryListByDay'>Timesheet Summary List / By Day</a></li>
                <li><a href='{$printMonthlySummaryUrl}'>Timesheet Summary / Month View</a></li>
            </ul>
            ";
        }

        return $text;
    }
}
