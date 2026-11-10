<?
class CP_Admin_Modules_Project_FollowUp_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {

        $SQL = "
        SELECT o.*
              ,c.company_name
        FROM opportunity o
        LEFT JOIN (company c) ON (o.company_id = c.company_id)
       ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar1($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');
        $searchVar->mainTableAlias = 'c';

        $status       = $fn->getReqParam('status');
        $company_id   = $fn->getReqParam('company_id');
        $company_name = $fn->getReqParam('company_name');

        if ($company_id != "") {
            $searchVar->sqlSearchVar[] = "c.company_id = '{$company_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "c.company_id = '{$tv['record_id']}'";
        } else {
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'c.company_id');


            if ($status != "") {
                $searchVar->sqlSearchVar[] = "c.status = '{$status}'";
            }

            if ($company_name != "") {
                $searchVar->sqlSearchVar[] = "c.company_name LIKE '%{$company_name}%'";
            }

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                    c.company_name  LIKE '%{$tv['keyword']}%'
                    OR c.group_name LIKE '%{$tv['keyword']}%'
                    OR c.email      LIKE '%{$tv['keyword']}%'
                )";
            }

            //------------------------------------------------------------------------//
            if ($tv['special_search'] == "Flagged") {
                $searchVar->sqlSearchVar[] = "c.flag = 1";
            }

            if ($tv['special_search'] == "Not-Flagged") {
                $searchVar->sqlSearchVar[] = "(c.flag != 1 OR c.flag IS null)";
            }

            //$searchVar->sortOrder = "c.company_name";
        }
    }

    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('company_name', 'Please enter the company name');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getAdd(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $fa['category'] = 'Client';
        $id = $fn->addRecord($fa);
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getEditValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getSave($tableName = '', $keyField = '', $keyFieldVal = ''){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $cpCfg = Zend_Registry::get('cpCfg');

        if (!$this->getEditValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $id = $fn->saveRecord($fa, $tableName, $keyField, $keyFieldVal);
        $fn->returnAfterNewSave($id, $cpCfg['cp.pagetoReturnAfterSave']);
    }

    /**
     *
     */
    function getSaveList(){
        $fn = Zend_Registry::get('fn');
        $fn->getSaveList();
    }

    /**
     *
     */
    function getFields(){
        $fn = Zend_Registry::get('fn');

        $fa = array();
        $fa = $fn->addToFieldsArray($fa, 'company_name');
        $fa = $fn->addToFieldsArray($fa, 'code');
        $fa = $fn->addToFieldsArray($fa, 'website');
        $fa = $fn->addToFieldsArray($fa, 'company_size');
        $fa = $fn->addToFieldsArray($fa, 'industry');
        $fa = $fn->addToFieldsArray($fa, 'source');
        $fa = $fn->addToFieldsArray($fa, 'address_flat');
        $fa = $fn->addToFieldsArray($fa, 'address_street');
        $fa = $fn->addToFieldsArray($fa, 'address_town');
        $fa = $fn->addToFieldsArray($fa, 'address_state');
        $fa = $fn->addToFieldsArray($fa, 'address_country');
        $fa = $fn->addToFieldsArray($fa, 'address_po_code');
        $fa = $fn->addToFieldsArray($fa, 'billing_address_flat');
        $fa = $fn->addToFieldsArray($fa, 'billing_address_street');
        $fa = $fn->addToFieldsArray($fa, 'billing_address_town');
        $fa = $fn->addToFieldsArray($fa, 'billing_address_state');
        $fa = $fn->addToFieldsArray($fa, 'billing_address_country');
        $fa = $fn->addToFieldsArray($fa, 'phone');
        $fa = $fn->addToFieldsArray($fa, 'fax');
        $fa = $fn->addToFieldsArray($fa, 'group_name');
        $fa = $fn->addToFieldsArray($fa, 'status');
        $fa = $fn->addToFieldsArray($fa, 'category');
        $fa = $fn->addToFieldsArray($fa, 'source');
        $fa = $fn->addToFieldsArray($fa, 'industry');
        $fa = $fn->addToFieldsArray($fa, 'company_size');
        $fa = $fn->addToFieldsArray($fa, 'supplier_type');
        $fa = $fn->addToFieldsArray($fa, 'customer_type');
        $fa = $fn->addToFieldsArray($fa, 'description');
        $fa = $fn->addToFieldsArray($fa, 'notes');
        $fa = $fn->addToFieldsArray($fa, 'title');

        return $fa;
    }



    /**
     *
     * @param <type> $SQL
     * @return <type>
     */
    function getEventDetails(){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg   = Zend_Registry::get('cpCfg');

        $jsonArray = array();


        $SQL = "
        SELECT o.*
              ,c.company_name
        FROM opportunity o
        LEFT JOIN (company c) ON (o.company_id = c.company_id)
       ";
        $result  = $db->sql_query($SQL);

        $title = '';
        while ($row = $db->sql_fetchrow($result)) {

            $eventStartdate    = $row['enquiry_date'] .' ' . '10:00:00';
            $eventEnddate      = $row['enquiry_date'] .' ' . '16:00:00';

            if($row['follow_up_date'] != ''){
                 $eventStartdate    = $row['follow_up_date'] .' ' . '10:00:00';
                 $eventEnddate      = $row['follow_up_date'] .' ' . '16:00:00';
            }

            $followUpLink      = "index.php?module=project_followUp&_spAction=followUpDetails&opportunity_id={$row['opportunity_id']}&showHTML=0";
            $company_name      = "<a class='evenDetails' href='{$followUpLink}'>{$row['company_name']}</a>";
            $opportunitytitle  = "<a class='evenDetails' href='{$followUpLink}'>{$row['title']}</a>";

            if ($row['status'] == 'Follow up') {
                $backgroundColor = '#51EF51';
            } else if ($row['status'] == 'Cancelled') {
                $backgroundColor = '#F23C3C';
            } else {
                $backgroundColor = '#3A87AD';
            }

            $buildjson = array(
              'title'             => ''
             ,'opportunitytitle'  => $opportunitytitle
             ,'company_name'      => $company_name
             ,'start'             => $eventStartdate
             ,'end'               => $eventEnddate
             ,'allDay'            => false
             ,'opportunity_id'    => $row['opportunity_id']
             ,'backgroundColor'   => $backgroundColor
             ,'borderColor'       => $backgroundColor
             );

            // Adds each array into the container array
            array_push($jsonArray, $buildjson);
        }

        echo json_encode($jsonArray);
    }

    /**
     *
     * @param <type> $SQL
     * @return <type>
     */

    function getFollowUpDetails($title=''){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $formObj = Zend_Registry::get('formObj');

        $expNoEdit = array('disabled' => true);

        $opportunity_id  = $fn->getReqParam('opportunity_id');
        $visitDetailsRow = $this->getAddFollowup($opportunity_id);
        $rowFollowUp     = $fn->getRecordRowByID('opportunity', 'opportunity_id', $opportunity_id);

        $status          = $fn->getReqParam('status');

        $row = '';
        $sqlStatus = "
        SELECT value
        FROM valuelist
        WHERE key_text = 'opportunityStatus'
          AND value != 'Won'
        ORDER BY sort_order
        ";
        $expStatus = array('sqlType' => 'OneField');
        $followUpDetails = "
        <div id='followUpDetails1'>
            <form class='yform columnar'>
                {$formObj->getDDRowBySQL('Status', 'status', $sqlStatus, $rowFollowUp['status'], $expStatus)}
                {$formObj->getTBRow('Opportunity Title', 'title', $rowFollowUp['title'], $expNoEdit)}
                <input type='hidden' id='opportunity_id' value='{$opportunity_id}' />
            </form>
            <div  class='followUpvisitScroll1'>
                {$visitDetailsRow}
            </div>
        </div>
        ";

        return $followUpDetails;
    }
    /**
     *
     */
    function getAddFollowup($opportunity_id='', $record_id=''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        if($opportunity_id == ''){
            $opportunity_id = $fn->getReqParam('opportunity_id');
        }
        $row = '';
        $record_id = $fn->getIssetParam($row, 'opportunity_id');
        $Comments = $this->getAddFollowupDetail($opportunity_id);
        $recCount = $fn->getRecordCount('comment', "record_id = '{$opportunity_id}'");

        $header ="
        <thead>
            <tr>
            <th>Notes ({$recCount})</th>
            <th>Created By</th>
            <th>Date</th>
            </tr>
        </thead>
        ";

        if($recCount == 0){
            $header ="<thead></thead>";
        }

        $formActionComments = "index.php?module=project_followUp&_spAction=FollowUpNotes&opportunity_id={$opportunity_id}&showHTML=0";

        $add = "<div class='actBtns'>
                    <a id='renewalLinkPortal' record_id='{$record_id}' opportunity_id='{$opportunity_id}'  href='{$formActionComments}' opportunity_id={$opportunity_id}>Add Notes</a>
                </div>";

        $text = "
        <div class='linkPortalWrapper project_followUp__project_commentLink'>
            {$add}
            <!--<div class='header' expanded='1'>
                <div class='floatbox'>
                    <div class='float_left'>Notes</div>
                    <div class='txtRight'>
                    <span class='count'>({$recCount})</span>
                    </div>
                </div>
            </div>-->
            <div class='linkPortalDataWrapper'>
                <form>
                    <table class='renewallist'>
                        {$header}
                        <tbody id='AddFollowUpPortal'>
                            {$Comments}
                        </tbody>
                    </table>
                    <input type='hidden' name='opportunity_id' value='{$opportunity_id}' />
                </form>
            </div>
            
        </div>
        ";

        return $text;

    }
    /**
     *
     */
    function getAddFollowupDetail($opportunity_id = ''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');
        $dateUtil = Zend_Registry::get('dateUtil');

        if($opportunity_id == ''){
            $opportunity_id = $fn->getReqParam('opportunity_id');
        }
        $row = '';
        $record_id = $fn->getIssetParam($row, 'opportunity_id');
        $record_id = $fn->getReqParam('record_id');
        $staff_id = $fn->getReqParam('staff_id');
        $comment_id = $fn->getReqParam('comment_id');

        $rows  = "";

        $SQL="
        SELECT c.*
              ,s.staff_id
              ,CONCAT_WS(' ', s.first_name, s.last_name) AS staff_name
        FROM comment c
        LEFT JOIN (staff s) ON (s.staff_id = c.staff_id)
        WHERE c.record_id = {$opportunity_id}
        AND room_name = 'project_opportunity'
        ORDER BY comment_date DESC
        ";
        $result   = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        $count = 1;
        while ($row = $db->sql_fetchrow($result)) {

            $formActionEditFollowup = "index.php?module=project_followUp&_spAction=EditFollowup&comment_id={$row['comment_id']}&showHTML=0";

            /*$deleteIcon ="
                <div class='float_right'>
                    <a class='deletefollowup' href='#' opportunity_id='{$opportunity_id}'  comment_id='{$row['comment_id']}'>
                        <img src='/cmspilotv30/CP/admin/images/icons/btn_remove.png'>
                    </a>
                </div>
                ";

            $editIcon ="
                <div class='float_right'>
                    <a class='EditFollowup' href='{$formActionEditFollowup}' opportunity_id='{$opportunity_id}' comment_id='{$comment_id} opportunity_id='{$opportunity_id}'>
                        <img src='/cmspilotv30/CP/admin/images/icons/btn_edit.png'>
                    </a>
                </div>
                ";*/

            $comment       = nl2br($row['comments']);
            $creation_date = $dateUtil->formatDate($row['creation_date'], 'DD-MM-YYYY HH:MIN:SS AM');
            $rows .= "
                <tr>
                    <td>{$comment}</td>
                    <td>{$row['staff_name']}</td>
                    <td>{$creation_date}</td>
                </tr>
            ";
            $count++;
        }


        if($numRows == 0){
            $rows .= "
                <tr>
                    <td class='noRenewal'>No Records Linked</td>
                </tr>
            ";

        }
        $text="{$rows}";

        return $text;
    }
    /**
     *
     */
    function getFollowUpNotes() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $opportunity_id = $fn->getReqParam('opportunity_id');
        $rowFollowUp    = $fn->getRecordRowByID('opportunity', 'opportunity_id', $opportunity_id);

        $formAction = "index.php?_topRm=order&module=project_followUp&_spAction=FollowUpNotesSubmit&showHTML=0";


        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            {$formObj->getDateRow('Follow up Date', 'follow_up_date', $rowFollowUp['follow_up_date'])}
            {$formObj->getTARow('Notes', 'comments')}
            <input type='hidden' name='opportunity_id' value='{$opportunity_id}' />
        </form>
        ";
        return $text;
    }
    /**
     *
     */
    function getFollowUpNotesSubmit() {
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
        $staff_id       = $fn->getPostParam('staff_id');
        $comment_id     = $fn->getPostParam('comment_id');
        $follow_up_date = $fn->getPostParam('follow_up_date');
        $room_name      = 'project_opportunity';

        $fa = array();
        $fa['record_id']      = $opportunity_id;
        $fa['comments']       = $comments;
        $fa['room_name']      = $room_name;
        $fa['comment_id']     = $comment_id;
        $fa['staff_id']       = $_SESSION['staff_id'];
        $fa['creation_date']  = date("Y-m-d H:i:s");
        $fa['comment_date']   = date("Y-m-d H:i:s");
        $fa['published']      = "1";
        $fa['contact_id']     = $_SESSION['staff_id'];
        //$fa['created_by']     = $fn->getSessionParam('userName');

        $insertFollowUpSQL = $dbUtil->getInsertSQLStringFromArray($fa, 'comment');
        $resultFollowUpSQL = $db->sql_query($insertFollowUpSQL);
        $new_comment_id    = $db->sql_nextid();

        $faFollowUp = array();
        $faFollowUp['follow_up_date']    = $follow_up_date;
        $faFollowUp['modified_by']       = $fn->getSessionParam('userName');
        $faFollowUp['modification_date'] = date("Y-m-d H:i:s");

        $whereConditionFollowUp = "WHERE opportunity_id = {$opportunity_id}" ;
        $sqlUpdateFollowUp      = $dbUtil->getUpdateSQLStringFromArray($faFollowUp, "opportunity", $whereConditionFollowUp);
        $resultUpdateFollowUp   = $db->sql_query($sqlUpdateFollowUp);

        $comment = getCPPluginObj('common_comment');
        $comment->model->sendNotificationToAdmin($new_comment_id);

        return $validate->getSuccessMessageXML();
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

        //$fa1['record_id']           = $opportunity_id;
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
    function getFollowupValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('comments', 'Please enter the notes');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }
    /**
     *
     */
    function getDeletefollowup(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');

        $opportunity_id = $fn->getReqParam('opportunity_id');
        $comment_id = $fn->getReqParam('comment_id');

        $SQL ="
               DELETE FROM comment
               WHERE comment_id = {$comment_id}
               ";
        $result = $db->sql_query($SQL);
    }
    /**
     *
     */
    function getUpdateFollowUpStatus(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $opportunity_id = $fn->getReqParam('opportunity_id');
        $status          = $fn->getReqParam('status');

        $SQLUpdateNotes = "
        UPDATE opportunity SET status = '{$status}'
        WHERE opportunity_id = {$opportunity_id}
        ";

        $resultUpdateNotes = $db->sql_query($SQLUpdateNotes);
    }
    /**
     *
     * @param <type> $SQL
     * @return <type>
     */

}
