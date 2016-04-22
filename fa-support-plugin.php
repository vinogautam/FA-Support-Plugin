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
		
		add_action( 'wp_footer', array( &$this, 'save_surfing_page'), 100 );
	}
	
	function save_surfing_page()
	{
		$surfing_page = isset($_COOKIE['fa_surfing_page']) ? explode(",", $_COOKIE['fa_surfing_page']) : array();
		//print_r(!in_array(get_the_title(), $surfing_page));
		if(get_the_title() && !in_array(get_the_title(), $surfing_page))
			$surfing_page[] = get_the_title();
		//print_r($surfing_page);
		setcookie("fa_surfing_page", implode(",", $surfing_page), time() + (86400 * 365), "/");
	}
	
	function save_lead()
	{
		global $wpdb;
		
		$lead_data = $_POST;
		$lead_data['created'] = date("Y-m-d H:i:s");
		//$lead_data['agent_id'] = '';
		$lead_data['blog_id'] = get_current_blog_id();
		$lead_data['form_url'] = $_SERVER['HTTP_REFERER'];
		$lead_data['visited_page'] = $_COOKIE['fa_surfing_page'];
		if(isset($_COOKIE['endorsement_track_link']) && !isset($_COOKIE['endorsement_tracked']))
		{
			$track_link = explode("#&$#", base64_decode(base64_decode($_COOKIE['endorsement_track_link'])));
			if(count($track_link) == 3)
			{
				$get_results = $wpdb->get_row("select * from ".$wpdb->prefix . "endorsements where id=".$track_link[0]);
				$lead_data['endorser_id'] = $get_results->endorser_id;
			}
		}
		
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
			   last_name tinytext,
			   email tinytext NOT NULL,
			   city tinytext,
			   phone tinytext,
			   postal_code tinytext,
			   province tinytext,
			   dob tinytext,
			   gender tinytext,
			   occupation tinytext,
			   income tinytext,
			   retire_age tinytext,
			   marital_status tinytext,
			   retire_income tinytext,
			   retirement_goal tinytext,
			   own_business tinytext,
			   comments text,
			   agent_id text NOT NULL,
			   blog_id text NOT NULL,
			   newsetter boolean,
			  PRIMARY KEY  (id) ) ENGINE=InnoDB";

			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($sql_one);
		}
	}
 }
 
 new FA_Support();