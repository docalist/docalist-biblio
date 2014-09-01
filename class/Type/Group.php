<?php
/**
 * This file is part of a "Docalist Core" plugin.
 *
 * Copyright (C) 2012-2014 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 *
 * @package     Docalist
 * @subpackage  Core
 * @author      Daniel Ménard <daniel.menard@laposte.net>
 * @version     SVN: $Id$
 */
namespace Docalist\Biblio\Type;

use Docalist\Forms\Fragment;
use Docalist\Type\Exception\InvalidTypeException;

/**
 * Group
 *
 * Pseudo type de champ utilisé pour gérer les groupes de champs.
 */
class Group extends \Docalist\Type\Any implements BiblioField {
    use BiblioFieldTrait;

    public function assign($value) {
        if (! is_null($value)) {
            throw new InvalidTypeException('Un groupe ne peut pas avoir de valeur.');
        }
    }

    public function settingsForm() {
        $name = $this->schema->name();
        $form = new Fragment($name);

        if ($this->schema->newgroup()) { // définit dans la vue fields.php
            $form->hidden('type');
            // pour un nouveau groupe, il faut que le groupe soit créé avec le bon type
            // pour un groupe existant, inutile : on a déjà le bon type dans le schéma
        }

        $form->input('name')
             ->attribute('class', 'name')
             ->label(__('Nom du groupe', 'docalist-biblio'))
             ->description(__("Le nom du groupe doit être unique (ni un nom de champ, ni le nom d'un autre groupe).", 'docalist-biblio'));

        $form->input('label')
             ->attribute('id', $name . '-label')
             ->attribute('class', 'label regular-text')
             ->label(__('Titre du groupe', 'docalist-biblio'))
             ->description(__("C'est le titre qui sera affiché dans la barre de titre du groupe et dans les options de l'écran de saisie. Valeur par défaut : type de la notice", 'docalist-biblio'));

        $form->textarea('description')
             ->attribute('id', $name . '-description')
             ->attribute('class', 'description large-text')
             ->attribute('rows', 2)
             ->label(__("Texte d'introduction", 'docalist-biblio'))
             ->description(__("Ce texte sera affiché entre la barre de titre et le premier champ du groupe. Vous pouvez utiliser cette zone pour donner des consignes de saisie ou toute autre information utile aux utilisateurs.", 'docalist-biblio'));

        $form->select('state')
            ->attribute('id', $name . '-state')
            ->attribute('class', 'state')
            ->label(__("Etat initial du groupe", 'docalist-biblio'))
            ->description(__("Dans l'écran de saisie, chaque utilisateur peut choisir comment afficher chacun des groupes : il peut replier ou déplier un groupe ou utiliser les options de l'écran de saisie pour masquer ou afficher certains groupes. Ce paramètre indique comment le groupe sera affiché initiallement (pour un nouvel utilisateur).", 'docalist-biblio'))
            ->options([
                '' => __('Ouvert', 'docalist-biblio'),
                'collapsed' => __('Replié', 'docalist-biblio'),
                'hidden' => __('Masqué', 'docalist-biblio'),
            ])
            ->firstOption(false);

        $form->button(__('Supprimer ce groupe', 'docalist-biblio'))
             ->attribute('class', 'delete-group button right');

        return $form;
    }
}