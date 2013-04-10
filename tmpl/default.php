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
			$('.itemList').hide();

			var spinner = [
				'<div class="loading">',
				    '<div class="spinner">',
				        '<div class="mask">',
				            '<div class="maskedCircle"></div>',
			            '</div>',
			        '</div>',
				'</div>'
			];
			 $(spinner.join('')).appendTo(".itemListView");

			$(window).bind('load', function () {
		        var tagAlias = window.location.href.split('#')[1];
		        if(tagAlias) {
		            $(".itemList").remove();
		            showVideos(tagAlias);
		        } else {
		            $(".loading").hide();
		            $('.itemList').show();

		        }
		    });

  			$('a.cloud').click(function () {
			    var tagAlias = this.href.split("#")[1];
			    $(".itemList").fadeOut(300, function(){ $(".itemList").remove();
			        showVideos(tagAlias);
				});
			});

		    function showVideos(tagAlias){
		        $(".loading").show();

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
                                    '<a href="' + this.link + '">',
                                        '<img src="' + this.videoImage + '" />',
                                        '<p class="title">' + this.title + '</p>',
                                    '</a>',
                                    '<div class="details">',
                                        '<b>' + this.hits + ' times</b>',
                                        '<h1>' + this.title + '</h1>',
                                        '<p>' + this.videoDuration + ' | ' + this.created + '</p>',
                                        '<div class="catItemIntroText">',
                                            '<p>' + this.introtext + '</p>',
                                        '</div>',
                                        '<p>',
                                            '<a class="k2ReadMore" href="' + this.link + '">Read more... </a>',
                                        '</p>',
                                    '</div>',
                                '</div>'
                            ];
                            $(video.join('')).appendTo("#itemListLeading").hide().fadeIn(300);
	                    }
	                });
	            }
	             $(".loading").hide();
		    }
		});
	})(jQuery)
</script>
EOD;

$doc->addCustomTag($js);

$css = '
	@-webkit-keyframes spin {
	from { -webkit-transform: rotate(0deg); }
	to { -webkit-transform: rotate(360deg); }
	}

	@-moz-keyframes spin {
	from { -moz-transform: rotate(0deg); }
	to { -moz-transform: rotate(360deg); }
	}

	@-o-keyframes spin {
	from { -o-transform: rotate(0deg); }
	to { -o-transform: rotate(360deg); }
	}

	@keyframes spin {
		from { transform: rotate(0deg); }
		to { transform: rotate(360deg); }
	}

	.loading {
	    position: absolute;
	    top: 50%;
	    left: 50%;
	    width: 28px;
	    height: 28px;
	    margin: -14px 0 0 -14px;
	}

	/* Spinning circle (inner circle) */
	.loading .maskedCircle {
	    width: 20px;
	    height: 20px;
	    border-radius: 12px;
	    border: 3px solid #444;
	}

	/* Spinning circle mask */
	.loading .mask {
	    width: 12px;
	    height: 12px;
	    overflow: hidden;
	}

	/* Spinner */
	.loading .spinner {
		position: absolute;
		left: 1px;
		top: 1px;
		width: 26px;
		height: 26px;
		-webkit-animation: spin 1s infinite linear;
		-moz-animation: spin 1s infinite linear;
		-o-animation: spin 1s infinite linear;
		animation: spin 1s infinite linear;
}';

$doc->addStyleDeclaration($css);

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