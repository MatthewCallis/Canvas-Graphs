<?php
/*
Plugin Name: Canvas Graphs
Plugin URI: https://github.com/MatthewCallis/Canvas-Graphs
Description: Add simple HTML canvas graphs to your posts.
Author: Matthew Callis
Version: 1.0.0
Author URI: http://superfamicom.org/
*/
function output_scripts(){
	wp_register_script('canvas-graphs', plugins_url(plugin_basename(dirname(__FILE__))).'/js/cv_graph.js', false);
	wp_enqueue_script('canvas-graphs');
}

function translate_values(&$value, $key){
	if($key == 'grid' || $key == 'range'){
		if(substr($value, -1) != ']' && substr($value, 0) != '['){
			$value = '['.$value.']';
		}
	}
}

function graph_shortcode($attributes, $content=null){
	# Parse graph attribtues
	$config = shortcode_atts(array(
		'id'          => 'graph_'.mt_rand(),
		'call'        => (get_option('call') != 'undefined' ? '"'.get_option('call').'"' : get_option('call')),
		'interval'    => get_option('interval'),
		'showline'    => get_option('showline'),
		'showfill'    => get_option('showfill'),
		'linewidth'   => get_option('linewidth'),
		'showshadow'  => get_option('showshadow'),
		'strokestyle' => get_option('strokestyle'),
		'gridcolor'   => get_option('gridcolor'),
		'background'  => get_option('background'),
		'fillstyle'   => get_option('fillstyle'),
		'showdots'    => get_option('showdots'),
		'showgrid'    => get_option('showgrid'),
		'showlabels'  => get_option('showlabels'),
		'labelfilter' => (get_option('labelfilter') != 'undefined' ? '"'.get_option('labelfilter').'"' : get_option('labelfilter')),
		'grid'        => get_option('grid'),
		'range'       => get_option('range'),
		'leftoffset'  => get_option('leftoffset'),
		'topoffset'   => get_option('topoffset'),
		'width'       => get_option('width'),
		'height'      => get_option('height')
	), $attributes);
	array_walk($config, 'translate_values');
	extract($config);

	return <<<canvasGraph
<script type="text/javascript">
//<![CDATA[
window.onload = function(){
	var csv_data = csv_array('{$content}');
	var total_points = csv_data.length;
	g_graph = new Graph({
		'background'	: "{$background}",
		'call'			: {$call},
		'data'			: csv_array('{$content}')[0],
		'fillStyle'		: "{$fillstyle}",
		'grid'			: {$grid},
		'gridcolor'		: "{$gridcolor}",
		'id'			: "{$id}",
		'interval'		: {$interval},
		'labelfilter'	: {$labelfilter},
		'lineWidth'		: {$linewidth},
		'range'			: {$range},
		'showdots'		: {$showdots},
		'showfill'		: {$showfill},
		'showgrid'		: {$showgrid},
		'showlabels'	: {$showlabels},
		'showline'		: {$showline},
		'showshadow'	: {$showshadow},
		'strokeStyle'	: "{$strokestyle}",
		'leftOffset'	: {$leftoffset}
	});
}
//]]>
</script>
<canvas id="{$id}" width="{$width}" height="{$height}">You need a better browser, perhaps <a href="http://www.google.com/chrome">Google Chrome</a>.</canvas>
canvasGraph;
}

# Do we need to add our JS to head?
function check_install($posts){
	$content = '';
	foreach($posts as $post){
		$content .= $post->post_content;
	}
	$need_header = (bool)preg_match("/\[graph(.*)\]/U", $content);

	return $posts;
}

