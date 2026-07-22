<?php

namespace App\Services;

use App\Models\TransactionCategory;
use App\Repositories\TransactionCategoryRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class TransactionCategoryService extends BaseService
{
    // Các giá trị type hợp lệ, khớp với transaction.type (receipt/payment/transfer)
    // qua bảng ánh xạ ở TransactionService::validateCategoryType()
    private const VALID_TYPES = ['income', 'expense', 'transfer'];

    public function __construct(
        protected TransactionCategoryRepositoryInterface $repository
    ) {}

    /**
     * Danh sách phân trang.
     */
    public function paginate(array $filters = []): LengthAwarePaginator
    {
        $filters['company_id'] = $this->companyId();

        return $this->repository->paginate($filters);
    }

    /**
     * Danh sách loại giao dịch đang hoạt động.
     */
    public function getActive(): Collection
    {
        return $this->repository->getActive($this->companyId());
    }

    /**
     * Chi tiết.
     */
    public function find(int $id): ?TransactionCategory
    {
        return $this->repository->find($id);
    }

    /**
     * Tạo mới. Mã được sinh tự động, không nhận từ client.
     * 'type' bắt buộc phải có ngay từ lúc tạo, vì đây là căn cứ để lọc
     * category theo loại giao dịch (thu/chi/chuyển khoản) ở form giao dịch.
     */
    public function create(array $data): TransactionCategory
    {
        $this->validateType($data['type'] ?? null);

        return DB::transaction(function () use ($data) {
            $data['company_id'] = $this->companyId();
            $data['code'] = $this->generateCode();
            $data['status'] = $data['status'] ?? 1;

            return $this->repository->create($data);
        });
    }

    /**
     * Cập nhật.
     * Nếu category đã được dùng ở >=1 giao dịch: chỉ cho phép đổi 'status',
     * không cho sửa 'name'/'note'/'type' để giữ toàn vẹn dữ liệu lịch sử.
     *
     * Lý do 'type' cũng bị khoá khi đã dùng: đổi type giữa chừng sẽ làm các
     * giao dịch cũ (đã gắn category này) không còn khớp với type mới, phá vỡ
     * logic lọc category theo loại giao dịch (thu/chi/chuyển khoản).
     */
    public function update(TransactionCategory $category, array $data): TransactionCategory
    {
        $this->ensureCurrentCompany($category);

        $isUsed = $category->transactions()->exists();

        if ($isUsed) {
            $data = collect($data)->only(['status'])->toArray();

            if (empty($data)) {
                throw ValidationException::withMessages([
                    'category' => 'Loại giao dịch đã được sử dụng, chỉ có thể khóa/mở, không thể chỉnh sửa thông tin.',
                ]);
            }
        } else {
            // Chưa dùng: cho phép đổi type nhưng vẫn phải hợp lệ
            if (array_key_exists('type', $data)) {
                $this->validateType($data['type']);
            }
        }

        // 'code' không bao giờ được phép đổi qua update, kể cả khi chưa dùng
        unset($data['code']);

        return $this->repository->update($category, $data);
    }

    /**
     * Xóa. Chỉ xóa được nếu chưa phát sinh giao dịch nào.
     */
    public function delete(TransactionCategory $category): bool
    {
        $this->ensureCurrentCompany($category);

        if ($category->transactions()->exists()) {
            throw ValidationException::withMessages([
                'category' => 'Loại giao dịch đã được sử dụng, không thể xóa.',
            ]);
        }

        return $this->repository->delete($category);
    }

    private function ensureCurrentCompany(TransactionCategory $category): void
    {
        abort_unless((int) $category->company_id === $this->companyId(), 404);
    }

    /**
     * Kiểm tra type hợp lệ (income/expense/transfer).
     */
    private function validateType(?string $type): void
    {
        if (empty($type) || !in_array($type, self::VALID_TYPES, true)) {
            throw ValidationException::withMessages([
                'type' => 'Loại giao dịch không hợp lệ. Chỉ chấp nhận Thu, Chi hoặc Chuyển nội bộ.',
            ]);
        }
    }

    /**
     * Sinh mã loại giao dịch tự động, dạng LGD-001, LGD-002...
     * Khóa bảng khi đọc mã cuối để tránh 2 request đồng thời
     * sinh trùng mã (dù danh mục này ít khi tạo đồng thời,
     * vẫn nên có để an toàn).
     */
    private function generateCode(): string
    {
        $last = TransactionCategory::where('company_id', $this->companyId())
            ->lockForUpdate()
            ->orderByDesc('id')
            ->first();

        $nextNumber = $last ? ((int) substr($last->code, 4)) + 1 : 1;

        return 'LGD-' . str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT);
    }
}
