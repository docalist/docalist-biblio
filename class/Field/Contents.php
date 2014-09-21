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
namespace Docalist\Biblio\Field;

use Docalist\Biblio\Type\Repeatable;
use Docalist\Forms\Table;

/**
 * Une liste de contenus.
 */
class Contents extends Repeatable {
    static protected $type = 'Content';

    public function editForm() {
        $field = new Table($this->schema->name());
        $field->TableLookup('type', $this->schema->table())
              ->addClass('content-type');
        $field->textarea('value')->addClass('content-value');

        return $field;
    }

    public function baseSettings() {
        $form = parent::baseSettings();
        return $this->addTableSelect($form, 'content', __("Table des types de contenus", 'docalist-biblio'));
    }

    public function editSettings() {
        $form = parent::editSettings();
        return $this->addTableSelect($form, 'content', __("Table des types de contenus", 'docalist-biblio'), true);
    }

    public function displaySettings() {
        $name = $this->schema->name();

        $form = parent::displaySettings();

        $form->input('newlines')
             ->attribute('id', $name . '-newlines')
             ->attribute('class', 'newlines regular-text')
             ->label(__("Remplacer les CR/LF par", 'docalist-biblio'))
             ->description(__("Indiquez par quoi remplacer les retours chariots (par exemple : <code>&lt;br/&gt;</code>), ou videz le champ pour les laisser inchangés.", 'docalist-biblio'));

        $form->input('maxlen')
             ->attribute('id', $name . '-maxlen')
             ->attribute('class', 'maxlen small-text')
             ->label(__("Couper à x caractères", 'docalist-biblio'))
             ->description(__("Coupe les textes trop longs pour qu'ils ne dépassent pas la limite indiquée, ajoute une ellipse (...) si le texte a été tronqué.", 'docalist-biblio'));

        return $this->addTableSelect($form, 'content', __("Table des types de contenus", 'docalist-biblio'), true);
    }
}