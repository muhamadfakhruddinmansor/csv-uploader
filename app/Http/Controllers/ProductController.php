<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $q            = trim((string)$request->query('q', ''));
        $size         = trim((string)$request->query('size', ''));
        $color        = trim((string)$request->query('color', ''));
        $minPrice     = $request->query('min_price');
        $maxPrice     = $request->query('max_price');
        $updatedFrom  = $request->query('updated_from'); // yyyy-mm-dd
        $updatedTo    = $request->query('updated_to');   // yyyy-mm-dd
        $orderBy      = $request->query('order_by', 'updated_at');
        $direction    = $request->query('dir', 'desc');

        $safeOrder = in_array($orderBy, ['updated_at','piece_price','unique_key']) ? $orderBy : 'updated_at';
        $safeDir   = in_array(strtolower($direction), ['asc','desc']) ? strtolower($direction) : 'desc';

        $query = Product::query();

        if ($q !== '') {
            $query->where(function($w) use ($q) {
                $w->where('unique_key','like',"%$q%")
                  ->orWhere('product_title','like',"%$q%")
                  ->orWhere('style_number','like',"%$q%")
                  ->orWhere('sanmar_mainframe_color','like',"%$q%")
                  ->orWhere('size','like',"%$q%")
                  ->orWhere('color_name','like',"%$q%");
            });
        }

        if ($size !== '')  $query->where('size', $size);
        if ($color !== '') $query->where('color_name', $color);

        if ($minPrice !== null && $minPrice !== '') $query->where('piece_price','>=',(float)$minPrice);
        if ($maxPrice !== null && $maxPrice !== '') $query->where('piece_price','<=',(float)$maxPrice);

        if ($updatedFrom) $query->whereDate('updated_at','>=',$updatedFrom);
        if ($updatedTo)   $query->whereDate('updated_at','<=',$updatedTo);

        $products = $query->orderBy($safeOrder, $safeDir)
                          ->paginate(25)
                          ->withQueryString();

        return view('products.index', compact('products','q','size','color','minPrice','maxPrice','updatedFrom','updatedTo','safeOrder','safeDir'));
    }
}
