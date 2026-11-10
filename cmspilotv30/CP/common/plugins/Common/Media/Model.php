<?
class CP_Common_Plugins_Common_Media_Model extends CP_Common_Lib_PluginModelAbstract
{
    /**
     *
     */
    function getMediaSQL($module, $recordType, $id, $lang = 'eng', $exp = array()){

        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        $limit = $fn->getIssetParam($exp, 'limit');
        $sortRecords = $fn->getIssetParam($exp, 'sortRecords', true);
        $colorId = $fn->getIssetParam($exp, 'colorId');
        $childId = $fn->getIssetParam($exp, 'childId');

        $limitTxt   = ($limit != "") ? "limit 0, {$limit}" : '';
        $recTypeSQL = ($recordType != '') ? " AND record_type = '{$recordType}'" : '';
        $langSQL    = ($lang != '') ? " AND lang = '{$lang}'" : '';
        $colorSQL   = ($colorId != '') ? " AND color_id = '{$colorId}'" : '';
        $childId    = ($childId != '') ? " AND child_id = '{$childId}'" : '';
        $sortText   = ($sortRecords == true) ? 'ORDER BY sort_order' : '';

        $SQL = "
        SELECT *
        FROM media
        WHERE room_name = '{$module}'
          {$recTypeSQL}
          {$langSQL}
          {$colorSQL}
          {$childId}
          AND record_id = '{$id}'
          {$sortText}
          ,creation_date
        {$limitTxt}
        ";
        return $SQL;
    }

    function setMediaArray($module, $recordType = ''){
        $mediaArrayObj = Zend_Registry::get('mediaArrayObj');
        $mediaArray = Zend_Registry::get('mediaArray');

        //--------------------------------------------------------------------//
        if($recordType != ''){
            if (!isset($mediaArray[$module][$recordType])){
                $mediaArrayObj->setMediaArray($module);
                /** re-get the mediaArray since it would have more values now **/
                $mediaArray = Zend_Registry::get('mediaArray');
            }

            /** if still the media type does not exist, throw an error **/
            if (!isset($mediaArray[$module][$recordType])){
                print trace();
                exit("setMediaArray is not set for {$module} -> {$recordType}");
            }
        } else {
            if (!isset($mediaArray[$module])){
                $mediaArrayObj->setMediaArray($module);
                /** re-get the mediaArray since it would have more values now **/
                $mediaArray = Zend_Registry::get('mediaArray');
            }
        }
    }

