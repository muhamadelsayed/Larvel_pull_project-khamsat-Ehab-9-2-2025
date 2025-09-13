<x-guest.layout>
    <x-slot name="title">تسجيل الدخول</x-slot>

    <div class="flex items-center justify-center min-h-screen bg-gray-100">
        <div class="w-full max-w-md px-8 py-10 bg-white rounded-lg shadow-xl">
            {{-- ... باقي محتوى الفورم كما هو بدون تغيير ... --}}
            <div class="text-center">
                <h1 class="text-3xl font-bold text-gray-800">لوحة التحكم</h1>
                <p class="mt-2 text-gray-500">مرحباً بك، يرجى تسجيل الدخول للمتابعة</p>
            </div>

            @if (request()->get('success'))
                <div class="p-4 mt-6 text-sm text-green-700 bg-green-100 rounded-lg" role="alert">
                    تم إنشاء حسابك بنجاح! يمكنك الآن تسجيل الدخول.
                </div>
            @endif
            @if (session('success'))
                <div class="p-4 mt-6 text-sm text-green-700 bg-green-100 rounded-lg" role="alert">
                    {{ session('success') }}
                </div>
            @endif
            @if($errors->any())
                <div class="p-4 mt-6 text-sm text-red-700 bg-red-100 rounded-lg" role="alert">
                    <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            <form action="{{ route('admin.login') }}" method="POST" class="mt-8 space-y-6">
                @csrf
                <div>
                    <label for="phone" class="block mb-2 text-sm font-medium text-gray-700">رقم الهاتف</label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone') }}" required
                           class="block w-full px-4 py-3 text-gray-800 bg-gray-50 border rounded-lg" placeholder="ادخل رقم هاتفك">
                </div>
                <div>
                    <label for="password" class="block mb-2 text-sm font-medium text-gray-700">كلمة المرور</label>
                    <input type="password" name="password" id="password" required
                           class="block w-full px-4 py-3 text-gray-800 bg-gray-50 border rounded-lg" placeholder="********">
                </div>
                <div>
                    <button type="submit" class="w-full px-4 py-3 font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                        تسجيل الدخول
                    </button>
                </div>
            </form>
            <div class="mt-6 text-center">
                <a href="{{ route('admin.register') }}" class="text-sm font-medium text-blue-600 hover:underline">
                    لا تملك حساباً؟ أنشئ حساباً جديداً
                </a>
            </div>
        </div>
    </div>
</x-guest.layout>