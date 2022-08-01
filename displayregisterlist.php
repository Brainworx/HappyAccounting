<?php 

global $wpdb;
if(!class_exists('Register_List_Table')){
	require_once( 'list-table-register.php' );
}
$regtable = new Register_List_Table();
if(isset($_GET["export"])){ 
	if(isset($_GET["month"])) $regtable->getparam['month'] = $_GET["month"];
	else {
		echo "incorrect parameter month";
		exit;
	}
	if(isset($_GET["year"])) $regtable->getparam['year'] = $_GET["year"];
	else {
		echo "incorrect parameter year";
		exit;
	}
	$regtable->prepare_items();
	$regtable->exportCSV();
}
//set params
if(isset($_GET["orderby"])) $regtable->getparam['orderby'] = $_GET["orderby"];
if(isset($_GET["order"])) $regtable->getparam['order'] = $_GET["order"];
if(isset($_GET["paged"])) $regtable->getparam['pages'] = $_GET["pages"];
if(isset($_GET["month"])) $regtable->getparam['month'] = $_GET["month"];
else $regtable->getparam['month']=date('m');
if(isset($_GET["year"])) $regtable->getparam['year'] = $_GET["year"];
else $regtable->getparam['year']=date('Y');
//Fetch, prepare, sort, and filter our data...
$regtable->prepare_items();
?>
<div class="wrap">
        
	<div id="icon-users" class="icon32"><br/></div>
	<h2>Cash in/out voor maand <?php echo($regtable->getparam['month'].'/'.$regtable->getparam['year'])?></h2>
	<!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
	<form id="income" method="get">
		<!-- For plugins, we also need to ensure that the form posts back to our current page -->
		<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
		<!-- Now we can render the completed list table -->
		<?php $regtable->display() ?>
	</form>
</div>
