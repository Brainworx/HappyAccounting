<?php
/*************************** LOAD THE BASE CLASS *******************************
 *******************************************************************************
 * The WP_List_Table class isn't automatically available to plugins, as it is not
 * public, we copy it here.
 */
if(!class_exists('WP_List_Table')){
    require_once( 'includes/class-wp-list-table.php' );
}




/************************** CREATE A PACKAGE CLASS *****************************
 *******************************************************************************
 * Create a new list table package that extends the core WP_List_Table class.
 * WP_List_Table contains most of the framework for generating the table, but we
 * need to define and override some methods so that our data can be displayed
 * exactly the way we need it to be.
 * 
 * To display this example on a page, you will first need to instantiate the class,
 * then call $yourInstance->prepare_items() to handle any data manipulation, then
 * finally call $yourInstance->display() to render the table to the page.
 * 
 * Our theme for this list table is going to be movies.
 */
class Register_List_Table extends WP_List_Table {
	public $getparam = [];
	private $totalin=0,$totalout=0, $count=0;
    
    /** ************************************************************************
     * REQUIRED. Set up a constructor that references the parent constructor. We 
     * use the parent reference to set some default configs.
     ***************************************************************************/
    function __construct(){
        global $status, $page;
                
        //Set parent defaults
        parent::__construct( array(
            'singular'  => 'record',     //singular name of the listed records
            'plural'    => 'records',    //plural name of the listed records
            'ajax'      => false        //does this table support ajax?
        ) );
    }
    /**
     * Add extra markup in the toolbars before or after the list
     * @param string $which, helps you decide if you add the markup after (bottom) or before (top) the list
     */
    function extra_tablenav( $which ) {

    	$currentmonth=date('m');
    	$currentyear=date('Y');
    	
    	if ( $which == "top" ){
    		//The code that goes before the table is here    	
    		$y = 0;
    		if(isset($this->getparam['year']))
    			$y = $this->getparam['year'];
    		$m = 1;

    		if(isset($this->getparam['month'])){
    			$m = $this->getparam['month'];
    			$m -= 1;
    			if($m == 0){
    				$m = 12;
    				$y -= 1;
    			}
    		}
    		//The code that goes before the table is here
    		echo sprintf('<a href="?page=%s&month=%s&year=%s">Vorige maand</a>','allregister',$m,$y);
    		$ny = $y;
    		$nm = $m + 2;
    		if($nm > 12){
    			$nm -= 12;
    			$ny +=1;
    		}
    		if(!(!isset($this->getparam['year'])||($this->getparam['year'] == $currentyear && $this->getparam['month'] == $currentmonth))){
    			echo sprintf(' - <a href="?page=%s&month=%s&year=%s">Volgende maand</a>','allregister',$nm,$ny);
    		}	
    	}
    	if ( $which == "bottom" ){
    		//The code that goes after the table is there
    		//echo"Hi, I'm after the table";
    		if((!isset($this->getparam['year'])||($this->getparam['year'] == $currentyear && $this->getparam['month'] == $currentmonth))){
	    		$amounts2 = $this->fetchTotal('"'.date('Y-m-01').'"');   		
	    		$amounts = $this->fetchTotal(); 
    		}else{
    			$dt = date('"'.$this->getparam['year'].'-'.$this->getparam['month'].'-01"');
    			$amounts2 = $this->fetchTotal($dt);
    			$amounts = $this->fetchTotal('DATE_ADD('.$dt.', INTERVAL 1 MONTH)');
    		}	
    			
    		echo "Totaal in deze periode: ".$this->count." dagen - ".$this->totalin." EUR IN - ".$this->totalout." EUR UIT<br>";
    		if(isset($amounts2[0]->balance))
    			echo "<br>Eindsaldo vorige maand: ".$amounts2[0]->balance." EUR";
    		else 
    			echo "<br>Eindsaldo vorige maand: 0 EUR";
    		echo "<br>Saldo deze maand: ".($this->totalin - $this->totalout)." EUR";
    		if(isset($amounts[0]->balance))
    			echo "<br><b>Bedrag in kassa: ".$amounts[0]->balance." EUR</b><br>";
    		else 
    			echo "<br><b>Bedrag in kassa: 0 EUR</b><br>";
    		
    		//note the noheader param to avoid error "header already sent"
    		echo sprintf('<br><a href="?page=%s&year=%s&month=%s&export=%s&noheader=1">Download gegevens</a><br>','allregister',$this->getparam['year'],$this->getparam['month'],'csv');
    		
    		
    	}
    }
    /**
     * Define the columns that are going to be used in the table
     * @return array $columns, the array of columns to use with the table
     */
    function get_columns() {
    	return $columns= array(
    			'id'=>__('Id'),
    			'date'=>__('Datum'),
    			'amountin'=>__('Bedrag IN'),
    			'amountout'=>__('Bedrag UIT')
    	);
    }
    /** ************************************************************************
     * Optional. If you want one or more columns to be sortable (ASC/DESC toggle),
     * you will need to register it here. This should return an array where the
     * key is the column that needs to be sortable, and the value is db column to
     * sort by. Often, the key and value will be the same, but this is not always
     * the case (as the value is a column name from the database, not the list table).
     *
     * This method merely defines which columns should be sortable and makes them
     * clickable - it does not handle the actual sorting. You still need to detect
     * the ORDERBY and ORDER querystring variables within prepare_items() and sort
     * your data accordingly (usually by modifying your query).
     *
     * @return array An associative array containing all the columns that should be sortable: 'slugs'=>array('data_values',bool)
     **************************************************************************/
    function get_sortable_columns() {
    	$sortable_columns = array(
    	);
    	return $sortable_columns;
    }


