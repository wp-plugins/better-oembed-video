<?php
/**
 *  Plugin Name: Better oEmbed Video
 *  Plugin URI: http://prixal.eu/
 *  Description: Better oEmbed video output for WordPress. This plugin prevents Youtube and Vimeo players to block page rendering. It also makes all players responsive.
 *  Version: 1.0
 *  Author: Prixal LLC
 *  Author URI: http://prixal.eu/
 *  License: GPL2+
 *  Text Domain: better-oembed-video
 *
 *
 *  Copyright 2015  Prixal LLC  (email: info@prixal.eu)
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License, version 2, as
 *  published by the Free Software Foundation.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 *
**/

defined('ABSPATH') or die('No script kiddies please!');

class Better_oEmbed_Video
{
    public function __construct()
    {
        if( ! is_admin() )
        {
            add_action('wp_enqueue_scripts', array($this, 'styles'));
            add_action('wp_enqueue_scripts', array($this, 'scripts'));
            add_filter('embed_oembed_html', array($this, 'embed'), 10, 4);
        }
    }

    public function styles()
    {
        wp_enqueue_style('px-better-oembed-video', plugins_url('/css/styles.css', __FILE__) );
    }

    public function scripts()
    {
        wp_enqueue_script('px-better-oembed-video', plugins_url('/js/custom.js' , __FILE__ ), array('jquery'), null, false);
    }

    private function getData($url, $post_id)
    {
        $id = '';
        $result = '';

        // Youtube Thumbnail
        if (preg_match("/^(?:https?:\/\/)?(?:www\.)?youtube\.com\/watch\?(?=.*v=((\w|-){11}))(?:\S+)?$/", $url, $matches))
        {
            $id = $matches[1];
            $result = get_post_meta($post_id, '_px_oembed_' . $id, true);

            if( empty($result) )
            {
                $response = wp_remote_get('http://www.youtube.com/oembed/?url=' . $url . '&format=json');
                $hash = @json_decode($response['body'], true);

                if( is_array($hash) )
                {
                    $data = array(
                        'id' => $id,
                        'hash' => 'px-' . rand(1, 100) . '-' . $id,
                        'thumbnail' => sprintf('<img src="%s" alt="%s" width="%d" height="%d" />', esc_url('http://img.youtube.com/vi/' . $id . '/mqdefault.jpg'), esc_attr($hash['title']), $hash['width'], $hash['height']),
                        'embed_url' => esc_url('https://www.youtube.com/embed/' . $id),
                        'caption'   => $hash['title']
                    );

                    add_post_meta($post_id, '_px_oembed_' . $id, $data, true);

                    return $data;
                }
            }

            return $result;
        }

        // Vimeo Thumbnail
        if (preg_match("/^.+vimeo.com\/(.*\/)?([^#\?]*)/", $url, $matches))
        {
            $id = $matches[count($matches)-1];
            $result = get_post_meta($post_id, '_px_oembed_' . $id, true);

            if( empty($result) )
            {
                $response = wp_remote_get('http://vimeo.com/api/oembed.json?url=' . $url);
                $hash = @json_decode($response['body'], true);

                if( is_array($hash) )
                {
                    $data = array(
                        'id' => $id,
                        'hash' => 'px-' . rand(1, 100) . '-' . $id,
                        'thumbnail' => sprintf('<img src="%s" alt="%s" width="%d" height="%d" />', esc_url($hash['thumbnail_url']), esc_attr($hash['title']), $hash['width'], $hash['height']),
                        'embed_url' => esc_url('http://player.vimeo.com/video/' . $id),
                        'caption'   => $hash['title']
                    );

                    add_post_meta($post_id, '_px_oembed_' . $id, $data, true);

                    return $data;
                }
            }

            return $result;
        }

    }

    public function embed($html, $url, $attr, $post_id)
    {
        if( is_admin() ){
            return $html;
        }

        $data = $this->getData($url, $post_id);

        if( $data )
        {
            $html = $this->template(
                '<figure class="px-oembed">
                    <div class="px-oembed-wrapper" data-href="%(embed_url)s">
                        <div class="embed-responsive-16by9 js-px-oembed top" aria-expanded="false" aria-controls="%(hash)s" role="button">%(thumbnail)s</div>
                        <div class="embed-responsive-16by9 bottom" id="%(hash)s"></div>
                    </div>
                    <figcaption class="wp-caption-text">%(caption)s</figcaption>
                </figure>',

                $data
            );
        }

        return $html;
    }

    private function template($template, $args)
    {
        $names = preg_match_all('/%\((.*?)\)/', $template, $matches, PREG_SET_ORDER);
        $values = array();

        foreach($matches as $match) {
            $values[] = $args[$match[1]];
        }

        $template = preg_replace('/%\((.*?)\)/', '%', $template);
        return vsprintf($template, $values);
    }
}

add_action('init', function() {
    new Better_oEmbed_Video();
});

