<?
class CP_Common_Modules_Common_Comment_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {
        $tv = Zend_Registry::get('tv');

        if ($tv['catType'] == 'My Reviews' || $tv['catType'] == 'Public Profile Reviews'){
            $SQL = "
            SELECT c.*
                  ,CONCAT_WS(' ', cont.first_name, cont.last_name) AS contact_name
            	  ,b.business_name
            	  ,b.category_id
            	  ,b.sub_category_id
            	  ,cat.title AS category_title
            	  ,sc.title AS sub_category_title
            FROM comment c
            JOIN (business b) ON (c.record_id = b.business_id)
            LEFT JOIN (contact cont) ON (c.contact_id = cont.contact_id)
            LEFT JOIN (category cat) ON (cat.category_id = b.category_id)
            LEFT JOIN (sub_category sc) ON (sc.sub_category_id = b.sub_category_id)
            ";
        } else {
            $SQL = "
            SELECT c.*
                  ,CONCAT_WS(' ', cont.first_name, cont.last_name) AS contact_name
                  ,(SELECT COUNT(*)
                    FROM contact_friend
                    WHERE friend_id = c.contact_id
                   ) AS user_no_of_followers

                  ,(SELECT COUNT(*)
                    FROM comment
                    WHERE contact_id = c.contact_id
                   ) AS user_comment_count
            FROM `comment` c
            LEFT JOIN (contact cont) ON (c.contact_id = cont.contact_id)
            ";
        }

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');
        $searchVar->mainTableAlias = 'c';
        $cpBusinessId = $fn->getSessionParam('cpBusinessId');
        $cpContactId = $fn->getSessionParam('cpContactId');

        if ($tv['record_id'] != '' ) {
            $searchVar->sqlSearchVar[] = "c.comment_id  = '{$tv['record_id']}'";

        } else {
            $searchVar->sqlSearchVar[] = 'c.parent_id = 0';
            $searchVar->sqlSearchVar[] = "c.room_name = 'directory_business'";

            if ($tv['catType'] == 'My Reviews'){
                $searchVar->sqlSearchVar[] = "c.contact_id = '{$cpContactId}'";
            } else if ($tv['catType'] == 'Public Profile Reviews'){
                $cpi = $fn->getReqParam('cpi');
                $searchVar->sqlSearchVar[] = "c.contact_id = '{$cpi}'";
            } else {
                $searchVar->sqlSearchVar[] = "c.record_id = '{$cpBusinessId}'";
            }

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                       c.subject LIKE '%{$tv['keyword']}%'
                    OR c.name LIKE '%{$tv['keyword']}%'
                    OR c.email LIKE '%{$tv['keyword']}%'
                    OR c.comments LIKE '%{$tv['keyword']}%'
                )";
            }
        }

        $searchVar->sortOrder = "c.creation_date DESC";
    }

    /**
     *
     */
    function getModuleDataArray(){
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $media = Zend_Registry::get('media');
        $cpUrl = Zend_Registry::get('cpUrl');
        $dbUtil = Zend_Registry::get('dbUtil');
        $modelHelper = Zend_Registry::get('modelHelper');
        $modelHelper->setModuleDataArray();
        $dataArray = $this->dataArray;

        $arr = array();
        foreach($dataArray AS $row){
            $exp = array('folder' => 'thumb');
            $userPicArray = $media->getFirstMediaRecord('directory_contact', 'picture', $row['contact_id']);

            if (count($userPicArray) == 0){
                $userPicArray = array(
                    'file_thumb'  => '//placehold.it/65x65'
                );
            }

            $urlUsr = $cpUrl->getUrlByCatType('Public Profile Dashboard') . "?cpi={$row['contact_id']}";
            $row['file_thumb_user'] = $userPicArray['file_thumb'];
            $row['urlUsr'] = $urlUsr;
            $row['rating'] = $fn->getRatingValue($row['rating']);

            if ($tv['catType'] == 'My Reviews' || $tv['catType'] == 'Public Profile Reviews'){
                $busPicArray = $media->getFirstMediaRecord('directory_business', 'picture', $row['record_id']);
                if (count($busPicArray) == 0){
                    $busPicArray = array(
                        'file_thumb'  => '//placehold.it/65x65'
                    );
                }
                $row['file_thumb_bus'] = $busPicArray['file_thumb'];
                $row['urlBus'] = $cpUrl->getUrlByRecord($row, 'record_id', array('secType' => 'Business', 'record_title' => $row['business_name']));

            } else {
                $row['user_no_of_followers'] = $row['user_no_of_followers'] > 0 ? $row['user_no_of_followers'] : 0;
                $row['user_comment_count'] = $row['user_comment_count'] > 0 ? $row['user_comment_count'] : 0;
            }

            $SQL = "
            SELECT *
            FROM media
            WHERE room_name = 'common_comment'
              AND record_id = {$row['comment_id']}
              ORDER BY creation_date DESC
            ";

            $picArray = $dbUtil->getSQLResultAsArray($SQL);

            $picArrayNew = array();
            foreach($picArray AS $picRow){
                $file_thumb = '/media/thumb/' . $picRow['file_name'];
                $file_large = '/media/large/' . $picRow['file_name'];
                $picArrayNew[] = array(
                    'file_thumb' => $file_thumb,
                    'file_large' => $file_large,
                );
            }
            $row['picArray'] = $picArrayNew;
            $row['childReviews'] = array();
            $row['childReviews'] = $this->getChildArray($row['room_name'], $row['record_id'], $row['comment_id']);

            $arr[] = $row;
        }

        return $arr;
    }

    /*
     *
     */
    function getChildArray($modName, $record_id, $parent_id) {
        $db = Zend_Registry::get('db');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $cpUrl = Zend_Registry::get('cpUrl');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dateUtil = Zend_Registry::get('dateUtil');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $media = Zend_Registry::get('media');

        $SQL = "
        SELECT c.*
              ,CONCAT_WS(' ', cont.first_name, cont.last_name) AS contact_name
        FROM `comment` c
        LEFT JOIN (contact cont) ON (c.contact_id = cont.contact_id)
        WHERE c.room_name = '{$modName}'
        AND c.record_id = '{$record_id}'
        AND c.parent_id = {$parent_id}
        ORDER BY c.creation_date ASC
        ";

        $dataArray = $dbUtil->getSQLResultAsArray($SQL);

        $rows = '';

        $arr = array();
        foreach($dataArray AS $row){
            $exp = array('folder' => 'thumb');
            $userPicArray = $media->getFirstMediaRecord('directory_contact', 'picture', $row['contact_id']);

            if (count($userPicArray) == 0){
                $userPicArray = array(
                    'file_thumb'  => '//placehold.it/65x65'
                );
            }

            if ($row['business_owner'] == 1){
                $row['file_thumb_user'] = '/www/images/business-owner.png';
                $row['urlUsr'] = 'javascript:void();';
                $row['contact_name'] = 'Business Owner';
                $row['divClass'] = 'businessOwner';
                
            } else {
                $row['file_thumb_user'] = $userPicArray['file_thumb'];
                $row['urlUsr'] = $cpUrl->getUrlByCatType('Public Profile Dashboard') . "?cpi={$row['contact_id']}";
                $row['divClass'] = '';
            }

            $arr[] = $row;
        }

        return $arr;
    }

    //==================================================================//
    function getTwigParams() {
        $pager = Zend_Registry::get('pager');

        $params = array(
            'pagerData' => $pager->getNavButtonsData(),
        );

        return $params;
    }
}
