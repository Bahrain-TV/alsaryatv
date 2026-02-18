# OBS Animation Sequence - Visual Timeline

## Full Animation Flow (14.5 seconds)

```
┌─────────────────────────────────────────────────────────────────┐
│                 ENHANCED OBS DISPLAY ANIMATION                  │
│                      Total: 14.5 seconds                        │
└─────────────────────────────────────────────────────────────────┘

TIME    PHASE                   VISUAL                    DURATION
═════════════════════════════════════════════════════════════════════

0.0s    ┌─ PHASE 1A: Individual Sponsor Cards  ──────────────────┐
        │                                                          │
        │   [  تلفزيون البحرين  ]                                │
        │   [   تلفزيون البحرين LOGO   ]                        │
        │                                                          │
        │   → Slides in from LEFT with elastic bounce            │
        └─────────────────────────────────────────────────────────┘
                                                          1.5s

1.5s    ┌─ TRANSITION: Fade out Sponsor 1  ─────────────────────┐
        │                                                          │
        │   [Scale down and fade out]                             │
        │                                                          │
        └─────────────────────────────────────────────────────────┘
                                                          0.8s

2.3s    ┌─ PHASE 1B: Individual Sponsor 2  ──────────────────────┐
        │                                                          │
        │            [  Bapco Energies  ]                            │
        │         [  BAPCO ENERGIES LOGO  ]                          │
        │                                                          │
        │   → Slides in from RIGHT with elastic bounce           │
        └─────────────────────────────────────────────────────────┘
                                                          1.2s

3.5s    ┌─ TRANSITION: Fade out Sponsor 2  ─────────────────────┐
        │                                                          │
        │   [Scale down and fade out]                             │
        │                                                          │
        └─────────────────────────────────────────────────────────┘
                                                          0.8s

3.1s    ┌─ PHASE 1C: Combined Sponsor Display  ────────────────┐
        │                                                        │
        │              برعاية                                   │
        │  [BTV LOGO]              [BAPCO LOGO]                │
        │                                                        │
        │   Both logos appear side-by-side                      │
        │   "برعاية" text fades in first                        │
        │   Logos stagger in (300ms apart)                      │
        └────────────────────────────────────────────────────────┘
                                                        3.4s

4.5s    ┌─ TRANSITION: Sponsors fade out  ───────────────────────┐
        │                                                          │
        │   [All elements fade to background]                     │
        │   [Sponsors phase opacity → 0]                          │
        │                                                          │
        └─────────────────────────────────────────────────────────┘
                                                          0.8s

5.5s    ┌─ PHASE 2: Magical Transition  ──────────────────────────┐
        │                                                          │
        │              ◆   ◇                                      │
        │            ◇   ●   ◇                                   │
        │          ◇   ●   ✨   ●   ◇                           │
        │          ◇   ●   ✨   ●   ◇                           │
        │            ◇   ●   ◇                                   │
        │              ◆   ◇                                      │
        │                                                          │
        │   Inner circle expands outward                         │
        │   Outer circle expands (staggered)                     │
        │   3 magic rings expand sequentially                    │
        │   20 sparkles burst in all directions                  │
        └─────────────────────────────────────────────────────────┘
                                                          3.0s

8.5s    ┌─ PHASE 3: Show Logo Reveal  ────────────────────────────┐
        │                                                          │
        │                  ✨ ◆ ✨                               │
        │                 ◆   ◆   ◆                              │
        │                ◆  LOGO  ◆                              │
        │                ◆   ◆   ◆                               │
        │                  ✨ ◆ ✨                               │
        │                                                          │
        │            برنامج السارية                              │
        │         مسابقة رمضانية حصرية                            │
        │                                                          │
        │   Logo rotates into view (Y-axis)                      │
        │   Glow pulses continuously                             │
        │   Title text fades in                                  │
        │   Subtitle text fades in (staggered)                   │
        └─────────────────────────────────────────────────────────┘
                                                          2.0s

10.5s   ┌─ HOLD: Logo Display  ──────────────────────────────────┐
        │                                                          │
        │              برنامج السارية                            │
        │            (Logo with glow pulse)                       │
        │         مسابقة رمضانية حصرية                            │
        │                                                          │
        │   All elements remain visible and pulsing              │
        └─────────────────────────────────────────────────────────┘
                                                          1.5s

12.0s   ┌─ PHASE 4: Fade to Oblivion  ────────────────────────────┐
        │                                                          │
        │              برنامج السارية                            │
        │            (Darkening vignette)                        │
        │         مسابقة رمضانية حصرية                            │
        │                                                          │
        │      Dark circular vignette closes in                  │
        │      Black overlay spreads from edges                  │
        │      All elements fade and scale down                  │
        └─────────────────────────────────────────────────────────┘
                                                          2.0s

14.0s   ┌─ COMPLETE FADE  ───────────────────────────────────────┐
        │                                                          │
        │                    [ALL BLACK]                          │
        │                                                          │
        │                                                          │
        └─────────────────────────────────────────────────────────┘
                                                          0.5s

14.5s   ┌─ REDIRECT  ────────────────────────────────────────────┐
        │                                                          │
        │        → Automatic redirect to / (Home Page)           │
        │        → Registration page loads                        │
        │                                                          │
        └─────────────────────────────────────────────────────────┘
```

