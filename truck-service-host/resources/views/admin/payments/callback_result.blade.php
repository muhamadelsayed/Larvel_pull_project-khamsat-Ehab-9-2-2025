<x-guest.layout>
    <div class="flex items-center justify-center min-h-screen bg-gray-100 px-4">
        <div class="p-8 bg-white shadow-lg rounded-2xl text-center max-w-sm w-full border-t-4 {{ $status === 'CAPTURED' ? 'border-green-500' : 'border-red-500' }}">
            @if($status === 'CAPTURED')
                <div class="text-green-500 mb-4 text-6xl">✓</div>
                <h2 class="text-2xl font-bold mb-2">تم الدفع بنجاح!</h2>
                <p class="text-gray-600 mb-6">شكراً لك، تم تأكيد حجزك بنجاح.</p>
            @else
                <div class="text-red-500 mb-4 text-6xl">✕</div>
                <h2 class="text-2xl font-bold mb-2">فشلت عملية الدفع</h2>
                <p class="text-gray-600 mb-6">للأسف، لم تكتمل العملية. يرجى المحاولة مرة أخرى أو التواصل مع الدعم.</p>
            @endif
            
            <div class="bg-gray-50 p-3 rounded-lg text-xs text-gray-500 font-mono mb-6">
                رقم العملية: {{ $tap_id }}
            </div>
            
            <p class="text-sm text-blue-600 font-bold animate-pulse">يمكنك الآن العودة للتطبيق</p>
        </div>
    </div>
</x-guest.layout>