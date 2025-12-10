# GreenBites Homepage - Quick Start Guide

## 🚀 Getting Started

### Step 1: Verify Your Docker Setup
Make sure MySQL and phpMyAdmin are running with your restored database:

```powershell
cd C:\nathnath\Patatas
docker compose ps
```

You should see:
- ✅ `patatas-mysql-1` (Up)
- ✅ `patatas-phpmyadmin-1` (Up)

### Step 2: Build Frontend Assets
If you haven't built the Webpack assets yet:

```powershell
npm install
npm run build
```

### Step 3: Verify Database Connection
Test that your app can reach the database:

```powershell
# Check database tables exist
docker compose exec mysql mysql -u nathan_user -p nathan_password -e "USE nathan_db; SHOW TABLES;"
```

### Step 4: Start the Application
Choose one of these options:

**Option A: Using Symfony CLI (local PHP)**
```powershell
symfony serve
# Visit http://localhost:8000
```

**Option B: Using Docker (if you have a Dockerfile for the app)**
```powershell
docker compose up
```

**Option C: Using PHP built-in server**
```powershell
php -S localhost:8000 -t public
# Visit http://localhost:8000
```

### Step 5: View the New Homepage
Open your browser and navigate to `http://localhost:8000`

You should see:
- ✨ Modern, clean homepage
- 🎨 Professional color scheme
- 📱 Responsive design
- 🛍️ Product showcase
- 📝 Blog section
- 💚 Subscription plans

---

## 📋 What Changed

### New Sections Added
1. **Feature Cards** - "100% Organic", "Fast Shipping", "Free Shipping"
2. **Special Offers** - Subscription plans with badges
3. **Featured Products** - Grid with ratings and badges
4. **Healthy Benefits** - Why choose GreenBites
5. **Chef/Brand Story** - Company mission
6. **Deals of the Day** - Countdown timer and featured deal
7. **Top Rated Products** - Best sellers list
8. **Blog Section** - Latest articles/posts
9. **Enhanced Footer** - Newsletter, social links, payment methods

### Improved Visuals
- Modern color palette (greens, oranges, neutrals)
- Consistent spacing and alignment
- Hover effects and animations
- Better typography hierarchy
- Professional card designs
- Responsive grid layouts

---

## 🎨 Customization Guide

### Change Colors
Edit `assets/styles/homepage.css` - look for these variables at the top:

```css
/* Primary Colors */
--primary-green: #2F5133;
--accent-green: #6BB97F;
--orange-accent: #FFA62B;

/* Apply anywhere in CSS:
  background: var(--primary-green);
*/
```

### Change Fonts
Find `.hero-title` section in CSS and update:
```css
.hero-title {
    font-family: 'Your Font Name', sans-serif;
    font-size: 3.5rem;
}
```

### Change Section Content
Edit `templates/landing_page/index.html.twig`:
- Search for the section you want to change
- Update Twig variables and text
- Rebuild if needed

### Add New Sections
1. Copy an existing section block (e.g., `.features-section`)
2. Paste at desired location
3. Modify HTML and class names
4. Create corresponding CSS styles
5. Test responsiveness

---

## 🔧 Common Tasks

### Update Product Images
1. Place images in `public/uploads/images/`
2. Images are referenced in database
3. Update product records in phpMyAdmin or admin panel
4. Images automatically appear on homepage

### Change Homepage Banner Text
Edit in `templates/landing_page/index.html.twig`:
```twig
<h1 class="hero-title">
    <span class="hero-highlight-green">Your Text Here</span><br>
    <span class="hero-highlight-main">Your Text Here</span>
</h1>
```

### Update Contact Information
Edit footer section in template:
```twig
<p>📞 <strong>1 (888) 123-4567</strong></p>
<p>✉️ <strong>info@greenbites.com</strong></p>
```

### Add Blog Posts
1. Create blog entries in your database
2. They'll automatically appear in the "From the Blog" section
3. Each post shows: image, date, author, title, excerpt, "Read More" link

