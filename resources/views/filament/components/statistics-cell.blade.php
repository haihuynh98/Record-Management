<div class="text-center space-y-1">
    <div class="text-sm font-medium text-gray-900">
        Tổng số lượng hồ sơ: <span class="font-bold text-blue-600">{{ $count }}</span>
    </div>
    <div class="text-sm text-gray-700">
        Tổng giá trị: <span class="font-bold text-green-600">{{ number_format($amount, 0, ',', ',') }} VNĐ</span>
    </div>
</div>
