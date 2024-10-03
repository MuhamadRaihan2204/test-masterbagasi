<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $product = Product::select('id', 'name', 'price')->get();

        $data = [
            'success' => true,
            'data' => $product ? $product : []
        ];

        return response()->json($data, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'price' => 'required|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            $product = Product::create([
                'name' => $request->name,
                'price' => $request->price,
            ]);

            DB::commit();
            return response()->json([
                'success' => true,
                'data' => $product,
            ], 201);
            
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        DB::beginTransaction();
    
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'price' => 'required|numeric|min:0',
            ]);
    
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }
    
            $product = Product::find($id);
    
            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found',
                ], 404);
            }
    
            $product->update([
                'name' => $request->name,
                'price' => $request->price,
            ]);
    
            DB::commit();
    
            return response()->json([
                'success' => true,
                'data' => $product,
            ], 200);

        } catch (\Throwable $th) {

            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $th->getMessage(),
            ], 500);
        }
    }
    

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        DB::beginTransaction();
    
        try {
            // Temukan produk berdasarkan ID
            $product = Product::find($id);
    
            // Cek apakah produk ada
            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found',
                ], 404);
            }
    
            // Hapus produk
            $product->delete();
    
            DB::commit();
    
            return response()->json([
                'success' => true,
                'message' => 'Product deleted successfully',
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack(); // Rollback jika terjadi kesalahan
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $th->getMessage(),
            ], 500);
        }
    }
    
}
