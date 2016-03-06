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
namespace Docalist\Biblio\Pages;

use Docalist\Biblio\Database;
use Docalist\Biblio\Type;
use WP_Post;
use WP_Screen;
use Exception;

use Docalist\Http\ViewResponse;
use Docalist\Forms\Container;

/**
 * Gère la page "création/modification d'une notice" d'une base documentaire.
 */
class EditReference {
    /**
     * Le nonce qui sera généré dans l'écran d'édition.
     */
    const NONCE = 'dcl_nonce';

    /**
     * Le nom du champ parent de tous les champs de la notice.
     */
    const FORM_NAME = 'dbref';

    /**
     * La base de données documentaire.
     *
     * @var Database
     */
    protected $database;

    /**
     * Le post-type de la base.
     *
     * Equivalent à $this->database->postType().
     *
     * @var string
     */
    protected $postType;

    /**
     * La notice en cours d'édition.
     *
     * @var Type
     */
    protected $reference;

    /**
     * Indique si on crée un nouveau post ou si on édite une notice existante.
     *
     * Initialisé par create() et utilisé par edit() pour savoir s'il faut ou
     * non injecter les valeurs par défaut dans les champs des metaboxes.
     *
     * @var bool
     */
    protected $isNewPost = false;

    /**
     *
     * @param Database $database
     */
    public function __construct(Database $database) {
        $this->database = $database;
        $this->postType = $database->postType();

        // Demande à l'utilisateur le type de la notice à créer
        add_action('load-post-new.php', function() {
            $this->isMyPostType() && $this->create();
        });

        // Ajoute les metaboxes dans le formulaire de saisie
        add_action('add_meta_boxes_' . $this->postType, function(WP_Post $post) {
            $this->edit($post->ID);
        });

        // Enregistre les données transmises lorsque wordpress sauve le post
        global $pagenow;
        if ($pagenow === 'post.php' && $this->isMyPostType() && isset($_POST['action']) && $_POST['action'] === 'editpost') {
            add_filter('wp_insert_post_data', function(array $data, array $postarr) {
                return $this->save($data, $postarr);
            }, 10, 2);
        }
    }

    /**
     * Indique si la requête en cours concerne un enregistrement du post-type
     * géré par cette base.
     *
     * @return boolean
     */
    protected function isMyPostType() {
        global $typenow;

        return $typenow === $this->postType;
    }

    /**
     * Modifie le titre de l'écran d'édition en fonction du type de notice en
     * cours.
     *
     * @param string $type le type en cours
     * @param boolean $creation true s'il s'agit d'un nouvele enregistrement,
     * false s'il s'agit d'une mise à jour.
     */
    protected function setPageTitle($type, $creation = false) {
        // Variable globale wordpress utilisée pour le titre de la page
        // Définie dans post-new.php (entres autres) et affichée dans
        // edit-form-advanced...
        global $title;

//         if ($creation) {
//             $title = __("Base %s : créer une notice (%s)", 'docalist-biblio');
//         } else {
//             $title = __("Base %s : modifier une notice (%s)", 'docalist-biblio');
//         }

        $title = __('%s : %s', 'docalist-biblio');

        $title = sprintf(
            $title,
            $this->database->settings()->label(),
            lcfirst($this->database->settings()->types[$type]->label())
        );
    }

