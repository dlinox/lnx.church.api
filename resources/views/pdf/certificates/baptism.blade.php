<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        Partida de Bautismo
    </title>
    <style>
        * {
            padding: 0;
            margin: 0;
            box-sizing: border-box;
        }

        body {
            background-image: url("{{$background}}");
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            font-family: 'Courier New', Courier, monospace;
            font-weight: bolder;
            /* font-size: 14px; */
        }
    </style>
</head>

<body>
    <p class="box-parish" style=" font-weight: bold; position: absolute;font-size: 1em; top: 5.90cm; left: 3.8cm;width: 15cm;">{{$parish}}</p>
    <p class="box-book" style="position: absolute; top: 6.75cm; left: 10.8cm; width: 1.8cm;">{{$bookNumber}}</p>
    <p class="box-folio" style="position: absolute; top: 6.75cm; left: 14.2cm; width: 1.8cm;">{{$folioNumber}}</p>
    <p class="box-act" style="position: absolute; top: 6.75cm; left: 17.5cm; width: 1.8cm;">{{$actNumber}}</p>
    <p class="box-lastname" style="position: absolute; top: 8.46cm; left: 3.6cm; width: 15cm;">{{$fellows['1']['lastName']}}</p>
    <p class="box-name" style="position: absolute; top: 9.33cm; left: 3.6cm; width: 15cm;">{{$fellows['1']['name']}} </p>
    <p class="box-father" style="position: absolute; top: 10.15cm; left: 3.2cm; width: 15cm;"> {{$fellows['1']['family']['1']['name'] ?? ""}} </p>
    <p class="box-mother" style="position: absolute; top: 11.05cm; left: 3.2cm; width: 15cm;">{{$fellows['1']['family']['2']['name'] ?? ""}} </p>
    <p class="box-location-and-date-of-birth" style="position: absolute; top: 11.9cm; left: 7.2cm; width: 15cm;">{{ $fellows['1']['birthLocation'] }}, {{ $fellows['1']['birthDate'] }}</p>
    <p class="box-baptism-date" style="position: absolute; top: 12.7cm; left: 5.2cm; width: 15cm;">{{$sacramentDate}} </p>
    <p class="box-godfather" style="position: absolute; top: 13.6cm; left: 3.5cm; width: 15cm;">{{$godparents}} </p>
    <p class="box-observations" style="position: absolute; display: block; top: 14.1cm; left: 1.8cm; width: 17cm; line-height: 2.3; text-indent: 4.5cm;">{{$observations}}</p>
    <p class="box-day-name" style="position: absolute; display: block; top: 18.3cm; left: 9.2cm; width: 3cm;">Ilave </p>
    <p class="box-day" style="position: absolute; display: block; top: 18.3cm; left: 12.6cm; width: 3cm;">{{$printDate['day']}}</p>
    <p class="box-month" style="position: absolute; display: block; top: 18.3cm; left: 14.2cm; width: 3cm;">{{$printDate['month']}}</p>
    <p class="box-year" style="position: absolute; display: block; top: 18.3cm; left: 17.9cm; width: 3cm;">{{$printDate['year']}}</p>
</body>

</html>