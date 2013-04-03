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

		$json = file_get_contents($uri);

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
	function buildIdArray($json) {
		$obj = json_decode($json);

		foreach ($obj->items as $item) {
			$ids[] = $item->id;
		}

		if ($ids) {
			return $ids;
		}

		return FALSE;
	}

	function getTags($json) {

		$results = json_decode($json);

		foreach ($results->items as $item) {
			$ids[] = $item->id;
		}

		if ($ids) {
			$db    = JFactory::getDbo();
			$query = "SELECT tag.name, tag.id FROM #__k2_tags as tag LEFT JOIN #__k2_tags_xref AS xref ON xref.tagID = tag.id WHERE xref.itemID IN (" . implode(',', $ids) . ") AND tag.published = 1";
			$db->setQuery($query);
			$rows = $db->loadObjectList();

			$cloud = array();
			if (count($rows)) {
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
					$tmp->count     = $value;
					$total          = $total + $value;
					$tmp->link      = urldecode(JRoute::_(K2HelperRoute::getTagRoute($key)));
					$tags[$counter] = $tmp;
					$counter++;
				}

				$tags['category']['name']  = $results->category->name;
				$tags['category']['total'] = $total;

				return $tags;
			}
		}
	}

	/**
	 * Function to retrieve items passed as JSON and then process them by K2 content plugins
	 *
	 * @param $json
	 * @return array|bool
	 */
	function prepareContent($json) {
		$results = json_decode($json);

		// Build array of IDs to reduce the number of database queries
		foreach ($results->items as $item) {
			$ids[] = $item->id;
		}

		// Retrieve select data from items currently being viewed
		$db    = JFactory::getDbo();
		$query = "SELECT i.id, i.title, i.alias, i.catid, i.published, i.introtext, i.fulltext, i.created, i.ordering, i.featured, i.hits, i.plugins, tag.name AS tag, tag.id as tagId
		FROM #__k2_tags as tag
		LEFT JOIN #__k2_tags_xref AS xref
		ON xref.tagID = tag.id
		LEFT JOIN #__k2_items AS i
		ON i.id = xref.itemID
		WHERE xref.itemID IN (" . implode(',', $ids) . ")
		AND tag.published = 1
		AND i.published = 1";
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		// Process each item through content plugins
		foreach ($rows as $item) {
			JPluginHelper::importPlugin('k2');
			$dispatcher =& JDispatcher::getInstance();
			$dispatcher->trigger('onK2PrepareContent', array(&$item, &$params, $limitstart));

			if ($item) {
				$items[] = $item;
			}
		}

		if ($items) {
			return $items;
		}

		return FALSE;
	}
}