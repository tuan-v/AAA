<?php

declare(strict_types=1);

/**
 * Sinh docs/PROJECT_FUNCTION_INDEX.md từ controller, Laravel route, Vue/JS và test.
 * Chạy tại root dự án: php docs/generate_project_function_index.php
 */

$root = dirname(__DIR__);
$output = __DIR__ . '/PROJECT_FUNCTION_INDEX.md';
$moduleOutput = __DIR__ . '/PROJECT_MODULE_DETAIL_INDEX.md';
$debugOutput = __DIR__ . '/PROJECT_DEBUGGING_INDEX.md';
$databaseOutput = __DIR__ . '/PROJECT_DATABASE_INDEX.md';
$generatedAt = date('d/m/Y');

function files(string $root, string $path, array $extensions): array
{
    $result = [];
    $dir = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path);
    if (!is_dir($dir)) {
        return [];
    }
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        if ($file->isFile() && in_array(strtolower($file->getExtension()), $extensions, true)) {
            $result[] = $file->getPathname();
        }
    }
    sort($result);
    return $result;
}

function relativePath(string $root, string $path): string
{
    return str_replace('\\', '/', substr($path, strlen($root) + 1));
}

function anchorId(string $value): string
{
    $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
    return strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', (string) $ascii), '-'));
}

function codeLink(string $root, string $path, int $line, ?string $label = null): string
{
    $absolute = str_replace('\\', '/', $path);
    return '[' . ($label ?? ('dòng ' . $line)) . '](vscode://file/' . $absolute . ':' . $line . ')';
}

function portableRootIndexLinks(string $source, string $root): string
{
    $rootPrefix = 'vscode://file/' . rtrim(str_replace('\\', '/', $root), '/') . '/';

    return preg_replace_callback(
        '~' . preg_quote($rootPrefix, '~') . '([^\)\r\n]+):(\d+)\)~',
        static fn (array $match): string => str_replace(' ', '%20', $match[1]) . '#L' . $match[2] . ')',
        $source
    ) ?? $source;
}

function controllerFunctions(string $root, string $path): array
{
    $source = file_get_contents($path);
    $tokens = token_get_all($source);
    $functions = [];
    $count = count($tokens);
    for ($i = 0; $i < $count; $i++) {
        if (!is_array($tokens[$i]) || $tokens[$i][0] !== T_FUNCTION) {
            continue;
        }
        $visibility = 'public';
        for ($j = $i - 1; $j >= 0; $j--) {
            if (is_string($tokens[$j]) && in_array($tokens[$j], [';', '{', '}'], true)) {
                break;
            }
            if (is_array($tokens[$j]) && $tokens[$j][0] === T_PRIVATE) $visibility = 'private';
            if (is_array($tokens[$j]) && $tokens[$j][0] === T_PROTECTED) $visibility = 'protected';
            if (is_array($tokens[$j]) && $tokens[$j][0] === T_PUBLIC) $visibility = 'public';
        }
        $name = null;
        for ($j = $i + 1; $j < $count; $j++) {
            if (is_array($tokens[$j]) && $tokens[$j][0] === T_STRING) {
                $name = $tokens[$j][1];
                break;
            }
            if ($tokens[$j] === '(') break;
        }
        if ($name === null) continue;
        $line = $tokens[$i][2];
        $body = '';
        for ($j = $i + 1; $j < $count && $tokens[$j] !== '{' && $tokens[$j] !== ';'; $j++);
        if ($j < $count && $tokens[$j] === '{') {
            $depth = 0;
            for ($k = $j; $k < $count; $k++) {
                $tokenText = is_array($tokens[$k]) ? $tokens[$k][1] : $tokens[$k];
                $body .= $tokenText;
                if ($tokens[$k] === '{') $depth++;
                if ($tokens[$k] === '}' && --$depth === 0) break;
            }
        }
        $callers = [];
        if ($visibility !== 'public') {
            foreach (preg_split('/\R/', $source) as $number => $text) {
                if ($number + 1 !== $line && preg_match('/(?:->|self::|static::|\$this->)' . preg_quote($name, '/') . '\s*\(/', $text)) {
                    $callers[] = $number + 1;
                }
            }
        }
        $functions[] = compact('name', 'line', 'visibility', 'callers', 'body');
    }
    return $functions;
}

function controllerSubject(string $class): string
{
    $controller = preg_replace('/Controller$/', '', substr($class, strrpos($class, '\\') + 1));
    $map = [
        'Account' => 'tài khoản kế toán', 'AccountLedger' => 'sổ cái tài khoản', 'Address' => 'địa chỉ hành chính',
        'AuditLog' => 'nhật ký hoạt động', 'Bank' => 'ngân hàng', 'Broadcast' => 'quyền truy cập kênh thời gian thực',
        'Category' => 'danh mục sản phẩm', 'CodReconciliation' => 'đối soát COD', 'Company' => 'công ty', 'Companies' => 'công ty',
        'Coupon' => 'mã giảm giá', 'Currency' => 'tiền tệ và tỷ giá', 'Customer' => 'khách hàng', 'Dashboard' => 'bảng điều khiển',
        'Department' => 'phòng ban', 'Departments' => 'phòng ban', 'Employee' => 'nhân viên',
        'InventoryMovement' => 'biến động tồn kho', 'Notification' => 'thông báo', 'Permission' => 'quyền',
        'Pos' => 'đơn bán POS', 'Position' => 'chức vụ', 'PositionUser' => 'phân công chức vụ', 'Product' => 'sản phẩm',
        'Profile' => 'hồ sơ người dùng', 'Province' => 'tỉnh/phường', 'PurchaseOrder' => 'đơn mua', 'Role' => 'vai trò',
        'SalesOrder' => 'đơn bán', 'Storefront' => 'cửa hàng trực tuyến', 'StorefrontAccount' => 'tài khoản khách hàng cửa hàng trực tuyến',
        'Supplier' => 'nhà cung cấp', 'Transaction' => 'giao dịch kế toán', 'TransactionCategory' => 'loại giao dịch',
        'Unit' => 'đơn vị tính', 'User' => 'người dùng/nhân sự', 'Warehouse' => 'kho',
        'WarehouseSlip' => 'phiếu nhập/xuất kho', 'WarehouseTransfer' => 'phiếu chuyển kho',
        'ProfitLossReport' => 'báo cáo lãi lỗ', 'AuthenticatedSession' => 'phiên đăng nhập',
        'RegisteredUser' => 'đăng ký người dùng', 'Password' => 'mật khẩu', 'PasswordResetLink' => 'liên kết đặt lại mật khẩu',
        'NewPassword' => 'mật khẩu mới', 'Google' => 'đăng nhập Google', 'UpdatePhone' => 'số điện thoại',
        'VerifyEmail' => 'xác minh email', 'EmailVerificationNotification' => 'email xác minh',
        'EmailVerificationPrompt' => 'màn hình xác minh email', 'ConfirmablePassword' => 'xác nhận mật khẩu',
    ];
    return $map[$controller] ?? strtolower(trim(preg_replace('/(?<!^)[A-Z]/', ' $0', $controller)));
}

function bodyFeatures(string $body): array
{
    $features = [];
    if (preg_match('/->validate\(|::validate\(|validated\(/', $body)) $features[] = 'kiểm tra dữ liệu đầu vào';
    if (str_contains($body, 'DB::beginTransaction') || str_contains($body, 'DB::transaction')) $features[] = 'chạy trong giao dịch cơ sở dữ liệu';
    if (preg_match('/->paginate\(|simplePaginate\(/', $body)) $features[] = 'phân trang kết quả';
    if (preg_match('/Notification|notificationService|->notify\(/i', $body)) $features[] = 'gửi thông báo';
    if (preg_match('/Inventory|stock|quantity/i', $body)) $features[] = 'xử lý số lượng/tồn kho';
    if (preg_match('/Debt|outstanding/i', $body)) $features[] = 'liên quan công nợ';
    if (preg_match('/Currency|exchange_rate/i', $body)) $features[] = 'quy đổi/kiểm tra tiền tệ';
    if (preg_match('/Coupon|voucher/i', $body)) $features[] = 'xử lý mã giảm giá';
    return array_slice(array_values(array_unique($features)), 0, 4);
}

function describe(string $name, string $visibility, string $subject, string $body, string $class): string
{
    $curated = [
        'SalesOrderController.companyId' => 'Lấy `company_id` của người dùng hiện tại (ưu tiên `user->company_id`, dự phòng bằng quan hệ `companies()`); trả 403 nếu người dùng không thuộc công ty.',
        'SalesOrderController.getCompanyCurrency' => 'Lấy tiền tệ mặc định của công ty qua `user->company` hoặc công ty đầu tiên của người dùng để quy đổi giá hiển thị.',
        'SalesOrderController.index' => 'Lấy danh sách đơn bán (loại POS chưa thanh toán), lọc theo trạng thái, điều kiện giao dịch, mã đơn và khách hàng; quy đổi giá/VAT/phí vận chuyển/giảm giá sang tiền tệ công ty rồi phân trang.',
        'SalesOrderController.warehouseIndex' => 'Lấy đơn bán `approved/partial/completed` cho màn hình kho, loại đơn POS và tính `warehouse_status` từ các phiếu xuất đã duyệt.',
        'SalesOrderController.availableForExport' => 'Trả danh sách kho và tồn khả dụng của từng sản phẩm trong đơn; tồn khả dụng bằng tồn thực tế trừ lượng đang được phiếu xuất `pending` giữ chỗ.',
        'SalesOrderController.show' => 'Lấy chi tiết đơn bán, tính số lượng đã xuất từng dòng (có nhánh riêng cho POS), quy đổi/tính tiền từng dòng và tổng đơn, kèm khách hàng, kho, người tạo/duyệt, địa chỉ và mã giảm giá.',
        'SalesOrderController.store' => 'Kiểm tra dữ liệu và tạo đơn bán cùng các dòng sản phẩm trong giao dịch cơ sở dữ liệu; kiểm tra tồn, tính tỷ giá/giá trị tiền tệ công ty, áp mã giảm giá và gửi thông báo cho kế toán.',
        'SalesOrderController.update' => 'Chỉ sửa đơn `draft/pending`: kiểm tra dữ liệu, tính lại tỷ giá, xóa và tạo lại toàn bộ dòng sản phẩm, hoàn tác rồi áp lại mã giảm giá, sau đó gửi thông báo trong giao dịch cơ sở dữ liệu.',
        'SalesOrderController.submitForApproval' => 'Chuyển đơn từ `draft` sang `pending`, ghi `submitted_at`, nhật ký hoạt động và gửi thông báo cho người có quyền duyệt đơn bán.',
        'SalesOrderController.approve' => 'Chuyển đơn `pending` sang `approved`, ghi nhận mã giảm giá đã dùng (trừ kênh cửa hàng trực tuyến), ghi nhật ký hoạt động và thông báo người tạo để đơn đi tiếp sang quy trình kho.',
        'SalesOrderController.cancel' => 'Hủy đơn `draft/pending` chưa có phiếu xuất kho; bắt buộc lý do, hoàn tác mã giảm giá, ghi nhật ký và thông báo người tạo/khách cửa hàng trực tuyến.',
        'SalesOrderController.destroy' => 'Xóa cứng đơn `draft/pending` chưa có phiếu xuất: xóa toàn bộ dòng sản phẩm rồi xóa đơn trong giao dịch cơ sở dữ liệu.',
        'SalesOrderController.stockOutData' => 'Chuẩn bị dữ liệu tạo phiếu xuất: tính lượng đã xuất/giữ bởi phiếu `pending` và `approved`, quy đổi tiền, đồng thời trả `can_export` và lý do chặn theo trạng thái đơn/hoàn hàng.',
    ];
    $shortClass = substr($class, strrpos($class, '\\') + 1);
    if (isset($curated[$shortClass . '.' . $name])) return $curated[$shortClass . '.' . $name];
    if ($name === '__construct') return 'Inject các service/dependency mà controller cần để xử lý ' . $subject . '.';
    $special = [
        'companyId' => 'Xác định công ty của người dùng hiện tại để cô lập dữ liệu và phân quyền.',
        'getCompanyCurrency' => 'Lấy tiền tệ mặc định của công ty để quy đổi số tiền.',
        'warehouseIndex' => 'Lấy các đơn đủ điều kiện cho màn hình kho và tính trạng thái nhập/xuất.',
        'availableForExport' => 'Tính số lượng còn có thể xuất theo đơn, sản phẩm và kho sau khi trừ phần đã giữ chỗ.',
        'stockOutData' => 'Chuẩn bị chi tiết đơn bán, lượng đã xuất/giữ chỗ và điều kiện tạo phiếu xuất.',
        'stockInData' => 'Chuẩn bị chi tiết đơn mua, lượng đã nhận và điều kiện tạo phiếu nhập.',
        'submitForApproval' => 'Chuyển chứng từ sang trạng thái chờ duyệt, ghi thời điểm gửi và thông báo người duyệt.',
        'forSelect' => 'Lấy danh sách ' . $subject . ' rút gọn, đang hoạt động để dùng trong danh sách chọn/biểu mẫu.',
        'select' => 'Lấy danh sách ' . $subject . ' rút gọn để dùng trong danh sách chọn/biểu mẫu.',
        'active' => 'Lấy các ' . $subject . ' đang hoạt động để dùng khi nhập liệu.',
        'all' => 'Lấy toàn bộ ' . $subject . ' thuộc công ty hiện tại ở dạng rút gọn.',
        'detail' => 'Lấy hồ sơ chi tiết ' . $subject . ' kèm các quan hệ và số liệu nghiệp vụ liên quan.',
        'toggleStatus' => 'Đổi trạng thái hoạt động của ' . $subject . ' sau khi kiểm tra quyền/điều kiện.',
        'nextCode' => 'Sinh mã ' . $subject . ' kế tiếp trong phạm vi công ty.',
        'validated' => 'Tập trung các rule validate dùng khi tạo hoặc sửa ' . $subject . '.',
        'isUsed' => 'Kiểm tra ' . $subject . ' đã được dữ liệu nghiệp vụ khác tham chiếu hay chưa.',
        'ensureCompanyCurrency' => 'Kiểm tra tiền tệ được chọn thuộc công ty hiện tại.',
        'unreadCount' => 'Đếm số thông báo chưa đọc của người dùng hiện tại.',
        'markAsRead' => 'Đánh dấu một thông báo thuộc người dùng hiện tại là đã đọc.',
        'markAllAsRead' => 'Đánh dấu toàn bộ thông báo của người dùng hiện tại là đã đọc.',
        'exchangeRate' => 'Lấy tỷ giá phù hợp giữa tiền tệ giao dịch và tiền tệ công ty.',
        'orderOutstanding' => 'Tính số tiền còn phải thu/phải trả của đơn để lập giao dịch.',
        'ledger' => 'Lấy các bút toán và biến động số dư của tài khoản kế toán.',
        'rebuildBalance' => 'Tính và xây dựng lại số dư tài khoản từ dữ liệu giao dịch.',
        'getStocks' => 'Lấy tồn kho theo sản phẩm/kho và các điều kiện lọc được gửi lên.',
        'ordersForImport' => 'Lấy các đơn mua còn số lượng có thể lập phiếu nhập.',
        'ordersForExport' => 'Lấy các đơn bán còn số lượng có thể lập phiếu xuất.',
        'accountantApprove' => 'Kế toán duyệt phiếu kho, cập nhật tồn kho/công nợ và hoàn tất trạng thái liên quan.',
        'assignShipping' => 'Gán đối tác vận chuyển và thông tin giao hàng cho phiếu xuất.',
        'requestDeliveryReturn' => 'Ghi nhận yêu cầu hoàn hàng giao thất bại và chuyển sang luồng xử lý hoàn.',
        'receiveDeliveryReturn' => 'Xác nhận kho đã nhận lại hàng hoàn để chờ kế toán duyệt.',
        'accountantApproveDeliveryReturn' => 'Kế toán duyệt hàng hoàn và ghi nhận biến động tồn kho tương ứng.',
        'confirmDelivery' => 'Xác nhận trạng thái giao hàng của phiếu xuất và cập nhật đơn liên quan.',
        'shippingPartners' => 'Lấy danh sách đối tác vận chuyển dùng khi giao hàng.',
        'storeShippingPartner' => 'Kiểm tra dữ liệu và tạo nhanh đối tác vận chuyển.',
        'createDraft' => 'Tạo đơn POS nháp để giữ nội dung giỏ hàng trước khi thanh toán.',
        'updateDraft' => 'Cập nhật sản phẩm, số lượng và thông tin của đơn POS nháp.',
        'cancelDraft' => 'Hủy đơn POS nháp và giải phóng dữ liệu tạm liên quan.',
        'drafts' => 'Lấy danh sách đơn POS nháp của công ty hiện tại.',
        'options' => 'Lấy sản phẩm, khách hàng, kho, tiền tệ và dữ liệu chọn dùng cho POS.',
        'history' => 'Lấy lịch sử các đơn POS đã hoàn tất.',
        'receipt' => 'Chuẩn hóa dữ liệu đơn POS thành cấu trúc hóa đơn/biên nhận trả về giao diện.',
        'checkout' => 'Kiểm tra giỏ hàng cửa hàng trực tuyến, tạo đơn bán và áp dụng thông tin giao hàng/mã giảm giá.',
        'products' => 'Lấy danh sách sản phẩm cửa hàng trực tuyến có lọc và phân trang.',
        'vouchers' => 'Lấy mã giảm giá hợp lệ mà khách hàng có thể dùng trên cửa hàng trực tuyến.',
        'sellingPrice' => 'Xác định giá bán hiện hành của sản phẩm trên cửa hàng trực tuyến.',
        'cancelOrder' => 'Cho khách hàng cửa hàng trực tuyến hủy đơn hợp lệ, ghi lý do và hoàn tác mã giảm giá nếu cần.',
        'repurchaseProductData' => 'Chuẩn hóa sản phẩm từ đơn cũ để khách hàng mua lại.',
        'trace' => 'Truy vết chuỗi nhật ký liên quan đến cùng chứng từ/bản ghi nghiệp vụ.',
        'sourceDocument' => 'Xác định loại và liên kết chứng từ nguồn tạo ra biến động tồn kho.',
    ];
    if (isset($special[$name])) return $special[$name];
    if ($visibility !== 'public') {
        $human = trim(preg_replace('/(?<!^)[A-Z]/', ' $0', $name));
        return 'Hàm hỗ trợ nội bộ “' . $human . '” phục vụ xử lý ' . $subject . '; thay đổi sẽ tác động các hàm gọi nó.';
    }
    $map = [
        'index' => 'Lấy danh sách ' . $subject . ' thuộc phạm vi được phép, áp dụng bộ lọc/tìm kiếm được gửi lên.',
        'show' => 'Lấy chi tiết một ' . $subject . ' kèm các quan hệ cần cho màn hình xem/sửa.',
        'store' => 'Kiểm tra đầu vào và tạo ' . $subject . ' mới cùng dữ liệu liên quan.',
        'update' => 'Kiểm tra và cập nhật ' . $subject . ' hiện có sau khi kiểm tra phạm vi/quyền sửa.',
        'destroy' => 'Kiểm tra điều kiện rồi xóa ' . $subject . ' và dữ liệu phụ thuộc được xử lý trong hàm.',
        'approve' => 'Duyệt ' . $subject . ', cập nhật trạng thái và kích hoạt bước nghiệp vụ tiếp theo.',
        'reject' => 'Từ chối ' . $subject . ', ghi lý do/trạng thái và thông báo bên liên quan nếu có.',
        'cancel' => 'Hủy ' . $subject . ' hợp lệ và hoàn tác dữ liệu/trạng thái liên quan nếu có.',
        'create' => 'Render hoặc chuẩn bị dữ liệu cho màn hình tạo ' . $subject . '.',
        'edit' => 'Render màn hình chỉnh sửa ' . $subject . ' với dữ liệu người dùng hiện tại.',
    ];
    $description = $map[$name] ?? null;
    $features = bodyFeatures($body);
    if ($description !== null) {
        if ($features) $description .= ' Hàm còn ' . implode(', ', $features) . '.';
        return $description;
    }
    $human = trim(preg_replace('/(?<!^)[A-Z]/', ' $0', $name));
    $description = 'Thực hiện nghiệp vụ “' . $human . '” cho ' . $subject . '.';
    if ($features) $description .= ' Bao gồm: ' . implode(', ', $features) . '.';
    return $description;
}

