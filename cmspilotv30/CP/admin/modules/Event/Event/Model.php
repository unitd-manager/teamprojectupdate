<?
class CP_Admin_Modules_Event_Event_Model extends CP_Common_Modules_Event_Event_Model
{
    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

        $validate->validateData('title', 'Please enter the title');

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
        $cpCfg = Zend_Registry::get('cpCfg');

        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();

        if ($cpCfg['m.event.event.showEventType']){
            $fa['content_type'] = 'Event';
        }

        $fa['creation_date'] = date("Y-m-d H:i:s");
        $id = $fn->addRecord($fa);
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getEditValidate() {
        $validate = Zend_Registry::get('validate');
        $cpCfg = Zend_Registry::get('cpCfg');

        $validate->resetErrorArray();

        $validate->validateData('title', 'Please enter the title');

        if ($cpCfg['m.event.event.showAvailableSeats']){
            $validate->validateData('available_seats', 'Please enter the available seats as number', 'number');
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
    function getSave($tableName = '', $keyField = '', $keyFieldVal = ''){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        if (!$this->getEditValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        //print $fa['repeat_every_day'] . 'aaaaaaaaaaaaaa';
        //return;
        $id = $fn->saveRecord($fa, $tableName, $keyField, $keyFieldVal);
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getFields() {
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $fa = array();
        $fa = $fn->addToFieldsArray($fa, 'title', '', true);
        $fa = $fn->addToFieldsArray($fa, 'description', '', true);
        $fa = $fn->addToFieldsArray($fa, 'section_id');
        $fa = $fn->addToFieldsArray($fa, 'category_id');
        $fa = $fn->addToFieldsArray($fa, 'sub_category_id');
        $fa = $fn->addToFieldsArray($fa, 'content_type');
        $fa = $fn->addToFieldsArray($fa, 'event_date_text', '', true);
        $fa = $fn->addToFieldsArray($fa, 'event_date');
        $fa = $fn->addToFieldsArray($fa, 'event_end_date');
        $fa = $fn->addToFieldsArray($fa, 'is_holiday');
        $fa = $fn->addToFieldsArray($fa, 'event_time', '', true);
        $fa = $fn->addToFieldsArray($fa, 'contact_no');
        $fa = $fn->addToFieldsArray($fa, 'event_venue', '', true);
        $fa = $fn->addToFieldsArray($fa, 'speaker', '', true);
        $fa = $fn->addToFieldsArray($fa, 'published');
        $fa = $fn->addToFieldsArray($fa, 'description_short', '', true);
        $fa = $fn->addToFieldsArray($fa, 'external_link', '', true);
        $fa = $fn->addToFieldsArray($fa, 'latest');

        $fa = $fn->addToFieldsArray($fa, 'meta_title', '', $cpCfg['cp.hasMultiLangForMetaData']);
        $fa = $fn->addToFieldsArray($fa, 'meta_keyword', '', $cpCfg['cp.hasMultiLangForMetaData']);
        $fa = $fn->addToFieldsArray($fa, 'meta_description', '', $cpCfg['cp.hasMultiLangForMetaData']);

        $fa = $fn->addToFieldsArray($fa, 'free');
        $fa = $fn->addToFieldsArray($fa, 'show_speaker');
        $fa = $fn->addToFieldsArray($fa, 'show_event_item');
        $fa = $fn->addToFieldsArray($fa, 'show_registration');
        $fa = $fn->addToFieldsArray($fa, 'ok_for_web');
        $fa = $fn->addToFieldsArray($fa, 'ok_for_mobile');
        $fa = $fn->addToFieldsArray($fa, 'available_seats');
        $fa = $fn->addToFieldsArray($fa, 'reg_end_date');
        $fa = $fn->addToFieldsArray($fa, 'price');
        $fa = $fn->addToFieldsArray($fa, 'theme'); //not related to site theme, used in Pudao
        $fa = $fn->addToFieldsArray($fa, 'repeat_type');
        $fa = $fn->addToFieldsArray($fa, 'repeat_every');
        $fa = $fn->addToFieldsArray($fa, 'repeat_month_by');
        $fa = $fn->addToFieldsArray($fa, 'repeat_on_sun');
        $fa = $fn->addToFieldsArray($fa, 'repeat_on_mon');
        $fa = $fn->addToFieldsArray($fa, 'repeat_on_tue');
        $fa = $fn->addToFieldsArray($fa, 'repeat_on_wed');
        $fa = $fn->addToFieldsArray($fa, 'repeat_on_thu');
        $fa = $fn->addToFieldsArray($fa, 'repeat_on_fri');
        $fa = $fn->addToFieldsArray($fa, 'repeat_on_sat');

        return $fa;
    }

    /**
     *
     */
    function getSQLContact() {
        $cpCfg = Zend_Registry::get('cpCfg');

        $SQL = "
        SELECT contact_id
              ,CONCAT_WS(' ', first_name, last_name) AS contact_name
        FROM contact
        ORDER BY contact_name
        ";

        return $SQL;
    }

    /**
     *
     */
    function getEventEventEventEventItemLinkSQL($id) {

        $SQL = "
        SELECT a.event_item_id
              ,a.title
              ,a.price
              ,a.sort_order
        FROM `event_item` a
        WHERE a.event_id = {$id}
        ORDER BY a.event_item_id DESC
        ";
        return $SQL;
    }

    /**
     *
     */
    function getEventEventWeb2TagsLinkSQL($id) {
        $cpCfg = Zend_Registry::get('cpCfg');

        $SQL   = "
        SELECT th.tags_history_id
              ,t.tag_text
        FROM tags_history th
        LEFT JOIN tags t ON (t.tags_id = th.tags_id)
        WHERE th.event_id = {$id}
        ORDER BY t.tag_text DESC
        ";

        return $SQL;
    }

    /**
     *
     */
    function getJsonForDropdown() {
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');
        $cpCfg  = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');
        $fn     = Zend_Registry::get('fn');
        $ln     = Zend_Registry::get('ln');
        $modulesArr = Zend_Registry::get('modulesArr');

        $module   = $fn->getReqParam('room');
        $srcFld   = $fn->getReqParam('srcFld', '', true);
        $srcValue = $fn->getReqParam('srcValue', '', true);

        $json = array();



        if ($srcValue == ''){
            $json[] = array('value' => '', 'caption' => $ln->gd('cp.form.lbl.pleaseSelect'));
            return json_encode($json);
        }

        if($srcFld == 'repeat_type' ){
            $arr = array();
            if($srcValue == 'Daily'){
                $arr = $cpCfg['m.event.event.repeatDaysArr'];
            } else if($srcValue == 'Weekly'){
                $arr = $cpCfg['m.event.event.repeatWeeksArr'];
            } else if($srcValue == 'Monthly'){
                $arr = $cpCfg['m.event.event.repeatMonthsArr'];
            } else if($srcValue == 'Yearly'){
                $arr = $cpCfg['m.event.event.repeatYearArr'];
            }

            $json[] = array('value' => '', 'caption' => $ln->gd('Please Select'));
            foreach($arr AS $value){
                $json[] = array('value' => $value, 'caption' => $value);
            }

            return json_encode($json);
        }


        $exp = array('condn' => "{$srcFld} = '{$srcValue}'");
        $SQL = $fn->getDDSql($module, $exp);
        //print $SQL;
        $result = $db->sql_query($SQL);

        $json[] = array('value' => '', 'caption' => $ln->gd('cp.form.lbl.pleaseSelect'));
        while ($row = $db->sql_fetchrow($result)) {
            $json[] = array("value" => $row[0], "caption" => $row[1]);
        }

        return json_encode($json);
    }

}
