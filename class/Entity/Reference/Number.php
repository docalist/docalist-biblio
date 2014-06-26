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
 * Un numéro propre au document (ISSN, ISBN, Volume, Fascicule...)
 *
 * @property string $type
 * @property string $value
 */
class Number extends AbstractEntity {

    protected function loadSchema() {
        // @formatter:off
        return array(
            'type' => array(
                'label' => __('Type', 'docalist-biblio'),
                'description' => __('Type de numéro', 'docalist-biblio'),
            ),
            'value' => array(
                'label' => __('Numéro', 'docalist-biblio'),
                'description' => __('Numéro dans le format indiqué par le type.', 'docalist-biblio'),
            ),
        );
        // @formatter:on
    }
}