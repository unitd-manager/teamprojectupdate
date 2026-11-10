<?
class CP_Admin_Modules_Tradingsg_Pos_Model extends CP_Common_Lib_ModuleModelAbstract
{
   /**
     *
     */
    function getSearchProductTitle() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $title = $fn->getReqParam('term', '', true);
        $extractor = explode(" **** ", $title);

        $productTitle = $extractor[0];

        $SQL = "
        (SELECT p.title AS value
              ,p.title AS label
              ,p.product_id AS id
              ,CONCAT_WS(' :: ', p.item_code, p.title, p.price, p.carton_no, p.model, p.unit
              ,
                (SELECT SUM(qty) FROM po_product pop
                WHERE pop.product_id = p.product_id)
                -
                if(
                    (SELECT SUM(oi.qty) FROM order_item oi
                    LEFT JOIN (`order` o) ON (o.order_id = oi.order_id)
                    WHERE oi.record_id = p.product_id
                      AND o.order_status = 'Paid'
                      AND o.record_type = 'POS'
                    )
                    ,(SELECT SUM(oi.qty) FROM order_item oi
                    LEFT JOIN (`order` o) ON (o.order_id = oi.order_id)
                    WHERE oi.record_id = p.product_id
                      AND o.order_status = 'Paid'
                      AND o.record_type = 'POS'
                    )
                    ,''
                )
                -
                if(
                    (SELECT SUM(invItem.qty) FROM invoice_item invItem
                    LEFT JOIN (invoice inv) ON (inv.invoice_id = invItem.invoice_id AND inv.status != 'Cancelled' )
                    LEFT JOIN (`order` o) ON (o.order_id = inv.order_id)
                    WHERE invItem.record_id = p.product_id
                      AND o.record_type != 'POS'
                      AND o.link_stock = 1
                    )
                    ,(SELECT SUM(invItem.qty) FROM invoice_item invItem
                    LEFT JOIN (invoice inv) ON (inv.invoice_id = invItem.invoice_id AND inv.status != 'Cancelled' )
                    LEFT JOIN (`order` o) ON (o.order_id = inv.order_id)
                    WHERE invItem.record_id = p.product_id
                      AND o.record_type != 'POS'
                      AND o.link_stock = 1
                    )
                    ,''
                )
                +
                if(
                    (SELECT SUM(srh.qty_return) FROM sales_return_history srh
                    LEFT JOIN (invoice_item ini) ON (ini.invoice_item_id = srh.invoice_item_id)
                    LEFT JOIN (invoice inv) ON (inv.invoice_id = srh.invoice_id AND inv.status != 'Cancelled' )
                    WHERE ini.record_id = p.product_id
                      AND srh.status IS NULL
                    )
                    ,(SELECT SUM(srh.qty_return) FROM sales_return_history srh
                    LEFT JOIN (invoice_item ini) ON (ini.invoice_item_id = srh.invoice_item_id)
                    LEFT JOIN (invoice inv) ON (inv.invoice_id = srh.invoice_id AND inv.status != 'Cancelled' )
                    WHERE ini.record_id = p.product_id
                      AND srh.status IS NULL
                    )
                    ,''
                )
                -
                if(
                    (SELECT po.damaged_qty FROM product po
                    WHERE po.product_id = p.product_id
                    )
                    ,(SELECT po.damaged_qty FROM product po
                    WHERE po.product_id = p.product_id
                    )
                    ,''
                )
              ) AS label
        FROM product p
        LEFT JOIN (po_product pop) ON (pop.product_id = p.product_id)
        WHERE p.item_code LIKE '{$productTitle}'
        AND p.published = 1
        GROUP BY p.product_id
        ORDER BY p.title)