### Manage Subscription Plans
1. Open phpMyAdmin: `http://localhost:8081`
2. Navigate to your database
3. Edit subscription plan records
4. Changes appear immediately on homepage

---

## 📱 Testing Responsiveness

### Desktop (1200px+)
- Full multi-column layouts
- All sections side-by-side where applicable
- Optimal viewing experience

### Tablet (768px-1199px)
- Adjusted column counts
- Slightly reduced spacing
- Touch-friendly buttons

### Mobile (640px-767px)
- Single/dual column stacks
- Optimized button sizes
- Better touch interaction

### Small Mobile (<640px)
- Mostly single column
- Larger touch targets
- Simplified layouts

**Test in Browser DevTools:**
- Press F12 to open DevTools
- Click device icon (Ctrl+Shift+M)
- Select different devices from dropdown
- Test touch interactions

---

## 🐛 Troubleshooting

### Homepage Looks Different
**Solution**: Clear cache
```powershell
# Clear browser cache (Ctrl+Shift+Del)
# Or do a hard refresh (Ctrl+F5)
```

### CSS Not Loading
**Solution**: Rebuild assets
```powershell
npm run build
```

### Database Connection Error
**Solution**: Verify Docker setup
```powershell
docker compose down
docker compose up -d
# Wait 10 seconds for MySQL to start
docker compose exec mysql mysql -u root -p -e "SHOW DATABASES;"
```

### Images Not Showing
**Solution**: Check image paths
1. Verify images exist: `public/uploads/images/`
2. Check database has correct filenames
3. Verify file permissions (readable)

### Mobile Layout Broken
**Solution**: Check viewport meta tag
In `templates/base.html.twig`:
```html
<meta name="viewport" content="width=device-width, initial-scale=1.0">
```

---

## 📊 Performance Tips

### Optimize Images
Use an image optimizer before uploading:
- TinyPNG, ImageOptim, or similar
- Reduce file size without losing quality
- Target ~200-300KB per product image

### Enable Caching
In `.env.production`:
```env
APP_ENV=prod
APP_DEBUG=0
```

### Minify CSS/JS
```powershell
npm run build
# Automatically minifies for production
```

### Use CDN for Large Images
Consider uploading large images to:
- Cloudinary
- AWS S3
- DigitalOcean Spaces

---

## 📞 Support Resources

### Files Reference
- **Homepage Template**: `templates/landing_page/index.html.twig`
- **Stylesheet**: `assets/styles/homepage.css`
- **Base Template**: `templates/base.html.twig`
- **Documentation**: `HOMEPAGE_REDESIGN.md`

### Key CSS Classes
- `.hero` - Hero banner section
- `.features-section` - Feature cards
- `.featured-products-section` - Products grid
- `.blog-section` - Blog posts
- `.footer` - Footer area

### Twig Template Sections
- Hero section (lines 68-88)
- Features (lines 90-106)
- Categories (lines 108-119)
- Special Offers (lines 121-160)
- Featured Products (lines 162-205)
- Benefits (lines 207-235)
- Chef Section (lines 237-259)
- Deals & Top Rated (lines 261-328)
- Blog (lines 330-373)
- Footer (lines 375-429)

---

## ✅ Checklist Before Launch

- [ ] Database restored and verified
- [ ] Frontend assets built (`npm run build`)
- [ ] Homepage loads without errors
- [ ] All images display correctly
- [ ] Navigation links work
- [ ] Buttons have proper hover effects
- [ ] Responsive design tested on mobile
- [ ] Forms submit properly
- [ ] Cart functionality works
- [ ] Product prices display correctly
- [ ] Blog section populated (if applicable)
- [ ] Contact information updated
- [ ] Footer links functional
- [ ] All Twig variables rendering
- [ ] No console errors in DevTools

---

## 🎉 You're Ready!

Your GreenBites homepage is now live with a modern, professional design. Monitor performance and gather user feedback for future improvements.

**Questions?** Check the detailed `HOMEPAGE_REDESIGN.md` file for more information.

---

**Last Updated**: December 5, 2025
**Version**: 1.0
**Status**: Production Ready ✅
