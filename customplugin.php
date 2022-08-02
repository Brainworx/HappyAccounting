<?php
 /*
   Plugin Name: Happy Accounting plugin
   Plugin URI: https://www.brainworx.be
   description: A simple custom plugin for managing basic register accounting
   Version: 1.0.0
   Author: Stijn Heylen
   Author URI: https://www.brainworx.be
 */

// Create a new table
// logging auto inserted, transaction date inserted via query (INSERT INTO example (col_name, col_date) VALUE ('YEAR: Auto CURDATE()', CURDATE() )";)
function customplugin_table(){
	global $wpdb;

	//raw transactions = payment for work
	$charset_collate = $wpdb->get_charset_collate();
	$transactiontable = $wpdb->prefix."happyaccounting_transaction";
	$queries = [];
	$sql = "CREATE TABLE IF NOT EXISTS $transactiontable (
		id mediumint(11) NOT NULL AUTO_INCREMENT,
		logtime DATETIME DEFAULT CURRENT_TIMESTAMP, 
		datetime DATETIME NOT NULL,
		date DATE NOT NULL,
		name varchar(80) NOT NULL,
		email varchar(80) NOT NULL,
		description varchar(300) NOT NULL default 'voetverzorging',
		cust_id mediumint(11),
		app_id mediumint(11),
		amount decimal(13,2),
		vat tinyint default 21,
		paymenttype varchar(20)NOT NULL default 'cash',
		PRIMARY KEY  (id)
	) $charset_collate;";
	$queries[] =$sql;
	
	//ontvangsten - per day
	$incometable = $wpdb->prefix."happyaccounting_income";
	$sql = "CREATE TABLE IF NOT EXISTS $incometable (
		id mediumint(11) NOT NULL AUTO_INCREMENT,
		logtime DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, 
		date DATE NOT NULL,
		quarter varchar(20),
		vat tinyint default 21,
		amount decimal(13,2),		
		vatamount decimal(13,2),
		netamount decimal (13,2),
		PRIMARY KEY  (id)
	) $charset_collate;";
	$queries[] =$sql;
	
	//kasboek - cash per day
	$registertable = $wpdb->prefix."happyaccounting_register";
	$sql = "CREATE TABLE IF NOT EXISTS $registertable (
		id mediumint(11) NOT NULL AUTO_INCREMENT,
		logtime DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, 
		date DATE NOT NULL,
		amountin decimal(13,2) default 0,
		amountout decimal(13,2) default 0,
		PRIMARY KEY  (id)
	) $charset_collate;";
	$queries[] =$sql;
	
	$sql = "DELIMITER //
			CREATE TRIGGER IF NOT EXISTS afterinsertTransaction
			AFTER INSERT
			ON $transactiontable FOR EACH ROW
			BEGIN
			DECLARE var_Vat decimal(13,2);
			DECLARE var_Net decimal(13,2);
			IF (NEW.paymenttype = 'cash' OR NEW.paymenttype = 'app') THEN	
				SET var_Net = NEW.amount/(1+(NEW.vat/100));
			    SET var_Vat = NEW.amount-var_Net;
				IF EXISTS (SELECT * FROM $incometable where date = NEW.date and vat=NEW.vat) THEN
					UPDATE $incometable 
							SET vat = NEW.vat, amount = amount + NEW.amount,
								vatamount = vatamount + var_Vat, netamount = netamount + var_Net
								WHERE date = NEW.date;
				ELSE
					INSERT INTO $incometable(date,quarter,vat,amount,vatamount,netamount) 
								values(NEW.date,concat(year(NEW.date),
								CASE WHEN month(NEW.date)<4 THEN 1
								WHEN month(NEW.date) < 7 THEN 2
								WHEN month(NEW.date) < 10 THEN 3
								ELSE 4 END),NEW.vat,NEW.amount,var_Vat,var_Net);
				END IF;
				IF (NEW.paymenttype = 'cash') THEN
			        IF EXISTS (SELECT * FROM $registertable where date = NEW.date) THEN
			            UPDATE $registertable 
			                    SET amountin = amountin + NEW.amount WHERE date = NEW.date;
			        ELSE
			            INSERT INTO wp_happyaccounting_register(date,amountin) 
			                    values(NEW.date,NEW.amount);
			        END IF;
				END IF;
			END IF;
			IF (NEW.paymenttype = 'cash-out' OR new.paymenttype = 'cash-to-bank') THEN
		        IF EXISTS (SELECT * FROM $registertable where date = NEW.date) THEN
		            UPDATE $registertable 
		                    SET amountout = amountout + (NEW.amount*-1) WHERE date = NEW.date;
		        ELSE
		            INSERT INTO wp_happyaccounting_register(date,amountout) 
		                    values(NEW.date,(NEW.amount*-1));
		        END IF;
			END IF;
			END
			//";
	$queries[] =$sql;
	
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $queries );
}
register_activation_hook( __FILE__, 'customplugin_table' );

// Add menu for user with right amelia_write_appointments
function customplugin_menu() {
	
    add_menu_page("Boekhouding", "Kassa","amelia_write_appointments", "accountingplugin", "displayWelcome",plugins_url('/HappyAccounting/img/icon.png'));
    add_submenu_page("accountingplugin","Afspraak betalen", "Afspraak betalen","amelia_write_appointments", "allappointments", "displayAppointmentsList");
    add_submenu_page("accountingplugin","Losse aankoop betalen", "Losse aankoop betalen","amelia_write_appointments", "addtransaction", "displayAddTransaction");
    add_submenu_page("accountingplugin","Storting", "Storting of uitgave","amelia_write_appointments", "addmoneyTransfer", "displayAddMoneyTransfer");
    add_submenu_page("accountingplugin","Alle betalingen", "Alle betalingen","amelia_write_appointments", "alltransactions", "displayTransactionList");
    add_submenu_page("accountingplugin","Ontvangensten", "Ontvangsten","amelia_write_appointments", "allincome", "displayIncomeList");
    add_submenu_page("accountingplugin","Kasboek", "Kasboek","amelia_write_appointments", "allregister", "displayRegisterList");
    
}

add_action("admin_menu", "customplugin_menu");

function displayWelcome(){
	include "displaywelcome.php";
}
function displayTransactionList(){
	include "displaytransactionlist.php";
}
//betalingen detail
function displayAddTransaction(){
	include "addtransaction.php";
}
//afspraken
function displayAppointmentsList(){
	include "displayappointmentlist.php";
}
//inkomsten - ontvangstenboek per dag
function displayIncomeList(){
	include "displayincomelist.php";
}
//kasboek - in/out cash per dag
function displayRegisterList(){
	include "displayregisterlist.php";
}
function displayAddMoneyTransfer(){
	include "addmoneytransfer.php";
}
