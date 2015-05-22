if (typeof jQuery === 'undefined') {
    throw new Error('Better oEmbed Video requires jQuery');
} else {

    jQuery(function($) {
        // px oEmbed handler
        // @Author Prixal
        //
        var pxoEmbed = {
            prevPlaying: null,
            init: function() {
                $('.js-px-oembed').on('click', pxoEmbed.play);
            },
            play: function() {
                pxoEmbed.stop();

                var $self = $(this);
                var $parent = $self.parent();

                $self.attr('aria-expanded', true);
                $self.next().html(pxoEmbed.embed($parent.data('href'), $self));
                $parent.addClass('open');
            },
            stop: function() {
                if( pxoEmbed.prevPlaying != null ) {
                    pxoEmbed.prevPlaying.closest('.px-oembed-wrapper').removeClass('open').find('.top').attr('aria-expanded', false);
                    pxoEmbed.prevPlaying.remove();
                }
            },
            embed: function(url, $el) {

                if( ! url.length ) {
                    throw new Error('No embed video URL');
                    return;
                }

                var $iframe = $('<iframe src="' + url + '?rel=0&autoplay=1&byline=0" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>');
                pxoEmbed.prevPlaying = $iframe;
                $el.next('.bottom').html($iframe);
            }
        };
        pxoEmbed.init();
    });
}
