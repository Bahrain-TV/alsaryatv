# OBS Animation - Testing & Deployment Guide

## Quick Start

### Test Locally
```bash
# Start Laravel development server
php artisan serve

# Open in browser
http://localhost:8000/splash

# Watch the 14.5 second animation sequence
```

### OBS Integration
1. **In OBS Studio**:
   - Add Browser Source
   - Set URL: `http://your-domain.com/splash`
   - Set width: 1920, height: 1080
   - Enable audio: OFF (no sound)

2. **Custom CSS** (to remove browser UI):
```css
body {
    margin: 0;
    overflow: hidden;
    background: #000;
}

::-webkit-scrollbar {
    display: none;
}
```

3. **Recommended OBS Settings**:
   - Refresh Browser: ON (when scene becomes active)
   - Shutdown source when not visible: ON
   - Control audio via OBS: OFF

---

## Animation Verification Checklist

### ✅ Phase 1: Individual Sponsor Cards (0-6s)
- [ ] Sponsor 1 card slides in from LEFT at 0.0s
- [ ] Sponsor 1 card title is visible
- [ ] Sponsor 1 logo is large (200px desktop)
- [ ] Sponsor 1 card fades out at 1.5s
- [ ] Sponsor 2 card slides in from RIGHT at 2.3s
- [ ] Sponsor 2 card title is visible
- [ ] Sponsor 2 logo is large (200px desktop)
- [ ] Sponsor 2 card fades out at 3.5s
- [ ] Combined display appears at 3.1s
- [ ] "برعاية" text fades in first
- [ ] BTV logo animates in (staggered)
- [ ] Bapco Energies logo animates in (staggered 300ms later)
- [ ] Both logos visible side-by-side (140px desktop)
- [ ] All sponsors fade out at 4.5s

### ✅ Phase 2: Magical Transition (5.5-8.5s)
- [ ] Sponsors completely faded by 5.5s
- [ ] Inner magic circle expands from center
- [ ] Outer magic circle expands (delayed)
- [ ] 3 magic rings expand sequentially
- [ ] 20 sparkles burst outward
- [ ] All magic effects fade by 8.5s

### ✅ Phase 3: Logo Reveal (8.5-10.5s)
- [ ] Dark background visible
- [ ] Logo glow appears at 8.7s (200ms after phase starts)
- [ ] Logo rotates into view from Y-axis 180°
- [ ] "برنامج السارية" title fades in at 9.5s
- [ ] "مسابقة رمضانية حصرية" subtitle fades in at 9.7s
- [ ] Logo glow pulses continuously
- [ ] All elements remain visible through 10.5s

### ✅ Phase 4: Oblivion Fade (12-14s)
- [ ] Vignette (dark edges) fade in starting 12.0s
- [ ] Oblivion overlay (black radial) fades in starting 12.5s
- [ ] Logo phase scales and fades starting 13.0s
- [ ] Complete black screen by 14.0s

### ✅ Redirect (14.5s)
- [ ] Page redirects to `/` after animation completes
- [ ] Registration page loads
- [ ] No error messages in console

### ✅ User Controls
- [ ] ESC key skips to registration
- [ ] Left click skips to registration
- [ ] Tab/focus change skips to registration

---

## Browser Console Checks

Open DevTools Console (F12) and verify:

```javascript
// Should show 0 errors
console.log("Animation test complete");

// Verify elements exist
console.log(document.getElementById('sponsorCard1')); // Should not be null
console.log(document.getElementById('sponsorCard2')); // Should not be null
console.log(document.getElementById('showLogo'));    // Should not be null
```

---

## Performance Metrics

### Expected Performance
- **Frame Rate**: 60 FPS (smooth on desktop, 30+ FPS on mobile)
- **GPU Usage**: Minimal (transform/opacity only)
- **Memory**: < 50MB for animation assets
- **Load Time**: < 2 seconds
- **Animation Total**: 14.5 seconds

### Measure in DevTools
1. Open DevTools → Performance
2. Click Record
3. Watch animation complete
4. Click Stop
5. Look for:
   - **Green**: Smooth frames
   - **No red**: No dropped frames
   - **FPS Graph**: Should stay flat at 60 FPS

---

## Mobile Testing

### Test on Real Devices
```bash
# Find your local IP
ifconfig | grep inet

# Access from mobile on same network
http://192.168.x.x:8000/splash
```

### Responsive Breakpoints
- **Desktop**: 1920x1080 (primary)
- **Tablet**: 1024x768
- **Mobile**: 375x667 (iPhone)
- **Mobile**: 412x915 (Android)

### Mobile Optimizations
- Logos scaled down (150px individual, 100px combined)
- Reduced particle count (20 vs 40 on desktop)
- Smoother animations on less powerful devices