function businessActionLabel(string $name, string $subject): string
{
    $actions = [
        'index' => 'Xem danh sách', 'show' => 'Xem chi tiết', 'detail' => 'Xem hồ sơ chi tiết',
        'store' => 'Tạo', 'update' => 'Sửa', 'destroy' => 'Xóa', 'approve' => 'Duyệt',
        'reject' => 'Từ chối', 'cancel' => 'Hủy', 'submitForApproval' => 'Gửi duyệt',
        'toggleStatus' => 'Bật/tắt trạng thái', 'all' => 'Lấy danh sách lựa chọn',
        'active' => 'Lấy danh sách đang hoạt động', 'forSelect' => 'Lấy danh sách lựa chọn',
        'select' => 'Lấy danh sách lựa chọn', 'warehouseIndex' => 'Xem danh sách chờ kho',
        'availableForExport' => 'Kiểm tra số lượng có thể xuất', 'stockOutData' => 'Chuẩn bị tạo phiếu xuất',
        'stockInData' => 'Chuẩn bị tạo phiếu nhập', 'exchangeRate' => 'Lấy tỷ giá giao dịch',
        'orderOutstanding' => 'Tính số tiền đơn còn phải thu/trả', 'ledger' => 'Xem sổ cái',
        'rebuildBalance' => 'Tính lại số dư', 'accountantApprove' => 'Kế toán duyệt',
        'confirmDelivery' => 'Xác nhận giao hàng', 'assignShipping' => 'Gán đơn vị vận chuyển',
        'requestDeliveryReturn' => 'Yêu cầu hoàn hàng giao', 'receiveDeliveryReturn' => 'Nhận hàng hoàn',
        'accountantApproveDeliveryReturn' => 'Kế toán duyệt hàng hoàn', 'ordersForImport' => 'Lấy đơn có thể nhập kho',
        'ordersForExport' => 'Lấy đơn có thể xuất kho', 'getStocks' => 'Kiểm tra tồn kho',
        'createDraft' => 'Tạo đơn nháp', 'updateDraft' => 'Sửa đơn nháp', 'cancelDraft' => 'Hủy đơn nháp',
        'drafts' => 'Xem đơn nháp', 'history' => 'Xem lịch sử', 'checkout' => 'Đặt hàng và thanh toán',
        'login' => 'Đăng nhập', 'logout' => 'Đăng xuất', 'register' => 'Đăng ký',
        'markAsRead' => 'Đánh dấu đã đọc', 'markAllAsRead' => 'Đánh dấu tất cả đã đọc',
        'unreadCount' => 'Đếm thông báo chưa đọc', 'rates' => 'Xem lịch sử tỷ giá',
        'storeRate' => 'Thêm tỷ giá', 'permissions' => 'Lấy danh sách quyền',
        'roles' => 'Lấy danh sách vai trò', 'products' => 'Xem danh sách sản phẩm',
        'product' => 'Xem chi tiết sản phẩm', 'vouchers' => 'Lấy mã giảm giá khả dụng',
        'createQuickOrder' => 'Tạo nhanh đơn hàng', 'trace' => 'Truy vết nhật ký',
        'overview' => 'Xem tổng quan', 'module' => 'Xem số liệu phân hệ', 'landing' => 'Mở trang tổng quan',
        'create' => 'Mở trang tạo', 'edit' => 'Mở trang chỉnh sửa',
        'managers' => 'Lấy danh sách quản lý', 'role' => 'Lấy vai trò',
        'options' => 'Lấy dữ liệu lựa chọn', 'storeCustomer' => 'Tạo nhanh khách hàng',
        'usages' => 'Xem lịch sử sử dụng', 'accountPage' => 'Mở trang tài khoản',
        'addresses' => 'Xem danh sách địa chỉ', 'cancelOrder' => 'Hủy đơn hàng',
        'cartPage' => 'Mở trang giỏ hàng', 'checkoutPage' => 'Mở trang thanh toán',
        'destroyAddress' => 'Xóa địa chỉ', 'destroyNotification' => 'Xóa thông báo',
        'directory' => 'Mở danh bạ cửa hàng',
        'markAllNotificationsRead' => 'Đánh dấu tất cả thông báo đã đọc',
        'markNotificationRead' => 'Đánh dấu thông báo đã đọc',
        'me' => 'Lấy thông tin tài khoản hiện tại',
        'notificationHistory' => 'Xem lịch sử thông báo',
        'notificationPage' => 'Mở trang thông báo', 'notifications' => 'Xem danh sách thông báo',
        'notificationUnreadCount' => 'Đếm thông báo chưa đọc',
        'orderPage' => 'Mở trang chi tiết đơn', 'orders' => 'Xem danh sách đơn',
        'productPage' => 'Mở trang sản phẩm', 'shop' => 'Mở cửa hàng',
        'storeAddress' => 'Tạo địa chỉ', 'successPage' => 'Mở trang đặt hàng thành công',
        'updateAddress' => 'Sửa địa chỉ', 'updatePassword' => 'Đổi mật khẩu',
        'updateProfile' => 'Sửa hồ sơ', 'shippingPartners' => 'Lấy danh sách đối tác vận chuyển',
        'storeShippingPartner' => 'Tạo đối tác vận chuyển',
        'storePartner' => 'Tạo đối tác đối soát',
        'handleGoogleCallback' => 'Xử lý đăng nhập Google',
        'provinces' => 'Lấy danh sách tỉnh/thành',
        'redirectToGoogle' => 'Chuyển tới đăng nhập Google',
        'wards' => 'Lấy danh sách phường/xã',
    ];
    if (isset($actions[$name])) return $actions[$name] . ' ' . $subject;
    $human = trim(preg_replace('/(?<!^)[A-Z]/', ' $0', $name));
    return ucfirst($human) . ' — ' . $subject;
}

function validationFields(string $body): array
{
    $fields = [];
    $offset = 0;
    while (($validateAt = strpos($body, '->validate(', $offset)) !== false) {
        $start = strpos($body, '[', $validateAt);
        if ($start === false) break;
        $depth = 0;
        $end = null;
        for ($index = $start; $index < strlen($body); $index++) {
            if ($body[$index] === '[') $depth++;
            if ($body[$index] === ']' && --$depth === 0) {
                $end = $index;
                break;
            }
        }
        if ($end === null) break;
        $rules = substr($body, $start, $end - $start + 1);
        if (preg_match_all('/[\'\"]([A-Za-z_][A-Za-z0-9_]*(?:\.\*)?)[\'\"]\s*=>/', $rules, $matches)) {
            foreach ($matches[1] as $field) $fields[] = $field;
        }
        $offset = $end + 1;
    }
    return array_values(array_unique($fields));
}

function debugDetails(string $body): string
{
    $parts = [];
    $fields = validationFields($body);
    if ($fields) {
        $shown = array_slice($fields, 0, 12);
        $parts[] = '**Input cần kiểm tra:** `' . implode('`, `', $shown) . '`'
            . (count($fields) > 12 ? ' và ' . (count($fields) - 12) . ' trường khác' : '');
    }
    $statuses = [];
    if (preg_match_all('/response\(\)->json\([\s\S]*?,\s*(4\d\d|5\d\d)\s*\)/U', $body, $matches)) $statuses = array_merge($statuses, $matches[1]);
    if (preg_match_all('/abort(?:_if|_unless)?\([^,]+,\s*(4\d\d|5\d\d)/', $body, $matches)) $statuses = array_merge($statuses, $matches[1]);
    if (preg_match('/->validate\(|Validator::make\(|validated\(/', $body)) $statuses[] = '422';
    $statuses = array_values(array_unique($statuses));
    if ($statuses) $parts[] = '**HTTP lỗi cần kiểm tra:** `' . implode('`, `', $statuses) . '`';
    return $parts ? implode('<br>', $parts) : 'Không phát hiện input validation hoặc mã lỗi HTTP viết trực tiếp trong Function.';
}

function symptomCategories(string $name, string $body): array
{
    $categories = [];
    if (preg_match('/->validate\(|Validator::make\(|validated\(|Request\b/', $body)) $categories[] = 'API trả 422 hoặc biểu mẫu báo sai dữ liệu';
    if (preg_match('/abort.*403|authorize\(|Permission|Gate::|can\(/i', $body)) $categories[] = 'API trả 403 hoặc không thấy nút thao tác';
    if (preg_match('/stock|inventory|quantity|warehouse/i', $body)) $categories[] = 'Sai tồn kho, số lượng nhập/xuất hoặc đơn không sang kho';
    if (preg_match('/amount|currency|exchange[_A-Z]?rate|debt|balance|payment/i', $body)) $categories[] = 'Sai số tiền, tỷ giá, số dư hoặc công nợ';
    if (preg_match('/Notification|notify\(|notificationService/i', $body)) $categories[] = 'Không có hoặc sai thông báo';
    if (preg_match('/status|approved|pending|cancelled|rejected/i', $body)
        || preg_match('/approve|reject|cancel|submit|confirm|receive/i', $name)) $categories[] = 'Sai trạng thái hoặc không chuyển được bước nghiệp vụ';
    return array_values(array_unique($categories));
}

function classMethodLine(string $path, string $method): int
{
    $lines = @file($path, FILE_IGNORE_NEW_LINES) ?: [];
    foreach ($lines as $index => $line) {
        if (preg_match('/function\s+' . preg_quote($method, '/') . '\s*\(/', $line)) return $index + 1;
    }
    return 1;
}

function serviceResponsibility(string $service): string
{
    return match ($service) {
        'AccountBalanceService' => 'tính và xây dựng lại số dư tài khoản từ dữ liệu giao dịch',
        'CodReconciliationService' => 'tạo và xử lý phiên đối soát tiền thu hộ COD',
        'CompanyCurrencyService' => 'xác định tiền tệ công ty và tỷ giá tại ngày chứng từ',
        'CouponService' => 'kiểm tra, áp dụng và hoàn tác coupon theo vòng đời đơn',
        'CustomerDebtService' => 'ghi nhận, thanh toán và tính công nợ khách hàng',
        'DashboardService' => 'tổng hợp dữ liệu widget theo module và khoảng ngày',
        'InventoryMovementService' => 'ghi biến động tồn và liên kết chứng từ nguồn',
        'LedgerService' => 'ghi và truy vấn bút toán sổ tài khoản',
        'NotificationService' => 'tạo và phân phối thông báo nội bộ',
        'OrderQuantityValidationService' => 'kiểm tra số lượng lẻ theo cấu hình đơn vị tính; không kiểm tra tồn kho',
        'StockService' => 'áp biến động phiếu kho vào tồn sản phẩm theo kho',
        'SupplierDebtService' => 'ghi nhận, thanh toán và tính công nợ nhà cung cấp',
        'TransactionService' => 'tạo/duyệt giao dịch, cập nhật số dư, ledger và đồng bộ công nợ',
        default => 'đóng gói logic nghiệp vụ dùng lại của phân hệ',
    };
}

function modelResponsibility(string $model): string
{
    return match ($model) {
        'WarehouseProductStock' => 'tồn hiện tại của một sản phẩm tại một kho',
        'InventoryMovement' => 'sổ biến động tồn có truy vết chứng từ nguồn',
        'CustomerDebt' => 'phát sinh và điều chỉnh công nợ khách hàng',
        'SupplierDebt' => 'phát sinh và điều chỉnh công nợ nhà cung cấp',
        'AccountLedger' => 'bút toán làm cơ sở đối chiếu số dư tài khoản',
        'Transaction' => 'chứng từ thu/chi/chuyển tiền và trạng thái duyệt',
        default => 'dữ liệu nghiệp vụ của ' . $model,
    };
}

function functionDependencyLinks(string $root, string $controllerSource, string $body): array
{
    $dependencies = [];
    $serviceProperties = [];
    if (preg_match_all('/([A-Z][A-Za-z0-9_]*Service)\s+\$([A-Za-z_][A-Za-z0-9_]*)/', $controllerSource, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $match) $serviceProperties[$match[2]] = $match[1];
    }
    if (preg_match_all('/\$this->([A-Za-z_][A-Za-z0-9_]*)\s*=\s*\$([A-Za-z_][A-Za-z0-9_]*)/', $controllerSource, $assignments, PREG_SET_ORDER)) {
        foreach ($assignments as $assignment) {
            if (isset($serviceProperties[$assignment[2]])) $serviceProperties[$assignment[1]] = $serviceProperties[$assignment[2]];
        }
    }
    if (preg_match_all('/\$this->([A-Za-z_][A-Za-z0-9_]*)->([A-Za-z_][A-Za-z0-9_]*)\s*\(/', $body, $calls, PREG_SET_ORDER)) {
        foreach ($calls as $call) {
            $service = $serviceProperties[$call[1]] ?? null;
            if (!$service) continue;
            $path = $root . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Services' . DIRECTORY_SEPARATOR . $service . '.php';
            $key = 'service:' . $service . ':' . $call[2];
            $dependencies[$key] = is_file($path)
                ? codeLink($root, $path, classMethodLine($path, $call[2]), $service . '::' . $call[2] . '()') . ' (' . serviceResponsibility($service) . ')'
                : '`' . $service . '::' . $call[2] . '()`';
        }
    }
    if (preg_match_all('/(?<!->)\$([A-Za-z_][A-Za-z0-9_]*)->([A-Za-z_][A-Za-z0-9_]*)\s*\(/', $body, $localCalls, PREG_SET_ORDER)) {
        foreach ($localCalls as $call) {
            $service = $serviceProperties[$call[1]] ?? null;
            if (!$service) continue;
            $path = $root . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Services' . DIRECTORY_SEPARATOR . $service . '.php';
            $dependencies['service:' . $service . ':' . $call[2]] = is_file($path)
                ? codeLink($root, $path, classMethodLine($path, $call[2]), $service . '::' . $call[2] . '()') . ' (' . serviceResponsibility($service) . ')'
                : '`' . $service . '::' . $call[2] . '()`';
        }
    }
    if (preg_match_all('/\b([A-Z][A-Za-z0-9_]*Service)::([A-Za-z_][A-Za-z0-9_]*)\s*\(/', $body, $staticCalls, PREG_SET_ORDER)) {
        foreach ($staticCalls as $call) {
            $path = $root . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Services' . DIRECTORY_SEPARATOR . $call[1] . '.php';
            $dependencies['service:' . $call[1] . ':' . $call[2]] = is_file($path)
                ? codeLink($root, $path, classMethodLine($path, $call[2]), $call[1] . '::' . $call[2] . '()') . ' (' . serviceResponsibility($call[1]) . ')'
                : '`' . $call[1] . '::' . $call[2] . '()`';
        }
    }
    if (preg_match_all('/\b([A-Z][A-Za-z0-9_]*)::(?:query|with|find|findOrFail|where|create|updateOrCreate|firstOrCreate)\s*\(/', $body, $modelCalls, PREG_SET_ORDER)) {
        foreach ($modelCalls as $call) {
            $path = $root . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Models' . DIRECTORY_SEPARATOR . $call[1] . '.php';
            if (is_file($path)) $dependencies['model:' . $call[1]] = codeLink($root, $path, 1, 'Model ' . $call[1]) . ' (' . modelResponsibility($call[1]) . ')';
        }
    }
    return array_values($dependencies);
}

function serviceMethodsCalledByControllers(string $serviceName, array $controllerNames, array $controllerByShort): array
{
    $methods = [];
    foreach ($controllerNames as $controllerName) {
        $controller = $controllerByShort[$controllerName] ?? null;
        if (!$controller) continue;
        $source = @file_get_contents($controller['path']) ?: '';
        $properties = [];
        if (preg_match_all('/' . preg_quote($serviceName, '/') . '\s+\$([A-Za-z_][A-Za-z0-9_]*)/', $source, $matches)) {
            foreach ($matches[1] as $property) $properties[$property] = true;
        }
        if (preg_match_all('/\$this->([A-Za-z_][A-Za-z0-9_]*)\s*=\s*\$([A-Za-z_][A-Za-z0-9_]*)/', $source, $assignments, PREG_SET_ORDER)) {
            foreach ($assignments as $assignment) {
                if (isset($properties[$assignment[2]])) $properties[$assignment[1]] = true;
            }
        }
        foreach (array_keys($properties) as $property) {
            if (preg_match_all('/(?:\$this->)?\$?' . preg_quote($property, '/') . '->([A-Za-z_][A-Za-z0-9_]*)\s*\(/', $source, $calls)) {
                foreach ($calls[1] as $method) $methods[$method] = true;
            }
        }
        if (preg_match_all('/\b' . preg_quote($serviceName, '/') . '::([A-Za-z_][A-Za-z0-9_]*)\s*\(/', $source, $staticCalls)) {
            foreach ($staticCalls[1] as $method) $methods[$method] = true;
        }
    }
    return array_keys($methods);
}

function modelTableName(string $modelPath, string $model): string
{
    $source = @file_get_contents($modelPath) ?: '';
    if (preg_match('/\$table\s*=\s*[\'\"]([^\'\"]+)/', $source, $match)) return $match[1];
    $snake = strtolower((string) preg_replace('/(?<!^)[A-Z]/', '_$0', $model));
    if (str_ends_with($snake, 'y')) return substr($snake, 0, -1) . 'ies';
    if (preg_match('/(?:s|x|z|ch|sh)$/', $snake)) return $snake . 'es';
    return $snake . 's';
}

function migrationLinksForTable(string $root, string $table): array
{
    static $cache = [];
    if (isset($cache[$table])) return $cache[$table];
    $links = [];
    foreach (files($root, 'database/migrations', ['php']) as $path) {
        $lines = @file($path, FILE_IGNORE_NEW_LINES) ?: [];
        foreach ($lines as $index => $line) {
            if (preg_match('/Schema::(?:create|table)\s*\(\s*[\'\"]' . preg_quote($table, '/') . '[\'\"]/', $line)) {
                $links[] = codeLink($root, $path, $index + 1, basename($path));
                break;
            }
        }
    }
    return $cache[$table] = $links;
}

