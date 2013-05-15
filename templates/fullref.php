<?php
/**
 * Fonction utilisée pour afficher un champ répétable.
 *
 * La fonction teste si le champ est renseigné ou non. Si le champ est vide,
 * elle ne fait rien. Dans le cas contraire, elle va parcourir chacun des
 * éléments du tableau et appeller la closure pour chacun des éléments en lui
 * passant en paramètre l'élément en cours.
 * Entre deux éléments, le séparateur indiqué dans $sep est inséré.
 *
 * @param array $field Le champ à afficher
 * @param string $sep Le séparateur inséré entre deux occurences du champ.
 * @param closure $closure Le callback à appeller pour chaque élément.
 */
function all(array $field, $sep, closure $closure) {
    if (empty($field)) {
        return;
    }
    if (is_int(key($field))) {
        foreach($field as $i => $value) {
            if ($i) echo $sep;
            ob_start();
            call_user_func_array($closure, $value);
            $result = ob_get_clean();
            $result = trim($result, "\r\n\t ");
            echo $result;
        }
    } else {
        ob_start();
        call_user_func_array($closure, $field);
        $result = ob_get_clean();
        $result = trim($result, "\r\n\t ");
        echo $result;
    }
}

/**
 * Affiche le contenu d'un champ avec un préfixe et un suffixe optionnels.
 * Si le champ est vide, la fonction ne fait rien. Sinon, elle afiche
 * le préfixe, le contenu du champ et le suffixe.
 *
 * @param string $before périxe
 * @param mixed $field le champ
 * @param string $after le suffixe
 *
 */
function wrap($before='', $field, $after='') {
    if ($field) {
        return $before . $field . $after;
    }
    return '';
}

function label($label) {
    return sprintf('<span class="label" style="display: inline-block; width: 90px; font-weight: bold;">%s : </span>', $label);
}
/*
function startrow($class = '') {
    $class && $class=sprintf(' class="%s"', $class);
    echo $class ? "<tr class=\"$class\">" : '<tr>';
}
function endrow() {
    echo '</tr>';
}
*/
function row($field, $label, $sep = ', ', Closure $writer = null) {
    if (empty($field)) {
        return;
    }

    // Début de ligne
    echo '<tr>';

    // label
    echo '<th scope="row" valign="top" style="text-align: right">', $label, ' :</th>';

    // value
    echo '<td>';
    if ($writer) {
        all($field, $sep, $writer);
    }
    elseif (is_scalar($field)) {
        echo $field;
    } elseif (is_array($field)) {
        echo implode($sep, $field);
    }
    echo '</td>';

    // Fin de ligne
    echo '</tr>';
}

?>

<table style="border-collapse: separate; border-spacing:5px">
<?php
    row($ref, 'Réf');
    row($type, 'Type');
    row($genre, 'Genre');
    row($media, 'Support');
    row($author, 'Auteur', ', ', function($name, $firstname, $role) {
        echo
            '<span class="author">',
            $name,
            wrap(' (', $firstname, ')'),
            wrap(' / ', $role),
            '</span>';
    });

    row($organisation, 'Organismes', ', ', function($name, $city, $country, $role) {  ?>
        <span class="organisation"><?php
            echo implode(', ', $name);
            echo wrap('. ', $city);
            echo wrap('. ', $country);
            echo wrap('/', $role) ?>
        </span>
    <?php });

    row($title, 'Titre');

    row($othertitle, 'Autres titres', ', ', function($type, $title) {  ?>
        <span class="othertitle"><?php
            echo wrap('<em>', $type, '</em> : ');
            echo $title; ?>
        </span>
    <?php });

    row($translation, 'Traduction', ', ', function($language, $title) {  ?>
        <span class="translation"><?php
            echo wrap('', $language, ' : ');
            echo $title; ?>
        </span>
    <?php });

    row($date, 'Date');
    row($journal, 'Journal');
    row($issn, 'ISSN');
    row($volume, 'Volume');
    row($issue, 'Fascicule');
    row($language, 'Langue');
    row($pagination, 'Pages');
    row($format, 'Format');
    row($isbn, 'ISBN');
    row($editor, 'Editeur', ', ', function($name, $city, $country) {  ?>
        <span class="editor"><?php
            echo implode(', ', $name);
            echo wrap(', ', $city);
            echo wrap(', ', $country) ?>
        </span>
    <?php });

    row($edition, 'Edition', ', ', function($type, $value) {  ?>
        <span class="edition"><?php
            echo $type;
            echo wrap(' : ', $value) ?>
        </span>
    <?php });

    row($collection, 'Collection', ', ', function($name, $number) {  ?>
        <span class="edition"><?php
            echo $name;
            echo wrap(' (', $number, ')') ?>
        </span>
    <?php });

    row($event, 'Evènement', '', function($title, $number, $place, $date) {
        echo $title;
        echo wrap(' (', $number, ')');
        echo wrap(', ', $place);
        echo wrap(', ', $date);
        echo '.';
    });

    row($degree, 'Diplôme', '', function($level, $title){
        echo $level;
        echo $title;
    });

    row($abstract, 'Résumé', ', ', function($language, $content) {  ?>
        <span class="abstract"><?php
            echo wrap('(', $language, ') ');
            echo $content ?>
        </span>
    <?php });

    // TODO: topic pas topics
    row($topic, 'Mots-clés', '', function($type, $term) {  ?>
        <span class="topic"><?php
            echo '<p>';
            echo wrap('<em>', $type, '</em> : ');
            echo implode(', ', $term);
            echo '</p>';
             ?>

        </span>
    <?php });

    row($note, 'Notes', '<br />', function($type, $content) {  ?>
        <span class="note"><?php
            echo wrap($type, ' : ');
            echo $content ?>
        </span>
    <?php });

    row($link, 'Liens', '<br />', function($type, $url, $label, $date, $lastcheck, $checkstatus) {  ?>
        <span class="link"><?php
            echo wrap($type, ' : ');
            printf('<a href="%s">%s</a>', $url, $label ?: $url);
            // TODO : date, lastcheck, status
            ?>
        </span>
    <?php });

    row($doi, 'DOI');

    row($relations, 'Relations', '<br />', function($type, $ref) {  ?>
        <span class="note"><?php
            echo wrap($type, ' : ');
            echo $ref ?>
        </span>
    <?php });

    row($owner, 'Producteur', ', ');

    row($creation, 'Création', '', function($date, $by) {
        echo wrap($date, ' ');
        echo wrap('(', $by, ')');
        echo '.';
    });

    row($lastupdate, 'Maj', '', function($date, $by) {
        echo wrap($date, ' ');
        echo wrap('(', $by, ')');
        echo '.';
    });

    row($status, 'Statut');
    row($statusdate, 'Depuis le');

    row($errors, 'Erreurs', ', ', function($code, $value='', $message='') {  ?>
        <span class="organisation"><?php
            echo $message;
            echo wrap(' : <code>', $value, '</code>');
            echo wrap(' (', $code, ')');
            ?>
        </span>
    <?php });
?>
<tr>
    <th></th>
    <td>
        <a href="#" onclick="jQuery(this).next().toggle('normal'); return false;">Voir la notice importée</a>
        <pre class="imported" style="display: none"><?= $imported ?></pre>
    </td>
</tr>
</table>