        UNION ALL
        (SELECT p.title AS value
              ,p.title AS label
              ,p.product_id AS id
              ,CONCAT_WS(' :: ', p.item_code, p.title, p.price, p.carton_no, p.model, p.unit
              ,
                (SELECT SUM(qty) FROM po_product pop
                WHERE pop.product_id = p.product_id)
                -
                if(
                    (SELECT SUM(oi.qty) FROM order_item oi
                    LEFT JOIN (`order` o) ON (o.order_id = oi.order_id)
                    WHERE oi.record_id = p.product_id
                      AND o.order_status = 'Paid'
                      AND o.record_type = 'POS'
                    )
                    ,(SELECT SUM(oi.qty) FROM order_item oi
                    LEFT JOIN (`order` o) ON (o.order_id = oi.order_id)
                    WHERE oi.record_id = p.product_id
                      AND o.order_status = 'Paid'
                      AND o.record_type = 'POS'
                    )
                    ,''
                )
                -
                if(
                    (SELECT SUM(invItem.qty) FROM invoice_item invItem
                    LEFT JOIN (invoice inv) ON (inv.invoice_id = invItem.invoice_id AND inv.status != 'Cancelled' )
                    LEFT JOIN (`order` o) ON (o.order_id = inv.order_id)
                    WHERE invItem.record_id = p.product_id
                      AND o.record_type != 'POS'
                      AND o.link_stock = 1
                    )
                    ,(SELECT SUM(invItem.qty) FROM invoice_item invItem
                    LEFT JOIN (invoice inv) ON (inv.invoice_id = invItem.invoice_id AND inv.status != 'Cancelled' )
                    LEFT JOIN (`order` o) ON (o.order_id = inv.order_id)
                    WHERE invItem.record_id = p.product_id
                      AND o.record_type != 'POS'
                      AND o.link_stock = 1
                    )
                    ,''
                )
                +
                if(
                    (SELECT SUM(srh.qty_return) FROM sales_return_history srh
                    LEFT JOIN (invoice_item ini) ON (ini.invoice_item_id = srh.invoice_item_id)
                    LEFT JOIN (invoice inv) ON (inv.invoice_id = srh.invoice_id AND inv.status != 'Cancelled' )
                    WHERE ini.record_id = p.product_id
                      AND srh.status IS NULL
                    )
                    ,(SELECT SUM(srh.qty_return) FROM sales_return_history srh
                    LEFT JOIN (invoice_item ini) ON (ini.invoice_item_id = srh.invoice_item_id)
                    LEFT JOIN (invoice inv) ON (inv.invoice_id = srh.invoice_id AND inv.status != 'Cancelled' )
                    WHERE ini.record_id = p.product_id
                      AND srh.status IS NULL
                    )
                    ,''
                )
                -
                if(
                    (SELECT po.damaged_qty FROM product po
                    WHERE po.product_id = p.product_id
                    )
                    ,(SELECT po.damaged_qty FROM product po
                    WHERE po.product_id = p.product_id
                    )
                    ,''
                )
              ) AS label
        FROM product p
        LEFT JOIN (po_product pop) ON (pop.product_id = p.product_id)
        WHERE p.carton_no LIKE '%{$productTitle}%'
        AND p.published = 1
        GROUP BY p.product_id
        ORDER BY p.title)