function pageName(string $root, string $path): string
{
    $relative = relativePath($root, $path);
    if (str_starts_with($relative, 'resources/js/components/')) {
        return 'Component ' . basename($path, '.' . pathinfo($path, PATHINFO_EXTENSION));
    }
    $known = [
        'resources/js/Pages/Sale/Order/Index.vue' => 'Trang Đơn bán',
        'resources/js/Pages/Sale/Order/SaleOrderForm.vue' => 'Form tạo/sửa đơn bán',
        'resources/js/Pages/Sale/Customer/CustomerDetail.vue' => 'Trang chi tiết khách hàng',
        'resources/js/Pages/Warehouse/Order/Index.vue' => 'Trang Đơn chờ kho',
        'resources/js/Pages/Warehouse/Slip/Salecreate.vue' => 'Trang tạo phiếu xuất',
        'resources/js/Pages/Warehouse/Slip/Purchasecreate.vue' => 'Trang tạo phiếu nhập',
        'resources/js/Pages/Warehouse/Slip/Index.vue' => 'Trang Phiếu kho',
        'resources/js/Pages/Accountant/Transaction/TransactionForm.vue' => 'Form giao dịch kế toán',
        'resources/js/Pages/Storefront/Account.vue' => 'Trang tài khoản cửa hàng trực tuyến',
        'resources/js/Pages/Storefront/OrderDetail.vue' => 'Trang chi tiết đơn cửa hàng trực tuyến',
        'resources/js/Pages/Storefront/Checkout.vue' => 'Trang thanh toán cửa hàng trực tuyến',
        'resources/js/Pages/Sale/Pos/Index.vue' => 'Trang bán hàng POS',
    ];
    if (isset($known[$relative])) return $known[$relative];
    $parts = explode('/', $relative);
    $file = basename($path, '.vue');
    $parent = count($parts) > 1 ? $parts[count($parts) - 2] : '';
    $source = $file === 'Index' ? $parent : $file;
    $phrases = [
        'AccountLedger' => 'Sổ cái tài khoản', 'AuditLogDetail' => 'Chi tiết nhật ký hoạt động',
        'CategoryForm' => 'Biểu mẫu danh mục', 'CodReconciliation' => 'Đối soát COD',
        'CouponForm' => 'Biểu mẫu mã giảm giá', 'CustomerDetail' => 'Chi tiết khách hàng',
        'CustomerForm' => 'Biểu mẫu khách hàng', 'CurrencyForm' => 'Biểu mẫu tiền tệ',
        'Department' => 'Phòng ban', 'PermissionForm' => 'Biểu mẫu quyền', 'Permission' => 'Quyền',
        'Position' => 'Chức vụ', 'RoleForm' => 'Biểu mẫu vai trò', 'Role' => 'Vai trò',
        'UserDetail' => 'Chi tiết người dùng', 'UserForm' => 'Biểu mẫu người dùng', 'User' => 'Người dùng',
        'PurchaseOrderDetail' => 'Chi tiết đơn mua', 'PurchaseOrderForm' => 'Biểu mẫu đơn mua',
        'SlipDetail' => 'Chi tiết phiếu kho', 'WarehouseDetail' => 'Chi tiết kho',
        'AccountForm' => 'Biểu mẫu tài khoản', 'BankForm' => 'Biểu mẫu ngân hàng',
        'CurencyRateHistory' => 'Lịch sử tỷ giá', 'ProfitLoss' => 'Báo cáo lãi lỗ',
        'AuditLog' => 'Nhật ký hoạt động',
        'ProductForm' => 'Biểu mẫu sản phẩm', 'SupplierDetail' => 'Chi tiết nhà cung cấp',
        'SupplierForm' => 'Biểu mẫu nhà cung cấp', 'UnitForm' => 'Biểu mẫu đơn vị tính',
        'SaleOrderDetail' => 'Chi tiết đơn bán', 'SaleOrderForm' => 'Biểu mẫu đơn bán',
        'TransactionDetail' => 'Chi tiết giao dịch', 'TransactionForm' => 'Biểu mẫu giao dịch',
        'TransactionCategoryForm' => 'Biểu mẫu loại giao dịch', 'ProfitLossReport' => 'Báo cáo lãi lỗ',
        'InventoryMovement' => 'Biến động tồn kho', 'Purchasecreate' => 'Tạo phiếu nhập',
        'Salecreate' => 'Tạo phiếu xuất', 'WarehouseForm' => 'Biểu mẫu kho',
        'ProductDetail' => 'Chi tiết sản phẩm', 'OrderDetail' => 'Chi tiết đơn hàng',
        'Dashboard' => 'Bảng điều khiển', 'DashBoard' => 'Bảng điều khiển',
        'NotificationHistory' => 'Lịch sử thông báo', 'Notifications' => 'Thông báo',
        'Account' => 'Tài khoản', 'Accountant' => 'Kế toán', 'Bank' => 'Ngân hàng',
        'Category' => 'Danh mục', 'Coupon' => 'Mã giảm giá', 'Customer' => 'Khách hàng',
        'Currency' => 'Tiền tệ', 'Order' => 'Đơn hàng', 'Product' => 'Sản phẩm',
        'Supplier' => 'Nhà cung cấp', 'Unit' => 'Đơn vị tính', 'Warehouse' => 'Kho',
        'Transaction' => 'Giao dịch', 'TransactionCategory' => 'Loại giao dịch',
        'Transfer' => 'Chuyển kho', 'Slip' => 'Phiếu kho', 'Pos' => 'Bán hàng POS',
        'Create' => 'Tạo công ty', 'Edit' => 'Chỉnh sửa hồ sơ',
        'Guide' => 'Hướng dẫn', 'Directory' => 'Danh bạ cửa hàng', 'Cart' => 'Giỏ hàng',
        'Checkout' => 'Thanh toán', 'Shop' => 'Cửa hàng', 'Success' => 'Đặt hàng thành công',
        'Document' => 'Tài liệu', 'Page' => 'Trang nội dung', 'Profile' => 'Hồ sơ',
    ];
    if ($source === 'Home') return 'Trang chủ';
    if ($source === 'Page') return 'Trang nội dung';
    if ($relative === 'resources/js/Pages/Purchase/Order/Index.vue') return 'Trang Đơn mua';
    if (isset($phrases[$source])) return 'Trang ' . $phrases[$source];
    $words = preg_split('/(?=[A-Z])/', $source, -1, PREG_SPLIT_NO_EMPTY);
    $wordMap = ['Form' => 'Biểu mẫu', 'Detail' => 'Chi tiết', 'Index' => 'Danh sách', 'Report' => 'Báo cáo', 'History' => 'Lịch sử', 'Create' => 'Tạo mới', 'Edit' => 'Chỉnh sửa'];
    $translated = array_map(fn ($word) => $wordMap[$word] ?? $word, $words);
    return 'Trang ' . implode(' ', $translated);
}

function businessPageName(string $root, string $path): string
{
    $label = pageName($root, $path);
    $relative = relativePath($root, $path);
    $moduleLabels = [
        'resources/js/Pages/Purchase/' => 'Mua hàng',
        'resources/js/Pages/Warehouse/' => 'Kho',
        'resources/js/Pages/Sale/' => 'Bán hàng',
        'resources/js/Pages/Accountant/' => 'Kế toán',
        'resources/js/Pages/Manage/' => 'Quản trị',
        'resources/js/Pages/Storefront/' => 'Cửa hàng trực tuyến',
    ];
    foreach ($moduleLabels as $prefix => $moduleLabel) {
        if (str_starts_with($relative, $prefix)) return $label . ' — ' . $moduleLabel;
    }
    return $label;
}

function pageEffect(string $name, string $subject, string $path): string
{
    $file = basename($path);
    $normalized = str_replace('\\', '/', $path);
    if (str_contains($normalized, '/Accountant/Transaction/TransactionForm.vue')) {
        return 'danh sách đơn có thể chọn, số tiền còn phải thu/trả và dữ liệu dùng để lập giao dịch có thể thay đổi';
    }
    if (str_contains($normalized, '/Sale/Customer/CustomerDetail.vue')) {
        return 'lịch sử đơn của khách hàng và nội dung chi tiết đơn được mở từ hồ sơ khách hàng có thể thay đổi';
    }
    if (str_contains($normalized, '/Warehouse/Order/Index.vue')) {
        return 'đơn có xuất hiện trong hàng chờ kho hay không, trạng thái đã nhập/xuất và nút tạo phiếu có thể thay đổi';
    }
    if (str_contains($normalized, '/Warehouse/Slip/Salecreate.vue')) {
        return 'dữ liệu đơn khởi tạo phiếu, kho được chọn, lượng đã xuất/còn được xuất và nút lưu phiếu có thể thay đổi';
    }
    if (str_contains($normalized, '/Warehouse/Slip/Purchasecreate.vue')) {
        return 'dữ liệu đơn khởi tạo phiếu, kho được chọn, lượng đã nhập/còn được nhập và nút lưu phiếu có thể thay đổi';
    }
    if (str_contains($normalized, '/Sale/Order/SaleOrderForm.vue')) {
        return $name === 'store'
            ? 'việc gửi biểu mẫu tạo đơn, lỗi kiểm tra dữ liệu, số tiền/mã giảm giá được lưu và đơn mới xuất hiện trong danh sách có thể thay đổi'
            : 'dữ liệu nạp vào biểu mẫu, lỗi kiểm tra dữ liệu và kết quả lưu đơn/dòng sản phẩm/mã giảm giá có thể thay đổi';
    }
    if (str_contains($normalized, '/Sale/Order/Index.vue')) {
        $saleEffects = [
            'index' => 'các dòng đơn, bộ lọc, phân trang và giá/VAT/phí/giảm giá/tổng tiền trong danh sách có thể thay đổi',
            'show' => 'modal chi tiết và dữ liệu dùng để mở form sửa có thể sai/thiếu hoặc không mở được',
            'approve' => 'nút duyệt, trạng thái đơn sau duyệt và thời điểm đơn đủ điều kiện chuyển sang kho có thể thay đổi',
            'cancel' => 'nút hủy, thông báo lỗi và trạng thái đơn sau hủy có thể thay đổi',
            'destroy' => 'nút xóa, điều kiện được xóa và việc dòng đơn biến mất khỏi danh sách có thể thay đổi',
            'submitForApproval' => 'nút gửi duyệt, trạng thái `draft/pending` và thông báo cho người duyệt có thể thay đổi',
        ];
        return $saleEffects[$name] ?? ('dữ liệu lựa chọn hoặc hành động liên quan đến ' . $subject . ' trên trang đơn bán có thể thay đổi');
    }
    if (in_array($name, ['index', 'all', 'active', 'forSelect', 'select', 'products', 'orders', 'history', 'drafts'], true)) {
        return 'danh sách, bộ lọc, phân trang hoặc dữ liệu lựa chọn có thể hiển thị thiếu/thừa/sai giá trị';
    }
    if (in_array($name, ['show', 'detail', 'orderPage', 'product', 'me'], true)) {
        return 'các trường chi tiết, quan hệ và số liệu trong modal/trang chi tiết có thể thay đổi hoặc không tải được';
    }
    if (in_array($name, ['store', 'checkout', 'register', 'createDraft', 'storeCustomer', 'storeAddress'], true)) {
        return 'dữ liệu gửi lên khi tạo mới, lỗi kiểm tra dữ liệu và dữ liệu xuất hiện sau khi lưu sẽ thay đổi';
    }
    if (in_array($name, ['update', 'updateDraft', 'updateProfile', 'updateAddress', 'updatePassword'], true)) {
        return 'giá trị biểu mẫu được lưu, lỗi kiểm tra dữ liệu và dữ liệu tải lại sau khi sửa sẽ thay đổi';
    }
    if (in_array($name, ['destroy', 'cancel', 'cancelOrder', 'cancelDraft'], true)) {
        return 'nút hủy/xóa, điều kiện được phép thao tác và trạng thái/dòng dữ liệu sau thao tác sẽ thay đổi';
    }
    if (in_array($name, ['approve', 'accountantApprove', 'submitForApproval', 'reject', 'resubmit'], true)) {
        return 'nút duyệt/gửi/từ chối, trạng thái hiển thị và khả năng chứng từ đi tiếp sang bước nghiệp vụ sau sẽ thay đổi';
    }
    if (in_array($name, ['warehouseIndex', 'ordersForImport', 'ordersForExport'], true)) {
        return 'đơn xuất hiện trong hàng chờ kho, trạng thái xử lý và hành động nhập/xuất có thể thay đổi';
    }
    if (in_array($name, ['availableForExport', 'stockOutData', 'stockInData', 'getStocks'], true)) {
        return 'kho/sản phẩm được chọn, số lượng khả dụng và lý do chặn nhập/xuất có thể thay đổi';
    }
    if (str_contains($file, 'Report') || str_contains(strtolower($name), 'overview')) {
        return 'chỉ số, tổng hợp và biểu đồ báo cáo có thể thay đổi';
    }
    if (str_contains(strtolower($name), 'notification') || str_contains(strtolower($name), 'read')) {
        return 'số thông báo, trạng thái đã đọc và danh sách thông báo có thể thay đổi';
    }
    return 'dữ liệu hoặc hành động liên quan đến ' . $subject . ' trên trang này có thể thay đổi';
}

function indirectPageImpacts(string $root, string $class, string $name): array
{
    $shortClass = substr($class, strrpos($class, '\\') + 1);
    $map = [
        'SalesOrderController.approve' => [
            ['resources/js/Pages/Warehouse/Order/Index.vue', 586, 'sau khi duyệt, đơn có thể xuất hiện hoặc không xuất hiện trong danh sách chờ xuất kho'],
        ],
        'PurchaseOrderController.approve' => [
            ['resources/js/Pages/Warehouse/Order/Index.vue', 569, 'sau khi duyệt, đơn có thể xuất hiện hoặc không xuất hiện trong danh sách chờ nhập kho'],
        ],
        'WarehouseSlipController.accountantApprove' => [
            ['resources/js/Pages/Warehouse/Order/Index.vue', 569, 'lượng đã nhập/xuất và trạng thái hoàn thành của đơn chờ kho có thể thay đổi'],
            ['resources/js/Pages/Warehouse/InventoryMovement/Index.vue', 1, 'biến động tồn kho phát sinh sau duyệt có thể thay đổi hoặc không được ghi nhận'],
        ],
        'WarehouseTransferController.approve' => [
            ['resources/js/Pages/Warehouse/InventoryMovement/Index.vue', 1, 'biến động giảm kho nguồn và tăng kho đích có thể thay đổi'],
        ],
    ];
    $results = [];
    foreach ($map[$shortClass . '.' . $name] ?? [] as [$relative, $line, $effect]) {
        $path = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
        if (!is_file($path)) continue;
        $results[] = '<br>• ' . codeLink($root, $path, $line, pageName($root, $path)) . ' (gián tiếp): ' . $effect . '.';
    }
    return $results;
}

function backendImpact(string $name, string $subject, string $body): string
{
    $parts = [];
    if (in_array($name, ['index', 'all', 'active', 'select', 'forSelect', 'show', 'detail'], true)) {
        $parts[] = 'Kết quả truy vấn ' . $subject . ', điều kiện lọc, quan hệ nạp kèm hoặc cấu trúc JSON trả về có thể thay đổi';
    } elseif (in_array($name, ['store', 'create', 'checkout', 'register', 'createDraft'], true)) {
        $parts[] = 'Quy tắc tạo ' . $subject . ', dữ liệu được ghi và lỗi trả về khi tạo có thể thay đổi';
    } elseif (in_array($name, ['update', 'updateDraft', 'toggleStatus', 'markAsRead', 'markAllAsRead'], true)) {
        $parts[] = 'Các trường được cập nhật, điều kiện cho phép sửa và trạng thái sau thao tác của ' . $subject . ' có thể thay đổi';
    } elseif (in_array($name, ['destroy', 'delete', 'cancel', 'cancelOrder', 'cancelDraft'], true)) {
        $parts[] = 'Điều kiện xóa/hủy, dữ liệu phụ thuộc và khả năng khôi phục trạng thái của ' . $subject . ' có thể thay đổi';
    } elseif (preg_match('/approve|reject|submit|confirm|receive|resubmit/i', $name)) {
        $parts[] = 'Điều kiện chuyển trạng thái, người được phép thao tác và bước nghiệp vụ kế tiếp của ' . $subject . ' có thể thay đổi';
    } else {
        $parts[] = 'Logic xử lý ' . $subject . ', dữ liệu đầu ra hoặc điều kiện nghiệp vụ của Function có thể thay đổi';
    }
    if (preg_match('/stock|inventory|quantity|warehouse/i', $body)) $parts[] = 'Cần đối chiếu tồn kho, lượng giữ chỗ, lượng đã nhập/xuất và biến động kho';
    if (preg_match('/amount|currency|exchange[_A-Z]?rate|debt|balance|payment/i', $body)) $parts[] = 'Cần đối chiếu số tiền, tỷ giá, số dư tài khoản và công nợ trước/sau';
    if (preg_match('/coupon|voucher|discount/i', $body)) $parts[] = 'Cần kiểm tra điều kiện áp dụng, số lần sử dụng và hoàn tác mã giảm giá';
    if (preg_match('/Notification|notify\(|notificationService/i', $body)) $parts[] = 'Cần kiểm tra người nhận, nội dung và thời điểm phát thông báo';
    if (preg_match('/DB::transaction|DB::beginTransaction/i', $body)) $parts[] = 'Cần bảo đảm toàn bộ thay đổi trong transaction cùng thành công hoặc cùng rollback';
    return implode('. ', array_values(array_unique($parts))) . '.';
}

function impactAssessment(string $name, string $visibility, string $body, array $dependencies = [], array $pages = []): array
{
    if ($name === '__construct') {
        return ['Cao', 'thay đổi dependency khởi tạo có thể làm tất cả endpoint của lớp không hoạt động'];
    }
    $isWrite = preg_match('/^(store|create|update|destroy|delete|approve|reject|cancel|toggle|assign|confirm|receive|request|mark|submit|resubmit|checkout|register|login|logout)/i', $name)
        || preg_match('/->(?:create|update|delete|save|increment|decrement)\s*\(|::(?:create|updateOrCreate|firstOrCreate)\s*\(/', $body);
    $touchesInventory = preg_match('/stock|inventory|quantity|warehouse/i', $body);
    $touchesFinance = preg_match('/amount|currency|exchange[_A-Z]?rate|debt|balance|payment|ledger/i', $body);
    $hasTransaction = preg_match('/DB::transaction|DB::beginTransaction/i', $body);
    $changesWorkflow = preg_match('/status|approved|pending|cancelled|rejected/i', $body)
        || preg_match('/approve|reject|cancel|submit|confirm|receive|resubmit/i', $name);
    $fanOut = count($dependencies) + count($pages);

    if ($isWrite && (($touchesInventory && $touchesFinance) || ($hasTransaction && $fanOut >= 4))) {
        return ['Rất cao', 'có ghi dữ liệu và tác động chéo tồn kho/tài chính hoặc transaction với nhiều nơi phụ thuộc'];
    }
    if ($isWrite && ($touchesInventory || $touchesFinance || $hasTransaction || $changesWorkflow)) {
        $areas = [];
        if ($touchesInventory) $areas[] = 'tồn kho';
        if ($touchesFinance) $areas[] = 'tiền tệ/công nợ/số dư';
        if ($changesWorkflow) $areas[] = 'trạng thái nghiệp vụ';
        if ($hasTransaction) $areas[] = 'transaction dữ liệu';
        return ['Cao', 'có ghi dữ liệu và liên quan ' . implode(', ', array_unique($areas))];
    }
    if ($isWrite || $visibility !== 'public' || $fanOut >= 5) {
        return ['Trung bình', $isWrite
            ? 'thay đổi dữ liệu hoặc validation nhưng chưa phát hiện tác động chéo tồn kho/tài chính'
            : 'là hàm hỗ trợ/dùng chung hoặc có nhiều nơi phụ thuộc cần kiểm tra'];
    }
    return ['Thấp', 'chủ yếu đọc/chuẩn bị dữ liệu; rủi ro chính nằm ở cấu trúc dữ liệu trả về và giao diện hiển thị'];
}

function impactAssessmentText(array $assessment): string
{
    return '**' . $assessment[0] . '** — ' . $assessment[1] . '.';
}

function quickFixPath(string $root, array $pages, array $routes, string $controllerLink, array $dependencies, array $tests): string
{
    $steps = [];
    $steps[] = $pages ? 'FE ' . referenceLinks($root, $pages) : 'FE: phân tích tĩnh chưa ánh xạ được trang gọi trực tiếp';
    $routeLabels = [];
    foreach ($routes as $route) $routeLabels[] = '`' . str_replace('|', '/', $route['method']) . ' /' . $route['uri'] . '`';
    $steps[] = $routeLabels ? 'API ' . implode(', ', $routeLabels) : 'API: chưa có route trực tiếp';
    $steps[] = $controllerLink;
    $steps[] = $dependencies ? 'Service/Model ' . implode(', ', $dependencies) : 'Service/Model: chưa phát hiện lời gọi trực tiếp';
    $steps[] = $tests ? 'Test ' . referenceLinks($root, $tests) : 'Test: chưa ánh xạ được test trực tiếp';
    return implode(' → ', $steps);
}

function impact(string $root, string $class, string $name, string $visibility, string $subject, string $body, bool $hasRoute, array $pages, array $tests, array $callers): string
{
    if ($name === '__construct') return 'Ảnh hưởng việc khởi tạo controller và tất cả endpoint của controller nếu dependency thay đổi.';
    if ($visibility !== 'public') {
        return $callers ? 'Ảnh hưởng các hàm gọi hàm hỗ trợ trong cùng bộ điều khiển.' : 'Ảnh hưởng nội bộ bộ điều khiển; chưa tìm thấy lời gọi trực tiếp bằng phân tích tĩnh.';
    }
    $parts = [backendImpact($name, $subject, $body)];
    foreach ($pages as $page) {
        $label = pageName($root, $page['path']);
        $link = codeLink($root, $page['path'], $page['line'], $label);
        $parts[] = '<br>• ' . $link . ': ' . pageEffect($name, $subject, $page['path']) . '.';
    }
    $parts = array_merge($parts, indirectPageImpacts($root, $class, $name));
    if (!$hasRoute) $parts[] = 'Phân tích tĩnh chưa thấy route trực tiếp; kiểm tra caller nội bộ, event, job hoặc framework hook trước khi sửa.';
    if (!$pages) $parts[] = 'Phân tích tĩnh chưa ánh xạ được trang Vue/JS trực tiếp; không đồng nghĩa Function không được sử dụng.';
    if (!$tests) $parts[] = 'Phân tích tĩnh chưa ánh xạ được test theo route/Controller; cần tìm theo tên nghiệp vụ hoặc bổ sung test hồi quy.';
    return implode(' ', $parts);
}

