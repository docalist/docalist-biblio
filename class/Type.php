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
namespace Docalist\Biblio;

use Docalist\Type\Entity;
use Docalist\Schema\Schema;

use Docalist\Biblio\Type\PostType;
use Docalist\Biblio\Type\PostStatus;
use Docalist\Biblio\Type\PostTitle;
use Docalist\Biblio\Type\PostDate;
use Docalist\Biblio\Type\PostAuthor;
use Docalist\Biblio\Type\PostModified;
use Docalist\Biblio\Type\PostPassword;
use Docalist\Biblio\Type\PostParent;
use Docalist\Biblio\Type\PostSlug;
use Docalist\Biblio\Type\RefNumber;
use Docalist\Biblio\Type\RefType;

use Docalist\Repository\Repository;

use Docalist\Forms\Container;

use Docalist\Search\MappingBuilder;
use Docalist\Tokenizer;
use ReflectionMethod;
use Docalist\Type\MultiField;
use Closure;

use InvalidArgumentException;

/**
 * Type de base des entités qu'on peut stocker dans une base docalist.
 *
 * @property PostType       $posttype   Post Type
 * @property PostStatus     $status     Statut de la fiche
 * @property PostTitle      $posttitle  Titre de la fiche
 * @property PostDate       $creation   Date/heure de création de la fiche
 * @property PostAuthor     $createdBy  Auteur de la fiche
 * @property PostModified   $lastupdate Date/heure de dernière modification
 * @property PostPassword   $password   Mot de passe de la fiche
 * @property PostParent     $parent     Post ID de la fiche parent
 * @property PostSlug       $slug       Slug de la fiche
 * @property RefNumber      $ref        Numéro unique identifiant la fiche
 * @property RefType        $type       Type de fiche
 */
class Type extends Entity
{
    public static function loadSchema()
    {
        return [
            'name' => 'type',
            'label' => __('Type de base (thing ?)', 'docalist-biblio'),
            'description' => __('Type de base docalist-biblio.', 'docalist-biblio'),
            'fields' => [
                'posttype'      => 'Docalist\Biblio\Type\PostType',
                'status'        => 'Docalist\Biblio\Type\PostStatus',
                'posttitle'     => 'Docalist\Biblio\Type\PostTitle',
                'creation'      => 'Docalist\Biblio\Type\PostDate',
                'createdBy'     => 'Docalist\Biblio\Type\PostAuthor',
                'lastupdate'    => 'Docalist\Biblio\Type\PostModified',
                'password'      => 'Docalist\Biblio\Type\PostPassword',
                'parent'        => 'Docalist\Biblio\Type\PostParent',
                'slug'          => 'Docalist\Biblio\Type\PostSlug',
                'ref'           => 'Docalist\Biblio\Type\RefNumber',
                'type'          => 'Docalist\Biblio\Type\RefType',
            ],
        ];
    }

    /**
     * Construit la liste des schémas hérités par la classe en cours.
     *
     * @return array Retourne un tableau de schémas php.
     * - 0 : schéma du type parent du type en cours
     * - 1 : schéma du type gran-parent
     * - etc.
     *
     * Remarque : le schéma de la classe de base (Type) contenant les champs de gestion n'est pas
     * inclus dans le tableau retourné.
     */
    private static function getSchemaHierarchy()
    {
        $schemas=[];

        $class = get_called_class();
        while ($class !== self::class) {
            $schemas[$class] = $class::loadSchema();
            $class = get_parent_class($class);
        }

        $schemas = array_reverse($schemas, true);

        return $schemas;
    }

    /**
     * Fait un "diff" entre les champs de deux types différents.
     *
     * La méthode retourne un tableau contenant la liste des champs du schéma du type $class1
     * qui ne figurent pas dans la liste des champs du schéma du type $class2.
     *
     * @param string $class1
     * @param string $class2
     *
     * @return array
     */
    private static function fieldsDiff($class1, $class2)
    {
        $schema1 = $class1::getDefaultSchema(); /** @var Schema $schema */
        $schema2 = $class2::getDefaultSchema(); /** @var Schema $parent */

        return array_diff_key($schema1->getFields(), $schema2->getFields());
    }

