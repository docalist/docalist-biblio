<?php
/**
 * This file is part of the 'Docalist Biblio' plugin.
 *
 * Copyright (C) 2012-2015 Daniel Ménard
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
 * Une collection d'auteurs physiques.
 */
class Authors extends Repeatable {
    static protected $type = 'Docalist\Biblio\Field\Author';

    public function editForm() {
        $field = new Table($this->schema->name());

        $field->input('name')->addClass('author-name');
        $field->input('firstname')->addClass('author-firstname');
        $field->TableLookup('role', $this->schema->table())
              ->addClass('author-role');

        return $field;
    }

    public function baseSettings() {
        $form = parent::baseSettings();
        return $this->addTableSelect($form, 'roles', __("Table des rôles", 'docalist-biblio'));
    }

    public function editSettings() {
        $form = parent::editSettings();
        return $this->addTableSelect($form, 'roles', __("Table des rôles", 'docalist-biblio'), true);
    }

    public function displaySettings() {
        $form = parent::displaySettings();
        return $this->addTableSelect($form, 'roles', __("Table des rôles", 'docalist-biblio'), true);
    }
}