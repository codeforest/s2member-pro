<?php
/**
 * s2Member Pro Remote Operations API.
 *
 * Copyright: © 2009-2011
 * {@link http://www.websharks-inc.com/ WebSharks, Inc.}
 * (coded in the USA)
 *
 * This WordPress plugin (s2Member Pro) is comprised of two parts:
 *
 * o (1) Its PHP code is licensed under the GPL license, as is WordPress.
 *   You should have received a copy of the GNU General Public License,
 *   along with this software. In the main directory, see: /licensing/
 *   If not, see: {@link http://www.gnu.org/licenses/}.
 *
 * o (2) All other parts of (s2Member Pro); including, but not limited to:
 *   the CSS code, some JavaScript code, images, and design;
 *   are licensed according to the license purchased.
 *   See: {@link http://www.s2member.com/prices/}
 *
 * Unless you have our prior written consent, you must NOT directly or indirectly license,
 * sub-license, sell, resell, or provide for free; part (2) of the s2Member Pro Module;
 * or make an offer to do any of these things. All of these things are strictly
 * prohibited with part (2) of the s2Member Pro Module.
 *
 * Your purchase of s2Member Pro includes free lifetime upgrades via s2Member.com
 * (i.e. new features, bug fixes, updates, improvements); along with full access
 * to our video tutorial library: {@link http://www.s2member.com/videos/}
 *
 * @package s2Member\API_Remote_Ops
 * @since 110713
 */
if(realpath(__FILE__) === realpath($_SERVER['SCRIPT_FILENAME']))
	exit('Do not access this file directly.');

if(!class_exists('c_ws_plugin__s2member_pro_remote_ops'))
{
	/**
	 * s2Member Pro Remote Operations API.
	 *
	 * @package s2Member\API_Remote_Ops
	 * @since 110713
	 */
	class c_ws_plugin__s2member_pro_remote_ops
	{
		/**
		 * Handles Remote Operation communications.
		 *
		 * @package s2Member\API_Remote_Ops
		 * @since 110713
		 *
		 * @attaches-to ``add_action('init');``
		 *
		 * @return null Or exits script execution with a serialized array on success, or a string beginning with `Error:` on failure.
		 */
		public static function remote_ops()
		{
			if(!empty($_GET['s2member_pro_remote_op']) && !empty($_POST['s2member_pro_remote_op']))
			{
				c_ws_plugin__s2member_no_cache::no_cache_constants(TRUE);

				status_header(200);
				header('Content-Type: text/plain; charset=UTF-8');
				while(@ob_end_clean()) ; // Clean any existing output buffers.

				if(is_array($op = maybe_unserialize(c_ws_plugin__s2member_utils_strings::trim_deep(stripslashes_deep($_POST['s2member_pro_remote_op'])))))
				{
					if(is_array($op = c_ws_plugin__s2member_utils_strings::trim_deep($op))) // Now trim again, in case of serialized array.
					{
						if(!empty($op['api_key']) && $op['api_key'] === c_ws_plugin__s2member_pro_remote_ops::remote_ops_key_gen())
						{
							if(!empty($op['op']) && is_callable('c_ws_plugin__s2member_pro_remote_ops_in::'.$op['op']))
								exit(call_user_func('c_ws_plugin__s2member_pro_remote_ops_in::'.$op['op'], $op));

							exit('Error: $_POST[\'s2member_pro_remote_op\'][\'op\'] is empty or invalid.');
						}
						exit('Error: $_POST[\'s2member_pro_remote_op\'][\'api_key\'] is empty or invalid.');
					}
					exit('Error: $_POST[\'s2member_pro_remote_op\'] is NOT a serialized array.');
				}
				exit('Error: $_POST[\'s2member_pro_remote_op\'] is NOT a serialized array.');
			}
		}

		/**
		 * Test if this WordPress instance is a specific Remote Operation.
		 *
		 * @package s2Member\API_Remote_Ops
		 * @since 110713
		 *
		 * @param string $_op The Remote Operation to test this instance against.
		 *
		 * @return bool True if instance is the specified Operation, else false.
		 */
		public static function is_remote_op($_op = '')
		{
			if(!empty($_GET['s2member_pro_remote_op']) && !empty($_POST['s2member_pro_remote_op']))
				if(is_array($op = maybe_unserialize(c_ws_plugin__s2member_utils_strings::trim_deep(stripslashes_deep($_POST['s2member_pro_remote_op'])))))
					if(is_array($op = c_ws_plugin__s2member_utils_strings::trim_deep($op))) // Now trim again, in case of serialized array.
						if(!empty($op['api_key']) && $op['api_key'] === c_ws_plugin__s2member_pro_remote_ops::remote_ops_key_gen())
							if(!empty($op['op']) && $op['op'] === $_op)
								return TRUE;
			return FALSE;
		}

		/**
		 * Generates an API Key, for Remote Operations.
		 *
		 * @package s2Member\API_Remote_Ops
		 * @since 110713
		 *
		 * @return string An API Key. It's an MD5 Hash, 32 chars, URL-safe.
		 */
		public static function remote_ops_key_gen()
		{
			global $current_site, $current_blog;

			if($GLOBALS['WS_PLUGIN__']['s2member']['o']['pro_remote_ops_key'])
				$key = $GLOBALS['WS_PLUGIN__']['s2member']['o']['pro_remote_ops_key'];

			else if(is_multisite() && !is_main_site()) // Child blogs in a MS network get their own key.
				$key = md5(c_ws_plugin__s2member_utils_encryption::xencrypt($current_blog->domain.$current_blog->path, FALSE, FALSE));

			else $key = md5(c_ws_plugin__s2member_utils_encryption::xencrypt(preg_replace('/\:[0-9]+$/', '', $_SERVER['HTTP_HOST']), FALSE, FALSE));

			return apply_filters('ws_plugin__s2member_pro_remote_ops_key', (!empty($key)) ? $key : '');
		}

		/**
		 * Updates Pro Remote OPs Key via AJAX.
		 *
		 * @package s2Member\API_Remote_Ops
		 * @since 140806
		 *
		 * @attaches-to ``add_action('wp_ajax_ws_plugin__s2member_update_roles_via_ajax');``
		 */
		public static function update_remote_ops_key_via_ajax()
		{
			do_action('ws_plugin__s2member_before_update_remote_ops_key_via_ajax', get_defined_vars());

			status_header(200); // Send a 200 OK status header.
			header('Content-Type: text/plain; charset=UTF-8'); // Content-Type with UTF-8.
			while(@ob_end_clean()) ; // Clean any existing output buffers.

			if(current_user_can('create_users')) // Check privileges. Ability to create Users?

				if(!empty($_POST['ws_plugin__s2member_update_remote_ops_key_via_ajax']))
					if(($nonce = $_POST['ws_plugin__s2member_update_remote_ops_key_via_ajax']))
						if(wp_verify_nonce($nonce, 'ws-plugin--s2member-update-remote-ops-key-via-ajax'))

							if(!apply_filters('ws_plugin__s2member_lock_roles_caps', FALSE))
							{
								c_ws_plugin__s2member_roles_caps::config_roles();
								$success = TRUE; // Roles updated.
							}
							else // Else flag as having been locked here.
								$locked = TRUE;

			exit(apply_filters('ws_plugin__s2member_update_remote_ops_key_via_ajax', // Also handle ``$locked`` here.
				((isset($success) && $success) ? '1' : ((isset($locked) && $locked) ? 'l' : '0')), get_defined_vars()));
		}
	}
}