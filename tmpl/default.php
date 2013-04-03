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
/*
$data = json_decode($videos);
echo '<pre>' . print_r($data, true) . '</pre>';
*/

$js = <<<EOD
<script type="text/javascript">
	(function ($) {
		$().ready(function () {
			var videos = {$videos};
			$.each(videos, function(index){
				var video = [
				    '<div id="something">',
				        '<span>' + this.title + '</span>',
				    '</div>'
				];
				$(video.join('')).appendTo(".videos");
			});
		});
	})(jQuery)
</script>
EOD;

$doc->addCustomTag($js);

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
<div class="videos"></div>