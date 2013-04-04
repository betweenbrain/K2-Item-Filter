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

$js = <<<EOD
<script type="text/javascript">
	(function ($) {

		$().ready(function () {

			$(window).bind('load', function () {
		        var tagAlias = window.location.href.split('#')[1];
		        showVideos(tagAlias);
		    });

  			$('a.cloud').click(function () {
			    var tagAlias = this.href.split("#")[1];
			    $(".itemList").fadeOut(300, function(){ $(".itemList").remove();
			        showVideos(tagAlias);
				});
			});

		    function showVideos(tagAlias){
		        var videos = {$videos};

		         if(videos){
	                var header = [
	                    '<div class="itemList">',
	                        '<div id="itemListLeading">'
	                ];

	                $(header.join('')).appendTo(".itemListView");

	                $.each(videos, function(index){
                        var tagArray = this.tags ? this.tags : '';

                        if ($.inArray(tagAlias, tagArray) !== -1) {
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
                            $(video.join('')).appendTo("#itemListLeading").hide().fadeIn(300);
	                    }
	                });
	            }
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
			<?php echo '<a class="cloud" href="#' . $tag->alias . '">' . $tag->tag . '</a><span class="count">' . $tag->count . '</span>';?>
		</li>
	<?php endforeach ?>
</ul>