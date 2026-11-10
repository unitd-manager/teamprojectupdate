<?
class CP_Common_Modules_Web2_Tags_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {
        $SQL = "
        SELECT t.* 
        FROM tags t
        ";
        
        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $searchVar = Zend_Registry::get('searchVar');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $searchVar->mainTableAlias = 't';

        $language = $fn->getReqParam('language');

        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "t.tags_id = {$tv['record_id']}";
        }

        if ($language != '' ) {
            $searchVar->sqlSearchVar[] = "t.language = '{$language}'";
        }

        if ($tv['keyword'] != "") {
            $searchVar->sqlSearchVar[] = "( t.tag_text   LIKE '%{$tv['keyword']}%'
                                          )";
        }

		$searchVar->sortOrder = "t.tags_id";
    }

    /**
     *
     */
    function updateCloudTagsByIds($record_type, $id) {
        global $db, $dbUtilCommon;
		
		/** when values are sent as comma seperated values **/
		if ($fldName != ''){
        	$tags_text = $this->getPostParam($fldName);
        	$tags_arr = explode(',', $tags_text);
        } else {
        	$tags_arr = $this->getPostParam('tags_ids', array());
        }

        $new_tag_ids = array();

        foreach($tags_arr as $tags_id) {
            $new_tag_ids[]  = $tags_id;

            /**** check whether the record exists in history ***/
            $SQL = "
            SELECT COUNT(*)
            FROM tags_history
            WHERE tags_id     = {$tags_id}
              AND record_id   = {$id}
              AND record_type = '{$record_type}'
                    ";
            $result = $db->sql_query($SQL);
            $row    = $db->sql_fetchrow($result);

            if ($row[0] == 0) {
                $fa = array();           
                $fa['tags_id']           = $tags_id;
                $fa['record_type']       = $record_type;
                $fa['record_id']         = $id;
                $fa['creation_date']     =  date("Y-m-d H:i:s");
                $fa['modification_date'] =  date("Y-m-d H:i:s");

                $SQL    = $dbUtilCommon->getInsertSQLStringFromArray($fa, "tags_history");
                $result = $db->sql_query($SQL);
            }
        }

        $list = "'". implode("', '", $new_tag_ids) ."'";

        /**** delete the redundant tag history records  ***/
        $SQL     = "
        DELETE FROM tags_history
        WHERE tags_id NOT IN ({$list})
          AND record_id   = '{$id}'
          AND record_type = '{$record_type}'
                ";
        $result  = $db->sql_query($SQL);
    }

    function updateCloudTagsByCSV($record_type, $id, $fldArr, $fldName) {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $dbUtil = Zend_Registry::get('dbUtil');
		
	    $tags_text = $fldArr[$fldName];
    	$tags_arr = explode(',', $tags_text);

        $langFld = $tv['lang'] == 'eng' ? '' : $tv['lang'];
        $new_tag_ids = array();
        
        foreach($tags_arr as $tag_text) {
        	$tag_text = trim($tag_text);
        	//$tagsRec = $fn->getRecordByCondition('tags', "tag_text = '". qstr($tag_text) . "'");
            
            $condition = "tag_text = '". mysql_real_escape_string($tag_text) . "'";
        	$tagsRec = $fn->getRecordByCondition('tags', $condition);
        	
        	if (is_array($tagsRec)){
				$tags_id = $tagsRec['tags_id'];
        	} else {
		        $fa = array();
                $fa["{$langFld}tag_text"] = $tag_text;
                $fa['published'] = 1;
        		$fa = $fn->addCreationDetailsToFieldsArray($fa, 'tags');

                $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'tags');
                $result = $db->sql_query($SQL);
        		$tags_id = $db->sql_nextid();
        	}

			$new_tag_ids[] = $tags_id;
	
            /**** check whether the record exists in history ***/
            $SQL = "
            SELECT 1
            FROM tags_history
            WHERE tags_id     = {$tags_id}
              AND record_id   = {$id}
              AND record_type = '{$record_type}'
            ";
            $result = $db->sql_query($SQL);
            $row    = $db->sql_fetchrow($result);
            $numRows = $db->sql_numrows();

            if ($numRows == 0) {
                $fa = array();           
                $fa['tags_id']           = $tags_id;
                $fa['record_type']       = $record_type;
                $fa['record_id']         = $id;
                $fa['creation_date']     =  date('Y-m-d H:i:s');
                $fa['modification_date'] =  date('Y-m-d H:i:s');

                $SQL    = $dbUtil->getInsertSQLStringFromArray($fa, 'tags_history');
                $result = $db->sql_query($SQL);
            }
        }

        $list = "'". implode("', '", $new_tag_ids) ."'";

        /**** delete the redundant tag history records  ***/
        $SQL     = "
        DELETE FROM tags_history
        WHERE tags_id NOT IN ({$list})
          AND record_id   = '{$id}'
          AND record_type = '{$record_type}'
		";
        $result  = $db->sql_query($SQL);
    }
}
