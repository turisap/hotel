/**
 * Created by HP on 23-May-17.
 */
$(document).ready(function () {



   /* // add a current room id to the query string of form action property
    var roomId = getParameterByName('id');
    $('#addPhotosForm').attr('action', '/admin/rooms/add-photos?id=' + roomId);

    // add a current room id to the query string of edit and delete room buttons
    $('#delete-room-button').attr('href', '/admin/rooms/add-photos?id=' + roomId);*/


    // prevent form from submission if there is no file to upload
    $('#btn-add').click(function () {
        if ($('#input-id').get(0).files.length === 0) {
            return false;
        }
    });




    // get id from a clicked button to process it through ajax
    $('.btn-popup-right').click(function () {

        var pictureId = ($(this).attr('id'));
        var roomId = getParameterByName('id');

        // set picture as the main one
        $.ajax({
            url: '/admin/rooms/set-main-picture',
            data: {
                picture_id: pictureId,
                room_id: roomId
            },
            type: 'POST',
            success: function(data){

                if(!data.error){
                    //alert(data);
                    // alert(pictureId);
                    // alert(roomId);

                    location.reload(true);
                }

            }
        });

        //alert(pictureId);
        //alert(roomId);

    });


    /*// ajax for photo deletion
    $('.btn-popup-left').click(function () {

        // current picture id
        var buttonId = ($(this).attr('id'));
        var array    = buttonId.split('-');
        var pictureId = parseInt(array[1]);

        // ajax
        $.ajax({
            url: '/admin/rooms/delete-photo',
            data: {
                picture_id: pictureId
            },
            type: 'POST',
            success: function(data){

                if(!data.error){

                    //alert(data);


                    location.reload(true);
                }

            }
        });

        //alert(pictureId);


    });*/



});

// this function gets url parameter by name (we need id only)
function getParameterByName( name ){
    var regexS = "[\\?&]"+name+"=([^&#]*)",
        regex = new RegExp( regexS ),
        results = regex.exec( window.location.search );
    if( results == null ){
        return "";
    } else{
        return decodeURIComponent(results[1].replace(/\+/g, " "));
    }
}


// POPOUTS
$(function() {


    //----- OPEN
    $('[data-popup-open]').on('click', function(e)  {
        var targeted_popup_class = jQuery(this).attr('data-popup-open');
        $('[data-popup="' + targeted_popup_class + '"]').fadeIn(350);

        e.preventDefault();
    });

    //----- CLOSE
    $('[data-popup-close]').on('click', function(e)  {
        var targeted_popup_class = jQuery(this).attr('data-popup-close');
        $('[data-popup="' + targeted_popup_class + '"]').fadeOut(350);

        e.preventDefault();
    });

});

