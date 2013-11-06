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

use Docalist\Data\Entity\AbstractEntity;

/**
 * Paramètres d'un type de référence.
 *
 * Un type est essentiellement une liste de champs
 *
 * @property string $name Identifiant du type
 * @property string $label Libellé du type
 * @property string $description Description du type
 * @property FieldSettings[] $fields Liste des champs de ce type
 */
class TypeSettings extends AbstractEntity {
    protected function loadSchema() {
        // @formatter:off
        return array(
            'name' => array(
                'label' => __('Nom', 'docalist-biblio'),
                'description' => __('Doit être un type enregistré (article, book, periodical, website, etc.)', 'docalist-biblio'),
            ),

            'label' => array(
                'label' => __('Libellé', 'docalist-biblio'),
                'description' => __('Libellé utilisé pour désigner ce type', 'docalist-biblio'),
            ),

            'description' => array(
                'label' => __('Description', 'docalist-biblio'),
                'description' => __('Description de ce type de référence, texte d\'intro, etc.', 'docalist-biblio'),
            ),

            'fields' => array(
                'type' => 'Docalist\Biblio\FieldSettings*',
                'label' => __('Grille de saisie', 'docalist-biblio'),
                'description' => __('Liste des champs et paramètres de chaque champ.', 'docalist-biblio'),
            ),
        );
        // @formatter:on
    }
}