<?php
/*

File: king-include/king-page-default.php
Description: Controller for home page, Q&A listing page, custom pages and plugin pages

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

More about this license: LICENCE.html
 */

if (!defined('QA_VERSION')) {
	// don't allow this page to be requested directly from browser
	header('Location: ../');
	exit;
}

require_once QA_INCLUDE_DIR . 'king-db/selects.php';
require_once QA_INCLUDE_DIR . 'king-app/format.php';

//    Determine whether path begins with qa or not (question and answer listing can be accessed either way)

$requestparts = explode('/', qa_request());
$explicitqa   = (strtolower($requestparts[0]) == 'qa');

if ($explicitqa) {
	$slugs = array_slice($requestparts, 1);
} elseif (strlen($requestparts[0])) {
	$slugs = $requestparts;
} else {
	$slugs = array();
}

$countslugs = count($slugs);
$start      = qa_get_start();

//    Get list of questions, other bits of information that might be useful

$userid = qa_get_logged_in_userid();

list($questions1, $questions2, $categories, $categoryid, $custompage) = qa_db_select_with_pending(
	qa_db_qs_selectspec($userid, 'created', $start, $slugs, null, false, false, qa_opt_if_loaded('page_size_activity')),
	qa_db_recent_a_qs_selectspec($userid, 0, $slugs),
	qa_db_category_nav_selectspec($slugs, false, false, true),
	$countslugs ? qa_db_slugs_to_category_id_selectspec($slugs) : null,
	(($countslugs == 1) && !$explicitqa) ? qa_db_page_full_selectspec($slugs[0], false) : null
);

//    First, if this matches a custom page, return immediately with that page's content

if (isset($custompage) && !($custompage['flags'] & QA_PAGE_FLAGS_EXTERNAL)) {
	qa_set_template('custom-' . $custompage['pageid']);

	$qa_content = qa_content_prepare();

	$level = qa_get_logged_in_level();

	if ((!qa_permit_value_error($custompage['permit'], $userid, $level, qa_get_logged_in_flags())) || !isset($custompage['permit'])) {
		$qa_content['title']  = qa_html($custompage['heading']);
		$qa_content['custompage'] = $custompage['content'];

		if ($level >= QA_USER_LEVEL_ADMIN) {
			$qa_content['navigation']['sub'] = array(
				'admin/pages' => array(
					'label' => qa_lang('admin/edit_custom_page'),
					'url'   => qa_path_html('admin/pages', array('edit' => $custompage['pageid'])),
				),
			);
		}

	} else {
		$qa_content['error'] = qa_lang_html('users/no_permission');
	}
	$qa_content['sside']=true;
	$qa_content['class'] = ' post-page';
	return $qa_content;
}

//    Then, see if we should redirect because the 'qa' page is the same as the home page

if ($explicitqa && (!qa_is_http_post()) && !qa_has_custom_home()) {
	qa_redirect(qa_category_path_request($categories, $categoryid), $_GET);
}

//    Then, if there's a slug that matches no category, check page modules provided by plugins

if ((!$explicitqa) && $countslugs && !isset($categoryid)) {
	$pagemodules = qa_load_modules_with('page', 'match_request');
	$request     = qa_request();

	foreach ($pagemodules as $pagemodule) {
		if ($pagemodule->match_request($request)) {
			qa_set_template('plugin');
			return $pagemodule->process_request($request);
		}
	}

}

//    Then, check whether we are showing a custom home page

if ((!$explicitqa) && (!$countslugs) && qa_opt('show_custom_home')) {
	qa_set_template('custom');
	$qa_content           = qa_content_prepare();
	$qa_content['title']  = qa_html(qa_opt('custom_home_heading'));
	$qa_content['custom'] = qa_opt('custom_home_content');
	return $qa_content;
}

//    If we got this far, it's a good old-fashioned Q&A listing page

require_once QA_INCLUDE_DIR . 'king-app/q-list.php';

qa_set_template('qa');
$questions = qa_any_sort_and_dedupe(array_merge($questions1, $questions2));
$pagesize  = qa_opt('page_size_home');
	if (!isset($categoryid)) {
		return include QA_INCLUDE_DIR . 'king-page-not-found.php';
	}
	$output = '';
if ($countslugs) {


	$output .= '<span class="cat-title" ' . (($categories[$categoryid]['color']) ? 'style="background-color: ' . $categories[$categoryid]['color'] . ';"' : '') . '>';
	$output .= $categories[$categoryid]['icon'] . ' ';
	$output .= qa_html($categories[$categoryid]['title']);
	if ( ! $categories[$categoryid]['qcount'] ) {
		$output .= '<p>' . qa_lang_html('main/no_questions_found') . '</p>';
	} else {
		$output .= '<p>' . $categories[$categoryid]['content'] . '</p>';
	}
	$output .= king_follow_tc($categoryid, 'cat');
	$output .= '</span>';
	$categorytitlehtml=qa_html($categories[$categoryid]['title']);
	$sometitle=qa_lang_html_sub('main/recent_qs_as_in_x', $categorytitlehtml);
	$nonetitle         = $output;

} else {
	$sometitle = qa_lang_html('main/recent_qs_as_title');
	$nonetitle = qa_lang_html('main/no_questions_found');
}

//    Prepare and return content for theme for Q&A listing page

$qa_content = qa_q_list_page_content(
	$questions, // questions
	$pagesize, // questions per page
	$start, // start offset
	$countslugs ? $categories[$categoryid]['qcount'] : qa_opt('cache_qcount'), // total count
	$sometitle, // title if some questions
	$nonetitle, // title if no questions
	$categories, // categories for navigation
	$categoryid, // selected category id
	true, // show question counts in category navigation
	$explicitqa ? 'qa/' : '', // prefix for links in category navigation
	qa_opt('feed_for_qa') ? 'qa' : null, // prefix for RSS feed paths (null to hide)
	null,
	null, // page link params
	null// category nav params
);

if (QA_ALLOW_UNINDEXED_QUERIES || !$countslugs) {
	$qa_content['navigation']['sub'] = qa_qs_sub_navigation($sort, $categoryslugs);
}
$qa_content['class']  = ' full-page';
$qa_content['header'] = $output;
return $qa_content;

/*
Omit PHP closing tag to help avoid accidental output
 */
