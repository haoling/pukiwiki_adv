<?php
/////////////////////////////////////////////////
// PukiWiki - Yet another WikiWikiWeb clone.
//
// $Id: side.inc.php,v 1.8.7 2011/02/05 12:38:00 Logue Exp $
//

use PukiWiki\Factory;
use PukiWiki\Renderer\RendererFactory;

// サブメニューを使用する
define('SIDE_ENABLE_SUBMENU', TRUE);

// サブメニューの名称
define('SIDE_SUBMENUBAR', 'SideBar');

function plugin_side_convert()
{
	global $vars, $sidebar;
	static $side = NULL;
	static $sidehtml = NULL;

//miko patched
	// Cached MenuHTML
	if ($sidehtml !== NULL)
		return preg_replace('/<ul class="list[^>]*>/','<ul class="menu">', $sidehtml);
//miko patched

	$num = func_num_args();
	if ($num > 0) {
		// Try to change default 'sideBar' page name (only)
		if ($num > 1)       return '#side(): Zero or One argument needed';
		if ($side !== NULL) return '#side(): Already set: ' . htmlsc($side);
		$args = func_get_args();
		if (! is_page($args[0])) {
			return '#side(): No such page: ' . htmlsc($args[0]);
		} else {
			$side = $args[0]; // Set
			return '';
		}

	} else {
		// Output sidebar page data
		$page = ($side === NULL) ? $sidebar : $side;

		if (SIDE_ENABLE_SUBMENU) {
			$path = explode('/', strip_bracket($vars['page']));
			while(count($path)) {
				$_page = join('/', $path) . '/' . SIDE_SUBMENUBAR;
				if (is_page($_page)) {
					$page = $_page;
					break;
				}
				array_pop($path);
			}
		}

		$wiki = Factory::Wiki($page);

		if (! $wiki->has()) {
			return '';
		} else if ($vars['page'] == $page) {
			return '<!-- #side(): You already view ' . htmlsc($page) . ' -->';
		} else {
			// Cut fixed anchors
			$sidetext = preg_replace('/^(\*{1,3}.*)\[#[A-Za-z][\w-]+\](.*)$/m','$1$2',$wiki->get(true));
//miko patched
			if (function_exists('convert_filter')) {
				$sidetext = convert_filter($sidetext);
			}
			global $top, $use_open_uri_in_new_window;
			$tmptop = $top;
			$top = '';
			$save_newwindow = $use_open_uri_in_new_window;
			$use_open_uri_in_new_window = 0;
			$sidehtml = RendererFactory::factory($sidetext);;
			$top = $tmptop;
			$use_open_uri_in_new_window = $save_newwindow;
			$sidehtml = str_replace("\n",'',$sidehtml);
			return preg_replace('/<ul class="list[^>]*>/','<ul class="menu">',$sidehtml);
//miko patched
		}
	}
}
/* End of file side.inc.php */
/* Location: ./wiki-common/plugin/side.inc.php */