    /**
     *
     */
    function getAddMedia($module, $recordType, $id, $fileFldName, $exp) {
        $modulesArr = Zend_Registry::get('modulesArr');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $mediaArray = Zend_Registry::get('mediaArray');
        $cpUtil = Zend_Registry::get('cpUtil');
        $mediaArrayObj = Zend_Registry::get('mediaArrayObj');

        if($module == "") {
            $module = $fn->getReqParam('module');
        }

        if($recordType == "") {
            $recordType = $fn->getReqParam('recordType');
        }

        if($id == "") {
            $id = $fn->getReqParam('id');
        }

        if($fileFldName == "") {
            $fileFldName = $fn->getReqParam('fileFldName');
        }

        if (CP_SCOPE == 'admin') {
            checkLoggedIn();
        }
        
        if (!isset($_FILES[$fileFldName])){
            return;
        }

        $actualFileName = $fn->getIssetParam($exp, 'actualFileName');

        // use this when you need you need to pass a single file object from the $_FILES
        $singleFileObj = $fn->getIssetParam($exp, 'singleFileObj');

        require_once("imageResize.php");
        $imageResize = new ImageResize();

        $text = "";
        set_time_limit(50000);
        ini_get('max_execution_time');
        ini_set('memory_limit', '512M');

        $lang = $fn->getReqParam('lang', 'eng');

        if (!isset($mediaArray[$module][$recordType])){
            $mediaArrayObj->setMediaArray($module);
        }
        $mediaArray = Zend_Registry::get('mediaArray');

        $keyFieldName = $modulesArr[$module]['keyField'];
        $mediaArr  = $mediaArray[$module][$recordType];
        $mediaType = $mediaArr['mediaType'];

        $imgQuality = 100;
        $hasWatermark  = $mediaArr['hasWatermark'];
        $watermarkLargeFontSize = '';
        $watermarkNormalFontSize = '';
        $watermarkMediumFontSize = '';
        $watermarkText = '';
        if ($hasWatermark) {
            $watermarkText = $mediaArr['watermarkText'];
            $watermarkLargeFontSize = $mediaArr['watermarkLargeFontSize'];

            $watermarkNormalFontSize = intval($watermarkLargeFontSize * ($mediaArr['maxWidthN'] / $mediaArr['maxWidthL']));
            $watermarkMediumFontSize = intval($watermarkLargeFontSize * ($mediaArr['maxWidthM'] / $mediaArr['maxWidthL']));
        }

        $expImgL = array(
            'hasWatermark' => $hasWatermark,
            'watermarkText' => $watermarkText,
            'watermarkFontSize' => $watermarkLargeFontSize,
        );
        $expImgN = $expImgL;
        $expImgM = $expImgL;

        $expImgN['watermarkFontSize'] = $watermarkNormalFontSize;
        $expImgM['watermarkFontSize'] = $watermarkMediumFontSize;

        $sortOrder = 1;

        $SQL = "
        SELECT MAX(sort_order)
        FROM media
        WHERE record_id    = '{$id}'
          AND record_type  = '{$recordType}'
          AND room_name    = '{$module}'
        ";

        $result = $db->sql_query($SQL);
        $row    = $db->sql_fetchrow($result);
        $sortOrder = ($row[0] != '') ? $row[0] + 1 : 1;

        // if single file the format would be
        // Array
        // (
        //     [name] => BB_flowers.jpg
        //     [type] => application/octet-stream
        //     ...
        // )

        // if multiple files the format would be
        // Array(
        //     [fileName] => Array (
        //          [0] => Array(
        //                    [name] => BB_flowers.jpg
        //                    [type] => application/octet-stream
        //                 )
        //          [1] => Array(
        //                    [name] => xxx.jpg
        //                    [type] => application/octet-stream
        //                 )
        //   ...
        // )

        if (is_array($singleFileObj)){
            $files = array(0 => $singleFileObj);
        } else {
            $files = $fn->getFilesArrOrganized($_FILES);
            //if single file upload then make the array look like a multi file upload
            //so that we can easily loop
            $files = !isset($files[$fileFldName][0]) ? array($files[$fileFldName]) : $files[$fileFldName];
        }

        foreach ($files as $file) {
            $sourceFile        = $file['tmp_name'];
            $mediaSize         = $file['size'];
            $actual_file_name  = $file['name'];
            $contentType       = $file['type'];

            if ($actualFileName != '') {
                $actual_file_name = $actualFileName;
            }

            if ($actual_file_name == '' || strpos($actual_file_name, '/') !== false ||
                $actual_file_name == '..'){
                continue;
            }

            $mediaSizeMB = $mediaSize/1024/1024;
            if ($mediaSizeMB > $cpCfg['cp.maxUploadLimit']) {
                print 'Error: File size is too big';
                return;
            }

            if ($mediaArr['count'] == 'single') {
                $this->deleteMediaRecord($module, $id, $recordType);
            }

            $fa = array();
            $fa['media_type']       = $mediaType;
            $fa['actual_file_name'] = $actual_file_name;
            $fa['content_type']     = $contentType;
            $fa['media_size']       = $mediaSize;
            $fa['room_name']        = $module;
            $fa['record_type']      = $recordType;
            $fa['lang']             = $lang;
            $fa['record_id']        = $id;
            $fa['creation_date']    = date("Y-m-d H:i:s");
            $fa['alt_tag_data']     = substr($actual_file_name, 0, strrpos($actual_file_name, "."));
            $fa['sort_order']       = $sortOrder;
            $sortOrder++;

            $SQL         = $dbUtil->getInsertSQLStringFromArray($fa, "media");
            $result      = $db->sql_query($SQL);
            $media_id    = $db->sql_nextid();

            if ($media_id == "") {
                return;
            }

            $file_name = $media_id . "_" . $cpUtil->fixFileName($actual_file_name);

            $fa = array();
            $fa['file_name'] = $file_name;

            $whereCondition = "WHERE media_id = {$media_id}";
            $SQL            = $dbUtil->getUpdateSQLStringFromArray($fa, "media", $whereCondition);
            $result         = $db->sql_query($SQL);

            if ($mediaArr["resize"]) {
                $tempFile    = $mediaArray["tempFolder"] . $file_name;
                $result      =  move_uploaded_file($sourceFile, $tempFile);

                if (array_key_exists("thumbFolder", $mediaArr)) {
                    $dest = $mediaArr["thumbFolder"] . $file_name;
                    $imageResize->imageCreateThumb($tempFile, $dest, $mediaArr["maxWidthT"], $mediaArr["maxHeightT"],
                                                   $imgQuality);
                }

                if (array_key_exists("mediumFolder", $mediaArr)) {
                    $dest = $mediaArr["mediumFolder"] . $file_name;
                    $imageResize->imageCreateThumb($tempFile, $dest, $mediaArr["maxWidthM"], $mediaArr["maxHeightM"],
                                                   $imgQuality, $expImgM);
                }

                if (array_key_exists("normalFolder", $mediaArr)) {
                    $dest = $mediaArr["normalFolder"] . $file_name;
                    $imageResize->imageCreateThumb($tempFile, $dest, $mediaArr["maxWidthN"], $mediaArr["maxHeightN"],
                                                   $imgQuality, $expImgN);
                }

                if (array_key_exists("largeFolder", $mediaArr)) {
                    $dest = $mediaArr["largeFolder"] . $file_name;


                    if ($mediaArr['doNotResizeLargeImage']) {
                        if (!copy($tempFile, $dest)) {
                            // not copied
                        }
                    } else {
                        $imageResize->imageCreateThumb($tempFile, $dest, $mediaArr["maxWidthL"], $mediaArr["maxHeightL"],
                                                       $imgQuality, $expImgL);
                    }
                }

                unlink($tempFile);

            } else {
                $destinationFile = $mediaArr["normalFolder"] . $file_name;
                $result = move_uploaded_file($sourceFile, $destinationFile);
            }
        }

        return 'success';
    }

