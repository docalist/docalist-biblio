<?php
/**
 * This file is part of a "Docalist Core" plugin.
 *
 * Copyright (C) 2012-2015 Daniel Ménard
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

    // pas de baseSettings() pour un groupe : pas de groupes dans une grille de base

    public function editSettings() {
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

        $form->input('capability')
            ->attribute('id', $name . '-capability')
            ->attribute('class', 'capability regular-text')
            ->label(__('Droit requis', 'docalist-biblio'))
            ->description(__("Droit requis pour afficher ce groupe de champs. Ce groupe (et tous les champs qu'il contient) sera masqué si l'utilisateur ne dispose pas du droit indiqué. Si vous laissez vide, aucun test ne sera effectué.", 'docalist-biblio'));

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


    public function displaySettings() {
        $name = $this->schema->name();
        $form = new Fragment($name);
        $form->hidden('name')
             ->attribute('class', 'name');
        $form->input('label')
             ->attribute('class', 'label regular-text')
             ->label(__('Nom du groupe', 'docalist-biblio'))
             ->description(__("Ce texte n'est pas affiché dans les notices, il sert uniquement à distinguer les différents groupes.", 'docalist-biblio'));

        $form->input('capability')
            ->attribute('id', $name . '-capability')
            ->attribute('class', 'capability regular-text')
            ->label(__('Droit requis', 'docalist-biblio'))
            ->description(__("Droit requis pour afficher ce groupe de champs. Ce groupe (et tous les champs qu'il contient) sera masqué si l'utilisateur ne dispose pas du droit indiqué. Si vous laissez vide, aucun test ne sera effectué.", 'docalist-biblio'));

/*
        $form->textarea('setup')
             ->attribute('class', 'setup code large-text')
             ->attribute('rows', 3)
             ->label(__('Initialisation', 'docalist-biblio'))
             ->description(__("Code html à insérer la première fois qu'une notice de ce type est affichée (émis une seule fois dans la page)", 'docalist-biblio'));
*/

        $form->textarea('before')
             ->attribute('class', 'before code large-text')
             ->attribute('rows', 3)
             ->label(__('Avant la liste des champs', 'docalist-biblio'))
             ->description(__('Code html à insérer avant la liste des champs de ce groupe.', 'docalist-biblio'));
        $form->textarea('format')
             ->attribute('class', 'format code large-text')
             ->attribute('rows', 4)
             ->label(__('Format des champs', 'docalist-biblio'))
             ->description(__("Code html utilisé comme modèle pour afficher chacun des champs de ce groupe. Utilisez <code>%label</code> pour désigner le libellé et <code>%content</code> pour désigner le contenu.<br />Exemple : <code>&lt;p&gt; &lt;b&gt;%label : &lt;/b&gt;%content&lt;/p&gt;</code>. Laissez vide pour créer un groupe qui n'affichera aucun champ.", 'docalist-biblio'));
        $form->textarea('after')
             ->attribute('class', 'after code large-text')
             ->attribute('rows', 3)
             ->label(__('Après la liste des champs', 'docalist-biblio'))
             ->description(__('Code html à insérer après la liste des champs de ce groupe.', 'docalist-biblio'));
        $form->textarea('sep')
             ->attribute('class', 'sep code large-text')
             ->attribute('rows', 3)
             ->label(__('Entre les champs', 'docalist-biblio'))
             ->description(__('Code html à insérer entre les champs de ce groupe.', 'docalist-biblio'));

        $form->button(__('Supprimer ce groupe', 'docalist-biblio'))
             ->attribute('class', 'delete-group button right');

        return $form;
    }
}