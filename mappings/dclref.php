<?php
/**
 * This file is part of a "Docalist Biblio" plugin.
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 *
 * @package     Docalist
 * @subpackage  Biblio
 * @author      Daniel Ménard <daniel.menard@laposte.net>
 * @version     SVN: $Id$
 */

namespace Docalist\Biblio;
// http://elasticsearch-users.115913.n3.nabble.com/help-needed-with-the-query-td3177477.html
// https://gist.github.com/clintongormley/1088986

// http://elasticsearch-users.115913.n3.nabble.com/Query-all-fields-in-an-embedded-document-tp3485229p3489928.html

/**
 * Mappings ElasticSearch pour les notices bibliographiques (dclref).
 *
 * @see http://www.elasticsearch.org/guide/reference/mapping/
 */
return array(
    'settings' => array(
        'analysis' => array(
            // Les CharFilter filtrent le texte avant tokenisation
            'char_filter' => array( //
            ),

            // Tokenisation du texte
            'tokenizer' => array( // liste des filtres
            ),

            // Filtre les tokens avant stockage
            'filter' => array( //
            ),

            // Définit une chaine d'indexation à partir des briques ci-dessus
            'analyzer' => array( //
                // Analyseur pour du texte standard (title, othertitle, collection...)
                'stdtext' => array(
                    'type' => 'custom',
                    'char_filter' => array(
                    ),
                    'tokenizer' => 'standard',
                    'filter' => array(
                        'asciifolding', 'elision',
                    ),
                ),

                // Analyseur pour du texte pouvant contenir du html (notes, résumé)
                'stdhtml' => array(
                    'type' => 'custom',
                    'char_filter' => array(
                        'html_strip',
                    ),
                    'tokenizer' => 'standard',
                    'filter' => array(
                        'asciifolding',
                    ),
                ),
            ),
        ),
    ),
    'mappings' => array(
        'dclref' => array(
            // http://www.elasticsearch.org/guide/reference/mapping/source-field.html
            '_source' => array(
                // Stocke le contenu du document original dans _source
                'enabled' => true,

                // Tous les champs sotn stockés
                'includes' => array('*'),

                // Sauf les champs imported et errors
                'excludes' => array(
                    'imported',
                    'errors'
                ),
            ),

            'dynamic' => false,

            // Liste des champs inclus dans l'index "_all".
            // Aucun par défaut, on le définit explicitement
            'include_in_all' => true,

            'properties' => array(
                'ref' => array('type' => 'long'),
                'type' => array(
                    'type' => 'multi_field',
                    'fields' => array(
                        'type' => array(
                            'type' => 'string',
                            'include_in_all' => true,
                        ),
                        'keyword' => array(
                            'type' => 'string',
                            'index' => 'not_analyzed',
                        ),
                    ),
                ),

                'genre' => array(
                    'type' => 'multi_field',
                    'repeatable=' => true,
                    'fields' => array(
                        'genre' => array(
                            'type' => 'string',
                        ),
                        'keyword' => array(
                            'type' => 'string',
                            'index' => 'not_analyzed',
                        )
                    )
                ),

                'media' => array(
                    'type' => 'multi_field',
                    'repeatable=' => true,
                    'fields' => array(
                        'media' => array(
                            'type' => 'string',
                        ),
                        'keyword' => array(
                            'type' => 'string',
                            'index' => 'not_analyzed',
                        )
                    )
                ),

                'author' => array(
                    'type' => 'object',
//                    'include_in_parent' => true,
                    'repeatable' => true,
                    'properties' => array(
                        'name' => array(
                            'type' => 'string',
                            'include_in_all' => true
                        ),
                        'firstname' => array(
                            'type' => 'string',
                            'include_in_all' => true
                        ),
                        'role' => array(
                            'type' => 'string',
                            'index' => 'no'
                        ),
                        'keyword' => array(
                            'type' => 'string',
                            'index' => 'not_analyzed',
                        ),
                    ),
                ),

                'organisation' => array(
                    'type' => 'object',
                    'repeatable' => true,
                    'properties' => array(
                        'name' => array(// docalist : repeatable
                            'type' => 'string',
                            'include_in_all' => true
                        ),
                        'city' => array('type' => 'string'),
                        'country' => array('type' => 'string'),
                        'role' => array(
                            'type' => 'string',
                            'index' => 'no'
                        )
                    ),
                ),
                'title' => array(
                    'type' => 'string',
                    'include_in_all' => true,
                    'analyzer' => 'stdtext',
                ),
                'othertitle' => array(
                    'type' => 'object',
                    'repeatable' => true,
                    'properties' => array(
                        'type' => array('type' => 'string'),
                        'title' => array(
                            'type' => 'string',
                            'include_in_all' => true
                        ),
                    ),
                ),
                'translation' => array(
                    'type' => 'object',
                    'repeatable' => true,
                    'properties' => array(
                        'language' => array('type' => 'string'),
                        'title' => array(
                            'type' => 'string',
                            'include_in_all' => true
                        ),
                    ),
                ),
                'date' => array('type' => 'string'),
                'journal' => array(
                    'type' => 'multi_field',
                    'fields' => array(
                        'journal' => array(
                            'type' => 'string',
                            'include_in_all' => true,
                        ),
                        'keyword' => array(
                            'type' => 'string',
                            'index' => 'not_analyzed',
                        ),
                    ),
                ),
                'issn' => array('type' => 'string'),
                'volume' => array('type' => 'string'),
                'issue' => array('type' => 'string'),
                'language' => array(
                    'type' => 'string',
                    'repeatable' => true
                ),
                'pagination' => array(
                    'type' => 'string',
                    'repeatable' => true
                ),
                'format' => array(
                    'type' => 'string',
                    'index' => 'no',
                ),
                'isbn' => array(
                    'type' => 'string',
                    'repeatable' => true
                ),
                'editor' => array(
                    'type' => 'object',
                    'repeatable' => true,
                    'properties' => array(
                        'name' => array('type' => 'string'), // docalist : repeatable
                        'city' => array('type' => 'string'),
                        'country' => array('type' => 'string')
                    ),
                ),
                'edition' => array(
                    'type' => 'object',
                    'repeatable' => true,
                    'properties' => array(
                        'type' => array('type' => 'string'),
                        'value' => array('type' => 'string')
                    ),
                ),
                'collection' => array(
                    'type' => 'object',
                    'repeatable' => true,
                    'properties' => array(
                        'name' => array('type' => 'string'),
                        'number' => array('type' => 'string')
                    ),
                ),
                'event' => array(
                    'type' => 'object',
                    'repeatable' => true,
                    'properties' => array(
                        'title' => array('type' => 'string'),
                        'date' => array('type' => 'string'),
                        'place' => array('type' => 'string'),
                        'number' => array('type' => 'string')
                    ),
                ),
                'degree' => array(
                    'type' => 'object',
                    'repeatable' => true,
                    'properties' => array(
                        'level' => array('type' => 'string'),
                        'title' => array('type' => 'string')
                    ),
                ),
                'abstract' => array(
                    'type' => 'object',
                    'repeatable' => true,
                    'properties' => array(
                        'language' => array('type' => 'string'),
                        'content' => array(
                            'type' => 'string',
                            'include_in_all' => true
                        )
                    ),
                ),
                'topic' => array(
                    'type' => 'object',
                    'repeatable' => true,
                    'properties' => array(
                        'type' => array('type' => 'string'),
                        'term' => array(
                            'type' => 'multi_field',
                            'repeatable' => true,
                            'fields' => array(
                                'term'=> array(
                                    'type' => 'string',
                                    'include_in_all' => true,
                                ),
                                'keyword' => array(
                                    'type' => 'string',
                                    'index' => 'not_analyzed',
                                ),
                            ),
                        ),
                    ),
                ),
                'note' => array(
                    'type' => 'object',
                    'repeatable' => true,
                    'properties' => array(
                        'type' => array('type' => 'string'),
                        'content' => array('type' => 'string')
                    ),
                ),
                'link' => array(
                    'type' => 'object',
                    'repeatable' => true,
                    'properties' => array(
                        'type' => array('type' => 'string'),
                        'url' => array('type' => 'string'),
                        'label' => array('type' => 'string'),
                        'date' => array('type' => 'date'), // TODO: +format
                        'lastcheck' => array('type' => 'date'), // TODO: +format
                        'checkstatus' => array('type' => 'string') // TODO: status ?
                    ),
                ),
                'doi' => array('type' => 'string'),
                'relations' => array(// TODO : au singulier
                    'type' => 'object',
                    'repeatable' => true,
                    'properties' => array(
                        'type' => array('type' => 'string'),
                        'ref' => array(
                            'type' => 'long',
                            'repeatable' => true // no repeat dans fmt docalist
                        )
                    ),
                ),
                'owner' => array(
                    'type' => 'string',
                    'repeatable' => true
                ),
                'creation' => array(
                    'type' => 'object',
                    'properties' => array(
                        'date' => array('type' => 'date'), // TODO: format
                        'by' => array('type' => 'string')
                    ),
                ),
                'lastupdate' => array(
                    'type' => 'object',
                    'properties' => array(
                        'date' => array('type' => 'date'), // TODO: format
                        'by' => array('type' => 'string')
                    ),
                ),
                'status' => array(
                    'type' => 'string',
                    'repeatable' => true
                ),
                'statusdate' => array('type' => 'date'), // TODO: format

                // Les champs qui suivent ne font pas partie du format docalist

                'imported' => array(
                    'type' => 'string',
                    'index' => 'no'
                    // http://elasticsearch-users.115913.n3.nabble.com/disabling-a-field-td3805952.html
                ),
                'errors' => array(
                    'type' => 'object',
                    'repeatable' => true,
                    'properties' => array(
                        'code' => array('type' => 'string'),
                        'value' => array('type' => 'string'),
                        'message' => array('type' => 'string'),
                    ),
                ),
                'todo' => array('type' => 'string'),
            ),
        ),
    ),
);