    /**
     *
     */
    function createMedia($module, $recordType, $id, $exp = array()) {
        $this->setMediaArray($module, $recordType);

        $modulesArr = Zend_Registry::get('modulesArr');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $tv = Zend_Registry::get('tv');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $mediaArray = Zend_Registry::get('mediaArray');
        $cpUtil = Zend_Registry::get('cpUtil');
        $mediaArrayObj = Zend_Registry::get('mediaArrayObj');

        require_once("imageResize.php");
        $imageResize = new ImageResize();

        $keyFieldName = $modulesArr[$module]["keyField"];
        $mediaType    = $mediaArray[$module][$recordType]["mediaType"];
        $sortOrder = 1;

        $SQL = "
        SELECT MAX(sort_order)
        FROM media
        WHERE record_id    = '{$id}'
          AND record_type  = '{$recordType}'
          AND room_name    = '{$module}'
        ";

        $result = $db->sql_query($SQL);
        $row    = $db->sql_fetchrow($result);
        $sortOrder = ($row[0] != "") ? $row[0] + 1 : 1;

        $contentType      = $fn->getIssetParam($exp, 'contentType');
        $sourceFile       = $fn->getIssetParam($exp, 'sourceFile');
        $mediaSize        = $fn->getIssetParam($exp, 'mediaSize');
        $srcFile          = $fn->getIssetParam($exp, 'srcFile');
        $actual_file_name = $fn->getIssetParam($exp, 'actualFileName');
        $deletePreviousMedia = $fn->getIssetParam($exp, 'deletePreviousMedia', false);
        $resizeImageCallback = $fn->getIssetParam($exp, 'resizeImageCallback', false); //array
        $lang                = $fn->getIssetParam($exp, 'lang', $tv['lang']); //array
        $mediaUrl            = $fn->getIssetParam($exp, 'mediaUrl', ''); //url provided instead of a file upload
        //if image source folder is provided then no need to resize
        $imageSourceFolder = $fn->getIssetParam($exp, 'imageSourceFolder', '');
        $thumbImageFolder  = $fn->getIssetParam($exp, 'thumbImageFolder', 'thumb');
        $normalImageFolder = $fn->getIssetParam($exp, 'normalImageFolder', 'normal');
        $largeImageFolder  = $fn->getIssetParam($exp, 'largeImageFolder', 'large');

        //ex: http://nearer.localdataimages.com/large/1000/10000000.jpg
        if ($mediaUrl) {
            $actual_file_name = substr($mediaUrl, strrpos($mediaUrl, '/') + 1);
        }

        if ($srcFile != '' && !file_exists($srcFile)){
            return;
        }

        $mediaArr = $mediaArray[$module][$recordType];

        if ($mediaType != 'attachment'){
            $imgQuality = 100;
            $hasWatermark  = $mediaArr['hasWatermark'];
            $watermarkText = $mediaArr['watermarkText'];
            $watermarkLargeFontSize = $mediaArr['watermarkLargeFontSize'];
            $watermarkNormalFontSize = intval($watermarkLargeFontSize * ($mediaArr['maxWidthN'] / $mediaArr['maxWidthL']));
            $watermarkMediumFontSize = intval($watermarkLargeFontSize * ($mediaArr['maxWidthM'] / $mediaArr['maxWidthL']));

            $expImgL = array(
                'hasWatermark' => $hasWatermark,
                'watermarkText' => $watermarkText,
                'watermarkFontSize' => $watermarkLargeFontSize,
            );
            $expImgN = $expImgL;
            $expImgM = $expImgL;

            $expImgN['watermarkFontSize'] = $watermarkNormalFontSize;
            $expImgM['watermarkFontSize'] = $watermarkMediumFontSize;
        }

        if ($mediaArray[$module][$recordType]['count'] == 'single' || $deletePreviousMedia) {
            $this->deleteMediaRecord($module, $id, $recordType, $exp);
        }

        $fa = array();
        $fa['media_type']       = $mediaType;
        $fa['actual_file_name'] = $actual_file_name;
        $fa['content_type']     = $contentType;
        $fa['media_size']       = $mediaSize;
        $fa['room_name']        = $module;
        $fa['record_type']      = $recordType;
        $fa['lang']             = $lang;
        $fa['record_id']        = $id;
        $fa['creation_date']    = date('Y-m-d H:i:s');
        $fa['alt_tag_data']     = substr($actual_file_name, 0, strrpos($actual_file_name, '.'));
        $fa['sort_order']       = $sortOrder;
        if ($mediaUrl) {
            $fa['media_url'] = $mediaUrl;
        }
        $sortOrder++;

        $SQL      = $dbUtil->getInsertSQLStringFromArray($fa, 'media');
        $result   = $db->sql_query($SQL);
        $media_id = $db->sql_nextid();

        if ($media_id == "") {
            return;
        }

        $file_name   = $media_id . "_" . $cpUtil->fixFileName($actual_file_name);

        $fa = array();
        $fa['file_name']   = $file_name;

        $whereCondition = "WHERE media_id = {$media_id}";
        $SQL            = $dbUtil->getUpdateSQLStringFromArray($fa, "media", $whereCondition);
        $result         = $db->sql_query($SQL);

        if (!$mediaUrl) { //if no media url provided then do the normal stuff. resize/create thumb etc.

            if ($mediaType != 'attachment'){
                if ($resizeImageCallback) {
                    call_user_func($resizeImageCallback, $media_id);
                } else {
                    //image source folder specified then just copy the file from there
                    if ($imageSourceFolder != '') {
                        if (array_key_exists('thumbFolder', $mediaArr)) {
                            $srcFile = $imageSourceFolder . $thumbImageFolder . '/' . $actual_file_name;
                            $dest = $mediaArr['thumbFolder'] . $file_name;
                            copy($srcFile, $dest);
                        }
                        if (array_key_exists('normalFolder', $mediaArr)) {
                            $srcFile = $imageSourceFolder . $normalImageFolder . '/' . $actual_file_name;
                            $dest = $mediaArr['normalFolder'] . $file_name;
                            copy($srcFile, $dest);
                        }
                        if (array_key_exists('largeFolder', $mediaArr)) {
                            $srcFile = $imageSourceFolder . $largeImageFolder . '/' . $actual_file_name;
                            $dest = $mediaArr['largeFolder'] . $file_name;
                            copy($srcFile, $dest);
                        }

                    } else if ($mediaArr["resize"]) {
                        if ($srcFile == ''){
                            $srcFile = $mediaArray["tempFolder"] . $file_name;
                        }

                        if (array_key_exists("thumbFolder", $mediaArr)) {
                            $dest = $mediaArr["thumbFolder"] . $file_name;
                            $imageResize->imageCreateThumb($srcFile, $dest,
                                                           $mediaArr["maxWidthT"],
                                                           $mediaArr["maxHeightT"], $imgQuality);
                        }

                        if (array_key_exists("mediumFolder", $mediaArr)) {
                            $dest = $mediaArr["mediumFolder"] . $file_name;
                            $imageResize->imageCreateThumb($srcFile, $dest,
                                                           $mediaArr["maxWidthM"],
                                                           $mediaArr["maxHeightM"], $imgQuality, $expImgM);
                        }

                        if (array_key_exists("normalFolder", $mediaArr)) {
                            $dest = $mediaArr["normalFolder"] . $file_name;
                            $imageResize->imageCreateThumb($srcFile, $dest,
                                                           $mediaArr["maxWidthN"],
                                                           $mediaArr["maxHeightN"], $imgQuality, $expImgN);
                        }

                        if (array_key_exists("largeFolder", $mediaArr)) {
                            $dest = $mediaArr["largeFolder"] . $file_name;
                            $imageResize->imageCreateThumb($srcFile, $dest,
                                                           $mediaArr["maxWidthL"],
                                                           $mediaArr["maxHeightL"], $imgQuality, $expImgL);
                        }
                        //unlink($srcFile);

                    } else {
                        $dest = $mediaArr["normalFolder"] . $file_name;
                        $result = move_uploaded_file($srcFile, $dest);
                    }
                }
            } else if($mediaType == 'attachment'){
                $dest = $mediaArr["normalFolder"] . $file_name;
                copy($srcFile, $dest);
                //unlink($srcFile);
            }
        } //$mediaUrl

        return 'success';
    }