function findReferences(string $root, array $paths, array $needles, int $limit = 4): array
{
    $found = [];
    $needles = array_values(array_unique(array_filter($needles, fn ($v) => strlen($v) >= 7)));
    foreach ($paths as $path) {
        $lines = @file($path, FILE_IGNORE_NEW_LINES);
        if ($lines === false) continue;
        foreach ($lines as $index => $line) {
            foreach ($needles as $needle) {
                if (str_contains($line, $needle)) {
                    $found[] = ['path' => $path, 'line' => $index + 1];
                    break;
                }
            }
            if (count($found) >= $limit) return $found;
        }
    }
    return $found;
}

function routeUriPattern(string $uri): string
{
    static $patterns = [];
    if (isset($patterns[$uri])) return $patterns[$uri];
    $parts = preg_split('/(\{[^}]+\})/', '/' . ltrim($uri, '/'), -1, PREG_SPLIT_DELIM_CAPTURE);
    $pattern = '';
    foreach ($parts as $part) {
        if ($part === '') continue;
        $pattern .= preg_match('/^\{[^}]+\}$/', $part)
            ? '(?:\$\{[^}]+\}|[^\/`\'"?\s]+)'
            : preg_quote($part, '/');
    }
    return $patterns[$uri] = '/' . $pattern . '(?=[`\'"?\s),]|$)/';
}

function findRouteReferences(array $paths, array $routes, int $limit = 4): array
{
    $globalUrlAliases = $GLOBALS['frontendUrlAliases'] ?? [];
    $found = [];
    foreach ($paths as $path) {
        $lines = @file($path, FILE_IGNORE_NEW_LINES);
        if ($lines === false) continue;
        $urlAliases = [];
        foreach ($lines as $sourceLine) {
            if (preg_match('/\b([A-Za-z_$][A-Za-z0-9_$]*)\s*=\s*([`\'\"])(\/[^`\'\"]+)\2\s*[,;]/', $sourceLine, $aliasMatch)) {
                $urlAliases[$aliasMatch[1]] = $aliasMatch[3];
            }
        }
        foreach ($lines as $index => $line) {
            $candidateLine = $line;
            foreach ($urlAliases as $alias => $value) {
                $candidateLine = str_replace('${' . $alias . '}', $value, $candidateLine);
            }
            $candidateLines = [$candidateLine];
            if (preg_match_all('/\$\{props\.([A-Za-z_][A-Za-z0-9_]*)\}/', $candidateLine, $propMatches)) {
                foreach (array_unique($propMatches[1]) as $propName) {
                    foreach ($globalUrlAliases[$propName] ?? [] as $value) {
                        $candidateLines[] = str_replace('${props.' . $propName . '}', $value, $candidateLine);
                    }
                }
            }
            foreach ($routes as $route) {
                $uri = '/' . $route['uri'];
                $matchedUri = false;
                foreach ($candidateLines as $candidate) {
                    if (preg_match(routeUriPattern($uri), $candidate)) {
                        $matchedUri = true;
                        break;
                    }
                }
                if (!$matchedUri) continue;
                $context = strtolower(implode(' ', array_slice($lines, max(0, $index - 2), 3)));
                $wideContext = strtolower(implode(' ', array_slice($lines, max(0, $index - 10), 11)));
                $verbs = array_map('strtolower', explode('|', $route['method']));
                $matchedVerb = false;
                foreach ($verbs as $verb) {
                    if ($verb === 'head') continue;
                    if (preg_match('/(?:axios\.|->|\b)(?:' . preg_quote($verb, '/') . '|' . preg_quote($verb, '/') . 'json)\s*\(/', $context)) {
                        $matchedVerb = true;
                        break;
                    }
                    if (in_array($verb, ['put', 'patch', 'delete'], true)
                        && preg_match('/(?:axios\.|\b)post\s*\(/', $wideContext)
                        && preg_match('/[\'\"]_method[\'\"].{0,80}[\'\"]' . preg_quote($verb, '/') . '[\'\"]/i', $wideContext)) {
                        $matchedVerb = true;
                        break;
                    }
                }
                if (!$matchedVerb) continue;
                $found[] = ['path' => $path, 'line' => $index + 1];
                break;
            }
            if (count($found) >= $limit) return $found;
        }
    }
    return $found;
}

function renderedPageReferences(string $root, array $function): array
{
    if (!preg_match_all('/(?:Inertia::render|inertia)\s*\(\s*[\'\"]([^\'\"]+)[\'\"]/', $function['body'], $matches)) {
        return [];
    }
    $references = [];
    foreach (array_unique($matches[1]) as $component) {
        $path = $root . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'js'
            . DIRECTORY_SEPARATOR . 'Pages' . DIRECTORY_SEPARATOR
            . str_replace('/', DIRECTORY_SEPARATOR, $component) . '.vue';
        if (!is_file($path)) continue;
        $references[] = ['path' => $path, 'line' => 1, 'source' => 'render'];
    }
    return $references;
}

function mergePageReferences(array ...$groups): array
{
    $merged = [];
    foreach ($groups as $references) {
        foreach ($references as $reference) {
            $pathKey = strtolower(str_replace('\\', '/', $reference['path']));
            if (!isset($merged[$pathKey])) $merged[$pathKey] = $reference;
        }
    }
    return array_values($merged);
}

function functionPageReferences(string $root, array $frontendFiles, array $routes, array $function, int $limit = 20): array
{
    // Route gốc "/" không được dò bằng chuỗi vì ký tự / xuất hiện trong hầu hết
    // URL/import của frontend và tạo ra rất nhiều kết quả dương tính giả.
    // Trang của route gốc được xác định chính xác qua Inertia::render bên dưới.
    $directRoutes = array_values(array_filter($routes, fn ($route) => trim((string) ($route['uri'] ?? ''), '/') !== ''));
    $direct = findRouteReferences($frontendFiles, $directRoutes, $limit);
    $importingPages = [];
    foreach ($direct as $reference) {
        $normalized = str_replace('\\', '/', $reference['path']);
        if (!str_contains($normalized, '/resources/js/components/')) continue;
        $componentFile = basename($reference['path']);
        foreach ($frontendFiles as $candidate) {
            $candidateNormalized = str_replace('\\', '/', $candidate);
            if (!str_contains($candidateNormalized, '/resources/js/Pages/')) continue;
            foreach (@file($candidate, FILE_IGNORE_NEW_LINES) ?: [] as $index => $line) {
                if (str_contains($line, $componentFile)) {
                    $importingPages[] = ['path' => $candidate, 'line' => $index + 1, 'source' => 'component-import'];
                    break;
                }
            }
        }
    }
    return mergePageReferences(
        $direct,
        $importingPages,
        renderedPageReferences($root, $function)
    );
}

function referenceLinks(string $root, array $references): string
{
    if (!$references) return '—';
    return implode('<br>', array_map(function ($ref) use ($root) {
        $label = basename($ref['path']) . ':' . $ref['line'];
        return codeLink($root, $ref['path'], $ref['line'], $label);
    }, $references));
}

$command = escapeshellarg(PHP_BINARY) . ' artisan route:list --json';
$previous = getcwd();
chdir($root);
$routeJson = shell_exec($command);
chdir($previous ?: $root);
$routes = json_decode((string) $routeJson, true);
if (!is_array($routes)) {
    fwrite(STDERR, "Không đọc được route:list --json.\n");
    exit(1);
}
$GLOBALS['allKnownRoutes'] = $routes;

$routeMap = [];
$staticRouteUrisByMethod = [];
foreach ($routes as $route) {
    // route:list có thể trả URI gốc là "/"; lưu URI không có dấu / đầu
    // để mọi chỗ hiển thị ghép thành đúng "/" thay vì "//".
    $route['uri'] = ltrim((string) ($route['uri'] ?? ''), '/');
    if (!str_contains($route['uri'] ?? '', '{')) {
        foreach (explode('|', $route['method'] ?? '') as $verb) $staticRouteUrisByMethod[$verb][] = $route['uri'];
    }
    if (!str_contains($route['action'] ?? '', '@')) continue;
    [$class, $method] = explode('@', $route['action'], 2);
    $routeMap[$class][$method][] = $route;
}
$GLOBALS['staticRouteUrisByMethod'] = $staticRouteUrisByMethod;

$frontendFiles = array_merge(files($root, 'resources/js/Pages', ['vue', 'js', 'ts']), files($root, 'resources/js/components', ['vue', 'js', 'ts']), files($root, 'resources/js/store', ['js', 'ts']));
$frontendUrlAliases = [];
foreach ($frontendFiles as $frontendPath) {
    foreach (@file($frontendPath, FILE_IGNORE_NEW_LINES) ?: [] as $frontendLine) {
        if (preg_match('/\b([A-Za-z_$][A-Za-z0-9_$]*)\s*=\s*([`\'\"])(\/[^`\'\"]+)\2\s*[,;]/', $frontendLine, $aliasMatch)) {
            $frontendUrlAliases[$aliasMatch[1]][] = $aliasMatch[3];
        }
    }
}
foreach ($frontendUrlAliases as $alias => $values) $frontendUrlAliases[$alias] = array_values(array_unique($values));
$GLOBALS['frontendUrlAliases'] = $frontendUrlAliases;
$testFiles = files($root, 'tests', ['php']);
$controllerFiles = files($root, 'app/Http/Controllers', ['php']);
$controllers = [];
foreach ($controllerFiles as $path) {
    $relative = relativePath($root, $path);
    $class = 'App\\' . str_replace(['app/', '/', '.php'], ['', '\\', ''], $relative);
    $functions = controllerFunctions($root, $path);
    if (!$functions) continue;
    $controllers[] = compact('path', 'relative', 'class', 'functions');
}

$totalFunctions = array_sum(array_map(fn ($c) => count($c['functions']), $controllers));
$out = [];
$out[] = '# Chỉ mục function toàn dự án';
$out[] = '';
$out[] = '> Sinh tự động từ mã nguồn ngày **' . $generatedAt . '**. Nguồn đúng cuối cùng vẫn là mã triển khai và tuyến API. Chạy lại: `php docs/generate_project_function_index.php`.';
$out[] = '';
$out[] = '## Cách đọc';
$out[] = '';
$out[] = '- **Function** mở đúng dòng trong controller.';
$out[] = '- **Route/API** cho biết HTTP method, URI và permission middleware.';
$out[] = '- **Trang gọi trực tiếp** trỏ tới dòng Vue/JS có endpoint tương ứng (kết quả phân tích tĩnh).';
$out[] = '- **Ảnh hưởng khi sửa** phân biệt thao tác đọc và thao tác có khả năng ghi dữ liệu.';
$out[] = '- **Kiểm thử liên quan** là nơi có tuyến API/bộ điều khiển tương ứng; dấu `—` là khoảng trống cần kiểm tra thủ công.';
$out[] = '';
$out[] = '## Tổng quan';
$out[] = '';
$out[] = '| Controller | Function | Có tuyến API |';
$out[] = '| ---: | ---: | ---: |';
$routedCount = 0;
foreach ($controllers as $controller) foreach ($controller['functions'] as $function) if (!empty($routeMap[$controller['class']][$function['name']])) $routedCount++;
$out[] = '| ' . count($controllers) . ' | ' . $totalFunctions . ' | ' . $routedCount . ' |';
$out[] = '';
$out[] = '## Mục lục controller';
$out[] = '';
$functionAnalysis = [];
foreach ($controllers as $controller) {
    $name = basename($controller['path'], '.php');
    $anchor = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $controller['class']));
    $out[] = '- [' . str_replace('App\\Http\\Controllers\\', '', $controller['class']) . '](#' . trim($anchor, '-') . ')';
}

foreach ($controllers as $controller) {
    $short = str_replace('App\\Http\\Controllers\\', '', $controller['class']);
    $subject = controllerSubject($controller['class']);
    $anchor = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $controller['class']));
    $out[] = '';
    $out[] = '<a id="' . trim($anchor, '-') . '"></a>';
    $out[] = '';
    $out[] = '## ' . $short;
    $out[] = '';
    $out[] = 'Controller: ' . codeLink($root, $controller['path'], 1, $controller['relative']);
    $out[] = '';
    $out[] = '| Function | Làm gì | Validation/input và lỗi | Service/model được gọi | Tuyến/API và quyền | Trang gọi trực tiếp | Ảnh hưởng khi sửa | Kiểm thử liên quan |';
    $out[] = '| --- | --- | --- | --- | --- | --- | --- | --- |';
    $source = file_get_contents($controller['path']);
    foreach ($controller['functions'] as $function) {
        $methodRoutes = $routeMap[$controller['class']][$function['name']] ?? [];
        $routeCells = [];
        $needles = [];
        foreach ($methodRoutes as $route) {
            $permission = [];
            foreach (($route['middleware'] ?? []) as $middleware) {
                if (str_contains($middleware, 'PermissionMiddleware:')) $permission[] = substr($middleware, strrpos($middleware, ':') + 1);
            }
            $httpMethod = str_replace('|', '\\|', $route['method']);
            $permission = array_map(fn ($value) => str_replace('|', '\\|', $value), $permission);
            $routeCells[] = '`' . $httpMethod . ' /' . $route['uri'] . '`' . ($permission ? '<br>quyền: `' . implode('`, `', $permission) . '`' : '');
            $uri = '/' . $route['uri'];
            $needles[] = preg_replace('/\{.*$/', '', $uri);
            $needles[] = $uri;
        }
        $pages = functionPageReferences($root, $frontendFiles, $methodRoutes, $function);
        $tests = findRouteReferences($testFiles, $methodRoutes, 3);
        if (!$methodRoutes) {
            $tests = findReferences($root, $testFiles, [basename($controller['path'], '.php')], 3);
        }
        $callerLinks = [];
        foreach ($function['callers'] as $line) $callerLinks[] = codeLink($root, $controller['path'], $line, 'dòng gọi ' . $line);
        $impactText = impact($root, $controller['class'], $function['name'], $function['visibility'], $subject, $function['body'], (bool) $methodRoutes, $pages, $tests, $function['callers']);
        $dependencies = functionDependencyLinks($root, $source, $function['body']);
        $assessment = impactAssessment($function['name'], $function['visibility'], $function['body'], $dependencies, $pages);
        $controllerFunctionLink = codeLink($root, $controller['path'], $function['line'], $short . '::' . $function['name'] . '()');
        $quickPath = quickFixPath($root, $pages, $methodRoutes, $controllerFunctionLink, $dependencies, $tests);
        if ($callerLinks) $impactText .= '<br>Được gọi tại: ' . implode(', ', $callerLinks) . '.';
        $functionAnalysis[$controller['class'] . '::' . $function['name']] = [
            'routes' => $methodRoutes,
            'pages' => $pages,
            'tests' => $tests,
            'dependencies' => $dependencies,
            'impact' => $impactText,
            'assessment' => $assessment,
            'quick_path' => $quickPath,
        ];
        $out[] = '| `' . $function['visibility'] . '` ' . codeLink($root, $controller['path'], $function['line'], '`' . $function['name'] . '()`')
            . ' | ' . describe($function['name'], $function['visibility'], $subject, $function['body'], $controller['class'])
            . ' | ' . debugDetails($function['body'])
            . ' | ' . ($dependencies ? implode('<br>', $dependencies) : 'Không phát hiện lời gọi Service/model trực tiếp.')
            . ' | ' . ($routeCells ? implode('<br>', $routeCells) : '—')
            . ' | ' . referenceLinks($root, $pages)
            . ' | ' . $impactText
            . ' | ' . referenceLinks($root, $tests) . ' |';
    }
}

$out[] = '';
$out[] = '## Giới hạn của chỉ mục tự động';
$out[] = '';
$out[] = '- Lời gọi endpoint được ghép theo chuỗi tĩnh; URL tạo hoàn toàn động có thể không được nhận diện.';
$out[] = '- Ảnh hưởng qua sự kiện, hàng đợi, bộ quan sát, service hoặc mô hình cần đọc thêm phần triển khai của hàm.';
$out[] = '- Một tuyến API dùng chung tiền tố có thể làm nhiều hàm cùng trỏ tới một trang; hãy mở đúng dòng và kiểm tra phương thức HTTP.';
$out[] = '- Hàm không có kiểm thử được tìm thấy không đồng nghĩa chắc chắn chưa được kiểm thử gián tiếp.';
$out[] = '';

file_put_contents($output, implode(PHP_EOL, $out));

$modules = [
    'Nền tảng và quản trị' => [
        'purpose' => 'Quản lý công ty, hồ sơ, nhân sự, phòng ban, chức vụ, vai trò và quyền.',
        'controllers' => ['API\\UserController', 'WEB\\UserController', 'CompanyController', 'WEB\\CompaniesController', 'DepartmentController', 'WEB\\DepartmentsController', 'PositionController', 'WEB\\PositionUserController', 'EmployeeController', 'RoleController', 'PermissionController', 'ProfileController'],
        'pages' => ['Manage', 'Company', 'Profile'],
        'services' => ['NotificationService', 'ActivityLogService'],
        'models' => ['User', 'Company', 'Department', 'Position', 'Role', 'Permission'],
        'tests' => ['Department', 'Position', 'User', 'Role', 'Permission', 'Company'],
    ],
    'Mua hàng' => [
        'purpose' => 'Quản lý nhà cung cấp và vòng đời đơn mua từ tạo, duyệt đến chuyển kho nhập.',
        'controllers' => ['PurchaseOrderController', 'SupplierController', 'ProductController', 'CategoryController', 'UnitController'],
        'pages' => ['Purchase'],
        'services' => ['PurchaseOrderService', 'SupplierDebtService', 'OrderQuantityValidationService', 'CompanyCurrencyService', 'CodeGeneratorService', 'NotificationService'],
        'models' => ['PurchaseOrder', 'PurchaseOrderItem', 'Supplier'],
        'tests' => ['Purchase', 'Supplier'],
    ],
    'Bán hàng' => [
        'purpose' => 'Quản lý khách hàng, đơn bán, POS và mã giảm giá từ tạo đơn đến duyệt/hủy.',
        'controllers' => ['SalesOrderController', 'CustomerController', 'CouponController', 'PosController'],
        'pages' => ['Sale'],
        'services' => ['CustomerDebtService', 'CouponService', 'OrderQuantityValidationService', 'CompanyCurrencyService', 'CodeGeneratorService', 'InventoryMovementService', 'NotificationService'],
        'models' => ['SalesOrder', 'SalesOrderItem', 'Customer', 'PosCoupon', 'PosCouponUsage'],
        'tests' => ['Sale', 'Customer', 'Coupon', 'Pos'],
    ],
    'Cửa hàng trực tuyến' => [
        'purpose' => 'Cửa hàng trực tuyến, thanh toán, tài khoản khách hàng, địa chỉ, đơn và thông báo cho khách mua hàng.',
        'controllers' => ['StorefrontController', 'StorefrontAccountController'],
        'pages' => ['Storefront'],
        'services' => ['CouponService', 'CodeGeneratorService', 'NotificationService'],
        'models' => ['CustomerAccount', 'CustomerAddress', 'SalesOrder', 'SalesOrderItem'],
        'tests' => ['Storefront'],
    ],
    'Kho' => [
        'purpose' => 'Quản lý kho, sản phẩm, phiếu nhập/xuất, chuyển kho và biến động tồn.',
        'controllers' => ['WarehouseController', 'WarehouseSlipController', 'WarehouseTransferController', 'InventoryMovementController', 'ProductController', 'CategoryController', 'UnitController'],
        'pages' => ['Warehouse'],
        'services' => ['InventoryMovementService', 'StockService', 'OrderQuantityValidationService', 'CodeGeneratorService', 'NotificationService'],
        'models' => ['Warehouse', 'WarehouseStock', 'WarehouseSlip', 'WarehouseSlipItem', 'WarehouseTransfer', 'InventoryMovement', 'Product', 'Category', 'Unit'],
        'tests' => ['Warehouse', 'Inventory', 'Stock'],
    ],
    'Kế toán và công nợ' => [
        'purpose' => 'Tài khoản, ngân hàng, tiền tệ, giao dịch, công nợ, đối soát COD và báo cáo lãi lỗ.',
        'controllers' => ['AccountController', 'Accountant\\AccountLedgerController', 'Accountant\\ProfitLossReportController', 'BankController', 'CurrencyController', 'TransactionController', 'TransactionCategoryController', 'CodReconciliationController'],
        'pages' => ['Accountant'],
        'services' => ['AccountBalanceService', 'LedgerService', 'TransactionService', 'TransactionCategoryService', 'CurrencyService', 'CompanyCurrencyService', 'CustomerDebtService', 'SupplierDebtService', 'CodReconciliationService'],
        'models' => ['Account', 'Bank', 'Currency', 'CurrencyRate', 'Transaction', 'TransactionCategory', 'CustomerDebt', 'SupplierDebt', 'CodReconciliation'],
        'tests' => ['Transaction', 'Debt', 'Account', 'Cod', 'Currency', 'Payment'],
    ],
    'Bảng điều khiển, nhật ký và thông báo' => [
        'purpose' => 'Tổng hợp bảng điều khiển, nhật ký hoạt động, thông báo và phân quyền kênh thời gian thực.',
        'controllers' => ['DashboardController', 'AuditLogController', 'NotificationController', 'BroadcastController'],
        'pages' => ['AuditLog'],
        'page_files' => ['resources/js/Pages/DashBoard.vue', 'resources/js/Pages/Home.vue'],
        'services' => ['DashboardService', 'ActivityLogService', 'NotificationService'],
        'models' => ['ActivityLog', 'Notification'],
        'tests' => ['Dashboard', 'Audit', 'Notification', 'Broadcast'],
    ],
    'Xác thực và API dùng chung' => [
        'purpose' => 'Đăng nhập/đăng ký, mật khẩu, xác minh thư điện tử, tỉnh phường và các hàm hỗ trợ dùng chung.',
        'controllers' => ['Auth\\AuthenticatedSessionController', 'Auth\\ConfirmablePasswordController', 'Auth\\EmailVerificationNotificationController', 'Auth\\EmailVerificationPromptController', 'Auth\\GoogleController', 'Auth\\NewPasswordController', 'Auth\\PasswordController', 'Auth\\PasswordResetLinkController', 'Auth\\RegisteredUserController', 'Auth\\UpdatePhoneController', 'Auth\\VerifyEmailController', 'AddressController', 'ProvinceController', 'Controller'],
        'pages' => [],
        'services' => [],
        'models' => ['User', 'Province', 'Ward'],
        'tests' => ['Auth', 'Login', 'Registration', 'Password'],
    ],
    'Hướng dẫn và tài liệu' => [
        'purpose' => 'Trang hướng dẫn sử dụng và các trang tài liệu/nội dung dùng chung của hệ thống.',
        'controllers' => [],
        'pages' => ['Guide'],
        'page_files' => ['resources/js/Pages/Document.vue', 'resources/js/Pages/Page.vue'],
        'services' => [],
        'models' => [],
        'tests' => ['Guide', 'Document'],
    ],
];