        UNION ALL
        (SELECT p.title AS value
              ,p.title AS label
              ,p.product_id AS id
              ,CONCAT_WS(' :: ', p.item_code, p.title, p.price, p.carton_no, p.model, p.unit
              ,
                (SELECT SUM(qty) FROM po_product pop
                WHERE pop.product_id = p.product_id)
                -
                if(
                    (SELECT SUM(oi.qty) FROM order_item oi
                    LEFT JOIN (`order` o) ON (o.order_id = oi.order_id)
                    WHERE oi.record_id = p.product_id
                      AND o.order_status = 'Paid'
                      AND o.record_type = 'POS'
                    )
                    ,(SELECT SUM(oi.qty) FROM order_item oi
                    LEFT JOIN (`order` o) ON (o.order_id = oi.order_id)
                    WHERE oi.record_id = p.product_id
                      AND o.order_status = 'Paid'
                      AND o.record_type = 'POS'
                    )
                    ,''
                )
                -
                if(
                    (SELECT SUM(invItem.qty) FROM invoice_item invItem
                    LEFT JOIN (invoice inv) ON (inv.invoice_id = invItem.invoice_id AND inv.status != 'Cancelled' )
                    LEFT JOIN (`order` o) ON (o.order_id = inv.order_id)
                    WHERE invItem.record_id = p.product_id
                      AND o.record_type != 'POS'
                      AND o.link_stock = 1
                    )
                    ,(SELECT SUM(invItem.qty) FROM invoice_item invItem
                    LEFT JOIN (invoice inv) ON (inv.invoice_id = invItem.invoice_id AND inv.status != 'Cancelled' )
                    LEFT JOIN (`order` o) ON (o.order_id = inv.order_id)
                    WHERE invItem.record_id = p.product_id
                      AND o.record_type != 'POS'
                      AND o.link_stock = 1
                    )
                    ,''
                )
                +
                if(
                    (SELECT SUM(srh.qty_return) FROM sales_return_history srh
                    LEFT JOIN (invoice_item ini) ON (ini.invoice_item_id = srh.invoice_item_id)
                    LEFT JOIN (invoice inv) ON (inv.invoice_id = srh.invoice_id AND inv.status != 'Cancelled' )
                    WHERE ini.record_id = p.product_id
                      AND srh.status IS NULL
                    )
                    ,(SELECT SUM(srh.qty_return) FROM sales_return_history srh
                    LEFT JOIN (invoice_item ini) ON (ini.invoice_item_id = srh.invoice_item_id)
                    LEFT JOIN (invoice inv) ON (inv.invoice_id = srh.invoice_id AND inv.status != 'Cancelled' )
                    WHERE ini.record_id = p.product_id
                      AND srh.status IS NULL
                    )
                    ,''
                )
                -
                if(
                    (SELECT po.damaged_qty FROM product po
                    WHERE po.product_id = p.product_id
                    )
                    ,(SELECT po.damaged_qty FROM product po
                    WHERE po.product_id = p.product_id
                    )
                    ,''
                )
              ) AS label
        FROM product p
        LEFT JOIN (po_product pop) ON (pop.product_id = p.product_id)
        WHERE p.model LIKE '%{$productTitle}%'
        AND p.published = 1
        GROUP BY p.product_id
        ORDER BY p.title)

