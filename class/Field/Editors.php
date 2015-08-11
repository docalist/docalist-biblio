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
 * Une collection d'éditeurs.
 */
class Editors extends Repeatable {
    static protected $type = 'Editor';

    public function editForm() {
        $field = new Table($this->schema->name());
        $field->input('name')->addClass('editor-name');
        $field->input('city')->addClass('editor-city');
        $field->TableLookup('country', $this->schema->table())
              ->addClass('editor-country');
        $field->TableLookup('role', $this->schema->table2())
              ->addClass('editor-role');

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