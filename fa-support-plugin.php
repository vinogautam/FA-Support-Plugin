<?php
/* Plugin Name: FinancialInsiders Support Plugin
 * Author: Vinodhagan Thangarajan
 * Description: Instant Connect plugin for Neil Thomas
 * Version: 1.0
 */
 
 include 'fa-support-listtable.php';
 
 if(!class_exists('Stripe'))
 {
	 require('../paid-memberships-pro/includes/lib/Stripe/Stripe.php');
 }
 
 Class FA_Support
 {
	function FA_Support(){
		
		register_activation_hook(__FILE__, array( &$this, 'FA_install'));
		register_uninstall_hook(__FILE__, array( &$this, 'FA_uninstall'));
		
		if(isset($_POST['appointments-confirmation-button']))
		{
			$this->save_lead();
		}
		if(isset($_GET['action']) && $_GET['action'] == 'update_lead_status')
		{
			$this->update_lead_status();
		}
		
		add_action( 'app_new_appointment', array( &$this, 'save_lead'), 100 );
		add_action( 'wp_footer', array( &$this, 'save_surfing_page'), 100 );
		add_action( 'admin_menu', array( $this, 'add_plugin_pages' ) );
	}
	
	function add_plugin_pages() {
		add_menu_page( 'Leads', 'Leads', 'manage_options', 'lead_table', array( $this, 'lead_table' ));
	}
	
	function save_surfing_page()
	{
		$surfing_page = isset($_COOKIE['fa_surfing_page']) ? explode(",", $_COOKIE['fa_surfing_page']) : array();
		//print_r(!in_array(get_the_title(), $surfing_page));
		$data = 'Blog:'.get_current_blog_id().' Page:'.get_the_title();
		if(!in_array($data, $surfing_page))
			$surfing_page[] = $data;
		//print_r($surfing_page);
		setcookie("fa_surfing_page", implode(",", $surfing_page), time() + (86400 * 365), "/");
		
		if(!isset($_COOKIE['visited_blog_id']) && get_current_blog_id())
			setcookie("visited_blog_id", get_current_blog_id(), time() + (86400 * 365), "/");
	}
	
	function get_visited_blog_id()
	{
		return $_COOKIE['visited_blog_id'];
	}
	
	function update_lead_status()
	{
		global $wpdb;
		
		$lead_id = base64_decode(base64_decode($_GET['id']));
		$wpdb->update($wpdb->prefix . "leads", array('status' => 1), array('id' => $lead_id));
		
		$administrator = get_users( array( 'role' => 'administrator' ) );
		foreach($administrator as $ad)
		{
			$this->send_lead_confirm_notification($ad->user_email, $lead_id);
		}
	}
	
	function save_lead($appointmentID)
	{
		global $wpdb;
		$lead_data = $_POST;
		$lead_data['created'] = date("Y-m-d H:i:s");
		//$lead_data['agent_id'] = '';
		$lead_data['blog_id'] = get_current_blog_id();
		$lead_data['form_url'] = $_SERVER['HTTP_REFERER'];
		$lead_data['visited_page'] = $_COOKIE['fa_surfing_page'];
		$lead_data['appointment_id'] = $appointmentID;
		if(isset($_COOKIE['endorsement_track_link']) && !isset($_COOKIE['endorsement_tracked']))
		{
			$track_link = explode("#&$#", base64_decode(base64_decode($_COOKIE['endorsement_track_link'])));
			if(count($track_link) == 3)
			{
				$get_results = $wpdb->get_row("select * from ".$wpdb->prefix . "endorsements where id=".$track_link[0]);
				$lead_data['endorser_id'] = $get_results->endorser_id;
			}
		}
		
		unset($lead_data['action']);
		unset($lead_data['app_name']);
		unset($lead_data['app_email']);
		unset($lead_data['app_phone']);
		unset($lead_data['app_address']);
		unset($lead_data['app_city']);
		unset($lead_data['app_note']);
		unset($lead_data['app_gcal']);
		unset($lead_data['nonce']);
		print_r($lead_data);
		$wpdb->insert($wpdb->prefix . "leads", $lead_data);
		$lead_id = $wpdb->insert_id;
		//$this->confirmation_mail($lead_id);
	
	}
	
	function confirmation_mail($lead_id)
	{
		$message = 'Thanks for signing up FinancialInsiders. <a href="'.site_url().'?action=update_lead_status&id='.base64_encode(base64_encode($lead_id)).'">Click here to confirm your registration</a>';
		
		NTM_mail_template::send_mail($_POST['email'], 'Registered with FinancialInsiders successfly.', $message);
	}
	
	function send_lead_confirm_notification($email, $lead_id)
	{
		global $wpdb;
		
		$lead = $wpdb->get_row("select * from ".$wpdb->prefix . "leads where id =" . $lead_id);
		
		$message = 'New Lead confirmation notification <br>
					<h2>Lead Detais <h2> <br>
					Lead Name : '.$lead->first_name.' '.$lead->last_name.'<br>
					Lead Name : '.$lead->email;
		
		NTM_mail_template::send_mail($email, 'New Lead confirmation notification.', $message);
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
			   status int(1),
			   address text,
			   visited_page tinytext,
			   form_url tinytext,
			   endorser_id int(11),
			   appointment_id int(11),
			  PRIMARY KEY  (id) ) ENGINE=InnoDB";

			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($sql_one);
		}
	}
	
	function lead_table()
	{
		global $wpdb, $appoinments;
		
		if(isset($_GET['action']) && $_GET['action'] == 'change_status')
		{
			$lead = $wpdb->get_row("select * from ".$wpdb->prefix . "leads where id=".$_GET['id']);
			
			Stripe::setApiKey("sk_test_ptIh1KjZKyzPthKwE4szUeDE");
			Stripe::setAPIVersion("2015-07-13");
			
			$customer_id = get_user_meta($lead->agent_id, "pmpro_stripe_customerid");
			$amount = 100;
			
			$invoice_item = Stripe_InvoiceItem::create( array(
				'customer'    => $customer_id, // the customer to apply the fee to
				'amount'      => $amount, // amount in cents
				'currency'    => 'usd',
				'description' => 'One-time setup fee' // our fee description
			) );
		 
			$invoice = Stripe_Invoice::create( array(
				'customer'    => $customer_id, // the customer to apply the fee to
			) );
		 
			$result = $invoice->pay();
			if(isset($result->object) && $result->object == 'invoice')
			{
				$wpdb->update($wpdb->prefix . "leads", array('status' => 2), array('id' => $_GET['id']));
				$appoinments->appointments_update_appointment_status( $_GET['appointment_id'], 'confirmed' );
			}
			
		}
		
		$lead_table = new LeadTable();
		$lead_table->prepare_items();
		$lead_table->display();
	}
 }
 
 new FA_Support();