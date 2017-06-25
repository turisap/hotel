/**
 * Created by HP on 25-Jun-17.
 */
$(document).ready(function () {

    // carousel visible on ready
    $('.home-carousel').css('visibility', 'visible');

    // check date inputs
    $("form").submit(function(e){
        if($('#checkin').val() == '' || $('#checkout').val() ==''){
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
});