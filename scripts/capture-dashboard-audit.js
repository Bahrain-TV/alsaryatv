import { chromium } from 'playwright';
import { mkdir, writeFile } from 'node:fs/promises';
import path from 'node:path';

const argv = process.argv.slice(2);

const getArg = (flag) => {
    const idx = argv.indexOf(flag);
    return idx !== -1 ? argv[idx + 1] ?? null : null;
};

const BASE_URL = (getArg('--base-url') || process.env.APP_URL || 'http://127.0.0.1:8133').replace(/\/$/, '');
const ROOT_DIR = path.resolve(getArg('--output-dir') || 'artifacts/dashboard-audit');
const SHOTS_DIR = path.join(ROOT_DIR, 'screenshots');
const MANIFEST_PATH = path.join(ROOT_DIR, 'manifest.json');

const ADMIN_EMAIL = getArg('--email') || process.env.ADMIN_EMAIL || 'aldoyh@gmail.com';
const ADMIN_PASSWORD = getArg('--password') || process.env.ADMIN_PASSWORD || '97333334122';

const wait = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

const waitForServer = async (url, maxRetries = 40, intervalMs = 1500) => {
    for (let i = 0; i < maxRetries; i += 1) {
        try {
            const response = await fetch(url, { method: 'GET', redirect: 'manual' });
            if (response.status > 0) return true;
        } catch {
            // retry
        }

        await wait(intervalMs);
    }

    return false;
};

const slugify = (value) => value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');

const loginToAdmin = async (page) => {
    await page.goto(`${BASE_URL}/admin/login`, { waitUntil: 'domcontentloaded', timeout: 30000 });
    await wait(800);

    const emailField = page.locator('input[name="email"], input[type="email"]').first();
    const passwordField = page.locator('input[name="password"], input[type="password"]').first();

    if ((await emailField.count()) === 0 || (await passwordField.count()) === 0) {
        return;
    }

    await emailField.fill(ADMIN_EMAIL);
    await passwordField.fill(ADMIN_PASSWORD);

    const submit = page.locator('button[type="submit"], button:has-text("دخول"), button:has-text("Login")').first();
    if ((await submit.count()) > 0) {
        await submit.click();
    } else {
        await page.keyboard.press('Enter');
    }

    await wait(1800);
};

