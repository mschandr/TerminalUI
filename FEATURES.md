# TerminalUI Framework - Feature Summary

## Overview

A powerful, CSS-like Terminal User Interface (TUI) framework for PHP, inspired by Turbo Vision. Built as a standalone Composer package.

## Core Architecture

### 1. Event System (`src/Events/`)

**Event Base Class**
- Event propagation and handling
- `setHandled()` to stop propagation
- Event bubbling through component tree

**KeyEvent**
- Comprehensive keyboard input handling
- Special key constants (ESC, TAB, ENTER, arrows, function keys)
- Ctrl+key detection with `isCtrl()` and `getCtrlChar()`
- Printable character detection
- Human-readable key names

**MouseEvent** (future enhancement)
- Click detection (left, right, middle)
- Position tracking
- Scroll events

### 2. Core Components (`src/Core/`)

**View** - Base class for all UI components
- Abstract base like TView in Turbo Vision
- Child management: `add()`, `remove()`, `removeAll()`
- Focus management: `focus()`, `blur()`, `focusNext()`, `focusPrev()`
- Event handling with bubbling
- Tree navigation: `getParent()`, `findChild()`, `getApplication()`
- Positioning: `getAbsolutePosition()`, `moveCursor()`, `clear()`
- Visibility and state: `show()`, `hide()`, `enable()`, `disable()`

**Window** - Bordered container with title
- Extends View
- Customizable border and title colors
- Closable with ESC key
- Tab navigation between children
- `getContentBounds()` for inner area positioning

**Application** - Main event loop (like TApplication)
- Non-blocking keyboard input
- Terminal initialization and cleanup
- Signal handling (SIGINT, SIGTERM)
- Event distribution to windows
- Frame-based rendering (~60 FPS)
- `run()` method for main loop
- `requestRedraw()` for manual refresh

**Desktop** - Window manager (like TDesktop)
- Manages multiple windows
- Z-ordering (window stacking)
- Active window tracking
- Event routing to focused window
- Window arrangement: `cascade()`, `tileHorizontal()`, `tileVertical()`
- Window switching: `nextWindow()`, `prevWindow()`

**Rect** - Rectangle positioning
- **Two-point definition**: `Rect::fromPoints(x1, y1, x2, y2)`
- CSS-like creation: `Rect::fromStyle(style, parent)`
- Geometry methods: `contains()`, `intersects()`, `center()`
- Positioning: `move()`, `resize()`, `shrink()`, `expand()`

### 3. Component Library (`src/Components/`)

**Label** - Text display
- Single or multi-line text
- Text alignment (left, center, right)
- Word wrapping
- Color and bold styling

**Button** - Interactive button
- Click handler callbacks
- Hover/focus states
- Keyboard activation (Enter/Space)
- Visual feedback on press
- Custom styling for normal/focused/pressed states

**Panel** - Container for other components
- Optional border
- Background color
- Padding support
- `getContentBounds()` for child placement
- `addText()` helper for quick content

**ListBox** - Scrollable selection list
- Arrow key navigation
- Page Up/Down support
- Home/End navigation
- Scrolling when items exceed height
- Selection highlighting
- `onSelect` and `onActivate` callbacks

**Input** - Single-line text input
- Text editing with cursor positioning
- Character insertion/deletion
- Home/End navigation
- Left/Right arrow navigation
- Backspace/Delete support
- Placeholder text
- Password mode (character masking)
- Max length validation
- `onChange` and `onSubmit` callbacks

### 4. Styling System (`src/Styling/`)

**StyleSheet** - CSS-like property system
- Position: `top`, `left`, `right`, `bottom`
- Size: `width`, `height` (supports percentages!)
- Spacing: `padding`, `margin` with CSS syntax (`"1 2 3 4"`)
- Colors: `foreground`, `background`, `border-color`
- Border: `border` (single, double, rounded, thick, etc.)
- Font: `font-weight` (bold)
- Text: `text-align` (left, center, right)

**Example:**
```php
$style = StyleSheet::create([
    'width' => '80%',        // Percentage!
    'height' => 20,
    'padding' => '1 2',      // CSS syntax (top/bottom, left/right)
    'background' => Color::BLUE,
    'border' => 'double',
]);
```

**Color** - Color management
- 16 basic colors + bright variants
- 256-color support
- RGB and hex color support
- `toForegroundCode()`, `toBackgroundCode()`

### 5. Layout System (`src/Layout/`)

**Border** - Customizable borders
- **Define each border character individually**
- Presets: `single()`, `double()`, `rounded()`, `thick()`, `ascii()`, `dots()`, `stars()`, `none()`
- Fluent interface: `top()`, `bottom()`, `left()`, `right()`, `topLeft()`, `topRight()`, etc.
- `draw()` method for rendering

