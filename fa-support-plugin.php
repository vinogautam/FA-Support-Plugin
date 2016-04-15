<?php
/* Plugin Name: FinancialInsiders Support Plugin
 * Author: Vinodhagan Thangarajan
 * Description: Instant Connect plugin for Neil Thomas
 * Version: 1.0
 */
 
 Class FA_Support
 {
	function FA_Support(){
		
		register_activation_hook(__FILE__, array( &$this, 'FA_install'));
		register_uninstall_hook(__FILE__, array( &$this, 'FA_uninstall'));
		
		if(isset($_POST['appointments-confirmation-button']))
		{
			$this->save_lead();
		}
	}
	
	function save_lead()
	{
		global $wpdb;
		
		$lead_data = $_POST;
		$lead_data['created'] = date("Y-m-d H:i:s");
		//$lead_data['agent_id'] = '';
		$lead_data['blog_id'] = get_current_blog_id();
		
		$wpdb->insert($wpdb->prefix . "leads", $lead_data);
		$lead_id = $wpdb->insert_id;
	}
	
	function FA_install()
	{
		global $wpdb;
		
		$mailtemplates = $wpdb->prefix . "leads";
		
		if($wpdb->get_var('SHOW TABLES LIKE ' . $mailtemplates) != $mailtemplates){
			$sql_one = "CREATE TABLE " . $mailtemplates . "(
			  id int(11) NOT NULL AUTO_INCREMENT,
			   created datetime NOT NULL,
			   first_name tinytext NOT NULL,
			   last_name tinytext NOT NULL,
			   email tinytext NOT NULL,
			   city tinytext NOT NULL,
			   phone tinytext NOT NULL,
			   postal_code tinytext NOT NULL,
			   province tinytext NOT NULL,
			   dob tinytext NOT NULL,
			   gender tinytext NOT NULL,
			   occupation tinytext NOT NULL,
			   income tinytext NOT NULL,
			   retire_age tinytext NOT NULL,
			   marital_status tinytext NOT NULL,
			   retire_income tinytext NOT NULL,
			   retirement_goal tinytext NOT NULL,
			   own_business tinytext NOT NULL,
			   comments text NOT NULL,
			   agent_id text NOT NULL,
			   blog_id text NOT NULL,
			  PRIMARY KEY  (id) ) ENGINE=InnoDB";

			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($sql_one);
		}
	}
 }
 
 new FA_Support();