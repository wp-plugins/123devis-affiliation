<?php
function sm_activation(){
	//first check version
	$php_version = phpversion();
	if (version_compare($php_version, '5.1.0') == -1){
		die("Unfortunately, this plugin is designed to work on php 5.1 or greater. You are currently running $php_version.");
	}

	$admin = & get_role('administrator');
	//WEBSITE-3691 : API Change the publishers' rights
	if (!empty($admin)) {
		$admin->add_cap('sm_api_manage_options');
		$admin->add_cap('sm_api_manage_forms');
	}

	$editor = & get_role('editor');

	if (!empty($editor)) {
		$editor->add_cap('sm_api_manage_forms');
	}


	sm_individual_activation();

    if (function_exists( 'is_network_admin' ) AND is_network_admin() ) {
    	global $wpdb;
		$root_blog = $wpdb->blogid;
    	$ms_blog_ids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");

    	foreach ($ms_blog_ids as $blog_id) {
    		switch_to_blog($blog_id);
        	sm_individual_activation();
    	}
    	//revert to original blog
		switch_to_blog($root_blog);
    }
}

function sm_individual_activation(){
	global $wpdb;
	//install some tables
	//first determine if we need to rename sm_sr_forms - if so do it
	$old_sr_table_name = $wpdb->prefix . "sm_forms";
	$new_sr_table_name = $wpdb->prefix . "sm_sr_forms";

	$find_old_table = $wpdb->query("SELECT 1 FROM Information_schema.tables WHERE table_name = '$old_sr_table_name' AND table_schema = '".DB_NAME."'");
	if (!empty($find_old_table)){
		$wpdb->query("ALTER TABLE $old_sr_table_name RENAME $new_sr_table_name;");
	}

	$sr_sql = "CREATE TABLE IF NOT EXISTS $new_sr_table_name (
		id INT(10) NOT NULL AUTO_INCREMENT,
		activity_id INT(10) NOT NULL,
		activity_title VARCHAR(200) NOT NULL,
		embedable_name VARCHAR(50) NOT NULL,
		name VARCHAR(50) NOT NULL,
		tracking_label VARCHAR(50) NOT NULL,
		parameters VARCHAR(2000) NOT NULL,
		is_archived SMALLINT NOT NULL DEFAULT '0',
		created  DATETIME,
		altered  DATETIME,
		PRIMARY KEY  (id) )  COLLATE='utf8_general_ci',  ENGINE=InnoDB;
	";
	$wpdb->query($sr_sql);

	$sp_table_name = $wpdb->prefix . "sm_sp_forms";
	$sp_sql = "CREATE TABLE IF NOT EXISTS $sp_table_name (
		id INT(10) NOT NULL AUTO_INCREMENT,
		embedable_name VARCHAR(50) NOT NULL,
		name VARCHAR(50) NOT NULL,
		tracking_label VARCHAR(50) NOT NULL,
		parameters VARCHAR(2000) NOT NULL,
		is_archived SMALLINT NOT NULL DEFAULT '0',
		created  DATETIME,
		altered  DATETIME,
		PRIMARY KEY  (id) )  COLLATE='utf8_general_ci',  ENGINE=InnoDB;
	";
	$wpdb->query($sp_sql);

	//check for and remove the kwid field
	foreach(array('new_sr_table_name', 'sp_table_name') as $tbl){
		$find_is_archived_field = $wpdb->get_col($wpdb->prepare("SELECT 1 FROM Information_schema.COLUMNS WHERE TABLE_NAME = %s AND COLUMN_NAME = 'kwid' AND TABLE_SCHEMA= %s", $$tbl, DB_NAME));
		if (!empty($find_is_archived_field)){
			$wpdb->query("ALTER TABLE {$$tbl} DROP COLUMN kwid");
		}
	}

	//check for and add the is_archived field
	foreach(array('new_sr_table_name', 'sp_table_name') as $tbl){
		$find_is_archived_field = $wpdb->get_col($wpdb->prepare("SELECT 1 FROM Information_schema.COLUMNS WHERE TABLE_NAME = %s AND COLUMN_NAME = 'is_archived' AND TABLE_SCHEMA= %s", $$tbl, DB_NAME));
		if (empty($find_is_archived_field)){
			$wpdb->query("ALTER TABLE {$$tbl} ADD COLUMN is_archived SMALLINT NOT NULL DEFAULT '0' AFTER tracking_label");
		}
	}

	$table_name = $wpdb->prefix . "sm_log";
	$sql1 = "CREATE TABLE IF NOT EXISTS $table_name (
		id INT(10) NOT NULL AUTO_INCREMENT,
		path VARCHAR(200) NOT NULL,
		get_json VARCHAR(4000),
		post_json VARCHAR(4000),
		type VARCHAR(50) NOT NULL,
		message VARCHAR(200),
		message_more VARCHAR(4000),
		timest INT(10) NOT NULL,
		user_name VARCHAR(50),
		server_json VARCHAR(4000),
		PRIMARY KEY  (id) )  COLLATE='utf8_general_ci',  ENGINE=InnoDB;
	";
	$wpdb->query($sql1);

	//add to settings
	if (!get_option('sm_api_url')){
		add_option( 'sm_api_url', "https://api.servicemagic.eu" );
		add_option( 'sm_api_server', "fr" );
		add_option( 'sm_default_aff_str', "default");
		add_option( 'sm_default_success_more_text', "");
		add_option( 'sm_deactivate_api_during_slow', 0);
		add_option( "sm_accept_spa", 0);
		add_option( 'sm_clear_all_trace_on_deactivation', 1);
		add_option( 'sm_api_timeout_spans', array(array("0300", "0355")));
		add_option( 'sm_api_cache_mechanism', "ETAG");
	}

	new sm_wp_log("Install");
}

function sm_deactivation(){
	sm_individual_deactivation();

	if (function_exists( 'is_network_admin' ) AND is_network_admin() ) {
		global $wpdb;
		$root_blog = $wpdb->blogid;
    	$ms_blog_ids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
    	foreach ($ms_blog_ids as $blog_id) {
    		switch_to_blog($blog_id);
        	sm_individual_deactivation();
    	}

    	//revert to original blog
		switch_to_blog($root_blog);
    }
}

function sm_individual_deactivation(){
	global $wpdb;

	if (get_option( 'sm_clear_all_trace_on_deactivation', 0)){
		foreach (array("sm_forms", "sm_sr_forms", "sm_sp_forms") AS $tbl){
			$table_name = $wpdb->prefix . $tbl;
			$sql = "DROP TABLE IF EXISTS $table_name";
			$wpdb->query($sql);
		}

		$table_name = $wpdb->prefix . "sm_log";
		$sql = "DROP TABLE IF EXISTS $table_name";
		$wpdb->query($sql);

		delete_option( "sm_creds" );
		delete_option( "sm_api_url" );
		delete_option( "sm_api_server" );
		delete_option( "sm_accept_spa" );
		delete_option( "sm_default_aff_str" );
		delete_option( "sm_default_success_more_text" );
		delete_option( 'sm_deactivate_api_during_slow' );
		delete_option( "sm_clear_all_trace_on_deactivation" );
		delete_option( 'sm_api_timeout_spans' );
		delete_option( 'sm_api_cache_mechanism' );
		delete_option( "sm_display_defaults" );
	}
}

