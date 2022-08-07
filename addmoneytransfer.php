<?php 
global $wpdb;
date_default_timezone_set('europe/brussels');
$i_datetime=date('Y-m-d H:i:s', time());
$i_date=date('Y-m-d', time());


// Add record
if(isset($_POST['but_submit'])){

	$name = $_POST['txt_name'];
	$datetime = $_POST['hd_datetime'];
	$date = $_POST['dt_date'];
	$description = $_POST['txt_description'];
	$amount = $_POST['num_amount'];
	$amount *= -1;
	$paymenttype = $_POST['rd_type'];
	if(strcmp($paymenttype,"cash-to-bank")==0){
		$vat = 0;
	}else{
		$vat = 21;
	}
	
	$tablename = $wpdb->prefix."happyaccounting_transaction";

	if($date != '' && $amount != '' & $paymenttype != ''){
        $insert_sql = "INSERT INTO ".$tablename."(datetime,date,name,email,description,cust_id,app_id,amount,vat,paymenttype ) 
        		values('".$datetime."','".$date."','".$name."','".$email."','".$description."','0','0','".$amount."','".$vat."','".$paymenttype."')";

        $wpdb->query($insert_sql);

        echo "<br>Bewaren gelukt<br>";
        include "displaytransactionlist.php";
        die;
	}

}
?>
<h1>Nieuwe storting of uitgave</h1>
<form method='post' action=''>
	<table>
		<tr>
			<td>Datum</td>
			<td><input type='date' name='dt_date' value='<?php echo $i_date; ?>' required></td>
		</tr>
		<tr>
			<td>Omschrijving</td>
			<td><input type='text' name='txt_description' value=''></td>
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
			<td>Type</td>
			<td><input type='radio' id="type1" name='rd_type' value="cash-to-bank" required checked><label for="type1">Storting naar bank</label>
			<br><input type='radio' id="type2" name='rd_type' value="cash-out" required><label for="type2">Aankoop Cash</label>
			<br><input type='radio' id="type3" name='rd_type' value="factuur-out" required><label for="type3">Aankoop op Factuur</label>
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
</form>