    /**
     * Supprime de la liste des champs passés en paramètre ceux qui sont marqués "unused" dans le schéma indiqué.
     *
     * @param array $fields
     * @param Schema $schema
     *
     * @return array La liste des champs épurée.
     */
    private static function removeUnused(array $fields, Schema $schema)
    {
        foreach (array_keys($fields) as $name) {
            if ($schema->hasField($name) && $schema->getField($name)->unused()) {
                unset($fields[$name]);
            }
        }

        return $fields;
    }

    /**
     * Retourne la liste des types dont hérite la classe en cours.
     *
     * @return Un tableau contenant le nom de classe complet des types dont hérite le type en cours, dans
     * l'ordre d'héritage (parent, grand-parent, etc.)
     *
     * Remarque : la classe de base des types (Type) n'est pas incluse dans le tableau retourné.
     */
    private static function getParentTypes()
    {
        $class = get_called_class();
        $parents = [];
        while ($class !== self::class) {
            $parents[] = $class;
            $class = get_parent_class($class);
        }

        return $parents;
    }

    /**
     * Retourne la grille de base du type.
     *
     * La grille de base est identique au schéma du type, sauf qu'elle ne contient pas les champs qui sont
     * marqués "unused" dans le schéma.
     *
     * @return Schema
     */
    public static function getBaseGrid()
    {
        // C'est simplement le schéma par défaut sans les champs unused
        $schema = static::getDefaultSchema();

        $fields = self::removeUnused($schema->getFields(), $schema);
        foreach ($fields as & $field) {
            $field = $field->value();
        }

        return [
            'name' => 'base',
            'type' => $schema->type(),
            'gridtype' => 'base',
            'label' => $schema->label(),
            'description' => $schema->description(),
            'fields' => $fields,
        ];
    }

    /**
     * Retourne la grille de saisie par défaut du type.
     *
     * @return Schema
     */
    public static function getEditGrid()
    {
        // On part du type en cours
        $class = get_called_class();

        // On construit le formulaire de saisie par défaut en regroupant les champs par niveau de hiérarchie
        $seen = $fields = [];
        $groupNumber = 1;
        while ($class !== self::class) {
            // Si le type en cours n'a pas surchargé loadSchema(), terminé (exemple : SvbType)
            $method = new ReflectionMethod($class, 'loadSchema');
            if ($method->class !== $class) {
                $class = get_parent_class($class);
                continue;
            }

            // Ajoute tous les champs qui sont dans le schéma du type en cours
            $schema = $class::loadSchema();
            $specific = [];
            foreach ($schema['fields'] as $name => $field) {
                if (isset($seen[$name])) {
                    continue;
                }
                $seen[$name] = true;
                if (isset($field['unused']) && $field['unused']) {
                    continue;
                }
                $specific[] = $name;
            }

            // Aucun champ, passe le niveau
            if (empty($specific)) {
                $class = get_parent_class($class);
                continue;
            }

            // Crée un groupe pour ce niveau
            $fields['group' . $groupNumber] = [
                'type' => 'Docalist\Biblio\Type\Group',
                'label' => $schema['label'],
            ];
            ++$groupNumber;

            // Ajoute les champs spécifique à ce niveau
            $fields = array_merge($fields, $specific);

            // Passe au niveau suivant de la hiérarchie des types
            $class = get_parent_class($class);
        }

        // Ajoute les champs de gestion (type et ref) si on ne les a pas encore rencontrés
        $specific = [];
        foreach (['type', 'ref'] as $name) {
            !isset($seen[$name]) && $specific[] = $name;
        }

        if ($specific) {
            $fields['group' . $groupNumber] = [
                'type' => 'Docalist\Biblio\Type\Group',
                'label' => __('Champs de gestion', 'docalist-core'),
                'state' => 'collapsed',
            ];
            $fields = array_merge($fields, $specific);
        }

        // Construit la grille finale
        /*
        $description = sprintf(__(
            "Saisie/modification d'une fiche '%s'.", 'docalist-biblio'),
            static::getDefaultSchema()->label()
        );
        */

        return [
            'name' => 'edit',
            'gridtype' => 'edit',
            'label' => __('Formulaire de saisie', 'docalist-biblio'),
            //'description' => $description,
            'fields' => $fields,
        ];
    }

