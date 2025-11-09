<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>CSV Uploader</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <style>
    body { font-family: -apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Ubuntu,"Helvetica Neue",Arial,sans-serif; margin:24px; }
    .card { border:1px solid #ddd; border-radius:12px; padding:16px; margin-bottom:24px; }
    table { width:100%; border-collapse:collapse; }
    th,td { padding:10px; border-bottom:1px solid #eee; text-align:left; }
    th { background:#f8f8f8; position:sticky; top:0; }
    .status { font-weight:600; text-transform:capitalize; }
    .pending{ color:#884488 } .processing{ color:#c98000 } .completed{ color:#0a7a3a } .failed{ color:#b00020 }
  </style>
</head>
<body>
  <h1>CSV Uploader</h1>

  <div class="card">
    <form action="{{ route('uploads.store') }}" method="post" enctype="multipart/form-data">
      @csrf
      <input type="file" name="file" accept=".csv,.txt" required>
      <button type="submit">Upload File</button>
    </form>
    @if (session('status')) <p>{{ session('status') }}</p> @endif
    @if ($errors->any()) <p style="color:#b00020">{{ $errors->first() }}</p> @endif
  </div>

  <div class="card">
    <h2>Recent Uploads</h2>
    <table id="uploads-table">
      <thead>
        <tr>
          <th style="width:28%">Time</th>
          <th>File Name</th>
          <th style="width:18%">Status</th>
          <th style="width:14%">Rows</th>
          <th>Error</th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>
    <p id="empty" style="display:none;">No uploads yet.</p>
  </div>

<script>
async function refreshUploads(){
  try{
    const res = await fetch('/api/uploads');
    const data = await res.json();
    const list = data.data || data || [];
    const tbody = document.querySelector('#uploads-table tbody');
    tbody.innerHTML = '';

    document.getElementById('empty').style.display = list.length ? 'none' : 'block';

    for(const u of list){
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>${new Date(u.created_at).toLocaleString()}<br><small>(${u.created_human ?? ''})</small></td>
        <td>${u.original_name}</td>
        <td class="status ${u.status}">${u.status}</td>
        <td>${u.rows_processed ?? 0}</td>
        <td>${u.error ?? ''}</td>`;
      tbody.appendChild(tr);
    }
  } catch(e){ console.error(e); }
}

refreshUploads();
setInterval(refreshUploads, 3000);
</script>
</body>
</html>

