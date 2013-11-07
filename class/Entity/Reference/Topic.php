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
 * Une liste de mots-clés.
 *
 * @property string $type
 * @property string[] $terms
 */
class Topic extends AbstractEntity {

    protected function loadSchema() {
        // @formatter:off
        return array(
            'type' => array(
                'label' => __('Type', 'docalist-biblio'),
//                 'description' => __('Type des mots-clés (nom du thesaurus ou de la liste)', 'docalist-biblio'),
            ),
            'term' => array( // @todo : au pluriel ?
                'repeatable' => true,
                'label' => __('Termes', 'docalist-biblio'),
//                 'description' => __('Liste des mots-clés.', 'docalist-biblio'),
            ),
        );
        // @formatter:on
    }

    public function __toString() {
        return $this->type . ' : ' . implode(', ', $this->term->toArray());
    }
}