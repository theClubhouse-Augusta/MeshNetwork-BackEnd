<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Booking</title>
</head>
<body>

  <p>Congratulations! Your Booking has been approved.</p>

  <strong>WHERE</strong><br/>
  {{$space->name}}<br/>
  {{$space->address}} {{$space->city}}, {{$space->state}}<br/><br/>

  <strong>WHEN</strong><br/>
  {{$booking->start}}<br/>
  TO<br/>
  {{$booking->end}}

  <br/>
  <br/>

  <strong>CONTACT</strong><br/>
  {{$space->email}}<br/>
  {{$space->phone_number}}</br>

</body>
</html>
