<?php
if(!class_exists('WP_List_Table')){
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
 
class LeadTable extends WP_List_Table {
    
    function __construct(){
        global $status, $page;
                
        //Set parent defaults
        parent::__construct( array(
            'singular'  => 'movie',     //singular name of the listed records
            'plural'    => 'movies',    //plural name of the listed records
            'ajax'      => false        //does this table support ajax?
        ) );
        
    }
	
	function column_default($item, $column_name){
		switch($column_name){
            case 'agent_id':
                return get_user_meta($item[$column_name], 'first_name', true).' '.get_user_meta($item[$column_name], 'last_name', true);
			case 'created':
                return date('Y/m/d H:i', strtotime($item[$column_name]));
			case 'manual_link':
                return $item['status'] ? '' : '<a href="?action=change_status&id='.$item['id'].'">Show data to agent</a>';
            default:
                return $item[$column_name];//print_r($item,true); //Show the whole array for troubleshooting purposes
        }
    }
    
    function column_title($item){
        
        	
		//Build row actions
        $actions = array(
            //'edit'      => sprintf('<a href="?page=%s&tab=%s&edit=%s">Edit</a>',$_REQUEST['page'],'add_template',$item['id']),
            //'delete'    => sprintf('<a href="?page=%s&tab=%s&delete=%s">Delete</a>',$_REQUEST['page'],$_REQUEST['tab'],$item['id']),
        );
        
        //Return the title contents
        return sprintf('%1$s <span style="color:silver">(id:%2$s)</span>%3$s',
            /*$1%s*/ get_user_meta($item['endorser_id'], 'first_name', true).' '.get_user_meta($item['endorser_id'], 'last_name', true),
            /*$2%s*/ $item['endorser_id'],
            /*$3%s*/ $this->row_actions($actions)
        );
    }
    
    function column_cb($item){
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            /*$1%s*/ $this->_args['singular'],  //Let's simply repurpose the table's singular label ("movie")
            /*$2%s*/ $item['id']                //The value of the checkbox should be the record's id
        );
    }
    
    
    function get_columns(){
        $columns = array(
            'cb'        => '<input type="checkbox" />', //Render a checkbox instead of text
            'first_name'     => 'First name',
            'last_name'    => 'Last name',
			'email'    => 'Email',
			'agent_id'    => 'Agent',
			'blog_id'    => 'Blog id',
			'status'    => 'Status',
			'manual_link' => 'Show data to agent',
			'created' => 'Gift Date'
        );
        return $columns;
    }
    
    function get_sortable_columns() {
        $sortable_columns = array(
            'name'     => array('name',false)
        );
        return $sortable_columns;
    }
    
    function get_bulk_actions() {
        $actions = array(
            'delete'    => 'Delete'
        );
        return $actions;
    }
    
    function process_bulk_action() {
        
        global $wpdb;
		
		if( 'delete'===$this->current_action()) {
		$del_val = $_REQUEST['movie'];
		foreach($del_val as $val) {
			$wpdb->delete($wpdb->prefix . "leads", array( 'id' => $val ) );
		}}
       
    }
    
    function prepare_items() {
        global $wpdb; 
        $per_page = 5;
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        
        $this->_column_headers = array($columns, $hidden, $sortable);
        
        $this->process_bulk_action();
        
		function objectToArray($d) 
		{
		if (is_object($d)) {
			$d = get_object_vars($d);
		}
 
		if (is_array($d)) {
			return array_map(__FUNCTION__, $d);
		}
		else {
			return $d;
		}
		}
		$data = objectToArray($wpdb->get_results("select * from ".$wpdb->prefix . "leads"));
        $newdat = array();
		foreach($data as $v){
			$newdat[] = $v;
		}
		$data = $newdat;
        function usort_reorder($a,$b){
            $orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'created'; //If no sort, default to title
            $order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'desc'; //If no order, default to asc
            $result = strcmp($a[$orderby], $b[$orderby]); //Determine sort order
            return ($order==='asc') ? $result : -$result; //Send final sort direction to usort
        }
        usort($data, 'usort_reorder');
        
        
        $current_page = $this->get_pagenum();
        
        
        $total_items = count($data);
        
        
        $data = array_slice($data,(($current_page-1)*$per_page),$per_page);
        
        $this->items = $data;
        
        $this->set_pagination_args( array(
            'total_items' => $total_items,                  //WE have to calculate the total number of items
            'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
            'total_pages' => ceil($total_items/$per_page)   //WE have to calculate the total number of pages
        ) );
    }
    
}