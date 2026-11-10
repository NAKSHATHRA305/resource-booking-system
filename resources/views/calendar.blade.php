<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Room Availability</title>
<style>
  body { font-family: Arial, sans-serif; margin: 24px; }
  table { border-collapse: collapse; margin: 12px 0; }
  th, td { border: 1px solid #ccc; padding: 6px 10px; text-align: center; }
  th.room { text-align: left; }
  td.ok { background: #d9f2d9; }
  td.low { background: #fff3c4; }
  td.none { background: #f5c6c6; }
  form { margin-top: 20px; display: flex; gap: 8px; flex-wrap: wrap; align-items: end; }
  label { display: flex; flex-direction: column; font-size: 13px; }
  #msg { margin-top: 10px; font-weight: bold; }
  .err { color: #b00020; } .good { color: #1b7f1b; }
</style>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>
<body>
<h2>Room Availability</h2>
<button id="prev">&laquo; Previous 14 days</button>
<button id="next">Next 14 days &raquo;</button>
<div id="grid"></div>

<h3>New booking (direct channel)</h3>
<form id="bookForm">
  <label>Room type
    <select name="room_type_id">
      @foreach ($roomTypes as $rt)
        <option value="{{ $rt->id }}">{{ $rt->name }}</option>
      @endforeach
    </select>
  </label>
  <label>Guest name <input name="guest_name" required></label>
  <label>Check-in <input type="date" name="check_in" required></label>
  <label>Check-out <input type="date" name="check_out" required></label>
  <label>Units <input type="number" name="units" value="1" min="1" max="10"></label>
  <button type="submit">Book</button>
</form>
<div id="msg"></div>

<script>
  $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

  var from = '{{ today()->toDateString() }}';

  function shiftDate(str, n) {
    var d = new Date(str + 'T00:00:00Z');
    d.setUTCDate(d.getUTCDate() + n);
    return d.toISOString().slice(0, 10);
  }

  function loadGrid() {
    $.getJSON('/availability', { from: from }, function (res) {
      var rooms = {}, dates = [];
      res.rows.forEach(function (r) {
        if (!rooms[r.name]) rooms[r.name] = [];
        rooms[r.name].push(r);
        if (dates.indexOf(r.date) === -1) dates.push(r.date);
      });

      var html = '<table><tr><th class="room">Room</th>';
      dates.forEach(function (d) { html += '<th>' + d.slice(5) + '</th>'; });
      html += '</tr>';
      Object.keys(rooms).forEach(function (name) {
        html += '<tr><th class="room">' + name + '</th>';
        rooms[name].forEach(function (c) {
          var cls = c.remaining == 0 ? 'none' : (c.remaining <= 1 ? 'low' : 'ok');
          html += '<td class="' + cls + '">' + c.remaining + '</td>';
        });
        html += '</tr>';
      });
      html += '</table>';
      $('#grid').html(html);
    });
  }

  $('#prev').click(function () { from = shiftDate(from, -14); loadGrid(); });
  $('#next').click(function () { from = shiftDate(from, 14); loadGrid(); });

  $('#bookForm').on('submit', function (e) {
    e.preventDefault();
    $.post('/bookings', $(this).serialize())
      .done(function (res) {
        $('#msg').attr('class', 'good').text('Booked. Booking #' + res.id);
        loadGrid();
      })
      .fail(function (xhr) {
        var m = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Request failed';
        $('#msg').attr('class', 'err').text(m);
      });
  });

  loadGrid();
</script>
</body>
</html>