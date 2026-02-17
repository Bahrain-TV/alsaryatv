# OBS Animation Sequence - Enhanced Display Guide

## Overview
The splash screen animation has been enhanced to display each sponsor card individually, then bring them together in a combined display, with a total animation duration of **14.5 seconds**.

---

## Animation Timeline Breakdown

### Phase 1: Individual & Combined Sponsor Display (0-6 seconds)
**Duration: 6 seconds**

#### Stage 1a: Sponsor 1 Card (0-1.5 seconds)
- **Animation**: `cardSlideInFromLeft` (1.2 seconds)
- **Effect**: Sponsor 1 slides in from the left with the title and logo
- **Element**: `#sponsorCard1`
- **Easing**: `cubic-bezier(0.34, 1.56, 0.64, 1)` (elastic bounce)

#### Stage 1b: Sponsor 2 Card (1.5-3 seconds)
- **Transition**: Sponsor 1 fades out using `cardSlideOut` (0.8 seconds)
- **Animation**: `cardSlideInFromRight` (1.2 seconds)
- **Effect**: Sponsor 2 slides in from the right with its title and logo
- **Element**: `#sponsorCard2`
- **Easing**: `cubic-bezier(0.34, 1.56, 0.64, 1)` (elastic bounce)

#### Stage 1c: Combined Display (3-6 seconds)
- **Transition**: Sponsor 2 fades out (0.8 seconds)
- **Combined Display Start**: 3.1 seconds
- **"برعاية" Text**: Fades in with `sponsorFadeIn` animation
- **Logo 1**: Enters with `sponsorLogoIn` at 3.2s
- **Logo 2**: Enters with `sponsorLogoIn` at 3.5s (staggered 300ms)
- **Hold Duration**: Both logos visible from 4.2s to 4.5s
- **Fade Out**: Both sponsors fade out at 4.5s (0.8 seconds)

---

### Phase 2: Magical Transition (5.5-8.5 seconds)
**Duration: 3 seconds**

- **Magic Circle Inner**: Expands with `magicCircleExpand` (1.2s)
- **Magic Circle Outer**: Expands with staggered timing (1.5s + 0.2s delay)
- **Magic Rings**: 3 rings expand sequentially (1s, 1.3s, 1.6s)
- **Sparkles**: 20 particle sparkles burst outward (0.6s each, staggered)
- **Fade Out**: Sponsors phase fades completely
- **Magic Effect**: Creates the transition from sponsors to show logo

---

### Phase 3: Show Logo Reveal (8.5-10.5 seconds)
**Duration: 2 seconds**

- **Logo Glow**: Starts at 0.2s (radial glow effect)
- **Logo Image**: Rotates Y-axis 180° → 0° while scaling up, with `logoReveal` animation
- **Title Text**: "برنامج السارية" fades in at 1.0s with `textFadeIn`
- **Subtitle Text**: "مسابقة رمضانية حصرية" fades in at 1.2s with `textFadeIn`
- **Glow Pulse**: Continuous subtle pulse animation

---

### Phase 4: Fade to Oblivion (12-14 seconds)
**Duration: 2 seconds**

- **Vignette Effect**: Inset box shadow fades in with `fadeToBlack` (1.5s)
- **Oblivion Overlay**: Radial gradient darkening (2s + 0.5s delay)
- **Show Logo Phase**: Scales down and fades with `finalFade` (1.5s + 1s delay)
- **Final State**: Everything fades to black

---

### Page Redirect (14.5 seconds)
After the complete animation sequence, the page redirects to `/` (registration page).

---

## Animation Details

### Individual Card Animation Properties

#### `cardSlideInFromLeft`
```css
0% { opacity: 0; transform: translateX(-100px) scale(0.8); }
50% { opacity: 1; }
100% { opacity: 1; transform: translateX(0) scale(1); }
```
- Slides from left with scale-up effect
- Used for Sponsor 1 card entrance

#### `cardSlideInFromRight`
```css
0% { opacity: 0; transform: translateX(100px) scale(0.8); }
50% { opacity: 1; }
100% { opacity: 1; transform: translateX(0) scale(1); }
```
- Slides from right with scale-up effect
- Used for Sponsor 2 card entrance

#### `cardSlideOut`
```css
0% { opacity: 1; transform: scale(1); }
100% { opacity: 0; transform: scale(0.8); }
```
- Shrinks and fades out before next card appears
- Used for individual card exit transitions

---

## Card Structure

### Individual Sponsor Card
```html
<div class="sponsor-card" id="sponsorCard1">
    <div class="sponsor-card-content">
        <div class="sponsor-card-title">تلفزيون البحرين</div>
        <img src="..." class="sponsor-card-logo">
    </div>
</div>
```

**Features:**
- Full-screen card display (position: absolute, width/height: 100%)
- Centered content with flexbox
- Sponsor name (title) above logo
- Larger logo size (200px) for individual display
- Drop shadow for depth

### Combined Display
```html
<div class="sponsors-logos" id="sponsorsLogos">
    <img src="..." class="sponsor-logo" id="sponsor1">
    <img src="..." class="sponsor-logo" id="sponsor2">
</div>
```

