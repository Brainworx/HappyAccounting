<?php 

global $wpdb;
if(!class_exists('Income_List_Table')){
	require_once( 'list-table-income.php' );
}
$inctable = new Income_List_Table();
if(isset($_GET["export"])){
	if(isset($_GET["quarter"])) $inctable->getparam['quarter'] = $_GET["quarter"];
	elseif (isset($_GET["month"])) $inctable->getparam['month'] = $_GET["month"];
	else {
		echo "incorrect parameter quarter or month";
		exit;
	}
	if(isset($_GET["year"])) $inctable->getparam['year'] = $_GET["year"];
	else {
		echo "incorrect parameter year";
		exit;
	}
	
	$inctable->prepare_items();
	$inctable->exportCSV();
}
//set params
if(isset($_GET["orderby"])) $inctable->getparam['orderby'] = $_GET["orderby"];
if(isset($_GET["order"])) $inctable->getparam['order'] = $_GET["order"];
if(isset($_GET["paged"])) $inctable->getparam['pages'] = $_GET["pages"];
if(isset($_GET["quarter"])) $inctable->getparam['quarter'] = $_GET["quarter"];
if(isset($_GET["month"])) $inctable->getparam['month'] = $_GET["month"];
else $inctable->getparam['month']=date('m');
if(isset($_GET["year"])) $inctable->getparam['year'] = $_GET["year"];
else $inctable->getparam['year']=date('Y');
//Fetch, prepare, sort, and filter our data...
$inctable->prepare_items();
?>
<div class="wrap">
        
	<div id="icon-users" class="icon32"><br/></div>
	<?php if(isset($inctable->getparam['quarter'])){?>
	<h2>Inkomsten voor kwartaal <?php echo($inctable->getparam['quarter'].'/'.$inctable->getparam['year'])?></h2>
	<?php }else{?>
	<h2>Inkomsten voor maand <?php echo($inctable->getparam['month'].'/'.$inctable->getparam['year'])?></h2>
	<?php }?>
	<!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
	<form id="income" method="get">
		<!-- For plugins, we also need to ensure that the form posts back to our current page -->
		<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
		<!-- Now we can render the completed list table -->
		<?php $inctable->display() ?>
	</form>
</div>