    /** ************************************************************************
     * Recommended. This method is called when the parent class can't find a method
     * specifically build for a given column. Generally, it's recommended to include
     * one method for each column you want to render, keeping your package class
     * neat and organized. For example, if the class needs to process a column
     * named 'title', it would first see if a method named $this->column_title() 
     * exists - if it does, that method will be used. If it doesn't, this one will
     * be used. Generally, you should try to use custom column methods as much as 
     * possible. 
     * 
     * Since we have defined a column_title() method later on, this method doesn't
     * need to concern itself with any column with a name of 'title'. Instead, it
     * needs to handle everything else.
     * 
     * For more detailed insight into how columns are handled, take a look at 
     * WP_List_Table::single_row_columns()
     * 
     * @param array $item A singular item (one full row's worth of data)
     * @param array $column_name The name/slug of the column to be processed
     * @return string Text or HTML to be placed inside the column <td>
     **************************************************************************/
    function column_default($item, $column_name){
        return print_r($item,true); //Show the whole array for troubleshooting purposes
    }

    /**
     * Prepare the table with different parameters, pagination, columns and table elements
     */
    function prepare_items() {
    	global $wpdb, $_wp_column_headers;
    	$screen = get_current_screen();
   
    	$tablename = $wpdb->prefix."happyaccounting_register";
    	
    	if(isset($this->getparam['month'])){
    		$month = $this->getparam['month'];
    	}else{
    		$month=date('m');
    	}
    	
    	if(isset($this->getparam['year'])){
    		$year = $this->getparam['year'];
    	}else{
    		$year=date('Y');
    	}
    	/* -- Preparing your query -- */
    	$query = ("SELECT * FROM ".$tablename." where date >= DATE('".$year."-".$month."-01') and date <= LAST_DAY(DATE('".$year."-".$month."-01')) ");

    	/* -- Ordering parameters -- */
    	//Parameters that are going to be used to order the result
    	$orderby = !empty($this->getparam["orderby"]) ? mysql_real_escape_string($this->getparam["orderby"]) : 'date';
    	$order = !empty($this->getparam["order"]) ? mysql_real_escape_string($this->getparam["order"]) : 'asc';
    	if(!empty($orderby) & !empty($order)){ $query.=' ORDER BY '.$orderby.' '.$order; }
 
    	/* -- Pagination parameters -- */
    	//Number of elements in your table?
    	$totalitems = $wpdb->query($query); //return the total number of affected rows
    	//How many to display per page?
    	$per_page = 10;
    	//Which page is this?
    	$paged = !empty($this->getparam["paged"]) ? mysql_real_escape_string($this->getparam["paged"]) : '1';
    	//Page Number
    	if(empty($paged) || !is_numeric($paged) || $paged<=0 ){ $paged=1; } //How many pages do we have in total? $totalpages = ceil($totalitems/$perpage); //adjust the query to take pagination into account if(!empty($paged) && !empty($perpage)){ $offset=($paged-1)*$perpage; $query.=' LIMIT '.(int)$offset.','.(int)$perpage; } /* -- Register the pagination -- */ 
    	$this->set_pagination_args( array(
	    	"total_items" => $totalitems,
	    	"total_pages" => $totalpages,
	    	"per_page" => $per_page,
    	) );
    	//The pagination links are automatically built according to those parameters
    
    	/* -- Register the Columns -- */
    	$columns = $this->get_columns();
    	$hidden = array();
        $sortable = $this->get_sortable_columns();
        
        /**
         * REQUIRED. Finally, we build an array to be used by the class for column 
         * headers. The $this->_column_headers property takes an array which contains
         * 3 other arrays. One for all columns, one for hidden columns, and one
         * for sortable columns.
         */
        $this->_column_headers = array($columns, $hidden, $sortable);
        
        $data = $wpdb->get_results($query);
        /**
         * REQUIRED for pagination. Let's figure out what page the user is currently
         * looking at. We'll need this later, so you should always include it in
         * your own package classes.
         */
        $current_page = $this->get_pagenum();
        
        /**
         * REQUIRED for pagination. Let's check how many items are in our data array.
         * In real-world use, this would be the total number of items in your database,
         * without filtering. We'll need this later, so you should always include it
         * in your own package classes.
        */
        $total_items = count($data);
        
        
        /**
         * The WP_List_Table class does not handle pagination for us, so we need
         * to ensure that the data is trimmed to only the current page. We can use
         * array_slice() to
        */
        $data = array_slice($data,(($current_page-1)*$per_page),$per_page);
    
    	/* -- Fetch the items -- */
    	$this->items = $data;
    }
    
