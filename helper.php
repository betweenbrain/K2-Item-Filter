<?php defined('_JEXEC') or die;

/**
 * File       helper.php
 * Created    2/19/13 1:53 PM
 * Author     Matt Thomas
 * Website    http://betweenbrain.com
 * Email      matt@betweenbrain.com
 * Support    https://github.com/betweenbrain/K2-Category-Tags/issues
 * Copyright  Copyright (C) 2013 betweenbrain llc. All Rights Reserved.
 * License    GNU GPL v3 or later
 */

require_once (JPATH_SITE . DS . 'components' . DS . 'com_k2' . DS . 'helpers' . DS . 'route.php');

class modK2ItemFilterHelper {

	/**
	 * Module parameters
	 *
	 * @var    boolean
	 * @since  0.0
	 */
	protected $params;

	/**
	 * Constructor
	 *
	 * @param   JRegistry $params  The module parameters
	 *
	 * @since  0.0
	 */
	public function __construct($params) {
		// Store the module params
		$this->params = $params;
	}

	function getK2Json($scope = NULL, $id = NULL) {

		$username = $this->params->get('username');
		$password = $this->params->get('password');
		$auth     = $this->params->get('auth');

		switch ($scope) :

			case "category":
				if (!$id && JRequest::getCmd('task') == 'category') {
					$id = JRequest::getVar('id');
				}
				$uri = JURI::base() . 'index.php?option=com_k2&view=itemlist&layout=category&task=category&id=' . $id . '&format=json';
				break;
			case "tag":
				if (!$id && JRequest::getCmd('task') == 'tag') {
					$id = JRequest::getVar('tag');
				}
				$uri = JURI::base() . 'index.php?option=com_k2&view=itemlist&layout=tag&task=tag&tag=' . $id . '&format=json';
				break;
			case "item":
				if (!$id && JRequest::getCmd('view') == 'item') {
					$id = JRequest::getVar('id');
				}
				$uri = JURI::base() . 'index.php?option=com_k2&view=item&layout=item&id=' . $id . '&format=json';
				break;
			default:
				if (JRequest::getCmd('option') == "com_k2") {
					// Reference the global JURI object
					$juri = JUri::getInstance();
					// Set query format as json
					$juri->setVar('format', 'json');
					// String representation of the URI
					$uri = $juri->toString();
				}
		endswitch;

		if ($auth) {
			$context = stream_context_create(array(
				'http' => array(
					'header' => "Authorization: Basic " . base64_encode("$username:$password")
				)
			));
			$json    = file_get_contents($uri, FALSE, $context);
		} else {
			$json = file_get_contents($uri);
		}

		if (json_last_error() == JSON_ERROR_NONE) {
			return $json;
		}

		return FALSE;
	}

	function testJson($json) {

		json_decode($json);

		switch (json_last_error()) {
			case JSON_ERROR_NONE:
				echo 'No errors';
				break;
			case JSON_ERROR_DEPTH:
				echo 'Maximum stack depth exceeded';
				break;
			case JSON_ERROR_STATE_MISMATCH:
				echo 'Underflow or the modes mismatch';
				break;
			case JSON_ERROR_CTRL_CHAR:
				echo 'Unexpected control character found';
				break;
			case JSON_ERROR_SYNTAX:
				echo 'Syntax error, malformed JSON';
				break;
			case JSON_ERROR_UTF8:
				echo 'Malformed UTF-8 characters, possibly incorrectly encoded';
				break;
			default:
				echo 'Unknown error';
				break;
		}
	}

	/**
	 * Function to build an associative array of IDs from supplied JSON data
	 *
	 * @param $json
	 * @return array|bool
	 */
	function buildIdArray() {
		$json = $this->getK2Json();
		$obj  = json_decode($json);

		foreach ($obj->items as $item) {
			$ids[] = $item->id;
		}

		if ($ids) {
			return $ids;
		}

		return FALSE;
	}

