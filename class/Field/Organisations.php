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
 */
namespace Docalist\Biblio\Field;

use Docalist\Biblio\Type\Repeatable;
use Docalist\Forms\Table;

/**
 * Une collection d'organismes.
 */
class Organisations extends Repeatable {
    static protected $type = 'Organisation';

    public function editForm() {
        $field = new Table($this->schema->name());
        $field->input('name')->addClass('organisation-name');
        $field->input('acronym')->addClass('organisation-acronym');
        $field->input('city')->addClass('organisation-city');
        $field->TableLookup('country', $this->schema->table())
              ->addClass('organisation-country');
        $field->TableLookup('role', $this->schema->table2())
              ->addClass('organisation-role');

        return $field;
    }

    public function baseSettings() {
        $form = parent::baseSettings();
        $form = $this->addTableSelect($form, 'countries', __("Table des pays", 'docalist-biblio'));
        return $this->addTable2Select($form, 'roles', __("Table des rôles", 'docalist-biblio'));
    }

    public function editSettings() {
        $form = parent::editSettings();
        $form = $this->addTableSelect($form, 'countries', __("Table des pays", 'docalist-biblio'), true);
        return $this->addTable2Select($form, 'roles', __("Table des rôles", 'docalist-biblio'), true);
    }

    public function displaySettings() {
        $form = parent::displaySettings();
        $form = $this->addTableSelect($form, 'countries', __("Table des pays", 'docalist-biblio'), true);
        return $this->addTable2Select($form, 'roles', __("Table des rôles", 'docalist-biblio'), true);
    }
}