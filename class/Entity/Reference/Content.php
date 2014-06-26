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
 * Content.
 *
 * @property string $type
 * @property string $value
 */
class Content extends AbstractEntity {

    protected function loadSchema() {
        // @formatter:off
        return array(
            'type' => array(
                'label' => __('Type', 'docalist-biblio'),
//                 'description' => __('Nature de la note', 'docalist-biblio'),
            ),
            'value' => array(
                'label' => __('Contenu', 'docalist-biblio'),
                'description' => __('Résumé, notes et remarques sur le contenu.', 'docalist-biblio'),
            ),
        );
        // @formatter:on
    }
}