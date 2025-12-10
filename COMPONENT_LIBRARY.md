# GreenBites Homepage - Visual Component Library

## 🎨 Complete Component Reference

### Hero Section

```
┌─────────────────────────────────────────────────────┐
│ HERO SECTION - Full Width Background Beige         │
│                                                     │
│  ┌────────────────────────────────────────────────┐ │
│  │  [Left Content]              [Right Image]    │ │
│  │                                                │ │
│  │  Vegetables                                     │ │
│  │  100% Organic                [Fresh Produce]  │ │
│  │                                Illustration   │ │
│  │  Get fresh, healthy produce                    │ │
│  │  delivered to your door.                       │ │
│  │  Save up to 50% on your first                  │ │
│  │  order!                                        │ │
│  │                                                │ │
│  │  [Shop Now] [View Plans]                      │ │
│  │                                                │ │
│  └────────────────────────────────────────────────┘ │
│                                                     │
│  Desktop: 2 columns, centered                      │
│  Mobile: 1 column, stacked                         │
│  Min Height: 500px                                 │
└─────────────────────────────────────────────────────┘

Text Styling:
  - "Vegetables" = Deep Green, Large
  - "100% Organic" = Orange, Extra Large, Bold
  - Subtitle = Gray, Medium
  - Buttons = Green & Orange Gradients
```

### Feature Cards Section

```
┌─────────────────────────────────────────────────────┐
│ FEATURES - Light Beige Background                  │
│                                                     │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐
│  │     🥬       │  │      ⚡      │  │      ♻️      │
│  │   48px Icon  │  │   48px Icon  │  │   48px Icon  │
│  │              │  │              │  │              │
│  │ 100% ORGANIC │  │ FAST SHIPPING│  │ FREE SHIPPING│
│  │ PRODUCE      │  │ IN THE CITY  │  │ ON ORDERS   │
│  │              │  │              │  │ $70+        │
│  │ Sourced      │  │ Same-day     │  │ Eco-friendly │
│  │ fresh daily  │  │ delivery     │  │ practices    │
│  │              │  │              │  │              │
│  └──────────────┘  └──────────────┘  └──────────────┘
│
│  3 Columns on Desktop
│  1 Column on Mobile
│  Gap: 32px
│  Card Padding: 32px 24px
│  Card Background: Light Green
└─────────────────────────────────────────────────────┘

Card Styling:
  - Icon: 48px, centered
  - Title: Uppercase, Bold, Deep Green
  - Description: Gray, Medium
  - Hover: Lift Y -5px, Enhanced Shadow
```

### Product Card Component

```
┌─────────────────────────────┐
│  [Sale Badge - Top Left]    │
│  ┌───────────────────────┐  │
│  │                       │  │
│  │   Product Image       │  │  1:1 Aspect Ratio
│  │   (1:1 Ratio)         │  │  Hover: Scale 1.05
│  │                       │  │
│  └───────────────────────┘  │
├─────────────────────────────┤
│ Product Name                 │  Bold, Deep Green
│                              │
│ This is a product...        │  Gray, Small
│                              │
│ ⭐⭐⭐⭐⭐ (23)            │  Stars + Count
│                              │
│ ₱249.00 │ Vegetables         │  Price + Tag
│                              │
│ [Add to Cart]                │  Green Button
└─────────────────────────────┘

Styling:
  - Background: White + Beige Gradient
  - Border: 1px solid #E4CFBF
  - Border Radius: 16px
  - Padding: 20px
  - Shadow: 0 8px 24px rgba(0,0,0,0.08)
  - Hover: Y -5px, Enhanced Shadow
```

### Category Card

```
┌──────────────────┐
│      🥦          │  48px Icon
│                  │
│  Vegetables      │  Bold Heading
│                  │
└──────────────────┘

Styling:
  - Width: 200px min, auto max
  - Padding: 32px 24px
  - Text Align: Center
  - Border Radius: 16px
  - Background: Light Green
  - Hover: Y -5px, Shadow increase
  - Colors: Deep Green text
```

### Subscription/Offer Card