---

## Animation Intensity Timeline

```
OPACITY
100% ├──┐
     │  │        ┌─────────┐
 80% ├──┤        │ Combined│      ┌────Logo Phase───┐
     │  │ Card 1 │ Sponsor ├─────┤                 │
 60% ├──┤   │    │ Display │     │  ┌──Logo Init──┐ │
     │  │   │    │         │     │  │   1.0s      │ │
 40% ├──┤   │    │         │     │  │             │ │
     │  │   │    │  Card2  │     │  │   Title     │ │
 20% ├──┤   ├───┤   │      │     │  │   Subtitle  │ │
     │  │   │   │   │      │     │  │             │ │
  0% └──┴───┴───┴───┴──────┴─────┴──┴─────────────┘─┘
      0   2   4   6    8   10   12   14
                    TIME (seconds)

SCALE & POSITION
     100% ├────Cards────┐
          │ Scale 0→1   │ Scale 1→0
          │             │    │
          │             │    │   ┌──Logo──┐
      50% ├─ L↙  R↗    ─┼────┤   │ Rotate │
          │             │    │   │ Y:180→0│
          │             │    │   │        │
       0% └─────────────┴────┴───┴────────┘
          0   2   4   6   8  10   12   14
                     TIME (seconds)
```

---

## Stage-by-Stage Breakdown

### Stage 1: Sponsor Card 1 (0-1.5s)
```
START                              PEAK                            END
0.0s                               0.6s                            1.5s

[1]  Card slides from left
     Opacity: 0% → 100%
     Scale: 80% → 100%
     TranslateX: -100px → 0px

     Title visible
     Logo visible
     Full-screen centered display
```

### Stage 2: Sponsor Card 2 (2.3-3.5s)
```
START                              PEAK                            END
2.3s                               2.9s                            3.5s

[2]  Card slides from right
     Opacity: 0% → 100%
     Scale: 80% → 100%
     TranslateX: +100px → 0px

     Different sponsor shown
     Title visible
     Logo visible
```

### Stage 3: Combined Display (3.1-4.5s)
```
START          LOGO1IN      LOGO2IN      HOLD      FADE OUT      END
3.1s           3.2s         3.5s         4.2s      4.5s          4.5s

[برعاية] ──→ [برعاية]      [برعاية]     [برعاية]  ─→ [   ]      ✓
              [L1]      [L1] [L2]        [L1][L2]      [Fade]

Title in:     Logo 1 in:    Logo 2 in:    Hold visible   Fade out
0.4s delay    0.2s scale    0.3s scale    0.7s hold      0.8s fade
              cubic-bezier  cubic-bezier  Both logos     Complete
              elastic       elastic       side-by-side   transition
```

### Stage 4: Magic Transition (5.5-8.5s)
```
START                         PEAK                           END
5.5s                          6.5s                           8.5s

[Sponsors fade]
                    ◆   ◇
                  ◇   ●   ◇
    (Expanding)◇   ●   ✨   ●   ◇(Expanding)
                ◇   ●   ✨   ●   ◇
                  ◇   ●   ◇
                    ◆   ◇

Inner circle:     1.2s expand + 0.2s delay
Outer circle:     1.5s expand + 0.2s delay
Ring 1:           1.0s expand
Ring 2:           1.3s expand + 0.15s delay
Ring 3:           1.6s expand + 0.3s delay
Sparkles:         20x bursts, staggered 50ms each
```