const scenes = [
    {
        key: 'dashboard-overview',
        url: '/admin',
        titleEn: 'Dashboard overview widgets',
        titleAr: 'نظرة عامة على ودجات لوحة التحكم',
        descEn: 'Quick actions, overview stats, trend chart, peak hours, status distribution, participation, recent activity, winners history.',
        descAr: 'الإجراءات السريعة، إحصائيات عامة، اتجاهات التسجيل، ساعات الذروة، توزيع الحالات، معدل المشاركة، النشاط الأخير، وسجل الفائزين.',
    },
    {
        key: 'dashboard-mid-section',
        url: '/admin',
        titleEn: 'Dashboard charts section',
        titleAr: 'قسم الرسوم البيانية في لوحة التحكم',
        descEn: 'Focused capture for charts and analytical widgets in the middle area of dashboard.',
        descAr: 'لقطة مركزة للرسوم البيانية وودجات التحليل في منتصف لوحة التحكم.',
        action: async (page) => {
            await page.evaluate(() => window.scrollTo({ top: 650, behavior: 'instant' }));
            await wait(600);
        },
    },
    {
        key: 'dashboard-bottom-tables',
        url: '/admin',
        titleEn: 'Dashboard activity tables',
        titleAr: 'جداول النشاط في لوحة التحكم',
        descEn: 'Recent activity and winners history tables at the dashboard bottom area.',
        descAr: 'جدول النشاط الأخير وسجل الفائزين في الجزء السفلي من لوحة التحكم.',
        action: async (page) => {
            await page.evaluate(() => window.scrollTo({ top: document.body.scrollHeight, behavior: 'instant' }));
            await wait(700);
        },
    },
    {
        key: 'analytics-overview',
        url: '/admin/analytics',
        titleEn: 'Advanced analytics overview',
        titleAr: 'نظرة عامة على التحليلات المتقدمة',
        descEn: 'Total callers, winners ratio, total hits, weekly growth and status distribution.',
        descAr: 'إجمالي المتصلين، نسبة الفائزين، إجمالي المشاركات، نمو الأسبوع، وتوزيع الحالات.',
    },
    {
        key: 'analytics-details',
        url: '/admin/analytics',
        titleEn: 'Advanced analytics details',
        titleAr: 'تفاصيل التحليلات المتقدمة',
        descEn: 'Top performers, recent winners, trend history, peak hours and comparison metrics.',
        descAr: 'أفضل المشاركين، الفائزون الأخيرون، اتجاهات التسجيل، ساعات الذروة، ومقارنات الأداء.',
        action: async (page) => {
            await page.evaluate(() => window.scrollTo({ top: 760, behavior: 'instant' }));
            await wait(650);
        },
    },
    {
        key: 'winner-selection-ready',
        url: '/admin/winner-selection',
        titleEn: 'Winner selection screen (ready)',
        titleAr: 'شاشة اختيار الفائز (جاهزة)',
        descEn: 'Spinner area, control buttons, counters and eligible callers list before spinning.',
        descAr: 'منطقة الدوران، أزرار التحكم، العدادات، وقائمة المتصلين المؤهلين قبل البدء.',
    },
    {
        key: 'winner-selection-result',
        url: '/admin/winner-selection',
        titleEn: 'Winner selection result state',
        titleAr: 'حالة نتيجة اختيار الفائز',
        descEn: 'After spin action to show selected winner details and confirm button enabled.',
        descAr: 'بعد بدء الدوران لإظهار بيانات الفائز المختار وتفعيل زر التأكيد.',
        action: async (page) => {
            const spinButton = page.locator('button:has-text("ابدأ"), button:has-text("Start")').first();
            if ((await spinButton.count()) > 0) {
                await spinButton.click();
                await wait(4200);
            }
        },
    },
    {
        key: 'callers-list',
        url: '/admin/callers',
        titleEn: 'Callers management list',
        titleAr: 'قائمة إدارة المتصلين',
        descEn: 'Main callers table with columns, filters, search, row actions and bulk action area.',
        descAr: 'جدول المتصلين الرئيسي مع الأعمدة، الفلاتر، البحث، إجراءات الصفوف، والإجراءات الجماعية.',
    },
    {
        key: 'callers-create',
        url: '/admin/callers/create',
        titleEn: 'Create caller form',
        titleAr: 'نموذج إضافة متصل',
        descEn: 'Manual caller registration form with identity, status and notes fields.',
        descAr: 'نموذج تسجيل متصل يدويًا مع بيانات الهوية والحالة والملاحظات.',
    },
    {
        key: 'winners-list',
        url: '/admin/callers/winners',
        titleEn: 'Winners list page',
        titleAr: 'صفحة قائمة الفائزين',
        descEn: 'Dedicated winners list with winner-status actions and caller details.',
        descAr: 'قائمة الفائزين المخصصة مع إجراءات حالة الفوز وتفاصيل المتصلين.',
    },
    {
        key: 'users-list',
        url: '/admin/users',
        titleEn: 'Users management list',
        titleAr: 'قائمة إدارة المستخدمين',
        descEn: 'Admin users listing with role badges, permissions toggles and CRUD actions.',
        descAr: 'جدول المستخدمين الإداريين مع شارات الأدوار وصلاحيات المدير وإجراءات CRUD.',
    },
    {
        key: 'users-create',
        url: '/admin/users/create',
        titleEn: 'Create user form',
        titleAr: 'نموذج إضافة مستخدم',
        descEn: 'User creation form with credentials and role/permissions setup.',
        descAr: 'نموذج إنشاء مستخدم جديد مع بيانات الدخول وتحديد الدور والصلاحيات.',
    },
    {
        key: 'obs-videos-list',
        url: '/admin/obs-overlay-videos',
        titleEn: 'OBS overlay videos list',
        titleAr: 'قائمة فيديوهات OBS Overlay',
        descEn: 'Media inventory with recording date, filename, size and status filters.',
        descAr: 'إدارة ملفات الفيديو مع تاريخ التسجيل، اسم الملف، الحجم، وفلاتر الحالة.',
    },
    {
        key: 'obs-videos-create',
        url: '/admin/obs-overlay-videos/create',
        titleEn: 'OBS overlay video details form',
        titleAr: 'نموذج تفاصيل فيديو OBS Overlay',
        descEn: 'Metadata/status form for handling overlay video lifecycle.',
        descAr: 'نموذج بيانات وحالة الفيديو للتحكم في دورة حياة فيديوهات البث.',
    },
    {
        key: 'protected-dashboard-route',
        url: '/dashboard',
        titleEn: 'Protected app dashboard route',
        titleAr: 'مسار لوحة التطبيق المحمي',
        descEn: 'Authenticated web dashboard route outside Filament panel.',
        descAr: 'صفحة لوحة التطبيق المحمية خارج لوحة Filament الإدارية.',
    },
    {
        key: 'public-obs-overlay',
        url: '/obs-overlay',
        titleEn: 'Public OBS overlay route',
        titleAr: 'مسار OBS Overlay العام',
        descEn: 'Public browser-source overlay page for studio integrations.',
        descAr: 'صفحة أوفرلاي عامة مخصصة لمصدر المتصفح في أنظمة البث.',
    },
];

