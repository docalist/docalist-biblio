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
 * Un champ d'une base.
 *
 * @property string     $name           Nom du champ
 * @property string     $label          Libellé du champ
 * @property string     $description    Description du champ
 * @property string[]   $table          Nom des tables d'autorité associées au champ
 * @property string     $format         Format d'affichage
 * @property bool       $split          Eclater le champ
 */
class FieldSettings extends AbstractEntity {

    protected function loadSchema() {
        // @formatter:off
        return array(
            'name' => array(
                'label' => __('Nom', 'docalist-biblio'),
                'description' => __("Nom du champ", 'docalist-biblio'),
            ),
            'label' => array(
                'label' => __('Libellé', 'docalist-biblio'),
                'description' => __("Libellé du champ", 'docalist-biblio'),
            ),
            'description' => array(
                'label' => __('Description', 'docalist-biblio'),
                'description' => __("Libellé du champ", 'docalist-biblio'),
            ),
            'table' => array(
                'repeatable' => true,
                'label' => __('Tables', 'docalist-biblio'),
                'description' => __('Table(s) d\'autorité associée(s) au champ (une seule en général, 2 pour organisation : countries et roles)', 'docalist-biblio'),
            ),
            'format' => array(
                'label' => __('Format d\'affichage', 'docalist-biblio'),
                'description' => __('Pour certains champs (ex auteur), choix du format d\'affichage', 'docalist-biblio'),
            ),
            'split' => array(
                'type' => 'bool',
                'label' => __('Split', 'docalist-biblio'),
                'description' => __('Pour les champs "combo" (ex. othertitle ou topics), éclater le champ en plusieurs champs ?', 'docalist-biblio'),
            )
        );
        // @formatter:on
    }
}