### Stage 5: Logo Reveal (8.5-10.5s)
```
START                         PEAK                           END
8.5s                          9.5s                           10.5s

[Dark background]
        ✨ ◆ ✨
       ◆   ◆   ◆
      ◆  LOGO  ◆     (Rotating Y-axis: 180° → 0°)
       ◆   ◆   ◆
        ✨ ◆ ✨

    برنامج السارية
 مسابقة رمضانية حصرية

Logo glow:        opacity 0% → 100%, then pulse
Logo image:       rotateY 180° → 0°, scale 50% → 100%
Title:            opacity 0% → 100% at 1.0s
Subtitle:         opacity 0% → 100% at 1.2s
All elements:     Held visible for entire duration
```

### Stage 6: Oblivion Fade (12-14s)
```
START                         PEAK                           END
12.0s                         13.0s                          14.0s

[Visible logo & text]
    (Vignette darkens from edges)
    (Oblivion overlay spreads)
    (All elements fade & scale)

[Darkening...]
[Dark...]
[Complete darkness]

Vignette:         1.5s fade-in
Oblivion overlay: 2.0s fade-in (0.5s delayed)
Logo phase:       1.5s fade-out (1.0s delayed)
Result:           Full black screen by 14s
```

---

## Key Animation Properties

### Acceleration/Deceleration
- **Individual Cards**: `cubic-bezier(0.34, 1.56, 0.64, 1)` - Elastic bounce effect
- **Logo Reveal**: `cubic-bezier(0.34, 1.56, 0.64, 1)` - Bouncy entrance
- **Sponsor Logos**: `cubic-bezier(0.34, 1.56, 0.64, 1)` - Bouncy scale
- **Fades**: `ease-out` and `ease-in` - Natural feel
- **Oblivion**: `ease-in` - Accelerating darkness

### Movement Patterns
- **Horizontal**: Cards slide from edges (±100px)
- **Vertical**: Text/logos translate small amounts (15-30px)
- **Rotation**: Logo rotates 180° on Y-axis
- **Scaling**: Cards and elements scale from 0.8 → 1.0
- **Radial**: Magic circles/rings expand from center

---

## Responsive Behavior

### Desktop (> 768px)
- Individual card logo: **200px** (large focal point)
- Combined logo size: **140px** each
- Gap between combined: **4rem**
- Smooth animations on high-performance devices

### Mobile (< 768px)
- Individual card logo: **150px** (scaled down)
- Combined logo size: **100px** each
- Gap between combined: **2rem**
- Animations optimized for mobile performance
- Touch-friendly layout

---

## User Controls

### Skip Animation
- **ESC Key**: Jump to home page
- **Left Click**: Jump to home page
- **Tab/Focus**: Jump to home page

### Auto-Redirect
- After 14.5 seconds: Automatically redirect to `/` (registration page)

---

## Browser Performance

| Browser | Animation Smoothness | GPU Acceleration |
|---------|---------------------|------------------|
| Chrome  | ✅ Excellent        | Full support     |
| Firefox | ✅ Excellent        | Full support     |
| Safari  | ✅ Excellent        | Full support     |
| Edge    | ✅ Excellent        | Full support     |
| Mobile  | ✅ Good            | Optimized        |

---

## Summary Statistics

| Metric | Value |
|--------|-------|
| **Total Duration** | 14.5 seconds |
| **Phase 1: Sponsors** | 6.0 seconds |
| **Phase 2: Magic** | 3.0 seconds |
| **Phase 3: Logo** | 2.0 seconds |
| **Phase 4: Oblivion** | 2.0 seconds |
| **Redirect Delay** | 0.5 seconds |
| **Individual Cards** | 2 (Sponsor 1 & 2) |
| **Combined Display** | 1 (Both together) |
| **Particle Effects** | 20 sparkles |
| **Background Particles** | 40 (desktop) / 20 (mobile) |
| **Animation Keyframes** | 12+ unique animations |

