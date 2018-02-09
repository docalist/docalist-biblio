<?php
/**
 * This file is part of Docalist Biblio.
 *
 * Copyright (C) 2012-2018 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 */
namespace Docalist\Biblio\Field;

use Docalist\Data\Field\TopicField as BaseTopicField;

/**
 * Mots-clés décrivant le document catalogué.
 *
 * Ce champ permet de saisir des mots-clés provenant de différents vocabulaires pour décrire et classer le document.
 *
 * Chaque indexation comporte deux sous-champs :
 * - `type` : vocabulaire,
 * - `value` : mots-clés.
 *
 * Le sous-champ type est associé à une table d'autorité qui liste les types d'indexation disponibles
 * ("table:topic-type" par défaut).
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class TopicField extends BaseTopicField
{
}