    /**
     *
     */
    function deleteMediaRecord($module, $keyFieldID, $recordType = '', $exp=array()) {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $lang = $fn->getIssetParam($exp, 'lang', $tv['lang']); //array

        if (CP_SCOPE == 'admin' && !in_array($tv['spAction'], $tv['protSiteSpActionExceptions']) ) {
            checkLoggedIn();
        }

        $this->setMediaArray($module, $recordType);

        $db = Zend_Registry::get('db');
        $mediaArray = Zend_Registry::get('mediaArray');

        if ($recordType == ''){
            $SQL = "
            SELECT file_name
                  ,record_type
                  ,room_name
            FROM media
            WHERE record_id = {$keyFieldID}
              AND room_name = '{$module}'
            ";
        } else {
            $SQL = "
            SELECT file_name
                  ,record_type
                  ,room_name
            FROM media
            WHERE record_id = {$keyFieldID}
              AND room_name = '{$module}'
              AND record_type = '{$recordType}'
              AND lang = '{$lang}'
            ";
        }
        $result = $db->sql_query($SQL);
        while ($row = $db->sql_fetchrow($result)) {
            $recordTypeTmp = $row['record_type'];
            $module          = $row['room_name'];

            if ($row['file_name'] != '') {
                if (array_key_exists("thumbFolder", $mediaArray[$module][$recordTypeTmp])) {
                    $fileName  = $mediaArray[$module][$recordTypeTmp]["thumbFolder"] . $row['file_name'];
                    if (file_exists($fileName)) {
                        unlink($fileName);
                    }
                }

                if (array_key_exists("mediumFolder", $mediaArray[$module][$recordTypeTmp])) {
                    $fileName  = $mediaArray[$module][$recordTypeTmp]["mediumFolder"] . $row['file_name'];
                    if (file_exists($fileName) ) {
                        unlink($fileName);
                    }
                }

                if (array_key_exists("normalFolder", $mediaArray[$module][$recordTypeTmp])) {
                    $fileName  = $mediaArray[$module][$recordTypeTmp]["normalFolder"] . $row['file_name'];
                    if (file_exists($fileName) ) {
                        unlink($fileName);
                    }
                }

                if (array_key_exists("largeFolder", $mediaArray[$module][$recordTypeTmp])) {
                    $fileName  = $mediaArray[$module][$recordTypeTmp]["largeFolder"] . $row['file_name'];
                    if (file_exists($fileName) ) {
                        unlink($fileName);
                    }
                }

                if (array_key_exists("croppedFolder", $mediaArray[$module][$recordTypeTmp])) {
                    $fileName  = $mediaArray[$module][$recordTypeTmp]["croppedFolder"] . $row['file_name'];
                    if (file_exists($fileName) ) {
                        unlink($fileName);
                    }
                }
            }
        }

        if ($recordType == ''){
            $SQL = "
            DELETE FROM media
            WHERE record_id = {$keyFieldID}
              AND room_name = '{$module}'
            ";
        } else {
            $SQL = "
            DELETE FROM media
            WHERE record_id = {$keyFieldID}
              AND room_name = '{$module}'
              AND record_type = '{$recordType}'
              AND lang = '{$lang}'
            ";
        }

        $result = $db->sql_query($SQL);
    }

