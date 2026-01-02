<?php

/**
 * TerminalUI Framework - Interactive Demo
 *
 * Demonstrates:
 * - Two-point window definition
 * - CSS-like styling
 * - Custom borders
 * - Component library
 * - Event handling
 *
 * Run: php examples/demo.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

use TerminalUI\Core\{Application, Window, Rect};
use TerminalUI\Components\{Label, Button, ListBox, Panel};
use TerminalUI\Styling\{StyleSheet, Color};
use TerminalUI\Layout\Border;

// Create application
$app = new Application();

// ==================== DEMO 1: Two-Point Window Definition ====================

echo "Creating window with two-point definition: (10, 3) → (110, 27)\n\n";

$mainWindow = new Window(
    Rect::fromPoints(10, 3, 110, 27),
    "TerminalUI Framework Demo"
);

$mainWindow->setBorder(
    Border::double()->borderColor(Color::CYAN)
);

// ==================== DEMO 2: CSS-like Styled Components ====================

// Title with CSS-like styling
$titleStyle = StyleSheet::create([
    'top' => 1,
    'left' => 2,
    'font-weight' => 'bold',
    'foreground' => Color::BRIGHT_YELLOW,
    'text-align' => 'center',
]);

$title = new Label(
    "🚀 CSS-LIKE STYLING FOR TERMINALS 🚀",
    $titleStyle
);

// Info box with percentage width
$infoStyle = StyleSheet::create([
    'top' => 3,
    'left' => 2,
    'width' => '45%',  // Percentage!
    'height' => 8,
    'border' => 'rounded',
    'border-color' => Color::GREEN,
    'padding' => 1,
    'background' => Color::BLACK,
]);

$infoBox = new Panel($infoStyle);
$infoBox->add(new Label("Two-Point Definition:", StyleSheet::create([
    'top' => 0,
    'foreground' => Color::BRIGHT_GREEN,
    'font-weight' => 'bold',
])));

$infoBox->add(new Label("Rect::fromPoints(x1, y1, x2, y2)", StyleSheet::create([
    'top' => 1,
    'foreground' => Color::WHITE,
])));

$infoBox->add(new Label("CSS Properties:", StyleSheet::create([
    'top' => 3,
    'foreground' => Color::BRIGHT_GREEN,
    'font-weight' => 'bold',
])));

$infoBox->add(new Label("width: 50%, height: 100%", StyleSheet::create([
    'top' => 4,
    'foreground' => Color::WHITE,
])));

$infoBox->add(new Label("padding: 1 2, margin: 2", StyleSheet::create([
    'top' => 5,
    'foreground' => Color::WHITE,
])));

// ==================== DEMO 3: Border Showcase ====================

$borderDemoStyle = StyleSheet::create([
    'top' => 3,
    'right' => 2,     // Stick to right edge!
    'width' => '45%',
    'height' => 18,
]);

$borderDemo = new Panel($borderDemoStyle);

// Show different border styles
$borders = [
    ['name' => 'Single', 'border' => Border::single()],
    ['name' => 'Double', 'border' => Border::double()],
    ['name' => 'Rounded', 'border' => Border::rounded()],
    ['name' => 'Thick', 'border' => Border::thick()],
    ['name' => 'ASCII', 'border' => Border::ascii()],
];

$y = 0;
foreach ($borders as $borderInfo) {
    $miniBox = new Panel(StyleSheet::create([
        'top' => $y,
        'left' => 0,
        'width' => 40,
        'height' => 3,
    ]));

    $miniBox->setBorder($borderInfo['border']);
    $miniBox->add(new Label($borderInfo['name'], StyleSheet::create([
        'top' => 0,
        'text-align' => 'center',
        'foreground' => Color::BRIGHT_CYAN,
    ])));

    $borderDemo->add($miniBox);
    $y += 3;
}

// ==================== DEMO 4: Interactive Components ====================

$buttonStyle = StyleSheet::create([
    'top' => 12,
    'left' => 2,
    'background' => Color::BLUE,
    'foreground' => Color::WHITE,
    'padding' => '0 3',
    'font-weight' => 'bold',
]);

$button1 = new Button("Press Me!", $buttonStyle, function() {
    echo "\n\nButton clicked! CSS-like TUI in action!\n\n";
});

$button2 = new Button("Another Button", $buttonStyle->set('left', 20)->set('background', Color::GREEN), function() {
    echo "\n\nSecond button works too!\n\n";
});

// ==================== DEMO 5: List Component ====================

$listStyle = StyleSheet::create([
    'top' => 15,
    'left' => 2,
    'width' => 40,
    'height' => 8,
    'border' => 'thick',
    'border-color' => Color::MAGENTA,
]);

$list = new ListBox([
    '⭐ Two-Point Windows',
    '🎨 CSS-like Styles',
    '🔲 Custom Borders',
    '🧩 Rich Components',
    '⚡ Fast Rendering',
], $listStyle);

// ==================== Add Everything to Window ====================

$mainWindow->add($title);
$mainWindow->add($infoBox);
$mainWindow->add($borderDemo);
$mainWindow->add($button1);
$mainWindow->add($button2);
$mainWindow->add($list);

// Status bar at bottom
$statusStyle = StyleSheet::create([
    'bottom' => 0,
    'left' => 0,
    'width' => '100%',
    'height' => 1,
    'background' => Color::BRIGHT_BLACK,
    'foreground' => Color::WHITE,
]);

$status = new Label("Press ESC to exit | TAB to focus next | ENTER to activate", $statusStyle);
$mainWindow->add($status);

// ==================== Run Application ====================

$app->addWindow($mainWindow);
$app->run();

echo "\n\n✨ Demo completed! Terminal UI with CSS-like styling! ✨\n\n";
