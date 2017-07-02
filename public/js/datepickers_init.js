/**
 * Created by HP on 25-Jun-17.
 */
$(document).ready(function () {

    // adding and removing class form navbar on scrolling  and removing while it's on top
    $(window).scroll(function(){
        if($('.navbar').offset().top > 50){
            $('.navbar-fixed-top').addClass('top-nav-collapse');
        } else {
            $('.navbar-fixed-top').removeClass('top-nav-collapse');
        }
    });

    // carousel visible on ready
    $('.home-carousel').css('visibility', 'visible');

    // check date inputs
    $("#searchHome").submit(function(e){
        if($('#checkin').val() == '' || $('#checkout').val() == ''){
            e.preventDefault();
        }
    });


    var checkOutCalendar =   $("#checkout").flatpickr({
        altInput: true
    });

    var checkInCalendar = $("#checkin").flatpickr({
        minDate: new Date(),
        altInput: true,
        onChange: function(selectedDates, dateStr, instance){

            var checkin = selectedDates[0];
            var checkOut = new Date(checkin);
            checkOut.setDate(checkOut.getDate() + 1);
            checkOutCalendar.clear();
            checkOutCalendar.jumpToDate(checkOut);
            checkOutCalendar.setDate(checkOut);
            checkOutCalendar.set('minDate', checkOut);
            checkOutCalendar.open();
        }
    });

    // if while booking a room dates weren't specified, prompt the user to enter them
    $('#bookingForm').submit(function (event) {
        if($('#checkin').val() == '' || $('#checkout').val() == ''){
            scrollToDatepickers();
            checkInCalendar.open();
            event.preventDefault();
        }
    });

    // rewrite thoose dates to our form's hidden inputs
    $('input.flatpickr-input').change(function () {
        if($('#checkin').val() != 0){
            var checkinDate = $('#checkin').val();
            $('#checkin-hidden').val(checkinDate);
        }
        if($('#checkout').val() != 0){
            var checkOutDate = $('#checkout').val();
            $('#checkout-hidden').val(checkOutDate);
        }
    });




    function scrollToDatepickers(){
        var top = $('.prebook').offset().top;
        $('html, body').animate({scrollTop:top}, 2000, 'easeOutExpo');
    }

    // Checkboxes
    $('.search-terms input').iCheck({
        checkboxClass: 'icheckbox_flat'
    });
    // Checkboxes
    $('.checkbox-input').iCheck({
        checkboxClass: 'icheckbox_flat'
    });

    //form validation using jQuery Validate plagin

    $('#bookingForm').validate({
        rules: {
            email: {
                required: true,
                email: true
            },
            first_name: {
                required: true
            },
            last_name: {
                required: true
            }

        },
        messages: {
            email: {
                required: 'We do need your email',
                email: 'Please enter a valid email'
            },
            first_name: {
                required: 'We do need your name'
            },
            last_name: {
                required: 'We do need your last name'
            }

        }
    });

    $('#contactUs').validate({
        rules: {
            email: {
                required: true,
                email: true
            },
            name: {
                required: true
            },
            message: {
                required: true
            }

        },
        messages: {
            email: {
                required: 'We do need your email',
                email: 'Please enter a valid email'
            },
            name: {
                required: 'We do need your name'
            },
            message: {
                required: 'Please enter your message'
            }

        }
    });



});