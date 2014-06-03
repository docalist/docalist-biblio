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
 * Date.
 *
 * @property string $type
 * @property string $date
 */
class Date extends AbstractEntity {

    protected function loadSchema() {
        // @formatter:off
        return array(
            'type' => array(
                'label' => __('Type de date', 'docalist-biblio'),
//                 'description' => __('Date', 'docalist-biblio'),
            ),
            'date' => array(
                'label' => __('Date', 'docalist-biblio'),
            ),
        );
        // @formatter:on
    }
}