```
┌────────────────────────────────┐
│ [MOST POPULAR Badge]           │
│                                │
│ Plan Name                      │  Bold, Large
│ Essential vegetables for healthy│  Gray
│ living. Fresh weekly delivery  │  
│                                │
│ ₱X,XXX.00                      │  Large, Bold
│ / Month                        │  Small, Gray
│                                │
│ Meals Included: 10 meals       │  Light Green Box
│                                │
│ [Subscribe Now]                │  Green Button
│                                │
└────────────────────────────────┘

Styling:
  - Background: Light Green
  - Border: 1px solid #E4CFBF
  - Border Radius: 16px
  - Padding: 28px
  - Shadow: 0 8px 24px rgba(0,0,0,0.08)
  - Badge: Orange Gradient, Top Right
  - Hover: Y -5px, Enhanced Shadow
```

### Blog Card

```
┌──────────────────────────┐
│                          │
│   Blog Featured Image    │  16:9 Ratio
│   Hover: Scale 1.05      │
│                          │
├──────────────────────────┤
│ Dec 3, 2025 | By Admin   │  Small, Gray
│                          │
│ Blog Post Title Here     │  Bold, Medium
│                          │
│ This is the excerpt      │  Gray Text
│ text for the blog post   │  Multiple Lines
│ description...           │
│                          │
│ [Read More →]            │  Green Link
└──────────────────────────┘

Styling:
  - Background: White
  - Border: 1px solid #E4CFBF
  - Border Radius: 16px
  - Padding: 28px
  - Shadow: 0 8px 24px rgba(0,0,0,0.08)
  - Image Height: 200px (16:9)
  - Hover: Y -5px, Image +shadow
```

### Button Variations

```
PRIMARY BUTTON
┌────────────────────┐
│  [Shop Now]        │
└────────────────────┘
  Background: Green Gradient
  Color: White
  Padding: 12px 20px
  Border Radius: 8px
  Font Weight: 700
  Hover: Lighter Green, Y -2px, Shadow+

SECONDARY BUTTON (Orange)
┌────────────────────┐
│  [View Plans]      │
└────────────────────┘
  Background: Orange Gradient
  Color: Deep Green
  Padding: 12px 20px
  Border Radius: 8px
  Font Weight: 700
  Hover: Reversed Gradient, Shadow+

LARGE BUTTON
┌────────────────────┐
│  [View All]        │
└────────────────────┘
  Padding: 16px 28px
  Font Size: 17px
  Border Radius: 8px

FULL WIDTH BUTTON
┌──────────────────────────────┐
│  [Add to Cart]               │
└──────────────────────────────┘
  Width: 100%
  Text Align: Center
```

### Input Components

```
SEARCH BOX
┌─────────────────────────────┐
│ Search for products...  [🔍] │
└─────────────────────────────┘
  Border Radius: 25px (Pill)
  Border: 2px solid #E4CFBF
  Padding: 12px 15px
  Focus: #6BB97F border, subtle shadow

EMAIL INPUT (Newsletter)
┌─────────────────┐ ┌─────────┐
│ Your email...   │ │[Submit] │
└─────────────────┘ └─────────┘
  Border Radius: 6px
  Border: 1px solid rgba(107,185,127,0.4)
  Padding: 10px 16px
  Background: Transparent dark
```

### Badge Components

```
SALE BADGE (Red-Orange)
┌──────────┐
│  Sale    │
└──────────┘
  Background: Red-Orange Gradient
  Color: White
  Padding: 6px 12px
  Border Radius: 20px
  Font Size: 12px
  Font Weight: 700

POPULAR BADGE (Orange)
┌──────────────┐
│ MOST POPULAR │
└──────────────┘
  Background: Orange Gradient
  Color: Deep Green
  Padding: 6px 12px
  Border Radius: 20px
  Font Size: 12px

CATEGORY TAG (Light Green)
┌────────────────┐
│  Vegetables    │
└────────────────┘
  Background: Light Green
  Color: Deep Green
  Padding: 4px 12px
  Border Radius: 12px
  Font Size: 12px
```

### Navigation Components

