<?php
/**
 * This file is part of the 'Docalist Biblio' plugin.
 *
 * Copyright (C) 2012, 2013 Daniel Ménard
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
 * Mentions d'édition
 *
 * @property string $type
 * @property string $value
 */
class Edition extends AbstractEntity {

    protected function loadSchema() {
        // @formatter:off
        return array(
            'type' => array(
                'label' => __("Type de mention", 'docalist-biblio'),
                'description' => __('Exemple : nouvelle édition', 'docalist-biblio'),
            ),
            'value' => array(
                'label' => __('Numéro', 'docalist-biblio'),
                'description' => __('Exemple : "2" pour "seconde édition"', 'docalist-biblio'),
            ),
        );
        // @formatter:on
    }
}