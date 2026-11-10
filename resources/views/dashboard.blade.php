<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Dashboard</title>
<style>
  body { font-family: Arial, sans-serif; margin: 24px; }
  table { border-collapse: collapse; margin: 8px 0 24px; min-width: 360px; }
  th, td { border: 1px solid #ccc; padding: 6px 12px; text-align: left; }
  th { background: #f2f2f2; }
</style>
</head>
<body>
<h2>Dashboard</h2>
<p><a href="/calendar">Calendar</a></p>

<h3>Bookings by channel</h3>
<table>
  <tr><th>Channel</th><th>Bookings</th><th>Units</th></tr>
  @foreach ($byChannel as $r)
    <tr><td>{{ $r->name }}</td><td>{{ $r->bookings }}</td><td>{{ $r->units }}</td></tr>
  @endforeach
</table>

<h3>Occupancy, next 30 days</h3>
<table>
  <tr><th>Room type</th><th>Booked</th><th>Total</th><th>Occupancy %</th></tr>
  @foreach ($occupancy as $r)
    <tr><td>{{ $r->name }}</td><td>{{ $r->booked }}</td><td>{{ $r->total }}</td><td>{{ $r->occupancy_pct }}</td></tr>
  @endforeach
</table>

<h3>Bookings by status</h3>
<table>
  <tr><th>Status</th><th>Total</th></tr>
  @foreach ($byStatus as $r)
    <tr><td>{{ $r->status }}</td><td>{{ $r->total }}</td></tr>
  @endforeach
</table>

<h3>Busiest dates</h3>
<table>
  <tr><th>Date</th><th>Booked</th><th>Total</th></tr>
  @foreach ($busiest as $r)
    <tr><td>{{ $r->date }}</td><td>{{ $r->booked }}</td><td>{{ $r->total }}</td></tr>
  @endforeach
</table>
</body>
</html>