    /**
     * Affiche l'écran "choix du type de notice à créer".
     */
    protected function create() {
        $this->isNewPost = true;

        // S'il n'y a qu'un seul type de notices, inutile de demander à l'utilisateur
        $types = $this->database->settings()->types;
        if (empty($_REQUEST['ref_type']) && count($types) === 1) {
            $_REQUEST['ref_type'] = $types->first()->name();
        }

        // On connaît le type de notice à créer
        if (isset($_REQUEST['ref_type'])) {
            // On va laisser WordPress continuer son process
            // Il va appeller wp_insert_post() pour créer un "auto-draft".
            // Juste avant l'insertion, il appelle le filtre wp_insert_post_data
            // avec les données initiales du post. C'est ce filtre qu'on intercepte
            // pour initialiser la notice.
            // Wordpress va ensuite enregistrer nos données puis afficher le
            // formulaire edit-form-advanced

            // Injecte les valeurs par défaut dans le draft qui va être créé
            add_filter('wp_insert_post_data', function(array $data) {
                // Crée une référence du type demandé avec les valeurs par défaut du formulaire
                $ref = $this->database->createReference($_REQUEST['ref_type'], null, 'edit');

                // Evite le titre wp "brouillon auto"
                $ref->schema()->hasField('title') && $ref->title = '';

                // Génère les données du post wp à créer
                $data = $this->database->encode($ref->value()) + $data;

                return $data;
            }, 1000); // on doit avoir une priorité > au filtre installé dans database.php

            // Adapte le titre de l'écran de saisie
            //$this->setPageTitle($_REQUEST['ref_type'], true);
            // TODO : ne sert à rien car edit() et appellé après et change le titre

            // Laisse wp afficher le formulaire
            return;
        }

        // On ne sait pas quel type de notice créer. Demande à l'utilisateur.

        // Indique à WP l'option de menu en cours
        // cf. wp-admin/post-new.php, lignes 28 et suivantes
        global $submenu_file;
        $submenu_file = "post-new.php?post_type=$this->postType";

        // Affiche la page "Choix du type de notice à créer"
        require_once('./admin-header.php');
        $view = new ViewResponse('docalist-biblio:reference/choose-type', [
            'database' => $this->database,
        ]);
        $view->sendContent();
        include('./admin-footer.php');

        // Empêche wp d'afficher le formulaire edit-form standard
        die();
    }

