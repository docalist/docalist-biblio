<?php
/**
 * This file is part of the 'Docalist Biblio' plugin.
 *
 * Copyright (C) 2012-2017 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 *
 * @package     Docalist
 * @subpackage  Biblio
 * @author      Daniel Ménard <daniel.menard@laposte.net>
 */
namespace Docalist\Biblio\Type;

use Docalist\Type\Exception\InvalidTypeException;
use Docalist\Type\Any;
use Docalist\Forms\Container;

/**
 * Group.
 *
 * Pseudo type de champ utilisé pour gérer les groupes de champs.
 */
class Group extends Any
{
    public function assign($value)
    {
        if (! is_null($value)) {
            throw new InvalidTypeException('A group can not have a value.');
        }
    }

    // pas de baseSettings() pour un groupe : pas de groupes dans une grille de base
    public function getEditorSettingsForm()
    {
        $name = $this->schema->name();
        $form = new Container($name);

        $form->hidden('type')->bind('Docalist\Biblio\Type\Group');

        $form->input('label')
             ->setAttribute('id', $name . '-label')
             ->addClass('label regular-text')
             ->setLabel(__('Libellé de la boite', 'docalist-biblio'))
             ->setDescription(__(
                 "Libellé qui sera affiché dans la barre de titre du groupe (metabox)
                 et dans les options de l'écran de saisie.",
                 'docalist-biblio'
             ));

        $form->input('capability')
            ->setAttribute('id', $name . '-capability')
            ->addClass('capability regular-text')
            ->setLabel(__('Droit requis', 'docalist-biblio'))
            ->setDescription(__(
                "Capacité WordPress requise pour afficher ce groupe de champs.
                Ce groupe (et tous les champs qu'il contient) sera masqué si l'utilisateur ne dispose
                pas du droit indiqué. Si vous laissez vide, aucun test ne sera effectué.",
                'docalist-biblio'
            ));

        $form->textarea('description')
             ->setAttribute('id', $name . '-description')
             ->addClass('description large-text autosize')
             ->setAttribute('rows', 1)
             ->setLabel(__('Introduction', 'docalist-biblio'))
             ->setDescription(__(
                 "Texte d'introduction affiché entre la barre de titre et le premier champ du groupe.
                 Vous pouvez utiliser cette zone pour donner des consignes de saisie ou toute autre
                 information utile aux utilisateurs.",
                 'docalist-biblio'
             ));

        $form->select('state')
            ->setAttribute('id', $name . '-state')
            ->addClass('state')
            ->setLabel(__('Etat initial', 'docalist-biblio'))
            ->setDescription(__(
                "Dans l'écran de saisie, chaque utilisateur peut choisir comment afficher chacun des groupes :
                il peut replier ou déplier un groupe ou utiliser les options de l'écran de saisie pour masquer
                ou afficher certains groupes.
                Ce paramètre indique comment le groupe sera affiché initiallement (pour un nouvel utilisateur).",
                'docalist-biblio'
            ))
            ->setOptions([
                '' => __('Ouvert', 'docalist-biblio'),
                'collapsed' => __('Replié', 'docalist-biblio'),
                'hidden' => __('Masqué', 'docalist-biblio'),
            ])
            ->setFirstOption(false);

        $form->button(__('Supprimer ce groupe', 'docalist-biblio'))
             ->addClass('delete-group button button-secondary button-small right');

        return $form;
    }


    public function getFormatSettingsForm()
    {
        $name = $this->schema->name();
        $form = new Container($name);

        $form->input('label')
             ->addClass('label regular-text')
             ->setLabel(__('Nom du groupe', 'docalist-biblio'))
             ->setDescription(__(
                 'Ce texte ne sera pas affiché, il sert uniquement à distinguer les différents groupes de champs.',
                 'docalist-biblio'
             ));

        $form->input('capability')
            ->setAttribute('id', $name . '-capability')
            ->addClass('capability regular-text')
            ->setLabel(__('Droit requis', 'docalist-biblio'))
            ->setDescription(__(
                "Droit requis pour afficher ce groupe de champs. Ce groupe (et tous les champs qu'il contient)
                sera masqué si l'utilisateur ne dispose pas du droit indiqué. Si vous laissez vide, aucun test
                ne sera effectué.",
                'docalist-biblio'
            ));

        $form->textarea('before')
             ->addClass('before code large-text')
             ->setAttribute('rows', 2)
             ->setLabel(__('Avant la liste des champs', 'docalist-biblio'))
             ->setDescription(__(
                 'Code html à insérer avant la liste des champs de ce groupe.',
                 'docalist-biblio'
             ));

        $form->textarea('format')
             ->addClass('format code large-text')
             ->setAttribute('rows', 2)
             ->setLabel(__('Format des champs', 'docalist-biblio'))
             ->setDescription(sprintf(
                 __(
                     "Code html utilisé comme modèle pour afficher chacun des champs de ce groupe.
                     Utilisez %s pour désigner le libellé du champ et %s pour désigner son contenu.
                     Exemple : %s. Laissez vide pour créer un groupe qui n'affichera aucun champ.",
                     'docalist-biblio'
                 ),
                 '<code>%label</code>',
                 '<code>%content</code>',
                 '<code>' . htmlspecialchars('<p> <b>%label</b> : %content </p>') . '</code>'
             ));

        $form->textarea('after')
             ->addClass('after code large-text')
             ->setAttribute('rows', 2)
             ->setLabel(__('Après la liste des champs', 'docalist-biblio'))
             ->setDescription(__(
                 'Code html à insérer après la liste des champs de ce groupe.',
                 'docalist-biblio'
             ));

        $form->textarea('sep')
             ->addClass('sep code large-text')
             ->setAttribute('rows', 2)
             ->setLabel(__('Entre les champs', 'docalist-biblio'))
             ->setDescription(__(
                 'Code html à insérer entre les champs de ce groupe.',
                 'docalist-biblio'
             ));

        $form->button(__('Supprimer ce groupe', 'docalist-biblio'))
             ->addClass('delete-group button button-secondary button-small right');

        return $form;
    }
}
