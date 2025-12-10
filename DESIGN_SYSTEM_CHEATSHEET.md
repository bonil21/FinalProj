# GreenBites - Design System Cheat Sheet

## 🎨 Color Swatches

```
Primary Colors
═════════════════════════════════════════
Deep Green         #2F5133  RGB(47,81,51)
Medium Green       #3B6B4A  RGB(59,107,74)  
Bright Green       #6BB97F  RGB(107,185,127)
Very Dark Green    #1a3320  RGB(26,51,32)

Accent Colors
═════════════════════════════════════════
Warm Orange        #FFA62B  RGB(255,166,43)
Soft Orange        #FFB84D  RGB(255,184,77)
Red-Orange         #FF6B6B  RGB(255,107,107)

Neutral Colors
═════════════════════════════════════════
Pure White         #FFFFFF  RGB(255,255,255)
Off-White          #FFFDF9  RGB(255,253,249)
Warm Beige         #F6E6D7  RGB(246,230,215)
Very Light Green   #F5FAF7  RGB(245,250,247)
Light Green        #E7F0DC  RGB(231,240,220)
Tan Border         #E4CFBF  RGB(228,207,191)

Text Colors
═════════════════════════════════════════
Very Dark Text     #1f2f24  RGB(31,47,36)
Primary Heading    #2F5133  RGB(47,81,51)
Body Text          #5f6b74  RGB(95,107,116)
Secondary Text     #6b7280  RGB(107,114,128)
Light Gray         #9CA3AF  RGB(156,163,175)
```

## 📐 Typography Scale

```
Hero Title        48px / 3.5rem    Font Weight 900
Section Title     40px / 2.5rem    Font Weight 900
Card Title        22px / 1.2rem    Font Weight 700
Product Name      17.6px / 1.1rem  Font Weight 700
Body Text         15.2px / 0.95rem Font Weight 400
Small Text        13.6px / 0.85rem Font Weight 500
Meta Text         12px / 0.75rem   Font Weight 500

Line Heights
─────────────────────────────────────────
Headlines         1.2 - 1.3
Subheadings       1.4
Body Text         1.6 - 1.8
```

## 🎨 Component Styles Quick Reference

```
BUTTONS
─────────────────────────────────────────
┌─ Primary Button ─────────────────────┐
│ Background: Green Gradient           │
│ Color: White                         │
│ Padding: 12px 20px                   │
│ Border Radius: 8px                   │
│ Font Weight: 700                      │
│ Shadow: 0 4px 12px rgba(47,81,51,0.3)│
│                                      │
│ Hover: Lighter Green, Y -2px, +shadow│
└──────────────────────────────────────┘

┌─ Secondary Button (Orange) ─────────┐
│ Background: Orange Gradient          │
│ Color: Deep Green                    │
│ Padding: 12px 20px                   │
│ Border Radius: 8px                   │
│ Font Weight: 700                      │
│                                      │
│ Hover: Reverse gradient, +shadow     │
└──────────────────────────────────────┘

┌─ Large Button ──────────────────────┐
│ Padding: 16px 28px                   │
│ Font Size: 17px                      │
└──────────────────────────────────────┘

CARDS
─────────────────────────────────────────
┌─ Product Card ──────────────────────┐
│ Background: White + Beige Gradient   │
│ Border: 1px solid #E4CFBF            │
│ Border Radius: 16px                  │
│ Shadow: 0 8px 24px rgba(0,0,0,0.08) │
│ Padding: 20px                        │
│                                      │
│ Hover: Y -5px, Stronger shadow       │
│        Image: scale 1.05             │
└──────────────────────────────────────┘

FEATURE CARDS
─────────────────────────────────────────
┌─ Feature Card ──────────────────────┐
│ Background: Light Green              │
│ Border: 1px solid #E4CFBF            │
│ Border Radius: 16px                  │
│ Padding: 32px 24px                   │
│ Text Align: Center                   │
│                                      │
│ Icon: 48px emoji                     │
│ Title: Uppercase, bold               │
│ Description: Gray text               │
│                                      │
│ Hover: Y -5px, +shadow              │
└──────────────────────────────────────┘

INPUTS
─────────────────────────────────────────
┌─ Search Box ────────────────────────┐
│ Border Radius: 25px (pill shape)     │
│ Border: 2px solid #E4CFBF            │
│ Background: Off-white                │
│ Padding: 12px 15px                   │
│                                      │
│ Focus: #6BB97F border, subtle shadow │
└──────────────────────────────────────┘

BADGES
─────────────────────────────────────────
┌─ Sale Badge ────────────────────────┐
│ Background: Red-Orange Gradient      │
│ Color: White                         │
│ Border Radius: 20px                  │
│ Padding: 6px 12px                    │
│ Font Size: 12px                      │
│ Font Weight: 700                      │
└──────────────────────────────────────┘

┌─ Category Badge ────────────────────┐
│ Background: Light Green              │
│ Color: Deep Green                    │
│ Border Radius: 12px                  │
│ Padding: 4px 12px                    │
│ Font Size: 12px                      │
└──────────────────────────────────────┘
```

## 📏 Spacing System

```
Padding/Margin Scale
═════════════════════════════════════════
 4px   = Extra Small (xs)
 8px   = Small (sm)
12px   = Small-Medium (xs-md)
16px   = Medium (md)
20px   = Medium-Large (md-lg)
24px   = Medium-Large (md-lg)
28px   = Large (lg)
32px   = Large (lg)
40px   = Extra Large (xl)
48px   = Extra Large (xl)
60px   = 2XL
80px   = 3XL

Grid Gaps
═════════════════════════════════════════
Products:    28px
Features:    32px
Categories:  24px
Blog:        32px
Footer:      40px

Section Padding
═════════════════════════════════════════
Normal Section:  60px top/bottom
Large Section:   80px top/bottom
```

