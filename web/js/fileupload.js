$(function(){


    $('#file_attachment').on('change', function(){

        // On récupère les élément du DOM
        var $this = $(this);
        var $form = $(this).parents("form");
        var $loadingBar = $this.siblings('.progress').children();
        var $label = $this.siblings('label');
        var $input = $this.siblings('input[type=hidden]');
        var $submit = $('#file_submit');

        // On récupère le fichier
        var file = $this[0].files[0];




        // On crée un FormData, c'est ce qu'on va envoyer au serveur
        var data = new FormData();
        data.append('file[attachment]', file);
        data.append('file[_token]', $form.find("#file__token").val());



        //lors du submit du form
        $submit.on("click",function(e){
            // On supprime le bouton et on affiche la barre de progression
            $label.remove();
            $loadingBar.parent().removeClass('hidden');

            e.preventDefault();
            // On envoie la requête AJAX
            $.ajax({
                type: 'POST',
                // On récupère l'url dans l'attribut data-target
                url: window.location.pathname,
                // On passe le FormData en paramètre
                data: data,
                // On ne précise pas le contentType pour l'upload de fichier
                contentType: false,
                // Comme on passe directement un FormData on désactive le processData
                processData: false,
                success: function(response){
                    // On affiche le nom du fichier dans la barre de chargement
                    $loadingBar
                        .css('width', '100%')
                        .text(file.name);
                    // On met l'id du File fraichement créé dans notre input
                    $input.val(response.id);
                    // On supprimer le <input type="file">
                    $this.remove();
                    $submit.remove();
                },
                error: function(response){
                    // On indique qu'il y a une erreur dans la barre de chargement
                    $loadingBar
                        .css('width', '100%')
                        .removeClass('progress-bar-success')
                        .addClass('progress-bar-danger')
                        .text('Erreur : ' + response.responseText);
                    // On supprime le <input type="file">
                    $this.remove();

                },
                xhr: function(){
                    var xhr = $.ajaxSettings.xhr();

                    xhr.upload.addEventListener("progress", function(e){
                        $loadingBar.css('width', e.loaded / e.total * 100 + '%');
                        $loadingBar.text(Math.floor(e.loaded / e.total * 100) + '%');
                    }, false);

                    return xhr;
                }
            });
        });

    });

});
