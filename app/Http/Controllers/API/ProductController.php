<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function all(Request $request)
    {

        $id = $request->input('id');
        $limit = $request->input('limit', 6);
        $name = $request->input('name');

        $description = $request->input('description');
        $tags = $request->input('tags');
        $categories = $request->input('categories');

        $Price_From = $request->input('price_from');
        $Price_To = $request->input('price_to');

        if ($id) {
            $product = Product::with(['category', 'galleries'])->find($id);

            if ($product) {
                return ResponseFormatter::success($product, 'Data Produk Berhasil Diambil');
            } else {
                return ResponseFormatter::error(null, 'Data Produk Tidak Ada', 404);
            }
        }

        $product = Product::with(['category', 'galleries']);

        if ($name) {
            $product->where('name', 'like', '%' . $name . '%');
        }
        if ($description) {
            $product->where('description', 'like', '%' . $description . '%');
        }
        if ($tags) {
            $product->where('tags', 'like', '%' . $tags . '%');
        }
        if ($Price_From) {
            $product->where('price', '>=', $Price_From);
        }
        if ($Price_To) {
            $product->where('price', '<=', $Price_To);
        }
        if ($categories) {
            $product->where('categories', $categories);
        }

        return ResponseFormatter::success(
            $product->paginate($limit),
            'Data List Produk Berhasil Diambil'
        );
    }
}
