<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Symfony\Contracts\Service\Attribute\Required;

class ProductController extends Controller
{
    // Lấy danh sách
    public function index()
    {

        return response()->json(
            Product::with('theLoai')
                ->orderBy('id', 'desc')
                ->get()
        );
    }

    // Thêm sản phẩm
    public function store(Request $request)
    {
        $request->validate(
            [
                'ten' => 'required|min:3|max:255',
                'so_luong' => 'required|integer|min:1',
                'mau_sac' => 'required',
                'gia' => 'required|numeric|min:0',
                'id_the_loai' => 'required|exists:theloai,id',
            ],
            [
                'ten.required' => 'Vui long nhap ten san pham',
                'ten.min' => 'Ten lon hon 3 ki tu',
                'ten.max' => 'Ten nho hon 255 ki tu',
                'so_luong.required' => 'Vui long nhap so luong',
                'so_luong.min' => 'So luong khong duoc nho hon 1',
                'so_luong.integer' => 'So luong phai la so nguyen',
                'mau_sac.required' => 'Vui long chon mau sac',
                'gia.required' => 'Vui long nhap gia',
                'gia.min' => 'Gia khong duoc la so am',
            ]
        );

        $product = Product::create([
            'ten' => $request->ten,
            'so_luong' => $request->so_luong,
            'mau_sac' => $request->mau_sac,
            'gia' => $request->gia,
            'id_the_loai' => $request->id_the_loai,
        ]);

        return response()->json([
            'message' => 'Them thanh cong',
            'data' => $product
        ]);
    }

    // Chi tiết sản phẩm
    public function show($id)
    {
        return response()->json(
            Product::with('theLoai')->findOrFail($id)
        );
    }

    // Update
    public function update(Request $request, $id)
    {
        $request->validate(
            [
                'ten' => 'required|min:3|max:255',
                'so_luong' => 'required|integer|min:1',
                'mau_sac' => 'required',
                'gia' => 'required|numeric|min:0',
                'id_the_loai' => 'required|exists:theloai,id',
            ],
            [
                'ten.required' => 'Vui long nhap ten san pham',
                'ten.min' => 'Ten lon hon 3 ki tu',
                'ten.max' => 'Ten nho hon 255 ki tu',
                'so_luong.required' => 'Vui long nhap so luong',
                'so_luong.min' => 'So luong khong duoc nho hon 1',
                'so_luong.integer' => 'So luong phai la so nguyen',
                'mau_sac.required' => 'Vui long chon mau sac',
                'gia.required' => 'Vui long nhap gia',
                'gia.min' => 'Gia khong duoc la so am',
            ]
        );
        $product = Product::findOrFail($id);

        $product->update([
            'ten' => $request->ten,
            'so_luong' => $request->so_luong,
            'mau_sac' => $request->mau_sac,
            'gia' => $request->gia,
            'id_the_loai' => $request->id_the_loai,
        ]);

        return response()->json([
            'message' => 'Sua thanh cong'
        ]);
    }

    // Delete
    public function destroy($id)
    {
        Product::destroy($id);

        return response()->json([
            'message' => 'Xoa thanh cong'
        ]);
    }
}
