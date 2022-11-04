(function( $ ) {
    "use strict";
   
    
	$( document ).ready(function() {
      
		$("#plugin-update-report-date-chooser-button").click(function() {
            $("#plugin-update-report-date-chooser").toggle();
        });

        $("#plugin-update-report-cancel").click(function() {
            $("#plugin-update-report-date-chooser").hide();
        });

        $("#plugin-update-report-apply").click(function() {
            $("#plugin-update-report-date-chooser").hide();
            var startDate = $("#from_value").val();
            var endDate = $("#to_value").val();
            if (!startDate && endDate) {
                startDate = endDate;
            } else if (!endDate && startDate) {
                endDate = startDate;
            }
            getData(startDate, endDate);
            setDates(startDate, endDate, null);
        });

        $("#plugin-update-report-quick-today").click(function(e) {
            e.preventDefault();
            var label = $(this).text();
            $("#plugin-update-report-date-chooser").hide();
            var startDate = moment().format("YYYY-MM-DD");
            var endDate = moment().format("YYYY-MM-DD");
            setDates(startDate, endDate, label);
            getData(startDate, endDate);
        });

        $("#plugin-update-report-quick-thismonth").click(function(e) {
            e.preventDefault();
            var label = $(this).text();
            $("#plugin-update-report-date-chooser").hide();
            var startDate = moment().startOf('month').format("YYYY-MM-DD");
            var endDate = moment().endOf("month").format("YYYY-MM-DD");
            setDates(startDate, endDate, label);
            getData(startDate, endDate);
        });

        getData(moment().format("YYYY-MM-DD"), moment().add(0, 'days').format("YYYY-MM-DD"));
        setDates(moment().format("YYYY-MM-DD"), moment().add(0, 'days').format("YYYY-MM-DD"), "Today");

        $("#date-range").datepicker({
            maxDate: 0,
            firstDay: 0,
            numberOfMonths: [2,1],
            dateFormat: 'yy-mm-dd',
            beforeShowDay: function(date) {
                var instance = $( this ).data( "datepicker" );
                var date1 = $.datepicker.parseDate(instance.settings.dateFormat, $("#from_value").val());
                var date2 = $.datepicker.parseDate(instance.settings.dateFormat, $("#to_value").val());
                var isHightlight = date1 && ((date.getTime() == date1.getTime()) || (date2 && date >= date1 && date <= date2));
                return [true, isHightlight ? "dp-highlight" : ""];
            },
            onSelect: function(dateText, inst) {
                var js_date_format = getDateFormat();
                var instance = inst;
                var date1 = $.datepicker.parseDate(instance.settings.dateFormat, $("#from_value").val());
                var date2 = $.datepicker.parseDate(instance.settings.dateFormat, $("#to_value").val());
                var selectedDate = $.datepicker.parseDate(instance.settings.dateFormat, dateText);
                if (!date1 || date2) {
                    $(".from_value").val(dateText);
                    $("#plugin-update-report-start-date").text(moment(dateText).format(js_date_format));
                    $(".to_value").val("");
                } else if (selectedDate < date1) {
                    $(".to_value").val($("#from_value").val());
                    $(".from_value").val(dateText);
                    $("#plugin-update-report-end-date").text(moment(dateText).format(js_date_format));
                } else {
                    $(".to_value").val(dateText);
                    $("#plugin-update-report-end-date").text(moment(dateText).format(js_date_format));
                }
                $(this).datepicker();
            }
        });

    });

    function setDates(startDate, endDate, label) {
        var js_date_format = getDateFormat();
        var start_date_formatted = moment(startDate).format(js_date_format);
        var end_date_formatted = moment(endDate).format(js_date_format);
        $(".from_value").val(startDate);
        $(".to_value").val(endDate);
        $("#plugin-update-report-start-date").text(start_date_formatted);
        $("#plugin-update-report-end-date").text(end_date_formatted);
        if (label) {
            $("#plugin-update-report-button-label").text(label);
        } else {
            $("#plugin-update-report-button-label").text(start_date_formatted + " - " + end_date_formatted);
        }
        $("#date-range").datepicker( "refresh" );
    }

    function getData(startDate, endDate) {
        var start_date_utc = moment(startDate).format("YYYY-MM-DD");
        var end_date_utc = moment(endDate).format("YYYY-MM-DD");
        $(document).trigger('plugin_update_report_js_get_data', [start_date_utc, end_date_utc]);
    }

    $(document).on('plugin_update_report_js_get_data', function(event, start_date_utc, end_date_utc){
        if ($('#plugin-update-report-updates').length) {
            $('#plugin-update-report-updates').addClass('loading');
            var dataString = 'action=plugin_update_report_updates_data&start=' + start_date_utc + '&end=' + end_date_utc;
            var js_date_format = getDateFormat();
           
            $.ajax({
                type: "GET",
                url: ajaxurl,
                data: dataString,
                dataType: 'json',
                success: function(data, err) {
                    $("#plugin-update-report-plugin-update-count").text(data.total_plugins_updated);
                    $("#plugin-update-report-plugin-update-count2").text(data.total_unsuccessful_plugins_updated);
                    $("#plugin-update-report-plugin-updates-list").html("");
                    $("#plugin-update-report-plugin-updates-list2").html("");
                    var pdfInput = '';
                    $.each(data.updates, function( index, update ) {
                        var date_formatted = moment(update.date).format(js_date_format);
                     
                        var newUpdate = '<tr><td class="plugin_name">' + update.name + '</td><td class="plugin_description">' + update.description + '</td><td class="plugin_date">' + date_formatted + '</td><td class="plugin_oldversion"> Version: ' + update.version_before + ' </td><td class="plugin_newversion"> Version: ' + update.version_after + '</td></tr>';

                        var plugin_id = update.id;
                        var newUnsuccessfulUpdate = '<tr><td class="plugin_name">' + update.name + '</td><td class="plugin_description">' + update.description + '</td><td class="plugin_description">' + update.reason + '</td><td class="plugin_date">' + date_formatted + '</td><td><form method = "POST" action=""><input type = "hidden" value = "'+ plugin_id +'" name ="plugin_id"/><input type="submit" name = "delete" value ="Remove" class="button button-primary"/></form></td></tr>';
                       
                        if (newUpdate) {
                            pdfInput += newUpdate;
                        }

                        if (newUnsuccessfulUpdate) {
                            pdfInput += newUnsuccessfulUpdate;
                        }

                        if (update.update_status == 'successful') {
                            $("#plugin-update-report-plugin-updates-list").append(newUpdate);
                        }

                        if (update.update_status == 'unsuccessful') {
                            $("#plugin-update-report-plugin-updates-list2").append(newUnsuccessfulUpdate);
                        }
                       
                    });
                    $('#pdfdata').attr('value', JSON.stringify(data.updates));
                    
                    if (data.total_plugins_updated === 0) {
                        $("#plugin-update-report-plugin-updates-list").append('<li class="plugin-update-report-empty">' + plugin_update_report_data.nopluginupdates + '</li>');
                    }
                    $('#plugin-update-report-updates').removeClass('loading');

                    if (data.total_unsuccessful_plugins_updated === 0) {
                        $("#plugin-update-report-plugin-updates-list2").append('<li class="plugin-update-report-empty">' + plugin_update_report_data.nopluginupdates + '</li>');
                    }
                    $('#plugin-update-report-updates').removeClass('loading');
                }
            });
        }
    });
    
    
    function getDateFormat() {
        if (plugin_update_report_data) {
            return plugin_update_report_data.moment_date_format;
        } else {
            return 'DD/MM/YYYY';
        }
    } 
    
}(jQuery));
