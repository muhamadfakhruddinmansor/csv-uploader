<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Products</title>
  <style>
    body { font-family: -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Ubuntu, "Helvetica Neue", Arial, sans-serif; margin:24px; }
    .card { border:1px solid #ddd; border-radius:12px; padding:16px; margin-bottom:24px; }
    .grid { display:grid; grid-template-columns: repeat(6, minmax(0,1fr)); gap:12px; }
    label { font-size:12px; color:#444; display:block; margin-bottom:4px; }
    input, select { width:100%; padding:8px; border:1px solid #ddd; border-radius:8px; }
    button { padding:10px 14px; border:0; border-radius:10px; background:#111; color:#fff; cursor:pointer; }
    table { width:100%; border-collapse:collapse; }
    th, td { padding:10px; border-bottom:1px solid #eee; text-align:left; vertical-align:top; }
    th { background:#f8f8f8; position:sticky; top:0; }
    .muted { color:#666; font-size:12px; }
    .toolbar { display:flex; gap:10px; align-items:flex-end; }
    .toolbar-right { margin-left:auto; display:flex; gap:8px; }
    .pill { padding:6px 10px; border-radius:999px; background:#f1f1f1; }
    .paginate { margin-top:14px; }
    .paginate a, .paginate span { margin-right:6px; text-decoration:none; color:#111; }
  </style>
</head>
<body>
  <h1>Products</h1>

  <div class="card">
    <form method="get" class="grid">
      <div>
        <label>Search (key/title/style/color/size)</label>
        <input type="text" name="q" value="{{ request('q') }}">
      </div>
      <div>
        <label>Size</label>
        <input type="text" name="size" value="{{ request('size') }}" placeholder="e.g. S">
      </div>
      <div>
        <label>Color Name</label>
        <input type="text" name="color" value="{{ request('color') }}" placeholder="e.g. White">
      </div>
      <div>
        <label>Min Price</label>
        <input type="number" step="0.01" name="min_price" value="{{ request('min_price') }}">
      </div>
      <div>
        <label>Max Price</label>
        <input type="number" step="0.01" name="max_price" value="{{ request('max_price') }}">
      </div>
      <div class="toolbar">
        <div>
          <label>Sort</label>
          <select name="order_by">
            <option value="updated_at" {{ request('order_by','updated_at')==='updated_at'?'selected':'' }}>Updated At</option>
            <option value="piece_price" {{ request('order_by')==='piece_price'?'selected':'' }}>Piece Price</option>
            <option value="unique_key" {{ request('order_by')==='unique_key'?'selected':'' }}>Unique Key</option>
          </select>
        </div>
        <div>
          <label>Direction</label>
          <select name="dir">
            <option value="desc" {{ request('dir','desc')==='desc'?'selected':'' }}>Desc</option>
            <option value="asc"  {{ request('dir')==='asc'?'selected':'' }}>Asc</option>
          </select>
        </div>
        <div class="toolbar-right">
          <button type="submit">Filter</button>
          <a href="{{ route('products.index') }}" class="pill">Reset</a>
        </div>
      </div>
    </form>
    <div class="muted" style="margin-top:10px;">
      Showing {{ $products->firstItem() ?? 0 }}–{{ $products->lastItem() ?? 0 }} of {{ $products->total() }} items
    </div>
  </div>

  <div class="card">
    <table>
      <thead>
        <tr>
          <th style="width:16%">Unique Key</th>
          <th>Title</th>
          <th style="width:10%">Style#</th>
          <th style="width:10%">Color</th>
          <th style="width:8%">Size</th>
          <th style="width:10%">Price</th>
          <th style="width:16%">Updated</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($products as $p)
          <tr>
            <td><code>{{ $p->unique_key }}</code></td>
            <td>{{ $p->product_title }}</td>
            <td>{{ $p->style_number }}</td>
            <td>{{ $p->color_name }}</td>
            <td>{{ $p->size }}</td>
            <td>{{ $p->piece_price !== null ? number_format($p->piece_price, 2) : '-' }}</td>
            <td>{{ $p->updated_at }}<br><span class="muted">({{ $p->updated_at?->diffForHumans() }})</span></td>
          </tr>
        @empty
          <tr><td colspan="7">No products match your filter.</td></tr>
        @endforelse
      </tbody>
    </table>

    <div class="paginate">
      {{ $products->onEachSide(1)->links() }}
    </div>
  </div>
</body>
</html>

