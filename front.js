/*!
 * jQuery Smooth Scroll - v1.6.0 - 2015-12-26
 * https://github.com/kswedberg/jquery-smooth-scroll
 * Copyright (c) 2015 Karl Swedberg
 * Licensed MIT
 */
!function(a){"function"==typeof define&&define.amd?
// AMD. Register as an anonymous module.
define(["jquery"],a):a("object"==typeof module&&module.exports?require("jquery"):jQuery)}(function(a){function b(a){return a.replace(/(:|\.|\/)/g,"\\$1")}var c="1.6.0",d={},e={exclude:[],excludeWithin:[],offset:0,
// one of 'top' or 'left'
direction:"top",
// if set, bind click events through delegation
//  supported since jQuery 1.4.2
delegateSelector:null,
// jQuery set of elements you wish to scroll (for $.smoothScroll).
//  if null (default), $('html, body').firstScrollable() is used.
scrollElement:null,
// only use if you want to override default behavior
scrollTarget:null,
// fn(opts) function to be called before scrolling occurs.
// `this` is the element(s) being scrolled
beforeScroll:function(){},
// fn(opts) function to be called after scrolling occurs.
// `this` is the triggering element
afterScroll:function(){},easing:"swing",speed:400,
// coefficient for "auto" speed
autoCoefficient:2,
// $.fn.smoothScroll only: whether to prevent the default click action
preventDefault:!0},f=function(b){var c=[],d=!1,e=b.dir&&"left"===b.dir?"scrollLeft":"scrollTop";
// If no scrollable elements, fall back to <body>,
// if it's in the jQuery collection
// (doing this because Safari sets scrollTop async,
// so can't set it to 1 and immediately get the value.)
// Use the first scrollable element if we're calling firstScrollable()
return this.each(function(){var b=a(this);if(this!==document&&this!==window)
// if scroll(Top|Left) === 0, nudge the element 1px and see if it moves
// then put it back, of course
return!document.scrollingElement||this!==document.documentElement&&this!==document.body?void(b[e]()>0?c.push(this):(b[e](1),d=b[e]()>0,d&&c.push(this),b[e](0))):(c.push(document.scrollingElement),!1)}),c.length||this.each(function(){"BODY"===this.nodeName&&(c=[this])}),"first"===b.el&&c.length>1&&(c=[c[0]]),c};a.fn.extend({scrollable:function(a){var b=f.call(this,{dir:a});return this.pushStack(b)},firstScrollable:function(a){var b=f.call(this,{el:"first",dir:a});return this.pushStack(b)},smoothScroll:function(c,d){if(c=c||{},"options"===c)return d?this.each(function(){var b=a(this),c=a.extend(b.data("ssOpts")||{},d);a(this).data("ssOpts",c)}):this.first().data("ssOpts");var e=a.extend({},a.fn.smoothScroll.defaults,c),f=function(c){var d=this,f=a(this),g=a.extend({},e,f.data("ssOpts")||{}),h=e.exclude,i=g.excludeWithin,j=0,k=0,l=!0,m={},n=a.smoothScroll.filterPath(location.pathname),o=a.smoothScroll.filterPath(d.pathname),p=location.hostname===d.hostname||!d.hostname,q=g.scrollTarget||o===n,r=b(d.hash);if(g.scrollTarget||p&&q&&r){for(;l&&j<h.length;)f.is(b(h[j++]))&&(l=!1);for(;l&&k<i.length;)f.closest(i[k++]).length&&(l=!1)}else l=!1;l&&(g.preventDefault&&c.preventDefault(),a.extend(m,g,{scrollTarget:g.scrollTarget||r,link:d}),a.smoothScroll(m))};return null!==c.delegateSelector?this.undelegate(c.delegateSelector,"click.smoothscroll").delegate(c.delegateSelector,"click.smoothscroll",f):this.unbind("click.smoothscroll").bind("click.smoothscroll",f),this}}),a.smoothScroll=function(b,c){if("options"===b&&"object"==typeof c)return a.extend(d,c);var e,f,g,h,i,j=0,k="offset",l="scrollTop",m={},n={};"number"==typeof b?(e=a.extend({link:null},a.fn.smoothScroll.defaults,d),g=b):(e=a.extend({link:null},a.fn.smoothScroll.defaults,b||{},d),e.scrollElement&&(k="position","static"===e.scrollElement.css("position")&&e.scrollElement.css("position","relative"))),l="left"===e.direction?"scrollLeft":l,e.scrollElement?(f=e.scrollElement,/^(?:HTML|BODY)$/.test(f[0].nodeName)||(j=f[l]())):f=a("html, body").firstScrollable(e.direction),e.beforeScroll.call(f,e),g="number"==typeof b?b:c||a(e.scrollTarget)[k]()&&a(e.scrollTarget)[k]()[e.direction]||0,m[l]=g+j+e.offset,h=e.speed,"auto"===h&&(i=Math.abs(m[l]-f[l]()),h=i/e.autoCoefficient),n={duration:h,easing:e.easing,complete:function(){e.afterScroll.call(e.link,e)}},e.step&&(n.step=e.step),f.length?f.stop().animate(m,n):e.afterScroll.call(e.link,e)},a.smoothScroll.version=c,a.smoothScroll.filterPath=function(a){return a=a||"",a.replace(/^\//,"").replace(/(?:index|default).[a-zA-Z]{3,4}$/,"").replace(/\/$/,"")},
// default options
a.fn.smoothScroll.defaults=e});

/**
 * jQuery Cookie plugin
 *
 * Copyright (c) 2010 Klaus Hartl (stilbuero.de)
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 *
 */
jQuery.cookie=function(a,b,c){if(arguments.length>1&&String(b)!=="[object Object]"){c=jQuery.extend({},c);if(b===null||b===undefined){c.expires=-1}if(typeof c.expires==="number"){var d=c.expires,e=c.expires=new Date;e.setDate(e.getDate()+d)}b=String(b);return document.cookie=[encodeURIComponent(a),"=",c.raw?b:encodeURIComponent(b),c.expires?"; expires="+c.expires.toUTCString():"",c.path?"; path="+c.path:"",c.domain?"; domain="+c.domain:"",c.secure?"; secure":""].join("")}c=b||{};var f,g=c.raw?function(a){return a}:decodeURIComponent;return(f=(new RegExp("(?:^|; )"+encodeURIComponent(a)+"=([^;]*)")).exec(document.cookie))?g(f[1]):null}

jQuery(document).ready(function($) {
	function getScrollOffset() {
		if ( typeof tocplus.smooth_scroll_offset != 'undefined' ) {
			return -1 * tocplus.smooth_scroll_offset;
		}

		if ( $('#wpadminbar').length > 0 && $('#wpadminbar').is(':visible') ) {
			return -30;
		}

		return 0;
	}

	function getViewportTopOffset() {
		var offset = 96;

		if ( typeof tocplus.display_top_offset !== 'undefined' ) {
			offset = parseInt(tocplus.display_top_offset, 10) || offset;
		}

		if ( $('#wpadminbar').length > 0 && $('#wpadminbar').is(':visible') ) {
			offset += $('#wpadminbar').outerHeight();
		}

		return offset;
	}

	function findTargetElement(hash) {
		var targetId;
		var targetElement;

		if ( !hash || hash.charAt(0) !== '#' ) {
			return null;
		}

		targetId = hash.substring(1);

		try {
			targetId = decodeURIComponent(targetId);
		}
		catch (error) {
			// Keep the raw hash when it cannot be decoded.
		}

		targetElement = document.getElementById(targetId);
		if ( targetElement ) {
			return targetElement;
		}

		targetElement = document.getElementsByName(targetId);
		if ( targetElement.length > 0 ) {
			return targetElement[0];
		}

		return null;
	}

	function getHeadingTargets() {
		return $(
			'h1 > span[id], h2 > span[id], h3 > span[id], h4 > span[id], h5 > span[id], h6 > span[id]'
		);
	}

	function getLabelHtml($item) {
		var html = '';

		$item.contents().each(function() {
			if ( this.nodeType === 1 && this.tagName.toLowerCase() === 'ul' ) {
				return false;
			}

			if ( this.nodeType === 1 ) {
				html += this.outerHTML;
			} else if ( this.nodeType === 3 ) {
				html += this.nodeValue;
			}
		});

		return $.trim(html);
	}

	function repairItemLink($item, targetId) {
		var $link = $item.children('a').first();
		var labelHtml;

		if ( $link.length ) {
			$link.attr('href', '#' + targetId);
			return;
		}

		labelHtml = getLabelHtml($item);
		if ( !labelHtml ) {
			return;
		}

		$item.contents().filter(function() {
			return !( this.nodeType === 1 && this.tagName.toLowerCase() === 'ul' );
		}).remove();

		$('<a />', {
			href: '#' + targetId,
			html: labelHtml
		}).prependTo($item);
	}

	function repairTOCLinks($container) {
		var $items = $container.find('ul.toc_list li');
		var $targets = getHeadingTargets();
		var max;
		var index;
		var targetId;

		if ( !$items.length || !$targets.length ) {
			return;
		}

		max = Math.min($items.length, $targets.length);

		for ( index = 0; index < max; index++ ) {
			targetId = $targets.eq(index).attr('id');
			if ( targetId ) {
				repairItemLink($items.eq(index), targetId);
			}
		}
	}

	function buildTrackedHeadings($container) {
		var trackedHeadings = [];

		$container.find('ul.toc_list li').each(function() {
			var $item = $(this);
			var $link = $item.children('a').first();
			var targetElement;

			if ( !$link.length ) {
				return;
			}

			targetElement = findTargetElement($link.attr('href'));
			if ( !targetElement ) {
				return;
			}

			trackedHeadings.push({
				$item: $item,
				$link: $link,
				target: targetElement
			});
		});

		return trackedHeadings;
	}

	function clearActiveHeading($container) {
		$container.find('li.is-active, li.is-active-parent').removeClass('is-active is-active-parent');
		$container.find('a.is-active-link').removeClass('is-active-link');
	}

	function setBranchExpanded($item, expanded, animate) {
		var $branch = $item.children('ul').first();
		var $toggle = $item.children('.toc_branch_toggle').first();
		var toggleLabel = expanded ? 'Hide subsections' : 'Show subsections';
		var method = expanded ? 'slideDown' : 'slideUp';

		if ( !$branch.length ) {
			return;
		}

		$item.toggleClass('is-expanded', expanded).toggleClass('is-collapsed', !expanded);
		$toggle.attr({
			'aria-expanded': expanded ? 'true' : 'false',
			'aria-label': toggleLabel,
			'title': toggleLabel
		});
		$toggle.find('.toc_branch_toggle_text').text(toggleLabel);

		if ( animate ) {
			$branch.stop(true, true)[method](160);
			return;
		}

		if ( expanded ) {
			$branch.show();
			return;
		}

		$branch.hide();
	}

	function syncCollapsibleBranches($container, animate) {
		var expandByDefault;

		if ( !$container.hasClass('toc_collapsible_subsections') ) {
			return;
		}

		expandByDefault = $container.hasClass('toc_collapsible_default_open');

		$container.find('li.is-collapsible').each(function() {
			var $item = $(this);
			var manualState = $item.data('tocBranchManualState');
			var shouldExpand = expandByDefault || $item.hasClass('is-active') || $item.hasClass('is-active-parent');

			if ( manualState === 'expanded' ) {
				shouldExpand = true;
			} else if ( manualState === 'collapsed' && !$item.hasClass('is-active') && !$item.hasClass('is-active-parent') ) {
				shouldExpand = false;
			}

			setBranchExpanded($item, shouldExpand, animate && typeof manualState !== 'undefined');
		});
	}

	function initializeCollapsibleSections($container) {
		if ( !$container.hasClass('toc_collapsible_subsections') ) {
			return;
		}

		$container.find('li').has('> ul').each(function() {
			var $item = $(this);
			var $link = $item.children('a').first();
			var $toggle = $item.children('.toc_branch_toggle').first();

			$item.addClass('is-collapsible');

			if ( !$toggle.length ) {
				$toggle = $('<button />', {
					type: 'button',
					'class': 'toc_branch_toggle',
					'aria-expanded': 'false',
					'aria-label': 'Show subsections',
					'title': 'Show subsections',
					html: '<span class="toc_branch_toggle_icon" aria-hidden="true"></span><span class="toc_branch_toggle_text">Show subsections</span>'
				});

				if ( $link.length ) {
					$link.after($toggle);
				} else {
					$item.prepend($toggle);
				}
			}
		});

		syncCollapsibleBranches($container, false);

		$container.off('click.tocplusBranches').on('click.tocplusBranches', '.toc_branch_toggle', function(event) {
			var $toggle = $(this);
			var $item = $toggle.parent('li');
			var expanded = !$item.hasClass('is-expanded');

			event.preventDefault();
			$item.data('tocBranchManualState', expanded ? 'expanded' : 'collapsed');
			setBranchExpanded($item, expanded, true);
		});
	}

	function setActiveHeading($container, entry) {
		clearActiveHeading($container);

		if ( !entry ) {
			return;
		}

		entry.$item.addClass('is-active');
		entry.$link.addClass('is-active-link');
		entry.$item.parents('li').addClass('is-active-parent');
		syncCollapsibleBranches($container, true);
	}

	function createActiveHeadingUpdater($container, eventNamespace) {
		var trackedHeadings = buildTrackedHeadings($container);
		var ticking = false;
		var scheduleUpdate = window.requestAnimationFrame || function(callback) {
			return window.setTimeout(callback, 16);
		};

		function updateActiveHeading() {
			var currentEntry = null;
			var scrollPosition;
			var index;

			ticking = false;

			if ( !trackedHeadings.length ) {
				clearActiveHeading($container);
				return;
			}

			scrollPosition = $(window).scrollTop() - getScrollOffset() + 12;

			for ( index = 0; index < trackedHeadings.length; index++ ) {
				if ( $(trackedHeadings[index].target).offset().top <= scrollPosition ) {
					currentEntry = trackedHeadings[index];
				} else {
					break;
				}
			}

			if ( !currentEntry ) {
				currentEntry = trackedHeadings[0];
			}

			setActiveHeading($container, currentEntry);
		}

		function requestUpdate() {
			if ( ticking ) {
				return;
			}

			ticking = true;
			scheduleUpdate(updateActiveHeading);
		}

		$(window)
			.off(eventNamespace)
			.on('scroll' + eventNamespace + ' resize' + eventNamespace + ' hashchange' + eventNamespace + ' load' + eventNamespace, requestUpdate);

		requestUpdate();
	}

	function setContainerContracted($container, $list, contracted) {
		if ( contracted ) {
			$list.hide('fast');
			$container.addClass('contracted').css({ width: 'auto', display: 'table' });
			if ( /MSIE 7\./.test(navigator.userAgent) ) {
				$container.css('width', '');
			}
			return;
		}

		$container.css('width', tocplus.width).removeClass('contracted');
		$list.show('fast');
	}

	function applyContainerViewportStyles($container) {
		$container[0].style.setProperty('--wptoc-display-top', getViewportTopOffset() + 'px');
	}

	function isCompactMobileViewport() {
		return ( window.innerWidth || document.documentElement.clientWidth || 0 ) <= 782;
	}

	function getMobileToggleLabel($container) {
		var $title = $container.find('p.toc_title').first().clone();

		$title.find('.toc_toggle, .toc_brackets').remove();

		return $.trim($title.text()) || 'Contents';
	}

	function setMobileCompactState($container, $list, expanded, animate) {
		var $button = $container.children('.toc_mobile_toggle');
		var method = expanded ? 'slideDown' : 'slideUp';

		$container.toggleClass('is-mobile-open', expanded);
		$button.attr('aria-expanded', expanded ? 'true' : 'false');

		if ( animate ) {
			$list.stop(true, true)[method](180);
			return;
		}

		if ( expanded ) {
			$list.show();
			return;
		}

		$list.hide();
	}

	function syncDesktopListState($container, $list) {
		$container.removeClass('is-mobile-open');

		if ( $container.hasClass('contracted') ) {
			$list.hide();
			return;
		}

		$list.show();
	}

	function applyMobileCompactMode($container, $list) {
		var isCompactMode = $container.hasClass('toc_mobile_compact');
		var isMobile = isCompactMobileViewport();
		var $button;
		var expanded;

		if ( !isCompactMode || !$list.length ) {
			return;
		}

		$button = $container.children('.toc_mobile_toggle');

		if ( !$button.length ) {
			$button = $('<button />', {
				type: 'button',
				'class': 'toc_mobile_toggle',
				'aria-expanded': 'false',
				html: '<span class="toc_mobile_toggle_label"></span><span class="toc_mobile_toggle_icon" aria-hidden="true"></span>'
			});

			$container.prepend($button);
		}

		$button.find('.toc_mobile_toggle_label').text(getMobileToggleLabel($container));

		if ( !isMobile ) {
			syncDesktopListState($container, $list);
			return;
		}

		expanded = $container.data('tocMobileExpanded');
		if ( typeof expanded === 'undefined' ) {
			expanded = false;
			$container.data('tocMobileExpanded', false);
		}

		setMobileCompactState($container, $list, expanded, false);
	}


	function applyInitialToggleState($container, $list, invert) {
		var visibilityText;

		if ( $.cookie ) {
			visibilityText = $.cookie('tocplus_hidetoc') ? tocplus.visibility_show : tocplus.visibility_hide;
		} else {
			visibilityText = tocplus.visibility_hide;
		}

		if ( invert ) {
			visibilityText = ( visibilityText == tocplus.visibility_hide ) ? tocplus.visibility_show : tocplus.visibility_hide;
		}

		if ( !$container.find('span.toc_toggle').length ) {
			$container.find('p.toc_title').append(
				' <span class="toc_toggle"><span class="toc_brackets">[</span><a href="#">' + visibilityText + '</a><span class="toc_brackets">]</span></span>'
			);
		}

		if ( visibilityText == tocplus.visibility_show ) {
			setContainerContracted($container, $list, true);
		}
	}

	if ( typeof tocplus === 'undefined' ) {
		return;
	}

	$('div#toc_container').each(function(index) {
		var $container = $(this);
		var $list = $container.children('ul.toc_list').first();
		var eventNamespace = '.tocplusActive' + index;
		var viewportEventNamespace = '.tocplusViewport' + index;
		var invert = typeof tocplus.visibility_hide_by_default !== 'undefined';

		repairTOCLinks($container);
		initializeCollapsibleSections($container);
		createActiveHeadingUpdater($container, eventNamespace);

		$(window)
			.off(viewportEventNamespace)
			.on('resize' + viewportEventNamespace + ' load' + viewportEventNamespace + ' scroll' + viewportEventNamespace, function(event) {
				applyContainerViewportStyles($container);
				applyMobileCompactMode($container, $list);
			});

		applyContainerViewportStyles($container);
		applyMobileCompactMode($container, $list);

		if ( tocplus.smooth_scroll === 1 ) {
			$container.off('click.tocplusSmooth').on('click.tocplusSmooth', 'a[href^="#"]', function(event) {
				var targetElement = findTargetElement($(this).attr('href'));

				if ( !targetElement ) {
					return;
				}

				event.preventDefault();
				$.smoothScroll({
					scrollTarget: targetElement,
					offset: getScrollOffset()
				});
			});
		}

		if ( typeof tocplus.visibility_show !== 'undefined' && $container.find('p.toc_title').length && $list.length ) {
			applyInitialToggleState($container, $list, invert);

			$container.off('click.tocplusToggle').on('click.tocplusToggle', 'span.toc_toggle a', function(event) {
				event.preventDefault();

				switch ( $(this).html() ) {
					case $('<div/>').html(tocplus.visibility_hide).text():
						$(this).html(tocplus.visibility_show);
						if ( $.cookie ) {
							if ( invert ) {
								$.cookie('tocplus_hidetoc', null, { path: '/' });
							} else {
								$.cookie('tocplus_hidetoc', '1', { expires: 30, path: '/' });
							}
						}
						setContainerContracted($container, $list, true);
						break;

					case $('<div/>').html(tocplus.visibility_show).text():
					default:
						$(this).html(tocplus.visibility_hide);
						if ( $.cookie ) {
							if ( invert ) {
								$.cookie('tocplus_hidetoc', '1', { expires: 30, path: '/' });
							} else {
								$.cookie('tocplus_hidetoc', null, { path: '/' });
							}
						}
						setContainerContracted($container, $list, false);
				}
			});
		}

		$container.off('click.tocplusMobileToggle').on('click.tocplusMobileToggle', '.toc_mobile_toggle', function(event) {
			var expanded;

			event.preventDefault();

			if ( !isCompactMobileViewport() ) {
				return;
			}

			expanded = !$container.hasClass('is-mobile-open');
			$container.data('tocMobileExpanded', expanded);
			setMobileCompactState($container, $list, expanded, true);
		});

		$container.off('click.tocplusMobileLink').on('click.tocplusMobileLink', 'ul.toc_list a[href^="#"]', function() {
			if ( !$container.hasClass('toc_mobile_compact') || !isCompactMobileViewport() ) {
				return;
			}

			$container.data('tocMobileExpanded', false);
			setMobileCompactState($container, $list, false, true);
		});
	});
});