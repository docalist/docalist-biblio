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
 * Une collection de relations.
 */
class Relations extends Repeatable {
    static protected $type = 'Docalist\Biblio\Field\Relation';

    public function editForm() {
        $field = new Table($this->schema->name());
        $field->TableLookup('type', $this->schema->table())
              ->addClass('relations-type');
        $field->input('ref')->addClass('relations-ref')->repeatable(true);
        /*
         * Remarque : on ne devrait pas fixer en dur repeatable=true pour
         * le sous-champ ref.
         * Mais si on ne le fait pas, le champ n'est pas répétable quand on
         * saisit une valeur par défaut pour relations dans les grilles de
         * saisie.
         * Pb de binding ?
         */
        return $field;
    }

    public function baseSettings() {
        $form = parent::baseSettings();
        return $this->addTableSelect($form, 'relations', __('Table des types de relations', 'docalist-biblio'));
    }

    public function editSettings() {
        $form = parent::editSettings();
        return $this->addTableSelect($form, 'relations', __('Table des types de relations', 'docalist-biblio'), true);
    }

    public function displaySettings() {
        $form = parent::displaySettings();
        return $this->addTableSelect($form, 'relations', __('Table des types de relations', 'docalist-biblio'), true);
    }
}