# Init plugin options to white list our options
function options_init(){
	register_setting('cg_options', 'cg_options', 'options_validate');

	add_option('leftoffset',	'0',	'',	'yes');
	add_option('topoffset',		'0',	'',	'yes');
	add_option('width',			'600',	'',	'yes');
	add_option('height',		'200',	'',	'yes');
	add_option('linewidth',		'2',	'',	'yes');
	add_option('interval',		'300',	'',	'yes');

	# Boolean Values
	add_option('showgrid',		1,	'',	'yes');
	add_option('showline',		1,	'',	'yes');
	add_option('showfill',		1,	'',	'yes');
	add_option('showdots',		1,	'',	'yes');
	add_option('showlabels',	1,	'',	'yes');
	add_option('showshadow',	1,	'',	'yes');

	# Text
	add_option('call',			'undefined',		'',	'yes');
	add_option('strokestyle',	'#666',				'',	'yes');
	add_option('gridcolor',		'#EEE',				'',	'yes');
	add_option('background',	'#F9F9F9',			'',	'yes');
	add_option('fillstyle',		'rgba(0,0,0,0.25)',	'',	'yes');
	add_option('labelfilter',	'undefined',		'',	'yes');
	add_option('grid',			'10,10',			'',	'yes');
	add_option('range',			'0,100',			'',	'yes');

	register_setting('cg_options', 'leftoffset');
	register_setting('cg_options', 'topoffset');
	register_setting('cg_options', 'width');
	register_setting('cg_options', 'height');
	register_setting('cg_options', 'linewidth');
	register_setting('cg_options', 'interval');

	register_setting('cg_options', 'showgrid');
	register_setting('cg_options', 'showline');
	register_setting('cg_options', 'showfill');
	register_setting('cg_options', 'showdots');
	register_setting('cg_options', 'showlabels');
	register_setting('cg_options', 'showshadow');

	register_setting('cg_options', 'call');
	register_setting('cg_options', 'strokestyle');
	register_setting('cg_options', 'gridcolor');
	register_setting('cg_options', 'background');
	register_setting('cg_options', 'fillstyle');
	register_setting('cg_options', 'labelfilter');
	register_setting('cg_options', 'grid');
	register_setting('cg_options', 'range');
}

# Remove all of our settings
function deactivate(){
	unregister_setting('cg_options', 'leftoffset');
	unregister_setting('cg_options', 'topoffset');
	unregister_setting('cg_options', 'width');
	unregister_setting('cg_options', 'height');
	unregister_setting('cg_options', 'linewidth');
	unregister_setting('cg_options', 'interval');

	unregister_setting('cg_options', 'showgrid');
	unregister_setting('cg_options', 'showline');
	unregister_setting('cg_options', 'showfill');
	unregister_setting('cg_options', 'showdots');
	unregister_setting('cg_options', 'showlabels');
	unregister_setting('cg_options', 'showshadow');

	unregister_setting('cg_options', 'call');
	unregister_setting('cg_options', 'strokestyle');
	unregister_setting('cg_options', 'gridcolor');
	unregister_setting('cg_options', 'background');
	unregister_setting('cg_options', 'fillstyle');
	unregister_setting('cg_options', 'labelfilter');
	unregister_setting('cg_options', 'grid');
	unregister_setting('cg_options', 'range');

	delete_option('leftoffset');
	delete_option('topoffset');
	delete_option('width');
	delete_option('height');
	delete_option('linewidth');
	delete_option('interval');

	delete_option('showgrid');
	delete_option('showline');
	delete_option('showfill');
	delete_option('showdots');
	delete_option('showlabels');
	delete_option('showshadow');

	delete_option('call');
	delete_option('strokestyle');
	delete_option('gridcolor');
	delete_option('background');
	delete_option('fillstyle');
	delete_option('labelfilter');
	delete_option('grid');
	delete_option('range');
}

# Add menu page
function options_add_page(){
	add_options_page('Canvas Graphs', 'Canvas Graphs', 'manage_options', 'canvas_graphs', 'options_do_page');
}

