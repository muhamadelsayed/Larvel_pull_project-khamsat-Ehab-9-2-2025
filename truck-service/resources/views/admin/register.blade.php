<x-guest.layout>
    <x-slot name="title">إنشاء حساب جديد</x-slot>
    {{-- لا نعرض الهيدر في صفحة التسجيل --}}
    {{-- 
        تم نقل كل منطق JavaScript إلى resources/js/register-flow.js.
        هذا الملف (View) مسؤول فقط عن عرض HTML.
    --}}
    <div class="flex items-center justify-center min-h-screen -mt-32 bg-gray-100 py-12"
         x-data="registerFlow" x-init="initFirebase()">
        
        <div class="w-full max-w-lg px-8 py-10 bg-white rounded-lg shadow-xl">
            
            {{-- الخطوة 1: إدخال رقم الهاتف --}}
            <div x-show="state === 'enter_phone'">
                <div class="text-center">
                    <h1 class="text-3xl font-bold text-gray-800">إنشاء حساب جديد</h1>
                    <p class="mt-2 text-gray-500">الخطوة 1 من 3: أدخل رقم هاتفك</p>
                </div>
                <div class="mt-8 space-y-6">
                    <div>
                        <label for="phone" class="block mb-2 text-sm font-medium text-gray-700">رقم الهاتف (مع رمز الدولة)</label>
                        <input type="text" x-model="phone" id="phone" placeholder="+9665XXXXXXXX"
                               class="block w-full px-4 py-3 text-left bg-gray-50 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    {{-- هذا العنصر ضروري لعرض reCAPTCHA بشكل غير مرئي --}}
                    <div id="recaptcha-container"></div>
                    <p x-text="errorMessage" class="text-sm text-red-600 h-4"></p>
                    <button @click="sendOtp()" :disabled="loading"
                            class="w-full px-4 py-3 font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:bg-gray-400 flex items-center justify-center">
                        <span x-show="!loading">إرسال رمز التحقق</span>
                        <span x-show="loading">جاري الإرسال...</span>
                    </button>
                </div>
            </div>

            {{-- الخطوة 2: إدخال رمز OTP --}}
            <div x-show="state === 'enter_otp'" style="display: none;">
                 <div class="text-center">
                    <h1 class="text-3xl font-bold text-gray-800">التحقق من الرمز</h1>
                    <p class="mt-2 text-gray-500" x-text="`الخطوة 2 من 3: أدخل الرمز المرسل إلى ${phone}`"></p>
                </div>
                <div class="mt-8 space-y-6">
                    <div>
                        <label for="otp" class="block mb-2 text-sm font-medium text-gray-700">رمز التحقق (OTP)</label>
                        <input type="text" x-model="otp" id="otp" maxlength="6"
                               class="block w-full px-4 py-3 text-center tracking-[1em] bg-gray-50 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <p x-text="errorMessage" class="text-sm text-red-600 h-4"></p>
                    <button @click="verifyOtp()" :disabled="loading"
                            class="w-full px-4 py-3 font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:bg-gray-400 flex items-center justify-center">
                        <span x-show="!loading">التحقق من الرمز</span>
                        <span x-show="loading">جاري التحقق...</span>
                    </button>
                </div>
            </div>

            {{-- الخطوة 3: إدخال باقي البيانات --}}
            <div x-show="state === 'enter_details'" style="display: none;">
                <div class="text-center">
                    <h1 class="text-3xl font-bold text-gray-800">إكمال التسجيل</h1>
                    <p class="mt-2 text-gray-500">الخطوة 3 من 3: أكمل بيانات حسابك</p>
                </div>
                <form @submit.prevent="submitRegistration()" class="mt-8 space-y-6">
                    <div>
                        <label for="name" class="block mb-2 text-sm font-medium text-gray-700">الاسم الكامل</label>
                        <input type="text" x-model="name" id="name" required class="block w-full px-4 py-3 bg-gray-50 border rounded-lg">
                    </div>
                    <div>
                        <label for="password" class="block mb-2 text-sm font-medium text-gray-700">كلمة المرور</label>
                        <input type="password" x-model="password" id="password" required class="block w-full px-4 py-3 bg-gray-50 border rounded-lg">
                    </div>
                    <p x-text="errorMessage" class="text-sm text-red-600 h-4"></p>
                    <button type="submit" :disabled="loading"
                            class="w-full px-4 py-3 font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:bg-gray-400 flex items-center justify-center">
                        <span x-show="!loading">إنشاء الحساب</span>
                        <span x-show="loading">جاري الإنشاء...</span>
                    </button>
                </form>
            </div>

            <div class="mt-6 text-center">
                <a href="{{ route('admin.login') }}" class="text-sm font-medium text-blue-600 hover:underline">لديك حساب بالفعل؟ تسجيل الدخول</a>
            </div>
        </div>
    </div>
</x-guest.layout>