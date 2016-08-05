<!DOCTYPE html>
<html>
    <head>
        <meta name="viewport" content="initial-scale=1.0, user-scalable=no">
        <meta charset="utf-8">

        <title>Directions service</title>
        <style>
            html, body {
                height: 100%;
            }
            #map {
                height: 40%;
            }
        </style>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
        <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    </head>
    <body>
        <?php echo csrf_field(); ?>
        <h3>Routes</h3>
        <div id="locationField">
            <input id="autocomplete" placeholder="Enter address" onFocus="geolocate()" type="text">
            <input id="autocomplete2" placeholder="Enter address" onFocus="geolocate()" type="text">
        </div>
        <button id="create_route">Create route</button>
        <div id="map"></div>
        <h3>Countries Statistic</h3>
        <div id="piechart" style="width: 900px; height: 500px;"></div>

        <script>
            // INIT
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('input[name="_token"]').val()
                }
            });
            $('#autocomplete').val('');
            $('#autocomplete2').val('');
            //CHART
            var chart;
            var options = {};
            google.charts.load('current', {'packages':['corechart']});
            google.charts.setOnLoadCallback(drawChart);
            function drawChart() {
                <?php
                echo 'var count='.count($countries).';';
                ?>
                var data = google.visualization.arrayToDataTable( [
                    <?php
                    echo '[\'Country\', \'Count\'],';
                    $total = count($countries);
                    $counter = 0;
                    foreach ($countries as $country){
                        $counter++;
                        echo '[\'' . $country->name . '\', ' . $country->count . ']';
                        if($counter != $total) echo ',';
                    };?>
                ]);
                chart = new google.visualization.PieChart(document.getElementById('piechart'));
                if (count > 0){
                    chart.draw(data, options);
                }
            }
            //AUTOCOMPLETE
            var autocomplete;
            var autocomplete2;
            function initAutocomplete() {
                // AUTOCOMPLITE INIT
                autocomplete = new google.maps.places.Autocomplete((document.getElementById('autocomplete')), {types: ['(cities)']});
                autocomplete2 = new google.maps.places.Autocomplete((document.getElementById('autocomplete2')),{types: ['(cities)']});
                //DIRECTION SERVICE INIT
                var directionsService = new google.maps.DirectionsService;
                var directionsDisplay = new google.maps.DirectionsRenderer;
                var map = new google.maps.Map(document.getElementById('map'), {
                    zoom: 3,
                    center: {lat: 51.15, lng: -9.64}
                });
                directionsDisplay.setMap(map);
                var onClickHandler = function() {

                    var value_input1 = $.trim($("#autocomplete").val());
                    var value_input2 = $.trim($("#autocomplete2").val());

                    if( value_input1.length > 0 && value_input2.length > 0 ){
                        calculateAndDisplayRoute(directionsService, directionsDisplay);
                        var place1 = autocomplete.getPlace();
                        var place2 = autocomplete2.getPlace();

                        var countyDetect = function(place) {
                            for (var i = 0; i < place.address_components.length; i++) {
                                var addressType = place.address_components[i].types[0];
                                if (addressType == 'country') {
                                    var countryName = place.address_components[i].long_name;
                                    return countryName;
                                }
                            }
                        };

                        var array_to_json = [];
                        var data = {};
                        var country_name1 = countyDetect(place1);
                        var country_name2 = countyDetect(place2);
                        array_to_json.push({country_name: country_name1});
                        array_to_json.push({country_name: country_name2});
                        data['points'] = array_to_json;

                        $.ajax({
                            url: '<?php echo route('route.statistics'); ?>',
                            type: 'POST',
                            data: data,
                            dataType: "json",
                            success: function(data1) {
                                var data_array = [['Country', 'Count']];
                                data1.forEach(function(item, i, arr) {
                                    data_array.push([ item['country_name'], parseInt(item['count'])]);
                                });
                                var data2 = google.visualization.arrayToDataTable(data_array);
                                chart.draw(data2, options);
                            },
                            error: function(data)   {
                                console.log('Ajax failed');
                                console.log(data);
                            }
                        });
                    }
                };
                document.getElementById("create_route").addEventListener("click", onClickHandler);
            }

            function geolocate() {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(function(position) {
                        var geolocation = {
                            lat: position.coords.latitude,
                            lng: position.coords.longitude
                        };
                        var circle = new google.maps.Circle({
                            center: geolocation,
                            radius: position.coords.accuracy
                        });
                        autocomplete.setBounds(circle.getBounds());
                    });
                }
            }

            function calculateAndDisplayRoute(directionsService, directionsDisplay) {
                $value = document.getElementById('autocomplete').value;
                directionsService.route({
                    origin: document.getElementById('autocomplete').value,
                    destination: document.getElementById('autocomplete2').value,
                    travelMode: google.maps.TravelMode.DRIVING
                }, function(response, status) {
                    if (status === google.maps.DirectionsStatus.OK) {
                        directionsDisplay.setDirections(response);
                    } else {
                        console.log('Directions request failed due to ' + status);
                    }
                });
            }
        </script>
        <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDH-CJHXJF6v66Q61M4vbH95GT2T1jUWH0&libraries=places&signed_in=true&callback=initAutocomplete"></script>
    </body>
</html>