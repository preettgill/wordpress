<?php 
//if (isset($_SERVER['REMOTE_ADDR'])) {
//    die(':)');
//}

echo "Cron is running";

$hostname = "localhost";
$username = "username";
$password = "password";
$myDatabase = "database";

try {
    $conn = new PDO('mysql:host=localhost;dbname='.$myDatabase, $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);    

    $stmt = $conn->prepare("select
    (@cnt := @cnt + 1) AS DocNum,
    DATE_FORMAT(max( CASE WHEN pm.meta_key = '_paid_date' and p.ID = pm.post_id THEN pm.meta_value END ), '%Y%m%d') as DocDate,
    max( CASE WHEN pm.meta_key = 'jckwds_date_ymd' and p.ID = pm.post_id THEN pm.meta_value END ) as DocDueDate,
    'CC161' as CardCode,
    max( CASE WHEN pm.meta_key = '_customer_user' and p.ID = pm.post_id THEN pm.meta_value END )  as NumAtCard,
    max( CASE WHEN pm.meta_key = '_order_total' and p.ID = pm.post_id THEN pm.meta_value END ) as DocTotal,
    '-1' as SlpCode,
    max( CASE WHEN pm.meta_key = '_billing_first_name' and p.ID = pm.post_id THEN pm.meta_value END ) as U_Name1,
    max( CASE WHEN pm.meta_key = '_billing_last_name' and p.ID = pm.post_id THEN pm.meta_value END ) as U_Name2,
    max( CASE WHEN pm.meta_key = '_shipping_address_1' and p.ID = pm.post_id THEN pm.meta_value END ) as U_Address1,
    max( CASE WHEN pm.meta_key = '_shipping_address_2' and p.ID = pm.post_id THEN pm.meta_value END ) as U_Address2,
    '' as U_Address3,
    max( CASE WHEN pm.meta_key = '_shipping_postcode' and p.ID = pm.post_id THEN pm.meta_value END ) as U_Zipcode,
    max( CASE WHEN pm.meta_key = '_cart_discount' and p.ID = pm.post_id THEN pm.meta_value END ) as U_Event,
    CONCAT (max(CASE WHEN pm.meta_key = 'jckwds_date' and p.ID = pm.post_id THEN pm.meta_value END) , ' ' , max(CASE WHEN pm.meta_key = 'jckwds_timeslot' and p.ID = pm.post_id THEN pm.meta_value END))as U_Delivery,
    '' as U_Special, 
    '' as U_Building,
    '' as U_Homephone,
    max( CASE WHEN pm.meta_key = '_billing_phone' and p.ID = pm.post_id THEN pm.meta_value END ) as U_Mobile1,
    '' as U_Mobile2,
    '' as U_Office,
    max( CASE WHEN pm.meta_key = '_billing_email' and p.ID = pm.post_id THEN pm.meta_value END ) as U_Email,
    max( CASE WHEN pm.meta_key = '_order_total' and p.ID = pm.post_id THEN pm.meta_value END ) as U_Payment1,
    max( CASE WHEN pm.meta_key = '_payment_method' and p.ID = pm.post_id THEN pm.meta_value END ) as U_Mode1,
    '' as U_PRef1,
    '' as U_Compute,
    max( CASE WHEN pm.meta_key = '_order_total' and p.ID = pm.post_id THEN pm.meta_value END ) as U_Total,
    '0' as U_Balance,
    DATE_FORMAT(max( CASE WHEN pm.meta_key = '_paid_date' and p.ID = pm.post_id THEN pm.meta_value END ), '%Y%m%d')as U_P_Date1,
    '' as U_Salutation
from
    wp_posts p 
    join wp_postmeta pm on p.ID = pm.post_id
    join wp_woocommerce_order_items oi on p.ID = oi.order_id
    CROSS JOIN (SELECT @cnt := 0) AS dummy
where 
post_type = 'shop_order' and
post_status = 'wc-completed' and 
DATE(post_date) = CURDATE()
group by 
p.id
ORDER BY
DocNum ASC");

    $stmt->execute();

	$filelocation = 'wp-content/uploads/export/';
	$filename     = 'export-'.date('Y-m-d H.i.s').'.csv';
	$file_export  =  $filelocation . $filename;

    $data = fopen($file_export, 'w');

    $csv_fields = array();

	$csv_fields[] = 'DocNum';
	$csv_fields[] = 'DocDate';
	$csv_fields[] = 'DocDueDate';
	$csv_fields[] = 'CardCode';
	$csv_fields[] = 'NumAtCard';
	$csv_fields[] = 'DocTotal';
	$csv_fields[] = 'SlpCode';
	$csv_fields[] = 'U_Name1';
	$csv_fields[] = 'U_Name2';
	$csv_fields[] = 'U_Address1';
	$csv_fields[] = 'U_Address2';
	$csv_fields[] = 'U_Address3';
	$csv_fields[] = 'U_Zipcode';
	$csv_fields[] = 'U_Event';
	$csv_fields[] = 'U_Delivery';
	$csv_fields[] = 'U_Special';
    $csv_fields[] = 'U_Building';
	$csv_fields[] = 'U_Homephone';
	$csv_fields[] = 'U_Mobile1';
    $csv_fields[] = 'U_Mobile2';
    $csv_fields[] = 'U_Office';
	$csv_fields[] = 'U_Email';
	$csv_fields[] = 'U_Payment1';
	$csv_fields[] = 'U_Mode1';
	$csv_fields[] = 'U_PRef1';
	$csv_fields[] = 'U_Compute';
    $csv_fields[] = 'U_Total';
	$csv_fields[] = 'U_Balance';
    $csv_fields[] = 'U_P_Date1';
	$csv_fields[] = 'U_Salutation';
    

	fputcsv($data, $csv_fields);

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($data, $row);
    }

} catch(PDOException $e) {
    echo 'ERROR: ' . $e->getMessage();
}
