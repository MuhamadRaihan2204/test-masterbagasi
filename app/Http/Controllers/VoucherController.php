<?php

namespace App\Http\Controllers;

use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class VoucherController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $voucher = Voucher::select('id', 'name', 'code', 'expired', 'status')->get();

        $data = [
            'success' => true,
            'data' => $voucher ? $voucher : []
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
                'name' => 'required|max:255',
                'code' => 'required|regex:/^[A-Za-z0-9]{6}$/|unique:vouchers,code',
                'status' => 'nullable|in:0,1',
                'expired' => 'required',
            ], [
                'code.regex' => 'code must be 6 digit',
                'status.in' => '0 = non active, 1 active'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            $expiredDate = \Carbon\Carbon::createFromFormat('Y-m-d', $request->expired)->format('Y-m-d');

            $voucher = Voucher::create([
                'name' => $request->name,
                'code' => strtoupper($request->code),
                'expired' => $expiredDate,
                'status' => $request->status ?? '0'
            ]);

            DB::commit();
            return response()->json([
                'success' => true,
                'data' => $voucher,
            ], 201);
        } catch (\Throwable $th) {
            Log::info($request->all());
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
    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $voucher = Voucher::find($id);
            if (!$voucher) {
                return response()->json([
                    'success' => false,
                    'message' => 'Voucher not found',
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'code' => 'required|regex:/^[A-Za-z0-9]{6}$/|unique:vouchers,code,' . $voucher->id,
                'status' => 'nullable|in:0,1',
                'expired' => 'required|date',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            $expiredDate = \Carbon\Carbon::createFromFormat('Y-m-d', $request->expired)->format('Y-m-d');

            $voucher->update([
                'name' => $request->name,
                'code' => strtoupper($request->code),
                'expired' => $expiredDate,
                'status' => $request->status ?? '0'
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $voucher,
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
            $voucher = Voucher::find($id);

            if (!$voucher) {
                return response()->json([
                    'success' => false,
                    'message' => 'voucher not found',
                ], 404);
            }

            $voucher->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Voucher deleted successfully',
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $th->getMessage(),
            ], 500);
        }
    }

    public function codeVoucher(Request $request)
    {
        try {

            $validator = Validator::make($request->only('code_voucher'), [
                'code_voucher' => 'required'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            $voucher = Voucher::whereRaw('BINARY code = ?', [$request->code_voucher])->first();

            if (!$voucher) {
                return response()->json([
                    'success' => false,
                    'message' => 'Voucher not found',
                ], 404);
            }

            if ($voucher->expired > now()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Voucher expired',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Voucher can be used',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $th->getMessage(),
            ], 500);
        }
    }

    public function runVoucherSchedule(Request $request)
    {
        try {
            Artisan::call('voucherSchedule:cron');

            return response()->json([
                'success' => true,
                'message' => 'Vouchers has been activated',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $th->getMessage(),
            ], 500);
        }
    }
}
