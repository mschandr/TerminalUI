# TerminalUI Framework

> A powerful, CSS-like TUI (Text User Interface) framework for PHP - Inspired by Turbo Vision

[![PHP Version](https://img.shields.io/badge/PHP-%5E8.2-blue)](https://www.php.net)
[![License](https://img.shields.io/badge/license-MIT-green)](LICENSE)

## ✨ Features

- **CSS-like Styling** - Familiar syntax for terminal UI styling
- **Two-Point Window Definition** - Define windows with coordinates like `(x1, y1) → (x2, y2)`
- **Customizable Borders** - Full control over every border character
- **Component Library** - Buttons, Labels, Lists, Inputs, and more
- **Event System** - Keyboard and mouse event handling
- **Layout System** - Box, Grid, and Flex layouts
- **256-Color Support** - RGB colors and hex values
- **Turbo Vision-inspired** - Classic DOS-era TUI design modernized

## 📦 Installation

```bash
composer require terminalui/framework
```

## 🚀 Quick Start

### Basic Window with CSS-like Styling

```php
use TerminalUI\Core\{Application, Window, Rect};
use TerminalUI\Styling\{StyleSheet, Color};
use TerminalUI\Layout\Border;

// Create application
$app = new Application();

// Define window with CSS-like styles
$style = StyleSheet::create([
    'width' => '80%',        // Percentage of parent
    'height' => 20,          // Fixed height
    'top' => 5,              // Position from top
    'left' => 10,            // Position from left
    'background' => Color::BLUE,
    'foreground' => Color::WHITE,
    'border' => 'double',    // Border style
    'border-color' => Color::CYAN,
    'padding' => '1 2',      // Top/Bottom Left/Right
    'margin' => 2,           // All sides
]);

// Create window
$window = new Window($style, "My Window");
$app->addWindow($window);

// Run application
$app->run();
```

### Two-Point Window Definition

The feature you specifically asked for!

```php
use TerminalUI\Core\Rect;

// Define window from two coordinates
// Creates a window from (10, 5) to (90, 25)
$rect = Rect::fromPoints(
    x1: 10,  // Top-left X
    y1: 5,   // Top-left Y
    x2: 90,  // Bottom-right X
    y2: 25   // Bottom-right Y
);

$window = new Window($rect, "Two-Point Window");
```

### Customizable Borders

Full control over every border character:

```php
use TerminalUI\Layout\Border;

// Use preset styles
$border = Border::double();  // ╔═══╗
$border = Border::rounded(); // ╭───╮
$border = Border::thick();   // ┏━━━┓
$border = Border::ascii();   // +---+

// Or customize each character
$border = Border::create()
    ->top('═')
    ->bottom('═')
    ->left('║')
    ->right('║')
    ->topLeft('╔')
    ->topRight('╗')
    ->bottomLeft('╚')
    ->bottomRight('╝');

// Apply to window
$window->setBorder($border);
```

## 🎨 CSS-like Styling Reference

### Supported Properties

```php
$style = StyleSheet::create([
    // Dimensions
    'width' => '50%',          // Percentage of parent
    'width' => 80,             // Fixed pixels
    'height' => '100%',        // Fill parent
    'height' => 30,            // Fixed

    // Position
    'position' => 'absolute',  // or 'relative'
    'top' => 5,                // From top edge
    'left' => 10,              // From left edge
    'right' => 5,              // From right edge (calculates width)
    'bottom' => 3,             // From bottom edge (calculates height)

    // Spacing
    'padding' => 1,            // All sides
    'padding' => '1 2',        // Top/Bottom Left/Right
    'padding' => '1 2 3',      // Top Left/Right Bottom
    'padding' => '1 2 3 4',    // Top Right Bottom Left
    'margin' => 2,             // Same syntax as padding

    // Colors
    'background' => Color::BLUE,
    'foreground' => Color::WHITE,
    'color' => Color::GREEN,   // Alias for foreground

    // Borders
    'border' => 'single',      // single, double, rounded, thick, ascii
    'border-color' => Color::CYAN,
    'border-style' => 'solid',

    // Text
    'font-weight' => 'bold',   // normal, bold
    'text-align' => 'center',  // left, center, right
    'text-decoration' => 'underline',

    // Display
    'display' => 'block',      // block, inline, none
    'visibility' => 'visible', // visible, hidden
    'z-index' => 10,           // Stack order
]);
```

### Color Support

```php
use TerminalUI\Styling\Color;

// 16 Basic Colors
Color::BLACK, Color::RED, Color::GREEN, Color::YELLOW,
Color::BLUE, Color::MAGENTA, Color::CYAN, Color::WHITE

// Bright Variants
Color::BRIGHT_RED, Color::BRIGHT_GREEN, etc.

// Grays (256-color)
Color::GRAY_DARK, Color::GRAY, Color::GRAY_LIGHT

// RGB Colors (256-color mode)
Color::rgb(255, 100, 50);

// Hex Colors
Color::hex('#FF6432');
```

## 📐 Layout System

### Two-Point Definition (Your Request!)

```php
// Define any UI element with two coordinates
$fullScreenRect = Rect::fromPoints(0, 0, 120, 30);
$topHalfRect = Rect::fromPoints(0, 0, 120, 15);
$bottomHalfRect = Rect::fromPoints(0, 15, 120, 30);
$centerBoxRect = Rect::fromPoints(40, 10, 80, 20);

// Windows, panels, buttons - anything can use two-point definition
$window = new Window($centerBoxRect, "Centered Box");
```

### CSS-style Positioning

```php
// Full-screen window
$style = StyleSheet::create([
    'width' => '100%',
    'height' => '100%',
    'top' => 0,
    'left' => 0,
]);

// Right-side panel (30% width, full height, stick to right)
$style = StyleSheet::create([
    'width' => '30%',
    'height' => '100%',
    'top' => 0,
    'right' => 0,  // Stick to right edge
]);

// Centered dialog (like CSS: top/left with calculated dimensions)
$style = StyleSheet::create([
    'width' => 60,
    'height' => 15,
    'top' => 7,      // (30 - 15) / 2 for center
    'left' => 30,    // (120 - 60) / 2 for center
]);
```

## 🧩 Component Library

### Button

```php
use TerminalUI\Components\Button;

$button = new Button(
    text: "Click Me",
    style: StyleSheet::create([
        'background' => Color::GREEN,
        'foreground' => Color::BLACK,
        'padding' => '0 2',
    ]),
    onClick: fn() => echo "Clicked!\n"
);
```

### Label

```php
use TerminalUI\Components\Label;

$label = new Label(
    text: "Status: Ready",
    style: StyleSheet::create([
        'font-weight' => 'bold',
        'foreground' => Color::BRIGHT_GREEN,
    ])
);
```

### ListBox

```php
use TerminalUI\Components\ListBox;

$list = new ListBox(
    items: ['Option 1', 'Option 2', 'Option 3'],
    style: StyleSheet::create([
        'width' => 40,
        'height' => 10,
        'border' => 'rounded',
    ])
);
```

## 🎯 Complete Example: Galaxy Map Window

```php
use TerminalUI\Core\{Application, Window};
use TerminalUI\Components\{Label, ListBox, Button};
use TerminalUI\Styling\{StyleSheet, Color};
use TerminalUI\Layout\Border;

$app = new Application();

// Main window - two-point definition
$mainWindow = new Window(
    Rect::fromPoints(5, 2, 115, 28),
    "Galaxy Map - Sector View"
);

// Custom border
$mainWindow->setBorder(
    Border::double()->borderColor(Color::CYAN)
);

// Title label
$titleStyle = StyleSheet::create([
    'top' => 1,
    'left' => 2,
    'font-weight' => 'bold',
    'foreground' => Color::BRIGHT_YELLOW,
]);
$title = new Label("Current Sector: Alpha Centauri", $titleStyle);
$mainWindow->add($title);

// Star list
$listStyle = StyleSheet::create([
    'top' => 3,
    'left' => 2,
    'width' => '40%',
    'height' => 15,
    'border' => 'single',
    'border-color' => Color::GREEN,
]);
$starList = new ListBox([
    '⭐ Sol System',
    '⭐ Proxima Centauri',
    '⭐ Betelgeuse',
], $listStyle);
$mainWindow->add($starList);

// Action buttons
$buttonStyle = StyleSheet::create([
    'top' => 20,
    'left' => 2,
    'background' => Color::BLUE,
    'foreground' => Color::WHITE,
    'padding' => '0 2',
]);
$travelButton = new Button("Travel", $buttonStyle);
$mainWindow->add($travelButton);

$app->addWindow($mainWindow);
$app->run();
```

## 🎨 Border Examples

```php
// All preset styles
Border::single();   // ┌─┐ │ │ └─┘
Border::double();   // ╔═╗ ║ ║ ╚═╝
Border::rounded();  // ╭─╮ │ │ ╰─╯
Border::thick();    // ┏━┓ ┃ ┃ ┗━┛
Border::ascii();    // +--+ | | +--+
Border::dots();     // ···· · · ····
Border::stars();    // **** * * ****
Border::none();     // No border (invisible)

// Custom border for sci-fi theme
$scifiBorder = Border::create()
    ->top('▀')->bottom('▄')
    ->left('█')->right('█')
    ->topLeft('▛')->topRight('▜')
    ->bottomLeft('▙')->bottomRight('▟');
```

## 📊 Performance

- Minimal memory footprint
- Fast rendering with ANSI escape codes
- Efficient event loop
- Supports terminals with 16, 256, or true color

## 🔧 Requirements

- PHP ^8.2
- ext-pcntl (for signal handling)
- ext-posix (for terminal control)
- POSIX-compatible terminal

## 📝 License

MIT License - see LICENSE file

## 🙏 Acknowledgments

Inspired by:
- **Turbo Vision** (Borland's classic TUI framework)
- **React** (component-based architecture)
- **CSS** (styling syntax)

---

**Made with ❤️ for terminal enthusiasts**