$servicePurposes = [
    'AccountBalanceService' => 'Tính, cập nhật và xây dựng lại số dư tài khoản kế toán.',
    'ActivityLogService' => 'Ghi nhật ký hoạt động cho các thao tác nghiệp vụ.',
    'CodeGeneratorService' => 'Sinh mã chứng từ/mã đối tượng theo công ty.',
    'CodReconciliationService' => 'Tạo và xử lý đối soát tiền thu hộ COD.',
    'CompanyCurrencyService' => 'Lấy tiền tệ công ty và tính tỷ giá quy đổi.',
    'CouponService' => 'Kiểm tra, áp dụng, ghi nhận sử dụng và hoàn tác mã giảm giá theo vòng đời đơn.',
    'CurrencyService' => 'Quản lý logic tiền tệ và lịch sử tỷ giá.',
    'CustomerDebtService' => 'Ghi nhận và tính công nợ phải thu khách hàng.',
    'DashboardService' => 'Tổng hợp chỉ số hiệu suất và số liệu bảng điều khiển theo module/khoảng ngày.',
    'InventoryMovementService' => 'Ghi biến động tăng/giảm/chuyển tồn kho và liên kết chứng từ nguồn.',
    'LedgerService' => 'Truy vấn và trình bày sổ cái/bút toán tài khoản.',
    'NotificationService' => 'Tạo, phân phối và quản lý thông báo nội bộ.',
    'OrderQuantityValidationService' => 'Kiểm tra số lượng lẻ theo cấu hình `allow_decimal` của đơn vị tính; Service này không kiểm tra tồn kho.',
    'PurchaseOrderService' => 'Đóng gói logic nghiệp vụ dùng cho đơn mua.',
    'StockService' => 'Truy vấn và cập nhật tồn kho.',
    'SupplierDebtService' => 'Ghi nhận và tính công nợ phải trả nhà cung cấp.',
    'TransactionCategoryService' => 'Quản lý quy tắc nghiệp vụ của loại giao dịch.',
    'TransactionService' => 'Tạo, duyệt, từ chối giao dịch và ghi nhận số dư/công nợ liên quan.',
];

$controllerByShort = [];
foreach ($controllers as $controller) {
    $controllerByShort[str_replace('App\\Http\\Controllers\\', '', $controller['class'])] = $controller;
}

$moduleDoc = ['# Chỉ mục theo phân hệ, nghiệp vụ và trang', '', '> Sinh tự động ngày **' . $generatedAt . '** bằng `php docs/generate_project_function_index.php`. Trang này là mục lục tra cứu nhanh; chi tiết được tách sang [Function](PROJECT_FUNCTION_INDEX.md), [tìm lỗi](PROJECT_DEBUGGING_INDEX.md), [database](PROJECT_DATABASE_INDEX.md) và [luồng nghiệp vụ](../resources/docs/BUSINESS_FLOWS.md).', '', '## Mục lục theo chức năng/nghiệp vụ', '', '> Dùng mục này khi chỉ nhớ việc cần làm, ví dụ “Tạo giao dịch”, “Duyệt đơn bán” hoặc “Tạo phiếu xuất”, nhưng không nhớ tên trang hay file.', ''];
$diagnosticItems = [];
$moduleIndexDetailBlocks = [];
foreach ($modules as $moduleName => $module) {
    $businessItems = [];
    foreach ($module['controllers'] as $shortController) {
        $controller = $controllerByShort[$shortController] ?? null;
        if (!$controller) continue;
        $subject = controllerSubject($controller['class']);
        foreach ($controller['functions'] as $function) {
            if ($function['visibility'] !== 'public' || $function['name'] === '__construct') continue;
            $methodRoutes = $routeMap[$controller['class']][$function['name']] ?? [];
            foreach ($methodRoutes as $route) {
                $label = businessActionLabel($function['name'], $subject);
                $method = str_replace('|', '/', $route['method']);
                $pages = functionPageReferences($root, $frontendFiles, [$route], $function, 20);
                // Một trang có thể gọi cùng API ở nhiều dòng. Mục lục nghiệp vụ chỉ
                // cần trỏ tới trang một lần; giữ vị trí gọi đầu tiên để tránh lặp tên.
                $pageLinksByPath = [];
                foreach ($pages as $page) {
                    $normalizedPagePath = strtolower(str_replace('\\', '/', $page['path']));
                    if (!isset($pageLinksByPath[$normalizedPagePath])) {
                        $pageLinksByPath[$normalizedPagePath] = codeLink(
                            $root,
                            $page['path'],
                            $page['line'],
                            businessPageName($root, $page['path'])
                        );
                    }
                }
                $pageLinks = array_values($pageLinksByPath);
                // Bản tra cứu hằng ngày chỉ giữ API đã ánh xạ được tới caller
                // Vue/JS. Route đầy đủ vẫn có trong PROJECT_FUNCTION_INDEX.md.
                if (str_starts_with($route['uri'], 'api/') && !$pageLinks) continue;
                $key = $label . '|' . $route['method'] . '|' . $route['uri'] . '|' . $controller['class'] . '|' . $function['name'];
                $businessItems[$key] = '- **' . $label . '** → `' . $method . ' /' . $route['uri'] . '` → '
                    . codeLink($root, $controller['path'], $function['line'], $shortController . '::' . $function['name'] . '()')
                    . ($pageLinks ? ' → Trang/Component: ' . implode(', ', $pageLinks) : '');
                foreach (symptomCategories($function['name'], $function['body']) as $symptom) {
                    $diagnosticItems[$symptom][$key] = '- `' . $method . ' /' . $route['uri'] . '` → '
                        . codeLink($root, $controller['path'], $function['line'], $shortController . '::' . $function['name'] . '()')
                        . ($pageLinks ? ' → ' . implode(', ', $pageLinks) : ' → chưa phát hiện caller frontend');
                }
            }
        }
    }
    if (!$businessItems) continue;
    ksort($businessItems, SORT_NATURAL | SORT_FLAG_CASE);
    $moduleDoc[] = '';
    $moduleDoc[] = '### ' . $moduleName;
    $moduleDoc[] = '';
    foreach ($businessItems as $item) $moduleDoc[] = $item;
}
$moduleDoc[] = '';
$moduleDoc[] = '## Mục lục tìm lỗi theo triệu chứng';
$moduleDoc[] = '';
$moduleDoc[] = '> Knowledge Base theo nguyên nhân gốc. Chọn câu hỏi gần nhất với điều người dùng báo; chỉ mở chỉ mục endpoint đầy đủ nếu các bước này chưa khoanh vùng được lỗi.';
$moduleDoc[] = '';
$moduleDoc[] = '### Vì sao tạo/sửa đơn báo “số lượng không hợp lệ”?';
$moduleDoc[] = '';
$moduleDoc[] = '- **Nguyên nhân thường gặp:** sản phẩm dùng đơn vị `allow_decimal = false` nhưng payload gửi số lượng lẻ.';
$moduleDoc[] = '- **Cách check:** đọc field 422 → mở ' . codeLink($root, $root . '/app/Services/OrderQuantityValidationService.php', 10, 'OrderQuantityValidationService::validate()') . ' → kiểm tra `Product → Unit → allow_decimal` và `items.*.quantity`.';
$moduleDoc[] = '- **Lưu ý:** Service này không kiểm tra tồn kho. Nếu lỗi nói không đủ tồn hoặc không thể xuất, dùng câu hỏi kế tiếp.';
$moduleDoc[] = '';
$moduleDoc[] = '### Vì sao còn hàng nhưng không thể tạo phiếu xuất?';
$moduleDoc[] = '';
$moduleDoc[] = '- **Nguyên nhân thường gặp:** tồn khả dụng khác tồn thực tế vì phiếu xuất `pending` đang giữ chỗ, chọn sai kho hoặc đơn đã xuất một phần.';
$moduleDoc[] = '- **Cách check:** đối chiếu `WarehouseProductStock.quantity` với lượng giữ chỗ → mở ' . codeLink($root, $root . '/app/Http/Controllers/SalesOrderController.php', 245, 'availableForExport()') . ' và ' . codeLink($root, $root . '/app/Http/Controllers/SalesOrderController.php', 923, 'stockOutData()') . ' → kiểm tra warehouse/product/company và lượng đã xuất.';
$moduleDoc[] = '';
$moduleDoc[] = '### Vì sao PO/SO đã duyệt nhưng Kho không thấy?';
$moduleDoc[] = '';
$moduleDoc[] = '- **Nguyên nhân thường gặp:** trạng thái chưa đúng, đơn đã xử lý hết, loại đơn bị loại khỏi danh sách, permission kho hoặc scope công ty.';
$moduleDoc[] = '- **Cách check:** xác nhận trạng thái `approved/partial` → tính lượng còn nhập/xuất sau các phiếu hiện có → kiểm tra endpoint danh sách chờ kho bằng tài khoản role Kho.';
$moduleDoc[] = '';
$moduleDoc[] = '### Vì sao phiếu đã duyệt nhưng tồn hoặc công nợ chưa đổi?';
$moduleDoc[] = '';
$moduleDoc[] = '- **Nguyên nhân thường gặp:** mới hoàn thành bước kho xác nhận, chưa qua kế toán duyệt; hoặc transaction duyệt kế toán đã rollback.';
$moduleDoc[] = '- **Cách check:** phân biệt ' . codeLink($root, $root . '/app/Http/Controllers/WarehouseSlipController.php', 818, 'approve() của kho') . ' với ' . codeLink($root, $root . '/app/Http/Controllers/WarehouseSlipController.php', 862, 'accountantApprove()') . ' → tìm `InventoryMovement` và debt theo phiếu nguồn.';
$moduleDoc[] = '- **Ràng buộc:** ' . codeLink($root, $root . '/resources/docs/decisions/ADR-001-WAREHOUSE-ACCOUNTING-APPROVAL.md', 1, 'ADR-001') . ' quy định chỉ kế toán duyệt mới cập nhật tồn, movement và công nợ.';
$moduleDoc[] = '';
$moduleDoc[] = '### Vì sao duyệt giao dịch xong nhưng công nợ không giảm?';
$moduleDoc[] = '';
$moduleDoc[] = '- **Nguyên nhân thường gặp:** sai `type` (`receipt/payment`), sai category (`THU_KH/CHI_NCC`), thiếu customer/supplier hoặc không gắn đúng PO/SO.';
$moduleDoc[] = '- **Cách check:** mở ' . codeLink($root, $root . '/app/Services/TransactionService.php', 191, 'TransactionService::approve()') . ' và ' . codeLink($root, $root . '/app/Services/TransactionService.php', 850, 'syncDebt()') . ' → kiểm tra type/category/đối tượng/đơn → đối chiếu debt và `AccountLedger` của cùng transaction.';
$moduleDoc[] = '';
$moduleDoc[] = '### Vì sao giá vốn xuất/chuyển không giống giá bình quân?';
$moduleDoc[] = '';
$moduleDoc[] = '- Đây có thể không phải bug. ' . codeLink($root, $root . '/resources/docs/decisions/ADR-002-INVENTORY-COST.md', 1, 'ADR-002') . ' quy định dùng giá nhập gần nhất; giá trị nhập gồm VAT, không dùng bình quân gia quyền.';
$moduleDoc[] = '- **Cách check:** lần từ sản phẩm đến đơn mua/phiếu nhập gần nhất và đối chiếu `cost_price`, `cost_amount` của phiếu/movement.';
$moduleDoc[] = '';
$moduleDoc[] = '### Vì sao thay tỷ giá làm số liệu mới khác chứng từ cũ?';
$moduleDoc[] = '';
$moduleDoc[] = '- ' . codeLink($root, $root . '/resources/docs/decisions/ADR-003-CURRENCY-AND-ROLES.md', 1, 'ADR-003') . ' yêu cầu VND luôn bằng 1; ngoại tệ có lịch sử và không sửa hồi tố chứng từ.';
$moduleDoc[] = '- **Cách check:** phân biệt tỷ giá hiện hành trong `CompanyCurrencyRate` với `exchange_rate`/giá trị base snapshot trên chứng từ.';
$moduleDoc[] = '';
$moduleDoc[] = '### Vì sao POS hoặc Storefront tạo trùng đơn?';
$moduleDoc[] = '';
$moduleDoc[] = '- **Nguyên nhân thường gặp:** double-click/retry tạo hai request, draft POS được checkout lại hoặc transaction checkout không bao phủ toàn bộ thao tác ghi.';
$moduleDoc[] = '- **Cách check:** tìm đơn theo customer/session, thời điểm và tổng tiền → kiểm tra `PosController::store()` hoặc `StorefrontController::checkout()` → kiểm tra transaction, code generation và retry frontend.';
$moduleDoc[] = '';
$moduleDoc[] = '### Vì sao coupon hợp lệ nhưng không áp dụng hoặc không được hoàn lại?';
$moduleDoc[] = '';
$moduleDoc[] = '- **Nguyên nhân thường gặp:** sai thời gian, channel, customer assignment, giới hạn sử dụng; hoặc nhánh hủy không hoàn tác `CouponUsage`.';
$moduleDoc[] = '- **Cách check:** mở ' . codeLink($root, $root . '/app/Services/CouponService.php', 1, 'CouponService') . ' → đối chiếu điều kiện áp dụng, usage theo đơn và nguồn Sale/POS/Storefront.';
$moduleDoc[] = '';
$moduleDoc[] = '### Vì sao có thông báo nhưng màn hình không tự cập nhật?';
$moduleDoc[] = '';
$moduleDoc[] = '- **Nguyên nhân thường gặp:** queue/Reverb chưa chạy, private channel từ chối, sai company channel hoặc listener frontend không đăng ký.';
$moduleDoc[] = '- **Cách check:** xác nhận notification đã commit → queue/Reverb → `routes/channels.php` → `companyData.js`/`useRealtimeRefresh.js`; phân biệt lỗi lưu, broadcast và render.';
$moduleDoc[] = '';
$moduleDoc[] = '### Vì sao Dashboard lệch số liệu chi tiết?';
$moduleDoc[] = '';
$moduleDoc[] = '- **Nguyên nhân thường gặp:** khác khoảng ngày/timezone, khác trạng thái được tính hoặc repository dùng điều kiện khác màn hình chi tiết.';
$moduleDoc[] = '- **Cách check:** mở ' . codeLink($root, $root . '/app/Services/DashboardService.php', 14, 'DashboardService::getOverview()') . ' và ' . codeLink($root, $root . '/app/Repositories/DashboardRepository.php', 1, 'DashboardRepository') . ' → cố định `date_from/date_to`, company và trạng thái rồi đối chiếu.';
$moduleDoc[] = '';
$moduleDoc[] = '### Vì sao dữ liệu công ty khác xuất hiện trên màn hình?';
$moduleDoc[] = '';
$moduleDoc[] = '- **Nguyên nhân thường gặp:** query thiếu `company_id`, relation/eager-load không có scope hoặc ID từ request chưa được xác minh thuộc công ty hiện tại.';
$moduleDoc[] = '- **Cách check:** lần từ controller xuống query → kiểm tra `BelongsToCompany`, điều kiện company trên relation và test cô lập công ty. Đây là lỗi bảo mật, không chỉ lỗi hiển thị.';

