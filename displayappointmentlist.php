<?php 

global $wpdb;

if(!class_exists('Appointments_List_Table')){
	require_once( 'list-table-appointments.php' );
}
?>
<h1>Alle onbetaalde afspraken</h1>
<?php 
 //Create an instance of our package class...
 
$apptable = new Appointments_List_Table();
//set params
if(isset($_GET["orderby"])) $apptable->getparam[] = $_GET["orderby"];
if(isset($_GET["order"])) $apptable->getparam[] = $_GET["order"];
if(isset($_GET["paged"])) $apptable->getparam[] = $_GET["pages"];
//Fetch, prepare, sort, and filter our data...
$apptable->prepare_items();
  
?>
<div class="wrap">
        
	<div id="icon-users" class="icon32"><br/></div>
	<h2>Te betalen afspraken</h2>
	<!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
	<form id="appointments" method="get">
		<!-- For plugins, we also need to ensure that the form posts back to our current page -->
		<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
		<!-- Now we can render the completed list table -->
		<?php $apptable->display() ?>
	</form>
</div>