    /**
     * Ajoute une metabox de débogage qui affiche le contenu brut du post.
     */
    protected function addDebugMetabox(Type $ref) {
        add_meta_box(
            'dbdebug',                             // id metabox
            'Informations de debug de la notice',  // titre
            function () use ($ref) {               // Callback
                global $post;

                $data = $post->to_array();
                unset($data['post_excerpt']);
                $data = array_filter($data);

                echo "<h4>Propriétés du post WordPress :</h4><pre>";
                echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                echo "</pre>";

                echo "<h4>Contenu de la notice :</h4><pre>";
                echo $ref;
                echo "</pre>";

                echo "<h4>Mapping Docalist-Search</h4><pre>";
                echo json_encode($ref->map(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

                echo "</pre>";
            },
            $this->postType,    // posttype
            'normal',           // contexte
            'high'              // priorité
        );
    }

    /**
     * Paramètre le formulaire de saisie et ajoute les metaboxes correspondant
     * au type de la notice en cours.
     *
     * @param int $id ID du post à éditer.
     */
    protected function edit($id) {
        // Charge la notice à éditer
        $ref = $this->database->load($id);

        // Adapte le titre de l'écran de saisie
        $this->setPageTitle($ref->type(), false);

        // Supprime la metabox "Identifiant"
        remove_meta_box('slugdiv', $this->postType, 'normal');

        add_action('edit_form_after_title', function() {
            $this->createNonce();
        });

        // Crée une metabox "debug" pour afficher le contenu brut du post
        $this->addDebugMetabox($ref);

        // Construit le formulaire
        foreach($this->metaboxes($ref, ! $this->isNewPost) as $form) {
            $id = $form->getAttribute('id');
            $title = $form->getLabel() ?: $ref->schema()->label();
            $form->setLabel(null); // comme on affiche le titre nous même

            // binde le formulaire
            $form->bind($ref);

            // Regroupe tous les champs dans un champ parent 'dbref'
            // Cela évite que wp interfére avec nos champs (#250, #335 par exemple)
            $form->setName(self::FORM_NAME);

            // Génère le formulaire et enqueue les assets requis
            $form = $form->render('wordpress');

            // Ajoute une metabox qui affiche le résultat
            add_meta_box(
                $id,
                $title,
                function() use($form) {
                    echo $form;
                },
                $this->postType,
                'normal',
                'high'
            );
        }

        // Insère notre feuille de style
        wp_styles()->enqueue('docalist-biblio-edit-reference');

        // Définit l'état initial des metaboxes (normal, replié, masqué)
        $hidden = ['authordiv', 'commentsdiv', 'commentstatusdiv', 'trackbacksdiv', 'revisionsdiv', 'dbdebug'];
        $collapsed = [];
        foreach($ref->schema()->getFields() as $name => $field) {
            if ($field->type() === 'Docalist\Biblio\Type\Group') {
                switch($field->state()) {
                    case 'hidden':
                        $hidden[] = $name;
                        break;
                    case 'collapsed':
                        $collapsed[] = $name;
                        break;
                    // default : affichage normal
                }
            }
        }

        add_filter('default_hidden_meta_boxes', function(array $result, WP_Screen $screen) use($hidden) {
            return $screen->id === $this->postType ? $hidden : $result;
        }, 10, 2);

        add_filter('get_user_option_closedpostboxes_dbprisme', function($result) use ($collapsed) {
            return $result === false ? $collapsed : $result;
        });
    }

    /**
     * Enregistre la notice.
     *
     * @param array $data Les données du post wordpress (wp_slashées).
     * @param array $postarr Les données transmises dans $_POST.
     *
     * @return array Les données à enregistrer dans le post.
     */
    protected function save($data, $postarr) {
        $debug = false;

        /*
         * Cette méthode est appellée par wp_insert_post() quand wordpress
         * s'apprête à enregistrer le post modifié dans la base (cf __constuct)
         * - $postarr contient la totalité des données transmises lors de
         *   l'appel à wp_insert_post (i.e. tous les champs du formulaire).
         * - $data contient les données standard du WP_POST que wordpress a
         *   construit à partir de $postarr (post_author, post_date, etc.)
         */

        // Vérifie le nonce et le post type (ignore révisions et autres types)
        if ($data['post_type'] !== $this->postType || ! $this->checkNonce()) {
            return $data;
        }

        // Les données transmises par Wordpress sont "slashées"
        $data = wp_unslash($data);
        $postarr = wp_unslash($postarr);

        // Tous les champs sont dans un champ parent 'dbref' (cf. edit)
        if (! isset($postarr[self::FORM_NAME])) {
            die('Aucune donnée transmise dans '. self::FORM_NAME . '[]');
        }

        if ($debug) {
            header('content-type: text/html; charset=UTF8');
            echo '<h1>Données du formulaire</h1>';
            var_dump($postarr[self::FORM_NAME]);
        }

        // Supprime les espaces de début et de fin partout (docalist/docalist#327)
        $postarr[self::FORM_NAME] = $this->deepTrim($postarr[self::FORM_NAME]);

        /*
         * Etape 1
         *
         * On part de $data qui contient :
         * - les champ wordpress mis à jour (post_status, post_date, etc.)
         * - les anciennes données de la notice existante, encodées en json
         *   dans le champ post_excerpt fournit par wordpress dans $data.
         *
         * On construit un objet Type à partir de ces données :
         * - la référence obtenue correspond aux données de l'ancienne notice
         *   sauf pour les champs mappés qui contiennent les données WP à jour.
         * - les champs wordpress qui ne sont pas mappés (post_date_gmt,
         *   ping_status...) sont perdus : ils figurent dans $data mais pas
         *   dans $ref. Ils seront réinjectés à la fin (étape 4).
         */
        $ref = $this->database->decode($data, $postarr['ID']);
        if (! isset($ref['type'])) {
            throw new Exception("Pas de type dans data");
        }
        unset($ref['title']);
        $ref = $this->database->createReference($ref['type'], $ref);
        $ref->id($postarr['ID']); // TODO devrait être géré dans createRef

        if ($debug) {
            echo '<h1>Notice existante</h1>';
            echo "<p>Ref créée à partir de data, il s'agit de la notice existante, sauf pour les champs WP mappés qui contiennent déjà la valeur mise à jour.</p>";
            echo "<pre>$ref</pre>";
        }

        /*
         * Etape 2
         *
         * Met à jour la référence avec les données des metaboxes.
         */
        if ($debug) {
            echo '<h1>Binding notice / formulaire</h1>';
        }
        foreach($this->metaboxes($ref) as $metabox) {
            $metabox->bind($postarr[self::FORM_NAME]);
            foreach($metabox->getData() as $key => $value) {
                if ($debug) {
                    echo "<li>Set <code>$key = ", htmlspecialchars(var_export($value,true)), '</code></li>';
                }
                $ref->$key = $value;
            }
        }

        if ($debug) {
            echo "<h1>Etat de la notice après binding</h1>";
            var_dump($ref);
            //echo "<pre>$ref</pre>";
        }

        // Filtre les champs et les valeurs vides
        $ref->filterEmpty(false);
        if ($debug) {
            echo "<h1>Filtrage des champs vides. Notice après :</h1>";
            echo "<pre>$ref</pre>";
        }

        // Numérote la notice s'il y a lieu
        $ref->beforeSave($this->database);
        if ($debug) {
            echo "<h1>Appel de beforeSave() :</h1>";
            echo "<pre>$ref</pre>";
        }

        /*
         * Etape 3
         *
         * Génère le post wordpress à partir de la notice
         */
        $ref = $this->database->encode($ref->value()); // !!! encode ne doit pas générer de valeurs par défaut
        if ($debug) {
            echo "<h1>Données de la notice après encode() = post généré :</h1>";
            var_dump($ref);
        }

        /*
         * Etape 4
         *
         * Injecte dans le post les champs wordpress qu'on a perdu à l'étape 1
         * quand on a convertit $data en référence
         */
        $data = $ref + $data;
        if ($debug) {
            echo "<h1>Injecte les champs wordpress manquants. Données finales retournées à wordpress :</h1>";
            var_dump($data);
        }

        // Retourne le résultat à Wordpress, en "slashant" les données
        $debug && die();
        return wp_slash($data);
    }

    /**
     * Trim récursif.
     *
     * @param array $data
     *
     * @return $data
     */
    protected function deepTrim(array $data) {
        return array_map(function($data) {
            return is_scalar($data) ? trim($data) : $this->deepTrim($data);
        }, $data);
    }

    /**
     * Génère un nonce WordPress lorsque l'écran d'édition du post type
     * est affiché.
     */
    protected function createNonce() {
        wp_nonce_field('edit-post', self::NONCE);
    }

    /**
     * Vérifie que $_POST contient le nonce créé par createNonce() et que
     * celui-ci est valide.
     *
     * @return bool
     */
    protected function checkNonce() {
        return isset($_POST[self::NONCE]) && wp_verify_nonce($_POST[self::NONCE], 'edit-post');
    }

    /**
     * Retourne les formulaires utilisés pour saisir une notice de ce type.
     *
     * @param string $type
     * @param bool $ignoreDefaults Indique s'il faut ignorer la valeur par
     * défaut des champs.
     *
     * @return Container[] Un tableau de la forme id metabox => form fragment
     */
    protected function metaboxes(Type $ref, $ignoreDefaults = false) {

        // Charge la grille "edit" correspondant au type de la notice
        $schema = $this->database->settings()->types[$ref->type()]->grids['edit'];

        // Récupère la liste des champs
        $fields = $schema->getFields();

        // Crée un groupe par défaut si la liste ne commence pas par un groupe
        if (reset($fields)->type() !== 'Docalist\Biblio\Type\Group') {
            $box = new Container();
            $box->setLabel('')->setAttribute('id', 'defaultgroup');
            $hasBoxCap = true;
        }

        // Balaie les champs et crée les boites au fur et à mesure
        $metaboxes = [];
        foreach($fields as $name => $field) {
            // Nouveau champ
            if ($field->type() !== 'Docalist\Biblio\Type\Group') {

                // Si on n'a pas la cap de la boite en cours, inutile de créer le champ
                if (! $hasBoxCap) {
                    continue;
                }

                // Si on n'a pas la cap du champ, inutile de créer le champ
                $cap = $field->capability();
                if ($cap && ! current_user_can($cap)) {
                    continue;
                }

                // Ok, on a tous les droits requis, crée le champ
//                $ignoreDefaults && $field->__unset('default', null);
                $box->add($ref->$name->getEditorForm($field));

                // Au suivant
                continue;
            }

            // Nouveau groupe de champ, sauvegarde la boite en cours si nécessaire
            if (isset($box) && $box->hasItems()) {
                $metaboxes[] = $box;
            }

            // Remarque box=null si on n'a pas les droits sur la boite en cours
            // et fields=null si on a deux groupes à se suivre dans la grille.

            // Si on n'a pas la cap requise pour la nouvelle boite, inutile de la créer
            $cap = $field->capability();
            if ($cap && ! current_user_can($cap)) {
                $hasBoxCap = false;
                unset($box); // secu : $box n'existe pas si hasBoxCap est à false
                continue;
            }

            // Ok, on a les droits, crée la nouvelle boite
            $hasBoxCap = true;
            $box = new Container();
            $box->setLabel($field->label())
                ->setDescription($field->description())
                ->setAttribute('id', $name);
        }

        // Sauvegarde la dernière boite créée si nécessaire
        if (isset($box) && $box->hasItems()) {
            $metaboxes[] = $box;
        }

        // Ok
        return $metaboxes;
    }

    /**
     * Vérifie que les tables indiquées dans la définition d'un champ sont
     * correctes et qu'elles existent.
     *
     * Si ce n'est pas le cas, la définition du champ est temporairement
     * modifiée pour utiliser la table par défaut indiquée en paramètre et
     * une erreur "admin notice" est générée.
     *
     * @param Schema $def La définition du champ à vérifier.
     * @param string $default Le nom de la première table par défaut.
     * @param string $default2 Le nom de laseconde table par défaut (si le champ
     * utilise deux tables, par exemple organization).
     */
//     protected function checkTables(Schema $def, $default, $default2 = null) {
//         foreach(['table' => $default, 'table2' => $default2] as $table => $default) {
//             if ($table === 'table2' && !isset($def->$table)) {
//                 continue;
//             }

//             // Vérifie que la table indiquée existe
//             if (preg_match('~([a-z]+):([a-zA-Z0-9_-]+)~', $def->$table(), $match)) {
//                 if (docalist('table-manager')->info($match[2])) {
//                     continue;
//                 }
//             }

//             // Table incorrecte, affiche une admin notice
//             $msg = __("La table <code>%s</code> indiquée pour le champ <code>%s</code> n'est pas valide.", 'docalist-biblio');
//             $msg = sprintf($msg, $def->$table() ?: ' ', $def->name());
//             $msg .= '<br />';
//             $msg .= __('Vous devez corriger la grille de saisie', 'docalist-biblio');
//             add_action('admin_notices', function () use ($msg) {
//                 printf('<div class="error"><p>%s</p></div>', $msg);
//             });

//             // Et utilise la table par défaut
//             $def->$table = $default;
//         }
//     }

    /*
     * N'est plus utilisée mais peut reservir si on voulait générer un select
     * contenant toutes les entrées d'une table.
     */
//     protected function tableOptions($table, $fields = 'code,label') {
//         empty($table) && $table = 'countries';
//         /* @var $tableManager TableManager */
//         $tableManager = docalist('table-manager');
//         return $tableManager->get($table)->search($fields);
//     }
}