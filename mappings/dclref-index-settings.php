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
 * Paramètre de l'index Docalist Search pour les notices documentaires.
 *
 * @return array
 */
return [
    '_meta' => ['docalist-biblio' => 0.2],
    'settings' => [
        'analysis' => [
            'analyzer' => [
                /* Analyseur générique pour du texte en français.
                 *
                 * Identique à l'analyseur "default" de Docalist Search, mais
                 * avec du stemming français en plus.
                 *
                 * - Supprime les tags html
                 * - Convertit le texte en minuscules
                 * - Supprime les accents (folding)
                 * - Supprime les élisions (c', d', l'...)
                 * - Stemming "light-stem-french" (définit par Docalist Search)
                 * - Tokenisation standard
                 */
                'dclref-default-fr' => [
                    'type' => 'custom',
                    'char_filter' => ['html_strip'],
                    'filter' => ['lowercase', 'asciifolding', 'elision', 'light-stem-french'],
                    'tokenizer' => 'standard',
                ],

                'dclref-url' => [
                    'type' => 'custom',
                    'char_filter' => ['html_strip'],
                    'filter' => ['lowercase'],
                    'tokenizer' => 'path_hierarchy',
                ]

            ]
        ]
    ]
];