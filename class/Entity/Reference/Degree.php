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
 * Diplôme, niveau et intitulé.
 *
 * @property string $level
 * @property string $title
 */
class Degree extends AbstractEntity {

    protected function loadSchema() {
        // @formatter:off
        return array(
            'level' => array(
                'label' => __('Niveau', 'docalist-biblio'),
                'description' => __("Niveau ou grade universitaire auquel donne droit ce diplôme (ex. licence)", 'docalist-biblio'),
            ),
            'title' => array(
                'label' => __('Titre du diplôme', 'docalist-biblio'),
                'description' => __("Intitulé du diplôme ou de la spécialité professionnelle.", 'docalist-biblio'),
            ),
        );
        // @formatter:on
    }
}