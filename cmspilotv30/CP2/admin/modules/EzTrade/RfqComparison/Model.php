<?
class CP_Admin_Modules_EzTrade_RfqComparison_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        $SQL = "
        SELECT DISTINCT
               qri.*
              ,qr.quote_request_code
              ,qr.buy_currency
              ,qr.payment_terms
              ,qr.notes_from_supplier AS notes_from_supplier_header
              ,CONCAT_WS('-', qr.quote_request_code, qri.line_no) AS quote_request_line_no
              ,qr.delivery_terms_supplier
              ,qr.shipping_method
              ,qr.valid_until
              ,p.product_code
              ,p.title AS product_name
              ,p.unit
              ,c.company_name AS supplier_name
        FROM enquiry_product ep
        JOIN quote_request_items qri ON (qri.enquiry_product_id = ep.enquiry_product_id)
        JOIN quote_request qr        ON (qr.quote_request_id = qri.quote_request_id)
        JOIN product p               ON (p.product_id = ep.product_id)
        LEFT JOIN company c          ON (c.company_id = qr.company_id_supplier)
        ";

        $SQL = "
        SELECT DISTINCT
               qri.*
              ,qr.quote_request_code
              ,qr.buy_currency
              ,qr.payment_terms
              ,qr.notes_from_supplier AS notes_from_supplier_header
              ,CONCAT_WS('-', qr.quote_request_code, qri.line_no) AS quote_request_line_no
              ,qr.delivery_terms_supplier
              ,qr.shipping_method
              ,qr.valid_until
              ,p.product_code
              ,p.title AS product_name
              ,p.unit
              ,c.company_name AS supplier_name
        FROM enquiry_product ep
        LEFT JOIN quote_request_items qri ON (qri.quote_request_items_id = ep.quote_request_items_id)
        LEFT JOIN quote_request qr        ON (qr.quote_request_id = qri.quote_request_id)
        JOIN product p               ON (p.product_id = ep.product_id)
        LEFT JOIN company c          ON (c.company_id = qr.company_id_supplier)
        ";

        
        return $SQL;
    }
}
