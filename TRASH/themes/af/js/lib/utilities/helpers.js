// Get date format
function getDateFormat(timestamp, format)
{
    var a = new Date(timestamp),
        months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
        year = a.getFullYear(),
        month = months[a.getMonth()],
        date = a.getDate(),
        hour = a.getHours(),
        min = a.getMinutes(),
        sec = a.getSeconds(),
        output = month + ' ' + date + ', ' + year;

    switch (format) {
        case 'mdy': output = month + ' ' + date + ', ' + year; break;
        case 'my': output = month + ' ' + year; break;
    }

    return output;
}

// Add zeros when missing for second decimal place
function addPriceZeros( num ) {
    var value = Number(num);
    var res = String(num).split(".");
    if(res.length == 1 || (res[1].length < 3)) {
        value = value.toFixed(2);
    }
    return value
}

// Affect elements when loading ajax
var ajax_loading = jQuery('#ajax-loading');      // Hide/show processing gif
var ajax_button = jQuery('.ajax-button');        // Disable/fade button
jQuery(document)
    .ajaxStart(function () {
        ajax_loading.show();
        ajax_button.attr('disabled', 'disabled').css('opacity','.3');
    })
    .ajaxStop(function () {
        ajax_loading.hide();
        ajax_button.removeAttr('disabled').css('opacity','1');
    });

// Target headers with the attribute sort-column
function sortTableInit() {
    $('.table-sortable th[sort-column]').on('click tap',function(e) {
        n = $(this).attr('sort-column');
        table = $(this).closest('table');
        table = table[0];
        sortTable(n, table);
    })
}

// Row sorting action
function sortTable(n, table) {
    var rows, switching, i, x, y, shouldSwitch, dir, switchcount = 0;
    //table = document.getElementById("portfolio-table");
    switching = true;
    //Set the sorting direction to ascending:
    dir = "asc"; 
    /*Make a loop that will continue until
    no switching has been done:*/
    while (switching) {
        //start by saying: no switching is done:
        switching = false;
        rows = table.rows;
        /*Loop through all table rows (except the
        first, which contains table headers):*/
        for (i = 1; i < (rows.length - 1); i++) {
            //start by saying there should be no switching:
            shouldSwitch = false;
            /*Get the two elements you want to compare,
            one from current row and one from the next:*/
            x = rows[i].getElementsByTagName("TD")[n];
            y = rows[i + 1].getElementsByTagName("TD")[n];
            /* if we encounter a header row, skip it*/
            if(x == undefined || y == undefined) {
                continue;
            }
            /*check if the two rows should switch place,
            based on the direction, asc or desc:*/
            if (dir == "asc") {
                if (x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase()) {
                    //if so, mark as a switch and break the loop:
                    shouldSwitch = true;
                    break;
                }
            } else if (dir == "desc") {
                if (x.innerHTML.toLowerCase() < y.innerHTML.toLowerCase()) {
                    //if so, mark as a switch and break the loop:
                    shouldSwitch = true;
                    break;
                }
            }
        }
        if (shouldSwitch) {
            /*If a switch has been marked, make the switch
            and mark that a switch has been done:*/
            rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
            switching = true;
            //Each time a switch is done, increase this count by 1:
            switchcount ++;      
        } else {
            /*If no switching has been done AND the direction is "asc",
            set the direction to "desc" and run the while loop again.*/
            if (switchcount == 0 && dir == "asc") {
                dir = "desc";
                switching = true;
            }
        }
    }
}

// Initialize on load
window.onload = sortTableInit;