$moduleDoc[] = '';
$moduleDoc[] = '## Luồng trạng thái và điểm dễ phát sinh lỗi';
$moduleDoc[] = '';
$moduleDoc[] = '> Các luồng dưới đây được đối chiếu với trạng thái đang ghi trong Controller/Service. Xem thêm ' . codeLink($root, $root . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'docs' . DIRECTORY_SEPARATOR . 'BUSINESS_FLOWS.md', 1, 'Luồng nghiệp vụ hiện hành') . '.';
$moduleDoc[] = '';
$moduleDoc[] = '- **Đơn bán:** `draft → pending → approved → partial → completed`; có thể sang `cancelled` khi còn `draft/pending`. Điểm kiểm tra: ' . codeLink($root, $root . DIRECTORY_SEPARATOR . 'app/Http/Controllers/SalesOrderController.php', 729, 'gửi duyệt') . ', ' . codeLink($root, $root . DIRECTORY_SEPARATOR . 'app/Http/Controllers/SalesOrderController.php', 776, 'duyệt') . ', ' . codeLink($root, $root . DIRECTORY_SEPARATOR . 'app/Http/Controllers/SalesOrderController.php', 832, 'hủy') . '.';
$moduleDoc[] = '- **Đơn mua:** `pending → approved → partial → completed`; có thể sang `cancelled` khi còn `pending`. Điểm kiểm tra: ' . codeLink($root, $root . DIRECTORY_SEPARATOR . 'app/Http/Controllers/PurchaseOrderController.php', 526, 'duyệt') . ', ' . codeLink($root, $root . DIRECTORY_SEPARATOR . 'app/Http/Controllers/PurchaseOrderController.php', 578, 'hủy') . '.';
$moduleDoc[] = '- **Phiếu kho:** bắt đầu `pending`; kho xác nhận rồi kế toán duyệt. Khi kế toán duyệt, đơn liên quan được tính lại thành `approved/partial/completed`. Điểm kiểm tra: ' . codeLink($root, $root . DIRECTORY_SEPARATOR . 'app/Http/Controllers/WarehouseSlipController.php', 818, 'kho duyệt') . ', ' . codeLink($root, $root . DIRECTORY_SEPARATOR . 'app/Http/Controllers/WarehouseSlipController.php', 862, 'kế toán duyệt') . '.';
$moduleDoc[] = '- **Giao dịch:** `pending → approved` hoặc `pending → rejected`. Chỉ bước duyệt mới ghi nhận số dư/công nợ liên quan. Điểm kiểm tra: ' . codeLink($root, $root . DIRECTORY_SEPARATOR . 'app/Services/TransactionService.php', 195, 'duyệt giao dịch') . ', ' . codeLink($root, $root . DIRECTORY_SEPARATOR . 'app/Services/TransactionService.php', 267, 'từ chối giao dịch') . '.';
$moduleDoc[] = '';
$moduleDoc[] = '### Thứ tự debug một chức năng';
$moduleDoc[] = '';
$moduleDoc[] = '1. Mở dòng gọi trên **Trang/Component** và kiểm tra payload, HTTP method, URL.';
$moduleDoc[] = '2. Mở **Controller/Function**, kiểm tra quyền, validation và trạng thái đầu vào.';
$moduleDoc[] = '3. Mở **Service/model được gọi**, kiểm tra transaction, thay đổi dữ liệu và quan hệ.';
$moduleDoc[] = '4. Đối chiếu trạng thái trước/sau với luồng ở trên và kiểm tra trang bị ảnh hưởng trực tiếp/gián tiếp.';
$moduleDoc[] = '5. Chạy **kiểm thử liên quan**; nếu tài liệu báo chưa phát hiện test thì cần bổ sung test tái hiện lỗi trước khi sửa.';
$moduleDoc[] = '';
$combinedLookupSections = [
    ['heading' => '## Mục lục theo chức năng/nghiệp vụ', 'anchor' => 'tra-cuu-theo-chuc-nang-nghiep-vu', 'summary' => 'Tra cứu theo chức năng/nghiệp vụ — khi quên tên Function'],
    ['heading' => '## Mục lục tìm lỗi theo triệu chứng', 'anchor' => 'tra-cuu-tim-loi-theo-trieu-chung', 'summary' => 'Knowledge Base — khoanh vùng theo nguyên nhân gốc'],
    ['heading' => '## Luồng trạng thái và điểm dễ phát sinh lỗi', 'anchor' => 'tra-cuu-luong-trang-thai', 'summary' => 'Luồng trạng thái và thứ tự debug'],
];
$moduleIndexLookupLines = [
    '## Cẩm nang chẩn đoán nhanh',
    '',
    '> Mở đúng khối theo nhu cầu. Tra cứu nghiệp vụ chỉ giữ endpoint đã ánh xạ được tới Vue/JS; danh sách Function đầy đủ nằm tại [`docs/PROJECT_FUNCTION_INDEX.md`](docs/PROJECT_FUNCTION_INDEX.md).',
    '',
];
foreach ($combinedLookupSections as $sectionIndex => $section) {
    $start = array_search($section['heading'], $moduleDoc, true);
    if ($start === false) continue;
    $nextHeading = $combinedLookupSections[$sectionIndex + 1]['heading'] ?? null;
    $end = $nextHeading ? array_search($nextHeading, $moduleDoc, true) : count($moduleDoc);
    if ($end === false) $end = count($moduleDoc);
    $content = array_slice($moduleDoc, $start + 1, $end - $start - 1);
    $moduleIndexLookupLines[] = '<a id="' . $section['anchor'] . '"></a>';
    $moduleIndexLookupLines[] = '';
    $moduleIndexLookupLines[] = '<details>';
    $moduleIndexLookupLines[] = '<summary><strong>' . $section['summary'] . '</strong></summary>';
    $moduleIndexLookupLines[] = '';
    $moduleIndexLookupLines = array_merge($moduleIndexLookupLines, $content);
    $moduleIndexLookupLines[] = '';
    $moduleIndexLookupLines[] = '</details>';
    $moduleIndexLookupLines[] = '';
}
$moduleDoc[] = '## Mục lục tra cứu như sách';
$moduleDoc[] = '';
$moduleDoc[] = '> Chọn phân hệ, sau đó chọn đúng trang cần sửa hoặc đang có lỗi. Mỗi trang có mục riêng với API, controller/function và phạm vi ảnh hưởng.';
$moduleDoc[] = '';
foreach (array_keys($modules) as $moduleName) {
    $anchor = anchorId($moduleName);
    $moduleDoc[] = '- [' . $moduleName . '](#' . $anchor . ')';
    $module = $modules[$moduleName];
    $tocPages = [];
    foreach ($module['pages'] as $pageDir) $tocPages = array_merge($tocPages, files($root, 'resources/js/Pages/' . $pageDir, ['vue']));
    foreach ($module['page_files'] ?? [] as $relativePage) {
        $pagePath = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePage);
        if (is_file($pagePath)) $tocPages[] = $pagePath;
    }
    foreach ($tocPages as $pagePath) {
        $relative = relativePath($root, $pagePath);
        $pageAnchor = anchorId($moduleName . '-' . $relative);
        $moduleDoc[] = '  - [' . pageName($root, $pagePath) . ' — `' . str_replace('resources/js/Pages/', '', $relative) . '`](#' . $pageAnchor . ')';
    }
    $moduleDoc[] = '  - [Controller, function và kiểm tra dữ liệu](#' . anchorId($moduleName . '-controller-validation') . ')';
    $moduleDoc[] = '  - [Service](#' . anchorId($moduleName . '-service') . ')';
    $moduleDoc[] = '  - [Mô hình và kiểm thử](#' . anchorId($moduleName . '-model-test') . ')';
}

$modulePageGroups = [];
foreach ($modules as $moduleName => $module) {
    $anchor = anchorId($moduleName);
    $moduleDoc[] = '';
    $moduleDoc[] = '<a id="' . $anchor . '"></a>';
    $moduleDoc[] = '';
    $moduleDoc[] = '## ' . $moduleName;
    $moduleDoc[] = '';
    $moduleDoc[] = $module['purpose'];
    $moduleDoc[] = '';
    $moduleDoc[] = '### Các trang trong phân hệ';
    $moduleDoc[] = '';
    $modulePages = [];
    foreach ($module['pages'] as $pageDir) $modulePages = array_merge($modulePages, files($root, 'resources/js/Pages/' . $pageDir, ['vue', 'js', 'ts']));
    foreach ($module['page_files'] ?? [] as $relativePage) {
        $pagePath = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePage);
        if (is_file($pagePath)) $modulePages[] = $pagePath;
    }
    $pageGroups = [];
    foreach ($modulePages as $pagePath) {
        if (strtolower(pathinfo($pagePath, PATHINFO_EXTENSION)) !== 'vue') continue;
        $pageKey = str_replace('\\', '/', $pagePath);
        $pageGroups[$pageKey] = ['path' => $pagePath, 'line' => 1, 'calls' => []];
    }
    foreach ($module['controllers'] as $shortController) {
        $controller = $controllerByShort[$shortController] ?? null;
        if (!$controller) continue;
        foreach ($controller['functions'] as $function) {
            $methodRoutes = $routeMap[$controller['class']][$function['name']] ?? [];
            foreach (functionPageReferences($root, $modulePages, $methodRoutes, $function, 20) as $page) {
                foreach ($methodRoutes as $route) {
                    $uriPrefix = preg_replace('/\{.*$/', '', '/' . $route['uri']);
                    $lineText = file($page['path'], FILE_IGNORE_NEW_LINES)[$page['line'] - 1] ?? '';
                    if (($page['source'] ?? 'api') !== 'render' && !str_contains($lineText, $uriPrefix)) continue;
                    $method = str_replace('|', '\\|', $route['method']);
                    $pageKey = str_replace('\\', '/', $page['path']);
                    if (!isset($pageGroups[$pageKey])) {
                        $pageGroups[$pageKey] = ['path' => $page['path'], 'line' => $page['line'], 'calls' => []];
                    }
                    $callKey = $route['method'] . '|' . $route['uri'] . '|' . $shortController . '|' . $function['name'];
                    $pageGroups[$pageKey]['calls'][$callKey] = [
                        'method' => $method,
                        'uri' => $route['uri'],
                        'controller' => $controller,
                        'shortController' => $shortController,
                        'function' => $function,
                        'subject' => controllerSubject($controller['class']),
                        'pagePath' => $page['path'],
                        'pageLine' => $page['line'],
                    ];
                    break;
                }
            }
        }
    }
    $modulePageGroups[$moduleName] = $pageGroups;
    $moduleBlock = [];
    $moduleBlock[] = '<details>';
    $moduleBlock[] = '<summary><strong>' . $moduleName . '</strong> — ' . count($pageGroups) . ' trang FE, ' . count($module['controllers']) . ' Controller</summary>';
    $moduleBlock[] = '';
    $moduleBlock[] = $module['purpose'];
    $moduleBlock[] = '';
    $moduleBlock[] = '### FE — từng trang và chức năng';
    $moduleBlock[] = '';
    if (!$pageGroups) $moduleBlock[] = '> Module không có trang Vue riêng được ánh xạ.';
    foreach ($pageGroups as $group) {
        $relative = relativePath($root, $group['path']);
        $moduleBlock[] = '<details>';
        $moduleBlock[] = '<summary><strong>' . pageName($root, $group['path']) . '</strong> — <code>' . str_replace('resources/js/Pages/', '', $relative) . '</code></summary>';
        $moduleBlock[] = '';
        $moduleBlock[] = '- **File FE:** ' . codeLink($root, $group['path'], 1, $relative) . '.';
        if (!$group['calls']) {
            $moduleBlock[] = '- **Chức năng:** dựng giao diện hoặc nhận dữ liệu qua Inertia/Component/composable; chưa phát hiện API trực tiếp trong file.';
        } else {
            foreach ($group['calls'] as $call) {
                $function = $call['function'];
                $moduleBlock[] = '- **' . businessActionLabel($function['name'], $call['subject']) . ':** `'
                    . str_replace('\\|', '/', $call['method']) . ' /' . $call['uri'] . '` → '
                    . codeLink($root, $call['controller']['path'], $function['line'], $call['shortController'] . '::' . $function['name'] . '()')
                    . ' — ' . describe($function['name'], $function['visibility'], $call['subject'], $function['body'], $call['controller']['class']);
            }
        }
        $moduleBlock[] = '';
        $moduleBlock[] = '</details>';
        $moduleBlock[] = '';
    }
    $moduleBlock[] = '### BE — Controller và từng Function';
    $moduleBlock[] = '';
    foreach ($module['controllers'] as $shortController) {
        $controller = $controllerByShort[$shortController] ?? null;
        if (!$controller) continue;
        $listedFunctions = array_values(array_filter($controller['functions'], fn ($function) => $function['name'] !== '__construct'));
        $moduleBlock[] = '<details>';
        $moduleBlock[] = '<summary><strong>' . $shortController . '</strong> — ' . count($listedFunctions) . ' Function</summary>';
        $moduleBlock[] = '';
        $moduleBlock[] = '- **File BE:** ' . codeLink($root, $controller['path'], 1, $controller['relative']) . '.';
        $subject = controllerSubject($controller['class']);
        foreach ($listedFunctions as $function) {
            $methodRoutes = $routeMap[$controller['class']][$function['name']] ?? [];
            $apis = [];
            foreach ($methodRoutes as $route) $apis[] = str_replace('|', '/', $route['method']) . ' /' . $route['uri'];
            $visibilityLabel = ['public' => 'public', 'private' => 'private', 'protected' => 'protected'][$function['visibility']] ?? $function['visibility'];
            $moduleBlock[] = '- `' . $visibilityLabel . '` '
                . codeLink($root, $controller['path'], $function['line'], $function['name'] . '()')
                . ($apis ? ' — API `' . implode('`, `', $apis) . '`' : ' — không có API trực tiếp')
                . ': ' . describe($function['name'], $function['visibility'], $subject, $function['body'], $controller['class']);
        }
        $moduleBlock[] = '';
        $moduleBlock[] = '</details>';
        $moduleBlock[] = '';
    }
    $moduleBlock[] = '- **Đọc sâu:** [Function, validation, Service/model và ảnh hưởng](docs/PROJECT_FUNCTION_INDEX.md) · [Tìm lỗi](docs/PROJECT_DEBUGGING_INDEX.md) · [Database](docs/PROJECT_DATABASE_INDEX.md).';
    $moduleBlock[] = '';
    $moduleBlock[] = '</details>';
    $moduleBlock[] = '';
    $moduleIndexDetailBlocks = array_merge($moduleIndexDetailBlocks, $moduleBlock);

    foreach ($pageGroups as $group) {
        $relative = relativePath($root, $group['path']);
        $pageAnchor = anchorId($moduleName . '-' . $relative);
        $moduleDoc[] = '';
        $moduleDoc[] = '<a id="' . $pageAnchor . '"></a>';
        $moduleDoc[] = '';
        $moduleDoc[] = '#### ' . pageName($root, $group['path']) . ' — `' . str_replace('resources/js/Pages/', '', $relative) . '`';
        $moduleDoc[] = '';
        $moduleDoc[] = 'File: ' . codeLink($root, $group['path'], 1, $relative);
        $moduleDoc[] = '';
        if (!$group['calls']) {
            $moduleDoc[] = '> Chưa tìm thấy lời gọi API trực tiếp trong tệp này. Trang có thể chỉ dựng giao diện, nhận thuộc tính từ tuyến web hoặc gọi dữ liệu qua thành phần/kho trạng thái/hàm tái sử dụng.';
            continue;
        }
        $moduleDoc[] = '| API gọi tại | Controller/function xử lý | Function làm gì? | Khi sửa sẽ ảnh hưởng gì trên trang? |';
        $moduleDoc[] = '| --- | --- | --- | --- |';
        foreach ($group['calls'] as $call) {
            $function = $call['function'];
            $controller = $call['controller'];
            $moduleDoc[] = '| ' . codeLink($root, $group['path'], $call['pageLine'], $call['method'] . ' /' . $call['uri'])
                . ' | ' . codeLink($root, $controller['path'], $function['line'], $call['shortController'] . '::' . $function['name'] . '()')
                . ' | ' . describe($function['name'], $function['visibility'], $call['subject'], $function['body'], $controller['class'])
                . ' | ' . pageEffect($function['name'], $call['subject'], $call['pagePath']) . '. |';
        }
    }
    if (!$pageGroups) $moduleDoc[] = '> Phân hệ này không có thư mục trang Vue riêng; xem tuyến web/bộ điều khiển để xác định giao diện do khung phần mềm cung cấp.';

    $moduleDoc[] = '';
    $moduleDoc[] = '<a id="' . anchorId($moduleName . '-controller-validation') . '"></a>';
    $moduleDoc[] = '';
    $moduleDoc[] = '### Controller, function và kiểm tra dữ liệu';
    $moduleDoc[] = '';
    $moduleDoc[] = '| Controller | Function | Viết gì? | Kiểm tra dữ liệu nằm ở đâu? | Dữ liệu và lỗi cần kiểm tra | Service/model được gọi | API | Trang/vị trí bị ảnh hưởng khi sửa |';
    $moduleDoc[] = '| --- | --- | --- | --- | --- | --- | --- | --- |';
    foreach ($module['controllers'] as $shortController) {
        $controller = $controllerByShort[$shortController] ?? null;
        if (!$controller) continue;
        $subject = controllerSubject($controller['class']);
        $controllerSource = file_get_contents($controller['path']);
        $sourceLines = file($controller['path'], FILE_IGNORE_NEW_LINES) ?: [];
        foreach ($controller['functions'] as $function) {
            if ($function['name'] === '__construct') continue;
            $methodRoutes = $routeMap[$controller['class']][$function['name']] ?? [];
            $validation = [];
            if (preg_match('/->validate\(|Validator::make\(|validated\(/', $function['body'])) $validation[] = 'trực tiếp trong hàm';
            $signature = implode(' ', array_slice($sourceLines, max(0, $function['line'] - 1), 6));
            if (preg_match_all('/([A-Z][A-Za-z0-9_]*Request)\s+\$/', $signature, $matches)) {
                foreach ($matches[1] as $requestClass) $validation[] = '`' . $requestClass . '` (lớp yêu cầu kiểm tra dữ liệu)';
            }
            if (preg_match('/ValidationService|->validateQuantities|quantityValidator/', $function['body'])) $validation[] = '`OrderQuantityValidationService`';
            $dependencies = functionDependencyLinks($root, $controllerSource, $function['body']);
            $api = [];
            foreach ($methodRoutes as $route) $api[] = '`' . str_replace('|', '\\|', $route['method']) . ' /' . $route['uri'] . '`';
            $affected = [];
            if ($function['visibility'] === 'public') {
                foreach (functionPageReferences($root, $frontendFiles, $methodRoutes, $function, 20) as $page) {
                    $key = str_replace('\\', '/', $page['path']) . ':' . $page['line'];
                    $affected[$key] = codeLink($root, $page['path'], $page['line'], pageName($root, $page['path'])) . ': ' . pageEffect($function['name'], $subject, $page['path']) . '.';
                }
                foreach (indirectPageImpacts($root, $controller['class'], $function['name']) as $indirect) {
                    $affected['indirect:' . md5($indirect)] = ltrim($indirect, '<br>• ');
                }
            } else {
                foreach ($function['callers'] as $callerLine) {
                    $callerFunction = null;
                    foreach ($controller['functions'] as $candidate) {
                        if ($candidate['line'] <= $callerLine && ($callerFunction === null || $candidate['line'] > $callerFunction['line'])) $callerFunction = $candidate;
                    }
                    if (!$callerFunction || $callerFunction['name'] === $function['name']) continue;
                    $callerLink = codeLink($root, $controller['path'], $callerLine, $callerFunction['name'] . '() gọi tại dòng ' . $callerLine);
                    $affected['caller:' . $callerLine] = $callerLink . ': thay đổi hàm hỗ trợ sẽ làm thay đổi logic của hàm này.';
                    $callerRoutes = $routeMap[$controller['class']][$callerFunction['name']] ?? [];
                    foreach (functionPageReferences($root, $frontendFiles, $callerRoutes, $callerFunction, 20) as $page) {
                        $key = 'caller-page:' . $callerFunction['name'] . ':' . str_replace('\\', '/', $page['path']) . ':' . $page['line'];
                        $affected[$key] = codeLink($root, $page['path'], $page['line'], pageName($root, $page['path'])) . ': bị ảnh hưởng gián tiếp qua `' . $callerFunction['name'] . '()`; ' . pageEffect($callerFunction['name'], $subject, $page['path']) . '.';
                    }
                }
            }
            if (!$affected) {
                $affected[] = $function['visibility'] === 'public'
                    ? 'Chưa phát hiện Vue/JS gọi trực tiếp; kiểm tra endpoint chưa dùng, tuyến web, Component, sự kiện, hàng đợi hoặc lời gọi qua Service/kho trạng thái.'
                    : 'Chưa tìm thấy nơi gọi bằng phân tích tĩnh; tìm tên hàm hỗ trợ trong bộ điều khiển trước khi sửa.';
            }
            $visibilityLabel = ['public' => 'công khai', 'private' => 'riêng tư', 'protected' => 'được bảo vệ'][$function['visibility']] ?? $function['visibility'];
            $moduleDoc[] = '| ' . codeLink($root, $controller['path'], $function['line'], $shortController) . ' | `' . $visibilityLabel . '` `' . $function['name'] . '()` | ' . describe($function['name'], $function['visibility'], $subject, $function['body'], $controller['class']) . ' | ' . ($validation ? implode('<br>', array_unique($validation)) : 'Không có validation trực tiếp trong Function này; xem Service/model nếu đây là thao tác ghi dữ liệu.') . ' | ' . debugDetails($function['body']) . ' | ' . ($dependencies ? implode('<br>', $dependencies) : 'Không phát hiện lời gọi Service/model trực tiếp.') . ' | ' . ($api ? implode('<br>', $api) : 'Không có tuyến API trực tiếp.') . ' | ' . implode('<br>', array_values($affected)) . ' |';
        }
    }

    $moduleDoc[] = '';
    $moduleDoc[] = '<a id="' . anchorId($moduleName . '-service') . '"></a>';
    $moduleDoc[] = '';
    $moduleDoc[] = '### Service';
    $moduleDoc[] = '';
    $moduleDoc[] = '| Service | Viết gì? | Hàm công khai |';
    $moduleDoc[] = '| --- | --- | --- |';
    if (!$module['services']) $moduleDoc[] = '| — | Phân hệ chủ yếu dùng controller hoặc service của khung phần mềm. | — |';
    foreach ($module['services'] as $serviceName) {
        $path = $root . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Services' . DIRECTORY_SEPARATOR . $serviceName . '.php';
        if (!is_file($path)) continue;
        $functions = array_values(array_filter(controllerFunctions($root, $path), fn ($function) => $function['visibility'] === 'public' && $function['name'] !== '__construct'));
        $functionLinks = array_map(fn ($function) => codeLink($root, $path, $function['line'], $function['name'] . '()'), $functions);
        $moduleDoc[] = '| ' . codeLink($root, $path, 1, $serviceName) . ' | ' . ($servicePurposes[$serviceName] ?? 'Đóng gói logic nghiệp vụ dùng lại trong phân hệ.') . ' | ' . ($functionLinks ? implode('<br>', $functionLinks) : 'Không có hàm công khai ngoài hàm khởi tạo.') . ' |';
    }

    $moduleDoc[] = '';
    $moduleDoc[] = '<a id="' . anchorId($moduleName . '-model-test') . '"></a>';
    $moduleDoc[] = '';
    $moduleDoc[] = '### Mô hình và kiểm thử';
    $moduleDoc[] = '';
    $modelLinks = [];
    $databaseLinks = [];
    foreach ($module['models'] as $model) {
        $path = $root . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Models' . DIRECTORY_SEPARATOR . $model . '.php';
        if (is_file($path)) {
            $modelLinks[] = codeLink($root, $path, 1, $model);
            $table = modelTableName($path, $model);
            $migrations = migrationLinksForTable($root, $table);
            $databaseLinks[] = '`' . $table . '` ← ' . codeLink($root, $path, 1, 'Model ' . $model)
                . ($migrations ? ' → ' . implode(', ', $migrations) : ' → chưa phát hiện migration tạo/sửa bảng');
        }
    }
    $matchingTests = [];
    foreach ($testFiles as $testPath) {
        foreach ($module['tests'] as $pattern) {
            if (stripos(basename($testPath), $pattern) !== false) {
                $matchingTests[] = codeLink($root, $testPath, 1, basename($testPath));
                break;
            }
        }
    }
    $moduleDoc[] = '- **Mô hình chính:** ' . ($modelLinks ? implode(', ', $modelLinks) : 'Chưa ánh xạ mô hình riêng.') . '.';
    $moduleDoc[] = '- **Bảng và migration cần kiểm tra:** ' . ($databaseLinks ? implode('<br>', $databaseLinks) : 'Phân hệ không có model/bảng riêng được ánh xạ') . '.';
    $moduleDoc[] = '- **Kiểm thử liên quan:** ' . ($matchingTests ? implode(', ', array_unique($matchingTests)) : 'Chưa tìm thấy kiểm thử theo tên phân hệ; kiểm tra chỉ mục hàm để tìm kiểm thử gián tiếp.') . '.';
}

