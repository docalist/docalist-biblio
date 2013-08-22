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
 * Note.
 *
 * @property string $type
 * @property string $content
 *
 */
class Note extends AbstractEntity {

    protected function loadSchema() {
        // @formatter:off
        return array(
            'type' => array(
                'label' => __('Type de note', 'docalist-biblio'),
                'description' => __('Nature de la note', 'docalist-biblio'),
            ),
            'content' => array(
                'label' => __('Contenu', 'docalist-biblio'),
                'description' => __('Texte de la note.', 'docalist-biblio'),
            ),
        );
        // @formatter:on
    }
}