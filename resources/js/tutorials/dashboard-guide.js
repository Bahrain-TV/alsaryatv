/**
 * Dashboard Tutorial using Driver.js
 * Interactive walkthrough for dashboard features
 */

export function initDashboardTutorial() {
    if (typeof window.driver === 'undefined') {
        console.error('Driver.js not loaded');
        return;
    }

    const driver = window.driver.driver;

    const dashboardSteps = [
        {
            element: 'header',
            popover: {
                title: 'لوحة التحكم',
                description: 'مرحباً بك في لوحة التحكم الخاصة بك',
                side: 'bottom',
                align: 'center'
            }
        },
        {
            element: '[data-tour="caller-list"]',
            popover: {
                title: 'قائمة المشاركين',
                description: 'عرض جميع المشاركين والمشاركات المسجلة',
                side: 'right',
                align: 'start'
            },
            onHighlighted: () => {
                // Custom actions when highlighted
            }
        },
        {
            element: '[data-tour="statistics"]',
            popover: {
                title: 'الإحصائيات',
                description: 'مشاهدة احصائيات المشاركات والزيارات',
                side: 'right',
                align: 'start'
            }
        },
        {
            element: '[data-tour="settings"]',
            popover: {
                title: 'الإعدادات',
                description: 'إدارة إعدادات البرنامج والبيانات',
                side: 'left',
                align: 'start'
            }
        },
        {
            element: '[data-tour="live-button"]',
            popover: {
                title: 'البث المباشر',
                description: 'تفعيل البث المباشر والتحكم فيه',
                side: 'bottom',
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
        steps: dashboardSteps
    });

    return driverObj;
}

/**
 * Quick Tips Tutorial
 */
export function initQuickTips() {
    if (typeof window.driver === 'undefined') {
        console.error('Driver.js not loaded');
        return;
    }

    const driver = window.driver.driver;

    const tips = [
        {
            element: '[data-tour="search-box"]',
            popover: {
                title: 'البحث السريع',
                description: 'ابحث عن أي مشارك باستخدام الاسم أو رقم الهاتف',
                side: 'bottom',
                align: 'start'
            }
        },
        {
            element: '[data-tour="export-data"]',
            popover: {
                title: 'تصدير البيانات',
                description: 'قم بتصدير البيانات إلى ملف Excel',
                side: 'bottom',
                align: 'center'
            }
        },
        {
            element: '[data-tour="notifications"]',
            popover: {
                title: 'الإشعارات',
                description: 'تلقي إشعارات الأنشطة الجديدة والمهمة',
                side: 'bottom',
                align: 'end'
            }
        }
    ];

    const driverObj = driver({
        showProgress: true,
        allowClose: true,
        overlayClickNext: false,
        stagePadding: 10,
        popoverClass: 'driver-popover-ar',
        steps: tips
    });

    return driverObj;
}
