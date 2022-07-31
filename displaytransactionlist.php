<?php 

global $wpdb;
if(!class_exists('Transactions_List_Table')){
	require_once( 'list-table-transactions.php' );
}
$trantable = new Transactions_List_Table();
//set params
if(isset($_GET["orderby"])) $trantable->getparam['orderby'] = $_GET["orderby"];
if(isset($_GET["order"])) $trantable->getparam['order'] = $_GET["order"];
if(isset($_GET["paged"])) $trantable->getparam['pages'] = $_GET["pages"];
if(isset($_GET["range"])) $trantable->getparam['range'] = $_GET["range"];
else $trantable->getparam['range']=15;
//Fetch, prepare, sort, and filter our data...
$trantable->prepare_items();
?>
<div class="wrap">
        
	<div id="icon-users" class="icon32"><br/></div>
	<h2>Alle betalingen in afgelopen <?php echo($trantable->getparam['range'])?> dagen</h2>
	<!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
	<form id="transactions" method="get">
		<!-- For plugins, we also need to ensure that the form posts back to our current page -->
		<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
		<!-- Now we can render the completed list table -->
		<?php $trantable->display() ?>
	</form>
</div>