## 🎬 Animation Values

```
Standard Transition
Duration:    0.3s
Easing:      ease
Property:    all

Transform Effects
─────────────────────────────────────────
Hover Lift Small:    translateY(-2px)
Hover Lift Medium:   translateY(-5px)
Image Zoom:          scale(1.05)
Subtle Rotate:       rotate(-2deg)

Box Shadow Animation
─────────────────────────────────────────
Light:    0 8px 24px rgba(0,0,0,0.08)
Medium:   0 12px 32px rgba(0,0,0,0.12)
Heavy:    0 12px 32px rgba(0,0,0,0.15)
Green:    0 4px 12px rgba(107,185,127,0.3)
Orange:   0 4px 12px rgba(255,166,43,0.3)
```

## 📱 Responsive Grid Reference

```
Desktop (1200px+)
─────────────────────────────────────────
Features:        3 columns
Categories:      4-5 columns
Products:        4 columns
Blog:            3 columns
Offers:          3 columns
Deals:           2 columns

Tablet (768px-1199px)
─────────────────────────────────────────
Features:        1 column
Categories:      2-3 columns
Products:        3 columns
Blog:            2 columns
Offers:          2 columns
Deals:           2 columns

Mobile (640px-767px)
─────────────────────────────────────────
Features:        1 column
Categories:      2 columns
Products:        2 columns
Blog:            1 column
Offers:          1 column
Deals:           1 column

Small Mobile (<640px)
─────────────────────────────────────────
All Grids:       1 column
Full Width:      100%
```

## 🎨 Gradient Combinations

```
Primary Green
background: linear-gradient(135deg, #2F5133, #3B6B4A);

Dark Green
background: linear-gradient(135deg, #3B6B4A, #6BB97F);

Warm Orange
background: linear-gradient(135deg, #FFA62B, #FFB84D);

Reversed Orange
background: linear-gradient(135deg, #FFB84D, #FFA62B);

Neutral Light
background: linear-gradient(135deg, #FFFFFF, #F6E6D7);

Neutral Beige
background: linear-gradient(135deg, #F6E6D7, #FFFFFF);

Neutral Green
background: linear-gradient(135deg, #FFFFFF, #E7F0DC);

Footer Dark
background: linear-gradient(135deg, #2F5133, #1a3320);
```

## 🖼️ Image Aspect Ratios

```
Product Images:    1:1 (square)
Blog Images:       16:9 (widescreen)
Deal Images:       1:1 (square)
Hero Image:        Varies
Background:        Full-width
```

## 🎯 Border Radius Values

```
Small:     4px
Medium:    8px
Large:     12px
Extra:     16px
Pill:      999px (fully rounded)
```

## 🔤 Font Weight Usage

```
400 - Body text, regular content
500 - Labels, secondary headings
600 - Semi-bold emphasis
700 - Main headings, bold text
900 - Extra bold, hero titles

Recommended Combinations
─────────────────────────────────────────
Headlines:     900 weight
Subheadings:   700 weight
Body:          400 weight
Labels:        600-700 weight
Meta:          500 weight
```

## 📐 Breakpoint Values

```
Mobile:        Default (< 640px)
Tablet:        640px - 768px
Tablet+:       768px - 1200px
Desktop:       1200px+
Large Desktop: 1400px+

Media Query Syntax
─────────────────────────────────────────
@media (min-width: 768px) { }
@media (min-width: 1200px) { }
@media (max-width: 640px) { }
```

## 🎪 Common Component Heights

```
Top Bar:          40-50px
Header/Navbar:    60-70px
Menu Bar:         50-60px
Button Default:   44px (with padding)
Button Large:     48px (with padding)
Product Card:     400-450px
Blog Card:        500-550px
```

## ✨ Hover State Changes

```
Cards:       Y -5px, +shadow
Buttons:     Y -2px, color shift, +shadow
Links:       Color: #2F5133 → #6BB97F
Images:      Scale 1.0 → 1.05
Opacity:     1.0 → 0.9 (on some elements)

Timing
─────────────────────────────────────────
All Transitions: 0.3s ease
Image Scale:     0.3s ease
Shadow:          0.3s ease
Color:           0.3s ease
```

## 🛠️ Quick CSS Snippets

### Center Content
```css
.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 20px;
}
```

### Flexbox Row
```css
.flex-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 20px;
}
```

### Grid 3 Columns
```css
.grid-3 {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 32px;
}
```

### Card Shadow
```css
box-shadow: 0 8px 24px rgba(0,0,0,0.08);
```

### Hover Lift
```css
&:hover {
  transform: translateY(-5px);
  box-shadow: 0 12px 32px rgba(0,0,0,0.12);
}
```

---

## 🎓 Common Tasks Quick Reference

### Change Primary Color
Find and replace: `#2F5133` with your color

### Change Accent Color
Find and replace: `#FFA62B` with your color

### Change Font Size
Modify the value in `font-size` property

### Add More Space
Increase `gap` or `padding` values

### Make Element Responsive
Add `@media` query for different breakpoints

### Change Button Style
Update `.btn-primary` class

### Adjust Container Width
Change `max-width` in `.container` class

---

**Last Updated**: December 5, 2025
**Version**: 1.0 Final
**Status**: Complete ✅
