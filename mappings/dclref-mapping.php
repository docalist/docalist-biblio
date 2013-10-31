<?php
/**
 * This file is part of the 'Docalist Biblio' plugin.
 *
 * Copyright (C) 2012, 2013 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 *
 * @package     Docalist
 * @subpackage  Biblio
 * @author      Daniel Ménard <daniel.menard@laposte.net>
 * @version     SVN => $Id$
 */

/**
 * Mapping Docalist Search pour une notice documentaire.
 *
 * @return array
 */
return [
    /*
     * De manière générale, aucun champ n'est stocké dans _source car on n'en
     * a pas besoin pour faire la recherche (pour afficher le contenu des
     * champs, on passe par la base mysql).
     *
     * La seule exception, ce sont les champs pour lesquels on veut pouvoir
     * faire du highlighting (aucun pour l'instant).
     *
     * @see http => //www.elasticsearch.org/guide/reference/mapping/source-field/
     */
    '_source' => [
        'enabled' => true,      // redondant (enabled par défaut), mais explicite
        'excludes' => [],    // exclut tout
        'includes' => ['*']        // n'inclut rien pour le moment
    ],

    /*
     * On n'indexe que les champs qui sont explicitement définis dans le
     * mapping.
     *
     * @see http => //www.elasticsearch.org/guide/reference/mapping/object-type/
     */
    'dynamic' => false,

    /*
     * Pour le moment, le champ _all est utilisé.
     *
     * On verra plus tard s'il c'est mieux ou non d'utiliser un multi_match.
     *
     * @see http://www.elasticsearch.org/guide/reference/mapping/all-field/
     */
    '_all' => [
        'enabled' => true,
        'analyzer' => 'dclref-default-fr'
    ],

    /*
     * Par défaut, aucun champ n'est inclus dans all, chaque champ doit
     * indiquer explictement 'include_in_all: true'.
     *
     * (en fait on définit include_in_all à false au niveau de l'objet, et
     * c'est hérité par les champs de l'objet). Pas clair dans la doc ES.
     *
     * @see http://www.elasticsearch.org/guide/reference/mapping/all-field/
     */
    'include_in_all' => false,

    /*
     * Liste des champs
     */
    'properties' => [
        'ref' => [
            'type' => 'long',
        ],
        'type' => [
            'type' => 'multi_field',
            'fields' => [
                'type' => [
                    'type' => 'string',
                    'include_in_all' => true,
                    'analyzer' => 'dclref-default-fr'
                ],
                'filter' => [
                    'type' => 'string',
                    'index' => 'not_analyzed'
                ]
            ]
        ],
        'genre' => [
            'type' => 'multi_field',
            'fields' => [
                'genre' => [
                    'type' => 'string',
                    'include_in_all' => true,
                    'analyzer' => 'dclref-default-fr'
                ],
                'filter' => [
                    'type' => 'string',
                    'index' => 'not_analyzed'
                ]
            ]
        ],
        'media' => [
            'type' => 'multi_field',
            'fields' => [
                'media' => [
                    'type' => 'string',
                    'include_in_all' => true,
                    'analyzer' => 'dclref-default-fr'
                ],
                'filter' => [
                    'type' => 'string',
                    'index' => 'not_analyzed'
                ]
            ]
        ],

        /*
         * Author
         * on veut :
         * - rechercher "en plein texte" sur l'auteur :
         * author = jacques martin, martin jacques, martin j., j. martin, etc.
         *
         * - on veut filtrer par auteur :
         * auhor.filter='martin, jacques';
         *
         * - on veut une table de lookup
         * author.lookup = 'martin¤jacques';
         * non : il faut que ce soit la forme riche :
         * author.lookup = 'Martin¤Jacques';
         *
         * en fait on peut ramener .filter et .lookup à la même chose ?
         *
         * (
         * apparté : fonctionnement des lookups :
         * - on est dans le champ author.name.
         * - l'utilisateur a commencé à taper le nom d'un auteur
         *   par exemple : mart
         * - on fait une recherche dans toute la base doc en cours
         *   author=mar (recherche plein texte)
         * - et on demande une facette sur author.lookup, triée par ordre alpha
         * - on récupère des entrées de la forme 'martin¤jacques'
         * - on les formatte pour affichage et on stocke l'entrée en value
         * - lors de l'insertion, on explode sur '¤' et on injecte la partie
         *   gauche dans name et la partie droite dans firstname.
         * - il faut indiquer au code js la "structure" de la table de lookup et
         *   les id des champs dans lesquels injecter (en fait la liste des id
         *   suffit : on explode sur ¤ et on injecte dans l'ordre=.
         * )
         * Database.map nous fournit les auteurs sous la forme d'un tableau de
         * chaines (i.e. les auteurs ne sont plus structurés), chaque chaine
         * ayant le format "name¤firstname" (role est ignoré)
         */
        'author' => [
            'type' => 'multi_field',
            'fields' => [
                'author' => [
                    'type' => 'string',
                    'include_in_all' => true,
                    'analyzer' => 'text' // pas de stemming
                ],
                'filter' => [
                    'type' => 'string',
                    'index' => 'not_analyzed'
                ],
                'suggest' => [
                    'type' => 'completion',
                    'index_analyzer' => 'suggest',
                    'search_analyzer' => 'suggest',
                ]
            ]
        ],

        /*
         * Database.map nous fournit un tableau de chaines de la forme :
         * nom¤city¤country
         * (role est ignoré)
         */
        'organisation' => [
            'type' => 'multi_field',
            'fields' => [
                'organisation' => [
                    'type' => 'string',
                    'include_in_all' => true,
                    'analyzer' => 'dclref-default-fr' // stemming sur les noms d'organismes
                ],
                'filter' => [
                    'type' => 'string',
                    'index' => 'not_analyzed'
                ],
                'suggest' => [
                    'type' => 'completion',
                    'index_analyzer' => 'suggest',
                    'search_analyzer' => 'suggest',
                ]
            ]
        ],
        'title' => [
            'type' => 'string',
            'include_in_all' => true,
            'analyzer' => 'dclref-default-fr'
        ],

        /*
         * database.map fournit un tableau de chaines contenant othertitle.title
         */
        'othertitle' => [
            'type' => 'string',
            'include_in_all' => true,
            'analyzer' => 'dclref-default-fr'
        ],

        'translation' => [
            'type' => 'string',
            'include_in_all' => true,
            'analyzer' => 'dclref-default-fr'
        ],

        'date' => [
            'type' => 'date',
            'format' => 'yyyy-MM-dd||yyyy-MM||yyyyMMdd||yyyyMM||yyyy',
            'ignore_malformed' => true
        ],

        // @todo : pour filter, faire titre¤issn
        // permettra d'injecter l'issn quand on saisit le titre
        // et peut-être l'inverse (on recherche issn on injecte titre)
        'journal' => [
            'type' => 'multi_field',
            'fields' => [
                'journal' => [
                    'type' => 'string',
                    'include_in_all' => true,
                    'analyzer' => 'dclref-default-fr' // stemming sur les noms d'organismes
                ],
                'filter' => [
                    'type' => 'string',
                    'index' => 'not_analyzed'
                ],
                'suggest' => [
                    'type' => 'completion',
                    'index_analyzer' => 'suggest',
                    'search_analyzer' => 'suggest',
                ]
            ]
        ],
        // @todo, à voir avec journal
        // pour le moment, indexé, ajout à all
        'issn' => [
            'type' => 'string',
            'include_in_all' => true,
            'analyzer' => 'text' // pas de stemming
        ],

        // searchable mais pas dans _all
        'volume' => [
            'type' => 'string',
            'analyzer' => 'text' // pas de stemming
        ],

        // searchable mais pas dans _all
        'issue' => [
            'type' => 'string',
            'analyzer' => 'text' // pas de stemming
        ],

        // searchable mais pas dans _all
        'language' => [
            'type' => 'string',
            'index' => 'not_analyzed' // c'est un code en minu, donc pas d'analyse
        ], // @todo : faire un filter pour permettre des facettes par langue

        // non indexé
        // supprimé dans database.map
/*
        'pagination' => [
            'type' => 'string',
            'index' => 'no'
        ],
*/

        // non indexé
        // supprimé dans database.map
/*
        'format' => [
            'type' => 'string',
            'index' => 'no'
        ],
*/
        // searchable, dans _all
        'isbn' => [
            'type' => 'string',
            'include_in_all' => true,
            'index' => 'not_analyzed' // @todo à revoir
        ],

        // searchable, _all, lookup(name¤city¤country)
        'editor' => [
            'type' => 'multi_field',
            'fields' => [
                'editor' => [
                    'type' => 'string',
                    'include_in_all' => true,
                    'analyzer' => 'dclref-default-fr' // stemming sur les noms d'organismes
                ],
                'filter' => [
                    'type' => 'string',
                    'index' => 'not_analyzed'
                ],
            ]
        ],

        // non indexé
        // supprimé dans database.map
/*
        'edition' => [
            'type' => 'object',
            'properties' => [
                'type' => [
                    'type' => 'string'
                ],
                'value' => [
                    'type' => 'string'
                ]
            ]
        ],
*/
        // searchable (seul le nom est indexé), pas dans _all
        'collection' => [
            'type' => 'string',
            'include_in_all' => true,
            'analyzer' => 'dclref-default-fr'
        ],

        // searchable, _all, lookup(title¤date¤place¤number)
        'event' => [
            'type' => 'multi_field',
            'fields' => [
                'event' => [
                    'type' => 'string',
                    'include_in_all' => true,
                    'analyzer' => 'dclref-default-fr' // stemming sur les noms de colloques
                ],
                'filter' => [
                    'type' => 'string',
                    'index' => 'not_analyzed'
                ],
            ]
        ],

        // searchable, _all, lookup(level¤title)
        'degree' => [
            'type' => 'multi_field',
            'fields' => [
                'degree' => [
                    'type' => 'string',
                    'include_in_all' => true,
                    'analyzer' => 'dclref-default-fr' // stemming sur les noms de diplômes
                ],
                'filter' => [
                    'type' => 'string',
                    'index' => 'not_analyzed'
                ]
            ]
        ],

        'abstract' => [
            'type' => 'string',
            'include_in_all' => true,
            'analyzer' => 'dclref-default-fr' // stemming sur les résumés
        ],

        // Tous les champs mots-clés sont regroupés en un seul index
        // database.map nous passe directement un tableau de mots-clés.
        // @todo : comment on fait pour les lookup ?
        'topic' => [
            'type' => 'multi_field',
            'fields' => [
                'topic' => [
                    'type' => 'string',
                    'include_in_all' => true,
                    'analyzer' => 'dclref-default-fr' // stemming sur les mots-clés
                ],
                'filter' => [
                    'type' => 'string',
                    'index' => 'not_analyzed'
                ],
                'suggest' => [
                    'type' => 'completion',
                    'index_analyzer' => 'suggest',
                    'search_analyzer' => 'suggest',
                ]
            ]
        ],

        // Note : non indexé (copyright, note interne, accès, etc.)
/*
        'note' => [
            'type' => 'string',
            'include_in_all' => true,
            'analyzer' => 'dclref-default-fr' // stemming sur les résumés
        ],
*/

        // link : que l'url, searchable, pas dans _all
        'link' => [
            'type' => 'string', // @todo : pathhierarchy tokenizer
            // 'analyzer' => 'dclref-url',
        ],


        'doi' => [
            'type' => 'string',
            'index' => 'not_analyzed', // c'est un code, donc pas d'analyse
            'include_in_all' => true,
        ],

        'relations' => [
            'type' => 'object',
            'properties' => [
                'type' => [
                    'type' => 'string'
                ],
                'ref' => [
                    'type' => 'long',
                ]
            ]
        ],

        // searchable, pas dans _all, lookup/filter
        'owner' => [
            'type' => 'multi_field',
            'fields' => [
                'owner' => [
                    'type' => 'string',
                    'analyzer' => 'dclref-default-fr' // stemming sur les noms de diplômes
                ],
                'filter' => [
                    'type' => 'string',
                    'index' => 'not_analyzed'
                ]
            ]
        ],

        /*
         * ISO 8601
         * formats des dates ES :
         * year_month_day :  yyyy-MM-dd (= date)
         * year_month : yyyy-MM
         * year : yyyy
         *
         * basic_date : yyyyMMdd
         *
         */
        'creation' => [
            'type' => 'object',
            'properties' => [
                'date' => [
                    'type' => 'date',
                    'format' => 'yyyy-MM-dd||yyyy-MM||yyyyMMdd||yyyyMM||yyyy',
                    // yyyyMMdd     = format ISO 'basic-date'
                    // yyyyMM       = non autorisé en ISO (confusion avec yyMMdd : 201005 = mai 2010 ou 5 octobre 2000)
                    // yyyy         = format ISO 'year'
                    // yyyy-MM-dd   = format ISO 'year_month_day'
                    // yyyy-MM      = format ISO 'year_month'
                    'ignore_malformed' => true
                ],
                'by' => [
                    'type' => 'string'
                ]
            ]
        ],
        'lastupdate' => [
            'type' => 'object',
            'properties' => [
                'date' => [
                    'type' => 'date',
                    'format' => 'yyyy-MM-dd||yyyy-MM||yyyyMMdd||yyyyMM||yyyy',
                    'ignore_malformed' => true
                ],
                'by' => [
                    'type' => 'string',
                    'analyzer' => 'text' // pas de stemming
                ]
            ]
        ],
        'status' => [
            'type' => 'string',
            'analyzer' => 'text' // pas de stemming
        ],
/*
  On garde ?
        'statusdate' => [
            'type' => 'date'
        ],
*/
        // imported : non indexé
/*
        'imported' => [
            'type' => 'string',
            'index' => 'no'
        ],
*/
        'errors' => [
            'type' => 'object',
            'properties' => [
                'code' => [
                    'type' => 'string',
                    'analyzer' => 'text' // pas de stemming
                ],
                'value' => [
                    'type' => 'string',
                    'index' => 'no',
                ],
                'message' => [
                    'type' => 'string',
                    'index' => 'no',
                ]
            ]
        ],
/*
        'todo' => [
            'type' => 'string'
        ]
*/
    ]
];