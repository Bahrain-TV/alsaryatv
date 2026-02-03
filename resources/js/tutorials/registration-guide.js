/**
 * Registration Tutorial using Driver.js
 * Interactive walkthrough for the registration process
 */

export function initRegistrationTutorial() {
    if (typeof window.driver === 'undefined') {
        console.error('Driver.js not loaded');
        return;
    }

    const driver = window.driver.driver;

    const registrationSteps = [
        {
            element: '#logo-section',
            popover: {
                title: 'أهلاً بك في السارية',
                description: 'برنامج تلفزيون البحرين الرئيسي خلال شهر رمضان المبارك',
                side: 'bottom',
                align: 'center'
            }
        },
        {
            element: '#toggleIndividual',
            popover: {
                title: 'اختر نوع التسجيل',
                description: 'اضغط هنا للتسجيل كمشارك فردي',
                side: 'bottom',
                align: 'center'
            }
        },
        {
            element: 'input[name="name"]',
            popover: {
                title: 'الاسم الكامل',
                description: 'أدخل اسمك الرباعي كاملاً',
                side: 'bottom',
                align: 'start'
            }
        },
        {
            element: 'input[name="phone_number"]',
            popover: {
                title: 'رقم الهاتف',
                description: 'أدخل رقم هاتفك الجوال (مثال: +973 33333333)',
                side: 'bottom',
                align: 'start'
            }
        },
        {
            element: 'input[name="cpr"]',
            popover: {
                title: 'الرقم الشخصي',
                description: 'أدخل رقمك الشخصي/البطاقة المدنية',
                side: 'bottom',
                align: 'start'
            }
        },
        {
            element: 'input[name="policy_agreement"]',
            popover: {
                title: 'الموافقة على الشروط',
                description: 'تأكد من الموافقة على شروط المشاركة',
                side: 'top',
                align: 'start'
            }
        },
        {
            element: 'button[type="submit"]',
            popover: {
                title: 'أرسل التسجيل',
                description: 'اضغط هنا لإرسال نموذج التسجيل',
                side: 'top',
                align: 'center'
            }
        },
        {
            element: '#sponsors-logos',
            popover: {
                title: 'رعاة البرنامج',
                description: 'البرنامج يأتيكم برعاية هذه الشركات الموثوقة',
                side: 'top',
                align: 'center'
            }
        }
    ];

    const driverObj = driver({
        showProgress: true,
        allowClose: true,
        overlayClickNext: false,
        stagePadding: 10,
        popoverClass: 'driver-popover-ar',
        steps: registrationSteps
    });

    return driverObj;
}

/**
 * Family Registration Tutorial
 */
export function initFamilyRegistrationTutorial() {
    if (typeof window.driver === 'undefined') {
        console.error('Driver.js not loaded');
        return;
    }

    const driver = window.driver.driver;

    const familySteps = [
        {
            element: '#toggleFamily',
            popover: {
                title: 'تسجيل العائلة',
                description: 'اضغط هنا للتسجيل كعائلة',
                side: 'bottom',
                align: 'center'
            }
        },
        {
            element: 'input[name="name"]',
            popover: {
                title: 'اسم المسؤول عن العائلة',
                description: 'أدخل اسم رب الأسرة كاملاً',
                side: 'bottom',
                align: 'start'
            }
        },
        {
            element: 'input[name="phone_number"]',
            popover: {
                title: 'رقم التواصل',
                description: 'رقم جوال يمكن التواصل عليه',
                side: 'bottom',
                align: 'start'
            }
        },
        {
            element: 'input[name="cpr"]',
            popover: {
                title: 'الرقم الشخصي',
                description: 'رقم البطاقة المدنية لمسؤول العائلة',
                side: 'bottom',
                align: 'start'
            }
        },
        {
            element: 'button[type="submit"]',
            popover: {
                title: 'تسجيل العائلة',
                description: 'اضغط لتسجيل عائلتك بالكامل',
                side: 'top',
                align: 'center'
            }
        }
    ];

    const driverObj = driver({
        showProgress: true,
        allowClose: true,
        overlayClickNext: false,
        stagePadding: 10,
        popoverClass: 'driver-popover-ar',
        steps: familySteps
    });

    return driverObj;
}
