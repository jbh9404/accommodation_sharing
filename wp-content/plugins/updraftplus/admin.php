<?php

if (!defined('UPDRAFTPLUS_DIR')) die('No direct access allowed');

// Admin-area code lives here. This gets called in admin_menu, earlier than admin_init

global $updraftplus_admin;
if (!is_a($updraftplus_admin, 'UpdraftPlus_Admin')) $updraftplus_admin = new UpdraftPlus_Admin();

class UpdraftPlus_Admin {

	public $logged = array();

	private $template_directories;

	private $backups_instance_ids;

	private $auth_instance_ids = array('dropbox' => array(), 'onedrive' => array(), 'googledrive' => array(), 'googlecloud' => array());

	private $php_versions = array('5.4', '5.5', '5.6', '7.0', '7.1', '7.2', '7.3');

	private $wp_versions = array('3.2', '3.3', '3.4', '3.5', '3.6', '3.7', '3.8', '3.9', '4.0', '4.1', '4.2', '4.3', '4.4', '4.5', '4.6', '4.7', '4.8', '4.9', '5.0');
	
	private $regions = array('London', 'New York', 'San Francisco', 'Amsterdam', 'Singapore', 'Frankfurt', 'Toronto', 'Bangalore');

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->admin_init();
	}
	
	/**
	 * Get the path to the UI templates directory
	 *
	 * @return String - a filesystem directory path
	 */
	public function get_templates_dir() {
		return apply_filters('updraftplus_templates_dir', UpdraftPlus_Manipulation_Functions::wp_normalize_path(UPDRAFTPLUS_DIR.'/templates'));
	}
	
	private function register_template_directories() {

		$template_directories = array();

		$templates_dir = $this->get_templates_dir();

		if ($dh = opendir($templates_dir)) {
			while (($file = readdir($dh)) !== false) {
				if ('.' == $file || '..' == $file) continue;
				if (is_dir($templates_dir.'/'.$file)) {
					$template_directories[$file] = $templates_dir.'/'.$file;
				}
			}
			closedir($dh);
		}

		// This is the optimal hook for most extensions to hook into
		$this->template_directories = apply_filters('updraftplus_template_directories', $template_directories);

	}

	/**
	 * Output, or return, the results of running a template (from the 'templates' directory, unless a filter over-rides it). Templates are run with $updraftplus, $updraftplus_admin and $wpdb set.
	 *
	 * @param String  $path					  - path to the template
	 * @param Boolean $return_instead_of_echo - by default, the template is echo-ed; set this to instead return it
	 * @param Array	  $extract_these		  - variables to inject into the template's run context
	 *
	 * @return Void|String
	 */
	public function include_template($path, $return_instead_of_echo = false, $extract_these = array()) {
		if ($return_instead_of_echo) ob_start();

		if (preg_match('#^([^/]+)/(.*)$#', $path, $matches)) {
			$prefix = $matches[1];
			$suffix = $matches[2];
			if (isset($this->template_directories[$prefix])) {
				$template_file = $this->template_directories[$prefix].'/'.$suffix;
			}
		}

		if (!isset($template_file)) $template_file = UPDRAFTPLUS_DIR.'/templates/'.$path;

		$template_file = apply_filters('updraftplus_template', $template_file, $path);

		do_action('updraftplus_before_template', $path, $template_file, $return_instead_of_echo, $extract_these);

		if (!file_exists($template_file)) {
			error_log("UpdraftPlus: template not found: $template_file");
			echo __('Error:', 'updraftplus').' '.__('template not found', 'updraftplus')." ($path)";
		} else {
			extract($extract_these);
			global $updraftplus, $wpdb;
			$updraftplus_admin = $this;
			include $template_file;
		}

		do_action('updraftplus_after_template', $path, $template_file, $return_instead_of_echo, $extract_these);

		if ($return_instead_of_echo) return ob_get_clean();
	}
	
	/**
	 * Add actions for any needed dashboard notices for remote storage services
	 *
	 * @param String|Array $services - a list of services, or single service
	 */
	private function setup_all_admin_notices_global($services) {
		
		global $updraftplus;

		if ('googledrive' === $services || (is_array($services) && in_array('googledrive', $services))) {
			$settings = UpdraftPlus_Storage_Methods_Interface::update_remote_storage_options_format('googledrive');
			
			if (is_wp_error($settings)) {
				if (!isset($this->storage_module_option_errors)) $this->storage_module_option_errors = '';
				$this->storage_module_option_errors .= "Google Drive (".$settings->get_error_code()."): ".$settings->get_error_message();
				add_action('all_admin_notices', array($this, 'show_admin_warning_multiple_storage_options'));
				$updraftplus->log_wp_error($settings, true, true);
			} elseif (!empty($settings['settings'])) {
				foreach ($settings['settings'] as $instance_id => $storage_options) {
					if ((defined('UPDRAFTPLUS_CUSTOM_GOOGLEDRIVE_APP') && UPDRAFTPLUS_CUSTOM_GOOGLEDRIVE_APP) || !empty($storage_options['clientid'])) {
						if (!empty($storage_options['clientid'])) {
							$clientid = $storage_options['clientid'];
							$token = empty($storage_options['token']) ? '' : $storage_options['token'];
						}
						if (!empty($clientid) && '' == $token) {
							if (!in_array($instance_id, $this->auth_instance_ids['googledrive'])) $this->auth_instance_ids['googledrive'][] = $instance_id;
							if (false === has_action('all_admin_notices', array($this, 'show_admin_warning_googledrive'))) add_action('all_admin_notices', array($this, 'show_admin_warning_googledrive'));
						}
						unset($clientid);
						unset($token);
					} else {
						if (empty($storage_options['user_id'])) {
							if (!in_array($instance_id, $this->auth_instance_ids['googledrive'])) $this->auth_instance_ids['googledrive'][] = $instance_id;
							if (false === has_action('all_admin_notices', array($this, 'show_admin_warning_googledrive'))) add_action('all_admin_notices', array($this, 'show_admin_warning_googledrive'));
						}
					}
				}
			}
		}
		if ('googlecloud' === $services || (is_array($services) && in_array('googlecloud', $services))) {
			$settings = UpdraftPlus_Storage_Methods_Interface::update_remote_storage_options_format('googlecloud');
			
			if (is_wp_error($settings)) {
				if (!isset($this->storage_module_option_errors)) $this->storage_module_option_errors = '';
				$this->storage_module_option_errors .= "Google Cloud (".$settings->get_error_code()."): ".$settings->get_error_message();
				add_action('all_admin_notices', array($this, 'show_admin_warning_multiple_storage_options'));
				$updraftplus->log_wp_error($settings, true, true);
			} elseif (!empty($settings['settings'])) {
				foreach ($settings['settings'] as $instance_id => $storage_options) {
					$clientid = $storage_options['clientid'];
					$token = (empty($storage_options['token'])) ? '' : $storage_options['token'];
					
					if (!empty($clientid) && empty($token)) {
						if (!in_array($instance_id, $this->auth_instance_ids['googlecloud'])) $this->auth_instance_ids['googlecloud'][] = $instance_id;
						if (false === has_action('all_admin_notices', array($this, 'show_admin_warning_googlecloud'))) add_action('all_admin_notices', array($this, 'show_admin_warning_googlecloud'));
					}
				}
			}
		}
		
		if ('dropbox' === $services || (is_array($services) && in_array('dropbox', $services))) {
			$settings = UpdraftPlus_Storage_Methods_Interface::update_remote_storage_options_format('dropbox');
			
			if (is_wp_error($settings)) {
				if (!isset($this->storage_module_option_errors)) $this->storage_module_option_errors = '';
				$this->storage_module_option_errors .= "Dropbox (".$settings->get_error_code()."): ".$settings->get_error_message();
				add_action('all_admin_notices', array($this, 'show_admin_warning_multiple_storage_options'));
				$updraftplus->log_wp_error($settings, true, true);
			} elseif (!empty($settings['settings'])) {
				foreach ($settings['settings'] as $instance_id => $storage_options) {
					if (empty($storage_options['tk_access_token'])) {
						if (!in_array($instance_id, $this->auth_instance_ids['dropbox'])) $this->auth_instance_ids['dropbox'][] = $instance_id;
						if (false === has_action('all_admin_notices', array($this, 'show_admin_warning_dropbox'))) add_action('all_admin_notices', array($this, 'show_admin_warning_dropbox'));
					}
				}
			}
		}
		
		if ('onedrive' === $services || (is_array($services) && in_array('onedrive', $services))) {
			$settings = UpdraftPlus_Storage_Methods_Interface::update_remote_storage_options_format('onedrive');
			
			if (is_wp_error($settings)) {
				if (!isset($this->storage_module_option_errors)) $this->storage_module_option_errors = '';
				$this->storage_module_option_errors .= "OneDrive (".$settings->get_error_code()."): ".$settings->get_error_message();
				add_action('all_admin_notices', array($this, 'show_admin_warning_multiple_storage_options'));
				$updraftplus->log_wp_error($settings, true, true);
			} elseif (!empty($settings['settings'])) {
				foreach ($settings['settings'] as $instance_id => $storage_options) {
					if ((defined('UPDRAFTPLUS_CUSTOM_ONEDRIVE_APP') && UPDRAFTPLUS_CUSTOM_ONEDRIVE_APP)) {
						if (!empty($storage_options['clientid']) && !empty($storage_options['secret']) && empty($storage_options['refresh_token'])) {
								if (!in_array($instance_id, $this->auth_instance_ids['onedrive'])) $this->auth_instance_ids['onedrive'][] = $instance_id;
								if (false === has_action('all_admin_notices', array($this, 'show_admin_warning_onedrive'))) add_action('all_admin_notices', array($this, 'show_admin_warning_onedrive'));
						} elseif (empty($storage_options['refresh_token'])) {
							if (!in_array($instance_id, $this->auth_instance_ids['onedrive'])) $this->auth_instance_ids['onedrive'][] = $instance_id;
							if (false === has_action('all_admin_notices', array($this, 'show_admin_warning_onedrive'))) add_action('all_admin_notices', array($this, 'show_admin_warning_onedrive'));
						}
					} else {
						if (empty($storage_options['refresh_token'])) {
							if (!in_array($instance_id, $this->auth_instance_ids['onedrive'])) $this->auth_instance_ids['onedrive'][] = $instance_id;
							if (false === has_action('all_admin_notices', array($this, 'show_admin_warning_onedrive'))) add_action('all_admin_notices', array($this, 'show_admin_warning_onedrive'));
						}
					}
				}
			}
		}

		if ('updraftvault' === $services || (is_array($services) && in_array('updraftvault', $services))) {
			$settings = UpdraftPlus_Storage_Methods_Interface::update_remote_storage_options_format('updraftvault');
			
			if (is_wp_error($settings)) {
				if (!isset($this->storage_module_option_errors)) $this->storage_module_option_errors = '';
				$this->storage_module_option_errors .= "UpdraftVault (".$settings->get_error_code()."): ".$settings->get_error_message();
				add_action('all_admin_notices', array($this, 'show_admin_warning_multiple_storage_options'));
				$updraftplus->log_wp_error($settings, true, true);
			} elseif (!empty($settings['settings'])) {
				foreach ($settings['settings'] as $instance_id => $storage_options) {
					if (empty($storage_options['token']) && empty($storage_options['email'])) {
						add_action('all_admin_notices', array($this, 'show_admin_warning_updraftvault'));
					}
				}
			}
		}

		if ($this->disk_space_check(1048576*35) === false) add_action('all_admin_notices', array($this, 'show_admin_warning_diskspace'));
	}
	
	private function setup_all_admin_notices_udonly($service, $override = false) {// phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found -- Filter use
		global $updraftplus;

		if (UpdraftPlus_Options::user_can_manage() && defined('DISABLE_WP_CRON') && DISABLE_WP_CRON && (!defined('UPDRAFTPLUS_DISABLE_WP_CRON_NOTICE') || !UPDRAFTPLUS_DISABLE_WP_CRON_NOTICE)) {
			add_action('all_admin_notices', array($this, 'show_admin_warning_disabledcron'));
		}

		if (UpdraftPlus_Options::get_updraft_option('updraft_debug_mode')) {
			@ini_set('display_errors', 1);
			// @codingStandardsIgnoreLine
			if (defined('E_DEPRECATED')) {
				// @codingStandardsIgnoreLine
				@error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
			} else {
				@error_reporting(E_ALL & ~E_NOTICE);
			}
			add_action('all_admin_notices', array($this, 'show_admin_debug_warning'));
		}

		if (null === UpdraftPlus_Options::get_updraft_option('updraft_interval')) {
			add_action('all_admin_notices', array($this, 'show_admin_nosettings_warning'));
			$this->no_settings_warning = true;
		}

		// Avoid false positives, by attempting to raise the limit (as happens when we actually do a backup)
		@set_time_limit(UPDRAFTPLUS_SET_TIME_LIMIT);
		$max_execution_time = (int) @ini_get('max_execution_time');
		if ($max_execution_time>0 && $max_execution_time<20) {
			add_action('all_admin_notices', array($this, 'show_admin_warning_execution_time'));
		}

		// LiteSpeed has a generic problem with terminating cron jobs
		if (isset($_SERVER['SERVER_SOFTWARE']) && strpos($_SERVER['SERVER_SOFTWARE'], 'LiteSpeed') !== false) {
			if (!is_file(ABSPATH.'.htaccess') || !preg_match('/noabort/i', file_get_contents(ABSPATH.'.htaccess'))) {
				add_action('all_admin_notices', array($this, 'show_admin_warning_litespeed'));
			}
		}

		if (version_compare($updraftplus->get_wordpress_version(), '3.2', '<')) add_action('all_admin_notices', array($this, 'show_admin_warning_wordpressversion'));
		
		// DreamObjects west cluster shutdown warning
		if ('dreamobjects' === $service || (is_array($service) && in_array('dreamobjects', $service))) {
			$settings = UpdraftPlus_Storage_Methods_Interface::update_remote_storage_options_format('dreamobjects');
			
			if (is_wp_error($settings)) {
				if (!isset($this->storage_module_option_errors)) $this->storage_module_option_errors = '';
				$this->storage_module_option_errors .= "DreamObjects (".$settings->get_error_code()."): ".$settings->get_error_message();
				add_action('all_admin_notices', array($this, 'show_admin_warning_multiple_storage_options'));
				$updraftplus->log_wp_error($settings, true, true);
			} elseif (!empty($settings['settings'])) {
				foreach ($settings['settings'] as $instance_id => $storage_options) {
					if ('objects-us-west-1.dream.io' == $storage_options['endpoint']) {
						add_action('all_admin_notices', array($this, 'show_admin_warning_dreamobjects'));
					}
				}
			}
		}
	}
	
	/**
	 * Used to output the information for the next scheduled backup.
	 * moved to function for the ajax saves
	 */
	public function next_scheduled_backups_output() {
		// UNIX timestamp
		$next_scheduled_backup = wp_next_scheduled('updraft_backup');
		if ($next_scheduled_backup) {
			// Convert to GMT
			$next_scheduled_backup_gmt = gmdate('Y-m-d H:i:s', $next_scheduled_backup);
			// Convert to blog time zone
			$next_scheduled_backup = get_date_from_gmt($next_scheduled_backup_gmt, 'D, F j, Y H:i');
			// $next_scheduled_backup = date_i18n('D, F j, Y H:i', $next_scheduled_backup);
		} else {
			$next_scheduled_backup = __('Nothing currently scheduled', 'updraftplus');
			$files_not_scheduled = true;
		}
		
		$next_scheduled_backup_database = wp_next_scheduled('updraft_backup_database');
		if (UpdraftPlus_Options::get_updraft_option('updraft_interval_database', UpdraftPlus_Options::get_updraft_option('updraft_interval')) == UpdraftPlus_Options::get_updraft_option('updraft_interval')) {
			if (isset($files_not_scheduled)) {
				$next_scheduled_backup_database = $next_scheduled_backup;
				$database_not_scheduled = true;
			} else {
				$next_scheduled_backup_database = __("At the same time as the files backup", 'updraftplus');
				$next_scheduled_backup_database_same_time = true;
			}
		} else {
			if ($next_scheduled_backup_database) {
				// Convert to GMT
				$next_scheduled_backup_database_gmt = gmdate('Y-m-d H:i:s', $next_scheduled_backup_database);
				// Convert to blog time zone
				$next_scheduled_backup_database = get_date_from_gmt($next_scheduled_backup_database_gmt, 'D, F j, Y H:i');
				// $next_scheduled_backup_database = date_i18n('D, F j, Y H:i', $next_scheduled_backup_database);
			} else {
				$next_scheduled_backup_database = __('Nothing currently scheduled', 'updraftplus');
				$database_not_scheduled = true;
			}
		}
		
		if (isset($files_not_scheduled) && isset($database_not_scheduled)) {
		?>
			<span class="not-scheduled"><?php _e('Nothing currently scheduled', 'updraftplus'); ?></span>
		<?php
		} else {
			echo empty($next_scheduled_backup_database_same_time) ? __('Files', 'updraftplus') : __('Files and database', 'updraftplus');
			?>
			: 
			<span class="updraft_all-files">
				<?php
					echo $next_scheduled_backup;
				?>
			</span>
			<?php
			if (empty($next_scheduled_backup_database_same_time)) {
				_e('Database', 'updraftplus');
			?>
			: 
			<span class="updraft_all-files">
				<?php
				echo $next_scheduled_backup_database;
				?>
			</span>
			<?php
			}
		}
		
	}
	
	/**
	 * Used to output the information for the next scheduled  file backup.
	 * moved to function for the ajax saves
	 *
	 * @param Boolean $return_instead_of_echo Whether to return or echo the results. N.B. More than just the results to echo will be returned
	 * @return Void|String If $return_instead_of_echo parameter is true, It returns html string
	 */
	public function next_scheduled_files_backups_output($return_instead_of_echo = false) {
		if ($return_instead_of_echo) ob_start();
		// UNIX timestamp
		$next_scheduled_backup = wp_next_scheduled('updraft_backup');
		if ($next_scheduled_backup) {
			// Convert to GMT
			$next_scheduled_backup_gmt = gmdate('Y-m-d H:i:s', $next_scheduled_backup);
			// Convert to blog time zone
			$next_scheduled_backup = get_date_from_gmt($next_scheduled_backup_gmt, 'D, F j, Y H:i');
			$files_not_scheduled = false;
		} else {
			$next_scheduled_backup = __('Nothing currently scheduled', 'updraftplus');
			$files_not_scheduled = true;
		}
		
		if ($files_not_scheduled) {
			echo '<span>'.$next_scheduled_backup.'</span>';
		} else {
			echo '<span class="updraft_next_scheduled_date_time">'.$next_scheduled_backup.'</span>';
		}
		
		if ($return_instead_of_echo) return ob_get_clean();
	}
	
	/**
	 * Used to output the information for the next scheduled database backup.
	 * moved to function for the ajax saves
	 *
	 * @param Boolean $return_instead_of_echo Whether to return or echo the results. N.B. More than just the results to echo will be returned
	 * @return Void|String If $return_instead_of_echo parameter is true, It returns html string
	 */
	public function next_scheduled_database_backups_output($return_instead_of_echo = false) {
		if ($return_instead_of_echo) ob_start();
		
		$next_scheduled_backup_database = wp_next_scheduled('updraft_backup_database');
		if ($next_scheduled_backup_database) {
			// Convert to GMT
			$next_scheduled_backup_database_gmt = gmdate('Y-m-d H:i:s', $next_scheduled_backup_database);
			// Convert to blog time zone
			$next_scheduled_backup_database = get_date_from_gmt($next_scheduled_backup_database_gmt, 'D, F j, Y H:i');
			$database_not_scheduled = false;
		} else {
			$next_scheduled_backup_database = __('Nothing currently scheduled', 'updraftplus');
			$database_not_scheduled = true;
		}
		
		if ($database_not_scheduled) {
			echo '<span>'.$next_scheduled_backup_database.'</span>';
		} else {
			echo '<span class="updraft_next_scheduled_date_time">'.$next_scheduled_backup_database.'</span>';
		}
		
		if ($return_instead_of_echo) return ob_get_clean();
	}
	
	/**
	 * Run upon the WP admin_init action
	 */
	private function admin_init() {

		add_action('core_upgrade_preamble', array($this, 'core_upgrade_preamble'));
		add_action('admin_action_upgrade-plugin', array($this, 'admin_action_upgrade_pluginortheme'));
		add_action('admin_action_upgrade-theme', array($this, 'admin_action_upgrade_pluginortheme'));

		add_action('admin_head', array($this, 'admin_head'));
		add_filter((is_multisite() ? 'network_admin_' : '').'plugin_action_links', array($this, 'plugin_action_links'), 10, 2);
		add_action('wp_ajax_updraft_download_backup', array($this, 'updraft_download_backup'));
		add_action('wp_ajax_updraft_ajax', array($this, 'updraft_ajax_handler'));
		add_action('wp_ajax_updraft_ajaxrestore', array($this, 'updraft_ajaxrestore'));
		add_action('wp_ajax_nopriv_updraft_ajaxrestore', array($this, 'updraft_ajaxrestore'));
		
		add_action('wp_ajax_plupload_action', array($this, 'plupload_action'));
		add_action('wp_ajax_plupload_action2', array($this, 'plupload_action2'));

		add_action('wp_before_admin_bar_render', array($this, 'wp_before_admin_bar_render'));

		// Add a new Ajax action for saving settings
		add_action('wp_ajax_updraft_savesettings', array($this, 'updraft_ajax_savesettings'));
		
		// Ajax for settings import and export
		add_action('wp_ajax_updraft_importsettings', array($this, 'updraft_ajax_importsettings'));

		// UpdraftPlus templates
		$this->register_template_directories();
		
		global $updraftplus, $pagenow;
		add_filter('updraftplus_dirlist_others', array($updraftplus, 'backup_others_dirlist'));
		add_filter('updraftplus_dirlist_uploads', array($updraftplus, 'backup_uploads_dirlist'));

		// First, the checks that are on all (admin) pages:

		$service = UpdraftPlus_Options::get_updraft_option('updraft_service');

		if (UpdraftPlus_Options::user_can_manage()) {

			$this->print_restore_in_progress_box_if_needed();

			// Main dashboard page advert
			// Since our nonce is printed, make sure they have sufficient credentials
			if ('index.php' == $pagenow && current_user_can('update_plugins') && (!file_exists(UPDRAFTPLUS_DIR.'/udaddons') || (defined('UPDRAFTPLUS_FORCE_DASHNOTICE') && UPDRAFTPLUS_FORCE_DASHNOTICE))) {

				$dismissed_until = UpdraftPlus_Options::get_updraft_option('updraftplus_dismisseddashnotice', 0);
				
				$backup_dir = $updraftplus->backups_dir_location();
				// N.B. Not an exact proxy for the installed time; they may have tweaked the expert option to move the directory
				$installed = @filemtime($backup_dir.'/index.html');
				$installed_for = time() - $installed;

				if (($installed && time() > $dismissed_until && $installed_for > 28*86400 && !defined('UPDRAFTPLUS_NOADS_B')) || (defined('UPDRAFTPLUS_FORCE_DASHNOTICE') && UPDRAFTPLUS_FORCE_DASHNOTICE)) {
					add_action('all_admin_notices', array($this, 'show_admin_notice_upgradead'));
				}
			}
			
			// Moved out for use with Ajax saving
			$this->setup_all_admin_notices_global($service);
		}
		
		if (!class_exists('Updraft_Dashboard_News')) include_once(UPDRAFTPLUS_DIR.'/includes/class-updraft-dashboard-news.php');

		$news_translations = array(
			'product_title' => 'UpdraftPlus',
			'item_prefix' => __('UpdraftPlus', 'updraftplus'),
			'item_description' => __('UpdraftPlus News', 'updraftplus'),
			'dismiss_tooltip' => __('Dismiss all UpdraftPlus news', 'updraftplus'),
			'dismiss_confirm' => __('Are you sure you want to dismiss all UpdraftPlus news forever?', 'updraftplus'),
		);
		
		$updraftplus_dashboard_news = new Updraft_Dashboard_News('https://feeds.feedburner.com/updraftplus/', 'https://updraftplus.com/news/', $news_translations);
		
		// New-install admin tour
		if ((!defined('UPDRAFTPLUS_ENABLE_TOUR') || UPDRAFTPLUS_ENABLE_TOUR) && (!defined('UPDRAFTPLUS_THIS_IS_CLONE') || !UPDRAFTPLUS_THIS_IS_CLONE)) {
			include_once(UPDRAFTPLUS_DIR.'/includes/updraftplus-tour.php');
		}

		// Next, the actions that only come on the UpdraftPlus page
		if (UpdraftPlus_Options::admin_page() != $pagenow || empty($_REQUEST['page']) || 'updraftplus' != $_REQUEST['page']) return;
		$this->setup_all_admin_notices_udonly($service);
		
		add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'), 99999);

		$udp_saved_version = UpdraftPlus_Options::get_updraft_option('updraftplus_version');
		if (!$udp_saved_version || $udp_saved_version != $updraftplus->version) {
			if (!$udp_saved_version) {
				// udp was newly installed, or upgraded from an older version
				do_action('updraftplus_newly_installed', $updraftplus->version);
			} else {
				// udp was updated or downgraded
				do_action('updraftplus_version_changed', UpdraftPlus_Options::get_updraft_option('updraftplus_version'), $updraftplus->version);
			}
			UpdraftPlus_Options::update_updraft_option('updraftplus_version', $updraftplus->version);
		}
	}

	/**
	 * Sets up what is needed to allow an in-page backup to be run. Will enqueue scripts and output appropriate HTML (so, should be run when at a suitable place). Not intended for use on the UpdraftPlus settings page.
	 *
	 * @param string   $title    Text to use for the title of the modal
	 * @param callable $callback Callable function to output the contents of the updraft_inpage_prebackup element - i.e. what shows in the modal before a backup begins.
	 */
	public function add_backup_scaffolding($title, $callback) {
		$this->admin_enqueue_scripts();
		?>
		<script>
		// TODO: This is not the best way.
		var updraft_credentialtest_nonce='<?php echo wp_create_nonce('updraftplus-credentialtest-nonce');?>';
		</script>
		<div id="updraft-poplog" >
			<pre id="updraft-poplog-content" style="white-space: pre-wrap;"></pre>
		</div>
		
		<div id="updraft-backupnow-inpage-modal" title="UpdraftPlus - <?php echo $title; ?>">

			<div id="updraft_inpage_prebackup" style="float:left; clear:both;">
				<?php call_user_func($callback); ?>
			</div>

			<div id="updraft_inpage_backup">

				<h2><?php echo $title;?></h2>

				<div id="updraft_backup_started" class="updated" style="display:none; max-width: 560px; font-size:100%; line-height: 100%; padding:6px; clear:left;"></div>

				<?php $this->render_active_jobs_and_log_table(true, false); ?>

			</div>

		</div>
		<?php
	}
	
	public function updraft_ajaxrestore() {
// TODO: All needs testing with restricted filesystem permissions. Those credentials need to be POST-ed too - currently not.
// TODO
// error_log(serialize($_POST));

		if (empty($_POST['subaction']) || 'restore' != $_POST['subaction']) {
			echo json_encode(array('e' => 'Illegitimate data sent (0)'));
			die();
		}

		if (empty($_POST['restorenonce'])) {
			echo json_encode(array('e' => 'Illegitimate data sent (1)'));
			die();
		}

		$restore_nonce = (string) $_POST['restorenonce'];

		if (empty($_POST['ajaxauth'])) {
			echo json_encode(array('e' => 'Illegitimate data sent (2)'));
			die();
		}

		global $updraftplus;

		$ajax_auth = get_site_option('updraft_ajax_restore_'.$restore_nonce);

		if (!$ajax_auth) {
			echo json_encode(array('e' => 'Illegitimate data sent (3)'));
			die();
		}

		if (!preg_match('/^([0-9a-f]+):(\d+)/i', $ajax_auth, $matches)) {
			echo json_encode(array('e' => 'Illegitimate data sent (4)'));
			die();
		}

		$nonce_time = $matches[2];
		$auth_code_sent = $matches[1];
		if (time() > $nonce_time + 600) {
			echo json_encode(array('e' => 'Illegitimate data sent (5)'));
			die();
		}

// TODO: Deactivate the auth code whilst the operation is underway

		$last_one = empty($_POST['lastone']) ? false : true;

		@set_time_limit(UPDRAFTPLUS_SET_TIME_LIMIT);

		$updraftplus->backup_time_nonce($restore_nonce);
		$updraftplus->logfile_open($restore_nonce);

		$timestamp = empty($_POST['timestamp']) ? false : (int) $_POST['timestamp'];
		$multisite = empty($_POST['multisite']) ? false : (bool) $_POST['multisite'];
		$created_by_version = empty($_POST['created_by_version']) ? false : (int) $_POST['created_by_version'];

		// TODO: We need to know about first_one (not yet sent), as well as last_one

		// TODO: Verify the values of these
		$type = empty($_POST['type']) ? false : (int) $_POST['type'];
		$backupfile = empty($_POST['backupfile']) ? false : (string) $_POST['backupfile'];

		$updraftplus->log("Deferred restore resumption: $type: $backupfile (timestamp=$timestamp, last_one=$last_one)");



		$backupable_entities = $updraftplus->get_backupable_file_entities(true);

		if (!isset($backupable_entities[$type])) {
			echo json_encode(array('e' => 'Illegitimate data sent (6 - no such entity)', 'data' => $type));
			die();
		}


		if ($last_one) {
			// Remove the auth nonce from the DB to prevent abuse
			delete_site_option('updraft_ajax_restore_'.$restore_nonce);
		} else {
			// Reset the counter after a successful operation
			update_site_option('updraft_ajax_restore_'.$restore_nonce, $auth_code_sent.':'.time());
		}

		echo json_encode(array('e' => 'TODO', 'd' => $_POST));
		die;
	}

	/**
	 * Runs upon the WP action wp_before_admin_bar_render
	 */
	public function wp_before_admin_bar_render() {
		global $wp_admin_bar;
		
		if (!UpdraftPlus_Options::user_can_manage()) return;
		
		if (defined('UPDRAFTPLUS_ADMINBAR_DISABLE') && UPDRAFTPLUS_ADMINBAR_DISABLE) return;

		if (false == apply_filters('updraftplus_settings_page_render', true)) return;

		$option_location = UpdraftPlus_Options::admin_page_url();
		
		$args = array(
			'id' => 'updraft_admin_node',
			'title' => apply_filters('updraftplus_admin_node_title', 'UpdraftPlus')
		);
		$wp_admin_bar->add_node($args);
		
		$args = array(
			'id' => 'updraft_admin_node_status',
			'title' => str_ireplace('Back Up', 'Backup', __('Backup', 'updraftplus')).' / '.__('Restore', 'updraftplus'),
			'parent' => 'updraft_admin_node',
			'href' => $option_location.'?page=updraftplus&tab=backups'
		);
		$wp_admin_bar->add_node($args);
		
		$args = array(
			'id' => 'updraft_admin_node_migrate',
			'title' => __('Migrate / Clone', 'updraftplus'),
			'parent' => 'updraft_admin_node',
			'href' => $option_location.'?page=updraftplus&tab=migrate'
		);
		$wp_admin_bar->add_node($args);
		
		$args = array(
			'id' => 'updraft_admin_node_settings',
			'title' => __('Settings', 'updraftplus'),
			'parent' => 'updraft_admin_node',
			'href' => $option_location.'?page=updraftplus&tab=settings'
		);
		$wp_admin_bar->add_node($args);
		
		$args = array(
			'id' => 'updraft_admin_node_expert_content',
			'title' => __('Advanced Tools', 'updraftplus'),
			'parent' => 'updraft_admin_node',
			'href' => $option_location.'?page=updraftplus&tab=expert'
		);
		$wp_admin_bar->add_node($args);
		
		$args = array(
			'id' => 'updraft_admin_node_addons',
			'title' => __('Extensions', 'updraftplus'),
			'parent' => 'updraft_admin_node',
			'href' => $option_location.'?page=updraftplus&tab=addons'
		);
		$wp_admin_bar->add_node($args);
		
		global $updraftplus;
		if (!$updraftplus->have_addons) {
			$args = array(
				'id' => 'updraft_admin_node_premium',
				'title' => 'UpdraftPlus Premium',
				'parent' => 'updraft_admin_node',
				'href' => apply_filters('updraftplus_com_link', 'https://updraftplus.com/shop/updraftplus-premium/')
			);
			$wp_admin_bar->add_node($args);
		}
	}

	/**
	 * Output HTML for a dashboard notice highlighting the benefits of upgrading to Premium
	 */
	public function show_admin_notice_upgradead() {
		$this->include_template('wp-admin/notices/thanks-for-using-main-dash.php');
	}

	/**
	 * Enqueue sufficient versions of jQuery and our own scripts
	 */
	private function ensure_sufficient_jquery_and_enqueue() {
		global $updraftplus;
		
		$enqueue_version = $updraftplus->use_unminified_scripts() ? $updraftplus->version.'.'.time() : $updraftplus->version;
		$min_or_not = $updraftplus->use_unminified_scripts() ? '' : '.min';
		
		if (version_compare($updraftplus->get_wordpress_version(), '3.3', '<')) {
			// Require a newer jQuery (3.2.1 has 1.6.1, so we go for something not too much newer). We use .on() in a way that is incompatible with < 1.7
			wp_deregister_script('jquery');
			$jquery_enqueue_version = $updraftplus->use_unminified_scripts() ? '1.7.2'.'.'.time() : '1.7.2';
			wp_register_script('jquery', 'https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery'.$min_or_not.'.js', false, $jquery_enqueue_version, false);
			wp_enqueue_script('jquery');
			// No plupload until 3.3
			wp_enqueue_script('updraft-admin-common', UPDRAFTPLUS_URL.'/includes/updraft-admin-common'.$min_or_not.'.js', array('jquery', 'jquery-ui-dialog', 'jquery-ui-core', 'jquery-ui-accordion'), $enqueue_version, true);
		} else {
			wp_enqueue_script('updraft-admin-common', UPDRAFTPLUS_URL.'/includes/updraft-admin-common'.$min_or_not.'.js', array('jquery', 'jquery-ui-dialog', 'jquery-ui-core', 'jquery-ui-accordion', 'plupload-all'), $enqueue_version);
		}
		
	}

	/**
	 * This is also called directly from the auto-backup add-on
	 */
	public function admin_enqueue_scripts() {

		global $updraftplus, $wp_locale;
		
		$enqueue_version = $updraftplus->use_unminified_scripts() ? $updraftplus->version.'.'.time() : $updraftplus->version;
		$min_or_not = $updraftplus->use_unminified_scripts() ? '' : '.min';

		// Defeat other plugins/themes which dump their jQuery UI CSS onto our settings page
		wp_deregister_style('jquery-ui');
		$jquery_ui_css_enqueue_version = $updraftplus->use_unminified_scripts() ? '1.11.4.0'.'.'.time() : '1.11.4.0';
		wp_enqueue_style('jquery-ui', UPDRAFTPLUS_URL.'/includes/jquery-ui.custom'.$min_or_not.'.css', array(), $jquery_ui_css_enqueue_version);
	
		wp_enqueue_style('updraft-admin-css', UPDRAFTPLUS_URL.'/css/updraftplus-admin'.$min_or_not.'.css', array(), $enqueue_version);
		// add_filter('style_loader_tag', array($this, 'style_loader_tag'), 10, 2);

		$this->ensure_sufficient_jquery_and_enqueue();
		$jquery_blockui_enqueue_version = $updraftplus->use_unminified_scripts() ? '2.70.0'.'.'.time() : '2.70.0';
		wp_enqueue_script('jquery-blockui', UPDRAFTPLUS_URL.'/includes/jquery.blockUI'.$min_or_not.'.js', array('jquery'), $jquery_blockui_enqueue_version);
	
		wp_enqueue_script('jquery-labelauty', UPDRAFTPLUS_URL.'/includes/labelauty/jquery-labelauty'.$min_or_not.'.js', array('jquery'), $enqueue_version);
		wp_enqueue_style('jquery-labelauty', UPDRAFTPLUS_URL.'/includes/labelauty/jquery-labelauty'.$min_or_not.'.css', array(), $enqueue_version);
		$serialize_js_enqueue_version = $updraftplus->use_unminified_scripts() ? '2.8.1'.'.'.time() : '2.8.1';
		wp_enqueue_script('jquery.serializeJSON', UPDRAFTPLUS_URL.'/includes/jquery.serializeJSON/jquery.serializejson'.$min_or_not.'.js', array('jquery'), $serialize_js_enqueue_version);
		$handlebars_js_enqueue_version = $updraftplus->use_unminified_scripts() ? '4.0.11'.'.'.time() : '4.0.11';
		wp_enqueue_script('handlebars', UPDRAFTPLUS_URL.'/includes/handlebars/handlebars'.$min_or_not.'.js', array(), $handlebars_js_enqueue_version);
		$this->enqueue_jstree();
		
		do_action('updraftplus_admin_enqueue_scripts');
		
		$day_selector = '';
		for ($day_index = 0; $day_index <= 6; $day_index++) {
			// $selected = ($opt == $day_index) ? 'selected="selected"' : '';
			$selected = '';
			$day_selector .= "\n\t<option value='" . $day_index . "' $selected>" . $wp_locale->get_weekday($day_index) . '</option>';
		}

		$mday_selector = '';
		for ($mday_index = 1; $mday_index <= 28; $mday_index++) {
			// $selected = ($opt == $mday_index) ? 'selected="selected"' : '';
			$selected = '';
			$mday_selector .= "\n\t<option value='" . $mday_index . "' $selected>" . $mday_index . '</option>';
		}
		$remote_storage_options_and_templates = UpdraftPlus_Storage_Methods_Interface::get_remote_storage_options_and_templates();
		$main_tabs = $this->get_main_tabs_array();
		wp_localize_script('updraft-admin-common', 'updraftlion', array(
			'tab' => empty($_GET['tab']) ? 'backups' : $_GET['tab'],
			'sendonlyonwarnings' => __('Send a report only when there are warnings/errors', 'updraftplus'),
			'wholebackup' => __('When the Email storage method is enabled, also send the backup', 'updraftplus'),
			'emailsizelimits' => esc_attr(sprintf(__('Be aware that mail servers tend to have size limits; typically around %s Mb; backups larger than any limits will likely not arrive.', 'updraftplus'), '10-20')),
			'rescanning' => __('Rescanning (looking for backups that you have uploaded manually into the internal backup store)...', 'updraftplus'),
			'dbbackup' => __('Only email the database backup', 'updraftplus'),
			'rescanningremote' => __('Rescanning remote and local storage for backup sets...', 'updraftplus'),
			'enteremailhere' => esc_attr(__('To send to more than one address, separate each address with a comma.', 'updraftplus')),
			'excludedeverything' => __('If you exclude both the database and the files, then you have excluded everything!', 'updraftplus'),
			'nofileschosen' => __('You have chosen to backup files, but no file entities have been selected', 'updraftplus'),
			'notableschosen' => __('You have chosen to backup a database, but no tables have been selected', 'updraftplus'),
			'restore_proceeding' => __('The restore operation has begun. Do not press stop or close your browser until it reports itself as having finished.', 'updraftplus'),
			'unexpectedresponse' => __('Unexpected response:', 'updraftplus'),
			'servererrorcode' => __('The web server returned an error code (try again, or check your web server logs)', 'updraftplus'),
			'newuserpass' => __("The new user's RackSpace console password is (this will not be shown again):", 'updraftplus'),
			'trying' => __('Trying...', 'updraftplus'),
			'fetching' => __('Fetching...', 'updraftplus'),
			'calculating' => __('calculating...', 'updraftplus'),
			'begunlooking' => __('Begun looking for this entity', 'updraftplus'),
			'stilldownloading' => __('Some files are still downloading or being processed - please wait.', 'updraftplus'),
			'processing' => __('Processing files - please wait...', 'updraftplus'),
			'emptyresponse' => __('Error: the server sent an empty response.', 'updraftplus'),
			'warnings' => __('Warnings:', 'updraftplus'),
			'errors' => __('Errors:', 'updraftplus'),
			'jsonnotunderstood' => __('Error: the server sent us a response which we did not understand.', 'updraftplus'),
			'errordata' => __('Error data:', 'updraftplus'),
			'error' => __('Error:', 'updraftplus'),
			'errornocolon' => __('Error', 'updraftplus'),
			'existing_backups' => __('Existing Backups', 'updraftplus'),
			'fileready' => __('File ready.', 'updraftplus'),
			'actions' => __('Actions', 'updraftplus'),
			'deletefromserver' => __('Delete from your web server', 'updraftplus'),
			'downloadtocomputer' => __('Download to your computer', 'updraftplus'),
			'browse_contents' => __('Browse contents', 'updraftplus'),
			'notunderstood' => __('Download error: the server sent us a response which we did not understand.', 'updraftplus'),
			'requeststart' => __('Requesting start of backup...', 'updraftplus'),
			'phpinfo' => __('PHP information', 'updraftplus'),
			'delete_old_dirs' => __('Delete Old Directories', 'updraftplus'),
			'raw' => __('Raw backup history', 'updraftplus'),
			'notarchive' => __('This file does not appear to be an UpdraftPlus backup archive (such files are .zip or .gz files which have a name like: backup_(time)_(site name)_(code)_(type).(zip|gz)).', 'updraftplus').' '.__('However, UpdraftPlus archives are standard zip/SQL files - so if you are sure that your file has the right format, then you can rename it to match that pattern.', 'updraftplus'),
			'notarchive2' => '<p>'.__('This file does not appear to be an UpdraftPlus backup archive (such files are .zip or .gz files which have a name like: backup_(time)_(site name)_(code)_(type).(zip|gz)).', 'updraftplus').'</p> '.apply_filters('updraftplus_if_foreign_then_premium_message', '<p><a href="'.apply_filters('updraftplus_com_link', "https://updraftplus.com/shop/updraftplus-premium/").'">'.__('If this is a backup created by a different backup plugin, then UpdraftPlus Premium may be able to help you.', 'updraftplus').'</a></p>'),
			'makesure' => __('(make sure that you were trying to upload a zip file previously created by UpdraftPlus)', 'updraftplus'),
			'uploaderror' => __('Upload error:', 'updraftplus'),
			'notdba' => __('This file does not appear to be an UpdraftPlus encrypted database archive (such files are .gz.crypt files which have a name like: backup_(time)_(site name)_(code)_db.crypt.gz).', 'updraftplus'),
			'uploaderr' => __('Upload error', 'updraftplus'),
			'followlink' => __('Follow this link to attempt decryption and download the database file to your computer.', 'updraftplus'),
			'thiskey' => __('This decryption key will be attempted:', 'updraftplus'),
			'unknownresp' => __('Unknown server response:', 'updraftplus'),
			'ukrespstatus' => __('Unknown server response status:', 'updraftplus'),
			'uploaded' => __('The file was uploaded.', 'updraftplus'),
			// One of the translators has erroneously changed "Backup" into "Back up" (which means, "reverse" !)
			'backupnow' => str_ireplace('Back Up', 'Backup', __('Backup Now', 'updraftplus')),
			'cancel' => __('Cancel', 'updraftplus'),
			'deletebutton' => __('Delete', 'updraftplus'),
			'createbutton' => __('Create', 'updraftplus'),
			'uploadbutton' => __('Upload', 'updraftplus'),
			'youdidnotselectany' => __('You did not select any components to restore. Please select at least one, and then try again.', 'updraftplus'),
			'proceedwithupdate' => __('Proceed with update', 'updraftplus'),
			'close' => __('Close', 'updraftplus'),
			'restore' => __('Restore', 'updraftplus'),
			'downloadlogfile' => __('Download log file', 'updraftplus'),
			'automaticbackupbeforeupdate' => __('Automatic backup before update', 'updraftplus'),
			'unsavedsettings' => __('You have made changes to your settings, and not saved.', 'updraftplus'),
			'saving' => __('Saving...', 'updraftplus'),
			'connect' => __('Connect', 'updraftplus'),
			'connecting' => __('Connecting...', 'updraftplus'),
			'disconnect' => __('Disconnect', 'updraftplus'),
			'disconnecting' => __('Disconnecting...', 'updraftplus'),
			'counting' => __('Counting...', 'updraftplus'),
			'updatequotacount' => __('Update quota count', 'updraftplus'),
			'addingsite' => __('Adding...', 'updraftplus'),
			'addsite' => __('Add site', 'updraftplus'),
			// 'resetting' => __('Resetting...', 'updraftplus'),
			'creating_please_allow' => __('Creating...', 'updraftplus').(function_exists('openssl_encrypt') ? '' : ' ('.__('your PHP install lacks the openssl module; as a result, this can take minutes; if nothing has happened by then, then you should either try a smaller key size, or ask your web hosting company how to enable this PHP module on your setup.', 'updraftplus').')'),
			'sendtosite' => __('Send to site:', 'updraftplus'),
			'checkrpcsetup' => sprintf(__('You should check that the remote site is online, not firewalled, does not have security modules that may be blocking access, has UpdraftPlus version %s or later active and that the keys have been entered correctly.', 'updraftplus'), '2.10.3'),
			'pleasenamekey' => __('Please give this key a name (e.g. indicate the site it is for):', 'updraftplus'),
			'key' => __('Key', 'updraftplus'),
			'nokeynamegiven' => sprintf(__("Failure: No %s was given.", 'updraftplus'), __('key name', 'updraftplus')),
			'deleting' => __('Deleting...', 'updraftplus'),
			'enter_mothership_url' => __('Please enter a valid URL', 'updraftplus'),
			'delete_response_not_understood' => __("We requested to delete the file, but could not understand the server's response", 'updraftplus'),
			'testingconnection' => __('Testing connection...', 'updraftplus'),
			'send' => __('Send', 'updraftplus'),
			'migratemodalheight' => class_exists('UpdraftPlus_Addons_Migrator') ? 555 : 300,
			'migratemodalwidth' => class_exists('UpdraftPlus_Addons_Migrator') ? 770 : 500,
			'download' => _x('Download', '(verb)', 'updraftplus'),
			'browse_download_link' => apply_filters('updraftplus_browse_download_link', '<a id="updraft_zip_download_notice" href="'.apply_filters('updraftplus_com_link', "https://updraftplus.com/landing/updraftplus-premium").'">'.__("With UpdraftPlus Premium, you can directly download individual files from here.", "updraftplus").'</a>'),
			'unsavedsettingsbackup' => __('You have made changes to your settings, and not saved.', 'updraftplus')."\n".__('You should save your changes to ensure that they are used for making your backup.', 'updraftplus'),
			'unsaved_settings_export' => __('You have made changes to your settings, and not saved.', 'updraftplus')."\n".__('Your export file will be of your displayed settings, not your saved ones.', 'updraftplus'),
			'dayselector' => $day_selector,
			'mdayselector' => $mday_selector,
			'day' => __('day', 'updraftplus'),
			'inthemonth' => __('in the month', 'updraftplus'),
			'days' => __('day(s)', 'updraftplus'),
			'hours' => __('hour(s)', 'updraftplus'),
			'weeks' => __('week(s)', 'updraftplus'),
			'forbackupsolderthan' => __('For backups older than', 'updraftplus'),
			'ud_url' => UPDRAFTPLUS_URL,
			'processing' => __('Processing...', 'updraftplus'),
			'pleasefillinrequired' => __('Please fill in the required information.', 'updraftplus'),
			'test_settings' => __('Test %s Settings', 'updraftplus'),
			'testing_settings' => __('Testing %s Settings...', 'updraftplus'),
			'settings_test_result' => __('%s settings test result:', 'updraftplus'),
			'nothing_yet_logged' => __('Nothing yet logged', 'updraftplus'),
			'import_select_file' => __('You have not yet selected a file to import.', 'updraftplus'),
			'import_invalid_json_file' => __('Error: The chosen file is corrupt. Please choose a valid UpdraftPlus export file.', 'updraftplus'),
			'updraft_settings_url' => UpdraftPlus_Options::admin_page_url().'?page=updraftplus',
			'network_site_url' => network_site_url(),
			'importing' => __('Importing...', 'updraftplus'),
			'importing_data_from' => __('This will import data from:', 'updraftplus'),
			'exported_on' => __('Which was exported on:', 'updraftplus'),
			'continue_import' => __('Do you want to carry out the import?', 'updraftplus'),
			'complete' => __('Complete', 'updraftplus'),
			'backup_complete' => __('The backup has finished running', 'updraftplus'),
			'backup_aborted' => __('The backup was aborted', 'updraftplus'),
			'remote_delete_limit' => defined('UPDRAFTPLUS_REMOTE_DELETE_LIMIT') ? UPDRAFTPLUS_REMOTE_DELETE_LIMIT : 15,
			'remote_files_deleted' => __('remote files deleted', 'updraftplus'),
			'http_code' => __('HTTP code:', 'updraftplus'),
			'makesure2' => __('The file failed to upload. Please check the following:', 'updraftplus')."\n\n - ".__('Any settings in your .htaccess or web.config file that affects the maximum upload or post size.', 'updraftplus')."\n - ".__('The available memory on the server.', 'updraftplus')."\n - ".__('That you are attempting to upload a zip file previously created by UpdraftPlus.', 'updraftplus')."\n\n".__('Further information may be found in the browser JavaScript console, and the server PHP error logs.', 'updraftplus'),
			'zip_file_contents' => __('Browsing zip file', 'updraftplus'),
			'zip_file_contents_info' => __('Select a file to view information about it', 'updraftplus'),
			'search' => __('Search', 'updraftplus'),
			'download_timeout' => __('Unable to download file. This could be caused by a timeout. It would be best to download the zip to your computer.', 'updraftplus'),
			'loading_log_file' => __('Loading log file', 'updraftplus'),
			'updraftplus_version' => $updraftplus->version,
			'updraftcentral_wizard_empty_url' => __('Please enter the URL where your UpdraftCentral dashboard is hosted.'),
			'updraftcentral_wizard_invalid_url' => __('Please enter a valid URL e.g http://example.com', 'updraftplus'),
			'export_settings_file_name' => 'updraftplus-settings-'.sanitize_title(get_bloginfo('name')).'.json',
			// For remote storage handlebarsjs template
			'remote_storage_options' => $remote_storage_options_and_templates['options'],
			'remote_storage_templates' => $remote_storage_options_and_templates['templates'],
			'instance_enabled' => __('Currently enabled', 'updraftplus'),
			'instance_disabled' => __('Currently disabled', 'updraftplus'),
			'local_upload_started' => __('Local backup upload has started; please check the log file to see the upload progress', 'updraftplus'),
			'local_upload_error' => __('You must select at least one remote storage destination to upload this backup set to.', 'updraftplus'),
			'already_uploaded' => __('(already uploaded)', 'updraftplus'),
			'onedrive_folder_url_warning' => __('Please specify the Microsoft OneDrive folder name, not the URL.', 'updraftplus'),
			'updraftcentral_cloud' => __('UpdraftCentral Cloud', 'updraftplus'),
			'login_successful' => __('Login successful.', 'updraftplus').' '.__('Please follow this link to open %s in a new window.', 'updraftplus'),
			'registration_successful' => __('Registration successful.', 'updraftplus').' '.__('Please follow this link to open %s in a new window.', 'updraftplus'),
			'username_password_required' => __('Both email and password fields are required.', 'updraftplus'),
			'valid_email_required' => __('An email is required and needs to be in a valid format.', 'updraftplus'),
			'trouble_connecting' => __('Trouble connecting? Try using an alternative method in the advanced security options.', 'updraftplus'),
			'perhaps_login' => __('Perhaps you would want to login instead.', 'updraftplus'),
			'generating_key' => __('Please wait while the system generates and registers an encryption key for your website with UpdraftCentral Cloud.', 'updraftplus'),
			'updraftcentral_cloud_redirect' => __('Please wait while you are redirected to UpdraftCentral Cloud.', 'updraftplus'),
			'data_consent_required' => __('You need to read and accept the UpdraftCentral Cloud data and privacy policies before you can proceed.', 'updraftplus'),
			'close_wizard' => __('You can also close this wizard.', 'updraftplus'),
			'control_udc_connections' => __('For future control of all your UpdraftCentral connections, go to the "Advanced Tools" tab.', 'updraftplus'),
			'main_tabs_keys' => array_keys($main_tabs),
			'clone_version_warning' => __('Warning: you have selected a lower version than your currently installed version. This may fail if you have components that are incompatible with earlier versions.', 'updraftplus'),
			'clone_backup_complete' => __('The clone has been provisioned, and its data has been sent to it. Once the clone has finished deploying it, you will receive an email.', 'updraftplus'),
			'clone_backup_aborted' => __('The preparation of the clone data has been aborted.', 'updraftplus'),
			'current_clean_url' => UpdraftPlus::get_current_clean_url(),
			'exclude_rule_remove_conformation_msg' => __('Are you sure you want to remove this exclusion rule?', 'updraftplus'),
			'exclude_select_file_or_folder_msg' => __('Please select a file/folder which you would like to exclude', 'updraftplus'),
			'exclude_type_ext_msg' => __('Please enter a file extension, like zip', 'updraftplus'),
			'exclude_ext_error_msg' => __('Please enter a valid file extension', 'updraftplus'),
			'exclude_type_prefix_msg' => __('Please enter characters that begin the filename which you would like to exclude', 'updraftplus'),
			'exclude_prefix_error_msg' => __('Please enter a valid file name prefix', 'updraftplus'),
			'duplicate_exclude_rule_error_msg' => __('The exclusion rule which you are trying to add already exists', 'updraftplus'),
			'clone_key_required' => __('UpdraftClone key is required.', 'updraftplus'),
			'files_new_backup' => __('Include your files in the backup', 'updraftplus'),
			'files_incremental_backup' => __('File backup options', 'updraftplus'),
		));
	}
	
	/**
	 * Despite the name, this fires irrespective of what capabilities the user has (even none - so be careful)
	 */
	public function core_upgrade_preamble() {
		// They need to be able to perform backups, and to perform updates
		if (!UpdraftPlus_Options::user_can_manage() || (!current_user_can('update_core') && !current_user_can('update_plugins') && !current_user_can('update_themes'))) return;

		if (!class_exists('UpdraftPlus_Addon_Autobackup')) {
			if (defined('UPDRAFTPLUS_NOADS_B')) return;
		}
		
		?>
		<?php
			if (!class_exists('UpdraftPlus_Addon_Autobackup')) {
				if (!class_exists('UpdraftPlus_Notices')) include_once(UPDRAFTPLUS_DIR.'/includes/updraftplus-notices.php');
				global $updraftplus_notices;
				echo apply_filters('updraftplus_autobackup_blurb', $updraftplus_notices->do_notice('autobackup', 'autobackup', true));
			} else {
				echo '<div class="updraft-ad-container updated" style="display:block;">';
				echo '<h3 style="margin-top: 2px;">'. __('Be safe with an automatic backup', 'updraftplus').'</h3>';
				echo apply_filters('updraftplus_autobackup_blurb', '');
				echo '</div>';
			}
		?>
		<script>
		jQuery(document).ready(function() {
			jQuery('.updraft-ad-container').appendTo('.wrap p:first');
		});
		</script>
		<?php
	}

	/**
	 * Run upon the WP admin_head action
	 */
	public function admin_head() {

		global $pagenow;

		if (UpdraftPlus_Options::admin_page() != $pagenow || !isset($_REQUEST['page']) || 'updraftplus' != $_REQUEST['page'] || !UpdraftPlus_Options::user_can_manage()) return;

		$chunk_size = min(wp_max_upload_size()-1024, 1048576*2);

		// The multiple_queues argument is ignored in plupload 2.x (WP3.9+) - http://make.wordpress.org/core/2014/04/11/plupload-2-x-in-wordpress-3-9/
		// max_file_size is also in filters as of plupload 2.x, but in its default position is still supported for backwards-compatibility. Likewise, our use of filters.extensions below is supported by a backwards-compatibility option (the current way is filters.mime-types.extensions

		$plupload_init = array(
			'runtimes' => 'html5,flash,silverlight,html4',
			'browse_button' => 'plupload-browse-button',
			'container' => 'plupload-upload-ui',
			'drop_element' => 'drag-drop-area',
			'file_data_name' => 'async-upload',
			'multiple_queues' => true,
			'max_file_size' => '100Gb',
			'chunk_size' => $chunk_size.'b',
			'url' => admin_url('admin-ajax.php', 'relative'),
			'multipart' => true,
			'multi_selection' => true,
			'urlstream_upload' => true,
			// additional post data to send to our ajax hook
			'multipart_params' => array(
				'_ajax_nonce' => wp_create_nonce('updraft-uploader'),
				'action' => 'plupload_action'
			)
		);

		// WP 3.9 updated to plupload 2.0 - https://core.trac.wordpress.org/ticket/25663
		if (is_file(ABSPATH.WPINC.'/js/plupload/Moxie.swf')) {
			$plupload_init['flash_swf_url'] = includes_url('js/plupload/Moxie.swf');
		} else {
			$plupload_init['flash_swf_url'] = includes_url('js/plupload/plupload.flash.swf');
		}

		if (is_file(ABSPATH.WPINC.'/js/plupload/Moxie.xap')) {
			$plupload_init['silverlight_xap_url'] = includes_url('js/plupload/Moxie.xap');
		} else {
			$plupload_init['silverlight_xap_url'] = includes_url('js/plupload/plupload.silverlight.swf');
		}

		?><script>
			var updraft_credentialtest_nonce = '<?php echo wp_create_nonce('updraftplus-credentialtest-nonce');?>';
			var updraftplus_settings_nonce = '<?php echo wp_create_nonce('updraftplus-settings-nonce');?>';
			var updraft_siteurl = '<?php echo esc_js(site_url('', 'relative'));?>';
			var updraft_plupload_config = <?php echo json_encode($plupload_init); ?>;
			var updraft_download_nonce = '<?php echo wp_create_nonce('updraftplus_download');?>';
			var updraft_accept_archivename = <?php echo apply_filters('updraftplus_accept_archivename_js', "[]");?>;
			<?php
			$plupload_init['browse_button'] = 'plupload-browse-button2';
			$plupload_init['container'] = 'plupload-upload-ui2';
			$plupload_init['drop_element'] = 'drag-drop-area2';
			$plupload_init['multipart_params']['action'] = 'plupload_action2';
			$plupload_init['filters'] = array(array('title' => __('Allowed Files'), 'extensions' => 'crypt'));
			?>
			var updraft_plupload_config2 = <?php echo json_encode($plupload_init); ?>;
			var updraft_downloader_nonce = '<?php wp_create_nonce("updraftplus_download"); ?>'
			<?php
				$overdue = $this->howmany_overdue_crons();
				if ($overdue >= 4) {
					?>
					jQuery(document).ready(function() {
						setTimeout(function(){ updraft_check_overduecrons(); }, 11000);
					});
				<?php } ?>
		</script>
		<?php
	}

	/**
	 * Check if available disk space is at least the specified number of bytes
	 *
	 * @param Integer $space - number of bytes
	 *
	 * @return Integer|Boolean - true or false to indicate if available; of -1 if the result is unknown
	 */
	private function disk_space_check($space) {
		// Allow checking by some other means (user request)
		if (null !== ($filtered_result = apply_filters('updraftplus_disk_space_check', null, $space))) return $filtered_result;
		global $updraftplus;
		$updraft_dir = $updraftplus->backups_dir_location();
		$disk_free_space = @disk_free_space($updraft_dir);
		if (false == $disk_free_space) return -1;
		return ($disk_free_space > $space) ? true : false;
	}

	/**
	 * Adds the settings link under the plugin on the plugin screen.
	 *
	 * @param  Array  $links Set of links for the plugin, before being filtered
	 * @param  String $file  File name (relative to the plugin directory)
	 * @return Array filtered results
	 */
	public function plugin_action_links($links, $file) {
		if (is_array($links) && 'updraftplus/updraftplus.php' == $file) {
			$settings_link = '<a href="'.UpdraftPlus_Options::admin_page_url().'?page=updraftplus" class="js-updraftplus-settings">'.__("Settings", "updraftplus").'</a>';
			array_unshift($links, $settings_link);
			$settings_link = '<a href="'.apply_filters('updraftplus_com_link', "https://updraftplus.com/").'">'.__("Add-Ons / Pro Support", "updraftplus").'</a>';
			array_unshift($links, $settings_link);
		}
		return $links;
	}

	public function admin_action_upgrade_pluginortheme() {
		if (isset($_GET['action']) && ('upgrade-plugin' == $_GET['action'] || 'upgrade-theme' == $_GET['action']) && !class_exists('UpdraftPlus_Addon_Autobackup') && !defined('UPDRAFTPLUS_NOADS_B')) {

			if ('upgrade-plugin' == $_GET['action']) {
				if (!current_user_can('update_plugins')) return;
			} else {
				if (!current_user_can('update_themes')) return;
			}

			$dismissed_until = UpdraftPlus_Options::get_updraft_option('updraftplus_dismissedautobackup', 0);
			if ($dismissed_until > time()) return;

			if ('upgrade-plugin' == $_GET['action']) {
				$title = __('Update Plugin');
				$parent_file = 'plugins.php';
				$submenu_file = 'plugins.php';
			} else {
				$title = __('Update Theme');
				$parent_file = 'themes.php';
				$submenu_file = 'themes.php';
			}

			include_once(ABSPATH.'wp-admin/admin-header.php');
			
			if (!class_exists('UpdraftPlus_Notices')) include_once(UPDRAFTPLUS_DIR.'/includes/updraftplus-notices.php');
			global $updraftplus_notices;
			$updraftplus_notices->do_notice('autobackup', 'autobackup');
		}
	}

	public function show_admin_warning($message, $class = 'updated') {
		echo '<div class="updraftmessage '.$class.'">'."<p>$message</p></div>";
	}

	public function show_admin_warning_multiple_storage_options() {
		$this->show_admin_warning('<strong>UpdraftPlus:</strong> '.__('An error occurred when fetching storage module options: ', 'updraftplus').htmlspecialchars($this->storage_module_option_errors), 'error');
	}

	public function show_admin_warning_unwritable() {
		// One of the translators has erroneously changed "Backup" into "Back up" (which means, "reverse" !)
		$unwritable_mess = htmlspecialchars(str_ireplace('Back Up', 'Backup', __("The 'Backup Now' button is disabled as your backup directory is not writable (go to the 'Settings' tab and find the relevant option).", 'updraftplus')));
		$this->show_admin_warning($unwritable_mess, "error");
	}
	
	public function show_admin_nosettings_warning() {
		$this->show_admin_warning('<strong>'.__('Welcome to UpdraftPlus!', 'updraftplus').'</strong> '.str_ireplace('Back Up', 'Backup', __('To make a backup, just press the Backup Now button.', 'updraftplus')).' <a href="'.UpdraftPlus::get_current_clean_url().'" id="updraft-navtab-settings2">'.__('To change any of the default settings of what is backed up, to configure scheduled backups, to send your backups to remote storage (recommended), and more, go to the settings tab.', 'updraftplus').'</a>', 'updated notice is-dismissible');
	}

	public function show_admin_warning_execution_time() {
		$this->show_admin_warning('<strong>'.__('Warning', 'updraftplus').':</strong> '.sprintf(__('The amount of time allowed for WordPress plugins to run is very low (%s seconds) - you should increase it to avoid backup failures due to time-outs (consult your web hosting company for more help - it is the max_execution_time PHP setting; the recommended value is %s seconds or more)', 'updraftplus'), (int) @ini_get('max_execution_time'), 90));
	}

	public function show_admin_warning_disabledcron() {
		$this->show_admin_warning('<strong>'.__('Warning', 'updraftplus').':</strong> '.__('The scheduler is disabled in your WordPress install, via the DISABLE_WP_CRON setting. No backups can run (even &quot;Backup Now&quot;) unless either you have set up a facility to call the scheduler manually, or until it is enabled.', 'updraftplus').' <a href="'.apply_filters('updraftplus_com_link', "https://updraftplus.com/faqs/my-scheduled-backups-and-pressing-backup-now-does-nothing-however-pressing-debug-backup-does-produce-a-backup/#disablewpcron/").'">'.__('Go here for more information.', 'updraftplus').'</a>', 'updated updraftplus-disable-wp-cron-warning');
	}

	public function show_admin_warning_diskspace() {
		$this->show_admin_warning('<strong>'.__('Warning', 'updraftplus').':</strong> '.sprintf(__('You have less than %s of free disk space on the disk which UpdraftPlus is configured to use to create backups. UpdraftPlus could well run out of space. Contact your the operator of your server (e.g. your web hosting company) to resolve this issue.', 'updraftplus'), '35 MB'));
	}

	public function show_admin_warning_wordpressversion() {
		$this->show_admin_warning('<strong>'.__('Warning', 'updraftplus').':</strong> '.sprintf(__('UpdraftPlus does not officially support versions of WordPress before %s. It may work for you, but if it does not, then please be aware that no support is available until you upgrade WordPress.', 'updraftplus'), '3.2'));
	}

	public function show_admin_warning_litespeed() {
		$this->show_admin_warning('<strong>'.__('Warning', 'updraftplus').':</strong> '.sprintf(__('Your website is hosted using the %s web server.', 'updraftplus'), 'LiteSpeed').' <a href="'.apply_filters('updraftplus_com_link', "https://updraftplus.com/faqs/i-am-having-trouble-backing-up-and-my-web-hosting-company-uses-the-litespeed-webserver/").'">'.__('Please consult this FAQ if you have problems backing up.', 'updraftplus').'</a>');
	}

	public function show_admin_debug_warning() {
		$this->show_admin_warning('<strong>'.__('Notice', 'updraftplus').':</strong> '.__('UpdraftPlus\'s debug mode is on. You may see debugging notices on this page not just from UpdraftPlus, but from any other plugin installed. Please try to make sure that the notice you are seeing is from UpdraftPlus before you raise a support request.', 'updraftplus').'</a>');
	}

	public function show_admin_warning_overdue_crons($howmany) {
		$ret = '<div class="updraftmessage updated"><p>';
		$ret .= '<strong>'.__('Warning', 'updraftplus').':</strong> '.sprintf(__('WordPress has a number (%d) of scheduled tasks which are overdue. Unless this is a development site, this probably means that the scheduler in your WordPress install is not working.', 'updraftplus'), $howmany).' <a href="'.apply_filters('updraftplus_com_link', "https://updraftplus.com/faqs/scheduler-wordpress-installation-working/").'">'.__('Read this page for a guide to possible causes and how to fix it.', 'updraftplus').'</a>';
		$ret .= '</p></div>';
		return $ret;
	}

	/**
	 * Output authorisation links for any un-authorised Dropbox settings instances
	 */
	public function show_admin_warning_dropbox() {
		$this->get_method_auth_link('dropbox');
	}

	/**
	 * Output authorisation links for any un-authorised OneDrive settings instances
	 */
	public function show_admin_warning_onedrive() {
		$this->get_method_auth_link('onedrive');
	}

	public function show_admin_warning_updraftvault() {
		$this->show_admin_warning('<strong>'.__('UpdraftPlus notice:', 'updraftplus').'</strong> '.sprintf(__('%s has been chosen for remote storage, but you are not currently connected.', 'updraftplus'), 'UpdraftPlus Vault').' '.__('Go to the remote storage settings in order to connect.', 'updraftplus'), 'updated');
	}

	/**
	 * Output authorisation links for any un-authorised Google Drive settings instances
	 */
	public function show_admin_warning_googledrive() {
		$this->get_method_auth_link('googledrive');
	}

	/**
	 * Output authorisation links for any un-authorised Google Cloud settings instances
	 */
	public function show_admin_warning_googlecloud() {
		$this->get_method_auth_link('googlecloud');
	}
	
	/**
	 * Show DreamObjects cluster migration warning
	 */
	public function show_admin_warning_dreamobjects() {
		$this->show_admin_warning('<strong>'.__('UpdraftPlus notice:', 'updraftplus').'</strong> '.sprintf(__('The %s endpoint is scheduled to shut down on the 1st October 2018. You will need to switch to a different end-point and migrate your data before that date. %sPlease see this article for more information%s'), 'objects-us-west-1.dream.io', '<a href="https://help.dreamhost.com/hc/en-us/articles/360002135871-Cluster-migration-procedure" target="_blank">', '</a>'), 'updated');
	}

	/**
	 * This method will setup the storage object and get the authentication link ready to be output with the notice
	 *
	 * @param  String $method - the remote storage method
	 */
	public function get_method_auth_link($method) {
		global $updraftplus;

		$storage_objects_and_ids = UpdraftPlus_Storage_Methods_Interface::get_storage_objects_and_ids(array($method));

		$object = $storage_objects_and_ids[$method]['object'];

		foreach ($this->auth_instance_ids[$method] as $instance_id) {
			
			$object->set_instance_id($instance_id);

			$this->show_admin_warning('<strong>'.__('UpdraftPlus notice:', 'updraftplus').'</strong> '.$object->get_authentication_link(false, false), 'updated updraft_authenticate_'.$method);
		}
	}

	/**
	 * Start a download of a backup. This method is called via the AJAX action updraft_download_backup. May die instead of returning depending upon the mode in which it is called.
	 */
	public function updraft_download_backup() {
		try {
			if (empty($_REQUEST['_wpnonce']) || !wp_verify_nonce($_REQUEST['_wpnonce'], 'updraftplus_download')) die;
	
			if (empty($_REQUEST['timestamp']) || !is_numeric($_REQUEST['timestamp']) || empty($_REQUEST['type'])) exit;
	
			$findexes = empty($_REQUEST['findex']) ? array(0) : $_REQUEST['findex'];
			$stage = empty($_REQUEST['stage']) ? '' : $_REQUEST['stage'];
			$file_path = empty($_REQUEST['filepath']) ? '' : $_REQUEST['filepath'];
	
			// This call may not actually return, depending upon what mode it is called in
			$result = $this->do_updraft_download_backup($findexes, $_REQUEST['type'], $_REQUEST['timestamp'], $stage, false, $file_path);
			
			// In theory, if a response was already sent, then Connection: close has been issued, and a Content-Length. However, in https://updraftplus.com/forums/topic/pclzip_err_bad_format-10-invalid-archive-structure/ a browser ignores both of these, and then picks up the second output and complains.
			if (empty($result['already_closed'])) echo json_encode($result);
		} catch (Exception $e) {
			$log_message = 'PHP Fatal Exception error ('.get_class($e).') has occurred during download backup. Error Message: '.$e->getMessage().' (Code: '.$e->getCode().', line '.$e->getLine().' in '.$e->getFile().')';
			error_log($log_message);
			echo json_encode(array(
				'fatal_error' => true,
				'fatal_error_message' => $log_message
			));
		// @codingStandardsIgnoreLine
		} catch (Error $e) {
			$log_message = 'PHP Fatal error ('.get_class($e).') has occurred during download backup. Error Message: '.$e->getMessage().' (Code: '.$e->getCode().', line '.$e->getLine().' in '.$e->getFile().')';
			error_log($log_message);
			echo json_encode(array(
				'fatal_error' => true,
				'fatal_error_message' => $log_message
			));
		}
		die();
	}
	
	/**
	 * Ensure that a specified backup is present, downloading if necessary (or delete it, if the parameters so indicate). N.B. This function may die(), depending on the request being made in $stage
	 *
	 * @param Array            $findexes                  - the index number of the backup archive requested
	 * @param String           $type                      - the entity type (e.g. 'plugins') being requested
	 * @param Integer          $timestamp                 - identifier for the backup being requested (UNIX epoch time)
	 * @param Mixed            $stage                     - the stage; valid values include (have not audited for other possibilities) at least 'delete' and 2.
	 * @param Callable|Boolean $close_connection_callable - function used to close the connection to the caller; an array of data to return is passed. If false, then UpdraftPlus::close_browser_connection is called with a JSON version of the data.
	 * @param String           $file_path                 - an over-ride for where to download the file to (basename only)
	 *
	 * @return Array - sumary of the results. May also just die.
	 */
	public function do_updraft_download_backup($findexes, $type, $timestamp, $stage, $close_connection_callable = false, $file_path = '') {

		@set_time_limit(UPDRAFTPLUS_SET_TIME_LIMIT);

		global $updraftplus;
		
		if (!is_array($findexes)) $findexes = array($findexes);

		$connection_closed = false;

		// Check that it is a known entity type; if not, die
		if ('db' != substr($type, 0, 2)) {
			$backupable_entities = $updraftplus->get_backupable_file_entities(true);
			foreach ($backupable_entities as $t => $info) {
				if ($type == $t) $type_match = true;
			}
			if (empty($type_match)) return array('result' => 'error', 'code' => 'no_such_type');
		}

		$debug_mode = UpdraftPlus_Options::get_updraft_option('updraft_debug_mode');

		// Retrieve the information from our backup history
		$backup_history = UpdraftPlus_Backup_History::get_history();

		foreach ($findexes as $findex) {
			// This is a bit ugly; these variables get placed back into $_POST (where they may possibly have come from), so that UpdraftPlus::log() can detect exactly where to log the download status.
			$_POST['findex'] = $findex;
			$_POST['type'] = $type;
			$_POST['timestamp'] = $timestamp;

			// We already know that no possible entities have an MD5 clash (even after 2 characters)
			// Also, there's nothing enforcing a requirement that nonces are hexadecimal
			$job_nonce = dechex($timestamp).$findex.substr(md5($type), 0, 3);

			// You need a nonce before you can set job data. And we certainly don't yet have one.
			$updraftplus->backup_time_nonce($job_nonce);

			// Set the job type before logging, as there can be different logging destinations
			$updraftplus->jobdata_set('job_type', 'download');
			$updraftplus->jobdata_set('job_time_ms', $updraftplus->job_time_ms);

			// Base name
			$file = $backup_history[$timestamp][$type];

			// Deal with multi-archive sets
			if (is_array($file)) $file = $file[$findex];

			if (false !== strpos($file_path, '..')) {
				error_log("UpdraftPlus_Admin::do_updraft_download_backup : invalid file_path: $file_path");
				return array('result' => __('Error: invalid path', 'updraftplus'));
			}

			if (!empty($file_path)) $file = $file_path;

			// Where it should end up being downloaded to
			$fullpath = $updraftplus->backups_dir_location().'/'.$file;

			if (!empty($file_path) && strpos(realpath($fullpath), realpath($updraftplus->backups_dir_location())) === false) {
				error_log("UpdraftPlus_Admin::do_updraft_download_backup : invalid fullpath: $fullpath");
				return array('result' => __('Error: invalid path', 'updraftplus'));
			}

			if (2 == $stage) {
				$updraftplus->spool_file($fullpath);
				// We only want to remove if it was a temp file from the zip browser
				if (!empty($file_path)) @unlink($fullpath);
				// Do not return - we do not want the caller to add any output
				die;
			}

			if ('delete' == $stage) {
				@unlink($fullpath);
				$updraftplus->log("The file has been deleted ($file)");
				return array('result' => 'deleted');
			}

			// TODO: FIXME: Failed downloads may leave log files forever (though they are small)
			if ($debug_mode) $updraftplus->logfile_open($updraftplus->nonce);

			set_error_handler(array($updraftplus, 'php_error'), E_ALL & ~E_STRICT);

			$updraftplus->log("Requested to obtain file: timestamp=$timestamp, type=$type, index=$findex");

			$itext = empty($findex) ? '' : $findex;
			$known_size = isset($backup_history[$timestamp][$type.$itext.'-size']) ? $backup_history[$timestamp][$type.$itext.'-size'] : 0;

			$services = isset($backup_history[$timestamp]['service']) ? $backup_history[$timestamp]['service'] : false;
			if (is_string($services)) $services = array($services);

			$updraftplus->jobdata_set('service', $services);

			// Fetch it from the cloud, if we have not already got it

			$needs_downloading = false;

			if (!file_exists($fullpath)) {
				// If the file doesn't exist and they're using one of the cloud options, fetch it down from the cloud.
				$needs_downloading = true;
				$updraftplus->log('File does not yet exist locally - needs downloading');
			} elseif ($known_size > 0 && filesize($fullpath) < $known_size) {
				$updraftplus->log("The file was found locally (".filesize($fullpath).") but did not match the size in the backup history ($known_size) - will resume downloading");
				$needs_downloading = true;
			} elseif ($known_size > 0 && filesize($fullpath) > $known_size) {
				$updraftplus->log("The file was found locally (".filesize($fullpath).") but the size is larger than what is recorded in the backup history ($known_size) - will try to continue but if errors are encountered then check that the backup is correct");
			} elseif ($known_size > 0) {
				$updraftplus->log('The file was found locally and matched the recorded size from the backup history ('.round($known_size/1024, 1).' KB)');
			} else {
				$updraftplus->log('No file size was found recorded in the backup history. We will assume the local one is complete.');
				$known_size = filesize($fullpath);
			}
			
			// The AJAX responder that updates on progress wants to see this
			$updraftplus->jobdata_set('dlfile_'.$timestamp.'_'.$type.'_'.$findex, "downloading:$known_size:$fullpath");

			if ($needs_downloading) {

				// Update the "last modified" time to dissuade any other instances from thinking that no downloaders are active
				@touch($fullpath);

				$msg = array(
					'result' => 'needs_download',
					'request' => array(
						'type' => $type,
						'timestamp' => $timestamp,
						'findex' => $findex
					)
				);
			
				if ($close_connection_callable && is_callable($close_connection_callable) && !$connection_closed) {
					$connection_closed = true;
					call_user_func($close_connection_callable, $msg);
				} elseif (!$connection_closed) {
					$connection_closed = true;
					$updraftplus->close_browser_connection(json_encode($msg));
				}
				UpdraftPlus_Storage_Methods_Interface::get_remote_file($services, $file, $timestamp);
			}

			// Now, be ready to spool the thing to the browser
			if (is_file($fullpath) && is_readable($fullpath)) {

				// That message is then picked up by the AJAX listener
				$updraftplus->jobdata_set('dlfile_'.$timestamp.'_'.$type.'_'.$findex, 'downloaded:'.filesize($fullpath).":$fullpath");

				$result = 'downloaded';
				
			} else {

				$updraftplus->jobdata_set('dlfile_'.$timestamp.'_'.$type.'_'.$findex, 'failed');
				$updraftplus->jobdata_set('dlerrors_'.$timestamp.'_'.$type.'_'.$findex, $updraftplus->errors);
				$updraftplus->log('Remote fetch failed. File '.$fullpath.' did not exist or was unreadable. If you delete local backups then remote retrieval may have failed.');
				
				$result = 'download_failed';
			}

			restore_error_handler();

			@fclose($updraftplus->logfile_handle);
			if (!$debug_mode) @unlink($updraftplus->logfile_name);
		}

		// The browser connection was possibly already closed, but not necessarily
		return array('result' => $result, 'already_closed' => $connection_closed);
	}

	/**
	 * This is used as a callback
	 *
	 * @param  Mixed $msg The data to be JSON encoded and sent back
	 */
	public function _updraftplus_background_operation_started($msg) {
		global $updraftplus;
		// The extra spaces are because of a bug seen on one server in handling of non-ASCII characters; see HS#11739
		$updraftplus->close_browser_connection(json_encode($msg).'        ');
	}
	
	public function updraft_ajax_handler() {

		global $updraftplus;

		$nonce = empty($_REQUEST['nonce']) ? '' : $_REQUEST['nonce'];

		if (!wp_verify_nonce($nonce, 'updraftplus-credentialtest-nonce') || empty($_REQUEST['subaction'])) die('Security check');

		$subaction = $_REQUEST['subaction'];
		// Mitigation in case the nonce leaked to an unauthorised user
		if ('dismissautobackup' == $subaction) {
			if (!current_user_can('update_plugins') && !current_user_can('update_themes')) return;
		} elseif ('dismissexpiry' == $subaction || 'dismissdashnotice' == $subaction) {
			if (!current_user_can('update_plugins')) return;
		} else {
			if (!UpdraftPlus_Options::user_can_manage()) return;
		}
		
		// All others use _POST
		$data_in_get = array('get_log', 'get_fragment');
		
		// UpdraftPlus_WPAdmin_Commands extends UpdraftPlus_Commands - i.e. all commands are in there
		if (!class_exists('UpdraftPlus_WPAdmin_Commands')) include_once(UPDRAFTPLUS_DIR.'/includes/class-wpadmin-commands.php');
		$commands = new UpdraftPlus_WPAdmin_Commands($this);
		
		if (method_exists($commands, $subaction)) {

			$data = in_array($subaction, $data_in_get) ? $_GET : $_POST;
			
			// Undo WP's slashing of GET/POST data
			$data = UpdraftPlus_Manipulation_Functions::wp_unslash($data);
			
			// TODO: Once all commands come through here and through updraft_send_command(), the data should always come from this attribute (once updraft_send_command() is modified appropriately).
			if (isset($data['action_data'])) $data = $data['action_data'];
			try {
				$results = call_user_func(array($commands, $subaction), $data);
			} catch (Exception $e) {
				$log_message = 'PHP Fatal Exception error ('.get_class($e).') has occurred during '.$subaction.' subaction. Error Message: '.$e->getMessage().' (Code: '.$e->getCode().', line '.$e->getLine().' in '.$e->getFile().')';
				error_log($log_message);
				echo json_encode(array(
					'fatal_error' => true,
					'fatal_error_message' => $log_message
				));
				die;
			// @codingStandardsIgnoreLine
			} catch (Error $e) {
				$log_message = 'PHP Fatal error ('.get_class($e).') has occurred during '.$subaction.' subaction. Error Message: '.$e->getMessage().' (Code: '.$e->getCode().', line '.$e->getLine().' in '.$e->getFile().')';
				error_log($log_message);
				echo json_encode(array(
					'fatal_error' => true,
					'fatal_error_message' => $log_message
				));
				die;
			}
			if (is_wp_error($results)) {
				$results = array(
					'result' => false,
					'error_code' => $results->get_error_code(),
					'error_message' => $results->get_error_message(),
					'error_data' => $results->get_error_data(),
				);
			}
			
			if (is_string($results)) {
				// A handful of legacy methods, and some which are directly the source for iframes, for which JSON is not appropriate.
				echo $results;
			} else {
				echo json_encode($results);
			}
			die;
		}
		
		// Below are all the commands not ported over into class-commands.php or class-wpadmin-commands.php

		if ('activejobs_list' == $subaction) {
			try {
				// N.B. Also called from autobackup.php
				// TODO: This should go into UpdraftPlus_Commands, once the add-ons have been ported to use updraft_send_command()
				echo json_encode($this->get_activejobs_list(UpdraftPlus_Manipulation_Functions::wp_unslash($_GET)));
			} catch (Exception $e) {
				$log_message = 'PHP Fatal Exception error ('.get_class($e).') has occurred during get active job list. Error Message: '.$e->getMessage().' (Code: '.$e->getCode().', line '.$e->getLine().' in '.$e->getFile().')';
				error_log($log_message);
				echo json_encode(array(
					'fatal_error' => true,
					'fatal_error_message' => $log_message
				));
			// @codingStandardsIgnoreLine
			} catch (Error $e) {
				$log_message = 'PHP Fatal error ('.get_class($e).') has occurred during get active job list. Error Message: '.$e->getMessage().' (Code: '.$e->getCode().', line '.$e->getLine().' in '.$e->getFile().')';
				error_log($log_message);
				echo json_encode(array(
					'fatal_error' => true,
					'fatal_error_message' => $log_message
				));
			}
			
		} elseif ('httpget' == $subaction) {
			try {
				// httpget
				$curl = empty($_REQUEST['curl']) ? false : true;
				echo $this->http_get(UpdraftPlus_Manipulation_Functions::wp_unslash($_REQUEST['uri']), $curl);
			// @codingStandardsIgnoreLine
			} catch (Error $e) {
				$log_message = 'PHP Fatal error ('.get_class($e).') has occurred during http get. Error Message: '.$e->getMessage().' (Code: '.$e->getCode().', line '.$e->getLine().' in '.$e->getFile().')';
				error_log($log_message);
				echo json_encode(array(
					'fatal_error' => true,
					'fatal_error_message' => $log_message
				));
			} catch (Exception $e) {
				$log_message = 'PHP Fatal Exception error ('.get_class($e).') has occurred during http get. Error Message: '.$e->getMessage().' (Code: '.$e->getCode().', line '.$e->getLine().' in '.$e->getFile().')';
				error_log($log_message);
				echo json_encode(array(
					'fatal_error' => true,
					'fatal_error_message' => $log_message
				));
			}
			 
		} elseif ('doaction' == $subaction && !empty($_REQUEST['subsubaction']) && 'updraft_' == substr($_REQUEST['subsubaction'], 0, 8)) {
			$subsubaction = $_REQUEST['subsubaction'];
			try {
					// These generally echo and die - they will need further work to port to one of the command classes. Some may already have equivalents in UpdraftPlus_Commands, if they are used from UpdraftCentral.
				do_action(UpdraftPlus_Manipulation_Functions::wp_unslash($subsubaction), $_REQUEST);
			} catch (Exception $e) {
				$log_message = 'PHP Fatal Exception error ('.get_class($e).') has occurred during doaction subaction with '.$subsubaction.' subsubaction. Error Message: '.$e->getMessage().' (Code: '.$e->getCode().', line '.$e->getLine().' in '.$e->getFile().')';
				error_log($log_message);
				echo json_encode(array(
					'fatal_error' => true,
					'fatal_error_message' => $log_message
				));
				die;
			// @codingStandardsIgnoreLine
			} catch (Error $e) {
				$log_message = 'PHP Fatal error ('.get_class($e).') has occurred during doaction subaction with '.$subsubaction.' subsubaction. Error Message: '.$e->getMessage().' (Code: '.$e->getCode().', line '.$e->getLine().' in '.$e->getFile().')';
				error_log($log_message);
				echo json_encode(array(
					'fatal_error' => true,
					'fatal_error_message' => $log_message
				));
				die;
			}
		} else {
			// These can be removed after a few releases
			include(UPDRAFTPLUS_DIR.'/includes/deprecated-actions.php');
		}
		
		die;

	}
	
	/**
	 * Run a credentials test for the indicated remote storage module
	 *
	 * @param Array   $test_settings          The test parameters, including the method itself indicated in the key 'method'
	 * @param Boolean $return_instead_of_echo Whether to return or echo the results. N.B. More than just the results to echo will be returned
	 * @return Array|Void - the results, if they are being returned (rather than echoed). Keys: 'output' (the output), 'data' (other data)
	 */
	public function do_credentials_test($test_settings, $return_instead_of_echo = false) {
	
		$method = (!empty($test_settings['method']) && preg_match("/^[a-z0-9]+$/", $test_settings['method'])) ? $test_settings['method'] : "";
		
		$objname = "UpdraftPlus_BackupModule_$method";
		
		$this->logged = array();
		// TODO: Add action for WP HTTP SSL stuff
		set_error_handler(array($this, 'get_php_errors'), E_ALL & ~E_STRICT);
		
		if (!class_exists($objname)) include_once(UPDRAFTPLUS_DIR."/methods/$method.php");

		$ret = '';
		$data = null;
		
		// TODO: Add action for WP HTTP SSL stuff
		if (method_exists($objname, "credentials_test")) {
			$obj = new $objname;
			if ($return_instead_of_echo) ob_start();
			$data = $obj->credentials_test($test_settings);
			if ($return_instead_of_echo) $ret .= ob_get_clean();
		}
		
		if (count($this->logged) >0) {
			$ret .= "\n\n".__('Messages:', 'updraftplus')."\n";
			foreach ($this->logged as $err) {
				$ret .= "* $err\n";
			}
			if (!$return_instead_of_echo) echo $ret;
		}
		restore_error_handler();
		
		if ($return_instead_of_echo) return array('output' => $ret, 'data' => $data);
		
	}
	
	/**
	 * Delete a backup set, whilst respecting limits on how much to delete in one go
	 *
	 * @uses remove_backup_set_cleanup()
	 * @param Array $opts - deletion options; with keys backup_timestamp, delete_remote, [remote_delete_limit]
	 * @return Array - as from remove_backup_set_cleanup()
	 */
	public function delete_set($opts) {
		
		global $updraftplus;
		
		$backups = UpdraftPlus_Backup_History::get_history();
		$timestamps = (string) $opts['backup_timestamp'];

		$remote_delete_limit = (isset($opts['remote_delete_limit']) && $opts['remote_delete_limit'] > 0) ? (int) $opts['remote_delete_limit'] : PHP_INT_MAX;
		
		$timestamps = explode(',', $timestamps);
		$deleted_timestamps = '';
		$delete_remote = empty($opts['delete_remote']) ? false : true;

		// You need a nonce before you can set job data. And we certainly don't yet have one.
		$updraftplus->backup_time_nonce();
		// Set the job type before logging, as there can be different logging destinations
		$updraftplus->jobdata_set('job_type', 'delete');
		$updraftplus->jobdata_set('job_time_ms', $updraftplus->job_time_ms);

		if (UpdraftPlus_Options::get_updraft_option('updraft_debug_mode')) {
			$updraftplus->logfile_open($updraftplus->nonce);
			set_error_handler(array($updraftplus, 'php_error'), E_ALL & ~E_STRICT);
		}

		$updraft_dir = $updraftplus->backups_dir_location();
		$backupable_entities = $updraftplus->get_backupable_file_entities(true, true);

		$local_deleted = 0;
		$remote_deleted = 0;
		$sets_removed = 0;

		foreach ($timestamps as $i => $timestamp) {

			if (!isset($backups[$timestamp])) {
				return array('result' => 'error', 'message' => __('Backup set not found', 'updraftplus'));
			}

			$nonce = isset($backups[$timestamp]['nonce']) ? $backups[$timestamp]['nonce'] : '';

			$delete_from_service = array();

			if ($delete_remote) {
				// Locate backup set
				if (isset($backups[$timestamp]['service'])) {
					// Convert to an array so that there is no uncertainty about how to process it
					$services = is_string($backups[$timestamp]['service']) ? array($backups[$timestamp]['service']) : $backups[$timestamp]['service'];
					if (is_array($services)) {
						foreach ($services as $service) {
							if ($service && 'none' != $service && 'email' != $service) $delete_from_service[] = $service;
						}
					}
				}
			}

			$files_to_delete = array();
			foreach ($backupable_entities as $key => $ent) {
				if (isset($backups[$timestamp][$key])) {
					$files_to_delete[$key] = $backups[$timestamp][$key];
				}
			}
			// Delete DB
			foreach ($backups[$timestamp] as $key => $value) {
				if ('db' == strtolower(substr($key, 0, 2)) && '-size' != substr($key, -5, 5)) {
					$files_to_delete[$key] = $backups[$timestamp][$key];
				}
			}

			// Also delete the log
			if ($nonce && !UpdraftPlus_Options::get_updraft_option('updraft_debug_mode')) {
				$files_to_delete['log'] = "log.$nonce.txt";
			}
			
			$updraftplus->register_wp_http_option_hooks();

			foreach ($files_to_delete as $key => $files) {

				if (is_string($files)) {
					$was_string = true;
					$files = array($files);
				} else {
					$was_string = false;
				}

				foreach ($files as $file) {
					if (is_file($updraft_dir.'/'.$file) && @unlink($updraft_dir.'/'.$file)) $local_deleted++;
				}

				if ('log' != $key && count($delete_from_service) > 0) {

					$storage_objects_and_ids = UpdraftPlus_Storage_Methods_Interface::get_storage_objects_and_ids($delete_from_service);

					foreach ($delete_from_service as $service) {
					
						if ('email' == $service || 'none' == $service || !$service) continue;

						$deleted = -1;

						$remote_obj = $storage_objects_and_ids[$service]['object'];

						$instance_settings = $storage_objects_and_ids[$service]['instance_settings'];
						$this->backups_instance_ids = empty($backups[$timestamp]['service_instance_ids'][$service]) ? array() : $backups[$timestamp]['service_instance_ids'][$service];

						if (empty($instance_settings)) continue;
						
						uksort($instance_settings, array($this, 'instance_ids_sort'));

						foreach ($instance_settings as $instance_id => $options) {

							$remote_obj->set_options($options, false, $instance_id);

							foreach ($files as $index => $file) {
								if ($remote_deleted == $remote_delete_limit) {
									$timestamps_list = implode(',', $timestamps);

									return $this->remove_backup_set_cleanup(false, $backups, $local_deleted, $remote_deleted, $sets_removed, $timestamps_list, $deleted_timestamps);
								}

								$deleted = $remote_obj->delete($file);
								
								if (-1 === $deleted) {
									// echo __('Did not know how to delete from this cloud service.', 'updraftplus');
								} elseif (false !== $deleted) {
									$remote_deleted++;
								}
								
								$itext = $index ? (string) $index : '';
								if ($was_string) {
									unset($backups[$timestamp][$key]);
									if ('db' == strtolower(substr($key, 0, 2))) unset($backups[$timestamp][$key][$index.'-size']);
								} else {
									unset($backups[$timestamp][$key][$index]);
									unset($backups[$timestamp][$key.$itext.'-size']);
									if (empty($backups[$timestamp][$key])) unset($backups[$timestamp][$key]);
								}
								if (isset($backups[$timestamp]['checksums']) && is_array($backups[$timestamp]['checksums'])) {
									foreach (array_keys($backups[$timestamp]['checksums']) as $algo) {
										unset($backups[$timestamp]['checksums'][$algo][$key.$index]);
									}
								}
								
								// If we don't save the array back, then the above section will fire again for the same files - and the remote storage will be requested to delete already-deleted files, which then means no time is actually saved by the browser-backend loop method.
								UpdraftPlus_Backup_History::save_history($backups);
							}
						}
					}
				}
			}

			unset($backups[$timestamp]);
			unset($timestamps[$i]);
			if ('' != $deleted_timestamps) $deleted_timestamps .= ',';
			$deleted_timestamps .= $timestamp;
			UpdraftPlus_Backup_History::save_history($backups);
			$sets_removed++;
		}

		$timestamps_list = implode(',', $timestamps);

		return $this->remove_backup_set_cleanup(true, $backups, $local_deleted, $remote_deleted, $sets_removed, $timestamps_list, $deleted_timestamps);

	}

	/**
	 * This function sorts the array of instance ids currently saved so that any instance id that is in both the saved settings and the backup history move to the top of the array, as these are likely to work. Then values that don't appear in the backup history move to the bottom.
	 *
	 * @param  String $a - the first instance id
	 * @param  String $b - the second instance id
	 * @return Integer   - returns an integer to indicate what position the $b value should be moved in
	 */
	public function instance_ids_sort($a, $b) {
		if (in_array($a, $this->backups_instance_ids)) {
			if (in_array($b, $this->backups_instance_ids)) return 0;
			return -1;
		}
		return in_array($b, $this->backups_instance_ids) ? 1 : 0;
	}

	/**
	 * Called by self::delete_set() to finish up before returning (whether the complete deletion is finished or not)
	 *
	 * @param Boolean $delete_complete    - whether the whole set is now gone (i.e. last round)
	 * @param Array	  $backups            - the backup history
	 * @param Integer $local_deleted      - how many backup archives were deleted from local storage
	 * @param Integer $remote_deleted     - how many backup archives were deleted from remote storage
	 * @param Integer $sets_removed       - how many complete sets were removed
	 * @param String  $timestamps         - a csv of remaining timestamps
	 * @param String  $deleted_timestamps - a csv of deleted timestamps
	 *
	 * @return Array - information on the status, suitable for returning to the UI
	 */
	public function remove_backup_set_cleanup($delete_complete, $backups, $local_deleted, $remote_deleted, $sets_removed, $timestamps, $deleted_timestamps) {

		global $updraftplus;

		$updraftplus->register_wp_http_option_hooks(false);

		UpdraftPlus_Backup_History::save_history($backups);

		$updraftplus->log("Local files deleted: $local_deleted. Remote files deleted: $remote_deleted");

		if ($delete_complete) {
			$set_message = __('Backup sets removed:', 'updraftplus');
			$local_message = __('Local files deleted:', 'updraftplus');
			$remote_message = __('Remote files deleted:', 'updraftplus');

			if (UpdraftPlus_Options::get_updraft_option('updraft_debug_mode')) {
				restore_error_handler();
			}
			
			return array('result' => 'success', 'set_message' => $set_message, 'local_message' => $local_message, 'remote_message' => $remote_message, 'backup_sets' => $sets_removed, 'backup_local' => $local_deleted, 'backup_remote' => $remote_deleted);
		} else {
		
			return array('result' => 'continue', 'backup_local' => $local_deleted, 'backup_remote' => $remote_deleted, 'backup_sets' => $sets_removed, 'timestamps' => $timestamps, 'deleted_timestamps' => $deleted_timestamps);
		}
	}

	/**
	 * Get the history status HTML and other information
	 *
	 * @param Boolean $rescan	  - whether to rescan local storage first
	 * @param Boolean $remotescan - whether to rescan remote storage first
	 * @param Boolean $debug	  - whether to return debugging information also
	 *
	 * @return Array - the information requested
	 */
	public function get_history_status($rescan, $remotescan, $debug = false) {
	
		global $updraftplus;
	
		if ($rescan) $messages = UpdraftPlus_Backup_History::rebuild($remotescan, false, $debug);
		$backup_history = UpdraftPlus_Backup_History::get_history();
		$output = UpdraftPlus_Backup_History::existing_backup_table($backup_history);
		$data = array();

		if (!empty($messages) && is_array($messages)) {
			$noutput = '';
			foreach ($messages as $msg) {
				if (empty($msg['code']) || 'file-listing' != $msg['code']) {
					$noutput .= '<li>'.(empty($msg['desc']) ? '' : $msg['desc'].': ').'<em>'.$msg['message'].'</em></li>';
				}
				if (!empty($msg['data'])) {
					$key = $msg['method'].'-'.$msg['service_instance_id'];
					$data[$key] = $msg['data'];
				}
			}
			if ($noutput) {
				$output = '<div style="margin-left: 100px; margin-top: 10px;"><ul style="list-style: disc inside;">'.$noutput.'</ul></div>'.$output;
			}
		}
		
		$logs_exist = (false !== strpos($output, 'downloadlog'));
		if (!$logs_exist) {
			list($mod_time, $log_file, $nonce) = $updraftplus->last_modified_log();
			if ($mod_time) $logs_exist = true;
		}
		
		return apply_filters('updraftplus_get_history_status_result', array(
			'n' => __('Existing Backups', 'updraftplus').' <span class="updraft_existing_backups_count">'.count($backup_history).'</span>',
			't' => $output,  // table
			'data' => $data,
			'cksum' => md5($output),
			'logs_exist' => $logs_exist,
			'web_server_disk_space' => UpdraftPlus_Filesystem_Functions::web_server_disk_space(true),
		));
	}
	
	/**
	 * Stop an active backup job
	 *
	 * @param String $job_id - job ID of the job to stop
	 *
	 * @return Array - information on the outcome of the attempt
	 */
	public function activejobs_delete($job_id) {
			
		if (preg_match("/^[0-9a-f]{12}$/", $job_id)) {
		
			global $updraftplus;
			$cron = get_option('cron', array());
			$found_it = false;
		
			$updraft_dir = $updraftplus->backups_dir_location();
			if (file_exists($updraft_dir.'/log.'.$job_id.'.txt')) touch($updraft_dir.'/deleteflag-'.$job_id.'.txt');
			
			foreach ($cron as $time => $job) {
				if (isset($job['updraft_backup_resume'])) {
					foreach ($job['updraft_backup_resume'] as $hook => $info) {
						if (isset($info['args'][1]) && $info['args'][1] == $job_id) {
							$args = $cron[$time]['updraft_backup_resume'][$hook]['args'];
							wp_unschedule_event($time, 'updraft_backup_resume', $args);
							if (!$found_it) return array('ok' => 'Y', 'c' => 'deleted', 'm' => __('Job deleted', 'updraftplus'));
							$found_it = true;
						}
					}
				}
			}
		}

		if (!$found_it) return array('ok' => 'N', 'c' => 'not_found', 'm' => __('Could not find that job - perhaps it has already finished?', 'updraftplus'));

	}

	/**
	 * Input: an array of items
	 * Each item is in the format: <base>,<timestamp>,<type>(,<findex>)
	 * The 'base' is not for us: we just pass it straight back
	 *
	 * @param  array $downloaders Array of Items to download
	 * @return array
	 */
	public function get_download_statuses($downloaders) {
		global $updraftplus;
		$download_status = array();
		foreach ($downloaders as $downloader) {
			// prefix, timestamp, entity, index
			if (preg_match('/^([^,]+),(\d+),([-a-z]+|db[0-9]+),(\d+)$/', $downloader, $matches)) {
				$findex = (empty($matches[4])) ? '0' : $matches[4];
				$updraftplus->nonce = dechex($matches[2]).$findex.substr(md5($matches[3]), 0, 3);
				$updraftplus->jobdata_reset();
				$status = $this->download_status($matches[2], $matches[3], $matches[4]);
				if (is_array($status)) {
					$status['base'] = $matches[1];
					$status['timestamp'] = $matches[2];
					$status['what'] = $matches[3];
					$status['findex'] = $findex;
					$download_status[] = $status;
				}
			}
		}
		return $download_status;
	}
	
	/**
	 * Get, as HTML output, a list of active jobs
	 *
	 * @param Array $request - details on the request being made (e.g. extra info to include)
	 *
	 * @return String
	 */
	public function get_activejobs_list($request) {
	
		global $updraftplus;
		
		$download_status = empty($request['downloaders']) ? array() : $this->get_download_statuses(explode(':', $request['downloaders']));

		if (!empty($request['oneshot'])) {
			$job_id = get_site_option('updraft_oneshotnonce', false);
			// print_active_job() for one-shot jobs that aren't in cron
			$active_jobs = (false === $job_id) ? '' : $this->print_active_job($job_id, true);
		} elseif (!empty($request['thisjobonly'])) {
			// print_active_jobs() is for resumable jobs where we want the cron info to be included in the output
			$active_jobs = $this->print_active_jobs($request['thisjobonly']);
		} else {
			$active_jobs = $this->print_active_jobs();
		}
		$logupdate_array = array();
		if (!empty($request['log_fetch'])) {
			if (isset($request['log_nonce'])) {
				$log_nonce = $request['log_nonce'];
				$log_pointer = isset($request['log_pointer']) ? absint($request['log_pointer']) : 0;
				$logupdate_array = $this->fetch_log($log_nonce, $log_pointer);
			}
		}
		return array(
			// We allow the front-end to decide what to do if there's nothing logged - we used to (up to 1.11.29) send a pre-defined message
			'l' => htmlspecialchars(UpdraftPlus_Options::get_updraft_lastmessage()),
			'j' => $active_jobs,
			'ds' => $download_status,
			'u' => $logupdate_array
		);
	}
	
	/**
	 * Start a new backup
	 *
	 * @param Array			   $request
	 * @param Boolean|Callable $close_connection_callable
	 */
	public function request_backupnow($request, $close_connection_callable = false) {
		global $updraftplus;

		$abort = false;
		$backupnow_nocloud = !empty($request['backupnow_nocloud']);
		$event = (!empty($request['backupnow_nofiles'])) ? 'updraft_backupnow_backup_database' : ((!empty($request['backupnow_nodb'])) ? 'updraft_backupnow_backup' : 'updraft_backupnow_backup_all');
		
		$request['incremental'] = !empty($request['incremental']);

		$entities = !empty($request['onlythisfileentity']) ? explode(',', $request['onlythisfileentity']) : array();

		$incremental = $request['incremental'] ? apply_filters('updraftplus_prepare_incremental_run', false, $entities) : false;

		// The call to backup_time_nonce() allows us to know the nonce in advance, and return it
		$nonce = $updraftplus->backup_time_nonce();

		$msg = array(
			'nonce' => $nonce,
			'm' => apply_filters('updraftplus_backupnow_start_message', '<strong>'.__('Start backup', 'updraftplus').':</strong> '.htmlspecialchars(__('OK. You should soon see activity in the "Last log message" field below.', 'updraftplus')), $nonce)
		);

		if (!empty($request['incremental']) && !$incremental) {
			$msg = array(
				'error' => __('No suitable backup set (that already contains a full backup of all the requested file component types) was found, to add increments to. Aborting this backup.', 'updaftplus')
			);
			$abort = true;
		}
		
		if ($close_connection_callable && is_callable($close_connection_callable)) {
			call_user_func($close_connection_callable, $msg);
		} else {
			$updraftplus->close_browser_connection(json_encode($msg));
		}

		if ($abort) die;
		
		$options = array('nocloud' => $backupnow_nocloud, 'use_nonce' => $nonce);
		if (!empty($request['onlythisfileentity']) && is_string($request['onlythisfileentity'])) {
			// Something to see in the 'last log' field when it first appears, before the backup actually starts
			$updraftplus->log(__('Start backup', 'updraftplus'));
			$options['restrict_files_to_override'] = explode(',', $request['onlythisfileentity']);
		}

		if ($request['incremental'] && !$incremental) {
			$updraftplus->log('An incremental backup was requested but no suitable backup found to add increments to; will proceed with a new backup');
			$request['incremental'] = false;
		}

		if (!empty($request['extradata'])) $options['extradata'] = $request['extradata'];
		
		$options['always_keep'] = empty($request['always_keep']) ? false : true;

		do_action($event, apply_filters('updraft_backupnow_options', $options, $request));
	}
	
	/**
	 * Get the contents of a log file
	 *
	 * @param String  $backup_nonce	 - the backup id; or empty, for the most recently modified
	 * @param Integer $log_pointer	 - the byte count to fetch from
	 * @param String  $output_format - the format to return in; allowed as 'html' (which will escape HTML entities in what is returned) and 'raw'
	 *
	 * @return String
	 */
	public function fetch_log($backup_nonce = '', $log_pointer = 0, $output_format = 'html') {
		global $updraftplus;

		if (empty($backup_nonce)) {
			list($mod_time, $log_file, $nonce) = $updraftplus->last_modified_log();
		} else {
			$nonce = $backup_nonce;
		}

		if (!preg_match('/^[0-9a-f]+$/', $nonce)) die('Security check');
		
		$log_content = '';
		$new_pointer = $log_pointer;
		
		if (!empty($nonce)) {
			$updraft_dir = $updraftplus->backups_dir_location();

			$potential_log_file = $updraft_dir."/log.".$nonce.".txt";

			if (is_readable($potential_log_file)) {
				
				$templog_array = array();
				$log_file = fopen($potential_log_file, "r");
				if ($log_pointer > 0) fseek($log_file, $log_pointer);
				
				while (($buffer = fgets($log_file, 4096)) !== false) {
					$templog_array[] = $buffer;
				}
				if (!feof($log_file)) {
					$templog_array[] = __('Error: unexpected file read fail', 'updraftplus');
				}
				
				$new_pointer = ftell($log_file);
				$log_content = implode("", $templog_array);

				
			} else {
				$log_content .= __('The log file could not be read.', 'updraftplus');
			}

		} else {
			$log_content .= __('The log file could not be read.', 'updraftplus');
		}
		
		if ('html' == $output_format) $log_content = htmlspecialchars($log_content);
		
		$ret_array = array(
			'log' => $log_content,
			'nonce' => $nonce,
			'pointer' => $new_pointer
		);
		
		return $ret_array;
	}

	/**
	 * Get a count for the number of overdue cron jobs
	 *
	 * @return Integer - how many cron jobs are overdue
	 */
	public function howmany_overdue_crons() {
		$how_many_overdue = 0;
		if (function_exists('_get_cron_array') || (is_file(ABSPATH.WPINC.'/cron.php') && include_once(ABSPATH.WPINC.'/cron.php') && function_exists('_get_cron_array'))) {
			$crons = _get_cron_array();
			if (is_array($crons)) {
				$timenow = time();
				foreach ($crons as $jt => $job) {
					if ($jt < $timenow) $how_many_overdue++;
				}
			}
		}
		return $how_many_overdue;
	}

	public function get_php_errors($errno, $errstr, $errfile, $errline) {
		global $updraftplus;
		if (0 == error_reporting()) return true;
		$logline = $updraftplus->php_error_to_logline($errno, $errstr, $errfile, $errline);
		if (false !== $logline) $this->logged[] = $logline;
		// Don't pass it up the chain (since it's going to be output to the user always)
		return true;
	}

	private function download_status($timestamp, $type, $findex) {
		global $updraftplus;
		$response = array('m' => $updraftplus->jobdata_get('dlmessage_'.$timestamp.'_'.$type.'_'.$findex).'<br>');
		if ($file = $updraftplus->jobdata_get('dlfile_'.$timestamp.'_'.$type.'_'.$findex)) {
			if ('failed' == $file) {
				$response['e'] = __('Download failed', 'updraftplus').'<br>';
				$response['failed'] = true;
				$errs = $updraftplus->jobdata_get('dlerrors_'.$timestamp.'_'.$type.'_'.$findex);
				if (is_array($errs) && !empty($errs)) {
					$response['e'] .= '<ul class="disc">';
					foreach ($errs as $err) {
						if (is_array($err)) {
							$response['e'] .= '<li>'.htmlspecialchars($err['message']).'</li>';
						} else {
							$response['e'] .= '<li>'.htmlspecialchars($err).'</li>';
						}
					}
					$response['e'] .= '</ul>';
				}
			} elseif (preg_match('/^downloaded:(\d+):(.*)$/', $file, $matches) && file_exists($matches[2])) {
				$response['p'] = 100;
				$response['f'] = $matches[2];
				$response['s'] = (int) $matches[1];
				$response['t'] = (int) $matches[1];
				$response['m'] = __('File ready.', 'updraftplus');
				if ('db' != substr($type, 0, 2)) $response['can_show_contents'] = true;
			} elseif (preg_match('/^downloading:(\d+):(.*)$/', $file, $matches) && file_exists($matches[2])) {
				// Convert to bytes
				$response['f'] = $matches[2];
				$total_size = (int) max($matches[1], 1);
				$cur_size = filesize($matches[2]);
				$response['s'] = $cur_size;
				$file_age = time() - filemtime($matches[2]);
				if ($file_age > 20) $response['a'] = time() - filemtime($matches[2]);
				$response['t'] = $total_size;
				$response['m'] .= __("Download in progress", 'updraftplus').' ('.round($cur_size/1024).' / '.round(($total_size/1024)).' KB)';
				$response['p'] = round(100*$cur_size/$total_size);
			} else {
				$response['m'] .= __('No local copy present.', 'updraftplus');
				$response['p'] = 0;
				$response['s'] = 0;
				$response['t'] = 1;
			}
		}
		return $response;
	}

	/**
	 * Used with the WP filter upload_dir to adjust where uploads go to when uploading a backup
	 *
	 * @param Array $uploads - pre-filter array
	 *
	 * @return Array - filtered array
	 */
	public function upload_dir($uploads) {
		global $updraftplus;
		$updraft_dir = $updraftplus->backups_dir_location();
		if (is_writable($updraft_dir)) $uploads['path'] = $updraft_dir;
		return $uploads;
	}

	/**
	 * We do actually want to over-write
	 *
	 * @param  String $dir  Directory
	 * @param  String $name Name
	 * @param  String $ext  File extension
	 *
	 * @return String
	 */
	public function unique_filename_callback($dir, $name, $ext) {// phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found -- Filter use
		return $name.$ext;
	}

	public function sanitize_file_name($filename) {
		// WordPress 3.4.2 on multisite (at least) adds in an unwanted underscore
		return preg_replace('/-db(.*)\.gz_\.crypt$/', '-db$1.gz.crypt', $filename);
	}

	/**
	 * Runs upon the WordPress action plupload_action
	 */
	public function plupload_action() {

		global $updraftplus;
		@set_time_limit(UPDRAFTPLUS_SET_TIME_LIMIT);

		if (!UpdraftPlus_Options::user_can_manage()) return;
		check_ajax_referer('updraft-uploader');

		$updraft_dir = $updraftplus->backups_dir_location();
		if (!@UpdraftPlus_Filesystem_Functions::really_is_writable($updraft_dir)) {
			echo json_encode(array('e' => sprintf(__("Backup directory (%s) is not writable, or does not exist.", 'updraftplus'), $updraft_dir).' '.__('You will find more information about this in the Settings section.', 'updraftplus')));
			exit;
		}
		
		add_filter('upload_dir', array($this, 'upload_dir'));
		add_filter('sanitize_file_name', array($this, 'sanitize_file_name'));
		// handle file upload

		$farray = array('test_form' => true, 'action' => 'plupload_action');

		$farray['test_type'] = false;
		$farray['ext'] = 'x-gzip';
		$farray['type'] = 'application/octet-stream';

		if (!isset($_POST['chunks'])) {
			$farray['unique_filename_callback'] = array($this, 'unique_filename_callback');
		}

		$status = wp_handle_upload(
			$_FILES['async-upload'],
			$farray
		);
		remove_filter('upload_dir', array($this, 'upload_dir'));
		remove_filter('sanitize_file_name', array($this, 'sanitize_file_name'));

		if (isset($status['error'])) {
			echo json_encode(array('e' => $status['error']));
			exit;
		}

		// If this was the chunk, then we should instead be concatenating onto the final file
		if (isset($_POST['chunks']) && isset($_POST['chunk']) && preg_match('/^[0-9]+$/', $_POST['chunk'])) {
		
			$final_file = basename($_POST['name']);
			
			if (!rename($status['file'], $updraft_dir.'/'.$final_file.'.'.$_POST['chunk'].'.zip.tmp')) {
				@unlink($status['file']);
				echo json_encode(array('e' => sprintf(__('Error: %s', 'updraftplus'), __('This file could not be uploaded', 'updraftplus'))));
				exit;
			}
			
			$status['file'] = $updraft_dir.'/'.$final_file.'.'.$_POST['chunk'].'.zip.tmp';

		}

		$response = array();
		if (!isset($_POST['chunks']) || (isset($_POST['chunk']) && preg_match('/^[0-9]+$/', $_POST['chunk']) && $_POST['chunk'] == $_POST['chunks']-1) && isset($final_file)) {
			if (!preg_match('/^log\.[a-f0-9]{12}\.txt/i', $final_file) && !preg_match('/^backup_([\-0-9]{15})_.*_([0-9a-f]{12})-([\-a-z]+)([0-9]+)?(\.(zip|gz|gz\.crypt))?$/i', $final_file, $matches)) {
				$accept = apply_filters('updraftplus_accept_archivename', array());
				if (is_array($accept)) {
					foreach ($accept as $acc) {
						if (preg_match('/'.$acc['pattern'].'/i', $final_file)) {
							$response['dm'] = sprintf(__('This backup was created by %s, and can be imported.', 'updraftplus'), $acc['desc']);
						}
					}
				}
				if (empty($response['dm'])) {
					if (isset($status['file'])) @unlink($status['file']);
					echo json_encode(array('e' => sprintf(__('Error: %s', 'updraftplus'), __('Bad filename format - this does not look like a file created by UpdraftPlus', 'updraftplus'))));
					exit;
				}
			} else {
				$backupable_entities = $updraftplus->get_backupable_file_entities(true);
				$type = isset($matches[3]) ? $matches[3] : '';
				if (!preg_match('/^log\.[a-f0-9]{12}\.txt/', $final_file) && 'db' != $type && !isset($backupable_entities[$type])) {
					if (isset($status['file'])) @unlink($status['file']);
					echo json_encode(array('e' => sprintf(__('Error: %s', 'updraftplus'), sprintf(__('This looks like a file created by UpdraftPlus, but this install does not know about this type of object: %s. Perhaps you need to install an add-on?', 'updraftplus'), htmlspecialchars($type)))));
					exit;
				}
			}
			
			// Final chunk? If so, then stich it all back together
			if (isset($_POST['chunk']) && $_POST['chunk'] == $_POST['chunks']-1 && !empty($final_file)) {
				if ($wh = fopen($updraft_dir.'/'.$final_file, 'wb')) {
					for ($i = 0; $i < $_POST['chunks']; $i++) {
						$rf = $updraft_dir.'/'.$final_file.'.'.$i.'.zip.tmp';
						if ($rh = fopen($rf, 'rb')) {
							while ($line = fread($rh, 262144)) {
								fwrite($wh, $line);
							}
							fclose($rh);
							@unlink($rf);
						}
					}
					fclose($wh);
					$status['file'] = $updraft_dir.'/'.$final_file;
					if ('.tar' == substr($final_file, -4, 4)) {
						if (file_exists($status['file'].'.gz')) unlink($status['file'].'.gz');
						if (file_exists($status['file'].'.bz2')) unlink($status['file'].'.bz2');
					} elseif ('.tar.gz' == substr($final_file, -7, 7)) {
						if (file_exists(substr($status['file'], 0, strlen($status['file'])-3))) unlink(substr($status['file'], 0, strlen($status['file'])-3));
						if (file_exists(substr($status['file'], 0, strlen($status['file'])-3).'.bz2')) unlink(substr($status['file'], 0, strlen($status['file'])-3).'.bz2');
					} elseif ('.tar.bz2' == substr($final_file, -8, 8)) {
						if (file_exists(substr($status['file'], 0, strlen($status['file'])-4))) unlink(substr($status['file'], 0, strlen($status['file'])-4));
						if (file_exists(substr($status['file'], 0, strlen($status['file'])-4).'.gz')) unlink(substr($status['file'], 0, strlen($status['file'])-3).'.gz');
					}
				}
			}
			
		}

		// send the uploaded file url in response
		$response['m'] = $status['url'];
		echo json_encode($response);
		exit;
	}

	/**
	 * Database decrypter - runs upon the WP action plupload_action2
	 */
	public function plupload_action2() {

		@set_time_limit(UPDRAFTPLUS_SET_TIME_LIMIT);
		global $updraftplus;

		if (!UpdraftPlus_Options::user_can_manage()) return;
		check_ajax_referer('updraft-uploader');

		$updraft_dir = $updraftplus->backups_dir_location();
		if (!is_writable($updraft_dir)) exit;

		add_filter('upload_dir', array($this, 'upload_dir'));
		add_filter('sanitize_file_name', array($this, 'sanitize_file_name'));
		// handle file upload

		$farray = array('test_form' => true, 'action' => 'plupload_action2');

		$farray['test_type'] = false;
		$farray['ext'] = 'crypt';
		$farray['type'] = 'application/octet-stream';

		if (isset($_POST['chunks'])) {
			// $farray['ext'] = 'zip';
			// $farray['type'] = 'application/zip';
		} else {
			$farray['unique_filename_callback'] = array($this, 'unique_filename_callback');
		}

		$status = wp_handle_upload(
			$_FILES['async-upload'],
			$farray
		);
		remove_filter('upload_dir', array($this, 'upload_dir'));
		remove_filter('sanitize_file_name', array($this, 'sanitize_file_name'));

		if (isset($status['error'])) die('ERROR: '.$status['error']);

		// If this was the chunk, then we should instead be concatenating onto the final file
		if (isset($_POST['chunks']) && isset($_POST['chunk']) && preg_match('/^[0-9]+$/', $_POST['chunk'])) {
			$final_file = basename($_POST['name']);
			rename($status['file'], $updraft_dir.'/'.$final_file.'.'.$_POST['chunk'].'.zip.tmp');
			$status['file'] = $updraft_dir.'/'.$final_file.'.'.$_POST['chunk'].'.zip.tmp';
		}

		if (!isset($_POST['chunks']) || (isset($_POST['chunk']) && $_POST['chunk'] == $_POST['chunks']-1)) {
			if (!preg_match('/^backup_([\-0-9]{15})_.*_([0-9a-f]{12})-db([0-9]+)?\.(gz\.crypt)$/i', $final_file)) {

				@unlink($status['file']);
				echo 'ERROR:'.__('Bad filename format - this does not look like an encrypted database file created by UpdraftPlus', 'updraftplus');
				exit;
			}
			
			// Final chunk? If so, then stich it all back together
			if (isset($_POST['chunk']) && $_POST['chunk'] == $_POST['chunks']-1 && isset($final_file)) {
				if ($wh = fopen($updraft_dir.'/'.$final_file, 'wb')) {
					for ($i=0; $i<$_POST['chunks']; $i++) {
						$rf = $updraft_dir.'/'.$final_file.'.'.$i.'.zip.tmp';
						if ($rh = fopen($rf, 'rb')) {
							while ($line = fread($rh, 32768)) {
								fwrite($wh, $line);
							}
							fclose($rh);
							@unlink($rf);
						}
					}
					fclose($wh);
				}
			}
			
		}

		// send the uploaded file url in response
		if (isset($final_file)) echo 'OK:'.$final_file;
		exit;
	}

	/**
	 * Include the settings header template
	 */
	public function settings_header() {
		$this->include_template('wp-admin/settings/header.php');
	}

	/**
	 * Include the settings footer template
	 */
	public function settings_footer() {
		$this->include_template('wp-admin/settings/footer.php');
	}

	/**
	 * Output the settings page content. Will also run a restore if $_REQUEST so indicates.
	 */
	public function settings_output() {

		if (false == ($render = apply_filters('updraftplus_settings_page_render', true))) {
			do_action('updraftplus_settings_page_render_abort', $render);
			return;
		}

		do_action('updraftplus_settings_page_init');

		global $updraftplus;

		/**
		 * We use request here because the initial restore is triggered by a POSTed form. we then may need to obtain credential for the WP_Filesystem. to do this WP outputs a form, but we don't pass our parameters via that. So the values are passed back in as GET parameters.
		 */
		if (isset($_REQUEST['action']) && (('updraft_restore' == $_REQUEST['action'] && isset($_REQUEST['backup_timestamp'])) || ('updraft_restore_continue' == $_REQUEST['action'] && !empty($_REQUEST['restoreid'])))) {

			$is_continuation = ('updraft_restore_continue' == $_REQUEST['action']) ? true : false;

			if ($is_continuation) {
				$restore_in_progress = get_site_option('updraft_restore_in_progress');
				if ($restore_in_progress != $_REQUEST['restoreid']) {
					$abort_restore_already = true;
					$updraftplus->log(__('Sufficient information about the in-progress restoration operation could not be found.', 'updraftplus').' (restoreid_mismatch)', 'error', 'restoreid_mismatch');
				} else {

					$restore_jobdata = $updraftplus->jobdata_getarray($restore_in_progress);
					if (is_array($restore_jobdata) && isset($restore_jobdata['job_type']) && 'restore' == $restore_jobdata['job_type'] && isset($restore_jobdata['second_loop_entities']) && !empty($restore_jobdata['second_loop_entities']) && isset($restore_jobdata['job_time_ms']) && isset($restore_jobdata['backup_timestamp'])) {
						$backup_timestamp = $restore_jobdata['backup_timestamp'];
						$continuation_data = $restore_jobdata;
					} else {
						$abort_restore_already = true;
						$updraftplus->log(__('Sufficient information about the in-progress restoration operation could not be found.', 'updraftplus').' (restoreid_nojobdata)', 'error', 'restoreid_nojobdata');
					}
				}

			} else {
				$backup_timestamp = $_REQUEST['backup_timestamp'];
				$continuation_data = null;
			}

			if (empty($abort_restore_already)) {
				$backup_success = $this->restore_backup($backup_timestamp, $continuation_data);
			} else {
				$backup_success = false;
			}

			if (empty($updraftplus->errors) && true === $backup_success) {
				// TODO: Deal with the case of some of the work having been deferred
				echo '<p><strong>';
				$updraftplus->log_e('Restore successful!');
				echo '</strong></p>';
				$updraftplus->log('Restore successful');
				$s_val = 1;
				if (!empty($this->entities_to_restore) && is_array($this->entities_to_restore)) {
					foreach ($this->entities_to_restore as $k => $v) {
						if ('db' != $v) $s_val = 2;
					}
				}
				$pval = $updraftplus->have_addons ? 1 : 0;

				echo '<strong>'.__('Actions', 'updraftplus').':</strong> <a href="'.UpdraftPlus_Options::admin_page_url().'?page=updraftplus&updraft_restore_success='.$s_val.'&pval='.$pval.'">'.__('Return to UpdraftPlus Configuration', 'updraftplus').'</a>';
				return;
				
			} elseif (is_wp_error($backup_success)) {
				echo '<p>';
				$updraftplus->log_e('Restore failed...');
				echo '</p>';
				$updraftplus->log_wp_error($backup_success);
				$updraftplus->log('Restore failed');
				$updraftplus->list_errors();
				echo '<strong>'.__('Actions', 'updraftplus').':</strong> <a href="'.UpdraftPlus_Options::admin_page_url().'?page=updraftplus">'.__('Return to UpdraftPlus Configuration', 'updraftplus').'</a>';
				return;
			} elseif (false === $backup_success) {
				// This means, "not yet - but stay on the page because we may be able to do it later, e.g. if the user types in the requested information"
				echo '<p>';
				$updraftplus->log_e('Restore failed...');
				echo '</p>';
				$updraftplus->log("Restore failed");
				$updraftplus->list_errors();
				echo '<strong>'.__('Actions', 'updraftplus').':</strong> <a href="'.UpdraftPlus_Options::admin_page_url().'?page=updraftplus">'.__('Return to UpdraftPlus Configuration', 'updraftplus').'</a>';
				return;
			}
		}

		if (isset($_REQUEST['action']) && 'updraft_delete_old_dirs' == $_REQUEST['action']) {
			$nonce = empty($_REQUEST['updraft_delete_old_dirs_nonce']) ? '' : $_REQUEST['updraft_delete_old_dirs_nonce'];
			if (!wp_verify_nonce($nonce, 'updraftplus-credentialtest-nonce')) die('Security check');
			$this->delete_old_dirs_go();
			return;
		}

		if (!empty($_REQUEST['action']) && 'updraftplus_broadcastaction' == $_REQUEST['action'] && !empty($_REQUEST['subaction'])) {
			$nonce = (empty($_REQUEST['nonce'])) ? "" : $_REQUEST['nonce'];
			if (!wp_verify_nonce($nonce, 'updraftplus-credentialtest-nonce')) die('Security check');
			do_action($_REQUEST['subaction']);
			return;
		}

		if (isset($_GET['error'])) {
			// This is used by Microsoft OneDrive authorisation failures (May 15). I am not sure what may have been using the 'error' GET parameter otherwise - but it is harmless.
			if (!empty($_GET['error_description'])) {
				$this->show_admin_warning(htmlspecialchars($_GET['error_description']).' ('.htmlspecialchars($_GET['error']).')', 'error');
			} else {
				$this->show_admin_warning(htmlspecialchars($_GET['error']), 'error');
			}
		}

		if (isset($_GET['message'])) $this->show_admin_warning(htmlspecialchars($_GET['message']));

		if (isset($_GET['action']) && 'updraft_create_backup_dir' == $_GET['action'] && isset($_GET['nonce']) && wp_verify_nonce($_GET['nonce'], 'create_backup_dir')) {
			$created = $this->create_backup_dir();
			if (is_wp_error($created)) {
				echo '<p>'.__('Backup directory could not be created', 'updraftplus').'...<br>';
				echo '<ul class="disc">';
				foreach ($created->get_error_messages() as $key => $msg) {
					echo '<li>'.htmlspecialchars($msg).'</li>';
				}
				echo '</ul></p>';
			} elseif (false !== $created) {
				echo '<p>'.__('Backup directory successfully created.', 'updraftplus').'</p><br>';
			}
			echo '<b>'.__('Actions', 'updraftplus').':</b> <a href="'.UpdraftPlus_Options::admin_page_url().'?page=updraftplus">'.__('Return to UpdraftPlus Configuration', 'updraftplus').'</a>';
			return;
		}

		echo '<div id="updraft_backup_started" class="updated updraft-hidden" style="display:none;"></div>';

		if (isset($_POST['action']) && 'updraft_wipesettings' == $_POST['action']) {
			$this->updraft_wipe_settings();
		}

		// This opens a div
		$this->settings_header();
		?>

			<div id="updraft-hidethis">
			<p>
			<strong><?php _e('Warning:', 'updraftplus'); ?> <?php _e("If you can still read these words after the page finishes loading, then there is a JavaScript or jQuery problem in the site.", 'updraftplus'); ?></strong>

			<?php if (false !== strpos(basename(UPDRAFTPLUS_URL), ' ')) { ?>
				<strong><?php _e('The UpdraftPlus directory in wp-content/plugins has white-space in it; WordPress does not like this. You should rename the directory to wp-content/plugins/updraftplus to fix this problem.', 'updraftplus');?></strong>
			<?php } else { ?>
				<a href="<?php echo apply_filters('updraftplus_com_link', "https://updraftplus.com/do-you-have-a-javascript-or-jquery-error/");?>"><?php _e('Go here for more information.', 'updraftplus'); ?></a>
			<?php } ?>
			</p>
			</div>

			<?php

			$include_deleteform_div = true;

			// Opens a div, which needs closing later
			if (isset($_GET['updraft_restore_success'])) {

				if (get_template() === 'optimizePressTheme' || is_plugin_active('optimizePressPlugin') || is_plugin_active_for_network('optimizePressPlugin')) {
					$this->show_admin_warning("<a href='https://optimizepress.zendesk.com/hc/en-us/articles/203699826-Update-URL-References-after-moving-domain' target='_blank'>" . __("OptimizePress 2.0 encodes its contents, so search/replace does not work.", "updraftplus") . ' ' . __("To fix this problem go here.", "updraftplus") . "</a>", "notice notice-warning");
				}
				$success_advert = (isset($_GET['pval']) && 0 == $_GET['pval'] && !$updraftplus->have_addons) ? '<p>'.__('For even more features and personal support, check out ', 'updraftplus').'<strong><a href="'.apply_filters("updraftplus_com_link", 'https://updraftplus.com/shop/updraftplus-premium/').'" target="_blank">UpdraftPlus Premium</a>.</strong></p>' : "";

				echo "<div class=\"updated backup-restored\"><span><strong>".__('Your backup has been restored.', 'updraftplus').'</strong></span><br>';
				// Unnecessary - will be advised of this below
				// if (2 == $_GET['updraft_restore_success']) echo ' '.__('Your old (themes, uploads, plugins, whatever) directories have been retained with "-old" appended to their name. Remove them when you are satisfied that the backup worked properly.');
				echo $success_advert;
				$include_deleteform_div = false;

			}

			if ($this->scan_old_dirs(true)) $this->print_delete_old_dirs_form(true, $include_deleteform_div);

			// Close the div opened by the earlier section
			if (isset($_GET['updraft_restore_success'])) echo '</div>';

			if (empty($success_advert) && empty($this->no_settings_warning)) {

				if (!class_exists('UpdraftPlus_Notices')) include_once(UPDRAFTPLUS_DIR.'/includes/updraftplus-notices.php');
				global $updraftplus_notices;
				$updraftplus_notices->do_notice();
			}

			if (!$updraftplus->memory_check(64)) {
				// HS8390 - A case where UpdraftPlus::memory_check_current() returns -1
				$memory_check_current = $updraftplus->memory_check_current();
				if ($memory_check_current > 0) {
				?>
					<div class="updated memory-limit"><?php _e('Your PHP memory limit (set by your web hosting company) is very low. UpdraftPlus attempted to raise it but was unsuccessful. This plugin may struggle with a memory limit of less than 64 Mb  - especially if you have very large files uploaded (though on the other hand, many sites will be successful with a 32Mb limit - your experience may vary).', 'updraftplus');?> <?php _e('Current limit is:', 'updraftplus');?> <?php echo $updraftplus->memory_check_current(); ?> MB</div>
				<?php }
			}


			if (!empty($updraftplus->errors)) {
				echo '<div class="error updraft_list_errors">';
				$updraftplus->list_errors();
				echo '</div>';
			}

			$backup_history = UpdraftPlus_Backup_History::get_history();
			if (empty($backup_history)) {
				UpdraftPlus_Backup_History::rebuild();
				$backup_history = UpdraftPlus_Backup_History::get_history();
			}

			$tabflag = 'backups';
			$main_tabs = $this->get_main_tabs_array();
			
			if (isset($_REQUEST['tab'])) {
				$request_tab = sanitize_text_field($_REQUEST['tab']);
				$valid_tabflags = array_keys($main_tabs);
				if (in_array($request_tab, $valid_tabflags)) {
					$tabflag = $request_tab;
				} else {
					$tabflag = 'backups';
				}
			}
			
			$this->include_template('wp-admin/settings/tab-bar.php', false, array('main_tabs' => $main_tabs, 'backup_history' => $backup_history, 'tabflag' => $tabflag));

			$updraft_dir = $updraftplus->backups_dir_location();
			$backup_disabled = UpdraftPlus_Filesystem_Functions::really_is_writable($updraft_dir) ? '' : 'disabled="disabled"';
		?>
		
		<div id="updraft-poplog" >
			<pre id="updraft-poplog-content"></pre>
		</div>
		
		<div id="updraft-navtab-backups-content" <?php if ('backups' != $tabflag) echo 'class="updraft-hidden"'; ?> style="<?php if ('backups' != $tabflag) echo 'display:none;'; ?>">
			<?php
				$is_opera = (false !== strpos($_SERVER['HTTP_USER_AGENT'], 'Opera') || false !== strpos($_SERVER['HTTP_USER_AGENT'], 'OPR/'));
				$tmp_opts = array('include_opera_warning' => $is_opera);
				$this->include_template('wp-admin/settings/tab-backups.php', false, array('backup_history' => $backup_history, 'options' => $tmp_opts));
				$this->include_template('wp-admin/settings/delete-and-restore-modals.php');
				$this->include_template('wp-admin/settings/upload-backups-modal.php');
			?>
		</div>
		
		<div id="updraft-navtab-migrate-content"<?php if ('migrate' != $tabflag) echo ' class="updraft-hidden"'; ?> style="<?php if ('migrate' != $tabflag) echo 'display:none;'; ?>">
			<?php
			if (has_action('updraftplus_migrate_tab_output')) {
				do_action('updraftplus_migrate_tab_output');
			} else {
				$this->include_template('wp-admin/settings/migrator-no-migrator.php');
			}
			?>
		</div>
		
		<div id="updraft-navtab-settings-content" <?php if ('settings' != $tabflag) echo 'class="updraft-hidden"'; ?> style="<?php if ('settings' != $tabflag) echo 'display:none;'; ?>">
			<h2 class="updraft_settings_sectionheading"><?php _e('Backup Contents And Schedule', 'updraftplus');?></h2>
			<?php UpdraftPlus_Options::options_form_begin(); ?>
				<?php $this->settings_formcontents(); ?>
			</form>
			<?php
				$our_keys = UpdraftPlus_Options::get_updraft_option('updraft_central_localkeys');
				if (!is_array($our_keys)) $our_keys = array();

				// Hide the UpdraftCentral Cloud wizard If the user already has a key created for either
				// updraftplus.com or self hosted version.
				if (empty($our_keys)) {
			?>
			<div id="updraftcentral_cloud_connect_container" class="updraftcentral_cloud_connect hidden-in-updraftcentral">
				<?php

					$email = '';

					// Checking email from "Premium / Extensions" tab
					if (defined('UDADDONS2_SLUG')) {
						global $updraftplus_addons2;

						if (is_a($updraftplus_addons2, 'UpdraftPlusAddons2') && is_callable(array($updraftplus_addons2, 'get_option'))) {
							$options = $updraftplus_addons2->get_option(UDADDONS2_SLUG.'_options');

							if (!empty($options['email'])) {
								$email = htmlspecialchars($options['email']);
							}
						}
					}

					// Check the vault's email if we fail to get the "email" from the "Premium / Extensions" tab
					if (empty($email)) {
						$settings = UpdraftPlus_Storage_Methods_Interface::update_remote_storage_options_format('updraftvault');
						if (!is_wp_error($settings)) {
							if (!empty($settings['settings'])) {
								foreach ($settings['settings'] as $instance_id => $storage_options) {
									if (!empty($storage_options['email'])) {
										$email = $storage_options['email'];
										break;
									}
								}
							}
						}
					}

					// Checking any possible email we could find from the "updraft_email" option in case the
					// above two checks failed.
					if (empty($email)) {
						$possible_emails = $updraftplus->just_one_email(UpdraftPlus_Options::get_updraft_option('updraft_email'));
						if (!empty($possible_emails)) {
							// If we get an array from the 'just_one_email' result then we're going
							// to pull the very first entry and make use of that on the succeeding process.
							if (is_array($possible_emails)) $possible_emails = array_shift($possible_emails);

							if (is_string($possible_emails)) {
								$emails = explode(',', $possible_emails);
								$email = trim($emails[0]);
							}
						}
					}

					$this->include_template('wp-admin/settings/updraftcentral-connect.php', false, array('email' => $email));
				?>
			</div>
			<?php
				}
			?>
		</div>

		<div id="updraft-navtab-expert-content"<?php if ('expert' != $tabflag) echo ' class="updraft-hidden"'; ?> style="<?php if ('expert' != $tabflag) echo 'display:none;'; ?>">
			<?php $this->settings_advanced_tools(); ?>
		</div>

		<div id="updraft-navtab-addons-content"<?php if ('addons' != $tabflag) echo ' class="updraft-hidden"'; ?> style="<?php if ('addons' != $tabflag) echo 'display:none;'; ?>">
		
			<?php
				$tab_addons = $this->include_template('wp-admin/settings/tab-addons.php', true, array('tabflag' => $tabflag));
				
				echo apply_filters('updraftplus_addonstab_content', $tab_addons);
				
			?>
		
		</div>
		
		<?php
		do_action('updraftplus_after_main_tab_content', $tabflag);
		// settings_header() opens a div
		$this->settings_footer();
	}
	
	/**
	 * Get main tabs array
	 *
	 * @return Array Array which have key as a tab key and value as tab label
	 */
	private function get_main_tabs_array() {
		return apply_filters(
			'updraftplus_main_tabs',
			array(
				'backups' => __('Backup / Restore', 'updraftplus'),
				'migrate' => __('Migrate / Clone', 'updraftplus'),
				'settings' => __('Settings', 'updraftplus'),
				'expert' => __('Advanced Tools', 'updraftplus'),
				'addons' => __('Premium / Extensions', 'updraftplus'),
			)
		);
	}
	
	/**
	 * Potentially register an action for showing restore progress
	 */
	private function print_restore_in_progress_box_if_needed() {
		$restore_in_progress = get_site_option('updraft_restore_in_progress');
		if (empty($restore_in_progress)) return;
		global $updraftplus;
		$restore_jobdata = $updraftplus->jobdata_getarray($restore_in_progress);
		if (is_array($restore_jobdata) && !empty($restore_jobdata)) {
			// Only print if within the last 24 hours; and only after 2 minutes
			if (isset($restore_jobdata['job_type']) && 'restore' == $restore_jobdata['job_type'] && isset($restore_jobdata['second_loop_entities']) && !empty($restore_jobdata['second_loop_entities']) && isset($restore_jobdata['job_time_ms']) && (time() - $restore_jobdata['job_time_ms'] > 120 || (defined('UPDRAFTPLUS_RESTORE_PROGRESS_ALWAYS_SHOW') && UPDRAFTPLUS_RESTORE_PROGRESS_ALWAYS_SHOW)) && time() - $restore_jobdata['job_time_ms'] < 86400 && (empty($_REQUEST['action']) || ('updraft_restore' != $_REQUEST['action'] && 'updraft_restore_continue' != $_REQUEST['action']))) {
				$restore_jobdata['jobid'] = $restore_in_progress;
				$this->restore_in_progress_jobdata = $restore_jobdata;
				add_action('all_admin_notices', array($this, 'show_admin_restore_in_progress_notice'));
			}
		}
	}

	public function show_admin_restore_in_progress_notice() {
	
		if (isset($_REQUEST['action']) && 'updraft_restore_abort' == $_REQUEST['action'] && !empty($_REQUEST['restoreid'])) {
			delete_site_option('updraft_restore_in_progress');
			return;
		}
	
		$restore_jobdata = $this->restore_in_progress_jobdata;
		$seconds_ago = time() - (int) $restore_jobdata['job_time_ms'];
		$minutes_ago = floor($seconds_ago/60);
		$seconds_ago = $seconds_ago - $minutes_ago*60;
		$time_ago = sprintf(__("%s minutes, %s seconds", 'updraftplus'), $minutes_ago, $seconds_ago);
		?><div class="updated show_admin_restore_in_progress_notice">
			<span class="unfinished-restoration"><strong><?php echo 'UpdraftPlus: '.__('Unfinished restoration', 'updraftplus'); ?> </strong></span><br>
			<p><?php printf(__('You have an unfinished restoration operation, begun %s ago.', 'updraftplus'), $time_ago);?></p>
		<form method="post" action="<?php echo UpdraftPlus_Options::admin_page_url().'?page=updraftplus'; ?>">
			<?php wp_nonce_field('updraftplus-credentialtest-nonce'); ?>
			<input id="updraft_restore_continue_action" type="hidden" name="action" value="updraft_restore_continue">
			<input type="hidden" name="restoreid" value="<?php echo $restore_jobdata['jobid'];?>" value="<?php echo esc_attr($restore_jobdata['jobid']);?>">
			<button onclick="jQuery('#updraft_restore_continue_action').val('updraft_restore_continue'); jQuery(this).parent('form').submit();" type="submit" class="button-primary"><?php _e('Continue restoration', 'updraftplus'); ?></button>
			<button onclick="jQuery('#updraft_restore_continue_action').val('updraft_restore_abort'); jQuery(this).parent('form').submit();" class="button-secondary"><?php _e('Dismiss', 'updraftplus');?></button>
		</form><?php
		echo "</div>";

	}

	/**
	 * This method will build the UpdraftPlus.com login form and echo it to the page.
	 *
	 * @param String  $option_page			  - the option page this form is being output to
	 * @param Boolean $tfa					  - indicates if we want to add the tfa UI
	 * @param Boolean $include_form_container - indicates if we want the form container
	 * @param Array	  $further_options		  - other options (see below for the possibilities + defaults)
	 *
	 * @return void
	 */
	public function build_credentials_form($option_page, $tfa = false, $include_form_container = true, $further_options = array()) {
	
		global $updraftplus;

		$further_options = wp_parse_args($further_options, array(
			'under_username' => __("Not yet got an account (it's free)? Go get one!", 'updraftplus'),
			'under_username_link' => $updraftplus->get_url('my-account')
		));
		
		if ($include_form_container) {
			$enter_credentials_begin = UpdraftPlus_Options::options_form_begin('', false, array(), 'updraftplus_com_login');
			if (is_multisite()) $enter_credentials_begin .= '<input type="hidden" name="action" value="update">';
		} else {
			$enter_credentials_begin = '<div class="updraftplus_com_login">';
		}

		$interested = htmlspecialchars(__('Interested in knowing about your UpdraftPlus.Com password security? Read about it here.', 'updraftplus'));

		$connect = htmlspecialchars(__('Connect', 'updraftplus'));

		$enter_credentials_end = '<p class="updraft-after-form-table">';
		
		if ($include_form_container) {
			$enter_credentials_end .= '<input type="submit" class="button-primary ud_connectsubmit" value="'.$connect.'" tabindex="1" />';
		} else {
			$enter_credentials_end .= '<button class="button-primary ud_connectsubmit" tabindex="1">'.$connect.'</button>';
		}
		
		$enter_credentials_end .= '<span class="updraftplus_spinner spinner">' . __('Processing', 'updraftplus') . '...</span></p>';

		$enter_credentials_end .= '<p class="updraft-after-form-table" style="font-size: 70%"><em><a href="https://updraftplus.com/faqs/tell-me-about-my-updraftplus-com-account/">'.$interested.'</a></em></p>';

		$enter_credentials_end .= $include_form_container ? '</form>' : '</div>';

		echo $enter_credentials_begin;

		$options = apply_filters('updraftplus_com_login_options', array("email" => "", "password" => ""));

		if ($include_form_container) {
			// We have to duplicate settings_fields() in order to set our referer
			// settings_fields(UDADDONS2_SLUG.'_options');

			$option_group = $option_page.'_options';
			echo "<input type='hidden' name='option_page' value='" . esc_attr($option_group) . "' />";
			echo '<input type="hidden" name="action" value="update" />';

			// wp_nonce_field("$option_group-options");

			// This one is used on multisite
			echo '<input type="hidden" name="tab" value="addons" />';

			$name = "_wpnonce";
			$action = esc_attr($option_group."-options");
			$nonce_field = '<input type="hidden" name="' . $name . '" value="' . wp_create_nonce($action) . '" />';
		
			echo $nonce_field;

			$referer = esc_attr(UpdraftPlus_Manipulation_Functions::wp_unslash($_SERVER['REQUEST_URI']));

			// This one is used on single site installs
			if (false === strpos($referer, '?')) {
				$referer .= '?tab=addons';
			} else {
				$referer .= '&tab=addons';
			}

			echo '<input type="hidden" name="_wp_http_referer" value="'.$referer.'" />';
			// End of duplication of settings-fields()
		}
		?>

		<h2> <?php _e('Connect with your UpdraftPlus.Com account', 'updraftplus'); ?></h2>
		<p class="updraftplus_com_login_status"></p>

		<table class="form-table">
			<tbody>
				<tr class="non_tfa_fields">
					<th><?php _e('Email', 'updraftplus'); ?></th>
					<td>
						<label for="<?php echo $option_page; ?>_options_email">
							<input id="<?php echo $option_page; ?>_options_email" type="text" size="36" name="<?php echo $option_page; ?>_options[email]" value="<?php echo htmlspecialchars($options['email']); ?>" tabindex="1" />
							<br/>
							<a target="_blank" href="<?php echo $further_options['under_username_link']; ?>"><?php echo $further_options['under_username']; ?></a>
						</label>
					</td>
				</tr>
				<tr class="non_tfa_fields">
					<th><?php _e('Password', 'updraftplus'); ?></th>
					<td>
						<label for="<?php echo $option_page; ?>_options_password">
							<input id="<?php echo $option_page; ?>_options_password" type="password" size="36" name="<?php echo $option_page; ?>_options[password]" value="<?php echo empty($options['password']) ? '' : htmlspecialchars($options['password']); ?>" tabindex="1" />
							<br/>
							<a target="_blank" href="<?php echo $updraftplus->get_url('lost-password'); ?>"><?php _e('Forgotten your details?', 'updraftplus'); ?></a>
						</label>
					</td>
				</tr>
				<?php
				if ('updraftplus-addons' == $option_page) {
				?>
					<tr>
						<th></th>
						<td>
							<label>
								<input type="checkbox" id="<?php echo $option_page; ?>_options_auto_updates" tabindex="2" data-updraft_settings_test="updraft_auto_updates" name="<?php echo $option_page; ?>_options[updraft_auto_update]" value="1" <?php if (UpdraftPlus_Options::get_updraft_option('updraft_auto_updates')) echo 'checked="checked"'; ?> />
								<?php _e('Ask WordPress to update UpdraftPlus automatically when an update is available', 'updraftplus');?>
							</label>
						</td>
					</tr>
					<?php
				}
				?>
				<?php
				if (isset($further_options['terms_and_conditions']) && isset($further_options['terms_and_conditions_link'])) {
				?>
					<tr class="non_tfa_fields">
						<th></th>
						<td>
							<input type="checkbox" class="<?php echo $option_page; ?>_terms_and_conditions" name="<?php echo $option_page; ?>_terms_and_conditions" value="1" tabindex="1">
							<a target="_blank" href="<?php echo $further_options['terms_and_conditions_link']; ?>"><?php echo $further_options['terms_and_conditions']; ?></a>
						</td>
					</tr>
					<?php
				}
				?>
				<?php if ($tfa) { ?>
				<tr class="tfa_fields" style="display:none;">
					<th><?php _e('One Time Password (check your OTP app to get this password)', 'updraftplus'); ?></th>
					<td>
						<label for="<?php echo $option_page; ?>_options_two_factor_code">
							<input id="<?php echo $option_page; ?>_options_two_factor_code" type="text" size="10" name="<?php echo $option_page; ?>_options[two_factor_code]" />
						</label>
					</td>
				</tr>	
				<?php } ?>
			</tbody>
		</table>

		<?php

		echo $enter_credentials_end;
	}

	/**
	 * Return widgetry for the 'backup now' modal.
	 * Don't optimise this method away; it's used by third-party plugins (e.g. EUM).
	 *
	 * @return String
	 */
	public function backupnow_modal_contents() {
		return $this->include_template('wp-admin/settings/backupnow-modal.php', true);
	}
	
	/**
	 * Also used by the auto-backups add-on
	 *
	 * @param  Boolean $wide_format       Whether to return data in a wide format
	 * @param  Boolean $print_active_jobs Whether to include currently active jobs
	 * @return String - the HTML output
	 */
	public function render_active_jobs_and_log_table($wide_format = false, $print_active_jobs = true) {
	?>
		<div id="updraft_activejobs_table">
			<?php $active_jobs = ($print_active_jobs) ? $this->print_active_jobs() : '';?>
			<div id="updraft_activejobsrow" class="<?php
				if (!$active_jobs && !$wide_format) {
					echo 'hidden';
				}
				if ($wide_format) {
					echo ".minimum-height";
				}
			?>">
				<div id="updraft_activejobs" class="<?php echo $wide_format ? 'wide-format' : ''; ?>">
					<?php echo $active_jobs;?>
				</div>
			</div>
			<div id="updraft_lastlogmessagerow">
				<?php if ($wide_format) {
					// Hide for now - too ugly
					?>
					<div class="last-message"><strong><?php _e('Last log message', 'updraftplus');?>:</strong><br>
						<span id="updraft_lastlogcontainer"><?php echo htmlspecialchars(UpdraftPlus_Options::get_updraft_lastmessage()); ?></span><br>
						<?php $this->most_recently_modified_log_link(); ?>
					</div>
				<?php } else { ?>
					<div>
						<strong><?php _e('Last log message', 'updraftplus');?>:</strong>
						<span id="updraft_lastlogcontainer"><?php echo htmlspecialchars(UpdraftPlus_Options::get_updraft_lastmessage()); ?></span><br>
						<?php $this->most_recently_modified_log_link(); ?>
					</div>
				<?php } ?>
			</div>
			<?php
			// Currently disabled - not sure who we want to show this to
			if (1==0 && !defined('UPDRAFTPLUS_NOADS_B')) {
				$feed = $updraftplus->get_updraftplus_rssfeed();
				if (is_a($feed, 'SimplePie')) {
					echo '<tr><th style="vertical-align:top;">'.__('Latest UpdraftPlus.com news:', 'updraftplus').'</th><td class="updraft_simplepie">';
					echo '<ul class="disc;">';
					foreach ($feed->get_items(0, 5) as $item) {
						echo '<li>';
						echo '<a href="'.esc_attr($item->get_permalink()).'">';
						echo htmlspecialchars($item->get_title());
						// D, F j, Y H:i
						echo "</a> (".htmlspecialchars($item->get_date('j F Y')).")";
						echo '</li>';
					}
					echo '</ul></td></tr>';
				}
			}
		?>
		</div>
		<?php
	}

	/**
	 * Output directly a link allowing download of the most recently modified log file
	 */
	private function most_recently_modified_log_link() {

		global $updraftplus;
		list($mod_time, $log_file, $nonce) = $updraftplus->last_modified_log();
		
		?>
			<a href="?page=updraftplus&amp;action=downloadlatestmodlog&amp;wpnonce=<?php echo wp_create_nonce('updraftplus_download'); ?>" <?php if (!$mod_time) echo 'style="display:none;"'; ?> class="updraft-log-link" onclick="event.preventDefault(); updraft_popuplog('');"><?php _e('Download most recently modified log file', 'updraftplus');?></a>
		<?php
	}
	
	public function settings_downloading_and_restoring($backup_history = array(), $return_result = false, $options = array()) {
		return $this->include_template('wp-admin/settings/downloading-and-restoring.php', $return_result, array('backup_history' => $backup_history, 'options' => $options));
	}
	
	/**
	 * Renders take backup content
	 */
	public function take_backup_content() {
		global $updraftplus;
		$updraft_dir = $updraftplus->backups_dir_location();
		$backup_disabled = UpdraftPlus_Filesystem_Functions::really_is_writable($updraft_dir) ? '' : 'disabled="disabled"';
		$this->include_template('wp-admin/settings/take-backup.php', false, array('backup_disabled' => $backup_disabled));
	}

	public function settings_debugrow($head, $content) {
		echo "<tr class=\"updraft_debugrow\"><th>$head</th><td>$content</td></tr>";
	}

	public function settings_advanced_tools($return_instead_of_echo = false, $pass_through = array()) {
		return $this->include_template('wp-admin/advanced/advanced-tools.php', $return_instead_of_echo, $pass_through);
	}

	private function print_delete_old_dirs_form($include_blurb = true, $include_div = true) {
		if ($include_blurb) {
			if ($include_div) {
				echo '<div id="updraft_delete_old_dirs_pagediv" class="updated delete-old-directories">';
			}
			echo '<p>'.__('Your WordPress install has old directories from its state before you restored/migrated (technical information: these are suffixed with -old). You should press this button to delete them as soon as you have verified that the restoration worked.', 'updraftplus').'</p>';
		}
		?>
		<form method="post" action="<?php echo esc_url(add_query_arg(array('error' => false, 'updraft_restore_success' => false, 'action' => false, 'page' => 'updraftplus'))); ?>">
			<?php wp_nonce_field('updraftplus-credentialtest-nonce', 'updraft_delete_old_dirs_nonce'); ?>
			<input type="hidden" name="action" value="updraft_delete_old_dirs">
			<input type="submit" class="button-primary" value="<?php echo esc_attr(__('Delete Old Directories', 'updraftplus'));?>">
		</form>
		<?php
		if ($include_blurb && $include_div) echo '</div>';
	}

	/**
	 * Return cron status information about a specified in-progress job
	 *
	 * @param Boolean|String $job_id - the job to get information about; or, if not specified, all jobs
	 *
	 * @return Array|Boolean - the requested information, or false if it was not found. Format differs depending on whether info on all jobs, or a single job, was requested.
	 */
	public function get_cron($job_id = false) {
	
		$cron = get_option('cron');
		if (!is_array($cron)) $cron = array();
		if (false === $job_id) return $cron;

		foreach ($cron as $time => $job) {
			if (!isset($job['updraft_backup_resume'])) continue;
			foreach ($job['updraft_backup_resume'] as $hook => $info) {
				if (isset($info['args'][1]) && $job_id == $info['args'][1]) {
					global $updraftplus;
					$jobdata = $updraftplus->jobdata_getarray($job_id);
					return is_array($jobdata) ? array($time, $jobdata) : false;
				}
			}
		}
	}

	/**
	 * Print active Jobs
	 *
	 * @param  boolean $this_job_only A value for $this_job_only also causes something to always be returned (to allow detection of the job having started on the front-end)
	 * @return [type]                 [description]
	 */
	private function print_active_jobs($this_job_only = false) {
		$cron = $this->get_cron();
		$ret = '';

		foreach ($cron as $time => $job) {
			if (isset($job['updraft_backup_resume'])) {
				foreach ($job['updraft_backup_resume'] as $hook => $info) {
					if (isset($info['args'][1])) {
						$job_id = $info['args'][1];
						if (false === $this_job_only || $job_id == $this_job_only) {
							$ret .= $this->print_active_job($job_id, false, $time, $info['args'][0]);
						}
					}
				}
			}
		}
		// A value for $this_job_only implies that output is required
		if (false !== $this_job_only && !$ret) {
			$ret = $this->print_active_job($this_job_only);
			if ('' == $ret) {
				// The presence of the exact ID matters to the front-end - indicates that the backup job has at least begun
				$ret = '<div class="active-jobs updraft_finished" id="updraft-jobid-'.$this_job_only.'"><em>'.__('The backup has finished running', 'updraftplus').'</em> - <a class="updraft-log-link" data-jobid="'.$this_job_only.'">'.__('View Log', 'updraftplus').'</a></div>';
			}
		}

		return $ret;
	}

	/**
	 * Print the HTML for a particular job
	 *
	 * @param String		  $job_id		   - the job identifier/nonce
	 * @param Boolean		  $is_oneshot	   - whether this backup should be 'one shot', i.e. no resumptions
	 * @param Boolean|Integer $time
	 * @param Integer		  $next_resumption
	 *
	 * @return String
	 */
	private function print_active_job($job_id, $is_oneshot = false, $time = false, $next_resumption = false) {

		$ret = '';
		
		global $updraftplus;
		$jobdata = $updraftplus->jobdata_getarray($job_id);

		if (false == apply_filters('updraftplus_print_active_job_continue', true, $is_oneshot, $next_resumption, $jobdata)) return '';

		if (!isset($jobdata['backup_time'])) return '';

		$backupable_entities = $updraftplus->get_backupable_file_entities(true, true);

		$began_at = (isset($jobdata['backup_time'])) ? get_date_from_gmt(gmdate('Y-m-d H:i:s', (int) $jobdata['backup_time']), 'D, F j, Y H:i') : '?';

		$remote_sent = (!empty($jobdata['service']) && ((is_array($jobdata['service']) && in_array('remotesend', $jobdata['service'])) || 'remotesend' === $jobdata['service'])) ? true : false;

		$jobstatus = empty($jobdata['jobstatus']) ? 'unknown' : $jobdata['jobstatus'];
		$stage = 0;
		switch ($jobstatus) {
			// Stage 0
			case 'begun':
			$curstage = __('Backup begun', 'updraftplus');
				break;
			// Stage 1
			case 'filescreating':
			$stage = 1;
			$curstage = __('Creating file backup zips', 'updraftplus');
			if (!empty($jobdata['filecreating_substatus']) && isset($backupable_entities[$jobdata['filecreating_substatus']['e']]['description'])) {
			
				$sdescrip = preg_replace('/ \(.*\)$/', '', $backupable_entities[$jobdata['filecreating_substatus']['e']]['description']);
				if (strlen($sdescrip) > 20 && isset($jobdata['filecreating_substatus']['e']) && is_array($jobdata['filecreating_substatus']['e']) && isset($backupable_entities[$jobdata['filecreating_substatus']['e']]['shortdescription'])) $sdescrip = $backupable_entities[$jobdata['filecreating_substatus']['e']]['shortdescription'];
				$curstage .= ' ('.$sdescrip.')';
				if (isset($jobdata['filecreating_substatus']['i']) && isset($jobdata['filecreating_substatus']['t'])) {
					$stage = min(2, 1 + ($jobdata['filecreating_substatus']['i']/max($jobdata['filecreating_substatus']['t'], 1)));
				}
			}
				break;
			case 'filescreated':
			$stage = 2;
			$curstage = __('Created file backup zips', 'updraftplus');
				break;
			// Stage 4
			case 'clonepolling':
				$stage = 4;
				$curstage = __('Clone server being provisioned and booted (can take several minutes)', 'updraftplus');
				break;
			case 'clouduploading':
			$stage = 4;
			$curstage = __('Uploading files to remote storage', 'updraftplus');
			if ($remote_sent) $curstage = __('Sending files to remote site', 'updraftplus');
			if (isset($jobdata['uploading_substatus']['t']) && isset($jobdata['uploading_substatus']['i'])) {
				$t = max((int) $jobdata['uploading_substatus']['t'], 1);
				$i = min($jobdata['uploading_substatus']['i']/$t, 1);
				$p = min($jobdata['uploading_substatus']['p'], 1);
				$pd = $i + $p/$t;
				$stage = 4 + $pd;
				$curstage .= ' '.sprintf(__('(%s%%, file %s of %s)', 'updraftplus'), floor(100*$pd), $jobdata['uploading_substatus']['i']+1, $t);
			}
				break;
			case 'pruning':
			$stage = 5;
			$curstage = __('Pruning old backup sets', 'updraftplus');
				break;
			case 'resumingforerrors':
			$stage = -1;
			$curstage = __('Waiting until scheduled time to retry because of errors', 'updraftplus');
				break;
			// Stage 6
			case 'finished':
			$stage = 6;
			$curstage = __('Backup finished', 'updraftplus');
				break;
			default:
			// Database creation and encryption occupies the space from 2 to 4. Databases are created then encrypted, then the next database is created/encrypted, etc.
			if ('dbcreated' == substr($jobstatus, 0, 9)) {
				$jobstatus = 'dbcreated';
				$whichdb = substr($jobstatus, 9);
				if (!is_numeric($whichdb)) $whichdb = 0;
				$howmanydbs = max((empty($jobdata['backup_database']) || !is_array($jobdata['backup_database'])) ? 1 : count($jobdata['backup_database']), 1);
				$perdbspace = 2/$howmanydbs;

				$stage = min(4, 2 + ($whichdb+2)*$perdbspace);

				$curstage = __('Created database backup', 'updraftplus');

			} elseif ('dbcreating' == substr($jobstatus, 0, 10)) {
				$whichdb = substr($jobstatus, 10);
				if (!is_numeric($whichdb)) $whichdb = 0;
				$howmanydbs = (empty($jobdata['backup_database']) || !is_array($jobdata['backup_database'])) ? 1 : count($jobdata['backup_database']);
				$perdbspace = 2/$howmanydbs;
				$jobstatus = 'dbcreating';

				$stage = min(4, 2 + $whichdb*$perdbspace);

				$curstage = __('Creating database backup', 'updraftplus');
				if (!empty($jobdata['dbcreating_substatus']['t'])) {
					$curstage .= ' ('.sprintf(__('table: %s', 'updraftplus'), $jobdata['dbcreating_substatus']['t']).')';
					if (!empty($jobdata['dbcreating_substatus']['i']) && !empty($jobdata['dbcreating_substatus']['a'])) {
						$substage = max(0.001, ($jobdata['dbcreating_substatus']['i'] / max($jobdata['dbcreating_substatus']['a'], 1)));
						$stage += $substage * $perdbspace * 0.5;
					}
				}
			} elseif ('dbencrypting' == substr($jobstatus, 0, 12)) {
				$whichdb = substr($jobstatus, 12);
				if (!is_numeric($whichdb)) $whichdb = 0;
				$howmanydbs = (empty($jobdata['backup_database']) || !is_array($jobdata['backup_database'])) ? 1 : count($jobdata['backup_database']);
				$perdbspace = 2/$howmanydbs;
				$stage = min(4, 2 + $whichdb*$perdbspace + $perdbspace*0.5);
				$jobstatus = 'dbencrypting';
				$curstage = __('Encrypting database', 'updraftplus');
			} elseif ('dbencrypted' == substr($jobstatus, 0, 11)) {
				$whichdb = substr($jobstatus, 11);
				if (!is_numeric($whichdb)) $whichdb = 0;
				$howmanydbs = (empty($jobdata['backup_database']) || !is_array($jobdata['backup_database'])) ? 1 : count($jobdata['backup_database']);
				$jobstatus = 'dbencrypted';
				$perdbspace = 2/$howmanydbs;
				$stage = min(4, 2 + $whichdb*$perdbspace + $perdbspace);
				$curstage = __('Encrypted database', 'updraftplus');
			} else {
				$curstage = __('Unknown', 'updraftplus');
			}
		}

		$runs_started = (empty($jobdata['runs_started'])) ? array() : $jobdata['runs_started'];
		$time_passed = (empty($jobdata['run_times'])) ? array() : $jobdata['run_times'];
		$last_checkin_ago = -1;
		if (is_array($time_passed)) {
			foreach ($time_passed as $run => $passed) {
				if (isset($runs_started[$run])) {
					$time_ago = microtime(true) - ($runs_started[$run] + $time_passed[$run]);
					if ($time_ago < $last_checkin_ago || -1 == $last_checkin_ago) $last_checkin_ago = $time_ago;
				}
			}
		}

		$next_res_after = (int) $time-time();
		$next_res_txt = ($is_oneshot) ? '' : sprintf(__("next resumption: %d (after %ss)", 'updraftplus'), $next_resumption, $next_res_after). ' ';
		$last_activity_txt = ($last_checkin_ago >= 0) ? sprintf(__('last activity: %ss ago', 'updraftplus'), floor($last_checkin_ago)).' ' : '';

		if (($last_checkin_ago < 50 && $next_res_after>30) || $is_oneshot) {
			$show_inline_info = $last_activity_txt;
			$title_info = $next_res_txt;
		} else {
			$show_inline_info = $next_res_txt;
			$title_info = $last_activity_txt;
		}
		
		$ret .= '<div class="updraft_row">';
		
		$ret .= '<div class="updraft_col"><div class="updraft_jobtimings next-resumption';

		if (!empty($jobdata['is_autobackup'])) $ret .= ' isautobackup';

		$is_clone = empty($jobdata['clone_job']) ? '0' : '1';

		$clone_url = empty($jobdata['clone_url']) ? false : true;
		
		$ret .= '" data-jobid="'.$job_id.'" data-lastactivity="'.(int) $last_checkin_ago.'" data-nextresumption="'.$next_resumption.'" data-nextresumptionafter="'.$next_res_after.'" title="'.esc_attr(sprintf(__('Job ID: %s', 'updraftplus'), $job_id)).$title_info.'">'.$began_at.
		'</div></div>';

		$ret .= '<div class="updraft_col updraft_progress_container">';
			// Existence of the 'updraft-jobid-(id)' id is checked for in other places, so do not modify this
			$ret .= '<div class="job-id" data-isclone="'.$is_clone.'" id="updraft-jobid-'.$job_id.'">';

			if ($clone_url) $ret .= '<div class="updraft_clone_url" data-clone_url="' . $jobdata['clone_url'] . '"></div>';
	
			$ret .= apply_filters('updraft_printjob_beforewarnings', '', $jobdata, $job_id);
	
			if (!empty($jobdata['warnings']) && is_array($jobdata['warnings'])) {
				$ret .= '<ul class="disc">';
				foreach ($jobdata['warnings'] as $warning) {
					$ret .= '<li>'.sprintf(__('Warning: %s', 'updraftplus'), make_clickable(htmlspecialchars($warning))).'</li>';
				}
				$ret .= '</ul>';
			}
	
			$ret .= '<div class="curstage">';
			// $ret .= '<span class="curstage-info">'.htmlspecialchars($curstage).'</span>';
			$ret .= htmlspecialchars($curstage);
			// we need to add this data-progress attribute in order to be able to update the progress bar in UDC

			$ret .= '<div class="updraft_percentage" data-info="'.esc_attr($curstage).'" data-progress="'.(($stage>0) ? (ceil((100/6)*$stage)) : '0').'" style="height: 100%; width:'.(($stage>0) ? (ceil((100/6)*$stage)) : '0').'%"></div>';
			$ret .= '</div></div>';
	
			$ret .= '<div class="updraft_last_activity">';
			
			$ret .= $show_inline_info;
			if (!empty($show_inline_info)) $ret .= ' - ';

			$file_nonce = empty($jobdata['file_nonce']) ? $job_id : $jobdata['file_nonce'];
			
			$ret .= '<a data-fileid="'.$file_nonce.'" data-jobid="'.$job_id.'" href="'.UpdraftPlus_Options::admin_page_url().'?page=updraftplus&action=downloadlog&updraftplus_backup_nonce='.$file_nonce.'" class="updraft-log-link">'.__('show log', 'updraftplus').'</a>';
				if (!$is_oneshot) $ret .=' - <a href="#" data-jobid="'.$job_id.'" title="'.esc_attr(__('Note: the progress bar below is based on stages, NOT time. Do not stop the backup simply because it seems to have remained in the same place for a while - that is normal.', 'updraftplus')).'" class="updraft_jobinfo_delete">'.__('stop', 'updraftplus').'</a>';
			$ret .= '</div>';
		
		$ret .= '</div></div>';

		return $ret;

	}

	private function delete_old_dirs_go($show_return = true) {
		echo $show_return ? '<h1>UpdraftPlus - '.__('Remove old directories', 'updraftplus').'</h1>' : '<h2>'.__('Remove old directories', 'updraftplus').'</h2>';

		if ($this->delete_old_dirs()) {
			echo '<p>'.__('Old directories successfully removed.', 'updraftplus').'</p><br>';
		} else {
			echo '<p>',__('Old directory removal failed for some reason. You may want to do this manually.', 'updraftplus').'</p><br>';
		}
		if ($show_return) echo '<b>'.__('Actions', 'updraftplus').':</b> <a href="'.UpdraftPlus_Options::admin_page_url().'?page=updraftplus">'.__('Return to UpdraftPlus Configuration', 'updraftplus').'</a>';
	}

	/**
	 * Deletes the -old directories that are created when a backup is restored.
	 *
	 * @return String. Can also exit (something we ought to probably review)
	 */
	private function delete_old_dirs() {
		global $wp_filesystem, $updraftplus;
		$credentials = request_filesystem_credentials(wp_nonce_url(UpdraftPlus_Options::admin_page_url()."?page=updraftplus&action=updraft_delete_old_dirs", 'updraftplus-credentialtest-nonce'));
		WP_Filesystem($credentials);
		if ($wp_filesystem->errors->get_error_code()) {
			foreach ($wp_filesystem->errors->get_error_messages() as $message) show_message($message);
			exit;
		}
		// From WP_CONTENT_DIR - which contains 'themes'
		$ret = $this->delete_old_dirs_dir($wp_filesystem->wp_content_dir());

		$updraft_dir = $updraftplus->backups_dir_location();
		if ($updraft_dir) {
			$ret4 = $updraft_dir ? $this->delete_old_dirs_dir($updraft_dir, false) : true;
		} else {
			$ret4 = true;
		}

		$plugs = untrailingslashit($wp_filesystem->wp_plugins_dir());
		if ($wp_filesystem->is_dir($plugs.'-old')) {
			echo "<strong>".__('Delete', 'updraftplus').": </strong>plugins-old: ";
			if (!$wp_filesystem->delete($plugs.'-old', true)) {
				$ret3 = false;
				echo "<strong>".__('Failed', 'updraftplus')."</strong><br>";
				echo $updraftplus->log_permission_failure_message($wp_filesystem->wp_content_dir(), 'Delete '.$plugs.'-old');
			} else {
				$ret3 = true;
				echo "<strong>".__('OK', 'updraftplus')."</strong><br>";
			}
		} else {
			$ret3 = true;
		}

		return $ret && $ret3 && $ret4;
	}

	private function delete_old_dirs_dir($dir, $wpfs = true) {

		$dir = trailingslashit($dir);

		global $wp_filesystem, $updraftplus;

		if ($wpfs) {
			$list = $wp_filesystem->dirlist($dir);
		} else {
			$list = scandir($dir);
		}
		if (!is_array($list)) return false;

		$ret = true;
		foreach ($list as $item) {
			$name = (is_array($item)) ? $item['name'] : $item;
			if ("-old" == substr($name, -4, 4)) {
				// recursively delete
				print "<strong>".__('Delete', 'updraftplus').": </strong>".htmlspecialchars($name).": ";

				if ($wpfs) {
					if (!$wp_filesystem->delete($dir.$name, true)) {
						$ret = false;
						echo "<strong>".__('Failed', 'updraftplus')."</strong><br>";
						echo $updraftplus->log_permission_failure_message($dir, 'Delete '.$dir.$name);
					} else {
						echo "<strong>".__('OK', 'updraftplus')."</strong><br>";
					}
				} else {
					if (UpdraftPlus_Filesystem_Functions::remove_local_directory($dir.$name)) {
						echo "<strong>".__('OK', 'updraftplus')."</strong><br>";
					} else {
						$ret = false;
						echo "<strong>".__('Failed', 'updraftplus')."</strong><br>";
						echo $updraftplus->log_permission_failure_message($dir, 'Delete '.$dir.$name);
					}
				}
			}
		}
		return $ret;
	}

	/**
	 * The aim is to get a directory that is writable by the webserver, because that's the only way we can create zip files
	 *
	 * @return Boolean|WP_Error true if successful, otherwise false or a WP_Error
	 */
	private function create_backup_dir() {

		global $wp_filesystem, $updraftplus;

		if (false === ($credentials = request_filesystem_credentials(UpdraftPlus_Options::admin_page().'?page=updraftplus&action=updraft_create_backup_dir&nonce='.wp_create_nonce('create_backup_dir')))) {
			return false;
		}

		if (!WP_Filesystem($credentials)) {
			// our credentials were no good, ask the user for them again
			request_filesystem_credentials(UpdraftPlus_Options::admin_page().'?page=updraftplus&action=updraft_create_backup_dir&nonce='.wp_create_nonce('create_backup_dir'), '', true);
			return false;
		}

		$updraft_dir = $updraftplus->backups_dir_location();

		$default_backup_dir = $wp_filesystem->find_folder(dirname($updraft_dir)).basename($updraft_dir);

		$updraft_dir = ($updraft_dir) ? $wp_filesystem->find_folder(dirname($updraft_dir)).basename($updraft_dir) : $default_backup_dir;

		if (!$wp_filesystem->is_dir($default_backup_dir) && !$wp_filesystem->mkdir($default_backup_dir, 0775)) {
			$wperr = new WP_Error;
			if ($wp_filesystem->errors->get_error_code()) {
				foreach ($wp_filesystem->errors->get_error_messages() as $message) {
					$wperr->add('mkdir_error', $message);
				}
				return $wperr;
			} else {
				return new WP_Error('mkdir_error', __('The request to the filesystem to create the directory failed.', 'updraftplus'));
			}
		}

		if ($wp_filesystem->is_dir($default_backup_dir)) {

			if (UpdraftPlus_Filesystem_Functions::really_is_writable($updraft_dir)) return true;

			@$wp_filesystem->chmod($default_backup_dir, 0775);
			if (UpdraftPlus_Filesystem_Functions::really_is_writable($updraft_dir)) return true;

			@$wp_filesystem->chmod($default_backup_dir, 0777);

			if (UpdraftPlus_Filesystem_Functions::really_is_writable($updraft_dir)) {
				echo '<p>'.__('The folder was created, but we had to change its file permissions to 777 (world-writable) to be able to write to it. You should check with your hosting provider that this will not cause any problems', 'updraftplus').'</p>';
				return true;
			} else {
				@$wp_filesystem->chmod($default_backup_dir, 0775);
				$show_dir = (0 === strpos($default_backup_dir, ABSPATH)) ? substr($default_backup_dir, strlen(ABSPATH)) : $default_backup_dir;
				return new WP_Error('writable_error', __('The folder exists, but your webserver does not have permission to write to it.', 'updraftplus').' '.__('You will need to consult with your web hosting provider to find out how to set permissions for a WordPress plugin to write to the directory.', 'updraftplus').' ('.$show_dir.')');
			}
		}

		return true;
	}

	/**
	 * scans the content dir to see if any -old dirs are present
	 *
	 * @param  Boolean $print_as_comment Echo information in an HTML comment
	 * @return Boolean
	 */
	private function scan_old_dirs($print_as_comment = false) {
		global $updraftplus;
		$dirs = scandir(untrailingslashit(WP_CONTENT_DIR));
		if (!is_array($dirs)) $dirs = array();
		$dirs_u = @scandir($updraftplus->backups_dir_location());
		if (!is_array($dirs_u)) $dirs_u = array();
		foreach (array_merge($dirs, $dirs_u) as $dir) {
			if (preg_match('/-old$/', $dir)) {
				if ($print_as_comment) echo '<!--'.htmlspecialchars($dir).'-->';
				return true;
			}
		}
		// No need to scan ABSPATH - we don't backup there
		if (is_dir(untrailingslashit(WP_PLUGIN_DIR).'-old')) {
			if ($print_as_comment) echo '<!--'.htmlspecialchars(untrailingslashit(WP_PLUGIN_DIR).'-old').'-->';
			return true;
		}
		return false;
	}

	/**
	 * Outputs html for a storage method using the parameters passed in, this version should be removed when all remote storages use the multi version
	 *
	 * @param String $method   a list of methods to be used when
	 * @param String $header   the table header content
	 * @param String $contents the table contents
	 */
	public function storagemethod_row($method, $header, $contents) {
		?>
			<tr class="updraftplusmethod <?php echo $method;?>">
				<th><?php echo $header;?></th>
				<td><?php echo $contents;?></td>
			</tr>
		<?php
	}

	/**
	 * Outputs html for a storage method using the parameters passed in, this version of the method is compatible with multi storage options
	 *
	 * @param  string $classes  a list of classes to be used when
	 * @param  string $header   the table header content
	 * @param  string $contents the table contents
	 */
	public function storagemethod_row_multi($classes, $header, $contents) {
		?>
			<tr class="<?php echo $classes;?>">
				<th><?php echo $header;?></th>
				<td><?php echo $contents;?></td>
			</tr>
		<?php
	}
	
	/**
	 * Returns html for a storage method using the parameters passed in, this version of the method is compatible with multi storage options
	 *
	 * @param  string $classes  a list of classes to be used when
	 * @param  string $header   the table header content
	 * @param  string $contents the table contents
	 * @return string handlebars html template
	 */
	public function get_storagemethod_row_multi_configuration_template($classes, $header, $contents) {
		return '<tr class="'.esc_attr($classes).'">
					<th>'.$header.'</th>
					<td>'.$contents.'</td>
				</tr>';
	}

	/**
	 * Get HTML suitable for the admin area for the status of the last backup
	 *
	 * @return String
	 */
	public function last_backup_html() {

		global $updraftplus;

		$updraft_last_backup = UpdraftPlus_Options::get_updraft_option('updraft_last_backup');

		if ($updraft_last_backup) {

			// Convert to GMT, then to blog time
			$backup_time = (int) $updraft_last_backup['backup_time'];

			$print_time = get_date_from_gmt(gmdate('Y-m-d H:i:s', $backup_time), 'D, F j, Y H:i');

			if (empty($updraft_last_backup['backup_time_incremental'])) {
				$last_backup_text = "<span style=\"color:".(($updraft_last_backup['success']) ? 'green' : 'black').";\">".$print_time.'</span>';
			} else {
				$inc_time = get_date_from_gmt(gmdate('Y-m-d H:i:s', $updraft_last_backup['backup_time_incremental']), 'D, F j, Y H:i');
				$last_backup_text = "<span style=\"color:".(($updraft_last_backup['success']) ? 'green' : 'black').";\">$inc_time</span> (".sprintf(__('incremental backup; base backup: %s', 'updraftplus'), $print_time).')';
			}

			$last_backup_text .= '<br>';

			// Show errors + warnings
			if (is_array($updraft_last_backup['errors'])) {
				foreach ($updraft_last_backup['errors'] as $err) {
					$level = (is_array($err)) ? $err['level'] : 'error';
					$message = (is_array($err)) ? $err['message'] : $err;
					$last_backup_text .= ('warning' == $level) ? "<span style=\"color:orange;\">" : "<span style=\"color:red;\">";
					if ('warning' == $level) {
						$message = sprintf(__("Warning: %s", 'updraftplus'), make_clickable(htmlspecialchars($message)));
					} else {
						$message = htmlspecialchars($message);
					}
					$last_backup_text .= $message;
					$last_backup_text .= '</span><br>';
				}
			}

			// Link log
			if (!empty($updraft_last_backup['backup_nonce'])) {
				$updraft_dir = $updraftplus->backups_dir_location();

				$potential_log_file = $updraft_dir."/log.".$updraft_last_backup['backup_nonce'].".txt";
				if (is_readable($potential_log_file)) $last_backup_text .= "<a href=\"?page=updraftplus&action=downloadlog&updraftplus_backup_nonce=".$updraft_last_backup['backup_nonce']."\" class=\"updraft-log-link\" onclick=\"event.preventDefault(); updraft_popuplog('".$updraft_last_backup['backup_nonce']."');\">".__('Download log file', 'updraftplus')."</a>";
			}

		} else {
			$last_backup_text = "<span style=\"color:blue;\">".__('No backup has been completed', 'updraftplus')."</span>";
		}

		return $last_backup_text;

	}

	/**
	 * Get a list of backup intervals
	 *
	 * @return Array - keys are used as identifiers in the UI drop-down; values are user-displayed text describing the interval
	 */
	public function get_intervals() {
		return apply_filters('updraftplus_backup_intervals', array(
			'manual' => _x("Manual", 'i.e. Non-automatic', 'updraftplus'),
			'every4hours' => sprintf(__("Every %s hours", 'updraftplus'), '4'),
			'every8hours' => sprintf(__("Every %s hours", 'updraftplus'), '8'),
			'twicedaily' => sprintf(__("Every %s hours", 'updraftplus'), '12'),
			'daily' => __("Daily", 'updraftplus'),
			'weekly' => __("Weekly", 'updraftplus'),
			'fortnightly' => __("Fortnightly", 'updraftplus'),
			'monthly' => __("Monthly", 'updraftplus')
		));
	}
	
	public function really_writable_message($really_is_writable, $updraft_dir) {
		if ($really_is_writable) {
			$dir_info = '<span style="color:green;">'.__('Backup directory specified is writable, which is good.', 'updraftplus').'</span>';
		} else {
			$dir_info = '<span style="color:red;">';
			if (!is_dir($updraft_dir)) {
				$dir_info .= __('Backup directory specified does <b>not</b> exist.', 'updraftplus');
			} else {
				$dir_info .= __('Backup directory specified exists, but is <b>not</b> writable.', 'updraftplus');
			}
			$dir_info .= '<span class="updraft-directory-not-writable-blurb"><span class="directory-permissions"><a class="updraft_create_backup_dir" href="'.UpdraftPlus_Options::admin_page_url().'?page=updraftplus&action=updraft_create_backup_dir&nonce='.wp_create_nonce('create_backup_dir').'">'.__('Follow this link to attempt to create the directory and set the permissions', 'updraftplus').'</a></span>, '.__('or, to reset this option', 'updraftplus').' <a href="'.UpdraftPlus::get_current_clean_url().'" class="updraft_backup_dir_reset">'.__('press here', 'updraftplus').'</a>. '.__('If that is unsuccessful check the permissions on your server or change it to another directory that is writable by your web server process.', 'updraftplus').'</span>';
		}
		return $dir_info;
	}

	/**
	 * Directly output the settings form (suitable for the admin area)
	 *
	 * @param Array $options current options (passed on to the template)
	 */
	public function settings_formcontents($options = array()) {
		$this->include_template('wp-admin/settings/form-contents.php', false, array(
			'options' => $options
		));
		if (!(defined('UPDRAFTCENTRAL_COMMAND') && UPDRAFTCENTRAL_COMMAND)) {
			$this->include_template('wp-admin/settings/exclude-modal.php', false);
		}
	}

	public function get_settings_js($method_objects, $really_is_writable, $updraft_dir, $active_service) {// phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found -- Filter use

		global $updraftplus;
		
		ob_start();
		?>
		jQuery(document).ready(function() {
			<?php
				if (!$really_is_writable) echo "jQuery('.backupdirrow').show();\n";
			?>
			<?php
				if (!empty($active_service)) {
					if (is_array($active_service)) {
						foreach ($active_service as $serv) {
							echo "jQuery('.${serv}').show();\n";
						}
					} else {
						echo "jQuery('.${active_service}').show();\n";
					}
				} else {
					echo "jQuery('.none').show();\n";
				}
				foreach ($updraftplus->backup_methods as $method => $description) {
					// already done: require_once(UPDRAFTPLUS_DIR.'/methods/'.$method.'.php');
					$call_method = "UpdraftPlus_BackupModule_$method";
					if (method_exists($call_method, 'config_print_javascript_onready')) {
						$method_objects[$method]->config_print_javascript_onready();
					}
				}
			?>
		});
		<?php
		$ret = ob_get_contents();
		ob_end_clean();
		return $ret;
	}
	
	/**
	 * Return the HTML for the files selector widget
	 *
	 * @param  String		  $prefix                 Prefix for the ID
	 * @param  Boolean		  $show_exclusion_options True or False for exclusion options
	 * @param  Boolean|String $include_more           $include_more can be (bool) or (string)"sometimes"
	 *
	 * @return String
	 */
	public function files_selector_widgetry($prefix = '', $show_exclusion_options = true, $include_more = true) {

		$ret = '';

		global $updraftplus;
		$for_updraftcentral = defined('UPDRAFTCENTRAL_COMMAND') && UPDRAFTCENTRAL_COMMAND;
		$backupable_entities = $updraftplus->get_backupable_file_entities(true, true);
		// The true (default value if non-existent) here has the effect of forcing a default of on.
		$include_more_paths = UpdraftPlus_Options::get_updraft_option('updraft_include_more_path');
		foreach ($backupable_entities as $key => $info) {
			$included = (UpdraftPlus_Options::get_updraft_option("updraft_include_$key", apply_filters("updraftplus_defaultoption_include_".$key, true))) ? 'checked="checked"' : "";
			if ('others' == $key || 'uploads' == $key) {

				$data_toggle_exclude_field = $show_exclusion_options ? 'data-toggle_exclude_field="'.$key.'"' : '';
			
				$ret .= '<label '.(('others' == $key) ? 'title="'.sprintf(__('Your wp-content directory server path: %s', 'updraftplus'), WP_CONTENT_DIR).'" ' : '').' for="'.$prefix.'updraft_include_'.$key.'" class="updraft_checkbox"><input class="updraft_include_entity" id="'.$prefix.'updraft_include_'.$key.'" '.$data_toggle_exclude_field.' type="checkbox" name="updraft_include_'.$key.'" value="1" '.$included.'> '.(('others' == $key) ? __('Any other directories found inside wp-content', 'updraftplus') : htmlspecialchars($info['description'])).'</label>';
				
				if ($show_exclusion_options) {
					$include_exclude = UpdraftPlus_Options::get_updraft_option('updraft_include_'.$key.'_exclude', ('others' == $key) ? UPDRAFT_DEFAULT_OTHERS_EXCLUDE : UPDRAFT_DEFAULT_UPLOADS_EXCLUDE);

					$display = ($included) ? '' : 'class="updraft-hidden" style="display:none;"';
					$exclude_container_class = $prefix.'updraft_include_'.$key.'_exclude';
					if (!$for_updraftcentral)  $exclude_container_class .= '_container';

					$ret .= "<div id=\"".$exclude_container_class."\" $display class=\"updraft_exclude_container\">";

					$ret .= '<label class="updraft-exclude-label" for="'.$prefix.'updraft_include_'.$key.'_exclude">'.__('Exclude these from', 'updraftplus').' '.htmlspecialchars($info['description']).':</label>';

					$exclude_input_type = $for_updraftcentral ? "text" : "hidden";
					$exclude_input_extra_attr = $for_updraftcentral ? 'title="'.__('If entering multiple files/directories, then separate them with commas. For entities at the top level, you can use a * at the start or end of the entry as a wildcard.', 'updraftplus').'" size="54"' : '';
					$ret .= '<input type="'.$exclude_input_type.'" id="'.$prefix.'updraft_include_'.$key.'_exclude" name="updraft_include_'.$key.'_exclude" '.$exclude_input_extra_attr.' value="'.htmlspecialchars($include_exclude).'" />';
					
					if (!$for_updraftcentral) {
						global $updraftplus;
						$backupable_file_entities = $updraftplus->get_backupable_file_entities();
						
						if ('uploads' == $key) {
							$path = UpdraftPlus_Manipulation_Functions::wp_normalize_path($backupable_file_entities['uploads']);
						} elseif ('others' == $key) {
							$path = UpdraftPlus_Manipulation_Functions::wp_normalize_path($backupable_file_entities['others']);
						}
						$ret .= $this->include_template('wp-admin/settings/file-backup-exclude.php', true, array(
							'key' => $key,
							'include_exclude' => $include_exclude,
							'path' => $path,
							'show_exclusion_options' => $show_exclusion_options,
						));
					}
					$ret .= '</div>';
				}

			} else {

				if ('more' != $key || true === $include_more || ('sometimes' === $include_more && !empty($include_more_paths))) {
				
					$data_toggle_exclude_field = $show_exclusion_options ? 'data-toggle_exclude_field="'.$key.'"' : '';
				
					$ret .= "<label for=\"".$prefix."updraft_include_$key\"".((isset($info['htmltitle'])) ? ' title="'.htmlspecialchars($info['htmltitle']).'"' : '')." class=\"updraft_checkbox\"><input class=\"updraft_include_entity\" $data_toggle_exclude_field id=\"".$prefix."updraft_include_$key\" type=\"checkbox\" name=\"updraft_include_$key\" value=\"1\" $included /> ".htmlspecialchars($info['description']);

					$ret .= "</label>";
					$ret .= apply_filters("updraftplus_config_option_include_$key", '', $prefix, $for_updraftcentral);
				}
			}
		}

		return $ret;
	}

	/**
	 * Output or echo HTML for an error condition relating to a remote storage method
	 *
	 * @param String  $text		  - the text of the message; this should already be escaped (no more is done)
	 * @param String  $extraclass - a CSS class for the resulting DOM node
	 * @param Integer $echo		  - if set, then the results will be echoed as well as returned
	 *
	 * @return String - the results
	 */
	public function show_double_warning($text, $extraclass = '', $echo = true) {

		$ret = "<div class=\"error updraftplusmethod $extraclass\"><p>$text</p></div>";
		$ret .= "<p class=\"double-warning\">$text</p>";

		if ($echo) echo $ret;
		return $ret;

	}

	public function optionfilter_split_every($value) {
		return max(absint($value), UPDRAFTPLUS_SPLIT_MIN);
	}

	/**
	 * Check if curl exists; if not, print or return appropriate error messages
	 *
	 * @param String  $service                the service description (used only for user-visible messages - so, use the description)
	 * @param Boolean $has_fallback           set as true if the lack of Curl only affects the ability to connect over SSL
	 * @param String  $extraclass             an extra CSS class for any resulting message, passed on to show_double_warning()
	 * @param Boolean $echo_instead_of_return whether the result should be echoed or returned
	 * @return String                         any resulting message, if $echo_instead_of_return was set
	 */
	public function curl_check($service, $has_fallback = false, $extraclass = '', $echo_instead_of_return = true) {

		$ret = '';

		// Check requirements
		if (!function_exists("curl_init") || !function_exists('curl_exec')) {
		
			$ret .= $this->show_double_warning('<strong>'.__('Warning', 'updraftplus').':</strong> '.sprintf(__("Your web server's PHP installation does not included a <strong>required</strong> (for %s) module (%s). Please contact your web hosting provider's support and ask for them to enable it.", 'updraftplus'), $service, 'Curl').' ', $extraclass, false);

		} else {
			$curl_version = curl_version();
			$curl_ssl_supported= ($curl_version['features'] & CURL_VERSION_SSL);
			if (!$curl_ssl_supported) {
				if ($has_fallback) {
					$ret .= '<p><strong>'.__('Warning', 'updraftplus').':</strong> '.sprintf(__("Your web server's PHP/Curl installation does not support https access. Communications with %s will be unencrypted. Ask your web host to install Curl/SSL in order to gain the ability for encryption (via an add-on).", 'updraftplus'), $service).'</p>';
				} else {
					$ret .= $this->show_double_warning('<p><strong>'.__('Warning', 'updraftplus').':</strong> '.sprintf(__("Your web server's PHP/Curl installation does not support https access. We cannot access %s without this support. Please contact your web hosting provider's support. %s <strong>requires</strong> Curl+https. Please do not file any support requests; there is no alternative.", 'updraftplus'), $service, $service).'</p>', $extraclass, false);
				}
			} else {
				$ret .= '<p><em>'.sprintf(__("Good news: Your site's communications with %s can be encrypted. If you see any errors to do with encryption, then look in the 'Expert Settings' for more help.", 'updraftplus'), $service).'</em></p>';
			}
		}
		if ($echo_instead_of_return) {
			echo $ret;
		} else {
			return $ret;
		}
	}

	/**
	 * Get backup information in HTML format for a specific backup
	 *
	 * @param Array		 $backup_history all backups history
	 * @param String	 $key		     backup timestamp
	 * @param String	 $nonce			 backup nonce (job ID)
	 * @param Array|Null $job_data		 if an array, then use this as the job data (if null, then it will be fetched directly)
	 *
	 * @return string HTML-formatted backup information
	 */
	public function raw_backup_info($backup_history, $key, $nonce, $job_data = null) {

		global $updraftplus;

		$backup = $backup_history[$key];

		$only_remote_sent = !empty($backup['service']) && (array('remotesend') === $backup['service'] || 'remotesend' === $backup['service']);

		$pretty_date = get_date_from_gmt(gmdate('Y-m-d H:i:s', (int) $key), 'M d, Y G:i');

		$rawbackup = "<h2 title=\"$key\">$pretty_date</h2>";

		if (!empty($backup['label'])) $rawbackup .= '<span class="raw-backup-info">'.$backup['label'].'</span>';

		if (null === $job_data) $job_data = empty($nonce) ? array() : $updraftplus->jobdata_getarray($nonce);
		
		if (!$only_remote_sent) {
			$rawbackup .= '<hr>';
			$rawbackup .= '<input type="checkbox" name="always_keep_this_backup" id="always_keep_this_backup" data-backup_key="'.$key.'" '.(empty($backup['always_keep']) ? '' : 'checked ').'><label for="always_keep_this_backup">'.__('Only allow this backup to be deleted manually (i.e. keep it even if retention limits are hit).', 'updraftplus').'</label>';
		}

		$rawbackup .= '<hr><p>';

		$backupable_entities = $updraftplus->get_backupable_file_entities(true, true);

		$checksums = $updraftplus->which_checksums();

		foreach ($backupable_entities as $type => $info) {
			if (!isset($backup[$type])) continue;

			$rawbackup .= $updraftplus->printfile($info['description'], $backup, $type, $checksums, $job_data, true);
		}

		$total_size = 0;
		foreach ($backup as $ekey => $files) {
			if ('db' == strtolower(substr($ekey, 0, 2)) && '-size' != substr($ekey, -5, 5)) {
				$rawbackup .= $updraftplus->printfile(__('Database', 'updraftplus'), $backup, $ekey, $checksums, $job_data, true);
			}
			if (!isset($backupable_entities[$ekey]) && ('db' != substr($ekey, 0, 2) || '-size' == substr($ekey, -5, 5))) continue;
			if (is_string($files)) $files = array($files);
			foreach ($files as $findex => $file) {
				$size_key = (0 == $findex) ? $ekey.'-size' : $ekey.$findex.'-size';
				$total_size = (false === $total_size || !isset($backup[$size_key]) || !is_numeric($backup[$size_key])) ? false : $total_size + $backup[$size_key];
			}
		}

		$services = empty($backup['service']) ? array('none') : $backup['service'];
		if (!is_array($services)) $services = array('none');

		$rawbackup .= '<strong>'.__('Uploaded to:', 'updraftplus').'</strong> ';

		$show_services = '';
		foreach ($services as $serv) {
			if ('none' == $serv || '' == $serv) {
				$add_none = true;
			} elseif (isset($updraftplus->backup_methods[$serv])) {
				$show_services .= $show_services ? ', '.$updraftplus->backup_methods[$serv] : $updraftplus->backup_methods[$serv];
			} else {
				$show_services .= $show_services ? ', '.$serv : $serv;
			}
		}
		if ('' == $show_services && $add_none) $show_services .= __('None', 'updraftplus');

		$rawbackup .= $show_services;

		if (false !== $total_size) {
			$rawbackup .= '</p><strong>'.__('Total backup size:', 'updraftplus').'</strong> '.UpdraftPlus_Manipulation_Functions::convert_numeric_size_to_text($total_size).'<p>';
		}
		
		$rawbackup .= '</p><hr><p><pre>'.print_r($backup, true).'</p></pre>';

		if (!empty($job_data) && is_array($job_data)) {
			$rawbackup .= '<p><pre>'.htmlspecialchars(print_r($job_data, true)).'</pre></p>';
		}

		return esc_attr($rawbackup);
	}

	private function download_db_button($bkey, $key, $esc_pretty_date, $backup, $accept = array()) {

		if (!empty($backup['meta_foreign']) && isset($accept[$backup['meta_foreign']])) {
			$desc_source = $accept[$backup['meta_foreign']]['desc'];
		} else {
			$desc_source = __('unknown source', 'updraftplus');
		}

		$ret = '';

		if ('db' == $bkey) {
			$dbt = empty($backup['meta_foreign']) ? esc_attr(__('Database', 'updraftplus')) : esc_attr(sprintf(__('Database (created by %s)', 'updraftplus'), $desc_source));
		} else {
			$dbt = __('External database', 'updraftplus').' ('.substr($bkey, 2).')';
		}

		$ret .= $this->download_button($bkey, $key, 0, null, '', $dbt, $esc_pretty_date, '0');
		
		return $ret;
	}

	/**
	 * Go through each of the file entities
	 *
	 * @param Array   $backup          An array of meta information
	 * @param Integer $key             Backup timestamp (epoch time)
	 * @param Array   $accept          An array of values to be accepted from vaules within $backup
	 * @param String  $entities        Entities to be added
	 * @param String  $esc_pretty_date Whether the button needs to escape the pretty date format
	 * @return String - the resulting HTML
	 */
	public function download_buttons($backup, $key, $accept, &$entities, $esc_pretty_date) {
		global $updraftplus;
		$ret = '';
		$backupable_entities = $updraftplus->get_backupable_file_entities(true, true);

		$first_entity = true;

		foreach ($backupable_entities as $type => $info) {
			if (!empty($backup['meta_foreign']) && 'wpcore' != $type) continue;

			$ide = '';
			if ('wpcore' == $type) $wpcore_restore_descrip = $info['description'];
			if (empty($backup['meta_foreign'])) {
				$sdescrip = preg_replace('/ \(.*\)$/', '', $info['description']);
				if (strlen($sdescrip) > 20 && isset($info['shortdescription'])) $sdescrip = $info['shortdescription'];
			} else {
				$info['description'] = 'WordPress';

				if (isset($accept[$backup['meta_foreign']])) {
					$desc_source = $accept[$backup['meta_foreign']]['desc'];
					$ide .= sprintf(__('Backup created by: %s.', 'updraftplus'), $accept[$backup['meta_foreign']]['desc']).' ';
				} else {
					$desc_source = __('unknown source', 'updraftplus');
					$ide .= __('Backup created by unknown source (%s) - cannot be restored.', 'updraftplus').' ';
				}

				$sdescrip = (empty($accept[$backup['meta_foreign']]['separatedb'])) ? sprintf(__('Files and database WordPress backup (created by %s)', 'updraftplus'), $desc_source) : sprintf(__('Files backup (created by %s)', 'updraftplus'), $desc_source);
				if ('wpcore' == $type) $wpcore_restore_descrip = $sdescrip;
			}
			if (isset($backup[$type])) {
				if (!is_array($backup[$type])) $backup[$type] = array($backup[$type]);
				$howmanyinset = count($backup[$type]);
				$expected_index = 0;
				$index_missing = false;
				$set_contents = '';
				$entities .= "/$type=";
				$whatfiles = $backup[$type];
				ksort($whatfiles);
				foreach ($whatfiles as $findex => $bfile) {
					$set_contents .= ('' == $set_contents) ? $findex : ",$findex";
					if ($findex != $expected_index) $index_missing = true;
					$expected_index++;
				}
				$entities .= $set_contents.'/';
				if (!empty($backup['meta_foreign'])) {
					$entities .= '/plugins=0//themes=0//uploads=0//others=0/';
				}
				$printing_first = true;
				foreach ($whatfiles as $findex => $bfile) {
					
					$pdescrip = ($findex > 0) ? $sdescrip.' ('.($findex+1).')' : $sdescrip;
					if ($printing_first) {
						$ide .= __('Press here to download or browse', 'updraftplus').' '.strtolower($info['description']);
					} else {
						$ret .= '<div class="updraft-hidden" style="display:none;">';
					}
					if (count($backup[$type]) >0) {
						if ($printing_first) $ide .= ' '.sprintf(__('(%d archive(s) in set).', 'updraftplus'), $howmanyinset);
					}
					if ($index_missing) {
						if ($printing_first) $ide .= ' '.__('You appear to be missing one or more archives from this multi-archive set.', 'updraftplus');
					}

					if (!$first_entity) {
					} else {
						$first_entity = false;
					}

					$ret .= $this->download_button($type, $key, $findex, $info, $ide, $pdescrip, $esc_pretty_date, $set_contents);

					if (!$printing_first) {
						$ret .= '</div>';
					} else {
						$printing_first = false;
					}
				}
			}
		}
		return $ret;
	}

	public function date_label($pretty_date, $key, $backup, $jobdata, $nonce, $simple_format = false) {

		$pretty_date = $simple_format ? $pretty_date : '<div class="clear-right">'.$pretty_date.'</div>';

		$ret = apply_filters('updraftplus_showbackup_date', $pretty_date, $backup, $jobdata, (int) $key, $simple_format);
		if (is_array($jobdata) && !empty($jobdata['resume_interval']) && (empty($jobdata['jobstatus']) || 'finished' != $jobdata['jobstatus'])) {
			if ($simple_format) {
				$ret .= ' '.__('(Not finished)', 'updraftplus');
			} else {
				$ret .= apply_filters('updraftplus_msg_unfinishedbackup', "<br><span title=\"".esc_attr(__('If you are seeing more backups than you expect, then it is probably because the deletion of old backup sets does not happen until a fresh backup completes.', 'updraftplus'))."\">".__('(Not finished)', 'updraftplus').'</span>', $jobdata, $nonce);
			}
		}
		return $ret;
	}

	public function download_button($type, $backup_timestamp, $findex, $info, $title, $pdescrip, $esc_pretty_date, $set_contents) {// phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found -- Filter use
	
		$ret = '';

		$wp_nonce = wp_create_nonce('updraftplus_download');
		
		// updraft_downloader(base, backup_timestamp, what, whicharea, set_contents, prettydate, async)
		$ret .= '<button data-wp_nonce="'.esc_attr($wp_nonce).'" data-backup_timestamp="'.esc_attr($backup_timestamp).'" data-what="'.esc_attr($type).'" data-set_contents="'.esc_attr($set_contents).'" data-prettydate="'.esc_attr($esc_pretty_date).'" type="button" class="button updraft_download_button '."uddownloadform_${type}_${backup_timestamp}_${findex}".'" title="'.$title.'">'.$pdescrip.'</button>';
		// onclick="'."return updraft_downloader('uddlstatus_', '$backup_timestamp', '$type', '.ud_downloadstatus', '$set_contents', '$esc_pretty_date', true)".'"
		
		return $ret;
	}

	public function restore_button($backup, $key, $pretty_date, $entities = '') {
		$ret = '<div class="restore-button">';

		if ($entities) {
			$show_data = $pretty_date;
			if (isset($backup['native']) && false == $backup['native']) {
				$show_data .= ' '.__('(backup set imported from remote location)', 'updraftplus');
			}

			$ret .= '<button data-showdata="'.esc_attr($show_data).'" data-backup_timestamp="'.$key.'" data-entities="'.esc_attr($entities).'" title="'.__('After pressing this button, you will be given the option to choose which components you wish to restore', 'updraftplus').'" type="button" class="button button-primary choose-components-button">'.__('Restore', 'updraftplus').'</button>';
		}
		$ret .= "</div>\n";
		return $ret;
	}

	/**
	 * Get HTML for the 'Upload' button for a particular backup in the 'Existing Backups' tab
	 *
	 * @param Integer	 $backup_time - backup timestamp (epoch time)
	 * @param String	 $nonce       - backup nonce
	 * @param Array		 $backup      - backup information array
	 * @param Null|Array $jobdata	  - if not null, then use as the job data instead of fetching
	 *
	 * @return String - the resulting HTML
	 */
	public function upload_button($backup_time, $nonce, $backup, $jobdata = null) {
		global $updraftplus;

		// Check the job is not still running.
		if (null === $jobdata) $jobdata = $updraftplus->jobdata_getarray($nonce);
		
		if (!empty($jobdata) && 'finished' != $jobdata['jobstatus']) return '';

		// Check that the user has remote storage setup.
		$services = (array) $updraftplus->just_one($updraftplus->get_canonical_service_list());
		if (empty($services)) return '';

		$show_upload = false;
		$not_uploaded = array();

		// Check that the backup has not already been sent to remote storage before.
		if (empty($backup['service']) || array('none') == $backup['service'] || array('') == $backup['service'] || 'none' == $backup['service']) {
			$show_upload = true;
		// If it has been uploaded then check if there are any new remote storage options that it has not yet been sent to.
		} elseif (!empty($backup['service']) && array('none') != $backup['service'] && array('') != $backup['service'] && 'none' != $backup['service']) {
			
			foreach ($services as $key => $value) {
				if (in_array($value, $backup['service'])) unset($services[$key]);
			}

			if (!empty($services)) $show_upload = true;
		}

		if ($show_upload) {
			
			$missing_file = false;
			$entities = $updraftplus->get_backupable_file_entities(true);
			// Add the database to the entities array ready to loop over
			$entities['db'] = '';
			$updraft_dir = trailingslashit($updraftplus->backups_dir_location());

			foreach ($entities as $type => $info) {

				if (!isset($backup[$type])) continue;

				// Cast this to an array so that a warning is not thrown when we encounter a Database.
				foreach ((array) $backup[$type] as $value) {
					if (!file_exists($updraft_dir . DIRECTORY_SEPARATOR . $value)) $missing_file = true;
				}
			}

			if (!$missing_file) {
				$service_list = '';
				$service_list_display = '';
				$is_first_service = true;
				
				foreach ($services as $key => $service) {
					if (!$is_first_service) {
						$service_list .= ',';
						$service_list_display .= ', ';
					}
					$service_list .= $service;
					$service_list_display .= $updraftplus->backup_methods[$service];

					$is_first_service = false;
				}

				return '<div class="updraftplus-upload">
				<button data-nonce="'.$nonce.'" data-key="'.$backup_time.'" data-services="'.$service_list.'" title="'.__('After pressing this button, you can select where to upload your backup from a list of your currently saved remote storage locations', 'updraftplus').' ('.$service_list_display.')." type="button" class="button button-primary updraft-upload-link">'.__('Upload', 'updraftplus').'</button>
				</div>';
			}

			return '';
		}
	}

	/**
	 * Get HTML for the 'Delete' button for a particular backup in the 'Existing Backups' tab
	 *
	 * @param Integer $backup_time - backup timestamp (epoch time)
	 * @param String  $nonce	   - backup nonce
	 * @param Array	  $backup	   - backup information array
	 *
	 * @return String - the resulting HTML
	 */
	public function delete_button($backup_time, $nonce, $backup) {
		$sval = (!empty($backup['service']) && 'email' != $backup['service'] && 'none' != $backup['service'] && array('email') !== $backup['service'] && array('none') !== $backup['service'] && array('remotesend') !== $backup['service']) ? '1' : '0';
		return '<div class="updraftplus-remove" data-hasremote="'.$sval.'">
			<a data-hasremote="'.$sval.'" data-nonce="'.$nonce.'" data-key="'.$backup_time.'" class="button button-remove no-decoration updraft-delete-link" href="'.UpdraftPlus::get_current_clean_url().'" title="'.esc_attr(__('Delete this backup set', 'updraftplus')).'">'.__('Delete', 'updraftplus').'</a>
		</div>';
	}

	public function log_button($backup) {
		global $updraftplus;
		$updraft_dir = $updraftplus->backups_dir_location();
		$ret = '';
		if (isset($backup['nonce']) && preg_match("/^[0-9a-f]{12}$/", $backup['nonce']) && is_readable($updraft_dir.'/log.'.$backup['nonce'].'.txt')) {
			$nval = $backup['nonce'];
			$lt = __('View Log', 'updraftplus');
			$url = esc_attr(UpdraftPlus_Options::admin_page()."?page=updraftplus&action=downloadlog&amp;updraftplus_backup_nonce=$nval");
			$ret .= <<<ENDHERE
				<div style="clear:none;" class="updraft-viewlogdiv">
					<a class="button no-decoration updraft-log-link" href="$url" data-jobid="$nval">
						$lt
					</a>
					<!--
					<form action="$url" method="get">
						<input type="hidden" name="action" value="downloadlog" />
						<input type="hidden" name="page" value="updraftplus" />
						<input type="hidden" name="updraftplus_backup_nonce" value="$nval" />
						<input type="submit" value="$lt" class="updraft-log-link" onclick="event.preventDefault(); updraft_popuplog('$nval');" />
					</form>
					-->
				</div>
ENDHERE;
			return $ret;
		}
	}

	/**
	 * This function will set up the backup job data for when we are uploading a local backup to remote storage. It changes the initial jobdata so that UpdraftPlus knows about what files it's uploading and so that it skips directly to the upload stage.
	 *
	 * @param array $jobdata - the initial job data that we want to change
	 * @param array $options - options sent from the front end includes backup timestamp and nonce
	 *
	 * @return array         - the modified jobdata
	 */
	public function upload_local_backup_jobdata($jobdata, $options) {
		global $updraftplus;

		if (!is_array($jobdata)) return $jobdata;
		
		$backup_history = UpdraftPlus_Backup_History::get_history();
		$services = !empty($options['services']) ? $options['services'] : array();
		$backup = $backup_history[$options['use_timestamp']];
		$backupable_entities = $updraftplus->get_backupable_file_entities(true);

		/*
			The initial job data is not set up in a key value array instead it is set up so key "x" is the name of the key and then key "y" is the value.
			e.g array[0] = 'backup_name' array[1] = 'my_backup'
		*/
		$jobstatus_key = array_search('jobstatus', $jobdata) + 1;
		$backup_time_key = array_search('backup_time', $jobdata) + 1;
		$backup_database_key = array_search('backup_database', $jobdata) + 1;
		$backup_files_key = array_search('backup_files', $jobdata) + 1;
		$service_key = array_search('service', $jobdata) + 1;

		$db_backups = $jobdata[$backup_database_key];
		$file_backups = array();

		// We need to construct the expected files array here, this gets added to the jobdata much later in the backup process but we need this before we start
		foreach ($backupable_entities as $entity => $path) {
			if (isset($backup[$entity])) $file_backups[$entity] = $backup[$entity];
			if (isset($backup[$entity.'-size'])) $file_backups[$entity.'-size'] = $backup[$entity.'-size'];
		}

		$db_backup_info = $updraftplus->update_database_jobdata($db_backups, $backup);
		
		// Next we need to build the services array using the remote storage destinations the user has selected to upload this backup set to
		$selected_services = array();
		
		foreach ($services as $key => $storage_info) {
			$selected_services[] = $storage_info['value'];
		}
		
		$jobdata[$jobstatus_key] = 'clouduploading';
		$jobdata[$backup_time_key] = $options['use_timestamp'];
		$jobdata[$backup_files_key] = 'finished';
		$jobdata[] = 'backup_files_array';
		$jobdata[] = $file_backups;
		$jobdata[] = 'blog_name';
		$jobdata[] = $db_backup_info['blog_name