---

## Common Issues & Fixes

### Issue: Animation doesn't start
**Cause**: JavaScript not loaded
**Fix**:
```javascript
// Check console for errors
// Verify window.load event fired
// Clear browser cache (Ctrl+Shift+Delete)
```

### Issue: Cards not sliding in
**Cause**: CSS animations not applied
**Fix**:
```css
/* Verify animations are in <style> tag */
@keyframes cardSlideInFromLeft { ... }
@keyframes cardSlideInFromRight { ... }
```

### Issue: Mobile layout broken
**Cause**: Viewport meta tag issue
**Fix**:
```html
<!-- Should be present in <head> -->
<meta name="viewport" content="width=device-width, initial-scale=1">
```

### Issue: Logos not appearing in OBS
**Cause**: CORS or file path issues
**Fix**:
```bash
# Verify images exist
ls -la public/images/btv-logo-ar.png
ls -la public/images/bapco-energies.svg
ls -la public/images/alsarya-logo-2026-1.png
```

### Issue: Animation freezes mid-way
**Cause**: JavaScript error or timeout
**Fix**:
```javascript
// Check console for errors
// Verify all element IDs exist
// Check for conflicting styles
```

---

## Deployment Checklist

### Pre-Deployment
- [ ] Test locally with `php artisan serve`
- [ ] Test on mobile device
- [ ] Run in OBS and verify timing
- [ ] Check console for errors
- [ ] Verify all image assets exist
- [ ] Test skip functionality (ESC, click)

### Deployment
```bash
# 1. Commit changes
git add resources/views/splash.blade.php

# 2. Deploy to production
./deploy.sh

# 3. Verify in production
curl https://your-domain.com/splash | grep "animator"
```

### Post-Deployment
- [ ] Test in production OBS source
- [ ] Verify redirect works
- [ ] Check mobile responsiveness
- [ ] Monitor for performance issues
- [ ] Collect user feedback

---

## Timing Verification Script

Run this in browser console to verify timing:

```javascript
// Timestamp each phase
const phases = {
    start: performance.now(),
    phases: []
};

// Log timestamps at each phase
function logPhase(name, expectedTime) {
    const now = performance.now() - phases.start;
    const actual = Math.round(now / 100) / 10;
    const expected = expectedTime;
    const diff = Math.abs(actual - expected);

    console.log(`${name}: ${actual}s (expected: ${expected}s, diff: ±${diff}s)`);
    phases.phases.push({ name, actual, expected, diff });
}

// Call these at each phase:
// logPhase("Sponsor 1 in", 0);
// logPhase("Sponsor 1 out", 1.5);
// logPhase("Sponsor 2 in", 2.3);
// logPhase("Sponsor 2 out", 3.5);
// logPhase("Combined in", 3.1);
// logPhase("Combined out", 4.5);
// logPhase("Magic in", 5.5);
// logPhase("Logo in", 8.5);
// logPhase("Oblivion start", 12.0);
// logPhase("Complete", 14.5);
```

---

## Network Optimization

### Assets Loaded
```
splash.blade.php          - 1 page
btv-logo-ar.png          - ~50 KB
bapco-energies.svg - ~50 KB
alsarya-logo-2026-1.png   - ~100 KB
CSS (inline)              - ~8 KB
JavaScript (inline)       - ~6 KB
────────────────────────────────
Total                     - ~214 KB
```

### Load Time
- First Contentful Paint: < 500ms
- Splash animation start: < 1s
- Full page load: < 2s

### Optimization Tips
```html
<!-- Preload critical images -->
<link rel="preload" as="image" href="{{ asset('images/btv-logo-ar.png') }}">
<link rel="preload" as="image" href="{{ asset('images/alsarya-logo-2026-1.png') }}">
```

---

## Accessibility Notes

### Respects Preferences
```css
@media (prefers-reduced-motion: reduce) {
    *, *::before, *::after {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
    }
}
```

### Screen Reader Support
- [ ] Alt text present on all images
- [ ] Semantic HTML used
- [ ] Redirect navigable for keyboard users

---

## Support & Troubleshooting

### Documentation Files
- `OBS_ANIMATION_GUIDE.md` - Detailed animation breakdown
- `ANIMATION_SEQUENCE_VISUAL.md` - Visual timeline
- `ANIMATION_TESTING.md` - This file
- `resources/views/splash.blade.php` - Source code

### Getting Help
```bash
# Check logs
tail -f storage/logs/laravel.log

# Test specific routes
curl http://localhost:8000/splash -v

# Clear cache if having issues
php artisan cache:clear
php artisan view:clear
```

