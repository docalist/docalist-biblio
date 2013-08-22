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
namespace Docalist\Biblio\Entity\Settings;

use Docalist\Data\Entity\AbstractEntity;
use DateTime;

/**
 *
 * @property string $name Identifiant de la base
 * @property string $label Libellé de la base
 * @property string $creation Date de création de la base
 */
class Database extends AbstractEntity {
    protected function loadSchema() {
        // @formatter:off
        return array(
            'name' => array(
//                 'label' => 'Identifiant de la base de données',
//                 'description' => 'Ce nom sert a créer le slug de la base et le type des posts créés (dclrefxxx)',
            ),

            'label' => array(
//                 'label' => 'Nom de la base de données',
//                 'description' => 'Nom utilisé dans les menus, dans les écrans, etc.',
            ),

            'creation' => array(
//                 'label' => 'Date/heure de création de la base',
//                 'default' => array($this, 'now')
            ),
        );
        // @formatter:on
    }

    private function now() {
        return new DateTime;
    }
}
