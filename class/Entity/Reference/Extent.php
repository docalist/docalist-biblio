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
 * Etendue du document : pagination, nombre de pages, durée en minutes, etc.
 *
 * @property string $type
 * @property string $value
 */
class Extent extends AbstractEntity {

    protected function loadSchema() {
        // @formatter:off
        return array(
            'type' => array(
                'label' => __("Type", 'docalist-biblio'),
                'description' => __("Type d'étendue", 'docalist-biblio'),
            ),
            'value' => array(
                'label' => __('Valeur', 'docalist-biblio'),
                'description' => __('Etendue dans le format indiqué par le type (n° de page, nb de pages, durée, etc.)', 'docalist-biblio'),
            ),
        );
        // @formatter:on
    }
}