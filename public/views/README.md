# Views Directory

This directory contains reusable UI components for the CRIM FAMS application.

## Components

### sidebar.html
A reusable sidebar navigation component that provides consistent navigation across all pages.

**Features:**
- Role-based navigation visibility
- Active page highlighting
- Consistent styling and structure
- Logout functionality

**Usage:**
```html
<div id="sidebar-container"></div>
<script src="js/sidebar.js"></script>
```

**JavaScript Integration:**
```javascript
// Load sidebar
sidebarManager.loadSidebar('sidebar-container');

// Update navigation based on user role
sidebarManager.updateNavigationVisibility('ADMIN');
```

## File Structure
```
views/
├── sidebar.html          # Main sidebar component
└── README.md            # This documentation
```

## Benefits
- ✅ **Consistency**: Same navigation across all pages
- ✅ **Maintainability**: Single file to update navigation
- ✅ **Reusability**: Easy to include in any page
- ✅ **Role-based**: Automatic permission handling
- ✅ **Active States**: Automatic current page highlighting

## Integration
1. Include `<div id="sidebar-container"></div>` in your HTML
2. Load `js/sidebar.js` script
3. The sidebar will automatically:
   - Load the component
   - Set active page
   - Handle user permissions
   - Setup logout functionality