const main = async () => {
    console.log('\nDashboard Audit Capture');
    console.log(`  Base URL:   ${BASE_URL}`);
    console.log(`  Output dir: ${ROOT_DIR}\n`);

    const ready = await waitForServer(BASE_URL);
    if (!ready) {
        console.error('Server is unreachable. Start Laravel app and retry.');
        process.exit(1);
    }

    await mkdir(SHOTS_DIR, { recursive: true });

    const browser = await chromium.launch({ args: ['--no-sandbox', '--disable-setuid-sandbox'] });
    const context = await browser.newContext({ viewport: { width: 1920, height: 1080 } });
    const page = await context.newPage();

    const manifest = {
        generatedAt: new Date().toISOString(),
        baseUrl: BASE_URL,
        outputDir: ROOT_DIR,
        screenshotsDir: SHOTS_DIR,
        adminEmailUsed: ADMIN_EMAIL,
        scenes: [],
    };

    let success = 0;
    let failed = 0;

    try {
        await loginToAdmin(page);

        for (let i = 0; i < scenes.length; i += 1) {
            const scene = scenes[i];
            const order = String(i + 1).padStart(2, '0');
            const filename = `${order}-${slugify(scene.key)}.png`;
            const filePath = path.join(SHOTS_DIR, filename);

            try {
                await page.goto(`${BASE_URL}${scene.url}`, { waitUntil: 'networkidle', timeout: 40000 });
                await wait(900);

                if (scene.action) {
                    await scene.action(page);
                }

                await page.screenshot({ path: filePath, fullPage: true });

                manifest.scenes.push({
                    order: i + 1,
                    key: scene.key,
                    filename,
                    path: filePath,
                    url: `${BASE_URL}${scene.url}`,
                    titleEn: scene.titleEn,
                    titleAr: scene.titleAr,
                    descEn: scene.descEn,
                    descAr: scene.descAr,
                    status: 'captured',
                });

                success += 1;
                console.log(`  [ok] ${filename} <- ${scene.titleEn}`);
            } catch (error) {
                failed += 1;
                manifest.scenes.push({
                    order: i + 1,
                    key: scene.key,
                    filename,
                    path: filePath,
                    url: `${BASE_URL}${scene.url}`,
                    titleEn: scene.titleEn,
                    titleAr: scene.titleAr,
                    descEn: scene.descEn,
                    descAr: scene.descAr,
                    status: 'failed',
                    error: String(error?.message || error),
                });

                console.error(`  [FAIL] ${scene.titleEn}: ${error?.message || error}`);
            }
        }
    } finally {
        await browser.close();
    }

    await writeFile(MANIFEST_PATH, JSON.stringify(manifest, null, 2), 'utf8');

    console.log(`\nCaptured: ${success} success, ${failed} failed`);
    console.log(`Manifest: ${MANIFEST_PATH}`);

    if (failed > 0) {
        process.exitCode = 1;
    }
};

main().catch((error) => {
    console.error('Fatal error:', error);
    process.exit(1);
});
