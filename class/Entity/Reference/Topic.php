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

use Docalist\Biblio\Type\Object;

/**
 * Une liste de mots-clés d'un certain type.
 *
 * @property String $type
 * @property String[] $terms
 */
class Topic extends Object {
    static protected function loadSchema() {
        // @formatter:off
        return [
            'fields' => [
                'type' => [
                    'label' => __('Type', 'docalist-biblio'),
    //                 'description' => __('Type des mots-clés (nom du thesaurus ou de la liste)', 'docalist-biblio'),
                ],
                'term' => [ // @todo : au pluriel ?
                    'repeatable' => true,
                    'label' => __('Termes', 'docalist-biblio'),
    //                 'description' => __('Liste des mots-clés.', 'docalist-biblio'),
                ]
            ]
        ];
        // @formatter:on
    }

    public function __toString() {
        return $this->type() . ' : ' . implode(', ', $this->term());
    }
}