**Features:**
- Horizontal layout with 4rem gap
- Smaller logos (140px) compared to individual cards
- Both sponsors visible side-by-side
- Staggered entrance animations

---

## Styling Classes

| Class | Purpose |
|-------|---------|
| `.sponsor-card` | Individual sponsor card container |
| `.sponsor-card-content` | Content wrapper for card (centered) |
| `.sponsor-card-title` | Sponsor name text |
| `.sponsor-card-logo` | Logo in individual card |
| `.sponsors-logos` | Combined sponsor display container |
| `.sponsor-logo` | Individual logos in combined display |
| `.sponsor-logo.active` | Active state for combined logos |

---

## Responsive Behavior

### Desktop (> 768px)
- Individual Card Logo: 200px
- Individual Card Title: `clamp(1rem, 2.5vw, 1.5rem)`
- Combined Logos: 140px each
- Gap between logos: 4rem

### Mobile (< 768px)
- Individual Card Logo: 150px (reduced for mobile)
- Individual Card Title: `clamp(0.8rem, 2vw, 1.2rem)` (smaller)
- Combined Logos: 100px each
- Gap adjusts: 2rem

---

## Animation Timing Summary

```
0.0s ├─ Sponsor Card 1 slides in (1.2s)
1.5s ├─ Sponsor Card 1 fades out (0.8s)
1.5s ├─ Sponsor Card 2 slides in (1.2s)
3.0s ├─ Sponsor Card 2 fades out (0.8s)
3.1s ├─ Combined Display shows
3.2s ├─ Logo 1 animates in (1s)
3.5s ├─ Logo 2 animates in (1s, staggered)
4.5s ├─ Sponsors fade out (0.8s)
5.5s ├─ Magic Transition begins (3s)
8.5s ├─ Show Logo Reveal begins (2s)
12.0s├─ Fade to Oblivion begins (2s)
14.5s└─ Redirect to registration page
```

---

## Easing Functions Used

| Function | Purpose | Applied To |
|----------|---------|-----------|
| `ease-out` | Smooth deceleration | Fade animations |
| `ease-in` | Smooth acceleration | Oblivion fade |
| `cubic-bezier(0.34, 1.56, 0.64, 1)` | Elastic bounce | Card entrances, logo reveal |
| `ease-in-out` | Smooth both sides | Magic circles |
| `sine.inOut` | Subtle wave effect | Wave paths |

---

## Testing the Animation

### Local Testing
```bash
# Start the development server
php artisan serve

# Navigate to splash screen
# http://localhost:8000/splash
```

### OBS Integration
1. Add Browser Source in OBS
2. Set URL to: `http://your-domain.com/splash`
3. Set Custom CSS to hide browser controls:
```css
body { margin: 0; overflow: hidden; }
```
4. Disable right-click context menu for clean display
5. Animation will auto-redirect to registration page after 14.5 seconds

### Keyboard/Click Skip
- **ESC Key**: Skip animation and go to registration
- **Left Click**: Skip animation and go to registration
- **Tab/Focus Change**: Skip animation and go to registration

---

## Customization Guide

### Adjust Individual Card Display Time
Edit line in `animateSplash()`:
```javascript
// Change 1500 to your desired milliseconds for card 1 display time
setTimeout(() => {
    sponsorCard1.style.animation = 'cardSlideOut 0.8s ease-in forwards';
}, 1500);  // Change this value
```

### Adjust Combined Display Time
Edit the fade-out timeout:
```javascript
// Change 4500 to adjust when combined display fades out
setTimeout(() => {
    sponsoredBy.style.transition = 'opacity 0.8s ease-out';
    sponsoredBy.style.opacity = '0';
}, 4500);  // Change this value
```

### Change Logo Sizes
Edit CSS variables:
```css
.sponsor-card-logo {
    width: 200px;  /* Change for individual cards */
}

.sponsor-logo {
    width: 140px;  /* Change for combined display */
}
```

### Adjust Total Animation Duration
Edit the final redirect timing:
```javascript
// Change 14500 to your desired total duration in milliseconds
setTimeout(() => {
    window.location.href = '/';
}, 14500);
```

---

## Performance Notes

- **Total Duration**: 14.5 seconds
- **Particle Effects**: 20 sparkles during magic transition
- **Background Particles**: 40 on desktop, 20 on mobile
- **Animations**: GPU-accelerated using `transform` and `opacity`
- **No Layout Thrashing**: All animations use GPU-efficient properties

---

## Browser Support

- ✅ Chrome/Chromium (90+)
- ✅ Firefox (88+)
- ✅ Safari (14+)
- ✅ Edge (90+)
- ✅ Mobile browsers (iOS Safari, Chrome Mobile)

**Note**: Prefers `reduce-motion` media query is respected for accessibility.

---

## Related Files

- **View**: `/resources/views/splash.blade.php`
- **Styles**: Inline CSS in splash.blade.php
- **Legacy JS**: `/resources/js/splash-screen.js` (GSAP version, not used)
- **Route**: `/routes/web.php` (splash route)

---

## Future Enhancements

- [ ] Add video background instead of gradient
- [ ] Include animated sponsor logos
- [ ] Add audio/music sync
- [ ] Support for additional sponsors
- [ ] QR code integration for sponsor landing pages
- [ ] Social media integration

