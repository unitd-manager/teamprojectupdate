<?
class CP_Admin_Modules_Project_FollowUp_View extends CP_Common_Lib_ModuleViewAbstract
{
    var $jssKeys = array('fullcalendar-1.6.4', 'jqUITimePickerAddon-0.9.3');
    /**
     *
     */
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $tv      = Zend_Registry::get('tv');
        $db      = Zend_Registry::get('db');
        $fn      = Zend_Registry::get('fn');
        $dbUtil  = Zend_Registry::get('dbUtil');
        $formObj = Zend_Registry::get('formObj');

        $currentDate  = date("Y-m-d");
        $dashboard = getCPModuleObj('common_dashboard')->model;

        $wOpportunityChart = getCPWidgetObj('project_opportunityChart');

        $cpOpportunityChart = "
        <div class='float_left'>
            <div class='multi-country'>{$wOpportunityChart->getWidget()}</div>
        </div>
        ";

        $sqlEmployeeName = "
        SELECT s.staff_id AS employee_id
              ,CONCAT_WS(' ', s.first_name, s.last_name) AS employee_name
        FROM staff s
        ORDER BY employee_name
        ";

        $text = "
        <div class='opportunityCalendarView'>
            <div class='companyFilter'>
                {$formObj->getDDRowBySQL('Employee Name', 'employee_id', $sqlEmployeeName)}
            </div>
            {$this->getOpportunityCalendarView()}
            {$dashboard->getDasboardObj('project_staffHistory', array('cssClass' => 'c100l'))}
            {$cpOpportunityChart}
        </div>

        <div class='opportunityCalendarViewRight'>
            {$this->getOpportunityDetails()}
        </div>
        ";

        return $text;
    }


    /**
     *
     */
    function getOpportunityCalendarView() {
        $viewHelper = Zend_Registry::get('viewHelper');
        $ln = Zend_Registry::get('ln');
        $cpCfg = Zend_Registry::get('cpCfg');
        $c = &$this->controller;

        $text = "
        <div id='{$c->handle}' class='{$c->cssClass}'>
        </div>
        ";

        $headerObj = "
        {
             left: '{$c->headerLeft}'
            ,center: '{$c->headerCenter}'
            ,right: '{$c->headerRight}'
        }
        ";

        $timeFormatObj = "{
             {$c->monthTimeFormat}
            ,{$c->genTimeFormat}
            }
        ";

        $minTime = $c->minTime;
        $maxTime = $c->maxTime;

        CP_Common_Lib_Registry::arrayMerge('inlineScripts', array("
            exp = {
                 handle: '{$c->handle}'
                ,eventAction: '{$c->eventAction}'
                ,headerObj: $headerObj
                ,timeFormatObj: $timeFormatObj
                ,minTime: $minTime
                ,maxTime: $maxTime
            }
            cpm.project.followUp.run(exp);
        "));


        $text = "
        <div id='{$c->handle}'></div>
        ";
        return $text;
    }
     /**
     *
     */
    function getEditFollowup($opportunity_id = '') {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');


        if($opportunity_id == ''){
            $opportunity_id = $fn->getReqParam('opportunity_id');
        }
        $row = '';
        $record_id = $fn->getIssetParam($row, 'opportunity_id');
        $record_id = $fn->getReqParam('record_id');
        $comment_id = $fn->getReqParam('comment_id');

        $rows  = "";
        $row = '';
        $formAction = "index.php?module=project_followUp&_spAction=EditFollowupFormSubmit&showHTML=0&record_id={$opportunity_id}";
        $rowcomment = $fn->getRecordRowByID('comment', 'comment_id', $comment_id);

        /*$SQL="
        SELECT c.*
        FROM comment c
        WHERE c.record_id = '{$opportunity_id}'
        ";
        $result   = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);*/

        //$count = 1;
        //while ($row = $db->sql_fetchrow($result)) {

            $rows .= "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            {$formObj->getTARow('Notes', 'comments', $rowcomment['comments'])}
            <input type='hidden' name='comment_id' value='{$comment_id}' />
        </form>
        ";
           // $count++;
       // }

        $text="{$rows}";

        return $text;
    }
    /**
     *
     */
    function getEditFollowupFormSubmit() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');

        if (!$this->getFollowupValidate()){
            return $validate->getErrorMessageXML();
        }

        $opportunity_id = $fn->getPostParam('opportunity_id');
        $comments       = $fn->getPostParam('comments');
        $record_id      = $fn->getPostParam('record_id');
        $comment_id     = $fn->getPostParam('comment_id');
        $room_name      = 'project_opportunity';

        $fa1 = array();
        $fa['record_id']      = $opportunity_id;
        $fa['comments']       = $comments;
        $fa['room_name']      = $room_name;
        $fa['comment_id']     = $comment_id;
        $fa['creation_date']  = date("Y-m-d H:i:s");

        $fa1['record_id']           = $opportunity_id;
        $fa1['comments']            = $comments;
        $fa1['room_name']           = $room_name;
        $fa1['comment_id']          = $comment_id;
        $fa1['modification_date']   = date("Y-m-d H:i:s");
        //$fa1['modified_by']         = $fn->getSessionParam('userName');

        $whereConditionFollowUp = "WHERE comment_id = {$comment_id}" ;
        $sqlUpdateFollowUp      = $dbUtil->getUpdateSQLStringFromArray($fa1, "comment", $whereConditionFollowUp);
        $resultUpdateFollowUp   = $db->sql_query($sqlUpdateFollowUp);

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getOpportunityDetails(){
        $tv      = Zend_Registry::get('tv');
        $db      = Zend_Registry::get('db');
        $fn      = Zend_Registry::get('fn');
        $dbUtil  = Zend_Registry::get('dbUtil');
        $formObj = Zend_Registry::get('formObj');
        $cpCfg   = Zend_Registry::get('cpCfg');

        $currentDate  = date("Y-m-d");

        $appendSqlAp = '';
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSqlAp = "AND a.site_id = {$cpSiteIdSession}";
        }

        $opportunityDetailsRow = '';
        $sqlFollowupDetails = "
        SELECT o.*
        FROM opportunity o
        WHERE (o.status != 'Cancelled' OR o.status != 'Win')
        {$appendSqlAp}
        ";
        $resultFollowupDetails = $db->sql_query($sqlFollowupDetails);
        while ($rowFollowupDetails = $db->sql_fetchrow($resultFollowupDetails)) {
            $opportunityDetailsRow .="
            <tr>
                <td>{$rowFollowupDetails['title']}</td>
                <td>{$rowFollowupDetails['status']}</td>
                <td>{$rowFollowupDetails['follow_up_date']}</td>
            </tr>
            ";
        }

        $opportunityDetails = "
        <div id='opportunityDetails'>
            <div class='header'>
                <div class='floatbox'>
                    <div  class='txtCenter'>Opportunity</div>
                </div>
            </div>

            <div  class='FollowupScroll'>
                <table class='thinlist'>
                    <thead>
                        <tr>
                            <th>Opp.title</th>
                            <th>Status</th>
                            <th>Follow up date</th>
                        </tr>
                    </thead>
                    <tbody>
                        {$opportunityDetailsRow}
                    </tbody>
                </table>
            </div>
        </div>
        ";

        return $opportunityDetails;
    }
    /**
     *
     * @param <type> $SQL
     * @return <type>
     */

}