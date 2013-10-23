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
 * Une traduction du titre original du document.
 *
 * @property string $language
 * @property string $title
 */
class Translation extends AbstractEntity {

    protected function loadSchema() {
        // @formatter:off
        return array(
            'language' => array(
                'label' => __('Langue', 'docalist-biblio'),
                'description' => __('Langue de la traduction', 'docalist-biblio'),
            ),
            'title' => array(
                'label' => __('Titre', 'docalist-biblio'),
                'description' => __('Titre traduit', 'docalist-biblio'),
            ),
        );
        // @formatter:on
    }
}