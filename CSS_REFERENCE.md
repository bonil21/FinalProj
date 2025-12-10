# GreenBites Homepage - CSS & Customization Reference

## 🎨 Color Variables Quick Reference

### Primary Brand Colors
```css
/* Green Palette */
#2F5133  /* Deep Green - Primary, text, headers */
#3B6B4A  /* Medium Green - Buttons, secondary elements */
#6BB97F  /* Bright Green - Accents, hovers, highlights */
#1a3320  /* Very Dark Green - Footer background */

/* Orange/Warm Palette */
#FFA62B  /* Warm Orange - Primary CTAs, attention-getters */
#FFB84D  /* Soft Orange - Hover states, secondary CTAs */
#FF6B6B  /* Red-Orange - Sale badges, alerts */

/* Neutral Palette */
#FFFFFF  /* Pure White - Cards, text backgrounds */
#F6E6D7  /* Warm Beige - Primary page background */
#E7F0DC  /* Light Green - Card backgrounds, subtle areas */
#E4CFBF  /* Tan - Borders */
#F5FAF7  /* Very Light - Section backgrounds */
#FFFDF9  /* Off-white - Input backgrounds */

/* Text Colors */
#1f2f24  /* Very Dark - Primary text */
#2F5133  /* Deep Green - Headings (same as primary)*/
#5f6b74  /* Medium Gray - Body text */
#6b7280  /* Neutral Gray - Secondary text */
#9CA3AF  /* Light Gray - Placeholders */

/* Transparency Values */
rgba(0,0,0,0.1)    /* Light shadow */
rgba(0,0,0,0.08)   /* Very light shadow */
rgba(0,0,0,0.15)   /* Medium shadow */
rgba(107,185,127,0.3)   /* Green with transparency */
rgba(255,166,43,0.3)    /* Orange with transparency */
rgba(255,255,255,0.1)   /* White overlay */
```

---

## 📐 Sizing Scale Reference

### Font Sizes (relative to 16px base)
```css
.text-xs      { font-size: 0.75rem; }    /* 12px */
.text-sm      { font-size: 0.85rem; }    /* 13.6px */
.text-base    { font-size: 0.95rem; }    /* 15.2px */
.text-lg      { font-size: 1.1rem; }     /* 17.6px */
.text-xl      { font-size: 1.2rem; }     /* 19.2px */
.text-2xl     { font-size: 1.3rem; }     /* 20.8px */
.text-3xl     { font-size: 1.5rem; }     /* 24px */
.text-4xl     { font-size: 2rem; }       /* 32px */
.text-5xl     { font-size: 2.2rem; }     /* 35.2px */
.text-6xl     { font-size: 2.5rem; }     /* 40px */
.text-7xl     { font-size: 3.5rem; }     /* 56px */
```

### Padding/Margin Scale
```css
p-1    { padding: 4px; }
p-2    { padding: 8px; }
p-3    { padding: 12px; }
p-4    { padding: 16px; }
p-5    { padding: 20px; }
p-6    { padding: 24px; }
p-7    { padding: 28px; }
p-8    { padding: 32px; }
p-10   { padding: 40px; }
p-12   { padding: 48px; }
p-16   { padding: 64px; }
p-20   { padding: 80px; }
```

### Border Radius
```css
rounded-none    { border-radius: 0; }
rounded-sm      { border-radius: 4px; }
rounded         { border-radius: 8px; }
rounded-lg      { border-radius: 12px; }
rounded-xl      { border-radius: 16px; }
rounded-2xl     { border-radius: 20px; }
rounded-full    { border-radius: 999px; }  /* Pill shape */
```

### Gap Spacing (grid/flex)
```css
gap-2    { gap: 8px; }
gap-3    { gap: 12px; }
gap-4    { gap: 16px; }
gap-5    { gap: 20px; }
gap-6    { gap: 24px; }
gap-8    { gap: 32px; }
gap-10   { gap: 40px; }
gap-12   { gap: 48px; }
gap-16   { gap: 64px; }
```

---

## 🎯 Box Shadow Presets

