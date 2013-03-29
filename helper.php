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

	/**
	 * Function to fetch K2 json based on current URL being viewed
	 *
	 * @internal param string $name
	 * @return bool|mixed
	 * @since    0.0
	 */
	function getK2Json() {

		if (JRequest::getCmd('option') == "com_k2") {

			// Reference the global JURI object
			$juri = JUri::getInstance();
			// Set query format as json
			$juri->setVar('format', 'json');
			// String representation of the URI
			$uri  = $juri->toString();
			$json = file_get_contents($uri);
			// Decode JSON for error checking
			json_decode($json);
		}
		if (json_last_error() == JSON_ERROR_NONE) {
			return $json;
		}

		return FALSE;
	}

	function getTags($json) {

		$results = json_decode($json);

		foreach ($results->items as $item) {
			$ids[] = $item->id;

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
	}
}