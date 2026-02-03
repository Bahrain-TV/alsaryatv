# Splash Screen Testing & Deployment Guide

## Quick Start

### Access the Splash Screen
```bash
# Local development
http://localhost:8001/splash

# Production
https://alsarya.tv/splash
```

## Testing Checklist

### Visual Elements
- [ ] Logo displays centered
- [ ] Logo bounces smoothly (3-second cycle)
- [ ] Logo has glow effect pulsing
- [ ] Background gradient animates
- [ ] Floating orbs visible and animated
- [ ] Stars twinkle in background
- [ ] Title text gradient visible
- [ ] Subtitle and description readable

### Animations
- [ ] Logo bounce animation smooth
- [ ] Background gradient shifts colors
- [ ] Orbs float continuously
- [ ] Progress bar fills smoothly (0 → 100%)
- [ ] Loading text animates with dots
- [ ] All animations run at 60 FPS

### Interactive Features
- [ ] Auto-redirect works after 4 seconds
- [ ] Click anywhere skips to home
- [ ] Press ESC key skips to home
- [ ] Page title updates correctly

### Responsive Testing
- [ ] Desktop (1920x1080) - full animations
- [ ] Tablet (768x1024) - adjusted layout
- [ ] Mobile (375x667) - optimized for small screens
- [ ] Mobile (414x896) - larger phones

### Accessibility
- [ ] Logo has alt text
- [ ] Semantic HTML structure
- [ ] Keyboard navigation works (ESC)
- [ ] Text has sufficient contrast
- [ ] Respects prefers-reduced-motion

### Browser Compatibility
- [ ] Chrome/Chromium (latest)
- [ ] Firefox (latest)
- [ ] Safari (latest)
- [ ] Edge (latest)

### Performance
- [ ] Page loads in < 1 second
- [ ] Animations remain smooth
- [ ] No memory leaks (check DevTools)
- [ ] Smooth redirect to home

## Performance Metrics

### Ideal Targets
| Metric | Target | Actual |
|--------|--------|--------|
| Page Load | < 1s | - |
| FCP (First Contentful Paint) | < 0.5s | - |
| LCP (Largest Contentful Paint) | < 1s | - |
| CLS (Cumulative Layout Shift) | < 0.1 | - |
| FPS | 60 FPS | - |

### Test with DevTools
```javascript
// Chrome DevTools Console
performance.mark('splash-start');
// ... page loads and animates ...
performance.mark('splash-end');
performance.measure('splash', 'splash-start', 'splash-end');
performance.getEntriesByName('splash')[0].duration;
```

## Customization Before Deployment

### 1. Change Logo
Edit `resources/views/splash.blade.php`:
```html
<img src="{{ asset('images/btv-logo-ar.png') }}" alt="BTV Logo">
```
Change path to your logo.

### 2. Change Redirect URL
Edit JavaScript section:
```javascript
window.location.href = '/';
```
Change `/` to desired route.

### 3. Change Animation Duration
Edit CSS:
```css
animation: bounce 3s cubic-bezier(0.68, -0.55, 0.265, 1.55) infinite;
```
Change `3s` to desired duration.

### 4. Change Colors
Edit gradient at top of CSS:
```css
background: linear-gradient(135deg, #0f172a 0%, ...);
```

### 5. Change Text
Edit HTML content:
```html
<h1 class="splash-title">Your Title Here</h1>
```

## Deployment Strategy

### Option 1: Standalone Route
Keep splash screen as separate route at `/splash` for:
- Testing/preview purposes
- Marketing landing pages
- Social media links

### Option 2: First-Visit Experience
Show splash on first visit only:

**Edit `routes/web.php`:**
```php
Route::get('/', function () {
    if (!session()->has('splash_shown')) {
        session(['splash_shown' => true]);
        return view('splash');
    }
    return view('welcome');
})->name('home');
```

### Option 3: Mobile-Only Display
Show only on mobile devices:

**Add to `splash.blade.php` JavaScript:**
```javascript
// Redirect immediately on desktop
if (window.innerWidth > 768) {
    window.location.href = '/';
}
```

### Option 4: First-Time Users
Show splash only to new visitors using localStorage:

**Add to `splash.blade.php` JavaScript:**
```javascript
// Check if first visit
if (localStorage.getItem('visited')) {
    window.location.href = '/';
} else {
    localStorage.setItem('visited', 'true');
    // Proceed with splash display
}
```

## Pre-Deployment Checklist

- [ ] Splash screen displays at `/splash` route
- [ ] Logo image file exists at `public/images/btv-logo-ar.png`
- [ ] All animations run smoothly (no jank)
- [ ] Page loads quickly (< 1 second)
- [ ] Auto-redirect works after 4 seconds
- [ ] Keyboard shortcuts work (ESC, click)
- [ ] Mobile responsive tested
- [ ] All browsers tested
- [ ] Accessibility verified
- [ ] No console errors
- [ ] Asset URLs correct (no 404s)
- [ ] Cache cleared: `php artisan optimize:clear`

