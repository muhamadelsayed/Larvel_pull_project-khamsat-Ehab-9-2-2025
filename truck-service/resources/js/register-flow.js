// استيراد الدوال اللازمة من Firebase
import { RecaptchaVerifier, signInWithPhoneNumber } from "firebase/auth";

// الآن، الدالة تقبل `auth` كمعامل
export default (auth) => ({
    state: 'enter_phone', // enter_phone, enter_otp, enter_details
    loading: false,
    errorMessage: '',
    phone: '',
    otp: '',
    name: '',
    password: '',
    firebaseIdToken: '',
    confirmationResult: null,

    initFirebase() {
        // لم نعد بحاجة لـ nextTick لأننا نضمن أن auth موجود
        window.recaptchaVerifier = new RecaptchaVerifier(auth, 'recaptcha-container', {
            'size': 'invisible'
        });
    },

    async sendOtp() {
        this.loading = true;
        this.errorMessage = '';
        try {
            this.confirmationResult = await signInWithPhoneNumber(auth, this.phone, window.recaptchaVerifier);
            this.state = 'enter_otp';
        } catch (error) {
            this.errorMessage = 'فشل إرسال الرمز. تأكد من صحة الرقم (مع رمز الدولة).';
            console.error(error);
        } finally {
            this.loading = false;
        }
    },

    async verifyOtp() {
        this.loading = true;
        this.errorMessage = '';
        try {
            const result = await this.confirmationResult.confirm(this.otp);
            this.firebaseIdToken = await result.user.getIdToken();
            this.state = 'enter_details';
        } catch (error) {
            this.errorMessage = 'الرمز الذي أدخلته غير صحيح.';
            console.error(error);
        } finally {
            this.loading = false;
        }
    },

    async submitRegistration() {
        this.loading = true;
        this.errorMessage = '';

        const formData = new FormData();
        formData.append('name', this.name);
        formData.append('phone', this.phone);
        formData.append('password', this.password);
        formData.append('account_type', 'client');

        try {
            const response = await fetch('/api/register', {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${this.firebaseIdToken}`,
                    'Accept': 'application/json',
                },
                body: formData
            });

            const data = await response.json();

            if (!response.ok) {
                this.errorMessage = data.message || 'حدث خطأ ما.';
                if(data.errors) {
                    this.errorMessage = Object.values(data.errors).join(' ');
                }
                return;
            }
            
            // نجاح! توجيه المستخدم لصفحة الدخول
            window.location.href = "/admin/login?success=1";

        } catch (error) {
            this.errorMessage = 'فشل الاتصال بالخادم.';
            console.error(error);
        } finally {
            this.loading = false;
        }
    }
});