    public static function getEditGridOLD()
    {
        // On part du schéma du type
        $schema = static::getDefaultSchema();

        // On construit le formulaire de saisie par défaut en regroupant les champs par niveau de hiérarchie
        $fields = [];
        $groupNumber = 1;
        foreach (self::getParentTypes() as $class) {
            // Détermine les champs spécifiques à ce type et supprime ceux qui ont été désactivé
            $parent = get_parent_class($class);
            $specific = self::removeUnused(self::fieldsDiff($class, $parent), $schema);

            // Aucun champ, passe le niveau
            if (empty($specific)) {
                continue;
            }

            // Crée un groupe pour ce niveau
            $level = $class::getDefaultSchema();
            $fields['group' . $groupNumber] = [
                'type' => 'Docalist\Biblio\Type\Group',
                'label' => $level->label(),
            ];
            ++$groupNumber;

            // Ajoute les champs spécifique à ce niveau
            $fields = array_merge($fields, array_keys($specific));

            // Passe au niveau suivant
            $class = $parent;
        }

        // Ajoute un groupe pour les champs de gestion (type et ref uniquement, les autres sont gérés par wordpress)
        $fields['group' . $groupNumber] = [
            'type' => 'Docalist\Biblio\Type\Group',
            'label' => 'Champs de gestion',
            'state' => 'collapsed',
        ];
//         $fields[] = 'posttitle';
        $fields[] = 'type';
        $fields[] = 'ref';
//         $fields = array_merge($fields, array_keys(self::loadSchema()['fields']));

        // Construit la grille finale
        $description = sprintf(__("Saisie/modification d'une fiche '%s'.", 'docalist-biblio'), $schema->label());

        return [
            'name' => 'edit',
            'gridtype' => 'edit',
            'label' => __('Formulaire de saisie', 'docalist-biblio'),
            'description' => $description,
            'fields' => $fields,
        ];
    }

    /**
     * Retourne la grille par défaut pour l'affichage long de ce type.
     *
     * @return Schema
     */
    public static function getContentGrid()
    {
        // On affiche un premier groupe contenant tous les champs (hérités ou non) de
        // l'entité (dans l'ordre) plus le champ ref.
        // Tous les autres champs (gestion) sont dans un groupe 2 qui n'est pas affiché.
        $schema = static::getDefaultSchema();

        // Groupe 1 : champs de l'entité + champ ref
        $fields = [];
        $fields['group1'] = [
            'type' => 'Docalist\Biblio\Type\Group',
            'label' => __('Champs affichés', 'docalist-biblio'),
            'before' => '<dl>',
            'format' => '<dt>%label</dt><dd>%content</dd>',
            'after' => '</dl>'
        ];

        // Ajoute tous les champs de l'entité sauf les champs de gestion
        $all = self::removeUnused(self::fieldsDiff(get_called_class(), self::class), $schema);
        $fields = array_merge($fields, array_keys($all));

        // Ajoute le champ ref
        $fields[] = 'ref';

        // Groupe 2 : champs de gestion
        $fields['group2'] = [
            'type' => 'Docalist\Biblio\Type\Group',
            'label' => __('Champs non affichés', 'docalist-biblio'),
        ];

        // Ajoute tous les champs de gestion (sauf ref, déjà affiché dans groupe 1)
        $management = self::loadSchema()['fields'];
        unset($management['ref']);
        $fields = array_merge($fields, array_keys($management));

        // Construit la grille finale
        $description = sprintf(__("Affichage détaillé d'une fiche '%s'.", 'docalist-biblio'), $schema->label());
        return [
            'name' => 'content',
            'gridtype' => 'display',
            'label' => __('Affichage long', 'docalist-biblio'),
            'description' => $description,
            'fields' => $fields,
        ];
    }

