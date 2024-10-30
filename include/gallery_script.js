jQuery(document).ready(function () {
    //place photosets labels with better margin
    jQuery('.masburti_photoset_link h4').each(function () {
        jQuery(this).css('margin-top', (-1) * jQuery(this).outerHeight() * 0.5 + 'px');
    });

    jQuery('.masburti_photoset_image, .masburti_photoset_link').click(function () {
        var data = {
            'action': 'mfg_photoset_show',
            'photoset_flickr_id': jQuery(this).data('id'),
            'thumbnails_cols': ajax_object.thumbnails_cols
        };

        var displayer = jQuery(this).closest('table').prev();
        displayer.fadeIn(500);
        displayer.find('.masburti_photoset_back').hide();
        displayer.find('.masburti_photoset_photos').hide();
        displayer.find('.masburti_photoset_title').show();
        displayer.find('.masburti_photoset_title').text(jQuery(this).data('title'));
        displayer.find('.masburti_photoset_loading').show();

        jQuery(this).closest('table').fadeOut(250);

        jQuery('html, body').animate({
            scrollTop: jQuery('article').offset().top
        }, 250);

        jQuery.get(ajax_object.ajax_url, data, function (response) {
            displayer.find('.masburti_photoset_back').show();
            displayer.find('.masburti_photoset_photos').show();
            displayer.find('.masburti_photoset_loading').hide();
            displayer.find('.masburti_photoset_photos').html(response);

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

            jQuery('.masburti_photoset_photos').find('.masburti_photoset_photo').each(function () {
                var height = jQuery(this).width() * 0.75;

                jQuery(this).find('img').css('height', height + 'px')
            });

            var photosPerRow = jQuery('.masburti_photoset_photos').find('tr:eq(0)').find('td').length;
            var cellPadding = 2 / photosPerRow * 8;
            jQuery('.masburti_photoset_photos').find('td').css('padding', cellPadding + 'px');
        });
    });

    jQuery('.masburti_photoset_back').click(function () {
        jQuery(this).parent().next().fadeIn(500);
        jQuery(this).parent().fadeOut(250);
    });

    jQuery('.masburti_photosets').find('.masburti_photoset_image').each(function () {
        var height = jQuery(this).width() * 0.75;
        jQuery(this).find('img').css('height', height + 'px');
    });

    var photosetsPerRow = jQuery('.masburti_photosets').find('tr:eq(0)').find('td').length;
    var cellPadding = 2 / photosetsPerRow * 10;
    jQuery('.masburti_photosets').find('td').css('padding', '0 ' + cellPadding + 'px');
    jQuery('.masburti_photosets').find('td.masburti_photoset_link').css('padding-bottom', (cellPadding * 2) + 'px');
});