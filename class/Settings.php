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
namespace Docalist\Biblio;

use Docalist\Data\Entity\AbstractSettingsEntity;

/**
 * Config de Docalist Biblio.
 *
 * @property Docalist\Biblio\DatabaseSettings[] $databases Liste des bases.
 */
class Settings extends AbstractSettingsEntity
{
    protected function loadSchema() {
        return array(
            'databases' => array(
                'type' => 'DatabaseSettings*',
                'key' => 'name',
                'label' => __('Liste des bases de données documentaires', 'docalist-biblio'),
            ),
        );
    }
}