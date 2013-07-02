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
use Docalist\AbstractTaxonomy;

/**
 * Taxonomie "Types de documents".
 */
class RefType extends AbstractTaxonomy {
    /**
     * @inheritdoc
     */
    protected $id = 'dclreftype';

    /**
     * @inheritdoc
     */
    protected $postTypes = array('dclref');

    /**
     * @inheritdoc
     */
    protected function options() {
        return array(
            'label' => __('Types de documents', 'docalist-biblio'),
/*
            'labels' => array(
                'name' => 'Types de documents (pluriel)',
                'singular_name' => 'Type de document (singulier)',
            ),
*/
            'hierarchical' => false,
            'show_ui' => false,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => false,
        );
    }

}