    /**
     *
     */
    function deleteMediaRecordInBulk($module, $keyFieldArray, $recordType = '') {
        if (CP_SCOPE == 'admin') {
            checkLoggedIn();
        }

        if(!is_array($keyFieldArray)){
            return;
        }

        foreach($keyFieldArray AS $keyFieldID){
            $this->deleteMediaRecord($module, $keyFieldID, $recordType);
        }
    }


    /**
     *
     */
    function getSaveMedia() {
        $tv = Zend_Registry::get('tv');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $mediaArray = Zend_Registry::get('mediaArray');
        $cpUtil = Zend_Registry::get('cpUtil');
        $fn = Zend_Registry::get('fn');
        $mediaArrayObj = Zend_Registry::get('mediaArrayObj');
        $mediaArray    = $mediaArrayObj->mediaArray;
        $modulesArr = Zend_Registry::get('modulesArr');

        $module      = $fn->getReqParam('room');
        $recordType  = $fn->getReqParam('recordType');
        $media_id    = $fn->getReqParam('media_id');

        $SQL = "
        SELECT file_name
              ,actual_file_name
              ,room_name
              ,record_type
              ,record_id
              ,record_id
        FROM media
        WHERE media_id = {$media_id}
        ";
        $result = $db->sql_query($SQL);

        while ($row = $db->sql_fetchrow($result)) {
            $module     = $row['room_name'];
            $recordType = $row['record_type'];
            $record_id  = $row['record_id'];

            $modParent = $modulesArr[$module];

            $rowParent = $fn->getRecordRowByID($modParent['tableName'], $modParent['keyField'], $record_id);

            if ($rowParent['name'] == 'webBasic_content' && !$rowParent['published']) {
                return;
            }

            if (!isset($mediaArray[$module][$recordType])){
                $mediaArrayObj->setMediaArray($module);
                $mediaArray    = $mediaArrayObj->mediaArray;
            }
            $arr = $mediaArray[$module][$recordType];

            $resize = isset($mediaArray[$module][$recordType]['resize']) ?
                      $mediaArray[$module][$recordType]['resize'] :
                      false;
            //if (array_key_exists('largeFolder', $mediaArray[$module][$recordType])) {

            if ($resize) {
                $fileName = $arr['largeFolder'] . $row['file_name'];
            } else {
                $fileName = $arr['normalFolder'] . $row['file_name'];
            }
            $actualFileName = $row['actual_file_name'];
        }
        $cpUtil->sendFile($fileName, $actualFileName);
    }

    function getDeleteAllMedia($module, $recordType, $id){
        if (CP_SCOPE == 'admin') {
            checkLoggedIn();
        }

        $dbz = Zend_Registry::get('dbz');

        $SQL = "
        SELECT media_id
        FROM media
        WHERE record_id = ?
          AND room_name = ?
          AND record_type = ?
        ";

        $stmt = $dbz->query($SQL, array($id, $module, $recordType));

        while ($row = $stmt->fetch()) {
            $this->getDeleteMedia($row['media_id']);
        }
    }

    function getDeleteMedia($media_id = '') {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $mediaArray = Zend_Registry::get('mediaArray');

        if ($media_id == '') {
            $media_id = $fn->getReqParam('media_id');
        }

        $validateArr = $fn->validateAccess('common_media', 'delete', $media_id);
        if (!$validateArr['valid']){
            return $validateArr['data'];
        }

        $SQL = "
        SELECT *
        FROM media
        WHERE media_id = '{$media_id}'
        ";
        $result = $db->sql_query($SQL);

        while ($row = $db->sql_fetchrow($result)) {
            $module     = $row['room_name'];
            $recordType = $row['record_type'];

            $mediaArrayObj = includeCPClass('Lib', 'MediaArray');
            $mediaArrayObj->setMediaArray($row['room_name']);
            $mediaArray = $mediaArrayObj->mediaArray;
            if (array_key_exists("thumbFolder", $mediaArray[$module][$recordType])) {
                $fileName  = $mediaArray[$module][$recordType]["thumbFolder"] . $row['file_name'];
                if (file_exists($fileName) ) {
                    unlink($fileName);
                }
            }

            if (array_key_exists("mediumFolder", $mediaArray[$module][$recordType])) {
                $fileName  = $mediaArray[$module][$recordType]["mediumFolder"] . $row['file_name'];
                if (file_exists($fileName) ) {
                    unlink($fileName);
                }
            }

            if (array_key_exists("normalFolder", $mediaArray[$module][$recordType])) {
                $fileName  = $mediaArray[$module][$recordType]["normalFolder"] . $row['file_name'];
                if (file_exists($fileName) ) {
                    unlink($fileName);
                }
            }

            if (array_key_exists("largeFolder", $mediaArray[$module][$recordType])) {
                $fileName  = $mediaArray[$module][$recordType]["largeFolder"] . $row['file_name'];
                if (file_exists($fileName) ) {
                    unlink($fileName);
                }
            }

            if (array_key_exists("croppedFolder", $mediaArray[$module][$recordType])) {
                $fileName  = $mediaArray[$module][$recordType]["croppedFolder"] . $row['file_name'];
                if (file_exists($fileName) ) {
                    unlink($fileName);
                }
            }

            if ($recordType == "flash") {
                $fileName  = $mediaArray[$module][$recordType]["normalFolder"] . $row['flash_movie_file'];
                if (file_exists($fileName) ) {
                    unlink($fileName);
                }
            }

        }

        $SQL = "
        DELETE FROM media
        WHERE media_id = '{$media_id}'
        ";
        $result = $db->sql_query($SQL);

        return $validateArr['data'];
    }

