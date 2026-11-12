<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Resource Booking</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Zen+Kaku+Gothic+New:wght@400;500;700;900&display=swap" rel="stylesheet">
<style>
:root{
  --paper:#F5F6F8; --surface:#FFFFFF; --ink:#171A21; --mute:#5E6573; --rule:#CDD2DB;
  --indigo:#2B3FA6; --indigo-d:#1D2C7A; --err:#B3261E;
  --font:"Zen Kaku Gothic New","Hiragino Sans","Yu Gothic",system-ui,-apple-system,"Segoe UI",Arial,sans-serif;
}
*{box-sizing:border-box}
body{margin:0;background:var(--paper);color:var(--ink);font-family:var(--font);font-size:15px;line-height:1.55;font-variant-numeric:tabular-nums}
.wrap{max-width:1180px;margin:0 auto;padding:0 28px}
button{font-family:inherit}
:focus-visible{outline:2px solid var(--indigo);outline-offset:2px}

.bar{border-bottom:1px solid var(--rule);background:var(--surface)}
.bar-in{display:flex;justify-content:space-between;align-items:center;height:60px}
.mark{font-weight:900;font-size:18px;letter-spacing:-.01em}
.btn{background:var(--indigo);color:#fff;border:0;border-radius:3px;padding:10px 18px;font-size:14px;font-weight:700;cursor:pointer}
.btn:hover{background:var(--indigo-d)}
.btn.plain{background:transparent;color:var(--ink);border:1px solid var(--rule);font-weight:500;padding:7px 14px}
.btn.plain:hover{border-color:var(--ink)}
.link{background:none;border:0;color:var(--indigo);font:inherit;font-weight:700;cursor:pointer;padding:0;text-decoration:underline}

.lede{padding:48px 0 32px}
.lede h1{margin:0;font-size:clamp(38px,6.4vw,72px);line-height:1.02;font-weight:900;letter-spacing:-.03em}
.lede p{margin:16px 0 0;font-size:18px;color:var(--mute);max-width:62ch}
.lede p b{color:var(--ink);font-weight:700}

.chart{background:var(--surface);border-top:1px solid var(--ink);border-bottom:1px solid var(--rule);padding:18px 0 14px;margin:0 0 56px}
.tools{display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:12px}
#range{margin-left:10px;font-weight:700}
.hint{margin-left:auto;color:var(--mute);font-size:13px}
.gridwrap{overflow-x:auto;padding-bottom:4px}
table.grid{border-collapse:separate;border-spacing:2px;width:100%}
.grid th{font-weight:500;font-size:12px;color:var(--mute);text-align:center;padding:4px 0 6px}
.grid th b{display:block;font-size:17px;color:var(--ink);font-weight:700}
.grid th.mon{border-left:2px solid var(--ink)}
.grid th.today{box-shadow:inset 0 -3px 0 var(--indigo)}
.grid th.today b{color:var(--indigo)}
.grid th.rm{text-align:left;color:var(--ink);font-size:15px;font-weight:700;min-width:118px;position:sticky;left:0;background:var(--surface);z-index:1}
td.cell{text-align:center;padding:13px 0;min-width:46px;border-radius:2px;font-weight:700;font-size:15px;cursor:pointer}
td.cell.mon{border-left:2px solid var(--ink)}
td.cell:hover:not(.full){outline:2px solid var(--ink);outline-offset:-2px}
td.full{cursor:not-allowed;color:var(--mute);font-weight:500;
  background:repeating-linear-gradient(135deg,#D3D7DE 0 1px,transparent 1px 6px),#EDEFF3}
td.closed{color:var(--ink);font-weight:700;
  background:repeating-linear-gradient(45deg,#B9BFCC 0 2px,transparent 2px 6px),#F5F6F8}
#peek{margin-top:10px;min-height:22px;font-size:14px}
.legend{display:flex;gap:22px;flex-wrap:wrap;margin-top:8px;font-size:13px;color:var(--mute);align-items:center}
.legend i{display:inline-block;width:26px;height:12px;border-radius:2px;vertical-align:-2px;margin-right:7px}
.sw-free{background:rgba(43,63,166,.12)} .sw-busy{background:rgba(43,63,166,.72)}
.sw-full{background:repeating-linear-gradient(135deg,#D3D7DE 0 1px,transparent 1px 6px),#EDEFF3}
.sw-closed{background:repeating-linear-gradient(45deg,#B9BFCC 0 2px,transparent 2px 6px),#F5F6F8;border:1px solid var(--rule)}

details.stops{margin-top:16px;border-top:1px solid var(--rule);padding-top:12px}
details.stops summary{cursor:pointer;font-weight:700}
.stop-row{display:flex;gap:10px;flex-wrap:wrap;align-items:end;margin-top:12px}
.stop-row label{display:flex;flex-direction:column;font-size:13px;font-weight:700;gap:6px}
.stop-row select,.stop-row input{padding:9px 10px;border:1px solid var(--rule);border-radius:3px;font:inherit;font-weight:400;background:#fff;color:var(--ink)}

.cols{display:grid;grid-template-columns:1.25fr 1fr;gap:64px;margin-bottom:56px}
h2{margin:0 0 4px;font-size:20px;font-weight:900;letter-spacing:-.01em}
.sub{margin:0 0 14px;color:var(--mute);font-size:14px}
table.t{width:100%;border-collapse:collapse}
.t th{text-align:left;font-size:13px;font-weight:500;color:var(--mute);padding:8px 10px 8px 0;border-bottom:1px solid var(--ink)}
.t td{padding:12px 10px 12px 0;border-bottom:1px solid var(--rule);vertical-align:middle}
.t .num{text-align:right;white-space:nowrap}
.t th.num{text-align:right}
.meter{width:34%}
.meter span{display:block;height:6px;background:#E3E6EC}
.meter i{display:block;height:100%;background:var(--indigo)}
.empty{color:var(--mute);padding:14px 0}
.ledger{margin-bottom:90px}
.ledger .t td:first-child{color:var(--mute)}
.status-ok{color:var(--mute)}
.status-x{color:var(--err)}

#overlay{position:fixed;inset:0;background:rgba(23,26,33,.4);opacity:0;pointer-events:none;transition:opacity .2s;z-index:20}
#overlay.open{opacity:1;pointer-events:auto}
#drawer{position:fixed;top:0;right:0;bottom:0;width:400px;max-width:100%;background:var(--surface);z-index:30;transform:translateX(100%);transition:transform .22s;padding:30px 26px;border-left:1px solid var(--ink);overflow:auto}
#drawer.open{transform:none}
#drawer h3{margin:0 0 4px;font-size:24px;font-weight:900;letter-spacing:-.02em}
#drawer .sub{margin-bottom:8px}
#drawer label{display:block;font-size:13px;font-weight:700;margin:18px 0 6px}
#drawer input,#drawer select{width:100%;padding:11px 12px;border:1px solid var(--rule);border-radius:3px;font-size:15px;font-family:inherit;background:#fff;color:var(--ink)}
#drawer input:focus,#drawer select:focus{outline:2px solid var(--indigo);border-color:var(--indigo)}
.row2{display:grid;grid-template-columns:1fr 1fr;gap:12px}
#nights{margin-top:10px;font-size:14px;font-weight:700;color:var(--indigo);min-height:21px}
#quote{font-size:14px;font-weight:700;min-height:21px}
#f-err{color:var(--err);font-size:14px;font-weight:700;min-height:22px;margin-top:10px}
.actions{display:flex;gap:10px;margin-top:16px}
.actions .btn{flex:1}
#toast{position:fixed;bottom:26px;left:50%;transform:translateX(-50%);background:var(--ink);color:#fff;padding:12px 22px;border-radius:3px;font-size:14px;font-weight:700;opacity:0;pointer-events:none;transition:opacity .25s;z-index:40}
#toast.show{opacity:1}

@media (max-width:860px){
  .wrap{padding:0 16px}
  .cols{grid-template-columns:1fr;gap:44px}
  .lede{padding-top:32px}
  .hint{margin-left:0;width:100%}
}
@media (prefers-reduced-motion:reduce){*{transition:none!important}}
html{-webkit-text-size-adjust:100%}
@media (max-width:640px){
  .wrap{padding:0 14px}
  .bar-in{height:auto;padding:10px 0;flex-wrap:wrap;gap:8px}
  .lede{padding:24px 0 20px}
  .lede h1{font-size:clamp(30px,9vw,42px)}
  .lede p{font-size:16px}
  .tools .hint{display:none}
  #range{margin-left:0;width:100%}
  td.cell{min-width:42px;padding:15px 0;font-size:16px}
  .grid th.rm{min-width:84px;font-size:13px}
  .legend{gap:10px 16px}
  .stop-row{flex-direction:column;align-items:stretch}
  .stop-row select,.stop-row input,.stop-row .btn{width:100%;font-size:16px}
  #drawer{width:100%;padding:22px 18px}
  #drawer input,#drawer select{font-size:16px}
  .t td,.t th{padding-right:8px}
  .meter{display:none}
  .btn{padding:12px 16px}
}
</style>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>
<body>

<header class="bar">
  <div class="wrap bar-in">
    <span class="mark">Resource Booking</span>
    @if ($isAdmin)
      <span>
        <button class="btn" id="newBtn">New booking</button>
        <form method="post" action="/logout" style="display:inline">@csrf<button class="btn plain" type="submit">Log out</button></form>
      </span>
    @else
      <a class="btn" href="/login" style="text-decoration:none;display:inline-block">Admin login</a>
    @endif
  </div>
</header>

<main class="wrap">
  <section class="lede">
    <h1>{{ today()->format('l j F') }}</h1>
    <p id="lede-p">Loading the latest figures.</p>
  </section>

  <section class="chart">
    <div class="tools">
      <button class="btn plain" id="prev">Previous</button>
      <button class="btn plain" id="todayBtn">Today</button>
      <button class="btn plain" id="next">Next</button>
      <span id="range"></span>
      <span class="hint">Rooms left per night. Click a cell to book it.</span>
    </div>
    <div class="gridwrap" id="grid"></div>
    <div id="peek"></div>
    <div class="legend">
      <span><i class="sw-free"></i>Lighter: more rooms free</span>
      <span><i class="sw-busy"></i>Darker: nearly full</span>
      <span><i class="sw-full"></i>Hatched: sold out</span>
      <span><i class="sw-closed"></i>Crossed: sales stopped</span>
    </div>

    @if ($isAdmin)
    <details class="stops">
      <summary>Stop or reopen sales</summary>
      <form id="stopForm" class="stop-row">
        <label>Room type
          <select name="room_type_id">
            @foreach ($roomTypes as $rt)
              <option value="{{ $rt->id }}">{{ $rt->name }}</option>
            @endforeach
          </select>
        </label>
        <label>From <input type="date" name="from" required></label>
        <label>To <input type="date" name="to" required></label>
        <button type="submit" class="btn">Stop sales</button>
        <button type="button" class="btn plain" id="reopenBtn">Reopen sales</button>
      </form>
    </details>
    @endif
  </section>

  <section class="cols">
    <div>
      <h2>Rooms sold, next 30 nights</h2>
      <p class="sub">Share of room-nights booked, by room type.</p>
      <table class="t">
        <thead><tr><th>Room type</th><th class="num">Sold</th><th class="meter"></th><th class="num">Share</th></tr></thead>
        <tbody id="occ"></tbody>
      </table>
    </div>
    <div>
      <h2>Where bookings come from</h2>
      <p class="sub">Confirmed bookings by channel.</p>
      <table class="t">
        <thead><tr><th>Channel</th><th class="num">Bookings</th><th class="num">Rooms</th></tr></thead>
        <tbody id="chan"></tbody>
      </table>
    </div>
  </section>

  <section class="ledger">
    <h2>Latest bookings</h2>
    <p class="sub">The eight most recent, newest first.</p>
    <div style="overflow-x:auto">
    <table class="t">
      <thead><tr><th>No.</th><th>Guest</th><th>Room</th><th>Channel</th><th>Stay</th><th class="num">Rooms</th><th>Status</th><th></th></tr></thead>
      <tbody id="recent"></tbody>
    </table>
    </div>
  </section>
</main>

<div id="overlay"></div>
<aside id="drawer" aria-label="New booking">
  <h3>New booking</h3>
  <p class="sub">Booked on the direct channel. Availability is checked inside a locked transaction, so two people cannot take the same room.</p>
  <form id="bookForm">
    <label for="f-room">Room type</label>
    <select id="f-room" name="room_type_id">
      @foreach ($roomTypes as $rt)
        <option value="{{ $rt->id }}">{{ $rt->name }}</option>
      @endforeach
    </select>
    <label for="f-guest">Guest name</label>
    <input id="f-guest" name="guest_name" required maxlength="100" placeholder="Full name">
    <div class="row2">
      <div><label for="f-in">Check-in</label><input id="f-in" type="date" name="check_in" required></div>
      <div><label for="f-out">Check-out</label><input id="f-out" type="date" name="check_out" required></div>
    </div>
    <div id="nights"></div>
    <div id="quote"></div>
    <label for="f-units">Rooms</label>
    <input id="f-units" type="number" name="units" value="1" min="1" max="10">
    <div id="f-err" role="alert"></div>
    <div class="actions">
      <button type="button" class="btn plain" id="cancelBtn">Cancel</button>
      <button type="submit" class="btn">Confirm booking</button>
    </div>
  </form>
</aside>

<div id="toast" role="status"></div>

<script>
$.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

var TODAY = '{{ today()->toDateString() }}';
var ADMIN = {{ $isAdmin ? 'true' : 'false' }};
var from = TODAY;
var WD = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
var MO = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
var DEFAULT_PEEK = 'Point at a cell for details.';

function esc(s) { return $('<div>').text(s == null ? '' : s).html(); }
function dt(str) { return new Date(str + 'T00:00:00Z'); }
function shift(str, n) { var x = dt(str); x.setUTCDate(x.getUTCDate() + n); return x.toISOString().slice(0, 10); }
function pretty(str) { var x = dt(str); return x.getUTCDate() + ' ' + MO[x.getUTCMonth()]; }
function yen(n) { return '¥' + Number(n).toLocaleString('en-US'); }

function toast(msg) {
  var t = $('#toast').text(msg).addClass('show');
  clearTimeout(window._t);
  window._t = setTimeout(function () { t.removeClass('show'); }, 3200);
}
function errMsg(xhr, fallback) {
  var j = xhr.responseJSON || {}, m = j.message || fallback;
  if (j.errors) { m = Object.values(j.errors)[0][0]; }
  return m;
}

function loadGrid() {
  $.getJSON('/availability', { from: from }, function (res) {
    var rooms = {}, order = [], dates = [];
    res.rows.forEach(function (r) {
      if (!rooms[r.room_type_id]) { rooms[r.room_type_id] = { name: r.name, cells: [] }; order.push(r.room_type_id); }
      rooms[r.room_type_id].cells.push(r);
      if (dates.indexOf(r.date) === -1) dates.push(r.date);
    });
    if (!dates.length) {
      $('#grid').html('<p class="empty">No inventory is set for these dates.</p>');
      $('#range').text('');
      return;
    }

    var h = '<table class="grid"><thead><tr><th class="rm">Room type</th>';
    dates.forEach(function (x) {
      var w = dt(x).getUTCDay();
      h += '<th class="' + (w === 1 ? 'mon ' : '') + (x === TODAY ? 'today' : '') + '">' + WD[w] + '<b>' + dt(x).getUTCDate() + '</b></th>';
    });
    h += '</tr></thead><tbody>';
    order.forEach(function (id) {
      h += '<tr><th class="rm">' + esc(rooms[id].name) + '</th>';
      rooms[id].cells.forEach(function (c) {
        var rem = +c.remaining, tot = +c.total, occ = tot ? (tot - rem) / tot : 1;
        var closed = +c.closed === 1;
        var mon = dt(c.date).getUTCDay() === 1 ? ' mon' : '';
        var attrs = ' data-room="' + id + '" data-name="' + esc(rooms[id].name) + '" data-date="' + c.date +
                    '" data-rem="' + rem + '" data-tot="' + tot + '" data-closed="' + (closed ? 1 : 0) +
                    '" data-price="' + Math.round(+c.price || 0) + '"';
        if (closed) {
          h += '<td class="cell full closed' + mon + '"' + attrs + '>×</td>';
        } else if (rem === 0) {
          h += '<td class="cell full' + mon + '"' + attrs + '>0</td>';
        } else {
          var a = (0.10 + 0.62 * occ).toFixed(2);
          var col = occ > 0.5 ? '#fff' : '#171A21';
          h += '<td class="cell' + mon + '" tabindex="0" role="button"' + attrs +
               ' style="background:rgba(43,63,166,' + a + ');color:' + col + '">' + rem + '</td>';
        }
      });
      h += '</tr>';
    });
    h += '</tbody></table>';
    $('#grid').html(h);
    $('#range').text(pretty(dates[0]) + ' to ' + pretty(dates[dates.length - 1]));
    $('#peek').text(DEFAULT_PEEK);
  });
}

function loadSummary() {
  $.getJSON('/summary', function (s) {
    $('#lede-p').html(
      '<b>' + s.stats.free_today + '</b> rooms free tonight. ' +
      '<b>' + s.stats.bookings + '</b> confirmed bookings across <b>' + s.stats.channels + '</b> channels. ' +
      '<b>' + s.stats.occupancy + '%</b> of the next 30 nights are sold.'
    );

    var o = '';
    s.occupancy.forEach(function (r) {
      o += '<tr><td>' + esc(r.name) + '</td>' +
           '<td class="num">' + r.booked + ' of ' + r.total + '</td>' +
           '<td class="meter"><span><i style="width:' + Math.min(100, +r.occupancy_pct) + '%"></i></span></td>' +
           '<td class="num"><b>' + r.occupancy_pct + '%</b></td></tr>';
    });
    $('#occ').html(o || '<tr><td colspan="4" class="empty">No inventory yet.</td></tr>');

    var c = '';
    s.channels.forEach(function (x) {
      c += '<tr><td>' + esc(x.name) + '</td><td class="num"><b>' + x.bookings + '</b></td><td class="num">' + x.units + '</td></tr>';
    });
    $('#chan').html(c || '<tr><td colspan="3" class="empty">No channels yet.</td></tr>');

    var r = '';
    s.recent.forEach(function (b) {
      var cancelled = b.status === 'cancelled';
      r += '<tr><td>' + b.id + '</td><td>' + esc(b.guest_name) + '</td><td>' + esc(b.room) + '</td>' +
           '<td>' + esc(b.channel) + '</td><td>' + pretty(b.check_in) + ' to ' + pretty(b.check_out) + '</td>' +
           '<td class="num">' + b.units + '</td>' +
           '<td class="' + (cancelled ? 'status-x' : 'status-ok') + '">' + esc(b.status) + '</td>' +
           '<td class="num">' + (cancelled || !ADMIN ? '' : '<button class="link cancel" data-id="' + b.id + '">Cancel</button>') + '</td></tr>';
    });
    $('#recent').html(r || '<tr><td colspan="8" class="empty">No bookings yet. Click a cell in the chart to make the first one.</td></tr>');
  });
}

function nights() {
  var n = Math.round((dt($('#f-out').val()) - dt($('#f-in').val())) / 86400000);
  $('#nights').text(isNaN(n) ? '' : (n > 0 ? n + (n > 1 ? ' nights' : ' night') : 'Check-out must be after check-in.'));
}

function loadQuote() {
  var room = $('#f-room').val(), ci = $('#f-in').val(), co = $('#f-out').val();
  if (!ci || !co || co <= ci) { $('#quote').text(''); return; }
  $.getJSON('/quote', { room_type_id: room, check_in: ci, check_out: co })
    .done(function (q) {
      $('#quote').text(q.nights ? 'Suggested rate: ' + yen(q.total) + ' for ' + q.nights + (q.nights > 1 ? ' nights' : ' night') + '.' : '');
    })
    .fail(function () { $('#quote').text(''); });
}

function openDrawer(room, date) {
  if (room) $('#f-room').val(room);
  var ci = date || TODAY;
  $('#f-in').val(ci).attr('min', TODAY);
  $('#f-out').val(shift(ci, 1));
  $('#f-guest').val('');
  $('#f-units').val(1);
  $('#f-err').text('');
  nights();
  loadQuote();
  $('#overlay, #drawer').addClass('open');
  setTimeout(function () { $('#f-guest').focus(); }, 230);
}
function closeDrawer() { $('#overlay, #drawer').removeClass('open'); }

$('#prev').click(function () { from = shift(from, -14); loadGrid(); });
$('#next').click(function () { from = shift(from, 14); loadGrid(); });
$('#todayBtn').click(function () { from = TODAY; loadGrid(); });
$('#newBtn').click(function () { openDrawer(); });
$('#cancelBtn, #overlay').click(closeDrawer);
$(document).on('keydown', function (e) { if (e.key === 'Escape') closeDrawer(); });
$('#f-in, #f-out, #f-room').on('change', function () { nights(); loadQuote(); });

$('#grid').on('click', 'td.cell:not(.full)', function () {
  if (!ADMIN) { toast('Log in as admin to make a booking.'); return; }
  openDrawer($(this).data('room'), $(this).data('date'));
});
$('#grid').on('keydown', 'td.cell:not(.full)', function (e) {
  if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); $(this).click(); }
});
$('#grid').on('mouseover focusin', 'td.cell', function () {
  var d = $(this).data(), t;
  if (+d.closed === 1) t = 'sales stopped.';
  else if (+d.rem === 0) t = 'sold out.';
  else t = d.rem + ' of ' + d.tot + ' left. Suggested rate ' + yen(d.price) + ' per night.';
  $('#peek').text(d.name + ', ' + pretty(d.date) + ': ' + t);
});
$('#grid').on('mouseleave', function () { $('#peek').text(DEFAULT_PEEK); });

$('#bookForm').on('submit', function (e) {
  e.preventDefault();
  $('#f-err').text('');
  $.post('/bookings', $(this).serialize())
    .done(function (res) {
      closeDrawer();
      toast('Booking ' + res.id + ' confirmed');
      loadGrid(); loadSummary();
    })
    .fail(function (xhr) {
      $('#f-err').text(errMsg(xhr, 'The booking could not be saved. Try again.'));
    });
});

$('#recent').on('click', '.cancel', function () {
  var id = $(this).data('id');
  if (!confirm('Cancel booking ' + id + '? The rooms go back into inventory.')) return;
  $.post('/bookings/' + id + '/cancel')
    .done(function () { toast('Booking ' + id + ' cancelled'); loadGrid(); loadSummary(); })
    .fail(function (xhr) { toast(errMsg(xhr, 'Could not cancel the booking.')); });
});

function sendStop(closed) {
  $.post('/stop-sales', $('#stopForm').serialize() + '&closed=' + closed)
    .done(function (r) {
      toast((closed ? 'Sales stopped' : 'Sales reopened') + ' on ' + r.nights + (r.nights === 1 ? ' night' : ' nights'));
      loadGrid(); loadSummary();
    })
    .fail(function (xhr) { toast(errMsg(xhr, 'Could not update sales.')); });
}
$('#stopForm').on('submit', function (e) { e.preventDefault(); sendStop(1); });
$('#reopenBtn').click(function () { sendStop(0); });

loadGrid();
loadSummary();
</script>
</body>
</html>