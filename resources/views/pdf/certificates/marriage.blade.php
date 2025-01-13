<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        Partida de Confirmación
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
        }
    </style>
</head>

<body>
    <p class="box-parish" style="position: absolute; bottom: 24.4cm; left: 3.8cm; width: 15.5cm;">{{$parish}}</p>
    <p class="box-book" style="position: absolute; bottom: 23.85cm; left: 11.3cm; width: 2cm;">{{$bookNumber}}</p>
    <p class="box-folio" style="position: absolute; bottom: 23.85cm; left: 14.6cm; width: 2cm;"> {{$folioNumber}}</p>
    <p class="box-act" style="position: absolute; bottom: 23.85cm; left: 18cm; width: 2cm;"> {{$actNumber}}</p>

    <p class="box-husband-name" style="position: absolute; bottom: 21.75cm; left: 5.6cm; width: 15cm;">{{$fellows['2']['name']}} {{$fellows['2']['lastName']}}</p>
    <p class="box-husband-location-and-date-of-birth" style="position: absolute; bottom: 21.15cm; left: 7cm; width: 15cm;">{{ $fellows['2']['birthLocation'] }} {{ $fellows['2']['birthDate'] }}</p>
    <p class="box-husband-father" style="position: absolute; bottom: 20.63cm; left: 3.1cm; width: 15cm;"> {{$fellows['2']['family']['1']['name'] ?? ""}} </p>
    <p class="box-husband-mother" style="position: absolute; bottom: 20.06cm; left: 3.1cm; width: 15cm;"> {{$fellows['2']['family']['2']['name'] ?? ""}} </p>
    <p class="box-husband-parish-baptism" style="position: absolute; display: block; bottom: 19.5cm; left: 6.6cm; width: 15cm;">{{$fellows['2']['baptism']['parish']}} </p>
    <p class="box-husband-date-baptism" style="position: absolute; display: block; bottom: 18.9cm; left: 5.5cm; width: 15cm;"> {{$fellows['2']['baptism']['date']}} </p>


    <p class="box-wife-name" style="position: absolute; bottom: 17.4cm; left: 5.6cm; width: 15cm;">{{$fellows['3']['name']}} {{$fellows['3']['lastName']}}</p>
    <p class="box-wife-location-and-date-of-birth" style="position: absolute; bottom: 16.85cm; left: 7cm; width: 15cm;">{{ $fellows['3']['birthLocation'] }} {{ $fellows['3']['birthDate'] }}</p>
    <p class="box-husband-father" style="position: absolute; bottom: 16.26cm; left: 3.1cm; width: 15cm;"> {{$fellows['3']['family']['1']['name'] ?? ""}} </p>
    <p class="box-husband-mother" style="position: absolute; bottom: 15.67cm; left: 3.1cm; width: 15cm;"> {{$fellows['3']['family']['2']['name'] ?? ""}} </p>
    <p class="box-husband-parish-baptism" style="position: absolute; display: block; bottom: 15.12cm; left: 6.6cm; width: 15cm;">{{$fellows['3']['baptism']['parish']}} </p>
    <p class="box-husband-date-baptism" style="position: absolute; display: block; bottom: 14.55cm; left: 5.5cm; width: 15cm;"> {{$fellows['3']['baptism']['date']}} </p>

    <p class="box-sacrament-date" style="position: absolute; bottom: 13cm; left: 5.7cm; width: 15cm;">{{$sacramentDate}} </p>
    <p class="box-minister" style="position: absolute; bottom: 12.45cm; left: 5.3cm; width: 15cm;">{{$minister}} </p>
    <p class="box-godfather" style="position: absolute; bottom: 11.85cm; left: 3.6cm; width: 15cm;">{{$godparents}} </p>
    <p class="box-witness" style="position: absolute; bottom: 11.3cm; left: 3.6cm; width: 15cm;"> - </p>
    <p class="box-observations" style="position: absolute; display: block; bottom: 10.6cm; left: 1.8cm; width: 17cm; line-height: 2.3; text-indent: 4.5cm;">{{$observations}}</p>


    <p class="box-day-name" style="position: absolute; display: block; bottom: 8.5cm; left: 9.2cm; width: 3cm;">El Collao</p>
    <p class="box-day" style="position: absolute; display: block; bottom: 8.5cm; left: 12.6cm; width: 3cm;">{{$printDate['day']}}</p>
    <p class="box-month" style="position: absolute; display: block; bottom: 8.5cm; left: 14.2cm; width: 3cm;">{{$printDate['month']}}</p>
    <p class="box-year" style="position: absolute; display: block; bottom: 8.5cm; left: 17.9cm; width: 3cm;">{{$printDate['year']}}</p>



</body>

</html>