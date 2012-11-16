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
    'name' => 'language',
    'post_type' => array('record'),
    'configuration' => array(
        'label' => __('Languages', 'docalist-biblio'),
        'hierarchical' => false,
        'show_ui' => true,
        'query_var' => true,
        'rewrite' => false,
    )
);