```css
/* Light shadow (cards) */
box-shadow: 0 8px 24px rgba(0,0,0,0.08);

/* Medium shadow (hover) */
box-shadow: 0 12px 32px rgba(0,0,0,0.12);

/* Strong shadow (featured) */
box-shadow: 0 12px 32px rgba(0,0,0,0.15);

/* Inset shadow */
box-shadow: inset 0 0 0 2px #eee;

/* Green tinted shadow */
box-shadow: 0 4px 12px rgba(107,185,127,0.3);

/* Orange tinted shadow */
box-shadow: 0 4px 12px rgba(255,166,43,0.3);

/* Multiple shadows */
box-shadow: 0 4px 12px rgba(107,185,127,0.3), 
            0 0 0 1px rgba(107,185,127,0.1);
```

---

## 🔤 Font Weight Scale

```css
font-weight: 400;   /* Normal - Body text */
font-weight: 500;   /* Medium - Labels, secondary headings */
font-weight: 600;   /* Semi-bold - Emphasis */
font-weight: 700;   /* Bold - Headings, labels */
font-weight: 900;   /* Extra bold - Hero titles, strong emphasis */
```

---

## 📏 Line Height Values

```css
line-height: 1.3;   /* Tight - Headlines */
line-height: 1.4;   /* Comfortable - Subheadings */
line-height: 1.5;   /* Standard - Body text (default) */
line-height: 1.6;   /* Relaxed - Body text, cards */
line-height: 1.8;   /* Loose - Footer, descriptive text */
```

---

## 🎬 Transition Presets

```css
/* Standard transition */
transition: all 0.3s ease;

/* Specific property transitions */
transition: transform 0.3s ease;
transition: box-shadow 0.3s ease;
transition: color 0.3s ease;
transition: background-color 0.3s ease;
transition: border-color 0.3s ease;

/* Multiple properties */
transition: transform 0.3s ease, box-shadow 0.3s ease;
transition: all 0.2s ease;

/* Timing functions */
ease        /* Smooth curve - recommended */
linear      /* Constant speed */
ease-in     /* Slow start */
ease-out    /* Slow end */
ease-in-out /* Slow start and end */
```

---

## 🎪 Button Styles Reference

### Primary Button
```css
background: linear-gradient(135deg, #2F5133, #3B6B4A);
color: #fff;
padding: 12px 20px;
border-radius: 8px;
font-weight: 700;
box-shadow: 0 4px 12px rgba(47,81,51,0.3);

/* Hover */
background: linear-gradient(135deg, #3B6B4A, #6BB97F);
transform: translateY(-2px);
box-shadow: 0 6px 16px rgba(47,81,51,0.4);
```

### Secondary Button (Orange)
```css
background: linear-gradient(135deg, #FFA62B, #FFB84D);
color: #2F5133;
padding: 12px 20px;
border-radius: 8px;
font-weight: 700;

/* Hover */
background: linear-gradient(135deg, #FFB84D, #FFA62B);
```

### Large Button
```css
padding: 16px 28px;
font-size: 1.05rem;
```

---

## 🎨 Gradient Presets

### Primary Green Gradient
```css
background: linear-gradient(135deg, #2F5133, #3B6B4A);
background: linear-gradient(135deg, #3B6B4A, #6BB97F);
```

### Warm Orange Gradient
```css
background: linear-gradient(135deg, #FFA62B, #FFB84D);
background: linear-gradient(135deg, #FFB84D, #FFA62B);
```

### Neutral Gradient
```css
background: linear-gradient(135deg, #FFFFFF, #F6E6D7);
background: linear-gradient(135deg, #F6E6D7, #FFFFFF);
background: linear-gradient(135deg, #FFFFFF, #E7F0DC);
background: linear-gradient(135deg, #E7F0DC, #F6E6D7);
```

### Footer Gradient
```css
background: linear-gradient(135deg, #2F5133, #1a3320);
```

---

## 🔍 CSS Class Reference

### Container & Layout
```css
.container       /* max-width: 1200px; centered */
.hero-inner      /* flex with gap: 60px */
.features-inner  /* grid 3 columns, gap 32px */
.products-grid   /* grid auto-fill, gap 28px */
```

### Typography
```css
.section-title        /* 2.5rem, #2F5133, centered */
.hero-title           /* 3.5rem, bold, green/orange */
.product-name         /* 1.1rem, bold, #2F5133 */
.product-description  /* 0.9rem, gray */
.blog-excerpt         /* 0.95rem, gray, flex-1 */
```