    /**
     * Retourne la grille par défaut pour l'affichage court de ce type.
     *
     * @return Schema
     */
    public static function getExcerptGrid()
    {
        // La grille courte par défaut n'affiche rien (groupe 1 vide) et wordpress affichera uniquement
        // le titre du post. Tous les champs sont dispos dans le groupe 2 qui n'affiche rien.

        // Groupe 1 : champs affichés (aucun)
        $fields = [];
        $fields['group1'] = [
            'type' => 'Docalist\Biblio\Type\Group',
            'label' => __('Champs affichés', 'docalist-biblio'),
            'before' => '<dl>',
            'format' => '<dt>%label</dt><dd>%content</dd>',
            'after' => '</dl>'
        ];

        // Groupe 2 : champs masqués (tous)
        $fields['group2'] = [
            'type' => 'Docalist\Biblio\Type\Group',
            'label' => __('Champs non affichés', 'docalist-biblio'),
        ];

        // Ajoute tous les champs dans le groupe 2
        $schema = static::getDefaultSchema();
        $all = self::removeUnused($schema->getFields(), $schema);
        $fields = array_merge($fields, array_keys($all));

        // Construit la grille finale
        $description = sprintf(__("Affichage court d'une fiche '%s'.", 'docalist-biblio'), $schema->label());
        return [
            'name' => 'excerpt',
            'gridtype' => 'display',
            'label' => __('Affichage court', 'docalist-biblio'),
            'description' => $description,
            'fields' => $fields,
        ];
    }

    /**
     * Initialise les champs de gestion de la notice juste avant qu'elle ne soit enregistrée.
     *
     * Les champs suivants sont initialisés :
     *
     * - ref : on attribue un numéro de référence à la notice si elle est en statut "publish" et qu'elle n'a pas
     *   encore de numéro de référence.
     * - slug : on attribue un slug à la notice si elle a un numéro de référence (ie si elle est publiée).
     * - posttitle : on utilise le champ ref comme postitle par défaut de la notice. Les classes descendantes
     *   définissent un meilleur titre (par exemple, Reference recopie le contenu du champ title).
     *
     * @param Repository $repository Le dépôt dans lequel l'entité va être enregistrée.
     */
    public function beforeSave(Repository $repository)
    {
        parent::beforeSave($repository);

        // Initialise le numéro de réf
        $this->initRefNumber($repository);

        // Initialise le slug du post
        $this->initSlug();

        // Initialise le titre du post
        $this->initPostTitle();
    }

    /**
     * Initialise/vérifie le numéro de référence (champ ref) de la notice lorsque celle-ci est enregistrée.
     *
     * On alloue un numéro de référence à la notice si celle-ci est en statut publish et qu'elle n'a pas encore de
     * numéro. Sinon, on met à jour la séquence si le numéro de la notice est supérieur au dernier numéro alloué.
     *
     * @param Database $database La base dans laquelle la notice va être enregistrée (utilisé pour déterminer le
     * nom de la séquence).
     */
    protected function initRefNumber(Database $database)
    {
        // Si la notice a déjà un numéro de référence, on se contente de synchroniser la séquence
        if (isset($this->ref)) {
            docalist('sequences')->setIfGreater($database->postType(), 'ref', $this->ref->getPhpValue());

            return;
        }

        // Si la notice est en statut publish, on lui attribue un numéro de référence
        if (isset($this->status) && $this->status->getPhpValue() === 'publish') {
            $this->ref = docalist('sequences')->increment($database->postType(), 'ref');
        }
    }

    /**
     * Initialise le slug (post_name) de la notice.
     *
     * Recopie le numéro de réf de la notice dans le champ slug (si la notice a un numéro), ne fait rien sinon.
     */
    protected function initSlug()
    {
        isset($this->ref) && $this->slug = $this->ref->getPhpValue();
    }

    /**
     * Initialise le champ post_title de la notice.
     *
     * Par défaut, le champ post_title est initialisé en recopiant le contenu du champ ref (si la notice a un numéro).
     * Les classes descendantes surchargent cette méthode pour définir un post_title plus adéquat (par exemple, la
     * classe Reference récupère le contenu du champ title).
     */
    protected function initPostTitle()
    {
        isset($this->ref) && $this->posttitle = $this->ref->getPhpValue();
    }