$moduleDoc[] = '';
$moduleDoc[] = '## Lưu ý khi tra cứu';
$moduleDoc[] = '';
$moduleDoc[] = '- “Trực tiếp trong hàm” nghĩa là quy tắc kiểm tra đang nằm trong bộ điều khiển; sửa quy tắc sẽ ảnh hưởng trực tiếp biểu mẫu/API tương ứng.';
$moduleDoc[] = '- `FormRequest` là lớp yêu cầu kiểm tra dữ liệu tách riêng; cần mở lớp này trước khi sửa bộ điều khiển.';
$moduleDoc[] = '- Service kiểm tra dữ liệu thường chứa quy tắc nghiệp vụ chéo như tồn kho/số lượng, không thay thế việc kiểm tra định dạng đầu vào.';
$moduleDoc[] = '- Một service có thể xuất hiện ở nhiều phân hệ vì đó là thành phần phụ thuộc dùng chung.';
$moduleDoc[] = '';

$debugStart = array_search('## Mục lục tìm lỗi theo triệu chứng', $moduleDoc, true);
$bookStart = array_search('## Mục lục tra cứu như sách', $moduleDoc, true);
$debugDoc = [
    '# Chỉ mục tìm lỗi theo triệu chứng',
    '',
    '> Sinh tự động ngày **' . $generatedAt . '**. Quay lại [mục lục module hợp nhất](../MODULE_INDEX.md).',
    '',
];
if ($debugStart !== false && $bookStart !== false && $bookStart > $debugStart) {
    $debugDoc = array_merge($debugDoc, array_slice($moduleDoc, $debugStart, $bookStart - $debugStart));
    array_splice($moduleDoc, $debugStart, $bookStart - $debugStart, [
        '## Tìm lỗi và luồng trạng thái',
        '',
        '- [Tìm theo triệu chứng, input, mã lỗi và thứ tự debug](PROJECT_DEBUGGING_INDEX.md).',
        '- [Xem luồng nghiệp vụ hiện hành](../resources/docs/BUSINESS_FLOWS.md).',
        '- [Tra bảng dữ liệu và migration](PROJECT_DATABASE_INDEX.md).',
        '',
    ]);
}

$databaseDoc = [
    '# Chỉ mục Model, bảng dữ liệu và migration',
    '',
    '> Sinh tự động ngày **' . $generatedAt . '**. Quay lại [mục lục module hợp nhất](../MODULE_INDEX.md).',
    '',
    '## Mục lục',
    '',
];
foreach (array_keys($modules) as $moduleName) $databaseDoc[] = '- [' . $moduleName . '](#' . anchorId($moduleName) . ')';
foreach ($modules as $moduleName => $module) {
    $databaseDoc[] = '';
    $databaseDoc[] = '<a id="' . anchorId($moduleName) . '"></a>';
    $databaseDoc[] = '';
    $databaseDoc[] = '## ' . $moduleName;
    $databaseDoc[] = '';
    if (!$module['models']) {
        $databaseDoc[] = '> Phân hệ không có model/bảng riêng được ánh xạ.';
        continue;
    }
    $databaseDoc[] = '| Model | Bảng | Migration tạo/sửa bảng |';
    $databaseDoc[] = '| --- | --- | --- |';
    foreach ($module['models'] as $model) {
        $path = $root . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Models' . DIRECTORY_SEPARATOR . $model . '.php';
        if (!is_file($path)) continue;
        $table = modelTableName($path, $model);
        $migrations = migrationLinksForTable($root, $table);
        $databaseDoc[] = '| ' . codeLink($root, $path, 1, $model) . ' | `' . $table . '` | '
            . ($migrations ? implode('<br>', $migrations) : 'Chưa phát hiện migration tạo/sửa bảng.') . ' |';
    }
}

$compactModuleDoc = [];
$currentModule = null;
$skippingControllerDetails = false;
foreach ($moduleDoc as $line) {
    if (str_starts_with($line, '## ')) {
        $candidateModule = substr($line, 3);
        if (isset($modules[$candidateModule])) $currentModule = $candidateModule;
    }
    if (str_contains($line, '](#') && str_contains($line, '-controller-validation)')) continue;
    if (str_starts_with($line, '<a id="') && str_contains($line, '-controller-validation')) {
        $skippingControllerDetails = true;
        $compactModuleDoc[] = '';
        $compactModuleDoc[] = '### Controller và Function';
        $compactModuleDoc[] = '';
        $controllerLinks = [];
        foreach (($modules[$currentModule]['controllers'] ?? []) as $shortController) {
            $controller = $controllerByShort[$shortController] ?? null;
            if (!$controller) continue;
            $anchor = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $controller['class']), '-'));
            $controllerLinks[] = '[' . $shortController . '](PROJECT_FUNCTION_INDEX.md#' . $anchor . ')';
        }
        $compactModuleDoc[] = '- **Controller:** ' . ($controllerLinks ? implode(', ', $controllerLinks) : 'Không có Controller riêng.') . '';
        $compactModuleDoc[] = '- **Chi tiết Function, validation và ảnh hưởng:** [Mở chỉ mục Function toàn dự án](PROJECT_FUNCTION_INDEX.md).';
        $compactModuleDoc[] = '- **Database:** [Mở Model, bảng và migration của phân hệ](PROJECT_DATABASE_INDEX.md#' . anchorId((string) $currentModule) . ').';
        continue;
    }
    if ($skippingControllerDetails) {
        if (str_starts_with($line, '<a id="') && str_contains($line, '-service')) {
            $skippingControllerDetails = false;
            $compactModuleDoc[] = '';
            $compactModuleDoc[] = $line;
        }
        continue;
    }
    if (str_starts_with($line, '- **Bảng và migration cần kiểm tra:**')) continue;
    $compactModuleDoc[] = $line;
}
$moduleDoc = $compactModuleDoc;

$moduleIndexGroups = [
    ['title' => '1. Khung ứng dụng và Dashboard', 'module' => 'Bảng điều khiển, nhật ký và thông báo', 'subgroups' => [
        ['number' => '1.1', 'title' => 'Dashboard tổng', 'paths' => ['/Pages/DashBoard.vue', '/Pages/Home.vue'], 'controllers' => ['DashboardController'], 'services' => ['DashboardService']],
    ]],
    ['title' => '2. Công ty và hồ sơ cá nhân', 'module' => 'Nền tảng và quản trị', 'subgroups' => [
        ['number' => '2.1', 'title' => 'Công ty', 'paths' => ['/Pages/Company/'], 'controllers' => ['CompanyController', 'WEB\\CompaniesController'], 'services' => []],
        ['number' => '2.2', 'title' => 'Hồ sơ cá nhân', 'paths' => ['/Pages/Profile/'], 'controllers' => ['ProfileController'], 'services' => []],
    ]],
    ['title' => '3. Nhân sự và cơ cấu tổ chức', 'module' => 'Nền tảng và quản trị', 'subgroups' => [
        ['number' => '3.1', 'title' => 'Người dùng và nhân sự', 'paths' => ['/Pages/Manage/User'], 'controllers' => ['API\\UserController', 'WEB\\UserController', 'EmployeeController'], 'services' => ['NotificationService', 'ActivityLogService']],
        ['number' => '3.2', 'title' => 'Phòng ban', 'paths' => ['/Pages/Manage/Department/'], 'controllers' => ['DepartmentController', 'WEB\\DepartmentsController'], 'services' => ['NotificationService']],
        ['number' => '3.3', 'title' => 'Chức vụ', 'paths' => ['/Pages/Manage/Position/'], 'controllers' => ['PositionController', 'WEB\\PositionUserController'], 'services' => []],
    ]],
    ['title' => '4. Vai trò và phân quyền', 'module' => 'Nền tảng và quản trị', 'subgroups' => [
        ['number' => '4.1', 'title' => 'Vai trò', 'paths' => ['/Pages/Manage/Role'], 'controllers' => ['RoleController'], 'services' => []],
        ['number' => '4.2', 'title' => 'Permission', 'paths' => ['/Pages/Manage/Permission'], 'controllers' => ['PermissionController'], 'services' => []],
    ]],
    ['title' => '5. Mua hàng', 'module' => 'Mua hàng', 'subgroups' => [
        ['number' => '5.1', 'title' => 'Nhà cung cấp', 'paths' => ['/Pages/Purchase/Supplier/'], 'controllers' => ['SupplierController'], 'services' => ['SupplierDebtService', 'CompanyCurrencyService', 'CodeGeneratorService']],
        ['number' => '5.2', 'title' => 'Danh mục sản phẩm', 'paths' => ['/Pages/Purchase/Category/'], 'controllers' => ['CategoryController'], 'services' => []],
        ['number' => '5.3', 'title' => 'Đơn vị tính', 'paths' => ['/Pages/Purchase/Unit/'], 'controllers' => ['UnitController'], 'services' => []],
        ['number' => '5.4', 'title' => 'Sản phẩm', 'paths' => ['/Pages/Purchase/Product/'], 'controllers' => ['ProductController'], 'services' => ['CompanyCurrencyService']],
        ['number' => '5.5', 'title' => 'Đơn mua', 'paths' => ['/Pages/Purchase/Order/'], 'controllers' => ['PurchaseOrderController'], 'services' => ['PurchaseOrderService', 'OrderQuantityValidationService', 'CompanyCurrencyService', 'CodeGeneratorService', 'NotificationService']],
        ['number' => '5.6', 'title' => 'Dashboard mua hàng', 'paths' => ['/Pages/Purchase/Dashboard.vue'], 'controllers' => ['DashboardController'], 'services' => ['DashboardService']],
    ]],
    ['title' => '6. Bán hàng', 'module' => 'Bán hàng', 'subgroups' => [
        ['number' => '6.1', 'title' => 'Khách hàng', 'paths' => ['/Pages/Sale/Customer/'], 'controllers' => ['CustomerController'], 'services' => ['CustomerDebtService', 'CompanyCurrencyService', 'CodeGeneratorService', 'NotificationService']],
        ['number' => '6.2', 'title' => 'Đơn bán', 'paths' => ['/Pages/Sale/Order/'], 'controllers' => ['SalesOrderController'], 'services' => ['CustomerDebtService', 'StockService', 'CouponService', 'CompanyCurrencyService', 'CodeGeneratorService', 'NotificationService']],
        ['number' => '6.3', 'title' => 'Bán hàng POS', 'paths' => ['/Pages/Sale/Pos/'], 'controllers' => ['PosController'], 'services' => ['StockService', 'CouponService', 'CodeGeneratorService']],
        ['number' => '6.4', 'title' => 'Mã giảm giá', 'paths' => ['/Pages/Sale/Coupon/'], 'controllers' => ['CouponController'], 'services' => ['CouponService']],
        ['number' => '6.5', 'title' => 'Dashboard bán hàng', 'paths' => ['/Pages/Sale/Dashboard.vue'], 'controllers' => ['DashboardController'], 'services' => ['DashboardService']],
    ]],
    ['title' => '6A. Cửa hàng trực tuyến', 'module' => 'Cửa hàng trực tuyến', 'subgroups' => [
        ['number' => '6A.1', 'title' => 'Gian hàng, sản phẩm và checkout', 'paths' => ['/Pages/Storefront/Directory.vue', '/Pages/Storefront/Shop.vue', '/Pages/Storefront/Product.vue', '/Pages/Storefront/Cart.vue', '/Pages/Storefront/Checkout.vue', '/Pages/Storefront/Success.vue'], 'controllers' => ['StorefrontController'], 'services' => ['CouponService']],
        ['number' => '6A.2', 'title' => 'Tài khoản và đơn của khách', 'paths' => ['/Pages/Storefront/Account.vue', '/Pages/Storefront/OrderDetail.vue'], 'controllers' => ['StorefrontAccountController'], 'services' => ['NotificationService']],
        ['number' => '6A.3', 'title' => 'Thông báo khách hàng', 'paths' => ['/Pages/Storefront/Notifications.vue'], 'controllers' => ['StorefrontAccountController'], 'services' => ['NotificationService']],
    ]],
    ['title' => '7. Kho', 'module' => 'Kho', 'subgroups' => [
        ['number' => '7.1', 'title' => 'Kho hàng', 'paths' => ['/Pages/Warehouse/Index.vue', '/Pages/Warehouse/Warehouse'], 'controllers' => ['WarehouseController'], 'services' => ['StockService']],
        ['number' => '7.2', 'title' => 'Sản phẩm trong kho', 'paths' => ['/Pages/Warehouse/Product/'], 'controllers' => ['ProductController'], 'services' => ['StockService', 'CompanyCurrencyService']],
        ['number' => '7.3', 'title' => 'Danh mục và đơn vị tính', 'paths' => ['/Pages/Warehouse/Category/', '/Pages/Warehouse/Unit/'], 'controllers' => ['CategoryController', 'UnitController'], 'services' => []],
        ['number' => '7.4', 'title' => 'Đơn chờ kho', 'paths' => ['/Pages/Warehouse/Order/'], 'controllers' => ['PurchaseOrderController', 'SalesOrderController'], 'services' => ['OrderQuantityValidationService']],
        ['number' => '7.5', 'title' => 'Phiếu nhập/xuất kho', 'paths' => ['/Pages/Warehouse/Slip/'], 'controllers' => ['WarehouseSlipController'], 'services' => ['StockService', 'InventoryMovementService', 'OrderQuantityValidationService', 'NotificationService']],
        ['number' => '7.6', 'title' => 'Chuyển kho', 'paths' => ['/Pages/Warehouse/Transfer/'], 'controllers' => ['WarehouseTransferController'], 'services' => ['StockService', 'InventoryMovementService']],
        ['number' => '7.7', 'title' => 'Biến động tồn kho', 'paths' => ['/Pages/Warehouse/InventoryMovement/'], 'controllers' => ['InventoryMovementController'], 'services' => ['InventoryMovementService']],
        ['number' => '7.8', 'title' => 'Dashboard kho', 'paths' => ['/Pages/Warehouse/Dashboard.vue'], 'controllers' => ['DashboardController'], 'services' => ['DashboardService']],
    ]],
    ['title' => '8. Kế toán, giao dịch và công nợ', 'module' => 'Kế toán và công nợ', 'subgroups' => [
        ['number' => '8.1', 'title' => 'Tài khoản và sổ cái', 'paths' => ['/Pages/Accountant/Account/', '/Pages/Accountant/AccountLedger/'], 'controllers' => ['AccountController', 'Accountant\\AccountLedgerController'], 'services' => ['AccountBalanceService', 'LedgerService']],
        ['number' => '8.2', 'title' => 'Ngân hàng', 'paths' => ['/Pages/Accountant/Bank/'], 'controllers' => ['BankController'], 'services' => []],
        ['number' => '8.3', 'title' => 'Tiền tệ và tỷ giá', 'paths' => ['/Pages/Accountant/Currency/'], 'controllers' => ['CurrencyController'], 'services' => ['CurrencyService', 'CompanyCurrencyService']],
        ['number' => '8.4', 'title' => 'Loại giao dịch', 'paths' => ['/Pages/Accountant/TransactionCategory/'], 'controllers' => ['TransactionCategoryController'], 'services' => ['TransactionCategoryService']],
        ['number' => '8.5', 'title' => 'Giao dịch kế toán', 'paths' => ['/Pages/Accountant/Transaction/'], 'controllers' => ['TransactionController'], 'services' => ['TransactionService', 'AccountBalanceService', 'CustomerDebtService', 'SupplierDebtService', 'CompanyCurrencyService']],
        ['number' => '8.6', 'title' => 'Công nợ khách hàng', 'paths' => ['/Pages/Accountant/Customer/'], 'controllers' => ['CustomerController'], 'services' => ['CustomerDebtService']],
        ['number' => '8.7', 'title' => 'Công nợ nhà cung cấp', 'paths' => ['/Pages/Accountant/Supplier/'], 'controllers' => ['SupplierController'], 'services' => ['SupplierDebtService']],
        ['number' => '8.8', 'title' => 'Đối soát COD', 'paths' => ['/Pages/Accountant/CodReconciliation/'], 'controllers' => ['CodReconciliationController'], 'services' => ['CodReconciliationService']],
        ['number' => '8.9', 'title' => 'Báo cáo lãi lỗ', 'paths' => ['/Pages/Accountant/Report/'], 'controllers' => ['Accountant\\ProfitLossReportController'], 'services' => []],
        ['number' => '8.10', 'title' => 'Dashboard kế toán', 'paths' => ['/Pages/Accountant/Dashboard.vue'], 'controllers' => ['DashboardController'], 'services' => ['DashboardService']],
    ]],
    ['title' => '9. Nhật ký hoạt động', 'module' => 'Bảng điều khiển, nhật ký và thông báo', 'subgroups' => [
        ['number' => '9.1', 'title' => 'Nhật ký hoạt động', 'paths' => ['/Pages/AuditLog/'], 'controllers' => ['AuditLogController'], 'services' => ['ActivityLogService']],
    ]],
    ['title' => '10. Thông báo và realtime', 'module' => 'Bảng điều khiển, nhật ký và thông báo', 'subgroups' => [
        ['number' => '10.1', 'title' => 'Thông báo và realtime', 'paths' => [], 'controllers' => ['NotificationController', 'BroadcastController'], 'services' => ['NotificationService']],
    ]],
    ['title' => '11. Xác thực', 'module' => 'Xác thực và API dùng chung', 'subgroups' => [
        ['number' => '11.1', 'title' => 'Đăng nhập, đăng ký và mật khẩu', 'paths' => [], 'controllers' => ['Auth\\AuthenticatedSessionController', 'Auth\\ConfirmablePasswordController', 'Auth\\EmailVerificationNotificationController', 'Auth\\EmailVerificationPromptController', 'Auth\\GoogleController', 'Auth\\NewPasswordController', 'Auth\\PasswordController', 'Auth\\PasswordResetLinkController', 'Auth\\RegisteredUserController', 'Auth\\UpdatePhoneController', 'Auth\\VerifyEmailController'], 'services' => []],
        ['number' => '11.2', 'title' => 'Địa chỉ hành chính', 'paths' => [], 'controllers' => ['AddressController', 'ProvinceController'], 'services' => []],
    ]],
    ['title' => '12. Thành phần dùng chung và hạ tầng', 'module' => 'Hướng dẫn và tài liệu', 'subgroups' => [
        ['number' => '12.1', 'title' => 'Component, composable, Controller nền và layout dùng chung', 'paths' => [], 'controllers' => ['Controller'], 'services' => []],
        ['number' => '12.2', 'title' => 'Service nghiệp vụ dùng chung', 'paths' => [], 'controllers' => [], 'services' => ['CompanyCurrencyService', 'CodeGeneratorService', 'NotificationService', 'StockService'], 'show_all_services' => true],
    ]],
    ['title' => '13. Hướng dẫn sử dụng', 'module' => 'Hướng dẫn và tài liệu', 'subgroups' => [
        ['number' => '13.1', 'title' => 'Trang hướng dẫn và tài liệu', 'paths' => ['/Pages/Guide/', '/Pages/Document.vue', '/Pages/Page.vue'], 'controllers' => [], 'services' => []],
    ]],
];

