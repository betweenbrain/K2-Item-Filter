<?php defined('_JEXEC') or die;

/**
 * File       default.php
 * Created    2/19/13 1:53 PM
 * Author     Matt Thomas
 * Website    http://betweenbrain.com
 * Email      matt@betweenbrain.com
 * Support    https://github.com/betweenbrain/K2-Category-Tags/issues
 * Copyright  Copyright (C) 2013 betweenbrain llc. All Rights Reserved.
 * License    GNU GPL v3 or later
 */

echo '<pre>' . print_r($json, TRUE) . '</pre>';

?>
<ul>
	<?php if ($tags['category']['total']) : ?>
		<li>
			<?php echo $countText . ' ' . $tags['category']['name'] . ' <span class="count">' . $tags['category']['total'] . '</span>' ?>
		</li>
	<?php endif ?>
	<?php
	unset($tags['category']);
	foreach ($tags as $tag) : ?>
		<li>
			<?php echo '<a href="' . $tag->link . '">' . $tag->tag . '</a><span class="count">' . $tag->count . '</span>';?>
		</li>
	<?php endforeach ?>
</ul>