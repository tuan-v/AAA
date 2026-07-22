<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\TransactionCategory\StoreTransactionCategoryRequest;
use App\Http\Requests\TransactionCategory\UpdateTransactionCategoryRequest;
use App\Http\Resources\TransactionCategoryResource;
use App\Models\TransactionCategory;
use App\Services\TransactionCategoryService;
use Illuminate\Http\JsonResponse;

class TransactionCategoryController extends Controller
{
    public function __construct(
        protected TransactionCategoryService $service
    ) {}

    /**
     * Danh sách.
     */
    public function index(): JsonResponse
    {
        $categories = $this->service->paginate(request()->all());

        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách loại giao dịch thành công.',
            'data' => TransactionCategoryResource::collection($categories),
            'meta' => [
                'current_page' => $categories->currentPage(),
                'last_page' => $categories->lastPage(),
                'per_page' => $categories->perPage(),
                'total' => $categories->total(),
            ],
        ]);
    }

    /**
     * Danh sách đang hoạt động.
     */
    public function active(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => TransactionCategoryResource::collection(
                $this->service->getActive()
            ),
        ]);
    }

    /**
     * Chi tiết.
     */
    public function show(TransactionCategory $transactionCategory): JsonResponse
    {
        $this->ensureCurrentCompany($transactionCategory);

        return response()->json([
            'success' => true,
            'data' => new TransactionCategoryResource($transactionCategory),
        ]);
    }

    /**
     * Tạo mới.
     */
    public function store(StoreTransactionCategoryRequest $request): JsonResponse
    {
        $category = $this->service->create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Tạo loại giao dịch thành công.',
            'data' => new TransactionCategoryResource($category),
        ], 201);
    }

    /**
     * Cập nhật.
     */
    public function update(
        UpdateTransactionCategoryRequest $request,
        TransactionCategory $transactionCategory
    ): JsonResponse {
        $this->ensureCurrentCompany($transactionCategory);

        $category = $this->service->update(
            $transactionCategory,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật loại giao dịch thành công.',
            'data' => new TransactionCategoryResource($category),
        ]);
    }

    /**
     * Xóa.
     */
    public function destroy(TransactionCategory $transactionCategory): JsonResponse
    {
        $this->ensureCurrentCompany($transactionCategory);

        $this->service->delete($transactionCategory);

        return response()->json([
            'success' => true,
            'message' => 'Xóa loại giao dịch thành công.',
        ]);
    }

    private function ensureCurrentCompany(TransactionCategory $category): void
    {
        abort_unless(
            (int) $category->company_id === (int) auth()->user()?->company_id,
            404
        );
    }
}
