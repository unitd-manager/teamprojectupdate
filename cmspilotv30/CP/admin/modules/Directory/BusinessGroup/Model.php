<?
class CP_Admin_Modules_Directory_BusinessGroup_Model extends CP_Common_Modules_Directory_BusinessGroup_Model
{
    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('title', 'Please enter the business group name');

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
        $fa['country_id'] = $fn->getSessionParam('cp_country_id');
        $id = $fn->addRecord($fa);
        $fn->returnAfterNewSave($id);
    }

    function getEditValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('title', 'Please enter the business group name');

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
        $id = $fn->saveRecord($fa, $tableName, $keyField, $keyFieldVal);

        getCPModuleObj('web2_tags')->model->updateCloudTagsByCSV('Business Group', $id, $_POST, 'tags');

        $fn->returnAfterNewSave($id, 'detail');
    }

    function getFields() {
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'title');
        $fa = $fn->addToFieldsArray($fa, 'published');
        $fa = $fn->addToFieldsArray($fa, 'country_id');
        $fa = $fn->addToFieldsArray($fa, 'category_id');
        $fa = $fn->addToFieldsArray($fa, 'sub_category_id');
        $fa = $fn->addToFieldsArray($fa, 'business_type');
        $fa = $fn->addToFieldsArray($fa, 'website');
        $fa = $fn->addToFieldsArray($fa, 'description');
        $fa = $fn->addToFieldsArray($fa, 'description_chi');
        $fa = $fn->addToFieldsArray($fa, 'contact_name');
        $fa = $fn->addToFieldsArray($fa, 'contact_position');
        $fa = $fn->addToFieldsArray($fa, 'contact_phone');
        $fa = $fn->addToFieldsArray($fa, 'contact_email');

        return $fa;
    }

    function getExportData($dataArray){
        $phpExcel = includeCPClass('Lib', 'PhpExcelExportWrapper', 'PhpExcelExportWrapper');

        $fa = array(
              'title' => $phpExcel->getFldObj('Business Group')
        );

        $config = array(
             'fldsArr'   => $fa
            ,'dataArray' => $dataArray
        );

        return $phpExcel->exportData($config);
    }


    /**
     *
     */
    function getImportData(){
        print "<img src='images/logo.jpg'><br>";
        $fn = Zend_Registry::get('fn');

        flush();
        ob_flush();

        $business_group_id = $fn->getReqParam('record_id');
        $rowBG = $fn->getRecordRowByID('business_group', 'business_group_id', $business_group_id);

        $phpExcel = includeCPClass('Lib', 'PhpExcelImportWrapper');
        $fa = array(
              'country'                => $phpExcel->getImportFldObj('Country')
             ,'state'                  => $phpExcel->getImportFldObj('State')
             ,'city'                   => $phpExcel->getImportFldObj('City')
             ,'borough'                => $phpExcel->getImportFldObj('Borough')
             ,'area'                   => $phpExcel->getImportFldObj('Area')
             ,'street'                 => $phpExcel->getImportFldObj('Street')
             ,'address_street_no_from' => $phpExcel->getImportFldObj('Street No (from)')
             ,'address_street_no_to'   => $phpExcel->getImportFldObj('Street No (to)')
             ,'address_building_name'  => $phpExcel->getImportFldObj('Building Name')
             ,'address_block'          => $phpExcel->getImportFldObj('Block')
             ,'address_floor_from'     => $phpExcel->getImportFldObj('Floor (from)')
             ,'address_floor_to'       => $phpExcel->getImportFldObj('Floor (to)')
             ,'address_unit_from'      => $phpExcel->getImportFldObj('Unit (from)')
             ,'address_unit_to'        => $phpExcel->getImportFldObj('Unit (to)')
             ,'address_po_code'        => $phpExcel->getImportFldObj('Zip Code')
             ,'phone'                  => $phpExcel->getImportFldObj('Phone - Primary')
             ,'phone2'                 => $phpExcel->getImportFldObj('Phone - Secondary')
             ,'mobile'                 => $phpExcel->getImportFldObj('Mobile')
             ,'fax'                    => $phpExcel->getImportFldObj('Fax')
             ,'email'                  => $phpExcel->getImportFldObj('Email')
             ,'business_name'          => $phpExcel->getImportFldObj('business_name')//ref field only
             ,'business_group_id'      => $phpExcel->getImportFldObj('BGID')//ref field only
        );

        /******** SPECIAL MANIPULATIONS ********/
        $fa['business_group_id']['defaultValue'] = $rowBG['business_group_id'];
        $fa['business_name']['defaultValue']     = $rowBG['title'];

        $fa['country']['refOnly']                = true;
        $fa['state']['refOnly']                  = true;
        $fa['city']['refOnly']                   = true;
        $fa['borough']['refOnly']                = true;
        $fa['area']['refOnly']                   = true;
        $fa['street']['refOnly']                 = true;
        $fa['address_street_no_from']['refOnly'] = true;
        $fa['address_street_no_to']['refOnly']   = true;
        $fa['address_building_name']['refOnly']  = true;
        $fa['address_block']['refOnly']          = true;
        $fa['address_floor_from']['refOnly']     = true;
        $fa['address_floor_to']['refOnly']       = true;
        $fa['address_unit_from']['refOnly']      = true;
        $fa['address_unit_to']['refOnly']        = true;
        $fa['address_po_code']['refOnly']        = true;

        /****************************************/
        $config = array(
             'module'              => 'directory_business'
            ,'fldsArr'             => $fa
            ,'callbackAfterInsert' => array($this, 'callbackAfterImportInsert')
        );

        return $phpExcel->importData($config);
    }

    function callbackAfterImportInsert($business_id, $fa) {
        $dbz = Zend_Registry::get('dbz');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');

        $country_id = $fn->getRecordIdByTitle('common_country', $fa['country']);
        $state_id   = $fn->getRecordIdByTitle('directory_state', $fa['state']);
        $city_id    = $fn->getRecordIdByTitle('directory_city', $fa['city']);
        $borough_id = $fn->getRecordIdByTitle('directory_borough', $fa['borough']);
        $area_id    = $fn->getRecordIdByTitle('directory_area', $fa['area']);
        $street_id  = $fn->getRecordIdByTitle('directory_street', $fa['street']);

        $fa2 = array();
        $fa2['published']  = 0;
        $fa2['country_id'] = $country_id;
        $fa2['state_id']   = $state_id;
        $fa2['city_id']    = $city_id;
        $fa2['borough_id'] = $borough_id;
        $fa2['area_id']    = $area_id;
        $fa2['street_id']  = $street_id;
        $fa2['address_street_no_from'] = $fa['address_street_no_from'];
        $fa2['address_street_no_to']   = $fa['address_street_no_to'];
        $fa2['address_block']          = $fa['address_block'];
        $fa2['address_floor_from']     = $fa['address_floor_from'];
        $fa2['address_floor_to']       = $fa['address_floor_to'];
        $fa2['address_unit_from']      = $fa['address_unit_from'];
        $fa2['address_unit_to']        = $fa['address_unit_to'];
        $fa2['address_po_code']        = $fa['address_po_code'];

        $SQL = $dbUtil->getInsertSQLStringFromArray($fa2, 'address');
        $stmt = $dbz->query($SQL);
        //$address_id = mysql_insert_id();
        $address_id = $stmt->getAdapter()->lastInsertId();

        $source_id = $fn->getSequenceFromSettings('m.directory.business.nextSourceId');
        $SQL = "
        UPDATE business b
        SET address_id = ?
           ,source_id = ?
        WHERE business_id = ?
        ";
        $dbz->query($SQL, array($address_id, $source_id, $business_id));
    }

    function getUpdateBusinesses() {
        $dbz = Zend_Registry::get('dbz');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');

        $business_group_id = $fn->getReqParam('business_group_id');

        $SQL = "
        SELECT bg.*
             ,(
              SELECT GROUP_CONCAT(t.tag_text ORDER BY t.tag_text SEPARATOR ', ')
              FROM tags t, tags_history th
              WHERE t.tags_id = th.tags_id
                AND th.record_id   = bg.business_group_id
                AND th.record_type = 'Business Group'
              ) AS tags

        FROM business_group bg
        WHERE bg.business_group_id = '{$business_group_id}'
        ";
        $rowBG = $fn->getRecordBySQL($SQL);

        //update main content in the business
        $SQL = "
        UPDATE business b
        SET category_id = :category_id
           ,sub_category_id = :sub_category_id
           ,business_type = :business_type
           ,website = :website
           ,description = :description
           ,description_chi = :description_chi
        WHERE business_group_id = :business_group_id
          AND (prohibit_business_group_update = 0 OR prohibit_business_group_update IS NULL)
        ";
        $arr = array(
             ':category_id' => $rowBG['category_id']
            ,':sub_category_id' => $rowBG['sub_category_id']
            ,':business_type' => $rowBG['business_type']
            ,':website' => $rowBG['website']
            ,':description' => $rowBG['description']
            ,':description_chi' => $rowBG['description_chi']
            ,':business_group_id' => $business_group_id
        );
        $dbz->query($SQL, $arr);

        //-------------------------------------------------//
        //get social media array from business group
        $SQL = "
        SELECT *
        FROM bg_social_media bgsm
        WHERE bgsm.business_group_id = ?
        ";
        $stmtBGSM = $dbz->query($SQL, $business_group_id);
        $bgsmRows = array();
        while ($row = $stmtBGSM->fetch()) {
            $bgsmRows[] = $row;
        }

        //delete existing social media for businesses under business group
        $SQL = "
        DELETE bsm
        FROM business_social_media bsm
        JOIN business b ON b.business_id = bsm.business_id
        WHERE b.business_group_id = ?
          AND (prohibit_business_group_update = 0 OR prohibit_business_group_update IS NULL)
        ";
        $dbz->query($SQL, $business_group_id);

        //-------------------------------------------------//
        //get payments array from business group
        $SQL = "
        SELECT *
        FROM bg_payment bgp
        WHERE bgp.business_group_id = ?
        ";
        $stmtbgp = $dbz->query($SQL, $business_group_id);
        $bgpRows = array();
        while ($row = $stmtbgp->fetch()) {
            $bgpRows[] = $row;
        }

        //delete existing payments for businesses under business group
        $SQL = "
        DELETE bsm
        FROM business_payment bsm
        JOIN business b ON b.business_id = bsm.business_id
        WHERE b.business_group_id = ?
          AND (b.prohibit_business_group_update = 0 OR b.prohibit_business_group_update IS NULL)
        ";
        $dbz->query($SQL, $business_group_id);

        //-------------------------------------------------//
        //delete existing tags for businesses
        $SQL = "
        DELETE th
        FROM tags_history th
        JOIN business b ON b.business_id = th.record_id
        WHERE b.business_group_id = ?
          AND (prohibit_business_group_update = 0 OR prohibit_business_group_update IS NULL)
        ";
        $dbz->query($SQL, $business_group_id);

        $SQL = "
        SELECT b.business_id
        FROM business b
        WHERE b.business_group_id = ?
          AND (prohibit_business_group_update = 0 OR prohibit_business_group_update IS NULL)
        ";
        $stmt = $dbz->query($SQL, $business_group_id);

        while ($rowB = $stmt->fetch()) {
            //update social media
            foreach ($bgsmRows as $bgsmRow) {
                $fa = array();
                $fa['social_media_id'] = $bgsmRow['social_media_id'];
                $fa['business_id']     = $rowB['business_id'];
                $fa['url']             = $bgsmRow['url'];
                $fa['creation_date']   = $fn->getCurrentTimestamp();
                $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'business_social_media');
                $dbz->query($SQL);
            }

            //update payment
            foreach ($bgpRows as $bgpRow) {
                $fa = array();
                $fa['payment_id']    = $bgpRow['payment_id'];
                $fa['business_id']   = $rowB['business_id'];
                $fa['creation_date'] = $fn->getCurrentTimestamp();
                $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'business_payment');
                $dbz->query($SQL);
            }

            //tags
            getCPModuleObj('web2_tags')->model
            ->updateCloudTagsByCSV('Business', $rowB['business_id'], $rowBG, 'tags', 'eng');
        }


    }

    function getDirectoryBusinessGroupDirectoryBusinessLinkSQL($id) {
        $SQL = "
        SELECT b.business_id
              ,CONCAT_WS('-', co.country_code, b.source_id) AS country_code_source_id
              ,b.email
        FROM business b
        JOIN address ad ON ad.address_id = b.address_id
        JOIN country co ON co.country_id = ad.country_id
        WHERE b.business_group_id = '{$id}'
        ORDER BY b.source_id
        ";
        return $SQL;
    }
}
