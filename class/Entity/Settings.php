<?php
/**
 * This file is part of a "Docalist Biblio" plugin.
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 *
 * @package Docalist
 * @subpackage Biblio
 * @author Daniel Ménard <daniel.menard@laposte.net>
 * @version SVN: $Id$
 */
namespace Docalist\Biblio\Entity;

use Docalist\Data\Entity\AbstractSettingsEntity;
use Docalist\RegistrableInterface;
use Docalist\RegistrableTrait;
/**
 * Options de configuration du plugin de gestion de notices bibliographiques.
 *
 * @property Docalist\Biblio\Entity\Settings\Database[] $databases Liste des bases.
 */
class Settings extends AbstractSettingsEntity /* implements RegistrableInterface */
{
    /* use RegistrableTrait; */


    protected function loadSchema() {
        return array(
            'databases' => array(
                'type' => 'Settings\DatabaseSettings*',
                'label' => __('Liste des bases de données documentaires', 'docalist-biblio'),
                'default' => array(
                    array(
                        'name' => '',
                        'label' => 'Base Prisme',
                        'creation' => 'datecreat',
                    ),
                    array(
                        'name' => 'bdsp',
                        'label' => 'Base BDSP',
                        'creation' => 'datecreat',
                    ),
                    array(
                        'name' => 'prisme-travail',
                        'label' => 'Base de travail Prisme',
                        'creation' => 'datecreat',
                    ),
                ),
            ),
        );
    }
}