        UNION ALL
        (SELECT p.title AS value
              ,p.title AS label
              ,p.product_id AS id
              ,CONCAT_WS(' :: ', p.item_code, p.title, p.price, p.carton_no, p.model, p.unit
              ,
                (SELECT SUM(qty) FROM po_product pop
                WHERE pop.product_id = p.product_id)
                -
                if(
                    (SELECT SUM(oi.qty) FROM order_item oi
                    LEFT JOIN (`order` o) ON (o.order_id = oi.order_id)
                    WHERE oi.record_id = p.product_id
                      AND o.order_status = 'Paid'
                      AND o.record_type = 'POS'
                    )
                    ,(SELECT SUM(oi.qty) FROM order_item oi
                    LEFT JOIN (`order` o) ON (o.order_id = oi.order_id)
                    WHERE oi.record_id = p.product_id
                      AND o.order_status = 'Paid'
                      AND o.record_type = 'POS'
                    )
                    ,''
                )
                -
                if(
                    (SELECT SUM(invItem.qty) FROM invoice_item invItem
                    LEFT JOIN (invoice inv) ON (inv.invoice_id = invItem.invoice_id AND inv.status != 'Cancelled' )
                    LEFT JOIN (`order` o) ON (o.order_id = inv.order_id)
                    WHERE invItem.record_id = p.product_id
                      AND o.record_type != 'POS'
                      AND o.link_stock = 1
                    )
                    ,(SELECT SUM(invItem.qty) FROM invoice_item invItem
                    LEFT JOIN (invoice inv) ON (inv.invoice_id = invItem.invoice_id AND inv.status != 'Cancelled' )
                    LEFT JOIN (`order` o) ON (o.order_id = inv.order_id)
                    WHERE invItem.record_id = p.product_id
                      AND o.record_type != 'POS'
                      AND o.link_stock = 1
                    )
                    ,''
                )
                +
                if(
                    (SELECT SUM(srh.qty_return) FROM sales_return_history srh
                    LEFT JOIN (invoice_item ini) ON (ini.invoice_item_id = srh.invoice_item_id)
                    LEFT JOIN (invoice inv) ON (inv.invoice_id = srh.invoice_id AND inv.status != 'Cancelled' )
                    WHERE ini.record_id = p.product_id
                      AND srh.status IS NULL
                    )
                    ,(SELECT SUM(srh.qty_return) FROM sales_return_history srh
                    LEFT JOIN (invoice_item ini) ON (ini.invoice_item_id = srh.invoice_item_id)
                    LEFT JOIN (invoice inv) ON (inv.invoice_id = srh.invoice_id AND inv.status != 'Cancelled' )
                    WHERE ini.record_id = p.product_id
                      AND srh.status IS NULL
                    )
                    ,''
                )
                -
                if(
                    (SELECT po.damaged_qty FROM product po
                    WHERE po.product_id = p.product_id
                    )
                    ,(SELECT po.damaged_qty FROM product po
                    WHERE po.product_id = p.product_id
                    )
                    ,''
                )
              ) AS label
        FROM product p
        LEFT JOIN (po_product pop) ON (pop.product_id = p.product_id)
        WHERE p.title LIKE '%{$productTitle}%'
        AND p.published = 1
        GROUP BY p.product_id
        ORDER BY p.title)
        ";
        $result = $db->sql_query($SQL);
        $dataArray = $dbUtil->getResultsetAsArray($result);
        $arr = json_encode($dataArray);
        return $arr;
    }

    /**
     *
     */
     function getUpdateOrderLineItems() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');
        $dbUtil = Zend_Registry::get('dbUtil');

        $product_id = $fn->getReqParam('product_id');
        $session_order_id = isset($_SESSION['order_id']) ? $_SESSION['order_id']  : '';

        $SQL    = "
        SELECT p.title
              ,p.item_code
              ,p.model
              ,p.price
              ,p.part_number
              ,p.price
              ,p.vat
        FROM product p
        LEFT JOIN (po_product pop) ON (pop.product_id = p.product_id)
        WHERE p.product_id = '{$product_id}'
        ";
        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);

        $vat = $row['vat'] * $row['price'] / 100;

        $fa = array();
        $fa['order_id']   = $session_order_id;
        $fa['record_id']  = $product_id;
        $fa['item_title'] = $row['title'];
        $fa['item_code']  = $row['item_code'];
        $fa['model']      = $row['model'];
        $fa['unit_price'] = $row['price'];
        $fa['cost_price'] = $row['price'];
        $fa['ref_code']   = $row['part_number'];
        $fa['discount_type']   = '%';
        $fa['qty']        = 1;
        $fa['vat']        = $row['vat'];

        $SQLOrderItem = "
        SELECT *
        FROM `order_item`
        WHERE order_id = '{$session_order_id}'
          AND record_id = {$product_id}
        ";
        $resultOrderItem = $db->sql_query($SQLOrderItem);
        $rec = $db->sql_fetchrow($resultOrderItem);

        if($rec['order_item_id'] != ''){
            $SQLUpdate = "UPDATE order_item SET qty = ({$rec['qty']} + 1)
                          WHERE order_id = '{$session_order_id}' AND record_id = {$product_id}";
            $resultUpdate = $db->sql_query($SQLUpdate);
        } else {
            $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'order_item');
            $db->sql_query($SQL);
            $order_item_id = $db->sql_nextid();
        }
    }


    /**
     *
     */
    function getCreateNewOrder() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        $fa = array();
        $fa['order_status'] = 'New';
        $fa['record_type'] = 'POS';
        $fa['order_date'] = date('Y-m-d');
        $fa['name_of_company'] = 'POS';
        $fa['vat'] = 1;
        $fa['link_stock'] = 1;
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
            $fa['site_id'] = $cpSiteIdSession;
        }
        $fa = $fn->addCreationDetailsToFieldsArray($fa, 'order');

        $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'order');
        $db->sql_query($SQL);
        $order_id = $db->sql_nextid();

        $_SESSION['order_id'] = $order_id;

    }

    /**
     *
     */
    function getApplyDiscountSubmit() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');

        if (!$this->getApplyDiscountValidate()){
            return $validate->getErrorMessageXML();
        }

        $discount_percentage= $fn->getPostParam('discount_percentage');
        $session_order_id = isset($_SESSION['order_id']) ? $_SESSION['order_id']  : '';

        $sqlUpdate = "
        UPDATE order_item SET discount_percentage = '{$discount_percentage}', discount_type = '%'
        WHERE order_id = {$session_order_id}
        ";
        $resultUpdate = $db->sql_query($sqlUpdate);

        /*$SQL    = "
        SELECT cost_price
              ,selling_price
              ,qty
              ,quote_id
              ,quote_product_id
              ,product_id
              ,discount_percentage
              ,discount_type
              ,mark_up
              ,mark_up_type
        FROM quote_product
        WHERE quote_id = {$quote_id}
        ";
        $result = $db->sql_query($SQL);

        while ($row = $db->sql_fetchrow($result)) {
            $discount_value              = $row['discount_percentage'];
            $discount_value_for_one_qty  =  $row['cost_price'] * ($row['discount_percentage']/100);

            $SQLUpdate    = "
            UPDATE quote_product
            set selling_price = {$selling_price}
            WHERE quote_product_id = {$row['quote_product_id']}
            ";
            $resultUpdate = $db->sql_query($SQLUpdate);
        }*/

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getApplyDiscountValidate() {
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
    function getAddClientSubmit() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');

        if (!$this->getAddClientValidate()){
            return $validate->getErrorMessageXML();
        }
        $session_order_id = isset($_SESSION['order_id']) ? $_SESSION['order_id']  : '';

        $company_name= $fn->getReqParam('company_name');
        $mobile= $fn->getReqParam('mobile');
        $email= $fn->getReqParam('email');
        $address_flat= $fn->getReqParam('address_flat');
        $address_street= $fn->getReqParam('address_street');
        $address_town= $fn->getReqParam('address_town');
        $address_state= $fn->getReqParam('address_state');
        $address_country= $fn->getReqParam('address_country');

        $fa = array();
        $fa['company_name'] = $company_name;
        $fa['mobile'] = $mobile;
        $fa['email'] = $email;
        $fa['address_flat'] = $address_flat;
        $fa['address_street'] = $address_street;
        $fa['address_town'] = $address_town;
        $fa['address_state'] = $address_state;
        $fa['address_country'] = $address_country;
        $fa['category'] = 'Client';
        $id = $fn->addRecord($fa, 'company');

        $fa1 = array();
        $fa1['cust_company_name'] = $company_name;
        $fa1['company_id'] = $id;
        $fa1['cust_phone'] = $mobile;
        $fa1['cust_email'] = $email;
        $fa1['cust_address1'] = $address_flat;
        $fa1['cust_address2'] = $address_street;
        $fa1['cust_address_city'] = $address_town;
        $fa1['cust_address_state'] = $address_state;
        $fa1['cust_address_country_code'] = $address_country;

        $whereCondition = "WHERE order_id = {$session_order_id}";
        $SQL = $dbUtil->getUpdateSQLStringFromArray($fa1, 'order', $whereCondition);
        $db->sql_query($SQL);

        return $validate->getSuccessMessageXML();
    }


    /**
     *
     */
    function getAddClientValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $validate->resetErrorArray();
        $company_name= $fn->getReqParam('company_name');

        $validate->validateData('company_name', 'Please enter the company name');

        if($company_name != ''){
            $SQL = "
            SELECT c.*
            FROM company c
            WHERE c.company_name = '{$company_name}'
            ";
            $result = $db->sql_query($SQL);
            $numRows  = $db->sql_numrows($result);
            if($numRows > 0){
                $validate->errorArray['company_name']['name'] = "company_name";
                $validate->errorArray['company_name']['msg']  = "Company name already exist";
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
    function getSearchCustomerDetails() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $title = $fn->getReqParam('term', '', true);
        $extractor = explode(" **** ", $title);

        $companyDetail = $extractor[0];

        $SQL = "
        SELECT c.company_name AS value
              ,c.company_name AS label
              ,c.company_id AS id
              ,c.company_name
        FROM company c
        WHERE (c.company_id LIKE '%{$companyDetail}%'
        OR c.company_name LIKE '%{$companyDetail}%'
        OR c.mobile LIKE '%{$companyDetail}%'
        OR c.email LIKE '%{$companyDetail}%')
        ORDER BY c.company_name
        ";
        $result = $db->sql_query($SQL);

        $dataArray = $dbUtil->getResultsetAsArray($result);
        $arr = json_encode($dataArray);
        return $arr;
    }

    /**
     *
     */
    function getDisplayCustomerDetails() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $company_id= $fn->getReqParam('company_id');
        $session_order_id = isset($_SESSION['order_id']) ? $_SESSION['order_id']  : '';

        $SQL = "
        SELECT c.*
        FROM company c
        WHERE c.company_id = {$company_id}
        ";
        $result = $db->sql_query($SQL);
        $row    = $db->sql_fetchrow($result);

        $text = "
        <div class='mt10'>
            <div>Company Name: {$row['company_name']}</div>
            <div>Mobile: {$row['mobile']}</div>
            <div>Email : {$row['email']}</div>
            <div>Address: {$row['address_flat']} ,{$row['address_street']} ,{$row['address_town']} {$row['address_state']}</div>
        </div>

        <div class='button float_left mt10'>
            <a href='javascript:void(0);' id='removeClient'>Remove Client</a>
        </div>
        ";

        $fa1 = array();
        $fa1['cust_company_name'] = $row['company_name'];
        $fa1['company_id'] = $row['company_name'];
        $fa1['cust_phone'] = $row['mobile'];
        $fa1['cust_email'] = $row['email'];
        $fa1['cust_address1'] = $row['address_flat'];
        $fa1['cust_address2'] = $row['address_street'];
        $fa1['cust_address_city'] = $row['address_town'];
        $fa1['cust_address_state'] = $row['address_state'];
        $fa1['cust_address_country_code'] = $row['address_country'];

        $whereCondition = "WHERE order_id = {$session_order_id}";
        $SQL = $dbUtil->getUpdateSQLStringFromArray($fa1, 'order', $whereCondition);
        $db->sql_query($SQL);

        return $text;
    }

    /**
     *
     */
    function getRemoveClient() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $session_order_id = isset($_SESSION['order_id']) ? $_SESSION['order_id']  : '';

        $fa1 = array();
        $fa1['cust_company_name'] = '';
        $fa1['company_id'] = '';
        $fa1['cust_phone'] = '';
        $fa1['cust_email'] = '';
        $fa1['cust_address1'] = '';
        $fa1['cust_address2'] = '';
        $fa1['cust_address_city'] = '';
        $fa1['cust_address_state'] = '';
        $fa1['cust_address_country_code'] = '';

        $whereCondition = "WHERE order_id = {$session_order_id}";
        $SQL = $dbUtil->getUpdateSQLStringFromArray($fa1, 'order', $whereCondition);
        $db->sql_query($SQL);

        return $text;
    }

    /**
     *
     */
    function getUpdateQtyOrderItem() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $order_item_id = $fn->getReqParam('order_item_id');
        $qty = $fn->getReqParam('qty');

        $SQL    = "
        UPDATE order_item
        set qty = {$qty}
        WHERE order_item_id = {$order_item_id}
        ";
        $result = $db->sql_query($SQL);

    }

    /**
     *
     */
    function getUpdatediscountType() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $order_item_id = $fn->getReqParam('order_item_id');
        $discount_type = $fn->getReqParam('discount_type');

        $order_item_percentage = 0;

        if ($discount_type!=''){
            $order_item_percentage = $discount_type;
            $discount_type ='%';
        }

        $SQL    = "
        UPDATE order_item
        set discount_type = '{$discount_type}',discount_percentage = '{$order_item_percentage}'
        WHERE order_item_id = {$order_item_id}
        ";
        $result = $db->sql_query($SQL);

    }

    /**
     *
     */
    function getUpdateDiscountPercentOrderItem() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $order_item_id = $fn->getReqParam('order_item_id');
        $discount_percentage = $fn->getReqParam('discount_percentage');
        $discount_type = $fn->getReqParam('discount_type');

        $SQL    = "
        UPDATE order_item
        set discount_percentage = '{$discount_percentage}',discount_type ='Value'
        WHERE order_item_id = {$order_item_id}
        ";
        $result = $db->sql_query($SQL);

    }

    /**
     *
     */
    function getUpdatePiecesOrderItem() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $order_item_id = $fn->getReqParam('order_item_id');
        $pieces = $fn->getReqParam('pieces');

        $SQL    = "
        UPDATE order_item
        set pieces = '{$pieces}'
        WHERE order_item_id = {$order_item_id}
        ";
        $result = $db->sql_query($SQL);

    }

    /**
     *
     */
    function getUpdateDiscountOrder() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $order_id = $fn->getReqParam('order_id');
        $discount = $fn->getReqParam('discount');

        $SQL    = "
        UPDATE `order`
        set discount = {$discount}
        WHERE order_id = {$order_id}
        ";
        $result = $db->sql_query($SQL);

    }

    /**
     *
     */
    function getUpdateBalance() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $amount_given = $fn->getReqParam('amount_given');
        $netTotal = $fn->getReqParam('netTotal');

        $balance = $amount_given - $netTotal;
        $balance = number_format($balance, 2);
        return $balance;

    }

    /**
     *
     */
    function getCancelOrder() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $session_order_id = isset($_SESSION['order_id']) ? $_SESSION['order_id']  : '';

        $SQL    = "
        UPDATE `order`
        set order_status = 'Cancelled'
        WHERE order_id = {$session_order_id}
        ";
        $result = $db->sql_query($SQL);

        $SQL    = "
        UPDATE `invoice`
        set status = 'Cancelled'
        WHERE order_id = {$session_order_id}
        ";
        $result = $db->sql_query($SQL);

        $SQL    = "
        UPDATE `receipt`
        set receipt_status = 'Cancelled'
        WHERE order_id = {$session_order_id}
        ";
        $result = $db->sql_query($SQL);

        $_SESSION['order_id'] = '';

    }

    /**
     *
     */
    function getCloseOrder() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $_SESSION['order_id'] = '';

    }

    /**
     *
     */
    function getDeleteItem(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $formObj = Zend_Registry::get('formObj');
        $cpCfg = Zend_Registry::get('cpCfg');

        $session_order_id = isset($_SESSION['order_id']) ? $_SESSION['order_id']  : false;
        $order_item_id    = $fn->getReqParam('order_item_id');

        if($session_order_id){
            $deleteSQL    = "
            DELETE FROM order_item
            WHERE order_id = {$session_order_id}
            AND order_item_id = {$order_item_id}
            ";
            $result = $db->sql_query($deleteSQL);
        }
        return;
    }

}
