$(function(){

    $convertedFiles = $(".file__transcoded");

    function getState(idInterval, $url, $progressbar, $urlDownload, $urlRetry, $urlDelete, $buttons){

        //Si le fichier est transcoder alors on arrête
        if ($progressbar.text() == "Transcoded") {
            $progressbar.removeClass("progress-bar-striped active");
            successButton($buttons, $urlDownload, $urlDelete);
            clearInterval(idInterval);
            return;
        }

        $progressbar.addClass("progress-bar-striped active");

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

                var percentage = response.percentage;

                if (typeof percentage != 'undefined' && percentage == 0) {
                    retryButton($buttons, $urlRetry, $urlDelete);
                    clearInterval(idInterval);
                } else {
                    $progressbar.css('width', response.percentage + '%');
                }

                $progressbar.text(response.state);

            },
            error: function(response){
            }
        });
    }

    /**
     * Display button for download transcoded file and delete button
     *
     * @param $parent
     * @param $urlDownload
     * @param $urlDelete
     */
    function successButton($parent, $urlDownload, $urlDelete) {
        var $buttonSuccess = "<div class=\"control-group\"><a href=\"" + $urlDownload + "\" class=\"btn btn-primary\">Download</a><a href=\"" + $urlDelete + "\" class=\"btn btn-danger\">Delete</a></div>";
        $parent.append($buttonSuccess);
    }


    /**
     * Display button retry button transcode file and delete button
     * 
     * @param $parent
     * @param $urlRetry
     * @param $urlDelete
     */
    function retryButton($parent, $urlRetry, $urlDelete) {
        var $buttonRetry = "<div class=\"control-group\"><a href=\"" + $urlRetry + "\" class=\"btn btn-info\">Retry</a><a href=\"" + $urlDelete + "\" class=\"btn btn-danger\">Delete</a></div>";
        $parent.append($buttonRetry);
    }

    $convertedFiles.each(function(){

        var $parent = $(this),
            $state = $parent.data("currente-state"),
            $statePercentage = $parent.data("currente-state-percentage"),
            $urlDownload = $parent.data("url-download"),
            $urlDelete = $parent.data("url-delete"),
            $urlRetry = $parent.data("url-retry"),
            $buttons = $parent.find("div#buttons");

        if (typeof $state != 'undefined' && $state == "Transcoded" && typeof $statePercentage != 'undefined' && $statePercentage == "100") {
            successButton($buttons, $urlDownload, $urlDelete);
            return;
        }

        var $progressbar = $parent.find(".progress").children(),
            $url = $parent.data("url-state");

        var idInterval = setInterval(function(){getState(idInterval, $url, $progressbar, $urlDownload, $urlRetry, $urlDelete, $buttons);}, 1000);
    });
});
