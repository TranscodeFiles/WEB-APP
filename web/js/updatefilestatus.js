$(function(){

    $convertedFiles = $(".file__transcoded");

    function getState(idInterval, $url , $id, $progressbar){

        //Si le fichier est transcoder alors on arrête
        if($progressbar.text() == "Transcoded" ){
            $progressbar.removeClass("progress-bar-striped active");

            clearInterval(idInterval);

        }

        $.ajax({
            type: 'GET',
            // On récupère l'url dans l'attribut data-target
            url: $url,
            // On passe le FormData en paramètre
            data: "",
            // On ne précise pas le contentType pour l'upload de fichier
            contentType: false,
            // Comme on passe directement un FormData on désactive le processData
            processData: false,
            success: function(response){

                $progressbar.css('width', response.percentage + '%');
                $progressbar.text(response.state);




            },
            error: function(response){



            }

        });


    }

    $convertedFiles.each(function(index){


        $idFile = $(this).data("id");
        $progressbar = $(this).find(".progress").children();
        $url = $(this).data("url-state");

        $progressbar.addClass("progress-bar-striped active");



        var idInterval = setInterval(function(){getState(idInterval, $url ,$idFile,$progressbar);}, 1000);





    });





});