    public function getSettingsForm()
    {
        $name = $this->schema->name();
        $form = new Container($name);

        $form->hidden('name')->addClass('name');

        $form->input('label')
            ->setAttribute('id', $name . '-label')
            ->addClass('label regular-text')
            ->setLabel(__('Libellé', 'docalist-core'))
            ->setDescription(__('Libellé utilisé pour désigner ce type.', 'docalist-core'));

        $form->textarea('description')
            ->setAttribute('id', $name . '-description')
            ->addClass('description large-text autosize')
            ->setAttribute('rows', 1)
            ->setLabel(__('Description', 'docalist-core'))
            ->setDescription(__('Description du type.', 'docalist-core'));

        return $form;
    }


    public function getEditorSettingsForm()
    {
        $name = $this->schema->name();
        $form = new Container($name);

        $form->hidden('name')->addClass('name');

        $form->input('label')
            ->setAttribute('id', $name . '-label')
            ->addClass('label regular-text')
            ->setAttribute('placeholder', $this->schema->label())
            ->setLabel(__('Titre', 'docalist-core'))
            ->setDescription(
                __('Titre du formulaire.', 'docalist-core') .
                ' ' .
                __("Par défaut, c'est le nom du type qui est utilisé.", 'docalist-core')
            );

        $form->textarea('description')
            ->setAttribute('id', $name . '-description')
            ->addClass('description large-text autosize')
            ->setAttribute('rows', 1)
            ->setAttribute('placeholder', $this->schema->description())
            ->setLabel(__('Introduction', 'docalist-core'))
            ->setDescription(
                __("Texte d'introduction qui sera affiché pour présenter le formulaire.", 'docalist-core') .
                ' ' .
                __("Par défaut, c'est la description du type qui est utilisée.", 'docalist-core') .
                ' ' .
                __("Indiquez '-' pour ne rien afficher.", 'docalist-core')
            );

        return $form;
    }

    public function getFormatSettingsForm()
    {
        $name = $this->schema->name();
        $form = new Container($name);

        $form->hidden('name')->addClass('name');

        $form->input('label')
            ->setAttribute('id', $name . '-label')
            ->addClass('label regular-text')
            ->setLabel(__('Nom du format', 'docalist-core'))
            ->setDescription(__("Libellé utilisé pour désigner ce format d'affichage.", 'docalist-core'));

        $form->textarea('description')
            ->setAttribute('id', $name . '-description')
            ->addClass('description large-text autosize')
            ->setAttribute('rows', 1)
            ->setLabel(__('Description', 'docalist-core'))
            ->setDescription(__("Description du format, notes, remarques... (champ de gestion)", 'docalist-core'));

        $form->input('before')
            ->setAttribute('id', $name . '-before')
            ->addClass('before regular-text')
            ->setLabel(__('Texte avant', 'docalist-core'))
            ->setDescription(__('Texte ou code html à afficher avant les données de la fiche.', 'docalist-core'));

        $form->input('after')
            ->setAttribute('id', $name . '-after')
            ->addClass('after regular-text')
            ->setLabel(__('Texte après', 'docalist-core'))
            ->setDescription(__('Texte ou code html à afficher les données de la fiche.', 'docalist-core'));

        return $form;
    }

    protected function getFieldOption(Schema $field, $option, $default = null)
    {
        $value = $field->__call($option);
        if (! is_null($value)) {
            return $value;
        }

        $field = $this->schema->getField($field->name());
        $value = $field->__call($option);
        if (! is_null($value)) {
            return $value;
        }

        return $default;
    }

