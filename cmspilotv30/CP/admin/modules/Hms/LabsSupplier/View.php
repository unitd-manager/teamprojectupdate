<?
class CP_Admin_Modules_Hms_LabsSupplier_View extends CP_Common_Lib_ModuleViewAbstract
{
    /**
     *
     */
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');

        $count   = 0;
        $rows    = '';

        foreach ($dataArray as $row){
            $email     = $row['email'];
            $website   = $row['website'];

            $rows .= "
            {$listObj->getListRowHeader($row, $count)}
            {$listObj->getGoToDetailText($count, $row['title'])}
            {$listObj->getListDataCell("<a href='{$website}'>{$website}</a>")}
            {$listObj->getListDataCell($row['phone'])}
            {$listObj->getListRowEnd($row['labs_supplier_id'])}
            ";

            $count++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Name', 'ls.title')}
        {$listObj->getListHeaderCell('Website', 'ls.website')}
        {$listObj->getListHeaderCell('Telephone', 'ls.phone' )}
        {$listObj->getListHeaderEnd()}
        {$rows}
        {$listObj->getListFooter()}
        ";

        return $text;
    }

    /**
     *
     */
    function getNew(){
        $formObj = Zend_Registry::get('formObj');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $expVl = array('sqlType' => 'OneField');
        $sqlCategory = $fn->getValueListSQL('labSupplierCategory');

        $fielset1 = "
        {$formObj->getTBRow('Name', 'title')}
        {$formObj->getDDRowBySQL('Category', 'category', $sqlCategory, '', $expVl)}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fielset1)}
        ";

        return $text;
    }

    /**
     *
     */
    function getEdit($row){
        $formObj = Zend_Registry::get('formObj');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        $formObj->mode = $tv['action'];

        $discountPercent = '';
        $cstNo = '';
        $tinNo = '';

        $sqlStatus   = $fn->getValueListSQL('companyStatus');
        $sqlCategory = $fn->getValueListSQL('labSupplierCategory');
        

        $sqlCountry = getCPModelObj('common_geoCountry')->getCountryDDSQL();
        $expCountry = array('detailValue' => $row['country_name']);

        $expVl = array('sqlType' => 'OneField');

        $text = "
        <div class='linkPortalWrapper'>
            <div expanded='1' class='header'>
                <div class='floatbox'>
                    <div class='float_left'>Labs Supplier Details</div>
                    <div class='toggle'></div>
                </div>
            </div>
            <div>
                <div class='linkPortalDataWrapper'>
                    <table class='thinlist'>
                        <tbody>
                            <tr>
                                <td>{$formObj->getTBRow('Name', 'title', $row['title'])}</td>
                                <td>{$formObj->getTBRow('Website', 'website', $row['website'])}</td>
                                <td>{$formObj->getTBRow('Main Phone', 'phone', $row['phone'])}</td>
                                <td>{$formObj->getTBRow('Main Fax', 'fax', $row['fax'])}</td>
                            </tr>

                            <tr>
                                <th colspan='5'> Delivery Address</th>
                            </tr>

                            <tr>
                                <td>{$formObj->getTBRow('Address1', 'address_flat', $row['address_flat'])}</td>
                                <td>{$formObj->getTBRow('District/ Town', 'address_town', $row['address_town'])}</td>
                                <td>{$formObj->getTBRow('State/ Zip', 'address_state', $row['address_state'])}</td>
                                <td>{$formObj->getDDRowBySQL('Country', 'address_country', $sqlCountry, $row['address_country'], $expCountry)}</td>
                            </tr>

                            <tr>
                                <th colspan='5'>Billing Address</th>
                            </tr>

                            <tr>
                                <td>{$formObj->getTBRow('Address1', 'billing_address_flat', $row['billing_address_flat'])}</td>
                                <td>{$formObj->getTBRow('Address2', 'billing_address_street', $row['billing_address_street'])}</td>
                                <td>{$formObj->getTBRow('District/ Town', 'billing_address_town', $row['billing_address_town'])}</td>
                                <td>{$formObj->getTBRow('State/ Zip', 'billing_address_state', $row['billing_address_state'])}</td>
                                <td>{$formObj->getDDRowBySQL('Country', 'billing_address_country', $sqlCountry, $row['billing_address_country'], $expCountry)}</td>
                            </tr>

                            <tr>
                                <td colspan='5' class='creModdate'>{$formObj->getCreationModificationText($row)}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getPrintDetail($row){
        $db = Zend_Registry::get('db');
        return $this->getDetail($row);
    }

    /**
     *
     */
    function getSearch(){
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $sqlCategory = $fn->getValueListSQL('companyCategory');
        $sqlStatus   = $fn->getValueListSQL('companyStatus');
        $expVl = array('sqlType' => 'OneField');

        $spArray = array(
            "Flagged"
           ,"Not-Flagged"
        );

        $fielset = "
        {$formObj->getTBRow('Name', 'title')}
        {$formObj->getDDRowBySQL('Choose Category', 'category', $sqlCategory, 'Client', $expVl)}
        {$formObj->getDDRowBySQL('Status', 'status', $sqlStatus, 'Current', $expVl)}
        {$formObj->getDDRowByArr('Special Search', 'special_search', $spArray)}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Company Details', $fielset)}
        ";

        return $text;
    }

    /**
     *
     */
    function getRightPanel($row){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $media = Zend_Registry::get('media');

        /*$sqlCategory = "
        SELECT a.*,b.labs_supplier_id, c.labs_suppliercategory_id
        FROM `valuelist` a, `labs_supplier` b, labs_suppliercategory c
        WHERE a.`key_text` = 'labSupplierCategory'
        AND b.labs_supplier_id = {$row['labs_supplier_id']}
        AND c.labs_supplier_id = {$row['labs_supplier_id']}
        ";
        $resultCategory = $db->sql_query($sqlCategory);
        $rowCategory    = $db->sql_fetchrow($resultCategory);*/

        //{$displayLinkData->getLinkPortalMain("hms_labsSupplier", "hms_labsSupplierLink", "Category Linked", $row)}

        $text = "
        {$displayLinkData->getLinkPortalMain('hms_labsSupplier', 'hms_contactLink', 'Contacts Linked', $row)}
        <div id='categoryLinkPortal'>{$this->getAddCategory($row['labs_supplier_id'])}</div>
        {$media->getRightPanelMediaDisplay('Attachments', 'hms_labsSupplier', 'attachment', $row)}
        ";

        return $text;
    }

     /**
     *
     */
    function getAddCategory($labs_supplier_id=''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        if($labs_supplier_id == ''){
            $labs_supplier_id = $fn->getReqParam('labs_supplier_id');
        }

        $recCount = $fn->getRecordCount('labs_suppliercategorylink', "labs_supplier_id = '{$labs_supplier_id}'");

        $header = '';
        $categoryLinked = "
            <tr>
                <td class='txtCenter'><div class='mb10 mt10'>No Records Linked</div></td>
            </tr>
        ";

        if($recCount > 0){
            $header ="
            <thead>
                <th width='10%'>S.No</th>
                <th width='90%'>Category</th>
            </thead>
            ";

            $categoryLinked = $this->getAddCategoryDetail($labs_supplier_id);

        }

        $formActionCategory = "index.php?module=hms_labsSupplier&_spAction=AddCategoryLinkPortal&labs_supplier_id={$labs_supplier_id}&showHTML=0";
        $add = "<div class='float_left'>
                    <a class='AddSupplierCategory' id='AddSupplierCategory' href='{$formActionCategory}' labs_supplier_id='{$labs_supplier_id}'><u>Add</u></a>
                </div>";

        $text = "
        <div class='linkPortalWrapper hms_purchaseOrder__hms_po_productLink'>
            <div class='header' expanded='1'>
                <div class='floatbox'>
                    <div class='float_left'>Category Linked</div>
                    {$add}
                    <div class='txtRight'>
                        <span class='count'>({$recCount})</span>
                        <div class='toggle'></div>
                    </div>
                </div>
            </div>
            <div class='linkPortalDataWrapper'>
                <form class='purchaseOrderPoProduct'>
                    <table class='labsSupplier room-category-table'>
                        {$header}
                        <tbody id='AddSupplierCategoryPortal'>
                            {$categoryLinked}
                        </tbody>
                    </table>
                    <input type='hidden' name='labs_supplier_id' value='{$labs_supplier_id}' />
                </form>
            </div>
        </div>
        ";

        return $text;

    }

    /**
     *
     */
    function getAddCategoryDetail($labs_supplier_id) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        $rows = '';

        $sqlCategory = "
        SELECT labs_suppliercategory_id
        FROM labs_suppliercategorylink
        WHERE labs_supplier_id = {$labs_supplier_id}
        ";
        $resultCategory  = $db->sql_query($sqlCategory);
        $numRowsCheck = $db->sql_numrows($resultCategory);  
        
        $SerialNo = 1;
        While($rowCategory  = $db->sql_fetchrow($resultCategory)){
            $SQLTitle = "
            SELECT labs_suppliercategory_id
              ,title
            FROM `labs_suppliercategory`
            WHERE labs_suppliercategory_id = {$rowCategory['labs_suppliercategory_id']}
            ";
            $resultTitle = $db->sql_query($SQLTitle);
            $rowTitle    = $db->sql_fetchrow($resultTitle);

            $rows .= "
            <tr>
                <td width='10%'>{$SerialNo}</td>
                <td width='90%'>{$rowTitle['title']}</td>
            </tr>
            ";

            $SerialNo++;
        }

        return $rows;
    }

    /**
     *
     */
    function getAddCategoryLinkPortal() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');
        
        $labs_supplier_id = $fn->getReqParam('labs_supplier_id');
        
        $text = '';
        $rows = '';

        $sqlCategory = "
        SELECT labs_suppliercategory_id
              ,title
        FROM `labs_suppliercategory`
        ";
        $resultCategory = $db->sql_query($sqlCategory);
        
        $SerialNo = 1;
        While($rowCategory  = $db->sql_fetchrow($resultCategory)){

            $SQLCheck = "
            SELECT labs_suppliercategory_id
            FROM labs_suppliercategorylink
            WHERE labs_supplier_id = {$labs_supplier_id}
            AND labs_suppliercategory_id = {$rowCategory['labs_suppliercategory_id']}
            ";
            $resultCheck  = $db->sql_query($SQLCheck);
            $numRowsCheck = $db->sql_numrows($resultCheck);    

            if($numRowsCheck > 0){            
                $addRemoveLink = "
                <a class='btn btn-danger removeCategoryToSupplier' labs_suppliercategory_id='{$rowCategory['labs_suppliercategory_id']}' labs_supplier_id='{$labs_supplier_id}'>Remove</a>
                ";
            }else{
                $addRemoveLink = "
                <a class='btn btn-info addCategoryToSupplier' labs_suppliercategory_id='{$rowCategory['labs_suppliercategory_id']}' labs_supplier_id='{$labs_supplier_id}'>Add</a>
                ";
            }


            $rows .= "
            <tr>
                <td width='20%'>{$SerialNo}</td>
                <td width='60%'>{$rowCategory['title']}</td>
                <td width='20%'>{$addRemoveLink}</td>
            </tr>
            ";

            $SerialNo++;
        }

        $removeAllLink = "
        <a class='btn btn-danger float_right removeCategoryAll' labs_supplier_id='{$labs_supplier_id}'>Remove All</a>
        ";

        $addAllLink = "
        <a class='btn btn-success float_left addCategoryAll' labs_supplier_id='{$labs_supplier_id}'>Add All</a>
        ";

        $text = "
        <table id='AddSupplierCategoryLinkTable' class = 'thinlist'>
            <thead>
                <tr>
                    <th colspan = '3'>{$addAllLink} {$removeAllLink}</th>
                </tr>
                <tr>
                    <th width='20%'>S.No</th>
                    <th width='60%'>Title</th>
                    <th width='20%'>Action</th>
                </tr>
            </thead>
            <tbody>
                {$rows}
            </tbody>
        </table>
        ";

        return $text;

    }

    /**
     *
     */
    function getQuickSearch() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        $status   = $fn->getReqParam('status');

        $sqlStatus = $fn->getValueListSQL('companyStatus');

        $spArray = array(
            "Flagged"
           ,"Not-Flagged"
        );

        $text = "
        <td>
            <select name='status' >
                <option value=''>Status</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $sqlStatus, $status)}
            </select>
        </td>
        <td>
            <select name='special_search'>
                <option value=''>Special Search</option
                {$cpUtil->getDropDown1($spArray, $tv['special_search'])}
           </select>
        </td>
        ";

        return $text;
    }
}