    //==================================================================//
    function getSaveMediaProperties() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $am = Zend_Registry::get('am');
        $cpCfg = Zend_Registry::get('cpCfg');
        $validate = Zend_Registry::get('validate');

        if (!$this->getEditMediaPropertiesValidate()){
            return $validate->getErrorMessageXML();
        }

        $media_id = $fn->getReqParam('media_id');
        $lnArray  = $cpCfg['cp.availableLanguages'];

        $fa = array();

        foreach ($lnArray as $key => $value) {
            $fieldName = $ln->getFieldPrefix($key). "caption";
            $fa = $fn->addToFieldsArray($fa, $fieldName);

            $internalLinkFldName  = $ln->getFieldPrefix($key). "internal_link";
            $fa = $fn->addToFieldsArray($fa, $internalLinkFldName);

            $descriptionFldName  = $ln->getFieldPrefix($key). "description";
            $fa = $fn->addToFieldsArray($fa, $descriptionFldName);
        }

        $fa = $fn->addToFieldsArray($fa, 'sort_order');
        $fa = $fn->addToFieldsArray($fa, 'project');
        $fa = $fn->addToFieldsArray($fa, 'note');
        $fa = $fn->addToFieldsArray($fa, 'alt_tag_data');
        $fa = $fn->addToFieldsArray($fa, 'bg_color');
        $fa = $fn->addToFieldsArray($fa, 'color_id');
        $fa = $fn->addToFieldsArray($fa, 'child_id');
        $fa = $fn->addToFieldsArray($fa, 'css_style');
        $fa = $fn->addToFieldsArray($fa, 'video_link');

        $whereCondition = "WHERE media_id = {$media_id}";
        $SQL    = $dbUtil->getUpdateSQLStringFromArray($fa, 'media', $whereCondition);
        $result = $db->sql_query($SQL);