    public function getFormattedValue($options = null)
    {
        // Détermine les champs à afficher
        // On ne peut pas utiliser getOption() car ça retourne un tableau à plat et non pas un tableau de Schemas
        if (is_null($options)) {
            $fields = $this->schema->getFields();
        } elseif ($options instanceof Schema) {
            $fields = $options->getFields();
        } elseif (is_array($options)) {
            $fields = isset($options['fields']) ? (new Schema($options))->getFields() : $this->schema->getFields();
        } else {
            throw new InvalidArgumentException('Invalid options, expected Schema or array');
        }

        // Initialise les variables pour que cela fonctionne quand la grille ne commence pas par un groupe
        $format = '<p><b>%label</b>: %content</p>'; // Le format du groupe en cours
        $before = null;                             // Propriété 'before' du groupe en cours
        $sep = null;                                // Propriété 'sep' du groupe en cours
        $after = null;                              // Propriété 'after' du groupe en cours
        $hasCap = true;                             // True si l'utilisateur a la cap requise par le groupe en cours
        $items = [];                                // Les items générés par le groupe en cours
        $result = '';                               // Le résultat final qui sera retourné

        // Formatte la notice
        foreach ($fields as $name => $field) {
            // Si c'est un groupe, cela devient le nouveau groupe courant
            if ($field->type() === 'Docalist\Biblio\Type\Group') {
                // Génère le groupe précédent si on a des items
                if ($items) {
                    $result .= $before . implode($sep, $items) . $after;
                    $items = [];
                }

                // Si le groupe requiert une capacité que l'utilisateur n'a pas, inutile d'aller plus loin
                $cap = $field->capability();
                if ($cap && ! current_user_can($cap)) {
                    $hasCap = false;
                    continue;
                }

                // Stocke les propriétés du nouveau groupe en cours
                $hasCap = true;
                $format = $field->format();
                $before = $field->before();
                $sep = $field->sep();
                $after = $field->after();
                continue;
            }

            // Ok, c'est un nouveau champ

            // Si on n'a pas la capacité du groupe en cours, ou si le format ou le champ sont vides, terminé
            if (! $hasCap || empty($format) || ! isset($this->phpValue[$name])) {
                continue;
            }

            // Si le champ requiert une capacité que l'utilisateur n'a pas, terminé
            $cap = $this->getFieldOption($field, 'capability');
            if ($cap && ! current_user_can($cap)) {
                continue;
            }

            // Ok, formatte le contenu du champ
            $content = $this->phpValue[$name]->getFormattedValue($this->getFieldOptions($name, $options));

            // Champ renseigné mais format() n'a rien retourné, passe au champ suivant
            if (empty($content)) {
                continue;
            }

            // format() nous a retourné soit un tableau de champs (vue éclatée), soit une simple chaine
            // avec le contenu formatté du champ. Si c'est une chaine, on le gère comme un tableau en
            // utilisant le libellé du champ.
            if (! is_array($content)) {
                $label = $this->getFieldOption($field, 'label');
                ($label === '-') && $label = '';
                $content = [$label => $content];
            }

            // Stocke le champ (ou les champs en cas de vue éclatée)
            $fieldBefore = $this->getFieldOption($field, 'before');
            $fieldAfter  = $this->getFieldOption($field, 'after');
            foreach ($content as $label => $content) {
                $content = $fieldBefore . $content . $fieldAfter;
                $items[] = strtr($format, ['%label' => $label, '%content' => $content]);
            }
        }

        // Génère le groupe en cours si on a des items
        $items && $result .= $before . implode($sep, $items) . $after;

        // Terminé
        return $result;
    }

    public function buildIndexSettings(array $settings, Database $database)
    {
        // Récupère l'analyseur par défaut pour les champs texte de cette base (dans les settings de la base)
        $defaultAnalyzer = $database->settings()->stemming();

        // Détermine le nom du mapping (nom de la base + nom du type)
        $name = $database->postType() . '-' . $this->schema->name();
        // garder synchro avec DatabaseIndexer::index()

        // Construit le mapping du type
        $mapping = docalist('mapping-builder'); /** @var MappingBuilder $mapping */
        $mapping->reset()->setDefaultAnalyzer($defaultAnalyzer);
        $mapping = $this->buildMapping($mapping);

        // Stocke le mapping dans les settings
        $settings['mappings'][$name] = $mapping->getMapping();

        // Ok
        return $settings;
    }

    /**
     *
     * @param MappingBuilder $mapping
     *
     * @return MappingBuilder
     */
    protected function buildMapping(MappingBuilder $mapping)
    {
        // pour les champs de base, maintenir le même ordre que dans CustomPostTypeIndexer
        $mapping->addField('in')->keyword();
        $mapping->addField('type')->keyword();
        $mapping->addField('type-label')->text()->filter();
        $mapping->addField('status')->keyword();
        $mapping->addField('slug')->text();
        $mapping->addField('createdby')->keyword();
        $mapping->addField('creation')->dateTime();
        $mapping->addField('lastupdate')->dateTime();
        $mapping->addField('posttitle')->text();
        $mapping->addField('posttitle-sort')->keyword();
        $mapping->addField('ref')->integer();

        return $mapping;
    }

