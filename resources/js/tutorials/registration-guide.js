/**
 * Registration Tutorial using Driver.js
 * Interactive walkthrough for the registration process
 */

export function initRegistrationTutorial() {
    if (typeof window.driver === 'undefined') {
        console.error('Driver.js not loaded');
        return;
    }

    const driverFn = window.driver.driver || window.driver;

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
            element: '#tab-individual',
            popover: {
                title: 'اختر نوع التسجيل',
                description: 'اضغط هنا للتسجيل كمشارك فردي',
                side: 'bottom',
                align: 'center'
            }
        },
        {
            element: '#name',
            popover: {
                title: 'الاسم الكامل',
                description: 'أدخل اسمك الرباعي كاملاً كما يظهر في بطاقة الهوية',
                side: 'bottom',
                align: 'start'
            }
        },
        {
            element: '#cpr',
            popover: {
                title: 'الرقم الشخصي (CPR)',
                description: 'أدخل رقمك الشخصي المكوّن من 9 أرقام',
                side: 'bottom',
                align: 'start'
            }
        },
        {
            element: '#phone_number',
            popover: {
                title: 'رقم الهاتف',
                description: 'أدخل رقم هاتفك الجوّال الفعّال للتواصل في حال الفوز',
                side: 'bottom',
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
        }
    ];

    const driverObj = driverFn({
        showProgress: true,
        allowClose: true,
        overlayClickNext: false,
        stagePadding: 10,
        animate: true,
        nextBtnText: 'التالي →',
        prevBtnText: '← السابق',
        doneBtnText: '✓ انتهيت',
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

    const driverFn = window.driver.driver || window.driver;

    const familySteps = [
        {
            element: '#tab-family',
            popover: {
                title: 'تسجيل العائلة',
                description: 'اضغط هنا للتسجيل كعائلة',
                side: 'bottom',
                align: 'center'
            }
        },
        {
            element: '#name',
            popover: {
                title: 'اسم المسؤول عن العائلة',
                description: 'أدخل اسم رب الأسرة كاملاً',
                side: 'bottom',
                align: 'start'
            }
        },
        {
            element: '#cpr',
            popover: {
                title: 'الرقم الشخصي (CPR) لرب العائلة',
                description: 'رقم البطاقة المدنية لمسؤول العائلة',
                side: 'bottom',
                align: 'start'
            }
        },
        {
            element: '#phone_number',
            popover: {
                title: 'رقم هاتف التواصل',
                description: 'رقم جوال يمكن التواصل عليه',
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

    const driverObj = driverFn({
        showProgress: true,
        allowClose: true,
        overlayClickNext: false,
        stagePadding: 10,
        animate: true,
        nextBtnText: 'التالي →',
        prevBtnText: '← السابق',
        doneBtnText: '✓ انتهيت',
        steps: familySteps
    });

    return driverObj;
}