        $fa['media_id'] = $media_id;
        return $validate->getSuccessMessageXML('', $fa);
    }

    //========================================================//
    function getEditMediaPropertiesValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    //==================================================================//
    function getSaveImageCaption() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $text = "";

        $media_id = $fn->getPostParam('media_id');
        $caption  = isset($_POST['caption'] ) ? $dbUtil->replaceForDB($_POST['caption'])  : "";

        //-----------------------------------------------------------------------//
        $updateSQL = "
        UPDATE media
        SET " . "caption = '{$caption}'" ."
        WHERE media_id = '{$media_id}'
        ";

        $result = $db->sql_query($updateSQL);

        //==================================================================//
        $text .= "
        <script>
            window.opener.Actions.apply();
            window.close();
        </script>
        ";
        return $text;
    }

    /**
     *
     * @param <type> $module
     * @param <type> $keyFieldName
     * @param <type> $id
     * @param <type> $recordType
     * @return <type>
     */
    function getMediaFilesArray($module, $recordType, $id, $exp = array()){
        $this->setMediaArray($module, $recordType);

        if ($id == ""){
            return;
        }

        $db = Zend_Registry::get('db');
        $mediaArray = Zend_Registry::get('mediaArray');
        $tv = Zend_Registry::get('tv');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $returnDimensions = $fn->getIssetParam($exp, 'returnDimensions');
        $langForced = $fn->getIssetParam($exp, 'langForced');
        $colorId = $fn->getIssetParam($exp, 'colorId');
        $childId = $fn->getIssetParam($exp, 'childId');
        $exp = array('colorId' => $colorId, 'childId' => $childId);

        $language = ($langForced != "") ? $langForced: $tv['lang'];

        $SQL     = $this->getMediaSQL($module, $recordType, $id, $language, $exp);
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        if ($numRows == 0 && $tv['lang'] != "eng" && $langForced == ""){
            $SQL    = $this->getMediaSQL($module, $recordType, $id, 'eng', $exp);
            $result = $db->sql_query($SQL);
            $numRows  = $db->sql_numrows($result);
        }

        if ($childId != '' && $numRows == 0){
            $exp['childId'] = '';
            $SQL = $this->getMediaSQL($module, $recordType, $id, $language, $exp);
            $result = $db->sql_query($SQL);
            $numRows  = $db->sql_numrows($result);
        }

        /** redundant bsas 05/07/2012
        if ($numRows == 0 && $tv['lang'] != "eng"){
            $SQL = $this->getMediaSQL($module, $recordType, $id, 'eng', $exp);
            $result = $db->sql_query($SQL);
        }
        ***/

        $filesArray   = array();

        $rowCounter  = 0;

        $arrTemp = $mediaArray[$module][$recordType];

        while ($row  = $db->sql_fetchrow($result)){
            $full_path_t = isset($arrTemp["thumbFolder"])   ? '/'. $arrTemp["thumbFolder"]   . $row['file_name'] : "";
            $full_path_c = isset($arrTemp["croppedFolder"]) ? '/'. $arrTemp["croppedFolder"]   . $row['file_name'] : "";
            $full_path_n = isset($arrTemp["normalFolder"])  ? '/'. $arrTemp["normalFolder"]  . $row['file_name'] : "";
            $full_path_l = isset($arrTemp["largeFolder"])   ? '/'. $arrTemp["largeFolder"]   . $row['file_name'] : "";

            $fldPrefix = $ln->gfp();
            $filesArray[$rowCounter]['media_id']         = $row['media_id'];
            $filesArray[$rowCounter]['file_name']        = $row['file_name'];
            $filesArray[$rowCounter]['content_type']     = $row['content_type'];
            $filesArray[$rowCounter]['internal_link']    = isset($row[$fldPrefix.'internal_link']) ? $ln->gfv($row, "internal_link") : $row['internal_link'];
            $filesArray[$rowCounter]['alt_tag_data']     = isset($row['alt_tag_data'])  ? $row['alt_tag_data']  : '';
            $filesArray[$rowCounter]['external_link']    = $row['external_link'];
            $filesArray[$rowCounter]['actual_file_name'] = $row['actual_file_name'];
            $filesArray[$rowCounter]['file_thumb']       = $full_path_t;
            $filesArray[$rowCounter]['file_cropped']     = $full_path_c;
            $filesArray[$rowCounter]['file_normal']      = $full_path_n;
            $filesArray[$rowCounter]['file_large']       = $full_path_l;
            $filesArray[$rowCounter]['content_type']     = $row['content_type'];
            $filesArray[$rowCounter]['caption']          = $ln->gfv($row, "caption");
            $filesArray[$rowCounter]['description']      = $ln->gfv($row, "description");
            $filesArray[$rowCounter]['ext']              = strtolower(substr($row['file_name'], strripos($row['file_name'], '.') + 1 , 5));
            $filesArray[$rowCounter]['thumb_w']          = '';
            $filesArray[$rowCounter]['thumb_h']          = '';
            $filesArray[$rowCounter]['cropped_w']        = '';
            $filesArray[$rowCounter]['cropped_h']        = '';
            $filesArray[$rowCounter]['normal_w']         = '';
            $filesArray[$rowCounter]['normal_h']         = '';
            $filesArray[$rowCounter]['large_w']          = '';
            $filesArray[$rowCounter]['large_h']          = '';
            $filesArray[$rowCounter]['bg_color']         = isset($row['bg_color']) ? $row['bg_color'] : '';
            $filesArray[$rowCounter]['css_style']        = isset($row['css_style']) ? $row['css_style'] : '';
            $filesArray[$rowCounter]['video_link']       = isset($row['css_style']) ? $row['video_link'] : '';
            $filesArray[$rowCounter]['media_url']        = isset($row['media_url']) ? $row['media_url'] : '';

            $url = '';
            if (trim($row['internal_link']) != ''){
                $url = $row['internal_link'];
            } else if (trim($row['external_link']) != ''){
                $url = $row['external_link'];
            }

            $filesArray[$rowCounter]['url'] = $url;
            if ($returnDimensions && $row['media_type'] == 'image'){
                $filename = $arrTemp['thumbFolder'] . $row['file_name'];
                if (file_exists($filename) ){
                    list($width, $height) = getimagesize($filename);
                    $filesArray[$rowCounter]['thumb_w'] = $width;
                    $filesArray[$rowCounter]['thumb_h'] = $height;
                }

                $filename = $arrTemp['croppedFolder'] . $row['file_name'];
                if (file_exists($filename) ){
                    list($width, $height) = getimagesize($filename);
                    $filesArray[$rowCounter]['cropped_w'] = $width;
                    $filesArray[$rowCounter]['cropped_h'] = $height;
                }

                $filename = $arrTemp['normalFolder'] . $row['file_name'];
                if (file_exists($filename) ){
                    list($width, $height) = getimagesize($filename);
                    $filesArray[$rowCounter]['normal_w'] = $width;
                    $filesArray[$rowCounter]['normal_h'] = $height;
                }

                $filename = $arrTemp['largeFolder'] . $row['file_name'];
                if (file_exists($filename) ){
                    list($width, $height) = getimagesize($filename);
                    $filesArray[$rowCounter]['large_w'] = $width;
                    $filesArray[$rowCounter]['large_h'] = $height;
                }
            }

            $rowCounter++;
        }

        return $filesArray;
    }

    /**
     *
     */
    function getFirstMediaRecord($module, $recordType, $id, $langForced = ""){
        $record = array();
        $arr = $this->getMediaFilesArray($module, $recordType, $id, array('langForced' => $langForced));
        if (is_array($arr) && count($arr) > 0){
            $record = $arr[0];
        }

        return $record;
    }


    /**
     *
     */
    function getFirstFileName($module, $recordType, $id){
        $langForced = '';

        $db = Zend_Registry::get('db');
        $mediaArray = Zend_Registry::get('mediaArray');
        $tv = Zend_Registry::get('tv');
        $ln = Zend_Registry::get('ln');
        $mediaArrayObj = Zend_Registry::get('mediaArrayObj');
        //--------------------------------------------------------------------//

        if (!isset($mediaArray[$module][$recordType])){
            $mediaArrayObj->setMediaArray($module);
        }

        $language = ($langForced != '') ? $langForced: $tv['lang'];

        $SQL     = $this->getMediaSQL($module, $recordType, $id, $language);
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        if ($numRows == 0 && $tv['lang'] != 'eng' && $langForced == ''){
            $SQL    = $this->getMediaSQL($module, $recordType, $id, 'eng');
            $result = $db->sql_query($SQL);
        }

        while ($row = $db->sql_fetchrow($result) ){
            return $row['file_name'];
        }
    }

    function getDuplicateMedia($module, $id, $newRecordId, $newRoom = '') {
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');
        $mediaArray = Zend_Registry::get('mediaArray');

        $newRoom = ($newRoom != '') ? $newRoom : $module;

        $SQL     = $this->getMediaSQL($module, '', $id, '');
        $result  = $db->sql_query($SQL);

        $sortOrder = 1;
        while ($row  = $db->sql_fetchrow($result) ){
            $fa = array();

            $srcFileName = $row['file_name'];
            $recordType  = $row['record_type'];

            $fa['media_type']       = $row['media_type'];
            $fa['actual_file_name'] = $row['actual_file_name'];
            $fa['content_type']     = $row['content_type'];
            $fa['media_size']       = $row['media_size'];
            $fa['room_name']        = $newRoom;
            $fa['record_type']      = $row['record_type'];
            $fa['lang']             = $row['lang'];
            $fa['record_id']        = $newRecordId;
            $fa['creation_date']    = date('Y-m-d H:i:s');
            $fa['alt_tag_data']     = $row['alt_tag_data'];
            $fa['sort_order']       = $sortOrder;
            $sortOrder++;

            $SQL2 = $dbUtil->getInsertSQLStringFromArray($fa, 'media');
            $db->sql_query($SQL2);
            $media_id = $db->sql_nextid();

            $file_name = $media_id . '_' . $fa['actual_file_name'];

            $fa = array();
            $fa['file_name'] = $file_name;

            $whereCondition = "WHERE media_id = {$media_id}";
            $SQL3 = $dbUtil->getUpdateSQLStringFromArray($fa, 'media', $whereCondition);
            $db->sql_query($SQL3);

            $mediaArrayObj = Zend_Registry::get('mediaArrayObj');
            $fnMod = includeCPClass('ModuleFns', $module);
            $fnMod->setMediaArray($mediaArrayObj);

            $fnMod = includeCPClass('ModuleFns', $newRoom);
            $fnMod->setMediaArray($mediaArrayObj);

            $mediaArray = $mediaArrayObj->mediaArray;

            $arrTempSrc  = &$mediaArray[$module][$recordType];
            $arrTempDest = &$mediaArray[$newRoom][$recordType];

            if (array_key_exists('thumbFolder', $arrTempSrc)) {
                $src  = $arrTempSrc['thumbFolder'] . $srcFileName;
                $newfile = $arrTempDest['thumbFolder'] . $file_name;
                if (file_exists($src)) {
                    copy($src, $newfile);
                }
            }

            if (array_key_exists('mediumFolder', $arrTempSrc)) {
                $src  = $arrTempSrc['mediumFolder'] . $srcFileName;
                $newfile = $arrTempDest['mediumFolder'] . $file_name;
                if (file_exists($src)) {
                    copy($src, $newfile);
                }
            }

            if (array_key_exists('normalFolder', $arrTempSrc)) {
                $src     = $arrTempSrc['normalFolder'] . $srcFileName;
                $newfile = $arrTempDest['normalFolder'] . $file_name;
                if (file_exists($src)) {
                    copy($src, $newfile);
                }
            }

            if (array_key_exists('largeFolder', $arrTempSrc)) {
                $src  = $arrTempSrc['largeFolder'] . $srcFileName;
                $newfile = $arrTempDest['largeFolder'] . $file_name;
                if (file_exists($src)) {
                    copy($src, $newfile);
                }
            }
        }
    }

    function getSaveCroppedImage() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $validate = Zend_Registry::get('validate');
        $mediaArrayObj = Zend_Registry::get('mediaArrayObj');
        $mediaArray = Zend_Registry::get('mediaArray');

        $media_id = $fn->getReqParam('media_id');

        $row = $fn->getRecordRowByID('media', 'media_id', $media_id);
        $module     = $row['room_name'];
        $recordType = $row['record_type'];

        if (!isset($mediaArray[$module][$recordType])){
            $mediaArrayObj->setMediaArray($module);
        }
        $mediaArr = $mediaArrayObj->mediaArray[$module][$recordType];
        $cropInfo = $mediaArr['cropInfo'];

        $fileName = $mediaArr['largeFolder'] . $row['file_name'];
        $croppedFileName = $mediaArr['croppedFolder'] . $row['file_name'];

        $targ_w = $cropInfo['width'];
        $targ_h = $cropInfo['height'];
        $jpeg_quality = 90;

        ini_set("gd.jpeg_ignore_warning", 1);
        $src = $fileName;

        $ext = substr($row['file_name'], strrpos($row['file_name'], '.')+1);

        if ($ext == 'png'){
            $img_r = imagecreatefrompng($src);
        } else if ($ext == 'gif'){
            $img_r = imagecreatefromgif($src);
        } else {
            $img_r = imagecreatefromjpeg($src);
        }

        $dst_r = ImageCreateTrueColor( $targ_w, $targ_h );

        $cropX = (int)$fn->getReqParam('cropX');
        $cropY = (int)$fn->getReqParam('cropY');
        $cropW = (int)$fn->getReqParam('cropW');
        $cropH = (int)$fn->getReqParam('cropH');

        imagecopyresampled($dst_r,
                           $img_r,
                           0,
                           0,
                           $cropX,
                           $cropY,
                           $targ_w,
                           $targ_h,
                           $cropW,
                           $cropH);

        imagejpeg($dst_r, $croppedFileName, $jpeg_quality);

        return $validate->getSuccessMessageXML();
    }

    function hasMediaRecord($module, $recordType, $id) {
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');

        $SQL     = $this->getMediaSQL($module, $recordType, $id, $tv['lang']);
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        if ($numRows == 0 && $tv['lang'] != "eng"){
            $SQL = $this->getMediaSQL($module, $recordType, $id, 'eng');
            $result = $db->sql_query($SQL);
            $numRows = $db->sql_numrows($result);
        }

        if ($numRows > 0) {
            return true;
        } else {
            return false;
        }
    }

}