# Sanitize and validate input. Accepts an array, return a sanitized array.
function options_validate($input){
	# Integer Values
	$input['leftoffset']	= (!isset($input['leftoffset']) ? absint($input['leftoffset']) : '0');
	$input['topoffset']		= (!isset($input['topoffset']) ? absint($input['topoffset']) : '0');
	$input['width']			= (!isset($input['width']) ? absint($input['width']) : '600');
	$input['height']		= (!isset($input['height']) ? absint($input['height']) : '200');
	$input['linewidth']		= (!isset($input['linewidth']) ? absint($input['linewidth']) : '2');
	$input['interval']		= (!isset($input['interval']) ? absint($input['interval']) : '300');

	# Boolean Values
	$input['showgrid']		= (($input['showgrid'] == 'true' || $input['showgrid'] == 'false') ? wp_filter_nohtml_kses(strtolower($input['showgrid'])) : 'true');
	$input['showline']		= (($input['showline'] == 'true' || $input['showline'] == 'false') ? wp_filter_nohtml_kses(strtolower($input['showline'])) : 'true');
	$input['showfill']		= (($input['showfill'] == 'true' || $input['showfill'] == 'false') ? wp_filter_nohtml_kses(strtolower($input['showfill'])) : 'true');
	$input['showdots']		= (($input['showdots'] == 'true' || $input['showdots'] == 'false') ? wp_filter_nohtml_kses(strtolower($input['showdots'])) : 'true');
	$input['showlabels']	= (($input['showlabels'] == 'true' || $input['showlabels'] == 'false') ? wp_filter_nohtml_kses(strtolower($input['showlabels'])) : 'true');
	$input['showshadow']	= (($input['showshadow'] == 'true' || $input['showshadow'] == 'false') ? wp_filter_nohtml_kses(strtolower($input['showshadow'])) : 'true');

	# Text
	$input['call']			= ((isset($input['call']) && $input['call'] != null) ? wp_filter_nohtml_kses($input['call']) : 'undefined');
	$input['strokestyle']	= ((isset($input['strokestyle']) && $input['strokestyle'] != null) ? wp_filter_nohtml_kses($input['strokestyle']) : '#666');
	$input['gridcolor']		= ((isset($input['gridcolor']) && $input['gridcolor'] != null) ? wp_filter_nohtml_kses($input['gridcolor']) : '#EEE');
	$input['background']	= ((isset($input['background']) && $input['background'] != null) ? wp_filter_nohtml_kses($input['background']) : '#F9F9F9');
	$input['fillstyle']		= ((isset($input['fillstyle']) && $input['fillstyle'] != null) ? wp_filter_nohtml_kses($input['fillstyle']) : 'rgba(0,0,0,0.25)');
	$input['labelfilter']	= ((isset($input['labelfilter']) && $input['labelfilter'] != null) ? wp_filter_nohtml_kses($input['labelfilter']) : 'undefined');
	$input['grid']			= ((isset($input['grid']) && $input['grid'] != null) ? wp_filter_nohtml_kses($input['grid']) : '10,10');
	$input['range']			= ((isset($input['range']) && $input['range'] != null) ? wp_filter_nohtml_kses($input['range']) : '0,100');

	return;
}