**Example:**
```php
// Use preset
$border = Border::double();

// Or customize each character
$border = Border::single()
    ->topLeft('╔')
    ->topRight('╗')
    ->top('═')
    ->borderColor(Color::CYAN);
```

## Key Features

### ✅ Two-Point Window Definition

```php
// Define window from top-left to bottom-right
$window = new Window(
    Rect::fromPoints(10, 5, 90, 25),
    "My Window"
);
```

### ✅ CSS-Like Styling

```php
$panel = new Panel(StyleSheet::create([
    'width' => '50%',          // Percentage support!
    'height' => '100%',
    'padding' => '1 2',        // CSS syntax
    'background' => Color::BLUE,
    'border' => 'rounded',
]));
```

### ✅ Custom Border Characters

```php
$border = Border::double()
    ->top('═')
    ->bottom('═')
    ->left('║')
    ->right('║')
    ->topLeft('╔');
```

### ✅ Event-Driven Architecture

```php
$button = new Button("Click Me!", $style, function() {
    echo "Button clicked!";
});

$listBox = new ListBox($items, $style);
$listBox->setOnActivate(function($item) {
    echo "Selected: $item";
});
```

### ✅ Focus Management

```php
// Automatic focus cycling with Tab
$window->add($button1);
$window->add($button2);
$window->add($button3);

// User presses Tab to cycle through
```

### ✅ Window Management

```php
$app = new Application();

$app->addWindow($window1);
$app->addWindow($window2);

$app->run(); // Event loop with 60 FPS rendering
```

## Space Wars Integration

### Installation

```bash
cd /home/mdhas/workspace/space-wars-3002
composer config repositories.terminalui path ../terminalui-framework
composer require terminalui/framework:@dev
```

### Usage Example

```php
// Old way (manual ANSI codes)
$this->line(str_repeat('═', 100));
echo "\033[31mRed text\033[0m\n";

// New way (TUI framework)
$window = new Window(
    Rect::fromPoints(0, 0, 120, 30),
    "Space Wars 3002"
);

$window->setBorder(Border::double());
$window->setBorderColor(Color::CYAN);

$label = new Label(
    "Welcome to Space Wars!",
    StyleSheet::create([
        'top' => 1,
        'foreground' => Color::BRIGHT_CYAN,
        'font-weight' => 'bold',
    ])
);

$window->add($label);
```

## Testing

### Simple Test
```bash
cd /home/mdhas/workspace/terminalui-framework
php examples/simple-test.php
```

### Full Demo
```bash
php examples/demo.php
```

### Space Wars TUI
```bash
cd /home/mdhas/workspace/space-wars-3002
php artisan player:interface-tui {player_id}
```

## Package Structure

```
terminalui-framework/
├── composer.json            # Package configuration
├── LICENSE                  # MIT License
├── README.md               # Documentation
├── INTEGRATION.md          # Space Wars integration guide
├── FEATURES.md            # This file
│
├── src/
│   ├── Core/
│   │   ├── Application.php    # Main event loop
│   │   ├── Desktop.php        # Window manager
│   │   ├── View.php          # Base component
│   │   ├── Window.php        # Bordered container
│   │   └── Rect.php          # Positioning
│   │
│   ├── Components/
│   │   ├── Label.php         # Text display
│   │   ├── Button.php        # Interactive button
│   │   ├── Panel.php         # Container
│   │   ├── ListBox.php       # Selection list
│   │   └── Input.php         # Text input
│   │
│   ├── Events/
│   │   ├── Event.php         # Base event
│   │   ├── KeyEvent.php      # Keyboard events
│   │   └── MouseEvent.php    # Mouse events
│   │
│   ├── Styling/
│   │   ├── StyleSheet.php    # CSS-like styles
│   │   └── Color.php         # Color management
│   │
│   └── Layout/
│       └── Border.php        # Border customization
│
└── examples/
    ├── simple-test.php       # Basic test
    └── demo.php             # Full feature demo
```

## Technical Details

### Requirements
- PHP ^8.2
- ext-pcntl (signal handling)
- ext-posix (terminal control)

### Terminal Features
- Raw mode input (non-blocking)
- ANSI escape sequences
- Box-drawing characters
- 16-color, 256-color, RGB support
- Cursor positioning and visibility control

### Performance
- ~60 FPS rendering
- Event-driven updates
- Efficient redraw system with `invalidate()`
- Non-blocking input for smooth interaction

## Future Enhancements

- Mouse support (clicks, drag, scroll)
- Menu bar component
- Textarea (multi-line input)
- Progress bar
- Dialog boxes (modal windows)
- Tree view component
- Table/grid component
- Color picker
- Terminal size detection
- Scrollable containers
- Layout managers (flexbox-style)

---

Built with ❤️ for Space Wars 3002
