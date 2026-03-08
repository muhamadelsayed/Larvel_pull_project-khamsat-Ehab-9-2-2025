<x-guest.layout>
    <x-slot name="title">شروط الخدمة والأسعار</x-slot>
    <div class="bg-gradient-to-b from-indigo-50 to-white min-h-screen py-20 px-4">
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="text-center mb-16">
                <h1 class="text-4xl md:text-5xl font-black text-gray-900 mb-4">شروط الخدمة والأسعار</h1>
                <p class="text-gray-600 text-lg">نحن نؤمن بالشفافية، إليك كافة التفاصيل والسياسات التي تحكم عملنا</p>
                <div class="w-24 h-1 bg-indigo-600 mx-auto mt-6 rounded-full"></div>
            </div>

            <!-- Policies List -->
            <div class="space-y-6">
                @forelse($policies as $index => $policy)
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8 hover:shadow-md transition-shadow">
                        <div class="flex items-start gap-4">
                            <span class="bg-indigo-100 text-indigo-600 font-bold px-3 py-1 rounded-lg text-sm">
                                {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}
                            </span>
                            <div class="flex-1">
                                <h3 class="text-xl font-bold text-gray-800 mb-3">{{ $policy->title }}</h3>
                                <div class="text-gray-600 leading-relaxed whitespace-pre-line text-justify">
                                    {{ $policy->content }}
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-20">
                        <p class="text-gray-400 italic">سيتم إضافة البنود قريباً...</p>
                    </div>
                @endforelse
            </div>

            <!-- Footer Action -->
            <div class="mt-16 text-center">
                <p class="text-gray-500 mb-6">لديك استفسار حول هذه البنود؟</p>
                <a href="mailto:support@bull-station.com" class="inline-block bg-indigo-600 text-white px-8 py-3 rounded-full font-bold hover:bg-indigo-700 transition shadow-lg">
                    تواصل مع الدعم الفني
                </a>
            </div>
        </div>
    </div>
</x-guest.layout>