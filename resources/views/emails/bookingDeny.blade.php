<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Booking</title>
</head>
<body>

  <p>Sorry! Your Booking has been denied. Please consider rescheduling with us.</p>

  <strong>WHERE</strong><br/>
  {{$space->name}}<br/>
  {{$space->address}} {{$space->city}}, {{$space->state}}<br/><br/>

  <strong>WHEN</strong><br/>
  {{$booking->day}} - {{$booking->time}}

  <br/>
  <br/>

  <strong>CONTACT</strong><br/>
  {{$space->email}}<br/>
  {{$space->phone_number}}</br>

</body>
</html>
