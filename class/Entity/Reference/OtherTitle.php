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
namespace Docalist\Biblio\Entity\Reference;

use Docalist\Data\Entity\AbstractEntity;

/**
 * Autre titre.
 *
 * @property string $type
 * @property string $title
 */
class OtherTitle extends AbstractEntity {

    protected function loadSchema() {
        // @formatter:off
        return array(
            'type' => array(
                'label' => __('Type de titre', 'docalist-biblio'),
            ),
            'title' => array(
                'label' => __('Titre', 'docalist-biblio'),
            ),
        );
        // @formatter:on
    }
}