    public function map()
    {
        $document = [];

        // In
        // -> initialisé dans DatabaseIndexer::map() car un type ne sait pas dans quelle base il figure

        // Type de réf
        isset($this->type) && $document['type'] = $this->type();

        // Récupère le libellé exact du type de notice, tel qu'indiqué dans la grille
        $document['type-label'] = $this->getSchema()->label() ?: $this->type();

        // Statut
        isset($this->status) && $document['status'] = $this->status();

        // Slug
        isset($this->slug) && $document['slug'] = $this->slug();

        // CreatedBy
        if (isset($this->createdBy)) {
            $user = get_user_by('id', $this->createdBy());
            $document['createdby'] = $user ? $user->user_login : $this->createdBy();
        }

        // Date de création
        isset($this->creation) && $document['creation'] = $this->creation();

        // Date de modification
        isset($this->lastupdate) && $document['lastupdate'] = $this->lastupdate();

        // Titre du post
        if (isset($this->posttitle)) {
            $postTitle = $this->posttitle->getPhpValue();
            $document['posttitle'] = $postTitle;
            $document['posttitle-sort'] = implode(' ', Tokenizer::tokenize($postTitle));
        }

        // Numéro de réf
        isset($this->ref) && $document['ref'] = $this->ref();

        // Ok
        return $document;
    }

    /**
     * Indexation standard d'un champ multifield répétable.
     *
     * Génère un champ field-type.
     *
     * @param array $document Document ElasticSearch à modifier.
     * @param string $field Nom du champ à mapper.
     * @param string|Closure $value Nom du sous-champ contenant la valeur à indexer ('value' par défaut) ou
     * une fonction chargée de retourner le contenu à indexer.
     * Exemple : function(TypedText $item) { return $item->text->getPhpValue(); }
     */
    protected function mapMultiField(array & $document, $field, $value = 'value')
    {
        if (isset($this->$field)) {
            foreach ($this->$field as $item) { /** @var MultiField $item */
                $code = $item->getCategoryCode();
                $key = $code ? ($field . '-' . $code) : $field;
                //$content = $item->$value->getPhpValue();
                $content = is_string($value) ? $item->$value->getPhpValue() : $value($item);
                if (isset($document[$key])) {
                    $content = array_merge((array) $document[$key], (array) $content);
                    $content = array_values(array_unique($content));
                }
                is_array($content) && count($content) === 1 && $content = array_shift($content);
                $document[$key] = $content;
            }
        }
    }

    /**
     * Recherche le code de tous les topics qui sont associés à une table de type 'thesaurus'.
     *
     * @return string[] Un tableau de la forme table => topic (les clés indiquent la table utilisée).
     *
     * @deprecated Utiliser Topics::getThesaurusTopics() à la place.
     */
    protected function getThesaurusTopics()
    {
        trigger_error(__METHOD__ . ' is deprecated, use $this->topic->getThesaurusTopics()');

        return $this->topic->getThesaurusTopics();
    }

    /**
     * Détermine le path complet des termes passés en paramètre dans le thesaurus indiqué.
     *
     * @param array $terms Liste des termes à traduire.
     * @param string $table Nom de la table d'autorité à utiliser (doit être de type 'thesaurus').
     *
     * @return string[] Le path complet des termes.
     */
    protected function getTermsPath(array $terms, $table)
    {
        // Ouvre le thesaurus
        $table = docalist('table-manager')->get($table);

        // Pour chaque terme ajoute le terme parent comme préfixe tant qu'on a un terme parent
        foreach ($terms as & $term) {
            $path = $term;
            while (!empty($term = $table->find('BT', 'code=' . $table->quote($term)))) {
                // find() retourne null si pas de BT ou false si pas de réponse (erreur dans le theso)
                $path = $term . '/' . $path;
            }
            $term = $path;
        }

        // Ok
        return $terms;
    }
}
