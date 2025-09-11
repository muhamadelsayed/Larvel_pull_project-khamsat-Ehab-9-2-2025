import './bootstrap';

// 1. استيراد Alpine
import Alpine from 'alpinejs';

// 2. استيراد دوال Firebase
import { initializeApp } from "firebase/app";
import { getAuth } from "firebase/auth";

// 3. استيراد منطق التسجيل الخاص بنا
import registerFlow from './register-flow';

// إعدادات Firebase من ملف .env
const firebaseConfig = {
  apiKey: import.meta.env.VITE_FIREBASE_API_KEY,
  authDomain: import.meta.env.VITE_FIREBASE_AUTH_DOMAIN,
  projectId: import.meta.env.VITE_FIREBASE_PROJECT_ID,
  storageBucket: import.meta.env.VITE_FIREBASE_STORAGE_BUCKET,
  messagingSenderId: import.meta.env.VITE_FIREBASE_MESSAGING_SENDER_ID,
  appId: import.meta.env.VITE_FIREBASE_APP_ID
};

// 4. تهيئة Firebase وإنشاء كائن المصادقة
const app = initializeApp(firebaseConfig);
const auth = getAuth(app);

// 5. تسجيل مكون Alpine.js وتمرير كائن `auth` إليه
// الآن Alpine يعرف ما هو `registerFlow`
Alpine.data('registerFlow', () => registerFlow(auth));

// 6. جعل Alpine متاحًا عالميًا وتشغيله
window.Alpine = Alpine;
Alpine.start();