	/**
	 * Function to get tags associated with item IDs
	 *
	 * @param $json
	 * @return mixed
	 */

	function getTagData() {
		$json = $this->getK2Json();
		$ids  = $this->buildIdArray($json);

		if ($ids) {
			$db    = JFactory::getDbo();
			$query = "SELECT tag.name, tag.id, xref.itemID AS itemId FROM #__k2_tags as tag LEFT JOIN #__k2_tags_xref AS xref ON xref.tagID = tag.id WHERE xref.itemID IN (" . implode(',', $ids) . ") AND tag.published = 1";
			$db->setQuery($query);
			$rows = $db->loadObjectList();

			return $rows;
		}

		return FALSE;
	}

	/**
	 * Function to generate tag cloud from supplied rows
	 *
	 * @param $rows
	 * @return mixed
	 */

	function buildTagCloud() {

		jimport('joomla.filter.output');

		$obj  = json_decode($this->getK2Json());
		$rows = $this->getTagData();

		// Initialize empty array
		$cloud = array();

		if ($rows) {

			foreach ($rows as $tag) {
				if (@array_key_exists($tag->name, $cloud)) {
					$cloud[$tag->name]++;
				} else {
					$cloud[$tag->name] = 1;
				}
			}

			$counter = '0';
			$total   = NULL;

			foreach ($cloud as $key => $value) {
				$tmp            = new stdClass;
				$tmp->tag       = $key;
				$tmp->alias     = JFilterOutput::stringURLSafe($key);
				$tmp->count     = $value;
				$total          = $total + $value;
				$tmp->link      = urldecode(JRoute::_(K2HelperRoute::getTagRoute($key)));
				$tags[$counter] = $tmp;
				$counter++;
			}

			$tags['category']['name']  = $obj->category->name;
			$tags['category']['total'] = $total;

			return $tags;
		}

		return FALSE;
	}

	/**
	 * Function to associate tags with itemIDs
	 *
	 * @param $json
	 * @return bool
	 */
	function buildTagArray() {

		jimport('joomla.filter.output');

		$rows = $this->getTagData();

		if ($rows) {
			foreach ($rows as $tag) {
				$cloud[$tag->itemId][] = JFilterOutput::stringURLSafe($tag->name);
			}

			return $cloud;
		}

		return FALSE;
	}

	/**
	 * Function to retrieve items passed as JSON and then process them by K2 content plugins
	 *
	 * @param $json
	 * @return array|bool
	 */
	function prepareContent() {

		if ($ids = $this->buildIdArray()) {

			$tags = $this->buildTagArray();

			// Retrieve select data from items currently being viewed
			$db    = JFactory::getDbo();
			$query = "SELECT i.id, i.title, i.alias, i.catid, i.published, i.introtext, i.fulltext, i.created, i.ordering, i.featured, i.hits, i.plugins
				FROM #__k2_items AS i
				WHERE i.id IN (" . implode(',', $ids) . ")
				AND i.published = 1";
			$db->setQuery($query);
			$rows = $db->loadObjectList();

			if ($rows) {

				foreach ($rows as $item) {

					// Process each item through content plugins
					JPluginHelper::importPlugin('k2');
					$dispatcher =& JDispatcher::getInstance();
					$dispatcher->trigger('onK2PrepareContent', array(&$item, &$params, $limitstart));

					// Format date
					$item->created = JHTML::_('date', $item->created, JText::_('K2_DATE_FORMAT_LC2'));

					//Read more link
					$item->link = urldecode(JRoute::_(K2HelperRoute::getItemRoute($item->id . ':' . urlencode($item->alias))));

					// Add tags array to item object
					if (@array_key_exists($item->id, $tags)) {
						$item->tags = $tags[$item->id];
					}

					$items[] = $item;
				}

				return $items;
			}

			return FALSE;
		}
	}
}