## Production Deployment

### Step 1: Clear Cache
```bash
php artisan optimize:clear
```

### Step 2: Verify Logo Exists
```bash
ls -la public/images/btv-logo-ar.png
```

### Step 3: Test Locally
```bash
php artisan serve
# Visit http://localhost:8000/splash
```

### Step 4: Deploy Code
```bash
./deploy.sh
```

### Step 5: Verify in Production
```bash
curl -I https://alsarya.tv/splash
# Check for 200 status code
```

### Step 6: Monitor Performance
```bash
# Check logs
tail -f storage/logs/laravel.log
```

## Troubleshooting

### Issue: Logo Not Loading
**Solution:**
```bash
# Verify file exists
ls -la public/images/btv-logo-ar.png

# Check permissions
chmod 644 public/images/btv-logo-ar.png

# Clear cache
php artisan optimize:clear
```

### Issue: Animations Not Smooth
**Solution:**
```javascript
// Check browser console for performance issues
// Try disabling other scripts
// Check CPU/GPU usage

// Force hardware acceleration
document.body.style.transform = 'translateZ(0)';
```

### Issue: Page Not Redirecting
**Solution:**
```javascript
// Check browser console
console.log('Redirect test:', window.location);

// Verify target URL exists
// Check Content Security Policy headers
```

### Issue: Responsive Layout Broken
**Solution:**
```bash
# Clear CSS cache
php artisan view:clear

# Hard refresh browser (Cmd+Shift+R on Mac)
```

### Issue: Text Appearing Garbled (RTL)
**Solution:**
```bash
# Verify Tailwind RTL support
# Check HTML dir="rtl" attribute
# Clear Tailwind cache
npm run build
```

## Analytics Integration

### Track Splash Views
```php
// Add to splash.blade.php JavaScript
gtag('event', 'splash_viewed', {
    'timestamp': new Date().toISOString()
});
```

### Track Completion
```php
gtag('event', 'splash_completed', {
    'duration_ms': 4000
});
```

### Track Skip Events
```php
document.addEventListener('click', () => {
    gtag('event', 'splash_skipped', {
        'method': 'click'
    });
});

document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        gtag('event', 'splash_skipped', {
            'method': 'keyboard'
        });
    }
});
```

## Performance Optimization

### Current Optimization
- ✅ Inline CSS (no external files)
- ✅ Inline JavaScript (no external files)
- ✅ GPU-accelerated animations
- ✅ Lazy-loaded Lottie animations
- ✅ Responsive image sizing
- ✅ Minimal dependencies

### Further Optimization Options
1. Add WebP format for logo
2. Add AVIF format for logo
3. Compress SVG elements
4. Minify inline CSS/JS
5. Add Service Worker caching

## Rollback Plan

If splash screen causes issues:

### Quick Rollback
```bash
# Option 1: Remove splash route
# Edit routes/web.php and remove splash route

# Option 2: Disable splash redirect
# Comment out first-visit logic

# Option 3: Revert file
git checkout HEAD -- resources/views/splash.blade.php
git checkout HEAD -- routes/web.php
```

## Monitoring

### Key Metrics to Watch
- Page load time
- Redirect completion rate
- Skip rate (before redirect)
- Browser console errors
- Server error logs
- User device types

### Commands to Monitor
```bash
# View recent logs
tail -f storage/logs/laravel.log

# Count splash requests
grep 'splash' storage/logs/laravel.log | wc -l

# Check for errors
grep ERROR storage/logs/laravel.log
```

## Success Criteria

✅ **Technical**
- Splash loads in < 1 second
- Auto-redirect works reliably
- No console errors
- All animations smooth

✅ **User Experience**
- Professional appearance
- Clear loading indication
- Smooth animations
- Intuitive interaction (ESC, click)

✅ **Business**
- Improved brand perception
- Engaging first impression
- Memorable experience
- Ready for content/updates

## Next Steps

1. **Test locally** - Verify all functionality
2. **Deploy to staging** - Test in staging environment
3. **Monitor performance** - Track metrics
4. **Deploy to production** - Roll out to users
5. **Gather feedback** - Monitor user reactions
6. **Iterate** - Make adjustments based on feedback

## Support

For issues or customization needs:
1. Check troubleshooting section above
2. Review SPLASH_SCREEN_GUIDE.md
3. Check browser console for errors
4. Monitor server logs
5. Test in incognito/private mode

---

**Status**: ✅ Ready for deployment  
**Last Updated**: 2024  
**Version**: 1.0.0  
