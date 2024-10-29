<?php
/*
Plugin Name: Advanced Media Downloader
Description: Advanced download method, resizing images, combine CSS or JavaScript files.
Author: Ceramedia
Version: 1.2.4
Author URI: http://ceramedia.net
License: GPL2
*/

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo "Hi there!  I'm just a plugin, not much I can do when called directly.";
	exit;
}

// Define path to plugin
defined('AMD_PATH')
	|| define('AMD_PATH', plugin_dir_path( __FILE__ ));


class CM_AdvancedMediaDownloader
{

	function __construct()
	{
		// Set up activation hooks
		register_activation_hook( __FILE__, array(&$this, 'activate') );

		// Using a filter instead of an action to create the rewrite rules.
		// Write rules -> Add query vars -> Recalculate rewrite rules
		add_filter('rewrite_rules_array', array(&$this, 'createRewriteRules'));
		add_filter('query_vars', array(&$this, 'addQueryVars'));
		add_action('template_redirect', array(&$this, 'templateRedirectIntercept') );

		add_filter('admin_init', array(&$this, 'flushRewriteRules'));
		add_action('delete_attachment', array(&$this, 'deleteTempFiles'));
	}

	function activate( /*$network_wide*/ )
	{
		$this->flushRewriteRules();
	}

	// Took out the $wp_rewrite->rules replacement so the rewrite rules filter could handle this.
	function createRewriteRules($rules)
	{
		global $wp_rewrite;
		$newRule    = array('download/([^/]+)/(.*)' => 'index.php?amd_type='.$wp_rewrite->preg_index(1).'&amd_args='.$wp_rewrite->preg_index(2));
		$newRules   = $newRule + $rules;
		return $newRules;
	}

	function addQueryVars($vars)
	{
		$vars[] = 'amd_type';
		$vars[] = 'amd_args';
		return $vars;
	}

	function flushRewriteRules()
	{
		global $wp_rewrite;
		$wp_rewrite->flush_rules();
	}

	function templateRedirectIntercept()
	{
		global $wp_query;
		if ($wp_query->get('amd_type')) {
			require AMD_PATH . '/Download/Download.php';
			new Amd_Download($wp_query->get('amd_type'), explode('/', urldecode($wp_query->get('amd_args'))));
		}
	}

	function deleteTempFiles($postId)
	{
		$dir = dirname(get_attached_file($postId, true)) . '/' . $postId;

		if (is_dir($dir)) {

			foreach(scandir($dir) as $file) {
				if ('.' === $file || '..' === $file) continue;
				unlink("$dir/$file");
			}

			rmdir($dir);
		}
	}


}
new CM_AdvancedMediaDownloader();

/**
 * Get an attachment file url
 * @param $id
 * @param array $args
 * @param string $sep
 * @return string
 */
function amd_get_file_link($id, array $args=array(), $sep='&amp;')
{
	return user_trailingslashit(home_url( sprintf('/download/file/%d', $id) )) . '?'.http_build_query($args, '', $sep);
}

/**
 * Print image tag for attachment
 * @param $id
 * @param array $args
 * @param string $alt
 * @param array $classes
 */
function amd_file_img($id, array $args=array(), $alt='', $classes=array())
{
	if (!empty($classes))
		echo sprintf('<img src="%s" alt="%s" class="%s" />', amd_get_file_link($id, $args), $alt, implode(' ', $classes));
	else
		echo sprintf('<img src="%s" alt="%s" />', amd_get_file_link($id, $args), $alt);
}

/**
 * Get an asset url
 * @param $type
 * @param array $files
 * @param null $no_cache
 * @return string
 * @throws InvalidArgumentException
 */
function amd_get_asset_link($type, array $files, $no_cache=null)
{
	if (!in_array($type, array('css','js','less')))
		throw new InvalidArgumentException('Unknown asset type ' . $type);

	return user_trailingslashit(home_url( sprintf('/download/%s/%s', $type, implode('+',$files)) )) . ($no_cache || ($no_cache===null && defined('APPLICATION_ENV') && APPLICATION_ENV!='production')?'?'.time():'');
}