# Draw the menu page itself
function options_do_page(){
?>
<div class="wrap">
	<h2>Canvas Graphs Defaults</h2>
	<form method="post" action="options.php">
		<?php settings_fields('cg_options'); ?>
		<?php $options = get_option('cg_options'); ?>
		<p>These are the default values that will be used unless over written in the shortcode.</p>
		<table class="form-table">
			<tr valign="top">
				<th scope="row">Width</th>
				<td><input type="text" name="width" value="<?php echo get_option('width'); ?>"/></td>
			</tr>
			<tr valign="top">
				<th scope="row">Height</th>
				<td><input type="text" name="height" value="<?php echo get_option('height'); ?>"/></td>
			</tr>

			<tr valign="top">
				<th scope="row">Left Offset (Left Padding)</th>
				<td><input type="text" name="leftoffset" value="<?php echo get_option('leftoffset'); ?>"/></td>
			</tr>
			<!-- <tr valign="top">
			<th scope="row">Top Offset</th>
			<td><input type="text" name="cg_options[topoffset]" value="<?php echo get_option('topoffset'); ?>"/></td>
			</tr> -->

			<tr valign="top">
				<th scope="row">Show Grid</th>
				<td><input name="showgrid" type="checkbox" value="1" <?php checked('1', get_option('showgrid')); ?>/></td>
			</tr>
			<tr valign="top">
				<th scope="row">Background (Example: #EEE or #EEEEEE or rgba(238,238,238,0.75))</th>
				<td><input type="text" name="background" value="<?php echo get_option('background'); ?>" class="regular-text code"/></td>
			</tr>
			<tr valign="top">
				<th scope="row">Grid Color (Example: #EEE or #EEEEEE or rgba(238,238,238,0.75))</th>
				<td><input type="text" name="gridcolor" value="<?php echo get_option('gridcolor'); ?>" class="regular-text code"/></td>
			</tr>
			<tr valign="top">
				<th scope="row">Grid Size (width, height) (Example: 10, 10)</th>
				<td><input type="text" name="grid" value="<?php echo get_option('grid'); ?>"/></td>
			</tr>

			<tr valign="top">
				<th scope="row">Show Line</th>
				<td><input name="showline" type="checkbox" value="1" <?php checked('1', get_option('showline')); ?>/></td>
			</tr>
			<tr valign="top">
				<th scope="row">Line Width</th>
				<td><input type="text" name="linewidth" value="<?php echo get_option('linewidth'); ?>"/></td>
			</tr>
			<tr valign="top">
				<th scope="row">Stroke Style (Example: #666 or #666666 or rgba(102,102,102,0.40))</th>
				<td><input type="text" name="strokestyle" value="<?php echo get_option('strokestyle'); ?>" class="regular-text code"/></td>
			</tr>

			<tr valign="top">
				<th scope="row">Show Fill</th>
				<td><input name="showfill" type="checkbox" value="1" <?php checked('1', get_option('showfill')); ?>/></td>
			</tr>
			<tr valign="top">
				<th scope="row">Fill Style (Example: #000 or #000000 or rgba(0,0,0,0.25))</th>
				<td><input type="text" name="fillstyle" value="<?php echo get_option('fillstyle'); ?>" class="regular-text code"/></td>
			</tr>

			<tr valign="top">
				<th scope="row">Show Dots</th>
				<td><input name="showdots" type="checkbox" value="1" <?php checked('1', get_option('showdots')); ?>/></td>
			</tr>
			<tr valign="top">
				<th scope="row">Show Labels</th>
				<td><input name="showlabels" type="checkbox" value="1" <?php checked('1', get_option('showlabels')); ?>/></td>
			</tr>
			<tr valign="top">
				<th scope="row">Show Shadow</th>
				<td><input name="showshadow" type="checkbox" value="1" <?php checked('1', get_option('showshadow')); ?>/></td>
			</tr>

			<tr valign="top">
				<th scope="row">Label Filter (Example: $%label% or ~ %label%lbs.)</th>
				<td><input type="text" name="labelfilter" value="<?php echo get_option('labelfilter'); ?>" class="regular-text code"/></td>
			</tr>
			<tr valign="top">
				<th scope="row">Data Range (min, max) (Example: 0, 100)</th>
				<td><input type="text" name="range" value="<?php echo get_option('range'); ?>"/></td>
			</tr>

			<tr valign="top">
				<th scope="row">Data Call Back (Live Data)</th>
				<td><input type="text" name="call" value="<?php echo get_option('call'); ?>" class="regular-text code"/></td>
			</tr>
			<tr valign="top">
				<th scope="row">Data Call Back Interval (Speed)</th>
				<td><input type="text" name="interval" value="<?php echo get_option('interval'); ?>"/></td>
			</tr>
		</table>
		<p class="submit">
			<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>"/>
		</p>
	</form>
</div>
<?php
}

# Add settings link on plugin page
function settings_link($links){
	$settings_link = '<a href="options-general.php?page=canvas_graphs">Settings</a>';
	array_unshift($links, $settings_link);
	return $links;
}

# Add Actions http://codex.wordpress.org/Function_Reference/add_action
add_action('init', 'output_scripts');
add_action('admin_init', 'options_init' );
add_action('admin_menu', 'options_add_page');

# Add Shortcode http://codex.wordpress.org/Function_Reference/add_shortcode
add_shortcode('graph', 'graph_shortcode');

# Add Filter http://codex.wordpress.org/Function_Reference/add_filter
# add_filter('the_posts', 'check_install');
add_filter('plugin_action_links_'.plugin_basename(__FILE__), 'settings_link');

# Deactivation
register_deactivation_hook(__FILE__, 'deactivate');
