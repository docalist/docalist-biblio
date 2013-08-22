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
 * Relation
 *
 * @property string $type
 * @property long[] $ref
 */
class Relation extends AbstractEntity {

    protected function loadSchema() {
        // @formatter:off
        return array(
            'type' => array(
                'label' => __('Type', 'docalist-biblio'),
                'description' => __('Type de relation', 'docalist-biblio'),
            ),
            'ref' => array(
                'type' => 'long*',
                'label' => __('Référence(s)', 'docalist-biblio'),
                'description' => __('Numéros de références des notices liées', 'docalist-biblio'),
            )
        );
        // @formatter:on
    }
}