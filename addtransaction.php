<?php 
global $wpdb;
$bookingstable = $wpdb->prefix."amelia_customer_bookings";
$appointmenttable = $wpdb->prefix."amelia_appointments";
$customertable = $wpdb->prefix."amelia_users";
date_default_timezone_set('europe/brussels');
$i_datetime=date('Y-m-d H:i:s', time());
$i_date=date('Y-m-d', time());

if(isset($_GET['bid'])){
	$i_bid = $_GET['bid'];
	$query="SELECT B.id, B.bookingStart, A.customerId, 
			CONCAT(C.firstName,' ',C.lastName)as'name', C.email, A.appointmentId
			FROM ".$bookingstable." A join ".$appointmenttable." B on A.appointmentId = B.id
			join ".$customertable." C on A.customerId = C.id where A.id = ".$i_bid;
	$entries = $wpdb->get_results($query);

	if(count($entries)>0){
		$entry = $entries[0];	
		$i_appid = $entry->appointmentId;
		$i_name=$entry->name;
		$i_email=$entry->email;
		$i_custid=$entry->customerId;
		$i_date=date("Y-m-d", strtotime($entry->bookingStart));
	}else{
		$i_date=date("Y-m-d", time());
	}
}

// Add record
if(isset($_POST['but_submit'])){

	$name = $_POST['txt_name'];
	$datetime = $_POST['hd_datetime'];
	$cust_id = $_POST['hd_custid'];
	$app_id = $_POST['hd_appid'];
	$date = $_POST['dt_date'];
	$email = $_POST['txt_email'];
	$description = $_POST['txt_description'];
	$vat = $_POST['num_vat'];
	$amount = $_POST['num_amount'];
	$paymenttype = $_POST['rd_type'];
	
	$tablename = $wpdb->prefix."happyaccounting_transaction";

	if($name != '' && $date != '' && $amount != '' & $paymenttype != ''){
        $insert_sql = "INSERT INTO ".$tablename."(datetime,date,name,email,description,cust_id,app_id,amount,vat,paymenttype ) 
        		values('".$datetime."','".$date."','".$name."','".$email."','".$description."','".$cust_id."','".$app_id."','".$amount."','".$vat."','".$paymenttype."')";
        $wpdb->query($insert_sql);
        echo "<br>Bewaren gelukt<br>";
        include "displaytransactionlist.php";
        die;
	}

}
?>
<h1>Nieuwe betaling</h1>
<form method='post' action=''>
	<table>
		<tr>
			<td>Datum</td>
			<td><input type='date' name='dt_date' value='<?php echo $i_date; ?>' required></td>
		</tr>
		<tr>
			<td>Klant</td>
			<td><input type='text' name='txt_name' value='<?php echo $i_name; ?>' required></td>
		</tr>
		<?php if(isset($i_email)){?>
		<tr>
			<td>Email</td>
			<td><input type='text' name='txt_email' value='<?php echo $i_email; ?>' required></td>
		</tr>
		<?php }else{?>
		<tr>
			<td>Omschrijving</td>
			<td><input type='text' name='txt_description'></td>
		</tr>
		<?php }?>		
		<tr>
			<td>Btw tarief</td>
			<td><input type='number' name='num_vat' min="0" value="21"></td>
		</tr>
		<tr>
			<td>Bedrag</td>
			<td><input type='number' name='num_amount' min="0" value="0" step="0.01" pattern="[0-9]*" onblur="this.parentNode.parentNode.style.backgroundColor=/^\d+(?:\.\d{1,2})?$/.test(this.value)?'inherit':'red'" required></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td>Betaalwijze</td>
			<td><input type='radio' id="type1" name='rd_type' value="cash" required checked><label for="type1">Cash</label>
			<input type='radio' id="type2" name='rd_type' value="app" required><label for="type2">App</label>
			<input type='radio' id="type3" name='rd_type' value="factuur" required><label for="type3">Factuur</label>
			<input type='radio' id="type4" name='rd_type' value="bon" required><label for="type4">Bon</label></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td><input type='submit' name='but_submit' value='Bewaar'></td>
		</tr>
	</table> 	
	<input type='hidden' name='hd_datetime' value='<?php echo $i_datetime; ?>'>
	<?php if(isset($_GET['bid'])){?>
	<input type='hidden' name='hd_custid' value='<?php echo $i_custid; ?>'>	
	<input type='hidden' name='hd_appid' value='<?php echo $i_appid; ?>'>
	<?php }?>
</form>