$moduleIndexDetailBlocks = [];
$moduleIndexBlocksByTitle = [];
$emittedModuleControllers = [];
$emittedModuleServices = [];
foreach ($moduleIndexGroups as $groupDefinition) {
    $groupBlockStart = count($moduleIndexDetailBlocks);
    $sourcePageGroups = $modulePageGroups[$groupDefinition['module']] ?? [];
    foreach ($groupDefinition['subgroups'] as $subgroup) {
        $subPages = [];
        foreach ($sourcePageGroups as $pageKey => $pageGroup) {
            $normalized = str_replace('\\', '/', $pageGroup['path']);
            foreach ($subgroup['paths'] as $pathNeedle) {
                if (str_contains($normalized, $pathNeedle)) {
                    $subPages[$pageKey] = $pageGroup;
                    break;
                }
            }
        }
        $activeServices = [];
        foreach ($subgroup['services'] as $serviceName) {
            $calledMethods = serviceMethodsCalledByControllers($serviceName, $subgroup['controllers'], $controllerByShort);
            if ($calledMethods || !empty($subgroup['show_all_services'])) $activeServices[$serviceName] = $calledMethods;
        }
        $moduleIndexDetailBlocks[] = '<details>';
        $moduleIndexDetailBlocks[] = '<summary><strong>' . $subgroup['number'] . ' ' . $subgroup['title'] . '</strong> — ' . count($subPages) . ' trang, ' . count($subgroup['controllers']) . ' Controller, ' . count($activeServices) . ' Service trực tiếp</summary>';
        $moduleIndexDetailBlocks[] = '';
        $moduleIndexDetailBlocks[] = '### FE — từng trang Vue';
        $moduleIndexDetailBlocks[] = '';
        if (!$subPages) $moduleIndexDetailBlocks[] = '> Nhóm nghiệp vụ này không có trang Vue riêng hoặc giao diện nằm trong Component/Blade dùng chung.';
        foreach ($subPages as $pageGroup) {
            $relative = relativePath($root, $pageGroup['path']);
            $moduleIndexDetailBlocks[] = '<details>';
            $moduleIndexDetailBlocks[] = '<summary><strong>' . pageName($root, $pageGroup['path']) . '</strong> — <code>' . str_replace('resources/js/Pages/', '', $relative) . '</code></summary>';
            $moduleIndexDetailBlocks[] = '';
            $moduleIndexDetailBlocks[] = '- **File:** ' . codeLink($root, $pageGroup['path'], 1, $relative) . '.';
            if (!$pageGroup['calls']) {
                $moduleIndexDetailBlocks[] = '- **Chức năng:** dựng giao diện hoặc nhận dữ liệu qua Inertia/Component/composable; chưa phát hiện API trực tiếp trong file.';
            } else {
                foreach ($pageGroup['calls'] as $call) {
                    $function = $call['function'];
                    $analysis = $functionAnalysis[$call['controller']['class'] . '::' . $function['name']] ?? [];
                    $moduleIndexDetailBlocks[] = '- **' . businessActionLabel($function['name'], $call['subject']) . ':** `'
                        . str_replace('\\|', '/', $call['method']) . ' /' . $call['uri'] . '` → '
                        . codeLink($root, $call['controller']['path'], $function['line'], $call['shortController'] . '::' . $function['name'] . '()')
                        . ' — ' . describe($function['name'], $function['visibility'], $call['subject'], $function['body'], $call['controller']['class']);
                    $moduleIndexDetailBlocks[] = '  - **Sửa Function này ảnh hưởng:** ' . pageEffect($function['name'], $call['subject'], $pageGroup['path']) . ' trên chính trang này; '
                        . (!empty($analysis['dependencies']) ? 'đồng thời cần kiểm tra ' . implode(', ', $analysis['dependencies']) . '.' : 'chưa phát hiện Service/model được gọi trực tiếp.');
                    $moduleIndexDetailBlocks[] = '  - **Trang khác và test cần kiểm tra:** ' . referenceLinks($root, $analysis['pages'] ?? [])
                        . ' · ' . referenceLinks($root, $analysis['tests'] ?? []) . '.';
                }
            }
            $moduleIndexDetailBlocks[] = '';
            $moduleIndexDetailBlocks[] = '</details>';
            $moduleIndexDetailBlocks[] = '';
        }
        $moduleIndexDetailBlocks[] = '### BE — Controller và Service';
        $moduleIndexDetailBlocks[] = '';
        foreach ($subgroup['controllers'] as $shortController) {
            $controller = $controllerByShort[$shortController] ?? null;
            if (!$controller) continue;
            $controllerAnchor = 'chi-tiet-' . anchorId($controller['class']);
            if (isset($emittedModuleControllers[$controller['class']])) {
                $moduleIndexDetailBlocks[] = '> **Controller dùng chung:** [' . $shortController . ' — mở phần chi tiết duy nhất](#' . $controllerAnchor . '). Các API/Function mà nghiệp vụ này sử dụng đã được liên kết tại từng trang FE phía trên.';
                $moduleIndexDetailBlocks[] = '';
                continue;
            }
            $emittedModuleControllers[$controller['class']] = true;
            $listedFunctions = array_values($controller['functions']);
            $moduleIndexDetailBlocks[] = '<a id="' . $controllerAnchor . '"></a>';
            $moduleIndexDetailBlocks[] = '';
            $moduleIndexDetailBlocks[] = '<details>';
            $moduleIndexDetailBlocks[] = '<summary><strong>Controller ' . $shortController . '</strong> — ' . count($listedFunctions) . ' Function</summary>';
            $moduleIndexDetailBlocks[] = '';
            $moduleIndexDetailBlocks[] = '- **File:** ' . codeLink($root, $controller['path'], 1, $controller['relative']) . '.';
            $subject = controllerSubject($controller['class']);
            foreach ($listedFunctions as $function) {
                $methodRoutes = $routeMap[$controller['class']][$function['name']] ?? [];
                $analysis = $functionAnalysis[$controller['class'] . '::' . $function['name']] ?? [];
                $apis = [];
                foreach ($methodRoutes as $route) $apis[] = str_replace('|', '/', $route['method']) . ' /' . $route['uri'];
                $moduleIndexDetailBlocks[] = '- `' . $function['visibility'] . '` '
                    . codeLink($root, $controller['path'], $function['line'], $function['name'] . '()')
                    . ($apis ? ' — API `' . implode('`, `', $apis) . '`' : ' — không có API trực tiếp')
                    . ': ' . describe($function['name'], $function['visibility'], $subject, $function['body'], $controller['class']);
                $moduleIndexDetailBlocks[] = '  - **Đường dẫn sửa nhanh:** ' . ($analysis['quick_path'] ?? 'chưa tạo được đường dẫn tự động; tìm caller trước khi sửa.') . '.';
                $moduleIndexDetailBlocks[] = '  - **Mức độ ảnh hưởng:** ' . impactAssessmentText($analysis['assessment'] ?? ['Trung bình', 'chưa đủ dữ liệu để phân loại chính xác']);
                $moduleIndexDetailBlocks[] = '  - **Ảnh hưởng khi sửa:** ' . ($analysis['impact'] ?? 'chưa đủ dữ liệu phân tích tĩnh; cần tìm nơi gọi Function trước khi sửa.');
            }
            $moduleIndexDetailBlocks[] = '';
            $moduleIndexDetailBlocks[] = '</details>';
            $moduleIndexDetailBlocks[] = '';
        }
        if (!$activeServices) {
            $moduleIndexDetailBlocks[] = '> **Service:** không phát hiện Controller của nhóm gọi Service trực tiếp; logic hiện nằm trong Controller/model hoặc đi qua thành phần gián tiếp.';
            $moduleIndexDetailBlocks[] = '';
        }
        foreach ($activeServices as $serviceName => $calledServiceMethods) {
            $servicePath = $root . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Services' . DIRECTORY_SEPARATOR . $serviceName . '.php';
            if (!is_file($servicePath)) continue;
            $serviceAnchor = 'chi-tiet-service-' . anchorId($serviceName);
            if (isset($emittedModuleServices[$serviceName])) {
                $calledMethodLinks = [];
                foreach ($calledServiceMethods as $calledMethod) {
                    $calledMethodLinks[] = codeLink($root, $servicePath, classMethodLine($servicePath, $calledMethod), $serviceName . '::' . $calledMethod . '()');
                }
                $moduleIndexDetailBlocks[] = '> **Service dùng chung:** [' . $serviceName . ' — mở phần chi tiết duy nhất](#' . $serviceAnchor . ')'
                    . ($calledMethodLinks ? '; Function nhóm này gọi: ' . implode(', ', $calledMethodLinks) : '; chưa phát hiện lời gọi trực tiếp trong Controller của nhóm') . '.';
                $moduleIndexDetailBlocks[] = '';
                continue;
            }
            $emittedModuleServices[$serviceName] = true;
            $serviceFunctions = array_values(controllerFunctions($root, $servicePath));
            $moduleIndexDetailBlocks[] = '<a id="' . $serviceAnchor . '"></a>';
            $moduleIndexDetailBlocks[] = '';
            $moduleIndexDetailBlocks[] = '<details>';
            $moduleIndexDetailBlocks[] = '<summary><strong>Service ' . $serviceName . '</strong> — ' . count($serviceFunctions) . ' Function public/private/protected</summary>';
            $moduleIndexDetailBlocks[] = '';
            $moduleIndexDetailBlocks[] = '- **File:** ' . codeLink($root, $servicePath, 1, 'app/Services/' . $serviceName . '.php') . '.';
            $moduleIndexDetailBlocks[] = '- **Logic nghiệp vụ:** ' . ($servicePurposes[$serviceName] ?? ('Xử lý logic dùng lại cho ' . strtolower($subgroup['title']) . '.'));
            if (!$serviceFunctions) $moduleIndexDetailBlocks[] = '- **Lời gọi trực tiếp:** chưa phát hiện trong Controller của nhóm; Service có thể được gọi gián tiếp qua Service khác hoặc được liệt kê vì ảnh hưởng nghiệp vụ dùng chung.';
            foreach ($serviceFunctions as $function) {
                $serviceCallerLinks = [];
                $serviceAffectedPages = [];
                $serviceAffectedTests = [];
                foreach ($controllerByShort as $shortController => $callerController) {
                    $callerSource = @file_get_contents($callerController['path']) ?: '';
                    foreach ($callerController['functions'] as $callerFunction) {
                        $dependencyText = implode(' ', functionDependencyLinks($root, $callerSource, $callerFunction['body']));
                        if (!str_contains($dependencyText, $serviceName . '::' . $function['name'] . '()')) continue;
                        $callerKey = $callerController['class'] . '::' . $callerFunction['name'];
                        $serviceCallerLinks[$callerKey] = codeLink(
                            $root,
                            $callerController['path'],
                            $callerFunction['line'],
                            $shortController . '::' . $callerFunction['name'] . '()'
                        );
                        foreach (($functionAnalysis[$callerKey]['pages'] ?? []) as $page) {
                            $pageKey = str_replace('\\', '/', $page['path']) . ':' . $page['line'];
                            $serviceAffectedPages[$pageKey] = codeLink($root, $page['path'], $page['line'], pageName($root, $page['path']));
                        }
                        foreach (($functionAnalysis[$callerKey]['tests'] ?? []) as $test) {
                            $testKey = str_replace('\\', '/', $test['path']) . ':' . $test['line'];
                            $serviceAffectedTests[$testKey] = codeLink($root, $test['path'], $test['line'], basename($test['path']) . ':' . $test['line']);
                        }
                    }
                }
                $moduleIndexDetailBlocks[] = '- `' . $function['visibility'] . '` '
                    . codeLink($root, $servicePath, $function['line'], $function['name'] . '()')
                    . ': ' . describe($function['name'], $function['visibility'], strtolower($subgroup['title']), $function['body'], 'App\\Services\\' . $serviceName);
                $internalCallerLinks = [];
                foreach ($function['callers'] as $callerLine) {
                    $internalCallerLinks[] = codeLink($root, $servicePath, $callerLine, 'lời gọi nội bộ dòng ' . $callerLine);
                }
                $calledByText = $serviceCallerLinks
                    ? implode(', ', $serviceCallerLinks)
                    : ($internalCallerLinks ? implode(', ', $internalCallerLinks) : 'chưa tìm thấy nơi gọi trực tiếp bằng phân tích tĩnh');
                $serviceDependencies = functionDependencyLinks($root, @file_get_contents($servicePath) ?: '', $function['body']);
                $serviceAssessment = impactAssessment(
                    $function['name'],
                    $function['visibility'],
                    $function['body'],
                    $serviceDependencies,
                    array_values($serviceAffectedPages)
                );
                $moduleIndexDetailBlocks[] = '  - **Đường dẫn sửa nhanh:** Controller ' . $calledByText
                    . ' → Service ' . codeLink($root, $servicePath, $function['line'], $serviceName . '::' . $function['name'] . '()')
                    . ($serviceDependencies ? ' → Service/Model ' . implode(', ', $serviceDependencies) : ' → Service/Model: chưa phát hiện lời gọi tiếp theo')
                    . ($serviceAffectedPages ? ' → FE ' . implode(', ', $serviceAffectedPages) : ' → FE: chưa ánh xạ được trang trực tiếp')
                    . ($serviceAffectedTests ? ' → Test ' . implode(', ', $serviceAffectedTests) : ' → Test: chưa ánh xạ được test trực tiếp') . '.';
                $moduleIndexDetailBlocks[] = '  - **Mức độ ảnh hưởng:** ' . impactAssessmentText($serviceAssessment);
                $moduleIndexDetailBlocks[] = '  - **Ảnh hưởng khi sửa:** thay đổi logic dùng chung của Service; cần kiểm tra các Function gọi phía trên'
                    . ($serviceAffectedPages ? ' và các trang ' . implode(', ', $serviceAffectedPages) : '; chưa ánh xạ được trang gọi trực tiếp') . '.';
            }
            $moduleIndexDetailBlocks[] = '';
            $moduleIndexDetailBlocks[] = '</details>';
            $moduleIndexDetailBlocks[] = '';
        }
        $moduleIndexDetailBlocks[] = '- **Đọc sâu:** [Function và ảnh hưởng](docs/PROJECT_FUNCTION_INDEX.md) · [Tìm lỗi](docs/PROJECT_DEBUGGING_INDEX.md) · [Database](docs/PROJECT_DATABASE_INDEX.md).';
        $moduleIndexDetailBlocks[] = '';
        $moduleIndexDetailBlocks[] = '</details>';
        $moduleIndexDetailBlocks[] = '';
    }
    $moduleIndexBlocksByTitle[$groupDefinition['title']] = array_slice($moduleIndexDetailBlocks, $groupBlockStart);
}

$moduleIndexPath = $root . DIRECTORY_SEPARATOR . 'MODULE_INDEX.md';
$moduleIndexSource = @file_get_contents($moduleIndexPath);
if ($moduleIndexSource !== false) {
    $moduleIndexSource = preg_replace('/<!-- GENERATED_MODULE_DETAILS_START -->[\s\S]*?<!-- GENERATED_MODULE_DETAILS_END -->\R*/', '', $moduleIndexSource);
    $moduleIndexSource = preg_replace('/<!-- GENERATED_COMBINED_LOOKUP_START -->[\s\S]*?<!-- GENERATED_COMBINED_LOOKUP_END -->\R*/', '', $moduleIndexSource);
    $moduleIndexSource = preg_replace('/<!-- GENERATED_MODULE_GROUP_[A-Z0-9_]+_START -->[\s\S]*?<!-- GENERATED_MODULE_GROUP_[A-Z0-9_]+_END -->\R*/', '', $moduleIndexSource);
    $moduleIndexSource = preg_replace(
        '/Cập nhật theo mã nguồn ngày \*\*\d{2}\/\d{2}\/\d{4}\*\*/',
        'Cập nhật theo mã nguồn ngày **' . $generatedAt . '**',
        $moduleIndexSource,
        1
    );
    if ($moduleIndexLookupLines) {
        $combinedLookupSection = implode(PHP_EOL, array_merge([
            '<!-- GENERATED_COMBINED_LOOKUP_START -->',
            '',
            '> Phần tra cứu hợp nhất từ `PROJECT_MODULE_DETAIL_INDEX.md`, được sinh tự động từ code.',
            '',
        ], $moduleIndexLookupLines, ['', '<!-- GENERATED_COMBINED_LOOKUP_END -->', '']));
        $firstModuleAt = strpos($moduleIndexSource, "\n## 1. ");
        if ($firstModuleAt !== false) {
            $insertAt = $firstModuleAt + 1;
            $moduleIndexSource = substr($moduleIndexSource, 0, $insertAt)
                . $combinedLookupSection . PHP_EOL
                . substr($moduleIndexSource, $insertAt);
        }
    }
    foreach ($moduleIndexBlocksByTitle as $sectionTitle => $sectionLines) {
        $marker = strtoupper(str_replace('-', '_', anchorId($sectionTitle)));
        $generatedModuleSection = implode(PHP_EOL, array_merge([
            '<!-- GENERATED_MODULE_GROUP_' . $marker . '_START -->',
            '',
            '> Chi tiết FE/BE bên dưới được sinh tự động từ code; mở từng nhóm nghiệp vụ con khi cần tra cứu.',
            '',
        ], $sectionLines, ['<!-- GENERATED_MODULE_GROUP_' . $marker . '_END -->', '']));
        $heading = '## ' . $sectionTitle;
        $headingAt = strpos($moduleIndexSource, $heading);
        if ($headingAt === false) continue;
        $nextHeadingAt = strpos($moduleIndexSource, "\n## ", $headingAt + strlen($heading));
        $insertAt = $nextHeadingAt === false ? strlen($moduleIndexSource) : $nextHeadingAt + 1;
        $moduleIndexSource = substr($moduleIndexSource, 0, $insertAt)
            . $generatedModuleSection . PHP_EOL
            . substr($moduleIndexSource, $insertAt);
    }
    file_put_contents($moduleIndexPath, portableRootIndexLinks($moduleIndexSource, $root));
}

$moduleRedirectDoc = [
    '# Chỉ mục chi tiết module đã được hợp nhất',
    '',
    '> Nội dung của trang này đã được chuyển vào [`MODULE_INDEX.md`](../MODULE_INDEX.md) để chỉ còn một nguồn tra cứu chính và tránh hai bản tài liệu bị lệch nhau.',
    '',
    '## Đi tới tài liệu chính',
    '',
    '- [Mở mục lục module hợp nhất](../MODULE_INDEX.md).',
    '- [Tra cứu Function toàn dự án](PROJECT_FUNCTION_INDEX.md).',
    '- [Tìm lỗi theo triệu chứng](../MODULE_INDEX.md#tra-cuu-tim-loi-theo-trieu-chung).',
    '- [Tra Model, bảng và migration](PROJECT_DATABASE_INDEX.md).',
    '- [Xem luồng nghiệp vụ](../resources/docs/BUSINESS_FLOWS.md).',
    '',
    '> Không bổ sung nội dung chi tiết mới vào file này. Hãy sửa `MODULE_INDEX.md` hoặc bộ sinh `docs/generate_project_function_index.php`.',
];
file_put_contents($moduleOutput, implode(PHP_EOL, $moduleRedirectDoc));
file_put_contents($debugOutput, implode(PHP_EOL, $debugDoc));
file_put_contents($databaseOutput, implode(PHP_EOL, $databaseDoc));
echo 'Đã tạo ' . $output . ' (' . count($controllers) . ' bộ điều khiển, ' . $totalFunctions . " hàm).\n";
echo 'Đã cập nhật trang chuyển hướng ' . $moduleOutput . ".\n";
echo 'Đã tạo ' . $debugOutput . ".\n";
echo 'Đã tạo ' . $databaseOutput . ".\n";
