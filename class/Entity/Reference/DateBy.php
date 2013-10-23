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
 * Date de création ou de dernière modification et agent utilisateur.
 *
 * @property string $date
 * @property string $by
 */
class DateBy extends AbstractEntity {

    protected function loadSchema() {
        // @formatter:off
        return array(
            'date' => array(
                'label' => __('Le', 'docalist-biblio'),
                'description' => __('Date', 'docalist-biblio'),
            ),
            'by' => array(
                'label' => __('Par', 'docalist-biblio'),
                'description' => __('Utilisateur', 'docalist-biblio'),
            ),
        );
        // @formatter:on
    }
}