```
MAIN HEADER
┌─────────────────────────────────────────────────────┐
│ [Logo] [Search...] [🛒(0)] [👤] [Register]         │
└─────────────────────────────────────────────────────┘
  Sticky (position: sticky)
  Background: White + Beige Gradient
  Height: 60-70px
  Shadow: 0 4px 12px rgba(0,0,0,0.08)

MENU BAR
┌─────────────────────────────────────────────────────┐
│ [📂 All Categories] Home  Shop  Plans  About        │
└─────────────────────────────────────────────────────┘
  Background: Dark Green Gradient
  Color: White
  Height: 50-60px
  Shadow: 0 4px 12px rgba(0,0,0,0.15)

TOP BAR
┌─────────────────────────────────────────────────────┐
│ 🌱 Best deals every day    📞 Call: 1(888)123-4567 │
└─────────────────────────────────────────────────────┘
  Background: Green Gradient
  Color: White
  Height: 40-50px
  Font Size: 14px
```

### Footer Section

```
┌──────────────────────────────────────────────┐
│ FOOTER - Dark Green Background               │
│                                              │
│  [Brand]    [Links]    [Contact]  [Mail]   │
│  • Logo     • Home     📞 Number  [Email]   │
│  • Desc     • Shop     ✉️ Email   [Submit] │
│  • Social   • About    💳💰🏦                │
│            • Plans                          │
│                                              │
├──────────────────────────────────────────────┤
│ © 2025 GreenBites | [Privacy] [Terms] [Help]│
└──────────────────────────────────────────────┘

Styling:
  - Background: Dark Green Gradient
  - Text Color: Light Green/White
  - Column Layout: 4 columns (responsive 1 on mobile)
  - Gap: 40px
  - Padding: 80px 0 40px
  - Links: Green hover color
```

### Rating Component

```
⭐⭐⭐⭐⭐ (23)
│                │
└─ Stars (Yellow)└─ Review Count (Gray)

Styling:
  - Font Size: 16px (stars), 12px (count)
  - Letter Spacing: 2px
  - Color: Gold (stars), Gray (count)
  - Display: Flex, Gap 8px
```

### Benefit List Item

```
✓ Pesticide-Free
  100% organic cultivation

✓ Farm Direct
  Delivered fresh daily

✓ Sustainable
  Eco-friendly practices

Styling:
  - Icon: Green, Bold, 24px
  - Title: Bold, Deep Green, 18px
  - Description: Gray, 15px
  - Layout: Flex, Gap 16px
  - Margin: 20px between items
```

## 🎯 Component Usage Guidelines

### When to Use Each Component

**Hero Section**: 
- At top of page
- Use for main value proposition
- Include high-quality image

**Feature Cards**:
- Below hero section
- Highlight 3-5 key benefits
- Use icons for visual interest

**Product Cards**:
- Grid layout (4, 3, 2, or 1 column)
- Include images, pricing, ratings
- Use for showcase sections

**Category Cards**:
- Navigation section
- Show product categories
- Use emoji for quick recognition

**Blog Cards**:
- Latest content section
- Show featured images
- Include metadata

**Buttons**:
- Primary: Main CTAs (Shop, Subscribe)
- Secondary: Alternative actions
- Large: For prominent placement
- Full: For forms/contained areas

**Badges**:
- Sales: For discounted items
- Popular: For featured items
- Tags: For categorization

## 📊 Component Sizing

```
Small Components:    100-150px width
Medium Components:   200-300px width
Large Components:    300-400px width
Full Width:         100% of container

Heights (card content):
Compact:     200-250px
Standard:    300-400px
Tall:        400-500px+
```

## 🎨 Color Usage by Component

```
Headers/Titles:     Deep Green (#2F5133)
Body Text:          Body Gray (#5f6b74)
Buttons:            Green Gradient
Accents:            Orange (#FFA62B)
Backgrounds:        Beige (#F6E6D7)
Card BG:            Light Green (#E7F0DC)
Borders:            Tan (#E4CFBF)
Hover:              Bright Green (#6BB97F)
```

## ✨ Animation Triggers

```
On Hover:
  - Cards: Lift (Y -5px)
  - Buttons: Color shift, Lift (Y -2px)
  - Images: Scale (1.05)
  - Links: Color change

On Focus:
  - Inputs: Border color, Shadow
  - Buttons: Outline (browser default)
  - Links: Underline (optional)

Page Load:
  - Fade in (optional, can add)
  - No animations by default
```

---

**This library covers all visual components used in the GreenBites homepage redesign.**

Use this as a quick reference when building similar pages or modifying existing components.

**Last Updated**: December 5, 2025
