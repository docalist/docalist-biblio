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

namespace Docalist\Biblio\Taxonomy;
use Docalist\Taxonomy;

/**
 * Taxonomie "Pays".
 */
class Country extends Taxonomy {
    /**
     * @inheritdoc
     */
    protected $id = 'dclcountry';

    /**
     * @inheritdoc
     */
    protected $postTypes = array('dclref');

    /**
     * @inheritdoc
     */
    protected function options() {
        return array(
            'label' => __('Pays', 'docalist-biblio'),
            'hierarchical' => false,
            'show_ui' => true,
            'query_var' => true,
            'rewrite' => false,
        );
    }

}