    /**
     * Display the rows of records in the table
     * @return string, echo the markup of the rows
     */
    function display_rows() {
    
    	//Get the records registered in the prepare_items method
    	$records = $this->items;
    
    	//Get the columns registered in the get_columns and get_sortable_columns methods
    	list( $columns, $hidden ) = $this->get_column_info();
    
    	//Loop for each record
    	$counter = 1;
    	if(!empty($records)){foreach($records as $rec){
    
    		//Open the line
    		echo '<tr id="record_'.$rec->id.'">';
    		foreach ( $columns as $column_name => $column_display_name ) {
    			
    			//Style attributes for each col
    			$class = "class='$column_name column-$column_name'";
    			$style = "";
    			if ( in_array( $column_name, $hidden ) ) $style = ' style="display:none;"';
    			$attributes = $class . $style;
    			if($column_display_name=="Id")
    				echo '<td '.$attributes.'>'.$counter.'</td>';
    			else
    				echo '<td '.$attributes.'>'.$rec->$column_name.'</td>';    
    			switch ($column_name){
    				case "amountin": {
    					$this->totalin += $rec->$column_name;
    					$this->count +=1;
    					break;
    				}
    				case "amountout": {
    					$this->totalout += $rec->$column_name;
    					break;
    				}
    			}			
    		}
    		$counter++;
    		//Close the line    		
    		echo'</tr>';
    	}}
    }
    /**
     * Displays the table.
     *
     * @since 3.1.0
     */
    public function display() {
    	$singular = $this->_args['singular'];
    
    	$this->display_tablenav( 'top' );
    
    	$this->screen->render_screen_reader_content( 'heading_list' );
    	?>
    	<table class="wp-list-table <?php echo implode( ' ', $this->get_table_classes() ); ?>">
    	<thead>
    	<tr>
    		<?php $this->print_column_headers(); ?>
    	</tr>
    	</thead>
    
    	<tbody id="the-list"
    		<?php
    		if ( $singular ) {
    			echo " data-wp-lists='list:$singular'";
    		}
    		?>
    		>
    		<?php $this->display_rows_or_placeholder(); ?>
    	</tbody>    
    </table>
    		<?php
    		$this->display_tablenav( 'bottom' );
    	}
        

