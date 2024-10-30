jQuery(document).ready(function () {
	jQuery('.colorbox').colorbox({
		transition: "elastic",
		height: "98%",
		slideshow: true,
		slideshowAuto: false,
		current: ajax_object.current_image + " {current} " + ajax_object.current_of + " {total}",
		previous: ajax_object.previous,
		next: ajax_object.next,
		close: ajax_object.close,
		xhrError: ajax_object.xhrError,
		imgError: ajax_object.imgError,
		slideshowStart: ajax_object.slideshowStart,
		slideshowStop: ajax_object.slideshowStop
	});
});