### Cards & Containers
```css
.card              /* white, rounded-xl, shadow, border */
.product-card      /* gradient background, shadow on hover */
.blog-card         /* white, overflow hidden */
.feature-card      /* light green, rounded-xl */
```

### Buttons
```css
.btn-primary       /* green gradient, shadow */
.btn-hero          /* green gradient, pill shape */
.btn-hero-secondary /* orange gradient */
.btn-add           /* green, smaller padding */
.btn-full          /* width: 100% */
.btn-lg            /* 16px padding, 18px font */
```

### Text Utilities
```css
.text-center       /* text-align: center */
.empty-state       /* centered, gray, larger font */
.blog-meta         /* small, gray, flex layout */
```

---

## 📱 Responsive Breakpoints

```css
/* Mobile First Approach */
/* Base styles apply to all screens */

/* Tablet and up */
@media (min-width: 768px) {
  /* Tablet-specific styles */
}

/* Desktop and up */
@media (min-width: 1200px) {
  /* Desktop optimizations */
}

/* Large Desktop */
@media (min-width: 1400px) {
  /* Extra large screens */
}

/* Small Mobile */
@media (max-width: 640px) {
  /* Mobile optimizations */
}
```

---

## 🎭 Transform Effects

```css
/* Scale */
transform: scale(1.05);        /* Enlarge 5% */
transform: scale(0.95);        /* Reduce 5% */

/* Translate */
transform: translateY(-2px);   /* Move up 2px */
transform: translateY(-5px);   /* Move up 5px */
transform: translateX(10px);   /* Move right 10px */

/* Rotate */
transform: rotate(45deg);      /* Rotate 45 degrees */
transform: rotate(-2deg);      /* Rotate -2 degrees */

/* Multiple transforms */
transform: scale(1.05) translateY(-2px);
```

---

## 🎨 Customization Checklist

### Change Primary Color
1. Find `.section-title` - update color
2. Find `#2F5133` throughout file
3. Replace with new color
4. Update button gradients

### Change Accent Color
1. Find `#FFA62B` - Orange accent
2. Find `#FFB84D` - Orange hover
3. Replace with new accent color
4. Test all buttons and badges

### Change Font
1. Add `@font-face` or Google Fonts link
2. Find `font-family: 'Arial'`
3. Replace with new font name
4. Adjust font weights

### Change Spacing
1. Adjust `.container` max-width
2. Modify gap sizes in grid classes
3. Adjust padding values
4. Test responsiveness

### Add Dark Mode
1. Create new CSS variables
2. Add dark color values
3. Create @media (prefers-color-scheme: dark)
4. Update all color references

---

## 🔗 Import Statements

```css
/* Use in base.html.twig */
<link rel="stylesheet" href="{{ asset('css/homepage.css') }}">

/* Or in Webpack */
import './styles/homepage.css';
```

---

## 🛠️ Developer Tips

### Using CSS Custom Properties (Future Enhancement)
```css
:root {
  --color-primary: #2F5133;
  --color-green: #6BB97F;
  --color-orange: #FFA62B;
  --color-text: #5f6b74;
  --spacing-sm: 8px;
  --spacing-md: 16px;
  --spacing-lg: 32px;
  --shadow-sm: 0 4px 12px rgba(0,0,0,0.08);
  --shadow-lg: 0 12px 32px rgba(0,0,0,0.12);
  --border-radius: 12px;
}

/* Usage */
.button {
  background: var(--color-primary);
  padding: var(--spacing-md);
  border-radius: var(--border-radius);
  box-shadow: var(--shadow-sm);
}
```

### Optimizing for Performance
```css
/* Prefer transform for animations */
/* Instead of: left, top, width, height */
transform: translateX(10px);

/* Use will-change sparingly */
.element:hover {
  will-change: transform;
}

/* Consider using contain for performance */
.product-card {
  contain: layout style paint;
}
```

---

## 📊 CSS Statistics

- **Total Lines**: 1000+
- **Color Values**: 20+ unique colors
- **Breakpoints**: 4 responsive ranges
- **Components**: 30+ custom classes
- **Animations**: 10+ transition effects
- **Gradients**: 6+ gradient presets
- **Shadow Presets**: 5+ box-shadow variations

---

**Last Updated**: December 5, 2025
**Version**: 1.0
**Status**: Production Ready ✅