    /** ************************************************************************
     * Recommended. This is a custom column method and is responsible for what
     * is rendered in any column with a name/slug of 'title'. Every time the class
     * needs to render a column, it first looks for a method named 
     * column_{$column_title} - if it exists, that method is run. If it doesn't
     * exist, column_default() is called instead.
     * 
     * This example also illustrates how to implement rollover actions. Actions
     * should be an associative array formatted as 'slug'=>'link html' - and you
     * will need to generate the URLs yourself. You could even ensure the links
     * 
     * 
     * @see WP_List_Table::::single_row_columns()
     * @param array $item A singular item (one full row's worth of data)
     * @return string Text to be placed inside the column <td> (movie title only)
     **************************************************************************/
    function column_title($item){
        
        //Return the title contents
        return sprintf('<span style="color:silver">(id:%1$s)</span>%2$s',
            /*$1%s*/ $item['ID'],
            /*$2%s*/ $this->row_actions($actions)
        );
    }    
    /*
     * returns result [totalin, totalout, balance]
     */
    function fetchTotal($maxdate = null){
    	global $wpdb;
    	$tablename = $wpdb->prefix."happyaccounting_register";
    	/* -- Preparing your query -- */
    	$query = ("SELECT sum(amountin) as totalin, sum(amountout) as totalout, sum(amountin)-sum(amountout) as balance FROM ".$tablename);
    	if(isset($maxdate)){
    		$query = $query . ' where date < '.$maxdate;
    	}
    	$result = $wpdb->get_results($query);
    	return $result;
    }
    function exportCSV(){

    	//Get the columns registered in the get_columns
    	list( $columns ) = $this->get_column_info();
    	$delimiter = ";";
    	date_default_timezone_set('europe/brussels');
    	$i_datetime=date('Y-m-d H:i:s', time());
    
    	$filename = "kasboek".$this->getparam['year'].$this->getparam['month']."_" . $i_datetime. ".csv";
    	$fields = [];
    	foreach ( $columns as $column_key => $column_display_name ) {
    		$fields[]=$column_display_name;
    	}
    	// Set headers to download file rather than displayed
    	header('Content-Type: text/csv');
    	header('Content-Disposition: attachment; filename="' . $filename . '";');
    	
    	// Create a file pointer
    	$f = fopen('php://output', 'w');
    	 
    	fputcsv($f, $fields, $delimiter);
    	$period = "";
    	if((!isset($this->getparam['year'])||($this->getparam['year'] == $currentyear && $this->getparam['month'] == $currentmonth))){
    		$amounts2 = $this->fetchTotal('"'.date('Y-m-01').'"');
    		$amounts = $this->fetchTotal();
    		$period = date('Y-m');
    	}else{
    		$dt = date('"'.$this->getparam['year'].'-'.$this->getparam['month'].'-01"');
    		$amounts2 = $this->fetchTotal($dt);
    		$amounts = $this->fetchTotal('DATE_ADD('.$dt.', INTERVAL 1 MONTH)');
    		$period = $this->getparam['year'].'-'.$this->getparam['month'];
    	}
    	     	
    	$this->totalin=0;
    	$this->totalout=0;
    	
    	$queryresult = $this->items;
    	if(!empty($queryresult)){

    		$counter = 1;
    		
    		// Output each row of the data, format line as csv and write to file pointer
    		foreach ($queryresult as $row){
    			$lineData = [];
    			foreach ( $columns as $column_name => $column_display_name){
    				if($column_display_name=="Id")
    					$lineData[]=$counter;
    				else{
    					if(is_numeric($row->$column_name))
    						$lineData[]=number_format($row->$column_name,2,',','');
    					else
    						$lineData[]=$row->$column_name;
    				}
    			switch ($column_name){
    				case "amountin": {
    					$this->totalin += $row->$column_name;
    					$this->count +=1;
    					break;
    				}
    				case "amountout": {
    					$this->totalout += $row->$column_name;
    					break;
    				}
    			}		
    			}
    			fputcsv($f, $lineData, $delimiter);

    			$counter++;
    		}
    		$lineData = [];
    		foreach ( $columns as $column_name => $column_display_name){
    			switch ($column_name){
    				case "amountin": {$lineData[]=$this->totalin; break;}
    				case "amountout": {$lineData[]=$this->totalout; break;}
    				default :  {$lineData[]='';}
    			}
    		}
    		fputcsv($f, $lineData, $delimiter);
    		     		
    	}
    	else{
    		$lineData = [];
    		$lineData[] = "Geen nieuwe data voor ". $period;
    		fputcsv($f, $lineData, $delimiter);
    	}
    	
    	$lineData = [];
    	fputcsv($f, $lineData, $delimiter);
    	
    	$lineData = [];
    	
    	$lineData[]="";
    	$lineData[]="Eindsaldo vorige maand";
    	if(isset($amounts2[0]))
    		$lineData[]=number_format($amounts2[0]->balance,2,',','');
    	else
    		$lineData[]=0;
    	fputcsv($f, $lineData, $delimiter);
    	$lineData = [];
    	
    	$lineData[]="";
    	$lineData[]="Saldo deze maand";
    	$lineData[]=number_format($this->totalin - $this->totalout,2,',','');
    	fputcsv($f, $lineData, $delimiter);
    	$lineData = [];
    	
    	$lineData[]="";
    	$lineData[]="Eindtotaal in kassa";
    	$lineData[]=number_format($amounts[0]->balance,2,',','');
    	fputcsv($f, $lineData, $delimiter);
    	fclose($f);    	 
    	die;
    }
}
