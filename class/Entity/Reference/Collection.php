<?php
/**
 * This file is part of the 'Docalist Biblio' plugin.
 *
 * Copyright (C) 2012-2014 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 *
 * @package     Docalist
 * @subpackage  Biblio
 * @author      Daniel Ménard <daniel.menard@laposte.net>
 * @version     $Id$
 */
namespace Docalist\Biblio\Entity\Reference;

use Docalist\Data\Entity\AbstractEntity;

/**
 * Collection et numéro au sein de la collection.
 *
 * @property string $name
 * @property string $number
 */
class Collection extends AbstractEntity {

    protected function loadSchema() {
        // @formatter:off
        return array(
            'name' => array(
                'label' => __("Nom", 'docalist-biblio'),
                'description' => __('Nom de la collection ou de la sous-collection.', 'docalist-biblio'),
            ),
            'number' => array(
                'label' => __('Numéro', 'docalist-biblio'),
                'description' => __('Numéro au sein de la collection ou de la sous-collection.', 'docalist-biblio'),
            ),
        );
        // @formatter:on
    }
}