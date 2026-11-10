<?
class CP_Admin_Modules_Pms_ParentLink_Model extends CP_Common_Lib_ModuleLinkModelAbstract
{
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $db = Zend_Registry::get('db');

        $validate->resetErrorArray();

        $validate->validateData('first_name', 'Please enter the first name');
        $validate->validateData('last_name', 'Please enter the last name');
        $validate->validateData('id_card_no' , 'Please enter the NRIC no.');

        $id_card_no = $fn->getPostParam('id_card_no', '', true);

        if ($id_card_no != ''){
            $rec = $fn->getRecordByCondition('contact', "id_card_no = '{$id_card_no}'");
            $expIdCard = array('displayText' => 'Go to this record', 'target' => '_blank');
            $IdCardlink = $fn->getRecordDetailLink('pms_parent', 'record_id', $rec['parent_id'], $expIdCard);
    
            if (is_array($rec)){
                $validate->errorArray['id_card_no']['name'] = "id_card_no";
                $validate->errorArray['id_card_no']['msg']  = "NRIC no. already exists. '{$IdCardlink}'";
            }
        }

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
        $tv = Zend_Registry::get('tv');

        $contact_id = $fn->getReqParam('contact_id');

        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }
        
        $fa = $this->getFields();

        $fa['dda']         = $fn->getSettingsValueByKey("nextParentCode");
        /* Updation of parent code */
        $modObj = getCPModuleObj('pms_parent');
        $fa['parent_code'] = $modObj->model->getUpdateParentCode();

        $id = $fn->addRecord($fa);
       
        $fa1 = array();
        $fa1['contact_id'] = $contact_id;
        $fa1['parent_id'] = $id;
       
        $id_parent_contact = $fn->addRecord($fa1, 'parent_contact');
        
        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getEditPortalValidate() {
        return $this->getNewValidate();
    }

    /**
     *
     */
    function getSave(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        if (!$this->getEditPortalValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $id = $fn->saveRecord($fa);
        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getFields(){
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'company_id');
        $fa = $fn->addToFieldsArray($fa, 'first_name');
        $fa = $fn->addToFieldsArray($fa, 'last_name');
        $fa = $fn->addToFieldsArray($fa, 'id_card_no');
        $fa = $fn->addToFieldsArray($fa, 'phone');
        $fa = $fn->addToFieldsArray($fa, 'mobile');
        $fa = $fn->addToFieldsArray($fa, 'email');
        $fa = $fn->addToFieldsArray($fa, 'relationship_to_student');
        $fa = $fn->addToFieldsArray($fa, 'occupation');
        $fa = $fn->addToFieldsArray($fa, 'address_flat');
        $fa = $fn->addToFieldsArray($fa, 'address_street');
        $fa = $fn->addToFieldsArray($fa, 'address_po_code');
        $fa = $fn->addToFieldsArray($fa, 'address_country');
        
        return $fa;
    }
    
    /**
     *
     */
    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');

        $mode_of_payment            = $fn->getReqParam('mode_of_payment');
        $parent_id                  = $fn->getReqParam('parent_id');
        $subscribe                  = $fn->getReqParam('subscribe');
        $special_search             = $fn->getReqParam('special_search');
        $continuing_to_next_year    = $fn->getReqParam('continuing_to_next_year');
        $enrollment_year            = $fn->getReqParam('enrollment_year');
        $course_id                  = $fn->getReqParam('course_id');
        $student_status             = $fn->getReqParam('student_status');
        $batch_id                   = $fn->getReqParam('batch_id');

        $searchVar->mainTableAlias = 'pc';

        // to check if the record exists in parent_contact
        // if no then hide the search var condition, in edit mode
        
        //$recParentContact = $fn->getRecordRowById('parent_contact', 'parent_id', $parent_id);
        //if ($tv['action'] == 'edit' && is_array($recParentContact)) {
        //    $searchVar->mainTableAlias = 'pc';
        //} else  if ($tv['action'] == 'list') {
        //    $searchVar->mainTableAlias = 'pc';
        //}

        if ($enrollment_year == '') {
            $enrollment_year = date('Y');
        }

        if ($parent_id != "") {
            $searchVar->sqlSearchVar[] = "p.parent_id = '{$parent_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "p.parent_id = '{$tv['record_id']}'";
        } else {

            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'p.parent_id');

            //------------------------------------------------------------------------//
            if ($tv['special_search'] == "Subscribed") {
                $searchVar->sqlSearchVar[] = "p.subscribe = 1";
            }

            if ($tv['special_search'] == "Not-Subscribed") {
                $searchVar->sqlSearchVar[] = "(p.subscribe != 1 OR p.subscribe IS null)";
            }

            if ($tv['special_search'] == "Flagged") {
                $searchVar->sqlSearchVar[] = "p.flag = 1";
            }

            if ($tv['special_search'] == "Not-Flagged") {
                $searchVar->sqlSearchVar[] = "(p.flag != 1 OR p.flag IS null)";
            }

            if ($tv['special_search']  == 'Published') {
                $searchVar->sqlSearchVar[] = "p.published = 1";
            }

            if ($tv['special_search'] == 'Not-Published' ) {
                $searchVar->sqlSearchVar[] = "p.published = 0 OR p.published IS NULL OR p.published = ''";
            }

            if ($tv['special_search'] == 'Half Subsidy') {
                $searchVar->sqlSearchVar[] = "cc.discount = 30 AND cc.year_of_enrollment = '{$enrollment_year}'";
            }

            if ($tv['special_search'] == 'Full Subsidy') {
                $searchVar->sqlSearchVar[] = "cc.discount = 60 AND cc.year_of_enrollment = '{$enrollment_year}'";
            }

            if ($mode_of_payment != '') {
                $searchVar->sqlSearchVar[] = "p.mode_of_payment = '{$mode_of_payment}'";
            }

            /*
            if ($giro_process_done != '') {
                if ($giro_process_done == 'Yes') {
                    $searchVar->sqlSearchVar[] = "p.giro_process_done = 1";
                } else {
                    $searchVar->sqlSearchVar[] = "p.giro_process_done = 0";
                }
            }
            */

            if ($course_id != '') {
                $searchVar->sqlSearchVar[] = "cc.course_id = {$course_id}";
            }

            if ($batch_id != '' ) {
                $searchVar->sqlSearchVar[] = "cc.batch_id = {$batch_id}";
            }

            //------------------------------------------------------------------------//
            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                       p.first_name      LIKE '%{$tv['keyword']}%'
                    OR p.last_name       LIKE '%{$tv['keyword']}%'
                    OR p.email           LIKE '%{$tv['keyword']}%'
                    OR p.dda             LIKE '%{$tv['keyword']}%'
                    OR p.id_card_no      LIKE '%{$tv['keyword']}%'
                    OR p.phone           LIKE '%{$tv['keyword']}%'
                    OR p.mobile          LIKE '%{$tv['keyword']}%'
                    OR p.address_flat    LIKE '%{$tv['keyword']}%'
                    OR p.address_street  LIKE '%{$tv['keyword']}%'
                    OR p.address_po_code LIKE '%{$tv['keyword']}%'
                )";

                //$searchVar->sqlSearchVar[] = "cc.year_of_enrollment = '{$enrollment_year}' OR cc.year_of_enrollment IS NULL";
            } else {
                if ($student_status != '' ) {
                    $searchVar->sqlSearchVar[] = "c.status = '{$student_status}'";
                    
                    if ($student_status == 'Graduated') {
                        $searchVar->sqlSearchVar[] = "c.graduation_year = '{$enrollment_year}'";
                    } else {
                        $searchVar->sqlSearchVar[] = "cc.year_of_enrollment = '{$enrollment_year}'";
                    }
                } else {
                    $searchVar->sqlSearchVar[] = "c.status = 'Active'";
                }

                if ($enrollment_year != '') {
                    $searchVar->sqlSearchVar[] = "cc.year_of_enrollment = '{$enrollment_year}'";
                }
            }

            if ($continuing_to_next_year != "") {
                if ($continuing_to_next_year == "Yes") {
                    $searchVar->sqlSearchVar[] = "c.continuing_to_next_year = 1 AND c.status = 'Active'";
                } else if ($continuing_to_next_year == "No"){
                    $searchVar->sqlSearchVar[] = "c.continuing_to_next_year = 0 AND c.status = 'Active'";
                }   
            }

            if ($tv['spAction'] == 'link' && $tv['module'] == 'broadcast' ){
                $searchVar->sqlSearchVar[] = "p.subscribe = 1";
            }

            $searchVar->sortOrder = "p.parent_code";
        }
    }

    /**
     *
     */
    function setSearchVarOld($linkRecType) {
        $searchVar = Zend_Registry::get('searchVar');

        $modObj = getCPModuleObj('pms_parent');
        $modObj->model->setSearchVar($linkRecType);

        //$searchVar->sqlSearchVar[] = "c.subscribe = 1";
                
    }
    /**
     *
     */
    function getAddNewGridItem(){
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        $fa = $this->getFields();
        $fa['batch_id'] = $tv['srcRoomId'];
        $id = $fn->addRecord($fa);
    }

    /**
     *
     */
    function getSaveGridItem(){
        $fn = Zend_Registry::get('fn');
        
        $fa = $this->getFields();
        $id = $fn->saveRecord($fa);
    }
}
