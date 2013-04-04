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

// $data = json_decode($videos);
// echo '<pre>' . print_r($data, true) . '</pre>';

$js = <<<EOD
<script type="text/javascript">
	(function ($) {
		$().ready(function () {
			var videos = {$videos};
			if(videos){
				var header = [
				    '<div class="itemList">',
						'<div id="itemListLeading">'
				];
				$(header.join('')).appendTo(".itemListView");
				$.each(videos, function(index){
					var video = [
					    '<div class="itemContainer" style="width:33.3%;">',
					        '<div class="catItemView groupLeading">',
					        '<a href="#">',
					            '<img src="' + this.videoImage + '" />',
					            '<p class="title">' + this.title + '</p>',
					        '</a>',
					        '<div class="details">',
			                    '<b>' + this.hits + ' times</b>',
			                    '<h1>' + this.title + '</h1>',
			                    '<p>' + this.videoDuration + '|' + this.created + '</p>',
			                    '<div class="catItemIntroText">',
			                        '<p>' + this.introtext + '</p>',
			                    '</div>',
			                    '<p>',
			                        '<a class="k2ReadMore" href="#">Read more... </a>',
			                    '</p>',
			                '</div>',
					    '</div>'
					];
					$(video.join('')).appendTo("#itemListLeading");
				});
			}
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