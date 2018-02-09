<?php
/**
 * This file is part of the 'Docalist Biblio' plugin.
 *
 * Copyright (C) 2012-2017 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 */
namespace Docalist\Biblio\Field;

use Docalist\Type\TypedText;
use Docalist\Type\TableEntry;
use Docalist\Type\Text;
use Docalist\Type\Any;

/**
 * Champ "translation" : traductions dans d'autres langues du titre original du document catalogué.
 *
 * Ce champ répétable permet de traduire le titre original du document dans d'autres langues. Par exemple
 * si le titre d'origine est en anglais, ça permet d'indiquer la traduction en français et en anglais.
 *
 * Chaque occurence du champ translation comporte deux sous-champs :
 * - `type` : langue de la traduction,
 * - `value` : titre traduit.
 *
 * Le sous-champ type est associé à une table d'autorité qui indique les langues possibles (table des langues de
 * l'union européenne par défaut).
 *
 * @property TableEntry $type   Langue de la traduction.
 * @property Text       $value  Titre traduit.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class TranslationField extends TypedText
{
    public static function loadSchema()
    {
        return [
            'name' => 'translation',
            'repeatable' => true,
            'label' => __('Titre traduit', 'docalist-biblio'),
            'description' => __('Traductions du titre original du document.', 'docalist-biblio'),
            'fields' => [
                'type' => [ // s'appellait "language" avant
                    'label' => __('Langue', 'docalist-biblio'),
                    'table' => 'table:ISO-639-2_alpha3_EU_fr',
                ],
                'value' => [ // s'appellait "title" avant
                    'label' => __('Traduction', 'docalist-biblio'),
                ]
            ]
        ];
    }

    /**
     * Compatibilité : le type Translation est devenu un TypedText standard.
     *
     * Le nom des sous-champs a changé (06/17) : avant, le champ 'type' s'appellait 'language' et le champ 'value'
     * s'appellait 'title'.
     *
     * Pour gérer la compatibilité ascendante (chargement d'une notice contenant les anciens noms de champs), on
     * surcharge la méthode assign() héritée de Composite et on traduit à la volée les noms des sous-champs.
     *
     * Cette méthode pourra être supprimée lorsque les bases Prisme auront été converties.
     *
     * {@inheritDoc}
     */
    public function assign($value)
    {
        ($value instanceof Any) && $value = $value->getPhpValue();

        if (is_array($value)) {
            foreach (['language' => 'type', 'title' => 'value'] as $oldName => $newName) {
                if (isset($value[$oldName])) {
                    $value[$newName] = $value[$oldName];
                    unset($value[$oldName]);
                }
            }
        }

        return parent::assign($value);
    }
}
