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

namespace Docalist\Biblio;
 
return array(
    'name' => 'record',
    'labels' => array(
        'name' => $this->option('Biblio.menu'),
        'singular_name' => $this->option('Biblio.name'),
        'add_new' => $this->option('Biblio.new'),
        'add_new_item' => $this->option('Biblio.new'),
        'edit_item' => $this->option('Biblio.edit'),
        'new_item' => $this->option('Biblio.new'),
        'view_item' => $this->option('Biblio.view'),
        'search_items' => $this->option('Biblio.search'),
        'not_found' => $this->option('Biblio.notfound'),
        'not_found_in_trash' => $this->option('Biblio.notfound'),
        'all_items' => $this->option('Biblio.all'),
        'menu_name' => $this->option('Biblio.menu'),
        'name_admin_bar' => $this->option('Biblio.name'),
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
