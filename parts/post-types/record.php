<?php
/**
 * This file is part of a "Docalist Biblio" plugin.
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 *
 * @package     Docalist
 * @subpackage  Biblio
 * @author      Daniel Ménard <daniel.menard@laposte.net>
 * @version     SVN: $Id$
 */

namespace Docalist;

return array(
    'name' => 'record',
    'labels' => array(
        'name' => __('Bibliographic records', 'docalist-biblio'), //addoption
        'singular_name' => __('New record', 'docalist-biblio'), // dans le menu
        // nouveau de la admin bar
        'add_new' => __('New record', 'docalist-biblio'),
        'all_items' => __('All records', 'docalist-biblio'),
        'add_new_item' => __('New bibliographic record', 'docalist-biblio'),
        'edit_item' => __('Edit bibliographic record', 'docalist-biblio'),
        'new_item' => __('Add New Notice', 'docalist-biblio'),
        'view_item' => __('View record', 'docalist-biblio'), // dans admin bar
        'search_items' => __('Search records', 'docalist-biblio'),
        'not_found' => __('No bibliographic records found.', 'docalist-biblio'),
        'not_found_in_trash' => __('No bibliographic records found in trash.', 'docalist-biblio'),
    ),
    'public' => true,
    'rewrite' => array(
        'slug' => 'base', // addoption
        'with_front' => false,
    ),
    'capability_type' => 'post',
    'supports' => array(
        'title',
        'thumbnail'
    ),
    'has_archive' => true,
);
