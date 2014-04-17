/**
 * This file is part of the 'Docalist Biblio' plugin.
 *
 * Copyright (C) 2012-2014 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 *
 * @package     Docalist
 * @subpackage  Biblio
 * @author      Daniel Ménard <daniel.menard@laposte.net>
 * @version     $Id$
 */

/**
 * Gère l'import de fichier dans une base.
 * 
 * @see http://shibashake.com/wordpress-theme/how-to-add-the-wordpress-3-5-media-manager-interface-part-2
 */
(function($) {
    // Gère l'ajout d'un ou de plusieurs fichiers dans la liste
    $('.add-file').on('click', function(){
        
        // Crée la boite Media Manager
        var uploader = wp.media({
            // title - titre de la boite de dialogue
            title: 'Sélectionner le fichier à importer',
            
            // button - paramètres du bouton principal (en bas à droite)
            button: {
                // text - titre du bouton
                text: 'Importer ce fichier',
                
                // close - True : ferme la boite quand on clique "choisir ce fichier", false : garde la bvoite ouverte
                close: true,
            },
            
            // multiple - définit le mode de sélection des médias
            // - false : sélection unique. dès qu'on clique sur un media, cela désélectionne le média précédemment sélectionné
            // - 'add' : sélection multiple, chaque clic sélectionne ou désélectionne le média
            // - 'reset' : sélection multiple, mais il faut utiliser ctrl ou shift
            // - true : synonyme de 'reset'.
            //
            multiple: 'add',
            
            // frame - indique le type d'interface à utiliser
            // - 'select' est l'interface par défaut. 
            //    Elle affiche un écran avec deux onglets (en haut) qui permettent soit d'uploader un fichier, 
            //    soit de choisir un fichier existant dans la bibliothèque de médias. 
            // - 'post' est l'interface qu'on obtient quand on clique "ajouter un media" depuis l'écran d'édition d'un post ou d'une page.
            //    C'est une version plus riche avec en plus un menu à gauche. La première option permet d'obtenir l'interface 'select', 
            //    la seconde permet de créer une gallerie d'images, la troisième permet d'insérer un média depuis une url.
            frame: 'select',
            
            // state - indique l'état initial de l'interface. Dépend du type de frame utilisé.
            // - pour frame='select' (cf. MediaFrame.Select.createStates() dans media-views.js), un seul état, 'library'.
            //   obtenu en faisant : console.log(new wp.media.view.MediaFrame.Select().states.models)
            // - pour frame='post' (cf. MediaFrame.Post.createStates() dans media-views.js), cinq états possibles :
            //   - 'insert'
            //   - 'gallery'
            //   - 'embed' : insérer depuis une url
            //   - 'gallery-edit' : modifier la gallerie qui figure dans l'article, .
            //   - 'gallery-library' : ajouter des médias à la gallerie
            state: 'library',
            
            // Allow gallery to be edited, si state='gallery-edit' 
            // editing: false,
            
            // Définit ce qui est affiché dans la bibliothèque de médias
            library: {
                type: 'text'
            }
        })
        
        // Réduit un peu la taille de la boite pour que le titre reste visible
        .on('ready', function() {
            $( '.media-modal' ).addClass('smaller');
        })
        
        // Affiche les fichiers sélectionnés quand l'utilisateur clique sur ok
        .on('select', function() {
            // Récupère la liste des fichiers sélectionnés
            var selection = uploader.state().get('selection');
            
            // Affiche les fichiers choisis en utilisant le template de la page
            var template = $('#file-template').html();
            selection.each(function(file) {
                var html = template.replace(/{(.*?)}/g, function(match, attr) {
                    return file.get(attr);
                });
                
                
                $('#file-list').append(html);
                
            });
            
            // Active le bouton "Lancer l'import"
            $('.run-import').attr('disabled', false);
        })
        
        // Ouvre la boite
        .open()
        
        // pré-sélectionner des fichiers à l'ouverture : http://stackoverflow.com/a/13963342        
    });
    
    // Supprime un fichier de la liste
    $(document).on('click', '.remove-file', function(event) {
        event.preventDefault();
        ($('#file-list li').length <= 1) && $('.run-import').attr('disabled', true);
        $(this).parents('li').hide('slow', function() {$(this).remove();});
    });
    
    // Permet de trier les fichiers à importer dans l'ordre que l'on veut
    $('#file-list').sortable({ 
        axis: "y",
        containment: "parent",
        handle: '.file-icon',
        cursor: "move",
        helper: 'clone',
        placeholder: 'sortable-placeholder',
        forcePlaceholderSize: true,
        opacity: 0.65            
    });
}(jQuery));